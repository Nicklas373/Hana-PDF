<?php

namespace App\Http\Controllers\Api\Core;

use App\Helpers\AppHelper;
use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Ilovepdf\Ilovepdf;
use Ilovepdf\HtmlpdfTask;
use Ilovepdf\Exceptions\StartException;
use Ilovepdf\Exceptions\AuthException;
use Ilovepdf\Exceptions\UploadException;
use Ilovepdf\Exceptions\ProcessException;
use Ilovepdf\Exceptions\DownloadException;
use Ilovepdf\Exceptions\TaskException;
use Ilovepdf\Exceptions\PathException;

class htmltopdfController extends Controller
{
    public function html(Request $request) {
        $validator = Validator::make($request->all(),[
		    'urlToPDF' => 'required',
	    ]);

        $uuid = AppHelper::Instance()->get_guid();

        // Carbon timezone
        date_default_timezone_set('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');
        $startProc = $now->format('Y-m-d H:i:s');

        if($validator->fails()) {
            try {
                DB::table('appLogs')->insert([
                    'processId' => $uuid,
                    'errReason' => $validator->messages(),
                    'errApiReason' => null
                ]);
                NotificationHelper::Instance()->sendErrNotify('','', $uuid, 'FAIL', 'htmlToPdf', 'Failed to convert HTML to PDF !',$validator->messages());
                return $this->returnCoreMessage(
                    200,
                    'Failed to convert HTML to PDF !',
                    null,
                    null,
                    'htmlToPdf',
                    $uuid,
                    null,
                    null,
                    null,
                    $validator->errors()->all()
                );
            } catch (QueryException $ex) {
                NotificationHelper::Instance()->sendErrNotify('','', $uuid, 'FAIL', 'htmlToPdf', 'Database connection error !',$ex->getMessage());
                return $this->returnCoreMessage(
                    200,
                    'Database connection error !',
                    null,
                    null,
                    'htmlToPdf',
                    $uuid,
                    null,
                    null,
                    null,
                    $ex->getMessage()
                );
            } catch (\Exception $e) {
                NotificationHelper::Instance()->sendErrNotify(null, null, $uuid, 'FAIL', 'htmlToPdf', 'Eloquent transaction error !', $e->getMessage());
                return $this->returnCoreMessage(
                    200,
                    'Eloquent transaction error !',
                    null,
                    null,
                    'htmlToPdf',
                    $uuid,
                    null,
                    null,
                    null,
                    $e->getMessage()
                );
            }
        } else {
            $start = Carbon::parse($startProc);
            $str = rand(1000,10000000);
            $pdfEncKey = bin2hex(random_bytes(16));
            $pdfDefaultFileName ='pdf_convert_'.substr(md5(uniqid($str)), 0, 8);
            $pdfProcessed_Location = env('PDF_DOWNLOAD');
            $pdfUpload_Location = env('PDF_UPLOAD');
            $pdfUrl = $request->post('urlToPDF');
            $newUrl = '';
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
                    try {
                        DB::table('appLogs')->insert([
                            'processId' => $uuid,
                            'errReason' => null,
                            'errApiReason' => null
                        ]);
                        DB::table('pdfHtml')->insert([
                            'urlName' => $request->post('urlToPDF'),
                            'result' => false,
                            'processId' => $uuid,
                            'procStartAt' => $startProc,
                            'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                            'procDuration' =>  $duration->s.' seconds'
                        ]);
                        DB::table('appLogs')
                            ->where('processId', '=', $uuid)
                            ->update([
                                'processId' => $uuid,
                                'errReason' => '404',
                                'errApiReason' => 'Webpage are not available or not valid'
                        ]);
                        NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'FAIL', 'htmlToPdf', 'HTML To PDF Conversion Failed !', 'Webpage are not available or not valid');
                        return $this->returnCoreMessage(
                            200,
                            'HTML To PDF Conversion Failed !',
                            $pdfUrl,
                            null,
                            'htmlToPdf',
                            $uuid,
                            null,
                            null,
                            null,
                            'Webpage are not available or not valid'
                        );
                    } catch (QueryException $ex) {
                        NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'FAIL', 'htmlToPdf', 'Database connection error !', $ex->getMessage());
                        return $this->returnCoreMessage(
                            200,
                            'Database connection error !',
                            $pdfUrl,
                            null,
                            'htmlToPdf',
                            $uuid,
                            null,
                            null,
                            null,
                            $ex->getMessage()
                        );
                    } catch (\Exception $e) {
                        NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'FAIL', 'htmlToPdf', 'Eloquent transaction error !', $e->getMessage());
                        return $this->returnCoreMessage(
                            200,
                            'Eloquent transaction error !',
                            $pdfUrl,
                            null,
                            'htmlToPdf',
                            $uuid,
                            null,
                            null,
                            null,
                            $e->getMessage()
                        );
                    }
                }
            }
            try {
                $ilovepdfTask = new HtmlpdfTask(env('ILOVEPDF_PUBLIC_KEY'),env('ILOVEPDF_SECRET_KEY'));
                $ilovepdfTask->setEncryptKey($pdfEncKey);
                $ilovepdfTask->setEncryption(true);
                $pdfFile = $ilovepdfTask->addUrl($newUrl);
                $ilovepdfTask->setOutputFileName($pdfDefaultFileName);
                $ilovepdfTask->execute();
                $ilovepdfTask->download(Storage::disk('local')->path('public/'.$pdfProcessed_Location));
            } catch (StartException $e) {
                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                $duration = $end->diff($startProc);
                try {
                    DB::table('appLogs')->insert([
                        'processId' => $uuid,
                        'errReason' => null,
                        'errApiReason' => null
                    ]);
                    DB::table('pdfHtml')->insert([
                        'urlName' => $request->post('urlToPDF'),
                        'result' => false,
                        'processId' => $uuid,
                        'procStartAt' => $startProc,
                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                        'procDuration' =>  $duration->s.' seconds'
                    ]);
                    DB::table('appLogs')
                        ->where('processId', '=', $uuid)
                        ->update([
                            'errReason' => 'iLovePDF API Error !, Catch on StartException',
                            'errApiReason' => $e->getMessage()
                    ]);
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'FAIL', 'htmlToPdf', 'iLovePDF API Error !, Catch on StartException', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Failed to convert HTML to PDF !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                } catch (QueryException $ex) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Database connection error !', $ex->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Database connection error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $ex->getMessage()
                    );
                } catch (\Exception $e) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Eloquent transaction error !', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Eloquent transaction error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                }
            } catch (AuthException $e) {
                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                $duration = $end->diff($startProc);
                try {
                    DB::table('appLogs')->insert([
                        'processId' => $uuid,
                        'errReason' => null,
                        'errApiReason' => null
                    ]);
                    DB::table('pdfHtml')->insert([
                        'urlName' => $request->post('urlToPDF'),
                        'result' => false,
                        'processId' => $uuid,
                        'procStartAt' => $startProc,
                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                        'procDuration' =>  $duration->s.' seconds'
                    ]);
                    DB::table('appLogs')
                        ->where('processId', '=', $uuid)
                        ->update([
                            'errReason' => 'iLovePDF API Error !, Catch on AuthException',
                            'errApiReason' => $e->getMessage()
                    ]);
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'iLovePDF API Error !, Catch on AuthException', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Failed to convert HTML to PDF !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                } catch (QueryException $ex) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Database connection error !', $ex->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Database connection error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $ex->getMessage()
                    );
                } catch (\Exception $e) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Eloquent transaction error !', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Eloquent transaction error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                }
            } catch (UploadException $e) {
                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                $duration = $end->diff($startProc);
                try {
                    DB::table('appLogs')->insert([
                        'processId' => $uuid,
                        'errReason' => null,
                        'errApiReason' => null
                    ]);
                    DB::table('pdfHtml')->insert([
                        'urlName' => $request->post('urlToPDF'),
                        'result' => false,
                        'processId' => $uuid,
                        'procStartAt' => $startProc,
                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                        'procDuration' =>  $duration->s.' seconds'
                    ]);
                    DB::table('appLogs')
                        ->where('processId', '=', $uuid)
                        ->update([
                            'errReason' => 'iLovePDF API Error !, Catch on UploadException',
                            'errApiReason' => $e->getMessage()
                    ]);
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'iLovePDF API Error !, Catch on UploadException', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Failed to convert HTML to PDF !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                } catch (QueryException $ex) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Database connection error !', $ex->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Database connection error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $ex->getMessage()
                    );
                } catch (\Exception $e) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Eloquent transaction error !', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Eloquent transaction error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                }
            } catch (ProcessException $e) {
                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                $duration = $end->diff($startProc);
                try {
                    DB::table('appLogs')->insert([
                        'processId' => $uuid,
                        'errReason' => null,
                        'errApiReason' => null
                    ]);
                    DB::table('pdfHtml')->insert([
                        'urlName' => $request->post('urlToPDF'),
                        'result' => false,
                        'processId' => $uuid,
                        'procStartAt' => $startProc,
                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                        'procDuration' =>  $duration->s.' seconds'
                    ]);
                    DB::table('appLogs')
                        ->where('processId', '=', $uuid)
                        ->update([
                            'errReason' => 'iLovePDF API Error !, Catch on ProcessException',
                            'errApiReason' => $e->getMessage()
                    ]);
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'FAIL', 'htmlToPdf', 'iLovePDF API Error !, Catch on ProcessException', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Failed to convert HTML to PDF !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                } catch (QueryException $ex) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Database connection error !', $ex->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Database connection error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $ex->getMessage()
                    );
                } catch (\Exception $e) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Eloquent transaction error !', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Eloquent transaction error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                }
            } catch (DownloadException $e) {
                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                $duration = $end->diff($startProc);
                try {
                    DB::table('appLogs')->insert([
                        'processId' => $uuid,
                        'errReason' => null,
                        'errApiReason' => null
                    ]);
                    DB::table('pdfHtml')->insert([
                        'urlName' => $request->post('urlToPDF'),
                        'result' => false,
                        'processId' => $uuid,
                        'procStartAt' => $startProc,
                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                        'procDuration' =>  $duration->s.' seconds'
                    ]);
                    DB::table('appLogs')
                        ->where('processId', '=', $uuid)
                        ->update([
                            'processId' => $uuid,
                            'errReason' => 'iLovePDF API Error !, Catch on DownloadException',
                            'errApiReason' => $e->getMessage()
                    ]);
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'iLovePDF API Error !, Catch on DownloadException', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Failed to convert HTML to PDF !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                } catch (QueryException $ex) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Database connection error !', $ex->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Database connection error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $ex->getMessage()
                    );
                } catch (\Exception $e) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Eloquent transaction error !', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Eloquent transaction error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                }
            } catch (TaskException $e) {
                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                $duration = $end->diff($startProc);
                try {
                    DB::table('appLogs')->insert([
                        'processId' => $uuid,
                        'errReason' => null,
                        'errApiReason' => null
                    ]);
                    DB::table('pdfHtml')->insert([
                        'urlName' => $request->post('urlToPDF'),
                        'result' => false,
                        'processId' => $uuid,
                        'procStartAt' => $startProc,
                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                        'procDuration' =>  $duration->s.' seconds'
                    ]);
                    DB::table('appLogs')
                        ->where('processId', '=', $uuid)
                        ->update([
                            'errReason' => 'iLovePDF API Error !, Catch on TaskException',
                            'errApiReason' => $e->getMessage()
                    ]);
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'iLovePDF API Error !, Catch on TaskException', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Failed to convert HTML to PDF !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                } catch (QueryException $ex) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Database connection error !', $ex->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Database connection error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $ex->getMessage()
                    );
                } catch (\Exception $e) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Eloquent transaction error !', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Eloquent transaction error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                }
            } catch (PathException $e) {
                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                $duration = $end->diff($startProc);
                try {
                    DB::table('appLogs')->insert([
                        'processId' => $uuid,
                        'errReason' => null,
                        'errApiReason' => null
                    ]);
                    DB::table('pdfHtml')->insert([
                        'urlName' => $request->post('urlToPDF'),
                        'result' => false,
                        'processId' => $uuid,
                        'procStartAt' => $startProc,
                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                        'procDuration' =>  $duration->s.' seconds'
                    ]);
                    DB::table('appLogs')
                        ->where('processId', '=', $uuid)
                        ->update([
                            'processId' => $uuid,
                            'errReason' => 'iLovePDF API Error !, Catch on PathException',
                            'errApiReason' => $e->getMessage()
                    ]);
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPDF', 'FAIL', 'iLovePDF API Error !, Catch on PathException', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Failed to convert HTML to PDF !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                } catch (QueryException $ex) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Database connection error !', $ex->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Database connection error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $ex->getMessage()
                    );
                } catch (\Exception $e) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Eloquent transaction error !', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Eloquent transaction error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                }
            } catch (\Exception $e) {
                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                $duration = $end->diff($startProc);
                try {
                    DB::table('appLogs')->insert([
                        'processId' => $uuid,
                        'errReason' => null,
                        'errApiReason' => null
                    ]);
                    DB::table('pdfHtml')->insert([
                        'urlName' => $request->post('urlToPDF'),
                        'result' => false,
                        'processId' => $uuid,
                        'procStartAt' => $startProc,
                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                        'procDuration' =>  $duration->s.' seconds'
                    ]);
                    DB::table('appLogs')
                        ->where('processId', '=', $uuid)
                        ->update([
                            'errReason' => 'iLovePDF API Error !, Catch on Exception',
                            'errApiReason' => $e->getMessage()
                    ]);
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'iLovePDF API Error !, Catch on Exception', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Failed to convert HTML to PDF !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                } catch (QueryException $ex) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Database connection error !', $ex->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Database connection error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $ex->getMessage()
                    );
                } catch (\Exception $e) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Eloquent transaction error !', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Eloquent transaction error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                }
            }
            if (file_exists(Storage::disk('local')->path('public/'.$pdfProcessed_Location.'/'.$pdfDefaultFileName.'.pdf'))) {
                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                $duration = $end->diff($startProc);
                try {
                    DB::table('appLogs')->insert([
                        'processId' => $uuid,
                        'errReason' => null,
                        'errApiReason' => null
                    ]);
                    DB::table('pdfHtml')->insert([
                        'urlName' => $request->post('urlToPDF'),
                        'result' => true,
                        'processId' => $uuid,
                        'procStartAt' => $startProc,
                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                        'procDuration' =>  $duration->s.' seconds'
                    ]);
                    DB::table('appLogs')
                        ->where('processId', '=', $uuid)
                        ->update([
                            'processId' => $uuid,
                            'errReason' => null,
                            'errApiReason' => null
                    ]);
                    return $this->returnCoreMessage(
                        200,
                        'OK',
                        $pdfUrl,
                        Storage::disk('local')->url($pdfProcessed_Location.'/'.$pdfDefaultFileName.'.pdf'),
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        null
                    );
                } catch (QueryException $ex) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Database connection error !', $ex->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Database connection error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $ex->getMessage()
                    );
                } catch (\Exception $e) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Eloquent transaction error !', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Eloquent transaction error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                }
            } else {
                $end = Carbon::parse(AppHelper::instance()->getCurrentTimeZone());
                $duration = $end->diff($startProc);
                try {
                    DB::table('appLogs')->insert([
                        'processId' => $uuid,
                        'errReason' => null,
                        'errApiReason' => null
                    ]);
                    DB::table('pdfHtml')->insert([
                        'urlName' => $request->post('urlToPDF'),
                        'result' => false,
                        'processId' => $uuid,
                        'procStartAt' => $startProc,
                        'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                        'procDuration' =>  $duration->s.' seconds'
                    ]);
                    DB::table('appLogs')
                        ->where('processId', '=', $uuid)
                        ->update([
                            'processId' => $uuid,
                            'errReason' => 'Failed to download converted file from iLovePDF API !',
                            'errApiReason' => null
                    ]);
                    NotificationHelper::Instance()->sendErrNotify(null, null, $uuid, 'FAIL', 'HTML To PDF Conversion Failed !', null);
                    return $this->returnCoreMessage(
                        200,
                        'HTML To PDF Conversion Failed !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        null
                    );
                } catch (QueryException $ex) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Database connection error !', $ex->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Database connection error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $ex->getMessage()
                    );
                } catch (\Exception $e) {
                    NotificationHelper::Instance()->sendErrNotify($pdfUrl, null, $uuid, 'htmlToPdf', 'FAIL', 'Eloquent transaction error !', $e->getMessage());
                    return $this->returnCoreMessage(
                        200,
                        'Eloquent transaction error !',
                        $pdfUrl,
                        null,
                        'htmlToPdf',
                        $uuid,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                }
            }
        }
    }
}