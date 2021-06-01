<?php


namespace Codewiser\Translation\Collectors;


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
    protected $base_path;

    /**
     * Path to xgettext executable.
     *
     * @var string
     */
    protected $xgettext = 'xgettext';
    protected $msginit = 'msginit';
    protected $msgmerge = 'msgmerge';
    protected $msgfmt = 'msgfmt';

    /**
     * Source code folders and files.
     *
     * @var array
     */
    protected $includes = [];

    /**
     * Exclude resources from scanning.
     *
     * @var array
     */
    protected $excludes = [];

    /**
     * Application locales.
     *
     * @var array
     */
    protected $locales = [];

    /**
     * Directory with translation files.
     *
     * @var string
     */
    protected $storage;

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
     * @return $this
     */
    public function parse()
    {
        $output = $this->getPortableObjectTemplate();

        if (file_exists($output)) {
            // todo really need to recreate pot file?
            // unlink($output);
        }

        foreach ($this->includes as $resource) {
            $this->collectStrings($resource, $output, $this->excludes);
        }

        return $this;
    }


    /**
     * Collect strings from given directory/file to given output file, excluding some resources...
     *
     * @param $resource
     * @param $output
     * @param array $excluding
     */
    public function collectStrings($resource, $output, array $excluding = [])
    {
        // xgettext misses Lang::get.
        // So we need to create tmp code of file and change its content.

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

        $content = str_replace("app('translator')->get", 'gettext', $content);
        $content = str_replace("Lang::get", ' gettext', $content);

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
    public function resourceListing(string $resource, $masks, array $excluding = [])
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
    protected function temporaryPhp($file)
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
     * @param $filename
     * @return string
     */
    protected function temporaryBlade($filename)
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
    protected function getTempDirectory()
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
     * @param $file
     * @return string
     */
    protected function getTempFile($file)
    {
        $relativePathToFile = str_replace($this->base_path, '', $file);
        $tmp = $this->getTempDirectory() . $relativePathToFile;
        $dir = dirname($tmp);

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        return $tmp;
    }

    protected function getParsedEntries(string $source)
    {
        if (!file_exists($source)) {
            return [];
        }

        $parser = new Parser(new FileSystem($source));

        try {
            $content = $parser->parse();
            return $content->getEntries();
        } catch (Exception $e) {
        }
        return [];

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
     * @param $language
     * @param $source
     * @param $target
     */
    protected function runXGetText(string $language, string $source, string $target)
    {
        $command = [
            $this->xgettext,
            '--language=' . $language,
            '--no-wrap',
            '--sort-output',
            '--from-code=UTF-8',
            '--default-domain=default',
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
            $content = str_replace($this->getTempDirectory(), '', $content);
            $content = str_replace($this->base_path, '', $content);
            file_put_contents($target, $content);
        }
    }

    /**
     * @return string
     */
    public function getPortableObjectTemplate(): string
    {
        return $this->getTempFile('default.pot');
    }

    /**
     * @param string $storage
     * @return $this
     */
    public function setStorage(string $storage)
    {
        $this->storage = $storage;
        return $this;
    }
}