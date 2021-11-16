<?php


namespace Codewiser\Polyglot\Console\Commands;


use Codewiser\Polyglot\FileSystem\DirectoryHandler;
use Codewiser\Polyglot\FileSystem\PoFileHandler;
use Codewiser\Polyglot\Xgettext\XgettextCompiler;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

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

        $lang_path = new DirectoryHandler(resource_path('lang'));
        /** @var XgettextCompiler $compiler */
        $compiler = app(XgettextCompiler::class);

        $this->line('Compiling...');

        $lang_path->allFiles()->po()
            ->each(function (PoFileHandler $po) use ($compiler) {
                $mo = Str::replaceLast('.po', '.mo', $po->filename());
                $compiler->setSource($po);
                $compiler->setTarget($mo);
                $compiler->compile();

                $this->info($po->filename());
            });

        return 0;
    }
}