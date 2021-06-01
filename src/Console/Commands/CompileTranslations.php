<?php


namespace Codewiser\Translation\Console\Commands;


use Codewiser\Translation\Collectors\GettextCollector;
use Codewiser\Translation\Contracts\CollectorInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Lang;

class CompileTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translator:compile';

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
    protected function collector()
    {
        return app(CollectorInterface::class);
    }
}