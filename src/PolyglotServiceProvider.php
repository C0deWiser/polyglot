<?php

namespace Codewiser\Polyglot;

use Codewiser\Polyglot\Console\Commands\CompileCommand;
use Codewiser\Polyglot\Console\Commands\InstallCommand;
use Codewiser\Polyglot\Console\Commands\PublishCommand;
use Codewiser\Polyglot\Console\Commands\CollectCommand;

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
                __DIR__ . '/../resources/lang' => resource_path('lang/vendor/polyglot'),
            ], 'polyglot-translations');
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
            POLYGLOT_PATH . '/public' => public_path('vendor/polyglot'),
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

            // Default text_domain for gettext
            $text_domain = 'messages';

            if (isset($config['xgettext']) && $config['xgettext']) {
                $text_domain = @$config['xgettext'][0]['text_domain'] ?? 'messages';
            }

            $trans = new Polyglot($loader, $locale);

            $trans->setFallback($app['config']['app.fallback_locale']);

            // To look for .mo files
            $trans->setTextDomain($text_domain);

            return $trans;
        });
    }

}
