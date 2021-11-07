<?php

namespace Codewiser\Polyglot;

use Codewiser\Polyglot\Contracts\PopulatorInterface;
use Codewiser\Polyglot\GettextPopulator;
use Codewiser\Polyglot\StringsPopulator;
use Codewiser\Polyglot\StringsCollector;
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

        $this->registerCollector();
        $this->registerStringsPopulator();
        $this->registerGettextPopulator();
        $this->registerPopulator();
    }

    protected function registerPopulator()
    {
        $this->app->bind(PopulatorInterface::class, function($app) {
            $config = $app['config']['polyglot'];
            switch ($config['mode']) {
                case 'translator':
                    return app(GettextPopulator::class);
                case 'collector':
                    return app(StringsPopulator::class);
                default:
                    return null;
            }
        });
    }

    protected function registerStringsPopulator()
    {
        $this->app->bind(StringsPopulator::class, function ($app) {
            $config = $app['config']['polyglot'];

            return new StringsPopulator(
                $config['locales'],
                $config['collector']['storage'],
                app(StringsCollector::class)
            );
        });
    }

    protected function registerGettextPopulator()
    {
        $this->app->bind(GettextPopulator::class, function ($app) {
            $config = $app['config']['polyglot'];

            $populator = new GettextPopulator(
                $config['locales'],
                $config['translator']['po'],
                $config['translator']['mo'],
                $config['translator']['domain'],
                app(StringsPopulator::class)
            );

            $populator
                ->msginit($config['executables']['msginit'])
                ->msgmerge($config['executables']['msgmerge'])
                ->msgfmt($config['executables']['msgfmt'])
                ->setPassthroughs($config['translator']['passthroughs']);

            return $populator;
        });
    }

    protected function registerCollector()
    {
        $this->app->bind(StringsCollector::class, function ($app) {
            $config = $app['config']['polyglot'];

            $collector = new StringsCollector(
                config('app.name'),
                base_path(),
                $config['collector']['includes'],
                $config['translator']['po'] .
                DIRECTORY_SEPARATOR . $config['translator']['domain'] . '.pot'
            );

            $collector
                ->exclude($config['collector']['excludes'])
                ->xgettext($config['executables']['xgettext']);

            return $collector;
        });
    }
}