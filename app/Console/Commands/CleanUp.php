<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hana:clean-storage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup linked storage every hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pdfUpload_Location = env('PDF_UPLOAD');
        $pdfProcessed_Location = env('PDF_DOWNLOAD');
        $pdfMerge_Location = env('PDF_MERGE_TEMP');
        $pdfImage_Location = env('PDF_IMG_POOL');
        $pdfPool_Location = env('PDF_POOL');
        $publicUploadTemp = Storage::disk('local')->allFiles($pdfUpload_Location);
        $publicDownloadTemp = Storage::disk('local')->allFiles($pdfUpload_Location);
        $publicMergeTemp = Storage::disk('local')->allFiles($pdfUpload_Location);
        $publicImageTemp = Storage::disk('local')->allFiles($pdfUpload_Location);
        $publicPoolTemp = Storage::disk('local')->allFiles($pdfPool_Location);
        Storage::disk('local')->delete($publicUploadTemp);
        Storage::disk('local')->delete($publicDownloadTemp);
        Storage::disk('local')->delete($publicMergeTemp);
        Storage::disk('local')->delete($publicImageTemp);
        Storage::disk('local')->delete($publicPoolTemp);
    }
}
