<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class MigrateStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all file from DO Spaces to AWS S3';

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
        if (Cache::has('do_files')) {
            $files = json_decode(Cache::get('do_files'), true);
        } else {
            $files = Storage::disk('spaces')->allFiles();
            Cache::put('do_files', json_encode($files), 600);
        }
        $this->info("Collecting " . count($files) . " files.");
        $progress = $this->output->createProgressBar(count($files));
        $progress->start();
        foreach ($files as $key => $file) {
            if (!Storage::disk('s3')->exists($file)) {
                Storage::disk('s3')->put($file, Storage::disk('spaces')->get($file));
                sleep(1);
            }
            $progress->advance();
        }
        $progress->finish();
    }
}
