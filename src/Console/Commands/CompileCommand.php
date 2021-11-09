<?php


namespace Codewiser\Polyglot\Console\Commands;


use Codewiser\Polyglot\Manipulators\GettextManipulator;
use Illuminate\Console\Command;

class CompileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'polyglot:compile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compile translation strings';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $this->manipulator()->compile();

        return 0;
    }

    protected function manipulator(): GettextManipulator
    {
        return app(GettextManipulator::class);
    }
}