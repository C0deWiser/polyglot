<?php

namespace Codewiser\Polyglot;

use Codewiser\Polyglot\Collectors\GettextCollector;
use Codewiser\Polyglot\Collectors\StringsCollector;
use Codewiser\Polyglot\Contracts\CollectorInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PolyglotApplicationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->gate();

        // Loading resources in deferrable provider will not work.
        // So we load resources in here.
        $this->registerRoutes();
        $this->registerResources();
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
     * Register the Polyglot gate.
     *
     * This gate determines who can access Polyglot in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewPolyglot', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/polyglot.php', 'polyglot');

        $this->registerStringsCollector();
        $this->registerGettextCollector();
        $this->registerCollector();
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