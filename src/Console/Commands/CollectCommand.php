<?php


namespace Codewiser\Polyglot\Console\Commands;


use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\FileSystem\Contracts\ResourceContract;
use Codewiser\Polyglot\FileSystem\Finder;
use Codewiser\Polyglot\Polyglot;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CollectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'polyglot:collect 
                            {--L|lang= : The only lang to produce} 
                            {--D|domain= : The only text domain to collect} 
                            {--O|output= : Save collected strings to this dir instead of default} 
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect translation strings';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $manager = Polyglot::extractors();

        if ($text_domain = $this->option('domain')) {
            // Left only one text domain
            $manager->setExtractors([
                $manager->getExtractor($text_domain, LC_MESSAGES)
            ]);
        }

        if ($output = $this->option('output')) {
            $manager->getProducersOfKeys()->setStorage($output);
            $manager->getProducersOfStrings()->setStorage($output);
        }

        foreach ($manager->extractors() as $extractor) {

            $this->newLine();

            $this->line('Sources');
            foreach ($extractor->getSources() as $source) {
                $this->info('          ' . Str::replace(base_path(), '', $source));
            }
            if ($extractor->getExclude()) {
                $this->line('Excluding');
                foreach ($extractor->getExclude() as $exclude) {
                    $this->info('          ' . Str::replace(base_path(), '', $exclude));
                }
            }

            $extracted = $extractor->extract();
            $this->newLine();
            $this->info('Collected ' . Str::replace(base_path(), '', $extracted->filename()));

            $separator = $manager->getSeparator();
            $separator->setSource($extracted);
            $separator->separate();

            $locales = $this->option('lang') ? [$this->option('lang')] : null;

            $producerOfKeys = $manager->getProducersOfKeys();
            $producerOfKeys->setSource($separator->getExtractedKeys());
            $producerOfKeys->produce($locales);

            $producerOfStrings = $manager->getProducersOfStrings();
            $producerOfStrings->setSource($separator->getExtractedStrings());
            $producerOfStrings->produce($locales);

            $this->newLine();

            $producerOfKeys->getPopulated()->merge(
                $producerOfStrings->getPopulated()
            )->each(function (FileHandlerContract $produced) {
                $this->info('Produced  ' . Str::replace(base_path(), '', $produced->filename()));
            });
        }

        $this->info('Clear temp dir');
        $this->clearTemp();

        return 0;
    }

    protected function clearTemp()
    {
        /** @var Finder $temp */
        $temp = app('polyglot_temp');

        $temp->allFiles()->each(function (ResourceContract $file) {
            $file->delete();
        });
    }
}
