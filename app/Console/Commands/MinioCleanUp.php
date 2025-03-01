<?php

namespace App\Console\Commands;

use App\Models\appLogModel;
use App\Models\compressModel;
use App\Models\cnvModel;
use App\Models\deleteModel;
use App\Models\htmlModel;
use App\Models\mergeModel;
use App\Models\splitModel;
use App\Models\watermarkModel;
use App\Models\fileModel;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MinioCleanUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hana:minio-cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup Minio Storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Carbon timezone
        date_default_timezone_set('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');
        $startProc = $now->format('Y-m-d H:i:s');

        $pdfUpload_Location = env('PDF_UPLOAD');
        $pdfProcessed_Location = env('PDF_DOWNLOAD');
        $pdfMerge_Location = env('PDF_MERGE_TEMP');
        $pdfImage_Location = env('PDF_IMG_POOL');
        $pdfPool_Location = env('PDF_POOL');

        $minioFiles = fileModel::where('isDeleted', '=', false)
                                    ->where('created_at', '<=', Carbon::now()->subHour())
                                    ->exists();
        if ($minioFiles) {
            $fileModel = fileModel::where('isDeleted', '=', false)
                                    ->where('created_at', '<=', Carbon::now()->subHour())
                                    ->get()
                                    ->pluck('fileId');
            foreach ($fileModel as $file) {
                $fileName = fileModel::where('fileId', '=', $file)
                                        ->get()
                                        ->first()
                                        ->fileName;
                $fileProcId = fileModel::where('fileId', '=', $file)
                                        ->get()
                                        ->first()
                                        ->processId;
                appLogModel::where('processId', '=', $fileProcId)
                            ->update([
                                'errReason' => null,
                                'errStatus' => null
                            ]);
                if (Storage::disk('minio')->exists($pdfUpload_Location.'/'.$fileName)) {
                    Storage::disk('minio')->delete($pdfUpload_Location.'/'.$fileName);
                    $this->fileValidate($file, true, $startProc);
                } else {
                    $this->fileValidate($file, true, $startProc);
                }
                if (Storage::disk('minio')->exists($pdfMerge_Location.'/'.$fileName)) {
                    Storage::disk('minio')->delete($pdfMerge_Location.'/'.$fileName);
                    $this->fileValidate($file, true, $startProc);
                } else {
                    $this->fileValidate($file, true, $startProc);
                }
                if (Storage::disk('minio')->exists($pdfImage_Location.'/'.$fileName)) {
                    Storage::disk('minio')->delete($pdfImage_Location.'/'.$fileName);
                    $this->fileValidate($file, true, $startProc);
                } else {
                    $this->fileValidate($file, true, $startProc);
                }
                if (Storage::disk('minio')->exists($pdfPool_Location.'/'.$fileName)) {
                    Storage::disk('minio')->delete($pdfPool_Location.'/'.$fileName);
                    $this->fileValidate($file, true, $startProc);
                } else {
                    $this->fileValidate($file, true, $startProc);
                }
                if (Storage::disk('minio')->exists($pdfProcessed_Location.'/'.$fileName)) {
                    Storage::disk('minio')->delete($pdfProcessed_Location.'/'.$fileName);
                    $this->fileValidate($file, false, $startProc);
                } else {
                    $this->fileValidate($file, false, $startProc);
                }
            }
        } else {
            $fileModel = fileModel::where('isDeleted','=',true)->get()->pluck('fileId');
            foreach ($fileModel as $file) {
                $this->fileValidate($file, false, $startProc);
            }
        }
    }

    private function fileValidate($file, $onlyFile, $startProc) {
        if ($onlyFile) {
            fileModel::where('fileId', '=', $file)
                        ->update([
                            'isDeleted' => true,
                            'deletedAt' => $startProc
                        ]);
        } else {
            fileModel::where('fileId', '=', $file)
                        ->update([
                            'isDeleted' => true,
                            'deletedAt' => $startProc
                        ]);
            if (compressModel::where('fileId','=',$file)->exists()) {
                $isDeletedModel = compressModel::where('fileId', '=', $file)->get()->first()->isDeleted;
                $isDeletedFile = fileModel::where('fileId', '=', $file)->get()->first()->isDeleted;

                if ($isDeletedFile !== $isDeletedModel) {
                    compressModel::where('fileId', '=', $file)
                                    ->update([
                                        'isDeleted' => $isDeletedFile,
                                        'deletedAt' => $startProc
                                    ]);
                }
            } else if (cnvModel::where('fileId','=',$file)->exists()) {
                $isDeletedModel = cnvModel::where('fileId', '=', $file)->get()->first()->isDeleted;
                $isDeletedFile = fileModel::where('fileId', '=', $file)->get()->first()->isDeleted;

                if ($isDeletedFile !== $isDeletedModel) {
                    cnvModel::where('fileId', '=', $file)
                                ->update([
                                    'isDeleted' => $isDeletedFile,
                                    'deletedAt' => $startProc
                                ]);
                }
            } else if (htmlModel::where('fileId','=',$file)->exists()) {
                $isDeletedModel = htmlModel::where('fileId', '=', $file)->get()->first()->isDeleted;
                $isDeletedFile = fileModel::where('fileId', '=', $file)->get()->first()->isDeleted;

                if ($isDeletedFile !== $isDeletedModel) {
                    htmlModel::where('fileId', '=', $file)
                                ->update([
                                    'isDeleted' => $isDeletedFile,
                                    'deletedAt' => $startProc
                                ]);
                }
            } else if (mergeModel::where('fileId','=',$file)->exists()) {
                $isDeletedModel = mergeModel::where('fileId', '=', $file)->get()->first()->isDeleted;
                $isDeletedFile = fileModel::where('fileId', '=', $file)->get()->first()->isDeleted;

                if ($isDeletedFile !== $isDeletedModel) {
                    mergeModel::where('fileId', '=', $file)
                                    ->update([
                                        'isDeleted' => $isDeletedFile,
                                        'deletedAt' => $startProc
                                    ]);
                }
            } else if (splitModel::where('fileId','=',$file)->exists()) {
                $isDeletedModel = splitModel::where('fileId', '=', $file)->get()->first()->isDeleted;
                $isDeletedFile = fileModel::where('fileId', '=', $file)->get()->first()->isDeleted;

                if ($isDeletedFile !== $isDeletedModel) {
                    splitModel::where('fileId', '=', $file)
                                    ->update([
                                        'isDeleted' => $isDeletedFile,
                                        'deletedAt' => $startProc
                                    ]);
                }
            } else if (watermarkModel::where('fileId','=',$file)->exists()) {
                $isDeletedModel = watermarkModel::where('fileId', '=', $file)->get()->first()->isDeleted;
                $isDeletedFile = fileModel::where('fileId', '=', $file)->get()->first()->isDeleted;

                log::info($isDeletedFile.' '.$isDeletedModel);
                if ($isDeletedFile !== $isDeletedModel) {
                    log::info("Updating with fileId:".$file);
                    watermarkModel::where('fileId', '=', $file)
                                    ->update([
                                        'isDeleted' => $isDeletedFile,
                                        'deletedAt' => $startProc
                                    ]);
                }
            }
        }
    }
}
