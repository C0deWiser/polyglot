<?php

namespace Codewiser\Polyglot\Xgettext;

use Codewiser\Polyglot\Collections\EntryCollection;
use Codewiser\Polyglot\Collections\FileCollection;
use Codewiser\Polyglot\Contracts\ExtractorContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\FileSystem\Contracts\ResourceContract;
use Codewiser\Polyglot\FileSystem\FileHandler;
use Codewiser\Polyglot\FileSystem\PoFileHandler;
use Codewiser\Polyglot\FileSystem\ResourceHandler;
use Codewiser\Polyglot\Polyglot;
use Codewiser\Polyglot\Traits\AsExtractor;
use Codewiser\Polyglot\Traits\FilesystemSetup;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use Sepia\PoParser\Catalog\Entry;
use Sepia\PoParser\Parser;
use Sepia\PoParser\SourceHandler\FileSystem;

/**
 * Extracts strings from app source code using xgettext.
 */
class XgettextExtractor implements ExtractorContract
{
    use FilesystemSetup, AsExtractor;

    /**
     * Application name used for .pot headers
     *
     * @var string
     */
    protected string $app_name;

    /**
     * xgettext executable.
     *
     * @var string
     */
    protected string $xgettext = 'xgettext';

    /**
     * Default text domain for xgettext.
     *
     * @var string
     */
    protected string $text_domain;

    /**
     * Default category for xgettext.
     *
     * @var int
     */
    protected int $category;

    public function __construct(string $app_name,
                                string $text_domain = 'messages',
                                int    $category = LC_MESSAGES
    )
    {
        $this->app_name = $app_name;
        $this->text_domain = $text_domain;
        $this->category = $category;
    }

    /**
     * Set xgettext executable.
     *
     * @param string $executable
     */
    public function setExecutable(string $executable): void
    {
        $this->xgettext = $executable;
    }

    public function setTextDomain(string $text_domain): void
    {
        $this->text_domain = $text_domain;
    }

    public function getTextDomain(): string
    {
        return $this->text_domain;
    }

    public function getCategory(): int
    {
        return $this->category;
    }

    public function setCategory(int $category): void
    {
        $this->category = $category;
    }

    public function getExtracted(): ?FileHandlerContract
    {
        $filename = $this->getPortableObjectTemplate();

        return $filename->exists() ? $filename : null;
    }

    /**
     * Get the path to .pot file (holds parsed strings).
     *
     * @return PoFileHandler
     */
    public function getPortableObjectTemplate(): PoFileHandler
    {
        return new PoFileHandler(
            $this->temp_path .
            DIRECTORY_SEPARATOR . class_basename($this) .
            DIRECTORY_SEPARATOR . Polyglot::getCategoryName($this->category) .
            DIRECTORY_SEPARATOR . $this->text_domain . '.pot'
        );
    }

    /**
     * Start collecting strings.
     *
     * @return FileHandlerContract
     */
    public function extract(): FileHandlerContract
    {
        $output = $this->getPortableObjectTemplate();
        $output->parent()->ensureDirectoryExists();
        $output->delete();

        foreach ($this->sources as $resource) {
            $this->collectStrings(new ResourceHandler($resource), $output, $this->exclude);
        }

        return $this->getExtracted();
    }

