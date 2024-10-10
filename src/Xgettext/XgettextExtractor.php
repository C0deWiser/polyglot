<?php

namespace Codewiser\Polyglot\Xgettext;

use Codewiser\Polyglot\Contracts\ExtractorContract;
use Codewiser\Polyglot\Contracts\PrecompilerContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\FileSystem\Contracts\ResourceContract;
use Codewiser\Polyglot\FileSystem\PoFileHandler;
use Codewiser\Polyglot\FileSystem\ResourceHandler;
use Codewiser\Polyglot\Polyglot;
use Codewiser\Polyglot\Traits\AsExtractor;
use Codewiser\Polyglot\Traits\FilesystemSetup;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

/**
 * Extracts strings from app source code using xgettext.
 */
class XgettextExtractor implements ExtractorContract
{
    use FilesystemSetup, AsExtractor;

    /**
     * xgettext executable.
     *
     * @var string
     */
    protected string $xgettext = 'xgettext';

    protected array $keywords = [];

    protected ?PrecompilerContract $precompiler = null;

    public function __construct(
        protected string $app_name,
        protected string $codeset,
        protected string $text_domain = 'messages',
        protected int $category = LC_MESSAGES,
    ) {
        //
    }

    /**
     * Set xgettext executable.
     *
     * @param  string  $executable
     */
    public function setExecutable(string $executable): void
    {
        $this->xgettext = $executable;
    }

    public function getTextDomain(): string
    {
        return $this->text_domain;
    }

    public function getCategory(): int
    {
        return $this->category;
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
            $this->temp_path.
            DIRECTORY_SEPARATOR.class_basename($this).
            DIRECTORY_SEPARATOR.Polyglot::getCategoryName($this->category).
            DIRECTORY_SEPARATOR.$this->text_domain.'.pot'
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
     * @param  ResourceContract  $resource
     * @param  PoFileHandler  $output
     * @param  array  $excluding
     */
    protected function collectStrings(ResourceContract $resource, PoFileHandler $output, array $excluding = [])
    {
        foreach ($this->resourceListing($resource, ['*.php', '*.js', '*.vue'], $excluding) as $filename) {
            foreach ($this->precompiler->compiled($filename) as $tmp) {
                switch ($tmp->extension()) {
                    case 'php':
                        $this->runXGetText('PHP', $tmp, $output);
                        break;
                    case 'js':
                        $this->runXGetText('JavaScript', $tmp, $output);
                        break;
                }
            }
        }
    }

    /**
     * Run xgettext.
     *
     * @param  string  $language
     * @param  string  $source
     * @param  PoFileHandler  $target
     */
    protected function runXGetText(string $language, string $source, PoFileHandler $target)
    {
        // For keywords
        // See https://www.gnu.org/software/gettext/manual/html_node/Default-Keywords.html

        $command = [
            $this->xgettext,
            '--language='.$language,
            '--no-wrap',
            '--from-code='.$this->codeset,
            '--package-name="'.$this->app_name.'"',
            '--output='.$target->filename(),
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

        foreach ($this->keywords as $keyword) {
            $command[] = '--keyword='.$keyword;
        }

        if ($target->exists()) {
            $command[] = '--join-existing';
        }

        $command[] = $source;

        $command = implode(' ', $command);

        exec($command);

        $this->relativizePaths($target);
    }

    protected function relativizePaths(PoFileHandler $file)
    {
        try {
            $content = $file->getContent();

            // xgettext collects context, make it relative
            $content = Str::replace($this->temp_path, '', $content);
            $content = Str::replace($this->base_path, '', $content);

            $content = $this->compilePotHeader($content);

            $file->putContent($content);
        } catch (FileNotFoundException $e) {
        }
    }

    /**
     * Get file listing in directory.
     *
     * @param  ResourceContract  $resource
     * @param  string|string[]  $masks
     * @param  array  $excluding
     *
     * @return array|string[]
     */
    public function resourceListing(ResourceContract $resource, $masks, array $excluding = []): array
    {
        $files = [];

        //$resource = rtrim($resource, DIRECTORY_SEPARATOR);

        if ($file = $resource->asFile()) {
            if (in_array('*.'.$file->extension(), (array) $masks)) {
                $files[] = $resource->filename();
            }
        } elseif ($dir = $resource->asDirectory()) {
            foreach ((array) $masks as $mask) {
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
                    if (str_starts_with($path, $exclude)) {
                        return false;
                    }
                }
                return true;
            })
            ->toArray();
    }

    /**
     * Update pot file header.
     *
     * @param  string  $content
     *
     * @return string
     */
    protected function compilePotHeader(string $content): string
    {
        return Str::replace(
            'Content-Type: text/plain; charset=CHARSET',
            'Content-Type: text/plain; charset='.$this->codeset,
            $content
        );
    }

    /**
     * @param  PrecompilerContract|null  $precompiler
     */
    public function setPrecompiler(?PrecompilerContract $precompiler): void
    {
        $this->precompiler = $precompiler;
    }

    /**
     * @param  array  $keywords
     */
    public function setKeywords(array $keywords): void
    {
        $this->keywords = $keywords;
    }

}
