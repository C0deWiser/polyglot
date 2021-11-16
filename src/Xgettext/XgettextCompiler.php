<?php

namespace Codewiser\Polyglot\Xgettext;

use Codewiser\Polyglot\FileSystem\PoFileHandler;
use Codewiser\Polyglot\Traits\FilesystemSetup;

class XgettextCompiler
{
    use FilesystemSetup;

    protected PoFileHandler $source;
    protected string $target;
    /**
     * msgfmt executable.
     *
     * @var string
     */
    protected string $msgfmt = 'msgfmt';

    /**
     * @param string $source
     */
    public function setSource(string $source): void
    {
        $this->source = new PoFileHandler($source);
    }

    /**
     * @param string $target
     */
    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    /**
     * Set msgfmt executable.
     *
     * @param string $executable
     */
    public function setExecutable(string $executable): void
    {
        $this->msgfmt = $executable;
    }

    public function compile()
    {
        $this->runMsgFmt($this->source, $this->target);
    }

    protected function runMsgFmt(string $po, string $mo)
    {
        $command = $this->msgfmt . " --use-fuzzy --output-file={$mo} {$po}";
        exec($command);
    }
}