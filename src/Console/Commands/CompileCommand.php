<?php


namespace Codewiser\Polyglot\Console\Commands;


use Codewiser\Polyglot\Collectors\GettextCollector;
use Codewiser\Polyglot\Contracts\CollectorInterface;
use Codewiser\Polyglot\Polyglot;
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
        $this->collector()->compile();

        return 0;
    }

    /**
     * @return GettextCollector
     */
    protected function collector(): CollectorInterface
    {
        return Polyglot::collector();
    }
}