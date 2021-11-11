<?php

namespace Codewiser\Polyglot\Http\Controllers;

use Codewiser\Polyglot\Contracts\ManipulatorInterface;
use Codewiser\Polyglot\Extractor;
use Codewiser\Polyglot\ExtractorsManager;
use Codewiser\Polyglot\Manipulators\GettextManipulator;
use Codewiser\Polyglot\Polyglot;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Lang;

class i18nController extends Controller
{
    public function index()
    {
        $config = config('polyglot');

        $manager = Polyglot::manager();
        $manipulator = Polyglot::manipulator();

        $data = [
            'mode' => $config['mode'],
            'locales' => $config['locales'],
            'ability' => [
                'collect' => false,
                'compile' => false,
            ]
        ];

        if ($manager) {
            $data['ability']['collect'] = true;

            $data['lastCollected'] = $manager->extractors()
                ->max(function (Extractor $extractor) {
                    return file_exists($extractor->getPortableObjectTemplate()) ?
                        filemtime($extractor->getPortableObjectTemplate()) :
                        0;
                });
            $data['lastCollected'] = $data['lastCollected'] ?
                Carbon::createFromTimestamp($data['lastCollected'])->diffForHumans() : 0;

            $data['domains'] = $manager->extractors()
                ->map(function (Extractor $extractor) {
                    return [
                        'domain' => $extractor->getTextDomain(),
                        'sources' => $extractor->getSources(),
                        'exclude' => $extractor->getExclude()
                    ];
                });
        }

        if ($manipulator instanceof GettextManipulator) {

            $data['ability']['compile'] = true;

            $data['lastCompiled'] = $manipulator->outputFiles()
                ->max(function ($filename) {
                    return file_exists($filename) ?
                        filemtime($filename) :
                        0;
                });
            $data['lastCompiled'] = $data['lastCompiled'] ?
                Carbon::createFromTimestamp($data['lastCompiled'])->diffForHumans() : 0;
        }

        /** @var GettextManipulator $gettext */
        $gettext = app(GettextManipulator::class);
        $data['stat'] = $this->progress($gettext);

        $data['lastTranslated'] = $gettext->files()
            ->merge($gettext->getPassthroughsManipulator()->files())
            ->max(function ($filename) {
                return file_exists($filename) ?
                    filemtime($filename) :
                    0;
            });
        $data['lastTranslated'] = $data['lastTranslated'] ?
            Carbon::createFromTimestamp($data['lastTranslated'])->diffForHumans() : 0;

        return response()->json($data);
    }

    protected function progress(GettextManipulator $manipulator): array
    {
        $statistics = $manipulator->all()->statistics();

        $statistics->add(
            $manipulator->getPassthroughsManipulator()->all()
        );

        return $statistics->toArray();
    }

    public function collect()
    {
        Artisan::call('polyglot:collect');
    }

    public function compile()
    {
        Artisan::call('polyglot:compile');
    }
}