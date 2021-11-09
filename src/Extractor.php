<?php

namespace Codewiser\Polyglot;

use Codewiser\Polyglot\Collections\EntryCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use Sepia\PoParser\Catalog\Entry;
use Sepia\PoParser\Parser;
use Sepia\PoParser\SourceHandler\FileSystem;

/**
 * Extracts strings from app source code using xgettext.
 */
class Extractor
{
    /**
     * Application name used for .pot headers
     *
     * @var string
     */
    protected string $app_name;

    protected FileLoader $loader;

    /**
     * Search strings in file system resources.
     *
     * @var array
     */
    protected array $sources;

    /**
     * Exclude file system resources from search.
     *
     * @var array
     */
    protected array $exclude = [];

    /**
     * xgettext executable.
     *
     * @var string
     */
    protected string $xgettext = 'xgettext';

    /**
     * Default domain for xgettext.
     *
     * @var string
     */
    protected string $domain = 'messages';

    /**
     * Default category for xgettext.
     *
     * @var int
     */
    protected int $category = LC_MESSAGES;

    /**
     * Filesystem shorthand.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected \Illuminate\Filesystem\Filesystem $fs;

    /**
     * @param string $app_name App name used in .pot header.
     * @param array $sources File system files and folders to search strings.
     */
    public function __construct(string $app_name, array $sources)
    {
        $this->app_name = $app_name;
        $this->sources = $sources;

    }

    public function setLoader(FileLoader $loader):Extractor
    {
        $this->loader = $loader;
        $this->fs = $loader->filesystem();
        return $this;
    }

    /**
     * Set xgettext executable.
     *
     * @param string $executable
     * @return $this
     */
    public function xgettext(string $executable): Extractor
    {
        $this->xgettext = $executable;
        return $this;
    }

