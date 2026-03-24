<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishTinyMce extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tinymce:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish TinyMCE assets to the public directory';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $sourcePath = base_path('vendor/tinymce/tinymce');
        $destinationPath = public_path('js/tinymce');

        if (! File::exists($sourcePath)) {
            $this->error('TinyMCE package not found in vendor directory. Please run "composer require tinymce/tinymce".');

            return;
        }

        if (! File::exists(public_path('js'))) {
            File::makeDirectory(public_path('js'), 0755, true);
        }

        File::copyDirectory($sourcePath, $destinationPath);

        $this->info('TinyMCE assets published successfully to public/js/tinymce.');
    }
}
