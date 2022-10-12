<?php


namespace Codewiser\Polyglot\Console\Commands;


use Codewiser\Polyglot\FileSystem\DirectoryHandler;
use Codewiser\Polyglot\FileSystem\JsonFileHandler;
use Codewiser\Polyglot\FileSystem\PhpFileHandler;
use Codewiser\Polyglot\FileSystem\PoFileHandler;
use Codewiser\Polyglot\Polyglot;
use Codewiser\Polyglot\Xgettext\MoCompiler;
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
    protected $signature = 'polyglot:compile 
                            {--G|gettext : compile only .mo files for a backend} 
                            {--J|javascript : compile only .json files for a frontend}';

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
        $lang_path = new DirectoryHandler(base_path('lang'));

        if ($this->options()['gettext']) {
            $compiler = Polyglot::compilers()->getCompiler('gettext');

            if ($compiler) {
                $this->line('Compiling .mo files...');

                $lang_path->allFiles()->po()
                    ->each(function (PoFileHandler $source) use ($compiler) {

                        // resources/lang/en/LC_MESSAGES/messages.po
                        // resources/lang/en/LC_MESSAGES/messages.mo

                        $target = Str::replaceLast('.po', '.mo', $source->filename());
                        $compiler->setSource($source);
                        $compiler->setTarget($target);
                        $compiler->compile();

                        $this->report($source, $target);
                    });
            }
        }
        if ($this->options()['javascript']) {
            $compiler = Polyglot::compilers()->getCompiler('javascript');
            if ($compiler) {
                $this->line('Compiling .json files...');

                $lang_path->allFiles()->json()
                    ->each(function (JsonFileHandler $source) use ($compiler) {

                        // resources/lang/en.json
                        // storage/lang/en.json

                        $target = Str::replaceFirst(base_path('lang'), storage_path('lang'), $source);

                        $compiler->setSource($source);
                        $compiler->setTarget($target);
                        $compiler->compile();

                        $this->report($source, $target);
                    });

                $lang_path->allFiles()->php()
                    ->each(function (PhpFileHandler $source) use ($compiler) {

                        // resources/lang/en/group.php
                        // storage/lang/en/group.json

                        // resources/lang/vendor/package/en/group.php
                        // storage/lang/vendor/package/en/group.json

                        $target = Str::replaceFirst(base_path('lang'), storage_path('lang'), $source);
                        $target = Str::replaceLast('.php', '.json', $target);

                        $compiler->setSource($source);
                        $compiler->setTarget($target);
                        $compiler->compile();

                        $this->report($source, $target);
                    });

                $lang_path->allFiles()->po()
                    ->each(function (PoFileHandler $source) use ($compiler) {
                        // e.g. storage/lang/en/LC_MESSAGES/messages.json
                        $locale = $source->parent()->parent()->basename();
                        $category = $source->parent()->basename();
                        $target = storage_path('lang') .
                            DIRECTORY_SEPARATOR . $locale .
                            DIRECTORY_SEPARATOR . $category .
                            DIRECTORY_SEPARATOR . $source->name() . '.json';

                        $compiler->setSource($source);
                        $compiler->setTarget($target);
                        $compiler->compile();

                        $this->report($source, $target);
                    });
            }
        }

        return 0;
    }

    protected function report(string $source, string $target)
    {
        $source = Str::replace(base_path(), '', $source);
        $target = Str::replace(base_path(), '', $target);

        $this->info($source . ' > ' . $target);
    }

    public function options()
    {
        $options = parent::options();
        if (!$options['gettext'] && !$options['javascript']) {
            // enable both by default
            $options['gettext'] = true;
            $options['javascript'] = true;
        }
        return $options;
    }
}
