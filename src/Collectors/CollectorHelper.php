<?php


namespace Codewiser\Polyglot\Collectors;


use Codewiser\Polyglot\Contracts\CollectorInterface;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use Sepia\PoParser\Catalog\Entry;
use Sepia\PoParser\Parser;
use Sepia\PoParser\SourceHandler\FileSystem;

trait CollectorHelper
{
    /**
     * Application base path.
     *
     * @var string
     */
    protected string $base_path;

    protected string $xgettext = 'xgettext';
    protected string $msginit = 'msginit';
    protected string $msgmerge = 'msgmerge';
    protected string $msgfmt = 'msgfmt';

    /**
     * Translation domain used for naming files.
     *
     * @var string
     */
    protected string $domain;

    /**
     * Source code folders and files.
     *
     * @var array|string[]
     */
    protected array $includes = [];

    /**
     * Exclude resources from scanning.
     *
     * @var array|string[]
     */
    protected array $excludes = [];

    /**
     * Application locales.
     *
     * @var array|string[]
     */
    protected array $locales = [];

    /**
     * Directory with translation files.
     *
     * @var string
     */
    protected string $storage;

    /**
     * Set up gettext executables.
     *
     * @param array $executables
     * @return $this
     */
    public function setExecutables(array $executables)
    {
        if (isset($executables['xgettext']))
            $this->xgettext = $executables['xgettext'];
        if (isset($executables['msginit']))
            $this->msginit = $executables['msginit'];
        if (isset($executables['msgmerge']))
            $this->msgmerge = $executables['msgmerge'];
        if (isset($executables['msgfmt']))
            $this->msgfmt = $executables['msgfmt'];

        return $this;
    }

    /**
     * Include file system resources for scanning process.
     *
     * @param array $resources
     * @return $this
     */
    public function setIncludes(array $resources)
    {
        $this->includes = $resources;
        return $this;
    }

    /**
     * Exclude file system resources from scanning process.
     *
     * @param array $resources
     * @return $this
     */
    public function setExcludes(array $resources)
    {
        $this->excludes = $resources;
        return $this;
    }

    /**
     * Scan sources for the strings.
     *
     * @return CollectorInterface
     */
    public function parse(): CollectorInterface
    {
        $output = $this->getPortableObjectTemplate();

        if (file_exists($output)) {
            unlink($output);
        }

        foreach ($this->includes as $resource) {
            $this->collectStrings($resource, $output, $this->excludes);
        }

        return $this;
    }


    /**
     * Collect strings from given directory/file to given output file, excluding some resources...
     *
     * @param string $resource
     * @param string $output
     * @param array $excluding
     */
    public function collectStrings(string $resource, string $output, array $excluding = [])
    {
        foreach ($this->resourceListing($resource, '*.php', $excluding) as $filename) {
            if (Str::endsWith($filename, '.blade.php')) {
                $tmp = $this->temporaryBlade($filename);
            } else {
                $tmp = $this->temporaryPhp($filename);
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
     * Prepare given temporary file to be parsed by xgettext.
     *
     * @param $file
     */
    protected function prepareTemporary($file)
    {
        $content = file_get_contents($file);

        $content = str_replace("app('translator')->get", '__', $content);
        $content = str_replace("Lang::get", ' __', $content);
        $content = preg_replace(
            '~trans_choice\s*?\(\s*?[\'"](.*?)\|(.*?)[\'"]\s*?,\s*?(\d+).*?\)~mi',
            "ngettext('$1', '$2', $3)",
            $content
        );
        $content = preg_replace(
            '~trans_choice\s*?\(\s*?[\'"](.*?)[\'"]\s*?,\s*?(\d+).*?\)~mi',
            "ngettext('$1', '$1', $2)",
            $content
        );

        file_put_contents($file, $content);
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
     * Make temporary copy of php file.
     *
     * @param $file
     * @return string
     */
    protected function temporaryPhp($file): string
    {
        $tmp = $this->getTempFile($file);

        if (file_exists($tmp)) {
            unlink($tmp);
        }

        copy($file, $tmp);

        return $tmp;
    }

    /**
     * Compile given blade template into temporary php file.
     *
     * @param string $filename
     * @return string
     */
    protected function temporaryBlade(string $filename): string
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
     * Temporary directory for compiled blades and php files.
     *
     * @return string
     */
    protected function getTempDirectory(): string
    {
        $dir = sys_get_temp_dir() .
            DIRECTORY_SEPARATOR . md5(env('APP_NAME')) .
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
        $relativePathToFile = str_replace($this->base_path, '', $file);
        $tmp = $this->getTempDirectory() . $relativePathToFile;
        $dir = dirname($tmp);

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        return $tmp;
    }

    /**
     * @param string $source
     * @return array|Entry[]
     */
    protected function getParsedEntries(string $source): array
    {
        if (!file_exists($source)) {
            return [];
        }

        $parser = new Parser(new FileSystem($source));

        try {
            $content = $parser->parse();
            return $content->getEntries();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Check if given entry is dot.separated.key.
     *
     * @param Entry $entry
     * @return array|null
     */
    protected function isKeyEntry(Entry $entry): ?array
    {
        $msgid = $entry->getMsgId();

        if (preg_match('~^\S*$~', $msgid)
            && (Str::lower($msgid) === $msgid)
            && ($key = explode('.', $msgid))
            && (count($key) > 1)) {
            return $key;
        } else {
            return null;
        }
    }

    /**
     * Get parsed strings.
     *
     * @return array
     */
    public function toArray()
    {
        return collect($this->getParsedEntries($this->getPortableObjectTemplate()))
            ->values()
            ->map(function (Entry $entry) {
                return $entry->getMsgId();
            })
            ->toArray();
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
        $command = [
            $this->xgettext,
            '--language=' . $language,
            '--no-wrap',
            '--sort-output',
            '--from-code=UTF-8',
            '--default-domain=' . $this->domain,
            '--package-name="' . env('APP_NAME') . '"',
            '--output=' . $target,
            '--keyword=__',
            '--keyword=trans',
        ];

        if (file_exists($target)) {
            $command[] = '--join-existing';
        }

        $command[] = $source;

        $command = implode(' ', $command);

        exec($command);

        // xgettext collects context, make it relative
        if (file_exists($target)) {
            $content = file_get_contents($target);
            $content = $this->compilePotHeader($content);
            file_put_contents($target, $content);
        }
    }

    /**
     * Update pot file header.
     *
     * @param $domain
     */
    protected function compilePotHeader(string $content): string
    {
        $content = str_replace($this->getTempDirectory(), '', $content);
        $content = str_replace($this->base_path, '', $content);
        $content = str_replace('Content-Type: text/plain; charset=CHARSET', 'Content-Type: text/plain; charset=UTF-8', $content);
        
        return $content;
    }

    public function getPortableObjectTemplate(): string
    {
        return $this->getTempFile($this->domain . '.pot');
    }

}