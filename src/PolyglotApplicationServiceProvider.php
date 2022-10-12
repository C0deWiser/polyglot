<?php

namespace Codewiser\Polyglot;

use Codewiser\Polyglot\FileSystem\Contracts\FinderContract;
use Codewiser\Polyglot\Contracts\SeparatorContract;
use Codewiser\Polyglot\FileSystem\Finder;
use Codewiser\Polyglot\Producers\ProducerOfJson;
use Codewiser\Polyglot\Producers\ProducerOfPhp;
use Codewiser\Polyglot\Producers\ProducerOfPo;
use Codewiser\Polyglot\Xgettext\JsonCompiler;
use Codewiser\Polyglot\Xgettext\MoCompiler;
use Codewiser\Polyglot\Xgettext\XgettextExtractor;
use Codewiser\Polyglot\Xgettext\XgettextSeparator;
use Illuminate\Filesystem\Filesystem;
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
        $this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'polyglot');
        $this->publishes([
            __DIR__.'/../resources/lang' => base_path('lang/vendor/polyglot'),
        ]);
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

        $this->registerExtractor();
        $this->registerFinder();
        $this->registerCompiler();
    }

    protected function registerFinder()
    {
        $this->app->singleton(FinderContract::class, function ($app) {
            return new Finder(base_path('lang'), new Filesystem());
        });
    }

    protected function getProducer($of, $app)
    {
        $config = $app['config']['polyglot'];

        $producer = null;

        switch ($of) {
            case 'keys':
                $producer = new ProducerOfPhp();
                break;
            case 'strings':
                if ($config['enabled']) {
                    $producer = new ProducerOfPo();
                    $producer->setMsgInitExecutable($config['executables']['msginit']);
                    $producer->setMsgMergeExecutable($config['executables']['msgmerge']);
                } else {
                    $producer = new ProducerOfJson();
                }
                break;
        }

        if ($producer) {
            $producer->setFilesystem(new Filesystem());
            $producer->setStorage(base_path('lang'));
            $producer->setLocales($config['locales']);
        }

        return $producer;
    }

    protected function getSeparator($app): SeparatorContract
    {
        $separator = new XgettextSeparator();
        $separator->setFilesystem(new Filesystem);
        $separator->setBasePath(app_path());
        $separator->setTempPath($this->getTempPath());
        return $separator;
    }

    protected function registerCompiler()
    {
        $this->app->singleton(CompilerManager::class, function ($app) {
            $manager = new CompilerManager();

            $compiler = new MoCompiler();
            $compiler->setFilesystem(new Filesystem);
            $manager->addCompiler('gettext', $compiler);

            $compiler = new JsonCompiler();
            $compiler->setFilesystem(new Filesystem);
            $manager->addCompiler('javascript', $compiler);

            return $manager;
        });
    }

    protected function registerExtractor()
    {
        $this->app->singleton(ExtractorsManager::class, function ($app) {
            $config = $app['config']['polyglot'];

            $manager = new ExtractorsManager();

            foreach ($config['sources'] as $text_domain) {
                $manager->addExtractor($this->getExtractor($config, $text_domain));
            }

            $manager->setSeparator($this->getSeparator($app));
            $manager->setProducersOfKeys($this->getProducer('keys', $app));
            $manager->setProducerOfStrings($this->getProducer('strings', $app));

            return $manager;
        });
    }


    protected function getExtractor(array $polyglot_config, array $text_domain_config): XgettextExtractor
    {
        $extractor = new XgettextExtractor(
            config('app.name'),
            $text_domain_config['text_domain'] ?? 'messages',
            $text_domain_config['category'] ?? LC_MESSAGES
        );

        $extractor->setFilesystem(new Filesystem);
        $extractor->setBasePath(base_path());
        $extractor->setTempPath($this->getTempPath());
        $extractor->setSources((array)$text_domain_config['include']);
        $extractor->setExclude((array)$text_domain_config['exclude'] ?? []);
        $extractor->setExecutable($polyglot_config['executables']['xgettext']);

        if (@$polyglot_config['executables']['npm_xgettext']) {
            $extractor->setNpmExecutable($polyglot_config['executables']['npm_xgettext']);
        }

        return $extractor;
    }

    protected function getTempPath(): string
    {

        return storage_path('temp');
    }
}
