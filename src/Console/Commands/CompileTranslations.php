<?php


namespace Codewiser\Polyglot\Console\Commands;


use Codewiser\Polyglot\Collectors\GettextCollector;
use Codewiser\Polyglot\Contracts\CollectorInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Lang;

class CompileTranslations extends Command
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
    protected function collector(): GettextCollector
    {
        return app(CollectorInterface::class);
    }
}