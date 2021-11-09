<?php

namespace Codewiser\Polyglot;

use Codewiser\Polyglot\Contracts\ManipulatorInterface;
use Codewiser\Polyglot\Manipulators\GettextManipulator;
use Codewiser\Polyglot\Manipulators\StringsManipulator;
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

        $this->registerManager();
        $this->registerStringsManipulator();
        $this->registerGettextManipulator();
        $this->registerManipulator();
    }

    protected function registerManager()
    {
        $this->app->singleton(ExtractorsManager::class, function ($app) {
            $manager = new ExtractorsManager($app['translation.loader']);

            $config = $app['config']['polyglot'];

            if (isset($config['domains'])) {
                // Multiple (configurable) extractors.
                foreach ($config['domains'] as $domain) {
                    $extractor = $this->getExtractor(
                        isset($domain['sources']) ? (array)$domain['sources'] : [],
                        isset($domain['exclude']) ? (array)$domain['exclude'] : []
                    );
                    $extractor->setDomain($domain['domain']);
                    $extractor->setCategory($domain['category'] ?? LC_MESSAGES);

                    $manager->addExtractor($extractor);
                }
            } else {
                // Single (default) extractor.
                $manager->addExtractor(
                    $this->getExtractor(
                        isset($config['sources']) ? (array)$config['sources'] : [],
                        isset($config['exclude']) ? (array)$config['exclude'] : []
                    )
                );
            }

            return $manager;
        });
    }

    protected function registerManipulator()
    {
        $this->app->bind(ManipulatorInterface::class, function ($app) {
            $config = $app['config']['polyglot'];
            switch ($config['mode']) {
                case 'translator':
                    return app(GettextManipulator::class);
                case 'collector':
                    return app(StringsManipulator::class);
                default:
                    return null;
            }
        });
    }

    protected function registerStringsManipulator()
    {
        $this->app->bind(StringsManipulator::class, function ($app) {
            $config = $app['config']['polyglot'];

            return new StringsManipulator(
                $config['locales'],
                $app['translation.loader']
            );
        });
    }

    protected function registerGettextManipulator()
    {
        $this->app->bind(GettextManipulator::class, function ($app) {
            $config = $app['config']['polyglot'];

            $manipulator = new GettextManipulator(
                $config['locales'],
                $app['translation.loader'],
                app(StringsManipulator::class)
            );

            $manipulator
                ->msginit($config['executables']['msginit'])
                ->msgmerge($config['executables']['msgmerge'])
                ->msgfmt($config['executables']['msgfmt'])
                ->setPassthroughs($config['passthroughs']);

            return $manipulator;
        });
    }

    protected function getExtractor(array $sources, array $exclude): Extractor
    {
        $config = config('polyglot');

        $collector = new Extractor(
            config('app.name'),
            $sources
        );

        $collector
            ->exclude($exclude)
            ->xgettext($config['executables']['xgettext']);

        return $collector;
    }
}