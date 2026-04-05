<?php

namespace App\Http\Controllers\Api\Core;

use App\Helpers\AppHelper;
use App\Helpers\NotificationHelper;
use App\Models\appLogModel;
use App\Models\fileModel;
use App\Models\htmlModel;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Ilovepdf\Ilovepdf;
use Ilovepdf\HtmlpdfTask;

class htmltopdfController extends Controller
{
    public function html(Request $request) {
        $validator = Validator::make($request->all(),[
		    'urlToPDF' => ['required', 'string'],
            'urlMarginValue' => ['required', 'numeric'],
            'urlSizeValue' => ['required', 'in:A3,A4,A5,Letter'],
            'urlPageOrientationValue' => ['required', 'in:landscape,portrait'],
            'urlSinglePage' => ['required', 'in:true,false']
	    ]);

        // Generate Uni UUID
        $uuid = AppHelper::Instance()->generateUniqueUuid(htmlModel::class, 'processId');
        $batchId = AppHelper::Instance()->generateUniqueUuid(htmlModel::class, 'groupId');
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
                'htmltopdf',
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
            $start = Carbon::parse($startProc);
            $pdfEncKey = bin2hex(random_bytes(16));
            $pdfDefaultFileName ='pdf_htmltopdf_'.substr(bin2hex(random_bytes(4)), 0, 8);
            $pdfProcessed_Location = env('PDF_DOWNLOAD');
            $pdfUpload_Location = env('PDF_UPLOAD');
            $pdfUrl = $request->post('urlToPDF');
            $pdfOrientation = $request->post('urlPageOrientationValue');
            $pdfMargin = $request->post('urlMarginValue');
            $pdfSize = $request->post('urlSizeValue');
            $pdfSinglePage = $request->post('urlSinglePage');
            $newUrl = '';
            AppHelper::instance()->fileModelHelper($pdfDefaultFileName.'.pdf', null, $fileUuid, $batchId, false, null);
            $fileId = fileModel::where('fileName', '=', $pdfDefaultFileName.'.pdf')
                                ->where('isDeleted', '=', false)
                                ->where('processId', '=', $fileUuid)
                                ->first()
                                ->fileId;
            appLogModel::create([
                'processId' => $uuid,
                'groupId' => $batchId,
                'errReason' => null,
                'errStatus' => null
            ]);
            htmlModel::create([
                'urlName' => $request->post('urlToPDF'),
                'urlMargin' => $pdfMargin,
                'urlOrientation' => $pdfOrientation,
                'urlSinglePage' => $pdfSinglePage,
                'urlSize' => $pdfSize,
                'result' => false,
                'isDeleted' => false,
                'fileId' => $fileId,
                'groupId' => $batchId,
                'processId' => $uuid,
                'deletedAt' => null,
                'procStartAt' => $startProc,
                'procEndAt' => null,
                'procDuration' => null
            ]);
            if (AppHelper::Instance()->checkWebAvailable($pdfUrl)) {
                $newUrl = 'https://'.$pdfUrl;
            } else {
                if (AppHelper::Instance()->checkWebAvailable('https://'.$pdfUrl)) {
                    $newUrl = 'https://'.$pdfUrl;
                } else if (AppHelper::Instance()->checkWebAvailable('http://'.$pdfUrl)) {
                    $newUrl = 'https://'.$pdfUrl;
                } else if (AppHelper::Instance()->checkWebAvailable('www.'.$pdfUrl)) {
                    $newUrl = 'https://'.$pdfUrl;
                } else {
                    $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                    $duration = $end->diff($startProc);
                    appLogModel::where('groupId', '=', $batchId)
                        ->update([
                            'errReason' => 'Webpage are not available or not valid: '.$pdfUrl,
                            'errStatus' => '404'
                        ]);
                    htmlModel::where('groupId', '=', $batchId)
                        ->update([
                            'result' => false,
                            'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                            'procDuration' => $duration->s.' seconds'
                        ]);
                    return $this->returnDataMessage(
                        400,
                        'HTML To PDF failed !',
                        null,
                        null,
                        null,
                        $batchId,
                        'Webpage are not available or not valid: '.$pdfUrl
                    );
                }
            }
            try {
                $ilovepdfTask = new HtmlpdfTask(env('ILOVEPDF_PUBLIC_KEY'),env('ILOVEPDF_SECRET_KEY'));
                $ilovepdfTask->setEncryptKey($pdfEncKey);
                $ilovepdfTask->setEncryption(true);
                $pdfFile = $ilovepdfTask->addUrl($newUrl);
                $ilovepdfTask->setPageOrientation($pdfOrientation);
                $ilovepdfTask->setPageMargin($pdfMargin);
                $ilovepdfTask->setPageSize($pdfSize);
                if ($pdfSinglePage == 'true') {
                    $ilovepdfTask->setSinglePage(true);
                } else {
                    $ilovepdfTask->setSinglePage(false);
                }
                $ilovepdfTask->setOutputFileName($pdfDefaultFileName);
                $ilovepdfTask->execute();
                $ilovepdfTask->download(Storage::disk('local')->path($pdfProcessed_Location));
                $ilovepdfTask->delete();
            } catch (\Exception $e) {
                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                $duration = $end->diff($startProc);
                appLogModel::where('groupId', '=', $batchId)
                    ->update([
                        'errReason' => $e->getMessage(),
                        'errStatus' => 'iLovePDF API Error !, Catch on Exception'
                    ]);
                htmlModel::where('groupId', '=', $batchId)
                    ->update([
                        'result' => false,
                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                        'procDuration' => $duration->s.' seconds'
                    ]);
                NotificationHelper::Instance()->sendErrNotify(
                    $pdfUrl,
                    null,
                    $batchId,
                    'FAIL',
                    'htmltopdf',
                    'iLovePDF API Error !, Catch on Exception',
                    $e->getMessage()
                );
                return $this->returnDataMessage(
                    400,
                    'HTML To PDF failed !',
                    null,
                    $e->getMessage(),
                    null,
                    $batchId,
                    'iLovePDF API Error !, Catch on Exception'
                );
            }
            if (Storage::disk('local')->exists($pdfProcessed_Location.'/'.$pdfDefaultFileName.'.pdf')) {
                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                $duration = $end->diff($startProc);
                Storage::disk('minio')->put(
                    $pdfProcessed_Location.'/'.$pdfDefaultFileName.'.pdf',
                    Storage::disk('local')->get($pdfProcessed_Location.'/'.$pdfDefaultFileName.'.pdf')
                );
                Storage::disk('local')->delete($pdfProcessed_Location.'/'.$pdfDefaultFileName.'.pdf');
                $fileProcSize = Storage::disk('minio')->size($pdfProcessed_Location.'/'.$pdfDefaultFileName.'.pdf');
                appLogModel::where('groupId', '=', $batchId)
                            ->update([
                                'errReason' => null,
                                'errStatus' => null
                            ]);
                fileModel::where('fileId', '=', $fileId)
                            ->update([
                                'fileSize' => $fileProcSize
                            ]);
                htmlModel::where('groupId', '=', $batchId)
                    ->update([
                        'result' => true,
                        'fileId' => $fileId,
                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                        'procDuration' => $duration->s.' seconds'
                    ]);
                return $this->returnCoreMessage(
                    200,
                    'OK',
                    $pdfUrl,
                    Storage::disk('minio')->temporaryUrl(
                        $pdfProcessed_Location.'/'.$pdfDefaultFileName.'.pdf',
                        now()->addMinutes(5)
                    ),
                    'htmltopdf',
                    $batchId,
                    $fileProcSize,
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
                htmlModel::where('groupId', '=', $batchId)
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
                    'Failed to download file from iLovePDF API !',
                    null
                );
                return $this->returnDataMessage(
                    400,
                    'HTML To PDF failed !',
                    null,
                    null,
                    null,
                    $batchId,
                    'Failed to download file from iLovePDF API !'
                );
            }
        }
    }
}
