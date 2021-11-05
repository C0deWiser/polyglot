<?php


namespace Codewiser\Polyglot\Console\Commands;


use Codewiser\Polyglot\Contracts\CollectorInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Lang;

class CollectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'polyglot:collect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect translation strings';

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
        $collector = $this->collector();

        $collector->parse();

        $collector->store();

        return 0;
    }

    protected function collector(): CollectorInterface
    {
        return app(CollectorInterface::class);
    }
}