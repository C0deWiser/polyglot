<?php

namespace Codewiser\Polyglot;

use Codewiser\Polyglot\Contracts\CompilerContract;
use Illuminate\Support\Collection;

class CompilerManager
{
    protected array $compilers = [];

    public function addCompiler(string $name, CompilerContract $compiler)
    {
        $this->compilers[$name] = $compiler;
    }

    public function getCompiler(string $name): ?CompilerContract
    {
        return $this->compilers[$name] ?? null;
    }

    /**
     * @return Collection|CompilerContract[]
     */
    public function compilers(): Collection
    {
        return collect($this->compilers);
    }
}