<?php

namespace App\Http\Controllers\Api\Data;

use App\Helpers\AppHelper;
use App\Helpers\NotificationHelper;
use App\Models\appLogModel;
use App\Http\Controllers\Controller;
use Ilovepdf\Ilovepdf;

class limitLogController extends Controller
{
    public function getLimit() {
        $uuid = AppHelper::Instance()->generateUniqueUuid(appLogModel::class, 'processId');
        try {
            $ilovepdf = new Ilovepdf(env('ILOVEPDF_PUBLIC_KEY'),env('ILOVEPDF_SECRET_KEY'));
            $remainingFiles = $ilovepdf->getRemainingCredits();
            $totalUsage = 2500 - $remainingFiles;
            return $this->returnLimitMessage(
                200,
                'Request generated',
                $remainingFiles,
                $totalUsage,
                null
            );
        } catch (\Exception $e) {
            NotificationHelper::Instance()->sendErrGlobalNotify('api/v1/ilovepdf/limit', 'Auth', 'FAIL', $uuid,'Unknown Exception', $e->getMessage());
            return $this->returnLimitMessage(
                400,
                'Unknown Exception',
                0,
                0,
                $e->getMessage()
            );
        }
    }
}
