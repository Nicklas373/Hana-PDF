<?php

namespace App\Http\Controllers\Api\Core;

use App\Helpers\AppHelper;
use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\appLogModel;
use App\Models\fileModel;
use App\Models\watermarkModel;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Ilovepdf\Ilovepdf;

class watermarkController extends Controller
{
    public function watermark(Request $request) {
        $validator = Validator::make($request->all(),[
           'file' => [
                'required',
                'array'
            ],
            'file.*' => [
                'required',
                'string',
                'regex:/\.pdf/i'
            ],
            'imgFile' => [
                'sometimes',
                'nullable',
                'image',
                'max:5120'
            ],
            'action' => ['required', 'in:img,txt'],
            'wmFontColor' => ['nullable','regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'wmFontSize' => ['nullable', 'numeric'],
            'wmFontStyle' => ['required', 'in:Regular,Bold,Italic'],
            'wmFontFamily' => ['required', 'in:Arial,Arial Unicode MS,Comic Sans MS,Courier,Times New Roman,Verdana'],
            'wmLayoutStyle' => ['required', 'in:above,below'],
            'wmRotation' => ['required', 'numeric'],
            'wmPage' => ['required', 'regex:/^(all|[0-9,-]+)$/'],
            'wmText' => ['nullable','string'],
            'wmTransparency' => ['required', 'numeric'],
            'wmMosaic' => ['required', 'in:true,false']
		]);

        // Generate Uni UUID
        $uuid = AppHelper::Instance()->generateUniqueUuid(watermarkModel::class, 'processId');
        $batchId = AppHelper::Instance()->generateUniqueUuid(watermarkModel::class, 'groupId');
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
                $batchId,
                'FAIL',
                'watermark',
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
                $pdfEncKey = bin2hex(random_bytes(16));
                $pdfUpload_Location = env('PDF_UPLOAD');
                $pdfProcessed_Location = env('PDF_DOWNLOAD');
                $pdfDownload_Location = $pdfProcessed_Location;
                $batchValue = false;
                $randomizePdfFileName = 'pdfWatermark_'.substr(bin2hex(random_bytes(4)), 0, 8);
                $loopCount = count($files);
                $altPoolFiles = array();
                foreach ($files as $file) {
                    $currentFileName = basename($file);
                    $trimPhase1 = str_replace(' ', '_', $currentFileName);
                    $newFileNameWithoutExtension = str_replace('.', '_', $trimPhase1);
                    $newFilePath = Storage::disk('local')->path($pdfUpload_Location.'/'.$trimPhase1);
                    if (Storage::disk('minio')->exists($pdfUpload_Location.'/'.$trimPhase1)) {
                        array_push($altPoolFiles, $newFileNameWithoutExtension);
                    } else {
                        $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                        $duration = $end->diff($startProc);
                        appLogModel::create([
                            'processId' => $procUuid,
                            'groupId' => $batchId,
                            'errReason' => null,
                            'errStatus' => $currentFileName.' could not be found in the object storage'
                        ]);
                        NotificationHelper::Instance()->sendErrNotify(
                            $currentFileName,
                            null,
                            $batchId,
                            'FAIL',
                            'watermark',
                            $currentFileName.' could not be found in the object storage',
                            null
                        );
                        return $this->returnDataMessage(
                            400,
                            'PDF Watermark failed !',
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
                        $minioUpload = Storage::disk('minio')->get($pdfUpload_Location.'/'.$trimPhase1);
                        Storage::disk('local')->put(
                            $pdfUpload_Location.'/'.$trimPhase1,
                            $minioUpload
                        );
                        $newFilePath = Storage::disk('local')->path($pdfUpload_Location.'/'.$trimPhase1);
                        $fileSize = Storage::disk('minio')->size($pdfUpload_Location.'/'.$trimPhase1);
                        $procUuid = AppHelper::Instance()->generateUniqueUuid(watermarkModel::class, 'processId');
                        $newFileSize = AppHelper::instance()->convert($fileSize, "MB");
                        $watermarkAction = $request->post('action');
                        $fileId = fileModel::where('fileName', '=', $trimPhase1)
                                            ->where('isDeleted', '=', false)
                                            ->first()
                                            ->fileId;
                        appLogModel::create([
                            'processId' => $procUuid,
                            'groupId' => $batchId,
                            'errReason' => null,
                            'errStatus' => null
                        ]);
                        watermarkModel::create([
                            'fileName' => $currentFileName,
                            'fileSize' => $newFileSize,
                            'watermarkFontFamily' => null,
                            'watermarkFontStyle' => null,
                            'watermarkFontSize' => null,
                            'watermarkFontTransparency' => null,
                            'watermarkImage' => null,
                            'watermarkLayout' => null,
                            'watermarkMosaic' => null,
                            'watermarkRotation' => null,
                            'watermarkStyle' => null,
                            'watermarkText' => null,
                            'watermarkPage' => null,
                            'result' => false,
                            'isBatch' => $batchValue,
                            'isDeleted' => false,
                            'batchName' => $randomizePdfFileName.'.pdf',
                            'fileId' => $fileId,
                            'groupId' => $batchId,
                            'processId' => $procUuid,
                            'deletedAt' => null,
                            'procStartAt' => $startProc,
                            'procEndAt' => null,
                            'procDuration' => null
                        ]);
                        if ($watermarkAction == 'img') {
                            $watermarkImage = $request->file('imgFile');
                            if (empty($watermarkImage)) {
                                appLogModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'errReason' => 'Image file for watermark can not be empty !',
                                        'errStatus' => 'PDF Watermark failed !'
                                    ]);
                                watermarkModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'result' => false,
                                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                        'procDuration' => $duration->s.' seconds'
                                    ]);
                                NotificationHelper::Instance()->sendErrNotify(
                                    null,
                                    null,
                                    $batchId,
                                    'FAIL',
                                    'watermark',
                                    'PDF Watermark failed !',
                                    'Image file for watermark can not be empty !'
                                );
                                return $this->returnDataMessage(
                                    400,
                                    'PDF Watermark failed !',
                                    null,
                                    null,
                                    null,
                                    $batchId,
                                    'Image file for watermark can not be empty !'
                                );
                            } else {
                                $currentImageName = basename($watermarkImage);
                                $trimImagePhase1 = str_replace(' ', '_', $currentImageName);
                                $newImageNameWithoutExtension = str_replace('.', '_', $trimImagePhase1);
                                $randomizeImageExtension = pathinfo($watermarkImage->getClientOriginalName(), PATHINFO_EXTENSION);
                                $wmImageName = $newImageNameWithoutExtension.'.'.$randomizeImageExtension;
                                Storage::disk('local')->put(
                                    $pdfUpload_Location.'/'.$wmImageName,
                                    file_get_contents($watermarkImage)
                                );
                                if (!Storage::disk('local')->exists($pdfUpload_Location.'/'.$wmImageName)) {
                                    return $this->returnDataMessage(
                                        400,
                                        'PDF Watermark failed !',
                                        null,
                                        null,
                                        null,
                                        $batchId,
                                        Storage::disk('local')->path($pdfUpload_Location.'/'.$wmImageName)
                                    );
                                }
                                $watermarkText = null;
                            }
                        } else if ($watermarkAction == 'txt') {
                            $watermarkText = $request->post('wmText');
                            $wmImageName = null;
                            if (empty($watermarkText)) {
                                appLogModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'errReason' => 'Text for watermark can not be empty !',
                                        'errStatus' => 'PDF Watermark failed !'
                                    ]);
                                watermarkModel::where('groupId', '=', $batchId)
                                    ->update([
                                        'result' => false,
                                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                        'procDuration' => $duration->s.' seconds'
                                    ]);
                                return $this->returnDataMessage(
                                    400,
                                    'PDF Watermark failed !',
                                    null,
                                    null,
                                    null,
                                    $batchId,
                                    'Text for watermark can not be empty !'
                                );
                            }
                        } else {
                            $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                            $duration = $end->diff($startProc);
                            appLogModel::where('groupId', '=', $batchId)
                                ->update([
                                    'errReason' => 'Invalid request action !,Current request: '.$watermarkAction,
                                    'errStatus' => 'PDF Watermark failed !'
                                ]);
                            watermarkModel::where('groupId', '=', $batchId)
                                ->update([
                                    'result' => false,
                                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                    'procDuration' => $duration->s.' seconds'
                                ]);
                            NotificationHelper::Instance()->sendErrNotify(
                                $currentFileName,
                                $fileSize,
                                $batchId,
                                'FAIL',
                                'Invalid request action !',
                                'Current request: '.$watermarkAction
                            );
                            return $this->returnDataMessage(
                                400,
                                'PDF Watermark failed !',
                                null,
                                null,
                                null,
                                $batchId,
                                'Invalid request action !'.',Current request: '.$watermarkAction
                            );
                        }
                        $tempPDF = $request->post('wmMosaic');
                        if ($tempPDF == 'true') {
                            $isMosaicDB = "true";
                            $isMosaic = true;
                        } else {
                            $isMosaicDB = "false";
                            $isMosaic = false;
                        }
                        $watermarkFontColorTemp = $request->post('wmFontColor');
                        if ($watermarkFontColorTemp == '') {
                            $watermarkFontColor = '#000000';
                        } else {
                            $watermarkFontColor = $watermarkFontColorTemp;
                        }
                        $watermarkFontFamily = $request->post('wmFontFamily');
                        $watermarkFontSizeTemp = $request->post('wmFontSize');
                        if ($watermarkFontSizeTemp == '') {
                            $watermarkFontSize = '12';
                        } else {
                            $watermarkFontSize = $watermarkFontSizeTemp;
                        }
                        $watermarkFontStyle = $request->post('wmFontStyle');
                        $watermarkLayout = $request->post('wmLayoutStyle');
                        $watermarkTempPage = $request->post('wmPage');
                        if (is_string($watermarkTempPage)) {
                            $watermarkPage = strtolower($watermarkTempPage);
                        } else {
                            $watermarkPage = $watermarkTempPage;
                        }
                        $watermarkRotation = $request->post('wmRotation');
                        $watermarkTransparency = $request->post('wmTransparency');
                        try {
                            $ilovepdf = new Ilovepdf(env('ILOVEPDF_PUBLIC_KEY'),env('ILOVEPDF_SECRET_KEY'));
                            $ilovepdfTask = $ilovepdf->newTask('watermark');
                            if ($watermarkAction == 'img') {
                                $ilovepdfTask->setEncryption(true);
                                $wmImage = $ilovepdfTask->addElementFile(Storage::disk('local')->path($pdfUpload_Location.'/'.$wmImageName));
                                $pdfFile = $ilovepdfTask->addFile($newFilePath);
                                Storage::disk('local')->delete($newFilePath);
                                $ilovepdfTask->setMode("image");
                                $ilovepdfTask->setImageFile($wmImage);
                                $ilovepdfTask->setTransparency($watermarkTransparency);
                                $ilovepdfTask->setRotation($watermarkRotation);
                                $ilovepdfTask->setLayer($watermarkLayout);
                                $ilovepdfTask->setPages($watermarkPage);
                                $ilovepdfTask->setMosaic($isMosaic);
                                $ilovepdfTask->setVerticalPosition("middle");
                            } else if ($watermarkAction == 'txt') {
                                $ilovepdfTask->setFileEncryption($pdfEncKey);
                                $ilovepdfTask->setEncryptKey($pdfEncKey);
                                $ilovepdfTask->setEncryption(true);
                                $pdfFile = $ilovepdfTask->addFile($newFilePath);
                                Storage::disk('local')->delete($newFilePath);
                                $ilovepdfTask->setMode("text");
                                $ilovepdfTask->setText($watermarkText);
                                $ilovepdfTask->setPages($watermarkPage);
                                $ilovepdfTask->setVerticalPosition("middle");
                                $ilovepdfTask->setRotation($watermarkRotation);
                                $ilovepdfTask->setFontColor($watermarkFontColor);
                                $ilovepdfTask->setFontFamily($watermarkFontFamily);
                                $ilovepdfTask->setFontStyle($watermarkFontStyle);
                                $ilovepdfTask->setFontSize($watermarkFontSize);
                                $ilovepdfTask->setTransparency($watermarkTransparency);
                                $ilovepdfTask->setLayer($watermarkLayout);
                                $ilovepdfTask->setMosaic($isMosaic);
                            }
                            $ilovepdfTask->setOutputFileName($randomizePdfFileName);
                            $ilovepdfTask->execute();
                            $ilovepdfTask->download(Storage::disk('local')->path($pdfDownload_Location));
                            $ilovepdfTask->delete();
                            if ($watermarkAction == 'img') {
                                Storage::disk('local')->delete($pdfUpload_Location.'/'.$wmImageName);
                            }
                            watermarkModel::where('processId', '=', $procUuid)
                                ->update([
                                    'watermarkFontFamily' => $watermarkFontFamily,
                                    'watermarkFontStyle' => $watermarkFontStyle,
                                    'watermarkFontSize' => $watermarkFontSize,
                                    'watermarkFontTransparency' => $watermarkTransparency,
                                    'watermarkImage' => $wmImageName,
                                    'watermarkLayout' => $watermarkLayout,
                                    'watermarkMosaic' => $isMosaicDB,
                                    'watermarkRotation' => $watermarkRotation,
                                    'watermarkStyle' => $watermarkAction,
                                    'watermarkText' => $watermarkText,
                                    'watermarkPage' => $watermarkPage
                                ]);
                        } catch (\Exception $e) {
                            $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                            $duration = $end->diff($startProc);
                            appLogModel::where('groupId', '=', $batchId)
                                ->update([
                                    'errReason' => $e->getMessage(),
                                    'errStatus' => 'iLovePDF API Error !, Catch on Exception'
                                ]);
                            watermarkModel::where('groupId', '=', $batchId)
                                ->update([
                                    'result' => false,
                                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                    'procDuration' => $duration->s.' seconds'
                                ]);
                            NotificationHelper::Instance()->sendErrNotify(
                                $currentFileName,
                                $fileSize,
                                $batchId,
                                'FAIL',
                                'watermark',
                                'iLovePDF API Error !, Catch on Exception',
                                $e->getMessage()
                            );
                            if ($watermarkAction == 'img') {
                                Storage::disk('local')->delete($pdfUpload_Location.'/'.$wmImageName);
                            }
                            return $this->returnDataMessage(
                                400,
                                'PDF Watermark failed !',
                                null,
                                $e->getMessage(),
                                null,
                                $batchId,
                                'iLovePDF API Error !, Catch on Exception'
                            );
                        }
                        if (Storage::disk('local')->exists($pdfDownload_Location.'/'.$randomizePdfFileName.'.pdf')) {
                            Storage::disk('minio')->put(
                                $pdfDownload_Location.'/'.$randomizePdfFileName.'.pdf',
                                Storage::disk('local')->get($pdfDownload_Location.'/'.$randomizePdfFileName.'.pdf')
                            );
                            $fileProcSize = Storage::disk('minio')->size($pdfDownload_Location.'/'.$randomizePdfFileName.'.pdf');
                            $newProcFileSize = AppHelper::instance()->convert($fileProcSize, "MB");
                            $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                            $duration = $end->diff($startProc);
                            Storage::disk('local')->delete($pdfDownload_Location.'/'.$randomizePdfFileName.'.pdf');
                            AppHelper::instance()->fileModelHelper($randomizePdfFileName.'.pdf', $newProcFileSize, $fileUuid, $batchId, false, null);
                            $fileId = fileModel::where('fileName', '=', $randomizePdfFileName.'.pdf')
                                                ->where('isDeleted', '=', false)
                                                ->first()
                                                ->fileId;
                            appLogModel::where('groupId', '=', $batchId)
                                ->update([
                                    'errReason' => null,
                                    'errStatus' => null
                                ]);
                            watermarkModel::where('groupId', '=', $batchId)
                                ->update([
                                    'result' => true,
                                    'fileId' => $fileId,
                                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                    'procDuration' => $duration->s.' seconds'
                                ]);
                            return $this->returnCoreMessage(
                                200,
                                'OK',
                                $randomizePdfFileName.'.pdf',
                                Storage::disk('minio')->temporaryUrl(
                                    $pdfDownload_Location.'/'.$randomizePdfFileName.'.pdf',
                                    now()->addMinutes(5)
                                ),
                                'watermark',
                                $batchId,
                                $newFileSize,
                                null,
                                null,
                                null
                            );
                        } else {
                            $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                            $duration = $end->diff($startProc);
                            appLogModel::where('groupId', '=', $batchId)
                                ->update([
                                    'errReason' => null,
                                    'errStatus' => 'Failed to download file from iLovePDF API !'
                                ]);
                            watermarkModel::where('groupId', '=', $batchId)
                                ->update([
                                    'result' => false,
                                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                    'procDuration' => $duration->s.' seconds'
                                ]);
                            NotificationHelper::Instance()->sendErrNotify(
                                $currentFileName.'.pdf',
                                $newFileSize,
                                $batchId,
                                'FAIL',
                                'watermark',
                                'Failed to download file from iLovePDF API !',
                                null,
                                true
                            );
                            return $this->returnDataMessage(
                                400,
                                'PDF Watermark failed !',
                                null,
                                null,
                                null,
                                $batchId,
                                'Failed to download file from iLovePDF API !'
                            );
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
                    watermarkModel::where('groupId', '=', $batchId)
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
                    'groupId' => $batchId,
                    'errReason' => null,
                    'errStatus' => 'PDF failed to upload !'
                ]);
                NotificationHelper::Instance()->sendErrNotify(
                    null,
                    null,
                    $batchId,
                    'FAIL',
                    'watermark',
                    'PDF failed to upload !',
                    null
                );
                return $this->returnDataMessage(
                    400,
                    'PDF Watermark failed !',
                    null,
                    null,
                    null,
                    $batchId,
                    'PDF failed to upload !'
                );
            }
        }
    }
}
