<?php

namespace Codewiser\Polyglot;

use Codewiser\Polyglot\Console\Commands\CompileCommand;
use Codewiser\Polyglot\Console\Commands\InstallCommand;
use Codewiser\Polyglot\Console\Commands\PublishCommand;
use Codewiser\Polyglot\Console\Commands\CollectCommand;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class PolyglotServiceProvider extends \Illuminate\Translation\TranslationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->defineAssetPublishing();
        $this->offerPublishing();
        $this->registerCommands();
    }


    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function offerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../stubs/PolyglotServiceProvider.stub' => app_path('Providers/PolyglotServiceProvider.php'),
            ], 'polyglot-provider');

            $this->publishes([
                __DIR__ . '/../config/polyglot.php' => config_path('polyglot.php'),
            ], 'polyglot-config');

            $this->publishes([
                __DIR__ . '/../resources/lang' => lang_path('vendor/polyglot')
            ]);
        }
    }

    /**
     * Define the asset publishing configuration.
     *
     * @return void
     */
    protected function defineAssetPublishing()
    {
        $this->publishes([
            POLYGLOT_PATH . '/public' => public_path('vendor/polyglot')
        ], ['polyglot-assets', 'laravel-assets']);
    }

    /**
     * Register the package's commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {

            $commands = [
                InstallCommand::class,
                PublishCommand::class,
                CollectCommand::class,
                CompileCommand::class,
            ];

            $this->commands($commands);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if (!defined('POLYGLOT_PATH')) {
            define('POLYGLOT_PATH', realpath(__DIR__ . '/../'));
        }

        $this->mergeConfigFrom(__DIR__ . '/../config/polyglot.php', 'polyglot');

        if (config('polyglot.enabled')) {
            // Replace Translator with Polyglot
            $this->registerPolyglot();
        } else {
            parent::register();
        }
    }

    protected function registerPolyglot()
    {
        $this->registerLoader();

        $this->app->singleton('translator', function ($app) {
            $loader = $app['translation.loader'];
            $locale = $app['config']['app.locale'];
            $config = $app['config']['polyglot'];

            $text_domain = @$config['xgettext'][0]['text_domain'] ?? 'messages';

            $trans = new Polyglot($loader, $locale, $text_domain, (boolean)@$config['log']);

            $trans->setFallback($app['config']['app.fallback_locale']);

            return $trans;
        });
    }

}
