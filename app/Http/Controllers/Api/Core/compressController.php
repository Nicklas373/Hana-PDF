<?php

namespace App\Http\Controllers\Api\Core;

use App\Helpers\AppHelper;
use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\appLogModel;
use App\Models\compressModel;
use App\Models\fileModel;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Ilovepdf\Ilovepdf;

class compressController extends Controller
{
 	public function compress(Request $request) {
		$validator = Validator::make($request->all(),[
            'batch' => ['required', 'in:true,false'],
            'compMethod' => ['required', 'in:low,recommended,extreme'],
            'file' => [
                'required',
                'array'
            ],
            'file.*' => [
                'required',
                'string',
                'regex:/\.pdf/i'
            ]
		]);

        // Carbon timezone
        date_default_timezone_set('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');
        $startProc = $now->format('Y-m-d H:i:s');

        // Generate Uni UUID
        $uuid = AppHelper::Instance()->generateUniqueUuid(compressModel::class, 'processId');
        $batchId = AppHelper::Instance()->generateUniqueUuid(compressModel::class, 'groupId');
        $fileUuid = AppHelper::Instance()->generateUniqueUuid(fileModel::class, 'processId');

		if ($validator->fails()) {
            appLogModel::create([
                'processId' => $uuid,
                'groupId' => $batchId,
                'errReason' => $validator->messages()->first(),
                'errStatus' => 'Validation Failed!'
            ]);
            NotificationHelper::Instance()->sendErrNotify(
                null,
                null,
                $uuid,
                'FAIL',
                'compress',
                'Validation failed',
                $validator->messages()->first()
            );
            return $this->returnDataMessage(
                400,
                'Validation failed',
                null,
                null,
                null,
                $batchId,
                $validator->messages()->first()
            );
		} else {
            if ($request->has('file')) {
                $files = $request->post('file');
                $compMethod = $request->post('compMethod');
                $batch = $request->post('batch');
                $pdfEncKey = bin2hex(random_bytes(16));
                $pdfUpload_Location = env('PDF_UPLOAD');
                $pdfProcessed_Location = env('PDF_DOWNLOAD');
                $pdfPool_Location = env('PDF_POOL');
                $loopCount = count($files);
                $altPoolFiles = array();
                $poolFiles = array();
                $procFile = 0;
                if ($batch == "true") {
                    $batchValue = true;
                } else {
                    $batchValue = false;
                }
                if ($loopCount > 1) {
                    $pdfDownload_Location = $pdfPool_Location;
                } else {
                    $pdfDownload_Location = $pdfProcessed_Location;
                }
                $randomizePdfFileName = 'pdfCompress_'.substr(bin2hex(random_bytes(4)), 0, 8);
                foreach ($files as $file) {
                    $currentFileName = basename($file);
                    $trimPhase1 = str_replace(' ', '_', $currentFileName);
                    $newFileNameWithoutExtension = str_replace('.', '_', $trimPhase1);
                    if (Storage::disk('minio')->exists($pdfUpload_Location.'/'.$trimPhase1)) {
                        array_push($altPoolFiles, $newFileNameWithoutExtension);
                    } else {
                        $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                        $duration = $end->diff($startProc);
                        appLogModel::create([
                            'processId' => $uuid,
                            'groupId' => $batchId,
                            'errReason' => null,
                            'errStatus' => $currentFileName.' could not be found in the object storage'
                        ]);
                        NotificationHelper::Instance()->sendErrNotify(
                            $currentFileName,
                            null,
                            $batchId,
                            'FAIL',
                            'compress',
                            $currentFileName.' could not be found in the object storage',
                            null
                        );
                        return $this->returnDataMessage(
                            400,
                            'PDF Compress failed !',
                            null,
                            null,
                            null,
                            $batchId,
                            $currentFileName.' could not be found in the object storage'
                        );
                    }
                }
                if ($loopCount == count($altPoolFiles)) {
                    try {
                        $ilovepdf = new Ilovepdf(env('ILOVEPDF_PUBLIC_KEY'),env('ILOVEPDF_SECRET_KEY'));
                        $ilovepdfTask = $ilovepdf->newTask('compress');
                        $ilovepdfTask->setFileEncryption($pdfEncKey);
                        $ilovepdfTask->setEncryptKey($pdfEncKey);
                        $ilovepdfTask->setEncryption(true);
                        foreach ($files as $file) {
                            $currentFileName = basename($file);
                            $currentFileNameExtension = pathinfo($currentFileName, PATHINFO_EXTENSION);
                            $trimPhase1 = str_replace(' ', '_', $currentFileName);
                            $trimPhase2 = str_replace('.', '_', $trimPhase1);
                            $newFileNameWithoutExtension = str_replace('_'.$currentFileNameExtension, '', $trimPhase2);
                            array_push($poolFiles, $newFileNameWithoutExtension);
                            $fileId = fileModel::where('fileName', '=', $trimPhase1)
                                                ->where('isDeleted', '=', false)
                                                ->first()
                                                ->fileId;
                            if ($batchValue) {
                                $newFileName = $randomizePdfFileName.'.zip';
                            } else {
                                $newFileName = $currentFileName;
                            }
                            $fileSize = Storage::disk('minio')->size($pdfUpload_Location.'/'.$trimPhase1);
                            $newFileSize = AppHelper::instance()->convert($fileSize, "MB");
                            $procUuid = AppHelper::Instance()->generateUniqueUuid(compressModel::class, 'processId');
                            $minioUpload = Storage::disk('minio')->get($pdfUpload_Location.'/'.$currentFileName);
                            Storage::disk('local')->put(
                                $pdfUpload_Location.'/'.$currentFileName,
                                $minioUpload
                            );
                            $newFilePath = Storage::disk('local')->path($pdfUpload_Location.'/'.$currentFileName);
                            $ilovepdfTask->setFileEncryption($pdfEncKey);
                            $ilovepdfTask->setEncryptKey($pdfEncKey);
                            $ilovepdfTask->setEncryption(true);
                            $pdfFile = $ilovepdfTask->addile($newFilePath);
                            Storage::disk('local')->delete($pdfUpload_Location.'/'.$currentFileName);
                            $pdfFile->setPassword($pdfEncKey);
                            $ilovepdfTask->setCompressionLevel($compMethod);
                            appLogModel::create([
                                'processId' => $procUuid,
                                'groupId' => $batchId,
                                'errReason' => null,
                                'errStatus' => null
                            ]);
                            compressModel::create([
                                'fileName' => $currentFileName,
                                'fileSize' => $newFileSize,
                                'compFileSize' => null,
                                'compMethod' => $compMethod,
                                'result' => false,
                                'isBatch' => $batchValue,
                                'isDeleted' => false,
                                'batchName' => $newFileName,
                                'fileId' => $fileId,
                                'groupId' => $batchId,
                                'processId' => $procUuid,
                                'deletedAt' => null,
                                'procStartAt' => $startProc,
                                'procDuration' => null
                            ]);
                        }
                        if ($batchValue) {
                            $ilovepdfTask->setPackagedFilename($randomizePdfFileName);
                        } else {
                            $ilovepdfTask->setOutputFileName($newFileNameWithoutExtension);
                        }
                        $ilovepdfTask->execute();
                        $ilovepdfTask->download(Storage::disk('local')->path($pdfDownload_Location));
                        $ilovepdfTask->delete();
                    } catch (\Exception $e) {
                        $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                        $duration = $end->diff($startProc);
                        appLogModel::where('groupId', '=', $batchId)
                            ->update([
                                'errReason' =>  $e->getMessage(),
                                'errStatus' => 'Failed to download file from iLovePDF API !'
                            ]);
                        compressModel::where('groupId', '=', $batchId)
                            ->update([
                                'result' => false,
                                'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                'procDuration' =>  $duration->s.' seconds'
                            ]);
                        NotificationHelper::Instance()->sendErrNotify(
                            $currentFileName,
                            $fileSize,
                            $batchId,
                            'FAIL',
                            'compress',
                            'iLovePDF API Error !, Catch on Exception',
                            $e->getMessage()
                        );
                        return $this->returnDataMessage(
                            400,
                            'PDF Compression failed !',
                            null,
                            $e->getMessage(),
                            null,
                            $batchId,
                            'iLovePDF API Error !, Catch on Exception'
                        );
                    }
                    $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                    $duration = $end->diff($startProc);
                    if ($batchValue) {
                        if (Storage::disk('local')->exists($pdfDownload_Location.'/'.$randomizePdfFileName.'.zip')) {
                            Storage::disk('minio')->put(
                                $pdfDownload_Location.'/'.$randomizePdfFileName.'.zip',
                                Storage::disk('local')->get($pdfDownload_Location.'/'.$randomizePdfFileName.'.zip')
                            );
                            $compFileSize = Storage::disk('minio')->size($pdfDownload_Location.'/'.$randomizePdfFileName.'.zip');
                            $newCompFileSize = AppHelper::instance()->convert($compFileSize, "MB");
                            Storage::disk('local')->delete($pdfDownload_Location.'/'.$randomizePdfFileName.'.zip');
                            AppHelper::instance()->fileModelHelper($randomizePdfFileName.'.zip', $newCompFileSize, $fileUuid, $batchId, false, null);
                            $fileId = fileModel::where('fileName', '=', $randomizePdfFileName.'.zip')
                                                    ->where('isDeleted', '=', false)
                                                    ->where('processId', '=', $fileUuid)
                                                    ->first()
                                                    ->fileId;
                            compressModel::where('groupId', '=', $batchId)
                                ->update([
                                    'compFileSize' => $newCompFileSize,
                                    'result' => true,
                                    'fileId' => $fileId,
                                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                    'procDuration' => $duration->s.' seconds'
                                ]);
                            return $this->returnCoreMessage(
                                200,
                                'OK',
                                $randomizePdfFileName.'.zip',
                                Storage::disk('minio')->temporaryUrl(
                                    $pdfDownload_Location.'/'.$randomizePdfFileName.'.zip',
                                    now()->addMinutes(5)
                                ),
                                'compress',
                                $batchId,
                                $newFileSize,
                                $newCompFileSize,
                                $compMethod,
                                null
                            );
                        } else {
                            $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                            $duration = $end->diff($startProc);
                            appLogModel::where('groupId', '=', $batchId)
                                ->update([
                                    'errReason' => 'Error while processing file: '.$randomizePdfFileName.'.zip',
                                    'errStatus' => 'Failed to download file from iLovePDF API !'
                                ]);
                            compressModel::where('groupId', '=', $batchId)
                                ->update([
                                    'result' => false,
                                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                    'procDuration' =>  $duration->s.' seconds'
                                ]);
                            NotificationHelper::Instance()->sendErrNotify(
                                $file,
                                $newFileSize,
                                $batchId,
                                'FAIL',
                                'compress',
                                'Failed to download file from iLovePDF API !',
                                'Error while processing file: '.$randomizePdfFileName.'.zip'
                            );
                            return $this->returnDataMessage(
                                400,
                                'PDF Compression failed !',
                                null,
                                null,
                                null,
                                $batchId,
                                'Failed to download file from iLovePDF API !'
                            );
                        }
                    } else {
                        foreach ($poolFiles as $file) {
                            if (Storage::disk('local')->exists($pdfDownload_Location.'/'.$file.'.pdf')) {
                                Storage::disk('minio')->put(
                                    $pdfDownload_Location.'/'.$file.'.pdf',
                                    Storage::disk('local')->get($pdfDownload_Location.'/'.$file.'.pdf')
                                );
                                $compFileSize = Storage::disk('minio')->size($pdfDownload_Location.'/'.$file.'.pdf');
                                $newCompFileSize = AppHelper::instance()->convert($compFileSize, "MB");
                                Storage::disk('local')->delete($pdfDownload_Location.'/'.$randomizePdfFileName.'.zip');
                                AppHelper::instance()->fileModelHelper($file.'.pdf', $newCompFileSize, $fileUuid, $batchId, false, null);
                                $fileId = fileModel::where('fileName', '=', $file.'.pdf')
                                                    ->where('isDeleted', '=', false)
                                                    ->where('processId', '=', $fileUuid)
                                                    ->first()
                                                    ->fileId;
                                compressModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'compFileSize' => $newCompFileSize,
                                        'result' => true,
                                        'fileId' => $fileId,
                                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                        'procDuration' => $duration->s.' seconds'
                                    ]);
                                return $this->returnCoreMessage(
                                    200,
                                    'OK',
                                    $file.'.pdf',
                                    Storage::disk('minio')->temporaryUrl(
                                        $pdfDownload_Location.'/'.$file.'.pdf',
                                        now()->addMinutes(5)
                                    ),
                                    'compress',
                                    $batchId,
                                    $newFileSize,
                                    $newCompFileSize,
                                    $compMethod,
                                    null
                                );
                            } else {
                                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                $duration = $end->diff($startProc);
                                appLogModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'errReason' => 'Error while processing file: '.$file,
                                        'errStatus' => 'Failed to download file from iLovePDF API !'
                                    ]);
                                compressModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'result' => false,
                                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                        'procDuration' =>  $duration->s.' seconds'
                                    ]);
                                NotificationHelper::Instance()->sendErrNotify(
                                    $file,
                                    $newFileSize,
                                    $batchId,
                                    'FAIL',
                                    'compress',
                                    'Failed to download file from iLovePDF API !',
                                    'Error while processing file: '.$file
                                );
                                return $this->returnDataMessage(
                                    400,
                                    'PDF Compression failed !',
                                    null,
                                    null,
                                    null,
                                    $batchId,
                                    'Failed to download file from iLovePDF API !'
                                );
                            }
                        }
                    }
                } else {
                    $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                    $duration = $end->diff($startProc);
                    appLogModel::where('groupId', '=', $batchId)
                        ->update([
                            'errReason' => 'File not found on our end, please try again',
                            'errStatus' => 'File not found on the server'
                        ]);
                    compressModel::where('groupId', '=', $batchId)
                        ->update([
                            'result' => false,
                            'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                            'procDuration' =>  $duration->s.' seconds'
                        ]);
                    NotificationHelper::Instance()->sendErrNotify(
                        $currentFileName,
                        null,
                        $batchId,
                        'FAIL',
                        'compress',
                        'File not found on the server',
                        'File not found on our end, please try again'
                    );
                    return $this->returnDataMessage(
                        400,
                        'PDF Compression failed !',
                        null,
                        null,
                        null,
                        $batchId,
                        'File not found on our end, please try again'
                    );
                }
            } else {
                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                $duration = $end->diff($startProc);
                appLogModel::create([
                    'processId' => $uuid,
                    'errReason' => null,
                    'errStatus' => 'PDF failed to upload !'
                ]);
                return $this->returnDataMessage(
                    400,
                    'PDF Compression failed !',
                    null,
                    null,
                    null,
                    $batchId,
                    'PDF failed to upload'
                );
            }
		}
	}
}
