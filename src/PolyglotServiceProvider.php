<?php

namespace Codewiser\Polyglot;

use Codewiser\Polyglot\Collectors\GettextCollector;
use Codewiser\Polyglot\Collectors\StringsCollector;
use Codewiser\Polyglot\Console\Commands\CompileTranslations;
use Codewiser\Polyglot\Console\Commands\ScanSources;
use Codewiser\Polyglot\Contracts\CollectorInterface;

class PolyglotServiceProvider extends \Illuminate\Translation\TranslationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/polyglot.php' => config_path('polyglot.php'),
        ], 'polyglot');

        if ($this->app->runningInConsole()) {

            if ($collector = app(CollectorInterface::class)) {
                $commands = [ScanSources::class];

                if ($collector instanceof GettextCollector) {
                    $commands[] = CompileTranslations::class;
                }

                $this->commands($commands);
            }

        }
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerLoader();

        $this->mergeConfigFrom(__DIR__ . '/../config/polyglot.php', 'polyglot');

        $this->app->singleton('translator', function ($app) {
            $loader = $app['translation.loader'];

            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale. So, we'll grab the application
            // configuration so we can easily get both of these values from there.
            $locale = $app['config']['app.locale'];

            $config = $app['config']['polyglot'];

            switch ($config['mode']) {
                case 'translator':
                    $trans = new Polyglot($loader, $locale,
                        $config['translator']['domain'],
                        $config['translator']['mo'],
                        $config['translator']['legacy']
                    );
                    break;
                default:
                    $trans = new \Illuminate\Translation\Translator($loader, $locale);
                    break;
            }

            $trans->setFallback($app['config']['app.fallback_locale']);

            return $trans;
        });
        
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
                ->setLegacy($config['translator']['legacy']);

            return $collector;
        });

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
