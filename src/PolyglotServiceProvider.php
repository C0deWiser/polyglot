<?php

namespace Codewiser\Polyglot;

use Codewiser\Polyglot\Collectors\GettextCollector;
use Codewiser\Polyglot\Collectors\StringsCollector;
use Codewiser\Polyglot\Console\Commands\CompileCommand;
use Codewiser\Polyglot\Console\Commands\InstallCommand;
use Codewiser\Polyglot\Console\Commands\PublishCommand;
use Codewiser\Polyglot\Console\Commands\CollectCommand;
use Codewiser\Polyglot\Contracts\CollectorInterface;
use Codewiser\Polyglot\Http\Middleware\Authorize;
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
        $this->registerRoutes();
        $this->registerResources();
        $this->defineAssetPublishing();
        $this->offerPublishing();
        $this->registerCommands();
    }

    /**
     * Register the Polyglot routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::group([
            'domain' => config('polyglot.domain', null),
            'prefix' => config('polyglot.path'),
            'middleware' => config('polyglot.middleware', 'web'),
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
     * Register the Polyglot resources.
     *
     * @return void
     */
    protected function registerResources()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'polyglot');
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function offerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../stubs/PolyglotServiceProvider.php' => app_path('Providers/PolyglotServiceProvider.php'),
            ], 'polyglot-provider');

            $this->publishes([
                __DIR__ . '/../config/polyglot.php' => config_path('polyglot.php'),
            ], 'polyglot-config');
        }
    }

    /**
     * Define the asset publishing configuration.
     *
     * @return void
     */
    public function defineAssetPublishing()
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
                PublishCommand::class
            ];

            if ($collector = app(CollectorInterface::class)) {
                $commands[] = CollectCommand::class;

                if ($collector instanceof GettextCollector) {
                    $commands[] = CompileCommand::class;
                }
            }

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

        $this->registerCollector();
        $this->registerStringsCollector();
        $this->registerGettextCollector();

        if (config('polyglot.mode') == 'translator') {

            // Replace Translator service with Polyglot

            $this->registerLoader();

            $this->app->singleton('translator', function ($app) {
                $loader = $app['translation.loader'];
                $locale = $app['config']['app.locale'];
                $config = $app['config']['polyglot'];

                $trans = new Polyglot($loader, $locale,
                    $config['translator']['domain'],
                    $config['translator']['mo'],
                    $config['translator']['passthroughs']
                );

                $trans->setFallback($app['config']['app.fallback_locale']);

                return $trans;
            });

        } else {
            parent::register();
        }
    }

    protected function registerStringsCollector()
    {
        $this->app->bind(StringsCollector::class, function ($app) {
            $config = $app['config']['polyglot'];

            $collector = new StringsCollector(
                base_path(),
                $config['locales'],
                $config['collector']['storage']
            );

            $collector
                ->setIncludes($config['collector']['includes'])
                ->setExcludes($config['collector']['excludes'])
                ->setExecutables($config['executables']);

            return $collector;
        });
    }

    protected function registerGettextCollector()
    {
        $this->app->bind(GettextCollector::class, function ($app) {
            $config = $app['config']['polyglot'];

            $collector = new GettextCollector(
                base_path(),
                $config['locales'],
                $config['translator']['po'],
                $config['translator']['domain'],
                $config['translator']['mo']
            );

            $collector
                ->setIncludes($config['collector']['includes'])
                ->setExcludes($config['collector']['excludes'])
                ->setExecutables($config['executables'])
                ->setPassthroughs($config['translator']['passthroughs']);

            return $collector;
        });
    }

    protected function registerCollector()
    {
        $this->app->bind(CollectorInterface::class, function ($app) {
            $config = $app['config']['polyglot'];

            switch ($config['mode']) {
                case 'collector':
                    return app(StringsCollector::class);
                case 'translator':
                    return app(GettextCollector::class);
                default:
                    return null;
            }
        });
    }
}
