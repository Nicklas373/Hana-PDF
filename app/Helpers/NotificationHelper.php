<?php
namespace App\Helpers;

use App\Models\appLogModel;
use App\Models\notifyLogModel;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class NotificationHelper
{
    public static function instance()
    {
         return new NotificationHelper();
    }

    function sendErrNotify($procFile, $fileSize, $processId, $status, $proc, $errReason, $errCode) {
        if ($procFile == null || $procFile == "") {
            $newProcFile = 'null';
        } else {
            $newProcFile = $procFile;
        }

        if ($fileSize == null || $fileSize == "") {
            $newFileSize = '0.0 MB';
        } else if (strstr($fileSize, "MB")) {
            $newFileSize = $fileSize;
        } else {
            $newFileSize = AppHelper::instance()->convert($fileSize, "MB");
        }

        if ($proc == "compress") {
            $newRoute = 'api/v2/core/compress';
        } else if ($proc == "convert" || $proc == "cnvToxlsx" || $proc == "cnvTopptx" || $proc == "cnvToDocx" || $proc == "cnvToImg" || $proc == "pdfToImg") {
            $newRoute = 'api/v2/core/convert';
        } else if ($proc == "htmltopdf") {
            $newRoute = 'api/v2/core/html';
        } else if ($proc == "merge") {
            $newRoute = 'api/v2/core/merge';
        } else if ($proc == "split" || $proc == "split_delete") {
            $newRoute = 'api/v2/core/split';
        } else if ($proc == "watermark") {
            $newRoute = 'api/v2/core/watermark';
        } else {
            $newRoute = 'undefined';
        }

        $CurrentTime = AppHelper::instance()->getCurrentTimeZone();
        $uuid = AppHelper::Instance()->generateUniqueUuid(notifyLogModel::class, 'processId');
        $message = "<b>HANA API Alert</b>".
                    "\n\nStatus: <b>".$status."</b>".
                    "\nStart At: <b>".$CurrentTime."</b>".
                    "\nEnvironment: <b>".env('APP_ENV')."</b>".
                    "\n\nServices: <b>Backend Services</b>".
                    "\nSource: <b>".env('APP_URL')."</b>".
                    "\nEndpoint: <b>".$newRoute."</b>".
                    "\n\nProcess: <b>".$proc."</b>".
                    "\nGroup Id: <b>".$processId."</b>".
                    "\nType: <b>Process Error</b>".
                    "\n\nFilename: <b>".$newProcFile."</b>".
                    "\nFileSize: <b>".$newFileSize."</b>".
                    "\n\nError Reason: <b>".$errReason."</b>".
                    "\nError Log: <pre><code>".$errCode."</code></pre>";
        try {
            $response = Telegram::sendMessage([
                'chat_id' => env('TELEGRAM_CHAT_ID'),
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);
            $messageId = $response->getMessageId();
            appLogModel::create([
                'processId' => $uuid,
                'groupId' => $processId,
                'errReason' => null,
                'errStatus' => null
            ]);
            notifyLogModel::create([
                'processId' => $uuid,
                'notifyName' => 'Telegram SDK',
                'notifyResult' => true,
                'notifyMessage' => 'Message has been sent !',
                'notifyResponse' => $response
            ]);
        } catch (\Telegram\Bot\Exceptions\TelegramResponseException $e) {
            if ($e->getHttpStatusCode() == null) {
                $httpStatus = null;
            } else {
                $httpStatus = $e->getHttpStatusCode();
            }
            appLogModel::where('processId', '=', $uuid)
                ->update([
                'errReason' => 'TelegramResponseException',
                'errStatus' => $errReason
                ]);
            notifyLogModel::where('processId', '=', $uuid)
                ->update([
                    'notifyResult' => false,
                    'notifyMessage' => 'TelegramResponseException',
                    'notifyResponse' => $e->getMessage().' | '.$httpStatus.' | '.$e->getErrorType()
                ]);
        } catch (\Exception $e) {
            appLogModel::where('processId', '=', $uuid)
                ->update([
                    'errReason' => 'TelegramResponseException',
                    'errStatus' => $errReason
                ]);
            notifyLogModel::where('processId', '=', $uuid)
                ->update([
                    'notifyResult' => false,
                    'notifyMessage' => 'Unexpected handling exception !',
                    'notifyResponse' => $e->getMessage()
                ]);
        }
    }

    function sendRouteErrNotify($processId, $status, $errReason, $errCode) {
        $CurrentTime = AppHelper::instance()->getCurrentTimeZone();
        $uuid = AppHelper::Instance()->generateUniqueUuid(notifyLogModel::class, 'processId');
        $message = "<b>HANA API Alert</b>
                    \nStatus: <b>".$status."</b>".
                    "\nStart At: <b>".$CurrentTime."</b>".
                    "\nEnvironment: <b>".env('APP_ENV')."</b>".
                    "\n\nServices: <b>Backend Services</b>".
                    "\nSource: <b>".env('APP_URL')."</b>".
                    "\nGroup Id: <b>".$processId."</b>".
                    "\n\nError Type: <b>Route Error</b>".
                    "\nError Reason: <b>".$errReason."</b>".
                    "\nError Log: <pre><code>".$errCode."</code></pre>";
        try {
            $response = Telegram::sendMessage([
                'chat_id' => env('TELEGRAM_CHAT_ID'),
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);
            $messageId = $response->getMessageId();
            appLogModel::create([
                'processId' => $uuid,
                'groupId' => $processId,
                'errReason' => null,
                'errStatus' => null
            ]);
            notifyLogModel::create([
                'processId' => $uuid,
                'notifyName' => 'Telegram SDK',
                'notifyResult' => true,
                'notifyMessage' => 'Message has been sent !',
                'notifyResponse' => $response
            ]);
        } catch (\Telegram\Bot\Exceptions\TelegramResponseException $e) {
            if ($e->getHttpStatusCode() == null) {
                $httpStatus = null;
            } else {
                $httpStatus = $e->getHttpStatusCode();
            }
            appLogModel::where('processId', '=', $uuid)
                ->update([
                    'errReason' => 'TelegramResponseException',
                    'errStatus' => $e->getMessage().' | '.$httpStatus.' | '.$e->getErrorType()
                ]);
            notifyLogModel::where('processId', '=', $uuid)
                ->update([
                    'notifyResult' => false,
                    'notifyMessage' => 'TelegramResponseException',
                    'notifyResponse' => $e->getMessage().' | '.$httpStatus.' | '.$e->getErrorType()
                ]);
        } catch (\Exception $e) {
            appLogModel::where('processId', '=', $uuid)
                ->update([
                    'errReason' => 'Unexpected handling exception !',
                    'errStatus' => $e->getMessage()
                ]);
            notifyLogModel::where('processId', '=', $uuid)
                ->update([
                    'notifyResult' => false,
                    'notifyMessage' => 'Unexpected handling exception !',
                    'notifyResponse' => $e->getMessage()
                ]);
        }
    }

    function sendSchedErrNotify($schedName, $schedRuntime, $processId , $status, $errReason, $errCode) {
        $CurrentTime = AppHelper::instance()->getCurrentTimeZone();
        $uuid = AppHelper::Instance()->generateUniqueUuid(notifyLogModel::class, 'processId');
        $message = "<b>HANA API Alert</b>
                    \nStatus: <b>".$status."</b>".
                    "\nStart At: <b>".$CurrentTime."</b>".
                    "\nEnvironment: <b>".env('APP_ENV')."</b>".
                    "\n\nServices: <b>Backend Services</b>".
                    "\nSource: <b>".env('APP_URL')."</b>".
                    "\nJobs Name: <b>".$schedName."</b>".
                    "\n\nProcess: <b>".$schedRuntime."</b>".
                    "\nGroup Id: <b>".$processId."</b>".
                    "\nType: <b>Jobs Error</b>".
                    "\n\nError Reason: <b>".$errReason."</b>".
                    "\nError Log: <pre><code>".$errCode."</code></pre>";
        try {
            $response = Telegram::sendMessage([
                'chat_id' => env('TELEGRAM_CHAT_ID'),
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);
            $messageId = $response->getMessageId();
            appLogModel::create([
                'processId' => $uuid,
                'groupId' => $processId,
                'errReason' => null,
                'errStatus' => null
            ]);
            notifyLogModel::create([
                'processId' => $processId,
                'notifyName' => 'Telegram SDK',
                'notifyResult' => true,
                'notifyMessage' => 'Message has been sent !',
                'notifyResponse' => $response
            ]);
        } catch (\Telegram\Bot\Exceptions\TelegramResponseException $e) {
            if ($e->getHttpStatusCode() == null) {
                $httpStatus = null;
            } else {
                $httpStatus = $e->getHttpStatusCode();
            }
            appLogModel::where('processId', '=', $uuid)
                ->update([
                    'errReason' => 'TelegramResponseException',
                    'errStatus' => $e->getMessage().' | '.$httpStatus.' | '.$e->getErrorType()
                ]);
            notifyLogModel::where('processId', '=', $uuid)
                ->update([
                    'notifyResult' => false,
                    'notifyMessage' => 'TelegramResponseException',
                    'notifyResponse' => $e->getMessage().' | '.$httpStatus.' | '.$e->getErrorType()
                ]);
        } catch (\Exception $e) {
            appLogModel::where('processId', '=', $uuid)
                ->update([
                    'errReason' => 'Unexpected handling exception !',
                    'errStatus' => $e->getMessage()
                ]);
            notifyLogModel::where('processId', '=', $uuid)
                ->update([
                    'notifyResult' => false,
                    'notifyMessage' => 'Unexpected handling exception !',
                    'notifyResponse' => $e->getMessage()
                ]);
        }
    }

    function sendDailyTaskNotify($compTotalScs, $compTotalErr, $cnvTotalScs, $cnvTotalErr, $htmlTotalScs, $htmlTotalErr,
                                $mergeTotalScs, $mergeTotalErr, $splitTotalScs, $splitTotalErr, $watermarkTotalScs,
                                $watermarkTotalErr, $taskStatus, $errReason, $errCode) {
        $CurrentTime = AppHelper::instance()->getCurrentTimeZone();
        $uuid = AppHelper::Instance()->generateUniqueUuid(notifyLogModel::class, 'processId');
        $Suuid = AppHelper::Instance()->generateUniqueUuid(appLogModel::class, 'groupId');
        if ($taskStatus) {
            $CountTotalScsProc = $compTotalScs + $cnvTotalScs + $htmlTotalScs + $mergeTotalScs + $splitTotalScs + $watermarkTotalScs;
            $CountTotalErrProc = $compTotalErr + $cnvTotalErr + $htmlTotalErr + $mergeTotalErr + $splitTotalErr + $watermarkTotalErr;
            $message = "<b>HANA API Daily Report Alert</b>".
                        "\n\nReported At: <b>".$CurrentTime."</b>".
                        "\nReport Status: <b>Success"."</b>".
                        "\nEnvironment: <b>".env('APP_ENV')."</b>".
                        "\nServices: <b>Backend Services</b>".
                        "\nSource: <b>".env('APP_URL')."</b>".
                        "\n\n<b>Compress Task\n</b>Success: <b>".$compTotalScs."</b>\nError: <b>".$compTotalErr."</b>".
                        "\n\n<b>Convert Task\n</b>Success: <b>".$cnvTotalScs."</b>\nError: <b>".$cnvTotalErr."</b>".
                        "\n\n<b>HTMLtoPDF Task\n</b>Success: <b>".$htmlTotalScs."</b>\nError: <b>".$htmlTotalErr."</b>".
                        "\n\n<b>Merge Task\n</b>Success: <b>".$mergeTotalScs."</b>\nError: <b>".$mergeTotalErr."</b>".
                        "\n\n<b>Split Task\n</b>Success: <b>".$splitTotalScs."</b>\nError: <b>".$splitTotalErr."</b>".
                        "\n\n<b>Watermark Task\n</b>Success: <b>".$watermarkTotalScs."</b>\nError: <b>".$watermarkTotalErr."</b>".
                        "\n\n<b>Total Task\n</b>Success: <b>".$CountTotalScsProc."</b>\nError: <b>".$CountTotalErrProc."</b>";
        } else {
            $message = "<b>HANA API Daily Report Alert</b>".
                        "\n\nReported At: <b>".$CurrentTime."</b>".
                        "\nReport Status: <b>FAIL"."</b>".
                        "\nEnvironment: <b>".env('APP_ENV')."</b>".
                        "\nServices: <b>Backend Services</b>".
                        "\nSource: <b>".env('APP_URL')."</b>".
                        "\n\nError Reason: <b>".$errReason."</b>".
                        "\nError Log: <pre><code>".$errCode."</code></pre>";
        }
        try {
            $response = Telegram::sendMessage([
                'chat_id' => env('TELEGRAM_REPORT_ID'),
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);
            $messageId = $response->getMessageId();
            appLogModel::create([
                'processId' => $uuid,
                'groupId' => $Suuid,
                'errReason' => $errReason,
                'errStatus' => null
            ]);
            notifyLogModel::create([
                'processId' => $uuid,
                'notifyName' => 'Telegram SDK',
                'notifyResult' => true,
                'notifyMessage' => 'Message has been sent !',
                'notifyResponse' => $response
            ]);
        } catch (\Telegram\Bot\Exceptions\TelegramResponseException $e) {
            if ($e->getHttpStatusCode() == null) {
                $httpStatus = null;
            } else {
                $httpStatus = $e->getHttpStatusCode();
            }
            appLogModel::where('groupId', '=', $Suuid)
                ->update([
                    'errReason' => 'TelegramResponseException',
                    'errStatus' => $e->getMessage().' | '.$httpStatus.' | '.$e->getErrorType()
                ]);
            notifyLogModel::where('groupId', '=', $Suuid)
                ->update([
                    'notifyResult' => false,
                    'notifyMessage' => 'TelegramResponseException',
                    'notifyResponse' => $e->getMessage().' | '.$httpStatus.' | '.$e->getErrorType()
                ]);
        } catch (\Exception $e) {
            appLogModel::where('groupId', '=', $Suuid)
                ->update([
                    'errReason' => 'Unexpected handling exception !',
                    'errStatus' => $e->getMessage()
                ]);
            notifyLogModel::where('groupId', '=', $Suuid)
                ->update([
                    'notifyResult' => false,
                    'notifyMessage' => 'Unexpected handling exception !',
                    'notifyResponse' => $e->getMessage()
                ]);
        }
    }

    function sendErrGlobalNotify($processEndpoint, $processName, $status, $processId, $errReason, $errCode) {
        $CurrentTime = AppHelper::instance()->getCurrentTimeZone();
        $uuid = AppHelper::Instance()->generateUniqueUuid(notifyLogModel::class, 'processId');
        $message = "<b>HANA API Alert</b>
                    \nStatus: <b>".$status."</b>".
                    "\nStart At: <b>".$CurrentTime."</b>".
                    "\nEnvironment: <b>".env('APP_ENV')."</b>".
                    "\n\nServices: <b>Backend Services</b>".
                    "\nSource: <b>".env('APP_URL')."</b>".
                    "\nEndpoint: <b>".$processEndpoint."</b>".
                    "\n\nProcess: <b>".$processName."</b>".
                    "\nGroup Id: <b>".$processId."</b>".
                    "\nType: <b>Universal Notify</b>".
                    "\n\nError Reason: <b>".$errReason."</b>".
                    "\nError Log: <pre><code>".$errCode."</code></pre>";
        try {
            $response = Telegram::sendMessage([
                'chat_id' => env('TELEGRAM_CHAT_ID'),
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);
            $messageId = $response->getMessageId();
            appLogModel::create([
                'processId' => $processId,
                'groupId' => $uuid,
                'errReason' => null,
                'errStatus' => null
            ]);
            notifyLogModel::create([
                'processId' => $processId,
                'notifyName' => 'Telegram SDK',
                'notifyResult' => true,
                'notifyMessage' => 'Message has been sent !',
                'notifyResponse' => $response
            ]);
        } catch (\Telegram\Bot\Exceptions\TelegramResponseException $e) {
            if ($e->getHttpStatusCode() == null) {
                $httpStatus = null;
            } else {
                $httpStatus = $e->getHttpStatusCode();
            }
            appLogModel::where('processId', '=', $uuid)
                ->update([
                    'errReason' => 'TelegramResponseException',
                    'errStatus' => $e->getMessage().' | '.$httpStatus.' | '.$e->getErrorType()
                ]);
            notifyLogModel::where('processId', '=', $uuid)
                ->update([
                    'notifyResult' => false,
                    'notifyMessage' => 'TelegramResponseException',
                    'notifyResponse' => $e->getMessage().' | '.$httpStatus.' | '.$e->getErrorType()
                ]);
        } catch (\Exception $e) {
            appLogModel::where('processId', '=', $uuid)
                ->update([
                    'errReason' => 'Unexpected handling exception !',
                    'errStatus' => $e->getMessage()
                ]);
            notifyLogModel::where('processId', '=', $uuid)
                ->update([
                    'notifyResult' => false,
                    'notifyMessage' => 'Unexpected handling exception !',
                    'notifyResponse' => $e->getMessage()
                ]);
        }
    }

    function sendVersioningErrNotify($versioningFE, $versioningGitFE, $versioningBE , $versioningGitBE, $status, $processId, $errReason, $errCode) {
        $CurrentTime = AppHelper::instance()->getCurrentTimeZone();
        $uuid = AppHelper::Instance()->generateUniqueUuid(notifyLogModel::class, 'processId');
        $message = "<b>HANA API Alert</b>".
                    "\nStatus: <b>".$status."</b>".
                    "\nStart At: <b>".$CurrentTime."</b>".
                    "\nEnvironment: <b>".env('APP_ENV')."</b>".
                    "\n\nServices: <b>Backend Services</b>".
                    "\nSource: <b>".env('APP_URL')."</b>".
                    "\nEndpoint: <b>api/v1/version</b>".
                    "\n\nProcess: <b>Versioning</b>".
                    "\nGroup Id: <b>".$processId."</b>".
                    "\nType: <b>Versioning Check</b>".
                    "\n\nBE Version: <b>".$versioningBE."</b>".
                    "\nBE Version GIT: <b>".$versioningGitBE."</b>".
                    "\nFE Version: <b>".$versioningFE."</b>".
                    "\nFE Version GIT: <b>".$versioningGitFE."</b>".
                    "\n\nError Reason: <b>".$errReason."</b>".
                    "\nError Log: <pre><code>".$errCode."</code></pre>";
        try {
            $response = Telegram::sendMessage([
                'chat_id' => env('TELEGRAM_CHAT_ID'),
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);
            $messageId = $response->getMessageId();
            appLogModel::create([
                'processId' => $processId,
                'groupId' => $uuid,
                'errReason' => null,
                'errStatus' => null
            ]);
            notifyLogModel::create([
                'processId' => $processId,
                'notifyName' => 'Telegram SDK',
                'notifyResult' => true,
                'notifyMessage' => 'Message has been sent !',
                'notifyResponse' => $response
            ]);
        } catch (\Telegram\Bot\Exceptions\TelegramResponseException $e) {
            if ($e->getHttpStatusCode() == null) {
                $httpStatus = null;
            } else {
                $httpStatus = $e->getHttpStatusCode();
            }
            appLogModel::where('processId', '=', $uuid)
                ->update([
                    'errReason' => 'TelegramResponseException',
                    'errStatus' => $e->getMessage().' | '.$httpStatus.' | '.$e->getErrorType()
                ]);
            notifyLogModel::where('processId', '=', $uuid)
                ->update([
                    'notifyResult' => false,
                    'notifyMessage' => 'TelegramResponseException',
                    'notifyResponse' => $e->getMessage().' | '.$httpStatus.' | '.$e->getErrorType()
                ]);
        } catch (\Exception $e) {
            appLogModel::where('processId', '=', $uuid)
                ->update([
                    'errReason' => 'Unexpected handling exception !',
                    'errStatus' => $e->getMessage()
                ]);
            notifyLogModel::where('processId', '=', $uuid)
                ->update([
                    'notifyResult' => false,
                    'notifyMessage' => 'Unexpected handling exception !',
                    'notifyResponse' => $e->getMessage()
                ]);
        }
    }
}
