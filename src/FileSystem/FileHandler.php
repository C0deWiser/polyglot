<?php

namespace Codewiser\Polyglot\FileSystem;

use Codewiser\Polyglot\FileSystem\Contracts\FileContract;

class FileHandler extends ResourceHandler implements FileContract
{
    public function name(): string
    {
        return $this->filesystem->name($this);
    }

    public function delete(): bool
    {
        return $this->filesystem->delete($this);
    }

    public function copyTo(string $to): bool
    {
        return $this->filesystem->copy($this, $to);
    }

    public function putContent(string $contents, bool $lock = false)
    {
        return $this->filesystem->put($this, $contents, $lock);
    }

    public function getContent(bool $lock = false): string
    {
        return $this->filesystem->get($this, $lock);
    }

    public function toArray()
    {
        return parent::toArray() +
            [
                'dir' => false,
                'file' => true,
                'type' => $this->extension()
            ];
    }

    public function extension(): string
    {
        return $this->filesystem->extension($this);
    }
}