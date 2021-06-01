<?php


namespace Codewiser\Translation\Console\Commands;


use Codewiser\Translation\Contracts\CollectorInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Lang;

class ScanSources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translator:collect {--save}';

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

        $this->table(['msgid'], $collector->toArray());

        return 0;
    }

    /**
     * @return CollectorInterface
     */
    protected function collector()
    {
        return app(CollectorInterface::class);
    }
}