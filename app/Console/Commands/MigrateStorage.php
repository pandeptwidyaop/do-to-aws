<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
        $files = Storage::disk('spaces')->allFiles();
        $this->info("Collecting " . count($files) . " files.");
        $progress = $this->output->createProgressBar(count($files));
        $progress->start();
        foreach ($files as $key => $file) {
            $this->info("$key. Copying $file");
            $this->newLine();
            Storage::disk('s3')->put($file, Storage::disk('spaces')->get($file));
            $progress->advance();
            sleep(1);
        }
        $progress->finish();
    }
}
