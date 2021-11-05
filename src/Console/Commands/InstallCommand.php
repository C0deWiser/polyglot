<?php

namespace Codewiser\Polyglot\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'polyglot:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install all of the Polyglot resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->comment('Publishing Polyglot Service Provider...');
        $this->callSilent('vendor:publish', ['--tag' => 'polyglot-provider']);

        $this->comment('Publishing Polyglot Assets...');
        $this->callSilent('vendor:publish', ['--tag' => 'polyglot-assets']);

        $this->comment('Publishing Polyglot Configuration...');
        $this->callSilent('vendor:publish', ['--tag' => 'polyglot-config']);

        $this->registerPolyglotServiceProvider();

        $this->info('Polyglot scaffolding installed successfully.');
    }

    /**
     * Register the Polyglot service provider in the application configuration file.
     *
     * @return void
     */
    protected function registerPolyglotServiceProvider()
    {
        $namespace = Str::replaceLast('\\', '', $this->laravel->getNamespace());

        $appConfig = file_get_contents(config_path('app.php'));

        if (Str::contains($appConfig, $namespace.'\\Providers\\PolyglotServiceProvider::class')) {
            return;
        }

        file_put_contents(config_path('app.php'), str_replace(
            "{$namespace}\\Providers\EventServiceProvider::class,".PHP_EOL,
            "{$namespace}\\Providers\EventServiceProvider::class,".PHP_EOL.
            "        {$namespace}\Providers\PolyglotServiceProvider::class,".PHP_EOL,
            $appConfig
        ));

        file_put_contents(app_path('Providers/PolyglotServiceProvider.php'), str_replace(
            "namespace App\Providers;",
            "namespace {$namespace}\Providers;",
            file_get_contents(app_path('Providers/PolyglotServiceProvider.php'))
        ));
    }
}
