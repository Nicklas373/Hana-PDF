<?php

namespace App\Http\Controllers\Api\Core;

use App\Helpers\AppHelper;
use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\appLogModel;
use App\Models\cnvModel;
use App\Models\fileModel;
use Aspose\Words\WordsApi;
use Aspose\Words\Model\Requests\{SaveAsRequest, UploadFileRequest};
use Aspose\Words\Model\{DocxSaveOptionsData};
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Ilovepdf\Ilovepdf;
use Ilovepdf\ImagepdfTask;
use Ilovepdf\OfficepdfTask;
use Ilovepdf\PdfjpgTask;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\RuntimeException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class convertController extends Controller
{
	public function convert(Request $request) {
		$validator = Validator::make($request->all(),[
            'batch' => ['required', 'in:true,false'],
            'convertType' => ['required', 'in:jpg,docx,pptx,xlsx,pdf'],
            'file' => [
                'required',
                'array'
            ],
            'file.*' => [
                'required',
                'string',
                'regex:/\.doc|\.docx|\.xls|\.xlsx|\.pptx|\.ppt|.pdf|\.jpg|\.jpeg|\.png/i'
            ],
            'extImage' => ['required', 'in:true,false']
		]);

        // Generate Uni UUID
        $uuid = AppHelper::Instance()->generateUniqueUuid(cnvModel::class, 'processId');
        $batchId = AppHelper::Instance()->generateUniqueUuid(cnvModel::class, 'groupId');
        $fileUuid = AppHelper::Instance()->generateUniqueUuid(fileModel::class, 'processId');

        // Carbon timezone
        date_default_timezone_set('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');
        $startProc = $now->format('Y-m-d H:i:s');

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
                'convert',
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
                $images = $request->post('extImage');
                $convertType = $request->post('convertType');
                $batch = $request->post('batch');
                $pdfEncKey = bin2hex(random_bytes(16));
                $pdfUpload_Location = env('PDF_UPLOAD');
                $pdfProcessed_Location = env('PDF_DOWNLOAD');
                $pdfPool_Location = env('PDF_POOL');
                $pdfExtImage_Location = env('PDF_IMG_POOL');
                $pdfImageTrueName = null;
                $loopCount = count($files);
                $poolFiles = array();
                $altPoolFiles = array();
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
                if ($images == "true") {
                    $imageModes = 'extract';
                    $extMode = true;
                } else {
                    $imageModes = 'pages';
                    $extMode = false;
                }
                $randomizePdfFileName = 'pdfConvert_'.substr(bin2hex(random_bytes(4)), 0, 8);
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
                            'convert',
                            $currentFileName.' could not be found in the object storage',
                            null
                        );
                        return $this->returnDataMessage(
                            400,
                            'PDF Convert failed !',
                            null,
                            null,
                            null,
                            $batchId,
                            $currentFileName.' could not be found in the object storage'
                        );
                    }
                }
                if ($loopCount == count($altPoolFiles)) {
                    foreach ($files as $file) {
                        $currentFileName = basename($file);
                        $trimPhase1 = str_replace(' ', '_', $currentFileName);
                        $newFileNameWithoutExtension = str_replace('.', '_', $trimPhase1);
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
                        $procUuid = AppHelper::Instance()->generateUniqueUuid(cnvModel::class, 'processId');
                        $pdfNameWithExtension = pathinfo($currentFileName, PATHINFO_EXTENSION);
                        $newFormattedFilename = str_replace('_'.$pdfNameWithExtension, '', $newFileNameWithoutExtension);
                        appLogModel::create([
                            'processId' => $procUuid,
                            'groupId' => $batchId,
                            'errReason' => null,
                            'errStatus' => null
                        ]);
                        cnvModel::create([
                            'fileName' => $currentFileName,
                            'fileSize' => $newFileSize,
                            'container' => $convertType,
                            'imgExtract' => false,
                            'result' => false,
                            'isBatch' => $batchValue,
                            'isDeleted' => false,
                            'batchName' => $newFileName,
                            'fileId' => $fileId,
                            'groupId' => $batchId,
                            'processId' => $procUuid,
                            'deletedAt' => null,
                            'procStartAt' => $startProc,
                            'procEndAt' => null,
                            'procDuration' => null
                        ]);
                        if ($convertType == 'xlsx' || $convertType == 'pptx') {
                            $minioUpload = Storage::disk('minio')->get($pdfUpload_Location.'/'.$currentFileName);
                            Storage::disk('local')->put(
                                $pdfUpload_Location.'/'.$currentFileName,
                                $minioUpload
                            );
                            $newFilePath = Storage::disk('local')->path($pdfUpload_Location.'/'.$currentFileName);
                            $pdfStream = fopen($newFilePath, 'r');
                            $asposeToken = AppHelper::instance()->getAsposeToken(env('ASPOSE_CLOUD_CLIENT_ID'), env('ASPOSE_CLOUD_TOKEN'));
                            if ($asposeToken) {
                                try {
                                    // file deepcode ignore Ssrf:
                                    // almost all variable already protected by laravel validation rule
                                    // this should reducing SSRF attacks potential, but still need to re-look after it
                                    $asposeAPI = Http::timeout(300)
                                        ->withToken($asposeToken)
                                        ->withHeaders([
                                            'Accept' => 'application/json',
                                            'Content-Type' => 'application/octet-stream'
                                        ])
                                        ->send('PUT', "https://api.aspose.cloud/v3.0/pdf/convert/{$convertType}?outPath={$newFormattedFilename}.{$convertType}", [
                                            'body' => $pdfStream,
                                        ]);
                                    fclose($pdfStream);
                                    if ($asposeAPI->successful()) {
                                        if (Storage::disk('ftp')->exists($newFormattedFilename.".".$convertType)) {
                                            $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                            $duration = $end->diff($startProc);
                                            array_push($poolFiles, $newFormattedFilename);
                                            $procFile += 1;
                                            $minioDownload = Storage::disk('ftp')->get($newFormattedFilename.'.'.$convertType);
                                            Storage::disk('local')->put(
                                                $pdfDownload_Location.'/'.$newFormattedFilename.'.'.$convertType,
                                                $minioDownload
                                            );
                                            $newFilePath = Storage::disk('local')->path($pdfDownload_Location.'/'.$newFormattedFilename.'.'.$convertType);
                                            Storage::disk('minio')->put(
                                                $pdfDownload_Location.'/'.$newFormattedFilename.'.'.$convertType,
                                                Storage::disk('local')->get($pdfDownload_Location.'/'.$newFormattedFilename.'.'.$convertType)
                                            );
                                            Storage::disk('ftp')->delete($newFormattedFilename.'.'.$convertType);
                                            Storage::disk('local')->delete($pdfUpload_Location.'/'.$trimPhase1);
                                        } else {
                                            $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                            $duration = $end->diff($startProc);
                                            appLogModel::where('groupId', '=', $batchId)
                                                ->update([
                                                    'errReason' => null,
                                                    'errStatus' => 'FTP Server Connection Failed !'
                                                ]);
                                            cnvModel::where('groupId', '=', $batchId)
                                                ->update([
                                                    'result' => false,
                                                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                                    'procDuration' => $duration->s.' seconds'
                                                ]);
                                            NotificationHelper::Instance()->sendErrNotify(
                                                $currentFileName,
                                                $newFileSize,
                                                $batchId,
                                                'FAIL',
                                                'cnvTo'.$convertType,
                                                'FTP Server Connection Failed !',
                                                'Aspose API v3.0 - '.$convertType
                                            );
                                            Storage::disk('local')->delete($pdfUpload_Location.'/'.$trimPhase1);
                                            return $this->returnDataMessage(
                                                400,
                                                'PDF Convert failed !',
                                                null,
                                                null,
                                                null,
                                                $batchId,
                                                'FTP Server Connection Failed !'
                                            );
                                        }
                                    } else {
                                        $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                        $duration = $end->diff($startProc);
                                        appLogModel::where('groupId', '=', $batchId)
                                            ->update([
                                                'errReason' => $asposeAPI->body(),
                                                'errStatus' => 'Aspose API v3.0 - '.$convertType.' failure'
                                            ]);
                                        cnvModel::where('groupId', '=', $batchId)
                                            ->update([
                                                'result' => false,
                                                'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                                'procDuration' => $duration->s.' seconds'
                                            ]);
                                        NotificationHelper::Instance()->sendErrNotify(
                                            $currentFileName,
                                            $newFileSize,
                                            $batchId,
                                            'FAIL',
                                            'cnvTo'.$convertType,
                                            'Aspose API v3.0 - '.$convertType.' failure',
                                            $asposeAPI->body()
                                        );
                                        Storage::disk('local')->delete($pdfUpload_Location.'/'.$trimPhase1);
                                        return $this->returnDataMessage(
                                            400,
                                            'PDF Convert failed !',
                                            null,
                                            $asposeAPI->body(),
                                            null,
                                            $batchId,
                                            'Aspose API v3.0 - '.$convertType.' failure'
                                        );
                                    }
                                } catch (\Exception $e) {
                                    $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                    $duration = $end->diff($startProc);
                                    appLogModel::where('groupId', '=', $batchId)
                                        ->update([
                                            'errReason' => $e->getMessage(),
                                            'errStatus' => 'Guzzle HTTP failure'
                                        ]);
                                    cnvModel::where('groupId', '=', $batchId)
                                        ->update([
                                            'result' => false,
                                            'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                            'procDuration' => $duration->s.' seconds'
                                        ]);
                                    NotificationHelper::Instance()->sendErrNotify(
                                        $currentFileName,
                                        $newFileSize,
                                        $batchId,
                                        'FAIL',
                                        'cnvTo'.$convertType,
                                        'Guzzle HTTP failure',
                                        $e->getMessage()
                                    );
                                    Storage::disk('local')->delete($pdfUpload_Location.'/'.$trimPhase1);
                                    return $this->returnDataMessage(
                                        400,
                                        'PDF Convert failed !',
                                        null,
                                        $e->getMessage(),
                                        null,
                                        $batchId,
                                        'Guzzle HTTP failure'
                                    );
                                }
                            } else {
                                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                $duration = $end->diff($startProc);
                                appLogModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'errReason' => $message->getMessage(),
                                        'errStatus' => 'Failed to generated Aspose Token !'
                                    ]);
                                cnvModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'result' => false,
                                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                        'procDuration' => $duration->s.' seconds'
                                    ]);
                                NotificationHelper::Instance()->sendErrNotify(
                                    $currentFileName,
                                    $newFileSize,
                                    $batchId,
                                    'FAIL',
                                    'cnvTo'.$convertType,
                                    'Failed to generated Aspose Token !',
                                    'Aspose API v3.0 - '.$convertType.' failure'
                                );
                                Storage::disk('local')->delete($pdfUpload_Location.'/'.$trimPhase1);
                                return $this->returnDataMessage(
                                    400,
                                    'PDF Convert failed !',
                                    null,
                                    null,
                                    null,
                                    $batchId,
                                    'Failed to generated Aspose Token !'
                                );
                            }
                        } else if ($convertType == 'docx') {
                            $minioUpload = Storage::disk('minio')->get($pdfUpload_Location.'/'.$currentFileName);
                            Storage::disk('local')->put(
                                $pdfUpload_Location.'/'.$currentFileName,
                                $minioUpload
                            );
                            $newFilePath = Storage::disk('local')->path($pdfUpload_Location.'/'.$currentFileName);
                            try {
                                $wordsApi = new WordsApi(env('ASPOSE_CLOUD_CLIENT_ID'), env('ASPOSE_CLOUD_TOKEN'));
                                $uploadFileRequest = new UploadFileRequest($newFilePath, $currentFileName);
                                $wordsApi->uploadFile($uploadFileRequest);
                                $requestSaveOptionsData = new DocxSaveOptionsData(array(
                                    "save_format" => "docx",
                                    "file_name" => $newFormattedFilename.".docx",
                                ));
                                $request = new SaveAsRequest(
                                    $currentFileName,
                                    $requestSaveOptionsData,
                                    NULL,
                                    NULL,
                                    NULL,
                                    NULL
                                );
                                $result = $wordsApi->saveAs($request);
                            } catch (\Exception $e) {
                                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                $duration = $end->diff($startProc);
                                appLogModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'errReason' => $e->getMessage(),
                                        'errStatus' => 'Aspose API Error !, CnvToDocx failure'
                                    ]);
                                cnvModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'result' => false,
                                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                        'procDuration' => $duration->s.' seconds'
                                    ]);
                                NotificationHelper::Instance()->sendErrNotify(
                                    $currentFileName,
                                    $newFileSize,
                                    $batchId,
                                    'FAIL',
                                    'CnvToDOCX',
                                    'Aspose API Error !, CnvToDOCX failure',
                                    $e->getMessage()
                                );
                                Storage::disk('local')->delete($pdfUpload_Location.'/'.$trimPhase1);
                                return $this->returnDataMessage(
                                    400,
                                    'PDF Convert failed !',
                                    null,
                                    $e->getMessage(),
                                    null,
                                    $batchId,
                                    'Aspose API Error !, CnvToDOCX failure'
                                );
                            }
                            if (json_decode($result, true) !== NULL) {
                                if (Storage::disk('ftp')->exists($newFormattedFilename.".docx")) {
                                    $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                    $duration = $end->diff($startProc);
                                    $procFile += 1;
                                    array_push($poolFiles, $newFormattedFilename);
                                    $minioDownload = Storage::disk('ftp')->get($newFormattedFilename.'.docx');
                                    Storage::disk('local')->put(
                                        $pdfDownload_Location.'/'.$newFormattedFilename.'.docx',
                                        $minioDownload
                                    );
                                    $newFilePath = Storage::disk('local')->path($pdfDownload_Location.'/'.$newFormattedFilename.'.docx');
                                    Storage::disk('minio')->put(
                                        $pdfDownload_Location.'/'.$newFormattedFilename.'.docx',
                                        Storage::disk('local')->get($pdfDownload_Location.'/'.$newFormattedFilename.'.docx')
                                    );
                                    Storage::disk('ftp')->delete($newFormattedFilename.'.docx');
                                    Storage::disk('local')->delete($pdfUpload_Location.'/'.$trimPhase1);
                                } else {
                                    $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                    $duration = $end->diff($startProc);
                                    appLogModel::where('groupId', '=', $batchId)
                                        ->update([
                                            'errReason' => null,
                                            'errStatus' => 'FTP Server Connection Failed !'
                                        ]);
                                    cnvModel::where('groupId', '=', $batchId)
                                        ->update([
                                            'result' => false,
                                            'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                            'procDuration' => $duration->s.' seconds'
                                        ]);
                                    NotificationHelper::Instance()->sendErrNotify(
                                        $currentFileName,
                                        $newFileSize,
                                        $batchId,
                                        'FAIL',
                                        'cnvToDocx',
                                        'FTP Server Connection Failed !',
                                        null
                                    );
                                    Storage::disk('local')->delete($pdfUpload_Location.'/'.$trimPhase1);
                                    return $this->returnDataMessage(
                                        400,
                                        'PDF Convert failed !',
                                        null,
                                        $e->getMessage(),
                                        null,
                                        $batchId,
                                        'FTP Server Connection Failed',
                                    );
                                }
                            } else {
                                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                $duration = $end->diff($startProc);
                                appLogModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'errReason' => null,
                                        'errStatus' => 'Aspose API has fail while process, Please look on Aspose Dashboard !'
                                    ]);
                                cnvModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'result' => false,
                                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                        'procDuration' => $duration->s.' seconds'
                                    ]);
                                NotificationHelper::Instance()->sendErrNotify(
                                    $currentFileName,
                                    $newFileSize,
                                    $batchId,
                                    'FAIL',
                                    'cnvToDocx',
                                    'Aspose Clouds API Error !',
                                    null
                                );
                                Storage::disk('local')->delete($pdfUpload_Location.'/'.$trimPhase1);
                                return $this->returnDataMessage(
                                    400,
                                    'PDF Convert failed !',
                                    null,
                                    $e->getMessage(),
                                    null,
                                    $batchId,
                                    'Aspose Clouds API Error !'
                                );
                            }
                        } else if ($convertType == 'jpg') {
                            $minioUpload = Storage::disk('minio')->get($pdfUpload_Location.'/'.$currentFileName);
                            Storage::disk('local')->put(
                                $pdfUpload_Location.'/'.$currentFileName,
                                $minioUpload
                            );
                            $newFilePath = Storage::disk('local')->path($pdfUpload_Location.'/'.$currentFileName);
                            $pdfTotalPages = AppHelper::instance()->count($newFilePath);
                            try {
                                $ilovepdfTask = new PdfjpgTask(env('ILOVEPDF_PUBLIC_KEY'),env('ILOVEPDF_SECRET_KEY'));
                                $ilovepdfTask->setFileEncryption($pdfEncKey);
                                $ilovepdfTask->setEncryptKey($pdfEncKey);
                                $ilovepdfTask->setEncryption(true);
                                $pdfFile = $ilovepdfTask->addFile($newFilePath);
                                Storage::disk('local')->delete($newFilePath);
                                $ilovepdfTask->setMode($imageModes);
                                $ilovepdfTask->setOutputFileName($newFormattedFilename);
                                $ilovepdfTask->setPackagedFilename($newFormattedFilename);
                                $ilovepdfTask->execute();
                                $ilovepdfTask->download(Storage::disk('local')->path($pdfDownload_Location));
                                $ilovepdfTask->delete();
                            } catch (\Exception $e) {
                                Storage::disk('local')->delete($pdfUpload_Location.'/'.$trimPhase1);
                                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                $duration = $end->diff($startProc);
                                appLogModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'errReason' => $e->getMessage(),
                                        'errStatus' => 'iLovePDF API Error !, Catch on Exception'
                                    ]);
                                cnvModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'result' => false,
                                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                        'procDuration' => $duration->s.' seconds'
                                    ]);
                                NotificationHelper::Instance()->sendErrNotify(
                                    $currentFileName,
                                    $newFileSize,
                                    $batchId,
                                    'FAIL',
                                    'cnvToImg',
                                    'iLovePDF API Error !, Catch on Exception',
                                    $e->getMessage()
                                );
                                return $this->returnDataMessage(
                                    400,
                                    'PDF Convert failed !',
                                    null,
                                    $e->getMessage(),
                                    null,
                                    $batchId,
                                    'iLovePDF API Error !, Catch on Exception'
                                );
                            }
                            if ($pdfTotalPages == 1 && $extMode) {
                                foreach (glob(Storage::disk('local')->path($pdfExtImage_Location).'/*.jpg') as $filename) {
                                    rename($filename, Storage::disk('local')->path($pdfDownload_Location.'/'.$newFormattedFilename.'.jpg'));
                                }
                            }
                            if (Storage::disk('local')->exists($pdfDownload_Location.'/'.$newFormattedFilename.'.zip')) {
                                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                $duration = $end->diff($startProc);
                                $procFile += 1;
                                array_push($poolFiles, $newFormattedFilename);
                                $pdfImageTrueName = $newFormattedFilename.'.zip';
                                Storage::disk('minio')->put(
                                    $pdfDownload_Location.'/'.$newFormattedFilename.'.zip',
                                    Storage::disk('local')->get($pdfDownload_Location.'/'.$newFormattedFilename.'.zip')
                                );
                            } else {
                                if (Storage::disk('local')->exists($pdfDownload_Location.'/'.$newFormattedFilename.'.jpg')) {
                                    $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                    $duration = $end->diff($startProc);
                                    $procFile += 1;
                                    array_push($poolFiles, $newFormattedFilename);
                                    $pdfImageTrueName = $newFormattedFilename.'.jpg';
                                    Storage::disk('minio')->put(
                                        $pdfDownload_Location.'/'.$newFormattedFilename.'.jpg',
                                        Storage::disk('local')->get($pdfDownload_Location.'/'.$newFormattedFilename.'.jpg')
                                    );
                                } else if (Storage::disk('local')->exists($pdfDownload_Location.'/'.$newFormattedFilename.'-0001.jpg')) {
                                    $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                    $duration = $end->diff($startProc);
                                    $procFile += 1;
                                    array_push($poolFiles, $newFormattedFilename);
                                    $pdfImageTrueName = $newFormattedFilename.'-0001.jpg';
                                    Storage::disk('minio')->put(
                                        $pdfDownload_Location.'/'.$newFormattedFilename.'-0001.jpg',
                                        Storage::disk('local')->get($pdfDownload_Location.'/'.$newFormattedFilename.'-0001.jpg')
                                    );
                                }
                            }
                        } else if ($convertType == 'pdf') {
                            $minioUpload = Storage::disk('minio')->get($pdfUpload_Location.'/'.$currentFileName);
                            Storage::disk('local')->put(
                                $pdfUpload_Location.'/'.$currentFileName,
                                $minioUpload
                            );
                            $newFilePath = Storage::disk('local')->path($pdfUpload_Location.'/'.$currentFileName);
                            try {
                                if ($pdfNameWithExtension == "jpg" || $pdfNameWithExtension == "jpeg" || $pdfNameWithExtension == "png" || $pdfNameWithExtension == "tiff") {
                                    $ilovepdfTask = new ImagepdfTask(env('ILOVEPDF_PUBLIC_KEY'),env('ILOVEPDF_SECRET_KEY'));
                                    $ilovepdfTask->setFileEncryption($pdfEncKey);
                                    $ilovepdfTask->setEncryptKey($pdfEncKey);
                                    $ilovepdfTask->setEncryption(true);
                                    $pdfFile = $ilovepdfTask->addFile($newFilePath);
                                    Storage::disk('local')->delete($newFilePath);
                                    $pdfFile->setPassword($pdfEncKey);
                                    $ilovepdfTask->setPageSize('fit');
                                    $ilovepdfTask->setOutputFileName($newFormattedFilename);
                                    $ilovepdfTask->setPackagedFilename($newFormattedFilename);
                                    $ilovepdfTask->execute();
                                    $ilovepdfTask->download(Storage::disk('local')->path($pdfDownload_Location));
                                    $ilovepdfTask->delete();
                                } else {
                                    $ilovepdfTask = new OfficepdfTask(env('ILOVEPDF_PUBLIC_KEY'),env('ILOVEPDF_SECRET_KEY'));
                                    $ilovepdfTask->setFileEncryption(env('ILOVEPDF_ENC_KEY'));
                                    $pdfFile = $ilovepdfTask->addFile($newFilePath);
                                    Storage::disk('local')->delete($newFilePath);
                                    $pdfFile->setPassword($pdfEncKey);
                                    $ilovepdfTask->setOutputFileName($newFormattedFilename);
                                    $ilovepdfTask->execute();
                                    $ilovepdfTask->download(Storage::disk('local')->path($pdfDownload_Location));
                                    $ilovepdfTask->delete();
                                }
                            } catch (\Exception $e) {
                                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                $duration = $end->diff($startProc);
                                appLogModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'errReason' => $e->getMessage(),
                                        'errStatus' => 'iLovePDF API Error !, Catch on Exception'
                                    ]);
                                cnvModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'result' => false,
                                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                        'procDuration' => $duration->s.' seconds'
                                    ]);
                                NotificationHelper::Instance()->sendErrNotify(
                                    $currentFileName,
                                    $newFileSize,
                                    $batchId,
                                    'FAIL',
                                    'imgToPDF',
                                    'iLovePDF API Error !, Catch on Exception',
                                    $e->getMessage()
                                );
                                return $this->returnDataMessage(
                                    400,
                                    'PDF Convert failed !',
                                    null,
                                    $e->getMessage(),
                                    null,
                                    $batchId,
                                    'iLovePDF API Error !, Catch on Exception'
                                );
                            }
                            if (Storage::disk('local')->exists($pdfDownload_Location.'/'.$newFormattedFilename.'.pdf')) {
                                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                $duration = $end->diff($startProc);
                                $procFile += 1;
                                array_push($poolFiles, $newFormattedFilename);
                                Storage::disk('minio')->put(
                                    $pdfDownload_Location.'/'.$newFormattedFilename.'.pdf',
                                    Storage::disk('local')->get($pdfDownload_Location.'/'.$newFormattedFilename.'.pdf')
                                );
                            } else {
                                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                $duration = $end->diff($startProc);
                                appLogModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'errReason' => null,
                                        'errStatus' => 'Failed to download file from iLovePDF API !'
                                    ]);
                                cnvModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'result' => false,
                                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                        'procDuration' => $duration->s.' seconds'
                                    ]);
                                NotificationHelper::Instance()->sendErrNotify(
                                    $currentFileName,
                                    $newFileSize,
                                    $batchId,
                                    'FAIL',
                                    'pdfToImg',
                                    'Failed to download file from iLovePDF API !',
                                    null
                                );
                                return $this->returnDataMessage(
                                    400,
                                    'PDF Convert failed !',
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
                        cnvModel::where('groupId', '=', $batchId)
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
                        'convert',
                        'File not found on the server',
                        'File not found on our end, please try again'
                    );
                    return $this->returnDataMessage(
                        400,
                        'PDF Convert failed !',
                        null,
                        null,
                        null,
                        $batchId,
                        'File not found on our end, please try again'
                    );
                }
                if ($loopCount == $procFile) {
                    if ($loopCount == 1) {
                        appLogModel::where('groupId', '=', $batchId)
                            ->update([
                                'errReason' => null,
                                'errStatus' => null
                            ]);
                        if ($convertType == 'jpg') {
                            Storage::disk('local')->delete($pdfDownload_Location.'/'.$pdfImageTrueName);
                            AppHelper::instance()->fileModelHelper(
                                    $pdfImageTrueName,
                                    AppHelper::instance()->convert(Storage::disk('minio')->size($pdfDownload_Location.'/'.$pdfImageTrueName), "MB"),
                                    $fileUuid,
                                    $batchId,
                                    false,
                                    null
                            );
                            $fileId = fileModel::where('fileName', '=', $pdfImageTrueName)
                                            ->where('isDeleted', '=', false)
                                            ->where('processId', '=', $fileUuid)
                                            ->first()
                                            ->fileId;
                            cnvModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'result' => true,
                                        'fileId' => $fileId,
                                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                        'procDuration' => $duration->s.' seconds'
                                    ]);
                            return $this->returnCoreMessage(
                                200,
                                'OK',
                                $pdfImageTrueName,
                                Storage::disk('minio')->temporaryUrl(
                                    $pdfDownload_Location.'/'.$pdfImageTrueName,
                                    now()->addMinutes(5)
                                ),
                                'convert',
                                $uuid,
                                $newFileSize,
                                null,
                                null,
                                null
                            );
                        } else {
                            Storage::disk('local')->delete($pdfDownload_Location.'/'.$newFormattedFilename.'.'.$convertType);
                            AppHelper::instance()->fileModelHelper(
                                $newFormattedFilename.'.'.$convertType,
                                AppHelper::instance()->convert(Storage::disk('minio')->size($pdfDownload_Location.'/'.$newFormattedFilename.'.'.$convertType), "MB"),
                                $fileUuid,
                                $batchId,
                                false,
                                null
                            );
                            $fileId = fileModel::where('fileName', '=', $newFormattedFilename.'.'.$convertType)
                                            ->where('isDeleted', '=', false)
                                            ->where('processId', '=', $fileUuid)
                                            ->first()
                                            ->fileId;
                            cnvModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'result' => true,
                                        'fileId' => $fileId,
                                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                        'procDuration' => $duration->s.' seconds'
                                    ]);
                            return $this->returnCoreMessage(
                                200,
                                'OK',
                                $newFormattedFilename.'.'.$convertType,
                                Storage::disk('minio')->temporaryUrl(
                                    $pdfDownload_Location.'/'.$newFormattedFilename.'.'.$convertType,
                                    now()->addMinutes(5)
                                ),
                                'convert',
                                $uuid,
                                $newFileSize,
                                null,
                                null,
                                null
                            );
                        }
                    } else {
                        $folderPath = Storage::disk('local')->path($pdfDownload_Location);
                        $zipFilePath = Storage::disk('local')->path($pdfDownload_Location.'/'.$randomizePdfFileName.'.zip');
                        $zip = new ZipArchive();
                        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
                            if ($convertType == 'jpg') {
                                foreach ($poolFiles as $file) {
                                    $filePath = $folderPath.DIRECTORY_SEPARATOR.$file.'.zip';
                                    $fileAltPath = $folderPath.DIRECTORY_SEPARATOR.$file.'-0001.jpg';
                                    if (file_exists($filePath)) {
                                        $relativePath = $file.'.zip';
                                        $zip->addFile($filePath, $relativePath);
                                    } else if (file_exists($fileAltPath)) {
                                        $relativePath = $file.'.zip';
                                        $zip->addFile($fileAltPath, $relativePath);
                                    } else {
                                        $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                                        $duration = $end->diff($startProc);
                                        appLogModel::where('groupId', '=', $batchId)
                                            ->update([
                                                'errReason' => 'File '. $filePath . ' was not found',
                                                'errStatus' => 'Failed convert PDF file !'
                                            ]);
                                        cnvModel::where('groupId', '=', $batchId)
                                            ->update([
                                                'result' => false,
                                                'procDuration' => $duration->s.' seconds'
                                            ]);
                                        NotificationHelper::Instance()->sendErrNotify(
                                            $currentFileName,
                                            $newFileSize,
                                            $batchId,
                                            'FAIL',
                                            'convert',
                                            'Failed convert PDF file !',
                                            'File '. $filePath . ' was not found'
                                        );
                                        return $this->returnDataMessage(
                                            400,
                                            'PDF Convert failed !',
                                            null,
                                            null,
                                            null,
                                            $batchId,
                                            'Failed convert PDF file !', 'File '. $filePath . ' was not found'
                                        );
                                    }
                                }
                                $zip->close();
                            } else {
                                foreach ($poolFiles as $file) {
                                    $filePath = $folderPath.DIRECTORY_SEPARATOR.$file.'.'.$convertType;
                                    if (file_exists($filePath)) {
                                        $relativePath = $file.'.'.$convertType;
                                        $zip->addFile($filePath, $relativePath);
                                    }
                                }
                                $zip->close();
                            }
                            foreach ($poolFiles as $file) {
                                Storage::disk('local')->delete($pdfDownload_Location.'/'.$file.'.zip');
                                Storage::disk('local')->delete($pdfDownload_Location.'/'.$file.'-0001.jpg');
                                Storage::disk('local')->delete($pdfDownload_Location.'/'.$file.'.'.$convertType);
                            }
                            $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                            $duration = $end->diff($startProc);
                            appLogModel::where('groupId', '=', $batchId)
                                ->update([
                                    'errReason' => null,
                                    'errStatus' => null
                                ]);
                            Storage::disk('minio')->put(
                                $pdfDownload_Location.'/'.$randomizePdfFileName.'.zip',
                                Storage::disk('local')->get($pdfDownload_Location.'/'.$randomizePdfFileName.'.zip')
                            );
                            Storage::disk('local')->delete($pdfDownload_Location.'/'.$randomizePdfFileName.'.zip');
                            AppHelper::instance()->fileModelHelper(
                                $randomizePdfFileName.'.zip',
                                AppHelper::instance()->convert(Storage::disk('minio')->size($pdfDownload_Location.'/'.$randomizePdfFileName.'.zip'), "MB"),
                                $fileUuid,
                                $batchId,
                                false,
                                null
                            );
                            $fileId = fileModel::where('fileName', '=', $randomizePdfFileName.'.zip')
                                            ->where('isDeleted', '=', false)
                                            ->where('processId', '=', $fileUuid)
                                            ->first()
                                            ->fileId;
                            cnvModel::where('groupId', '=', $batchId)
                                    ->update([
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
                                'convert',
                                $uuid,
                                null,
                                $newFileSize,
                                $convertType,
                                null
                            );
                        } else {
                            $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                            $duration = $end->diff($startProc);
                            appLogModel::where('groupId', '=', $batchId)
                                ->update([
                                    'errReason' => null,
                                    'errStatus' => 'Failed archiving PDF files !'
                                ]);
                            cnvModel::where('groupId', '=', $batchId)
                                ->update([
                                    'result' => false,
                                    'procDuration' => $duration->s.' seconds'
                                ]);
                            NotificationHelper::Instance()->sendErrNotify(
                                $randomizePdfFileName.'.zip',
                                null,
                                $batchId,
                                'FAIL',
                                'compress',
                                'Failed archiving PDF files !',
                                null
                            );
                            foreach ($poolFiles as $file) {
                                $currentFileName = basename($file);
                                Storage::disk('local')->delete($pdfDownload_Location.'/'.$file.'.'.$convertType);
                            }
                            return $this->returnDataMessage(
                                400,
                                'PDF Compression failed !',
                                null,
                                null,
                                null,
                                $batchId,
                                'Failed archiving PDF files !'
                            );
                        }
                    }
                } else {
                    $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                    $duration = $end->diff($startProc);
                    appLogModel::where('groupId', '=', $batchId)
                        ->update([
                            'errReason' => 'Processed file are not same with total file, processed: '.$procFile.' totalFile: '.$loopCount,
                            'errStatus' => 'PDF convert failed'
                        ]);
                    cnvModel::where('groupId', '=', $batchId)
                        ->update([
                            'result' => false,
                            'procDuration' => $duration->s.' seconds'
                        ]);
                    foreach ($poolFiles as $file) {
                        $currentFileName = basename($file);
                        Storage::disk('local')->delete($pdfDownload_Location.'/'.$file.'.'.$convertType);
                    }
                    return $this->returnDataMessage(
                        400,
                        'PDF convert failed !',
                        null,
                        null,
                        null,
                        $batchId,
                        'Processed file are not same with total file, processed: '.$procFile.' totalFile: '.$loopCount
                    );
                }
            } else {
                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                $duration = $end->diff($startProc);
                appLogModel::create([
                    'processId' => $procUuid,
                    'groupId' => $batchId,
                    'errReason' =>  'PDF failed to upload',
                    'errStatus' => 'PDF Convert failed !'
                ]);
                return $this->returnDataMessage(
                    400,
                    'PDF Convert failed !',
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
