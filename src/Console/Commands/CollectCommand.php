<?php


namespace Codewiser\Polyglot\Console\Commands;


use Codewiser\Polyglot\Contracts\ManipulatorInterface;
use Codewiser\Polyglot\StringsCollector;
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
        $this->populator()->populate(
            $this->collector()->collect()->getPortableObjectTemplate()
        );

        return 0;
    }

    protected function collector(): StringsCollector
    {
        return app(StringsCollector::class);
    }

    protected function populator(): ManipulatorInterface
    {
        return app(ManipulatorInterface::class);
    }
}