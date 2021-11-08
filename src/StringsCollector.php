<?php

namespace Codewiser\Polyglot;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use Sepia\PoParser\Catalog\Entry;
use Sepia\PoParser\Parser;
use Sepia\PoParser\SourceHandler\FileSystem;

/**
 * Collects strings from app source code using xgettext.
 */
class StringsCollector
{
    /**
     * Application name used for .pot headers
     *
     * @var string
     */
    protected string $app_name;

    /**
     * Application base path. Used to convert absolute paths to relative.
     *
     * @var string
     */
    protected string $app_path;

    /**
     * Search strings in file system resources.
     *
     * @var array
     */
    protected array $include;

    /**
     * Exclude file system resources from search.
     *
     * @var array
     */
    protected array $exclude;

    /**
     * xgettext executable.
     *
     * @var string
     */
    protected string $xgettext;

    /**
     * Path to .pot file (collects found strings).
     *
     * @var string
     */
    protected string $pot;

    /**
     * @param string $app_name App name used in .pot header.
     * @param string $app_path App base path used to calculate relative paths.
     * @param array $paths File system files and folders to search strings.
     * @param string $pot Path to output .pot file.
     */
    public function __construct(string $app_name, string $app_path, array $paths, string $pot)
    {
        $this->app_name = $app_name;
        $this->app_path = $app_path;
        $this->include = $paths;
        $this->exclude = [];
        $this->xgettext = 'xgettext';
        $this->pot = $pot;
    }

    /**
     * Set xgettext executable.
     *
     * @param string $executable
     * @return $this
     */
    public function xgettext(string $executable): StringsCollector
    {
        $this->xgettext = $executable;
        return $this;
    }

    /**
     * Exclude some files and folders from search.
     *
     * @param array $exclude
     * @return $this
     */
    public function exclude(array $exclude): StringsCollector
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
        return $this->pot;
    }

    /**
     * Get collected strings from portable object template file.
     *
     * @param string $pot
     * @return EntryCollection|Entry[]
     */
    public function getStrings(string $pot): EntryCollection
    {
        if (!file_exists($pot)) {
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
    public function collect(): StringsCollector
    {
        $this->clear();

        foreach ($this->include as $resource) {
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

        if (file_exists($output)) {
            unlink($output);
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
            unlink($tmp);
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
        if (!file_exists(dirname($target))) {
            mkdir(dirname($target), 0777, true);
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

        if (file_exists($target)) {
            $command[] = '--join-existing';
        }

        $command[] = $source;

        $command = implode(' ', $command);

        exec($command);

        if (file_exists($target)) {
            $content = file_get_contents($target);

            // xgettext collects context, make it relative
            $content = str_replace($this->getTempDirectory(), '', $content);
            $content = str_replace($this->app_path, '', $content);

            $content = $this->compilePotHeader($content);

            file_put_contents($target, $content);
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
        $resource = rtrim($resource, DIRECTORY_SEPARATOR); // remove last slash

        if (is_file($resource)) {
            if (in_array('*.' . pathinfo($resource, PATHINFO_EXTENSION), (array)$masks)) {
                $files[] = $resource;
            }
        } else {

            foreach ((array)$masks as $mask) {
                foreach (glob("{$resource}/{$mask}") as $filename) {
                    $files[] = $filename;
                }
                foreach (glob("{$resource}/*") as $filename) {
                    if (is_dir($filename)) {
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
     * Temporary directory for compiled blades and php files.
     *
     * @return string
     */
    protected function getTempDirectory(): string
    {
        $dir = sys_get_temp_dir() .
            DIRECTORY_SEPARATOR . md5($this->app_name) .
            DIRECTORY_SEPARATOR . 'polyglot';

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir;
    }

    /**
     * Get path to temporary copy of given file.
     *
     * @param string $file
     * @return string
     */
    protected function getTempFile(string $file): string
    {
        $relativePathToFile = str_replace($this->app_path, '', $file);
        $tmp = $this->getTempDirectory() . $relativePathToFile;
        $dir = dirname($tmp);

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
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
        $dir = dirname($tmp);

        if (file_exists($tmp)) {
            unlink($tmp);
        }

        $filesystem = new \Illuminate\Filesystem\Filesystem();
        $compiler = new BladeCompiler($filesystem, $dir);
        file_put_contents($tmp, $compiler->compileString(file_get_contents($filename)));

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

        if (file_exists($tmp)) {
            unlink($tmp);
        }

        copy($filename, $tmp);

        return $tmp;
    }

    /**
     * Prepare given temporary file to be parsed by xgettext.
     *
     * @param string $filename
     */
    protected function prepareTemporary(string $filename)
    {
        $content = file_get_contents($filename);

        $content = str_replace("app('translator')->get", '__', $content);
        $content = str_replace("Lang::get", ' __', $content);
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

        file_put_contents($filename, $content);
    }

    /**
     * Update pot file header.
     *
     * @param string $content
     * @return string
     */
    protected function compilePotHeader(string $content): string
    {
        $content = str_replace('Content-Type: text/plain; charset=CHARSET', 'Content-Type: text/plain; charset=UTF-8', $content);

        return $content;
    }
}