    /**
     * Collect strings from given directory/file to given output file, excluding some resources...
     *
     * @param ResourceContract $resource
     * @param PoFileHandler $output
     * @param array $excluding
     */
    protected function collectStrings(ResourceContract $resource, PoFileHandler $output, array $excluding = [])
    {
        foreach ($this->resourceListing($resource, '*.php', $excluding) as $filename) {

            $filename = new FileHandler($filename);

            if (Str::endsWith($filename, '.blade.php')) {
                $tmp = $this->makeTemporaryBlade($filename);
            } else {
                $tmp = $this->makeTemporaryPhp($filename);
            }
            $this->prepareTemporary($tmp);

            $this->runXGetText('PHP', $tmp, $output);
            $tmp->delete();
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
     * @param PoFileHandler $target
     */
    protected function runXGetText(string $language, string $source, PoFileHandler $target)
    {
        // For keywords
        // See https://www.gnu.org/software/gettext/manual/html_node/Default-Keywords.html

        $command = [
            $this->xgettext,
            '--language=' . $language,
            '--no-wrap',
            '--sort-output',
            '--from-code=UTF-8',
            '--package-name="' . $this->app_name . '"',
            '--output=' . $target->filename(),
//            '--output-dir=' . dirname($target),
            '--add-comments',
            '--keyword', // Disable defaults

            '--keyword=__',
            '--keyword=trans',

            '--keyword=gettext',
            '--keyword=dgettext:2',
            '--keyword=dcgettext:2',

            '--keyword=ngettext:1,2',
            '--keyword=dngettext:2,3',
            '--keyword=dcngettext:2,3',

            '--keyword=pgettext:1c,2',
            '--keyword=dpgettext:2c,3',
            '--keyword=dcpgettext:2c,3',

            '--keyword=npgettext:1c,2,3',
            '--keyword=dnpgettext:2c,3,4',
            '--keyword=dcnpgettext:2c,3,4'
        ];

        if ($target->exists()) {
            $command[] = '--join-existing';
        }

        $command[] = $source;

        $command = implode(' ', $command);

        exec($command);

        try {
            $content = $target->getContent();

            // xgettext collects context, make it relative
            $content = Str::replace($this->temp_path, '', $content);
            $content = Str::replace($this->base_path, '', $content);

            $content = $this->compilePotHeader($content);

            $target->putContent($content);

        } catch (FileNotFoundException $e) {
        }
    }

    /**
     * Get file listing in directory.
     *
     * @param ResourceContract $resource
     * @param string|string[] $masks
     * @param array $excluding
     * @return array|string[]
     */
    public function resourceListing(ResourceContract $resource, $masks, array $excluding = []): array
    {
        $files = [];

        //$resource = rtrim($resource, DIRECTORY_SEPARATOR);

        if ($file = $resource->asFile()) {
            if (in_array('*.' . $file->extension(), (array)$masks)) {
                $files[] = $resource->filename();
            }
        } elseif ($dir = $resource->asDirectory()) {
            foreach ((array)$masks as $mask) {

                foreach ($dir->glob($mask) as $filename) {
                    $files[] = $filename->filename();
                }

                foreach ($dir->glob('*') as $filename) {
                    if ($filename->asDirectory()) {
                        $files = array_merge($files, $this->resourceListing($filename, $mask));
                    }
                }
            }
        }

        return collect($files)
            ->filter(function (string $path) use ($excluding) {
                foreach ($excluding as $exclude) {
                    if (strpos($path, $exclude) === 0) {
                        return false;
                    }
                }
                return true;
            })
            ->toArray();
    }

    /**
     * Compile given blade template into temporary php file.
     *
     * @param FileContract $filename
     * @return FileContract
     */
    protected function makeTemporaryBlade(FileContract $filename): FileContract
    {
        $tmp = $this->temporize($filename);
        $tmp->delete();
        $tmp->parent()->ensureDirectoryExists();

        $compiler = new BladeCompiler($this->filesystem, $tmp->parent());

        try {
            $tmp->putContent($compiler->compileString($filename->getContent()));
        } catch (FileNotFoundException $e) {
        }

        return $tmp;
    }

    /**
     * Make temporary copy of php file.
     *
     * @param FileContract $filename
     * @return FileContract
     */
    protected function makeTemporaryPhp(FileContract $filename): FileContract
    {
        $tmp = $this->temporize($filename);
        $tmp->delete();
        $tmp->parent()->ensureDirectoryExists();
        $filename->copyTo($tmp);

        return $tmp;
    }

    /**
     * Prepare given temporary file to be parsed by xgettext.
     *
     * @param FileContract $filename
     */
    protected function prepareTemporary(FileContract $filename)
    {
        try {
            $content = $filename->getContent();
        } catch (FileNotFoundException $e) {
            return;
        }

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

        $filename->putContent($content);
    }

    /**
     * Update pot file header.
     *
     * @param string $content
     * @return string
     */
    protected function compilePotHeader(string $content): string
    {
        $content = Str::replace(
            'Content-Type: text/plain; charset=CHARSET',
            'Content-Type: text/plain; charset=UTF-8',
            $content
        );

        return $content;
    }

}