    public function setDomain(string $domain): Extractor
    {
        $this->domain = $domain;
        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Exclude some files and folders from search.
     *
     * @param array $exclude
     * @return $this
     */
    public function exclude(array $exclude): Extractor
    {
        $this->exclude = $exclude;
        return $this;
    }

    /**
     * Get the path to .pot file (holds parsed strings).
     *
     * @return string
     */
    public function getPortableObjectTemplate(): string
    {
        return $this->loader->tmpPath() . DIRECTORY_SEPARATOR .
            'templates' . DIRECTORY_SEPARATOR .
            $this->categoryName($this->category) . DIRECTORY_SEPARATOR .
            $this->domain . '.pot';
    }

    /**
     * Get collected strings from portable object template file.
     *
     * @param string $pot
     * @return EntryCollection|Entry[]
     */
    public function getStrings(string $pot): EntryCollection
    {
        if (!$this->fs->exists($pot)) {
            return new EntryCollection;
        }

        $file = new FileSystem($pot);
        $parser = new Parser($file);
        try {
            $catalog = $parser->parse();
            return EntryCollection::make($catalog->getEntries());
        } catch (\Exception $e) {
            return new EntryCollection;
        }
    }

    /**
     * Start collecting strings.
     *
     * @return $this
     */
    public function extract(): Extractor
    {
        $this->clear();

        foreach ($this->sources as $resource) {
            $this->collectStrings($resource, $this->getPortableObjectTemplate(), $this->exclude);
        }

        return $this;
    }

    /**
     * Remove previously created portable object template with collected strings.
     */
    public function clear()
    {
        $output = $this->getPortableObjectTemplate();

        if ($this->fs->exists($output)) {
            $this->fs->delete($output);
        }
    }

    /**
     * Collect strings from given directory/file to given output file, excluding some resources...
     *
     * @param string $resource
     * @param string $output
     * @param array $excluding
     */
    protected function collectStrings(string $resource, string $output, array $excluding = [])
    {
        foreach ($this->resourceListing($resource, '*.php', $excluding) as $filename) {
            if (Str::endsWith($filename, '.blade.php')) {
                $tmp = $this->makeTemporaryBlade($filename);
            } else {
                $tmp = $this->makeTemporaryPhp($filename);
            }
            $this->prepareTemporary($tmp);
            $this->runXGetText('PHP', $tmp, $output);
            $this->fs->delete($tmp);
        }

        foreach ($this->resourceListing($resource, ['*.js', '*.vue'], $excluding) as $filename) {
            $this->runXGetText('JavaScript', $filename, $output);
        }
    }

    /**
     * Run xgettext.
     *
     * @param string $language
     * @param string $source
     * @param string $target
     */
    protected function runXGetText(string $language, string $source, string $target)
    {
        if (!$this->fs->exists($this->fs->dirname($target))) {
            $this->fs->makeDirectory($this->fs->dirname($target), 0777, true);
        }

        // For keywords
        // See https://www.gnu.org/software/gettext/manual/html_node/Default-Keywords.html

        $command = [
            $this->xgettext,
            '--language=' . $language,
            '--no-wrap',
            '--sort-output',
            '--from-code=UTF-8',
            '--package-name="' . $this->app_name . '"',
            '--output=' . $target,
//            '--output-dir=' . dirname($target),
            '--add-comments',
            '--keyword', // Disable defaults

            '--keyword=__',
            '--keyword=trans',

            '--keyword=gettext',
//            '--keyword=dgettext:2',
//            '--keyword=dcgettext:2',

            '--keyword=ngettext:1,2',
//            '--keyword=dngettext:2,3',
//            '--keyword=dcngettext:2,3',

            '--keyword=pgettext:1c,2',
//            '--keyword=dpgettext:2c,3',
//            '--keyword=dcpgettext:2c,3',

            '--keyword=npgettext:1c,2,3',
//            '--keyword=dnpgettext:2c,3,4',
//            '--keyword=dcnpgettext:2c,3,4'
        ];

        if ($this->fs->exists($target)) {
            $command[] = '--join-existing';
        }

        $command[] = $source;

        $command = implode(' ', $command);

        exec($command);

        if ($this->fs->exists($target)) {
            $content = $this->fs->get($target);

            // xgettext collects context, make it relative
            $content = Str::replace($this->loader->tmpPath(), '', $content);
            $content = Str::replace($this->loader->appPath(), '', $content);

            $content = $this->compilePotHeader($content);

            $this->fs->put($target, $content);
        }
    }

    /**
     * Get file listing in directory.
     *
     * @param string $resource
     * @param string|string[] $masks
     * @param array $excluding
     * @return Collection
     */
    public function resourceListing(string $resource, $masks, array $excluding = []): Collection
    {
        $files = [];

        if ($this->fs->isFile($resource)) {
            if (in_array('*.' . $this->fs->extension($resource), (array)$masks)) {
                $files[] = $resource;
            }
        } else {

            foreach ((array)$masks as $mask) {
                foreach ($this->fs->glob("{$resource}/{$mask}") as $filename) {
                    $files[] = $filename;
                }
                foreach ($this->fs->glob("{$resource}/*") as $filename) {
                    if ($this->fs->isDirectory($filename)) {
                        $files = array_merge($files, $this->resourceListing($filename, $mask)->toArray());
                    }
                }
            }
        }

        return collect($files)
            ->filter(function ($path) use ($excluding) {
                foreach ($excluding as $exclude) {
                    if (strpos($path, $exclude) === 0) {
                        return false;
                    }
                }
                return true;
            });
    }

    /**
     * Get path to temporary copy of given file.
     *
     * @param string $file
     * @return string
     */
    protected function getTempFile(string $file): string
    {
        $relativePathToFile = Str::replace($this->loader->appPath(), '', $file);
        $tmp = $this->loader->tmpPath() . $relativePathToFile;

        if (!$this->fs->exists($this->fs->dirname($tmp))) {
            $this->fs->makeDirectory($this->fs->dirname($tmp), 0777, true);
        }

        return $tmp;
    }

    /**
     * Compile given blade template into temporary php file.
     *
     * @param string $filename
     * @return string
     */
    protected function makeTemporaryBlade(string $filename): string
    {
        $tmp = $this->getTempFile($filename);
        $dir = $this->fs->dirname($tmp);

        if ($this->fs->exists($tmp)) {
            $this->fs->delete($tmp);
        }

        $compiler = new BladeCompiler($this->fs, $dir);
        $this->fs->put(
            $tmp,
            $compiler->compileString(
                $this->fs->get($filename)
            )
        );

        return $tmp;
    }

    /**
     * Make temporary copy of php file.
     *
     * @param string $filename
     * @return string
     */
    protected function makeTemporaryPhp(string $filename): string
    {
        $tmp = $this->getTempFile($filename);

        if ($this->fs->exists($tmp)) {
            $this->fs->delete($tmp);
        }

        $this->fs->copy($filename, $tmp);

        return $tmp;
    }

    /**
     * Prepare given temporary file to be parsed by xgettext.
     *
     * @param string $filename
     */
    protected function prepareTemporary(string $filename)
    {
        $content = $this->fs->get($filename);

        $content = Str::replace("app('translator')->get", '__', $content);
        $content = Str::replace("Lang::get", ' __', $content);
        $content = preg_replace(
            '~trans_choice\s*?\(\s*?[\'"](.*?)\|(.*?)[\'"]\s*?,(.+?)\)~mi',
            "ngettext('$1', '$2', $3)",
            $content
        );
        $content = preg_replace(
            '~trans_choice\s*?\(\s*?[\'"](.*?)[\'"]\s*?,(.+?)\)~mi',
            "ngettext('$1', '$1', $2)",
            $content
        );

        $this->fs->put($filename, $content);
    }

    /**
     * Update pot file header.
     *
     * @param string $content
     * @return string
     */
    protected function compilePotHeader(string $content): string
    {
        $content = Str::replace('Content-Type: text/plain; charset=CHARSET', 'Content-Type: text/plain; charset=UTF-8', $content);

        return $content;
    }

    /**
     * @return int
     */
    public function getCategory(): int
    {
        return $this->category;
    }

    /**
     * @param int $category
     * @return $this
     */
    public function setCategory(int $category): Extractor
    {
        $this->category = $category;
        return $this;
    }

    protected function categoryName(int $category): string
    {
        switch ($category) {
            case LC_CTYPE:
                return 'LC_CTYPE';
            case LC_NUMERIC:
                return 'LC_NUMERIC';
            case LC_TIME:
                return 'LC_TIME';
            case LC_COLLATE:
                return 'LC_COLLATE';
            case LC_MONETARY:
                return 'LC_MONETARY';
            case LC_MESSAGES:
                return 'LC_MESSAGES';
            case LC_ALL:
                return 'LC_ALL';
            default:
                return 'UNKNOWN';
        }
    }

    /**
     * @return FileLoader
     */
    public function loader(): FileLoader
    {
        return $this->loader;
    }
}