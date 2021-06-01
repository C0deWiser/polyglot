<?php

namespace Codewiser\Translation;

use Codewiser\Translation\Collectors\GettextCollector;
use Codewiser\Translation\Collectors\StringsCollector;
use Codewiser\Translation\Console\Commands\CompileTranslations;
use Codewiser\Translation\Console\Commands\ScanSources;
use Codewiser\Translation\Contracts\CollectorInterface;

class TranslationServiceProvider extends \Illuminate\Translation\TranslationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/translator.php' => config_path('translator.php'),
        ]);

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

        $this->app->singleton('translator', function ($app) {
            $loader = $app['translation.loader'];

            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale. So, we'll grab the application
            // configuration so we can easily get both of these values from there.
            $locale = $app['config']['app.locale'];

            switch ($app['config']['translator.mode']) {
                case 'translator':
                    $trans = new Translator($loader, $locale);
                    $trans->setDomain($app['config']['gettext.domain']);
                    $trans->setCompiled($app['config']['gettext.compile']);
                    break;
                default:
                    $trans = new \Illuminate\Translation\Translator($loader, $locale);
                    break;
            }

            $trans->setFallback($app['config']['app.fallback_locale']);

            return $trans;
        });

        $this->app->singleton(CollectorInterface::class, function ($app) {
            $config = $app['config']['translator'];

            switch ($config['mode']) {
                case 'collector':
                    $collector = new StringsCollector(
                        base_path(),
                        $config['locales'],
                        $config['collector']['storage']
                    );
                    break;
                case 'translator':
                    $collector = new GettextCollector(
                        base_path(),
                        $config['locales'],
                        $config['gettext']['storage'],
                        $config['gettext']['domain'],
                        $config['gettext']['compile']
                    );
                    break;
                default:
                    $collector = null;
                    break;
            }

            if ($collector) {
                $collector
                    ->setIncludes($config['collector']['includes'])
                    ->setExcludes($config['collector']['excludes'])
                    ->setExecutables($config['executables'])
                ;
            }

            return $collector;
        });
    }
}
