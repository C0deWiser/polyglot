<?php

namespace Codewiser\Polyglot\Console\Commands;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'polyglot:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all of the Polyglot resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->call('vendor:publish', [
            '--tag' => 'polyglot-assets',
            '--force' => true,
        ]);
    }
}
