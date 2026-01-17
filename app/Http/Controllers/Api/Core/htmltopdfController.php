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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class htmltopdfController extends Controller
{
    public function html(Request $request) {
        $validator = Validator::make($request->all(),[
		    'urlToPDF' => ['required', 'string'],
            'urlMarginValue' => ['required', 'numeric'],
            'urlPageOrientationValue' => ['required', 'in:landscape,portrait'],
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
            $pdfMargin = $request->post('urlMarginValue');
            $pdfOrientation = $request->post('urlPageOrientationValue');
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
                'urlSinglePage' => null,
                'urlSize' => null,
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
                $newUrl = $pdfUrl;
            } else {
                if (AppHelper::Instance()->checkWebAvailable('https://'.$pdfUrl)) {
                    $newUrl = 'https://'.$pdfUrl;
                } else if (AppHelper::Instance()->checkWebAvailable('http://'.$pdfUrl)) {
                    $newUrl = 'http://'.$pdfUrl;
                } else if (AppHelper::Instance()->checkWebAvailable('www.'.$pdfUrl)) {
                    $newUrl = 'www.'.$pdfUrl;
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
            $asposeToken = AppHelper::instance()->getAsposeToken(env('ASPOSE_CLOUD_CLIENT_ID'), env('ASPOSE_CLOUD_TOKEN'));
            if ($asposeToken) {
                try {
                   $isLandscape = ($pdfOrientation === 'landscape') ? 'true' : 'false';
                   $asposeAPI = Http::timeout(300)
                    ->withToken($asposeToken)
                    ->withHeaders([
                        'Accept' => 'application/json',
                    ])
                    ->withOptions([
                        'query' => [
                            'url'    => $newUrl,
                            'isLandscape' => $isLandscape,
                            'marginLeft'    => $pdfMargin,
                            'marginRight'   => $pdfMargin,
                            'marginTop'     => $pdfMargin,
                            'marginBottom'  => $pdfMargin,
                        ],
                    ])
                    ->get("https://api.aspose.cloud/v3.0/pdf/create/web");
                    if ($asposeAPI->successful()) {
                        $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                        $duration = $end->diff($startProc);
                        Storage::disk('minio')->put(
                            $pdfProcessed_Location.'/'.$pdfDefaultFileName.'.pdf',
                            $asposeAPI->body()
                        );
                    } else {
                        $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                        $duration = $end->diff($startProc);
                        appLogModel::where('groupId', '=', $batchId)
                            ->update([
                                'errReason' => $asposeAPI->body(),
                                'errStatus' => 'Aspose API v3.0 - html failure'
                            ]);
                        htmlModel::where('groupId', '=', $batchId)
                            ->update([
                                'result' => false,
                                'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                                'procDuration' => $duration->s.' seconds'
                            ]);
                        NotificationHelper::Instance()->sendErrNotify(
                            $pdfDefaultFileName.'.pdf',
                            null,
                            $batchId,
                            'FAIL',
                            'htmltopdf',
                            'Aspose API v3.0 - html failure',
                            $asposeAPI->body()
                        );
                        return $this->returnDataMessage(
                            400,
                            'PDF Convert failed !',
                            null,
                            $asposeAPI->body(),
                            null,
                            $batchId,
                            'Aspose API v3.0 - html failure'
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
                    htmlModel::where('groupId', '=', $batchId)
                        ->update([
                            'result' => false,
                            'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                            'procDuration' => $duration->s.' seconds'
                        ]);
                    NotificationHelper::Instance()->sendErrNotify(
                        $pdfDefaultFileName.'.pdf',
                        null,
                        $batchId,
                        'FAIL',
                        'htmltopdf',
                        'Guzzle HTTP failure',
                        $e->getMessage()
                    );
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
                htmlModel::where('groupId', '=', $batchId)
                    ->update([
                        'result' => false,
                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                        'procDuration' => $duration->s.' seconds'
                    ]);
                NotificationHelper::Instance()->sendErrNotify(
                    $currentFileName,
                    null,
                    $batchId,
                    'FAIL',
                    'htmltopdf',
                    'Failed to generated Aspose Token !',
                    'Aspose API v3.0 - html failure'
                );
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
            if (Storage::disk('minio')->exists($pdfProcessed_Location.'/'.$pdfDefaultFileName.'.pdf')) {
                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                $duration = $end->diff($startProc);
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
                        'errStatus' => 'Failed to download file from S3 Object Storage !'
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
                    'Failed to download file from S3 Object Storage !',
                    null
                );
                return $this->returnDataMessage(
                    400,
                    'HTML To PDF failed !',
                    null,
                    null,
                    null,
                    $batchId,
                    'Failed to download file from S3 Object Storage !'
                );
            }
        }
    }
}
