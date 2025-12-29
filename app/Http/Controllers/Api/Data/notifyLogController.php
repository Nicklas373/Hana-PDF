<?php

namespace App\Http\Controllers\Api\Data;

use App\Http\Controllers\Controller;
use App\Models\appLogModel;
use App\Models\fileModel;
use App\Models\jobLogModel;
use App\Models\notifyLogModel;
use App\Models\compressModel;
use App\Models\cnvModel;
use App\Models\htmlModel;
use App\Models\mergeModel;
use App\Models\splitModel;
use App\Models\watermarkModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class notifyLogController extends Controller
{
    public function getLogs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'processId' => 'required|uuid',
            'groupId' => 'uuid',
            'logType' =>  ['required', 'in:app,file,jobs,notify,compress,convert,html,merge,split,watermark'],
        ]);
        if ($validator->fails()) {
            return $this->returnDataMessage(
                400,
                'Validation failed',
                null,
                null,
                null,
                null,
                $validator->messages()->first()
            );
        }
        $logModel = $request->input('logType');
        $processId = $request->input('processId');
        $groupId = $request->input('groupId');
        try {
            if ($logModel == 'app') {
                if ($groupId) {
                    $applog = appLogModel::where('groupId', $groupId)->get();
                } else {
                    $applog = appLogModel::where('processId', $processId)->get();
                }
                $datalog = null;
                $filelog = null;
            } else if ($logModel == 'file') {
                if ($groupId) {
                    return $this->returnDataMessage(
                        400,
                        'Validation failed',
                        null,
                        null,
                        null,
                        'Group ID are not exists on fileModel'
                    );
                } else {
                    $applog = appLogModel::where('processId', $processId)->get();
                    $datalog = null;
                    $filelog = fileModel::where('processId', $processId)->get();
                }
            } else if ($logModel == 'jobs') {
                if ($groupId) {
                    $applog = appLogModel::where('groupId', $groupId)->get();
                    $datalog = jobLogModel::where('groupId', $groupId)->get();
                } else {
                    $applog = appLogModel::where('processId', $processId)->get();
                    $datalog = jobLogModel::where('processId', $processId)->get();
                }
                $filelog = null;
            } else if ($logModel == 'notify') {
                $applog = appLogModel::where('processId', $processId)->get();
                $datalog = notifyLogModel::where('processId', $processId)->get();
                $filelog = null;
            } else if ($logModel == 'compress') {
                if ($groupId) {
                    $applog = appLogModel::where('groupId', $groupId)->get();
                    $datalog = compressModel::where('groupId', $groupId)->get();
                    if (compressModel::where('groupId', $groupId)->exists()) {
                        $fileId = compressModel::where('groupId', $groupId)
                                                ->get()
                                                ->pluck('fileId');
                        if (!empty($fileId)) {
                            $filelog = fileModel::whereIn('fileId', $fileId)->get();
                        } else {
                            $filelog = null;
                        }
                    }
                } else {
                    $applog = appLogModel::where('processId', $processId)->get();
                    $datalog = compressModel::where('processId', $processId)->get();
                    if (compressModel::where('processId', '=', $processId)->exists()) {
                        $fileId = compressModel::where('processId', '=', $processId)
                                                ->get()
                                                ->fileId;
                        $filelog = fileModel::where('fileId', '=', $fileId)->get();
                    } else {
                        $filelog = null;
                    }
                }
            } else if ($logModel == 'convert') {
                if ($groupId) {
                    $applog = appLogModel::where('groupId', $groupId)->get();
                    $datalog = cnvModel::where('groupId', $groupId)->get();
                    if (cnvModel::where('groupId', $groupId)->exists()) {
                        $fileId = cnvModel::where('groupId', $groupId)
                                                ->get()
                                                ->pluck('fileId');
                        if (!empty($fileId)) {
                            $filelog = fileModel::whereIn('fileId', $fileId)->get();
                        } else {
                            $filelog = null;
                        }
                    }
                } else {
                    $applog = appLogModel::where('processId', $processId)->get();
                    $datalog = cnvModel::where('processId', $processId)->get();
                    if (cnvModel::where('processId', '=', $processId)->exists()) {
                        $fileId = cnvModel::where('processId', '=', $processId)
                                                ->get()
                                                ->fileId;
                        $filelog = fileModel::where('fileId', '=', $fileId)->get();
                    } else {
                        $filelog = null;
                    }
                }
            } else if ($logModel == 'html') {
                if ($groupId) {
                    $applog = appLogModel::where('groupId', $groupId)->get();
                    $datalog = htmlModel::where('groupId', $groupId)->get();
                    if (htmlModel::where('groupId', $groupId)->exists()) {
                        $fileId = htmlModel::where('groupId', $groupId)
                                                ->get()
                                                ->pluck('fileId');
                        if (!empty($fileId)) {
                            $filelog = fileModel::whereIn('fileId', $fileId)->get();
                        } else {
                            $filelog = null;
                        }
                    }
                } else {
                    $applog = appLogModel::where('processId', $processId)->get();
                    $datalog = htmlModel::where('processId', $processId)->get();
                    if (htmlModel::where('processId', '=', $processId)->exists()) {
                        $fileId = htmlModel::where('processId', '=', $processId)
                                                ->get()
                                                ->fileId;
                        $filelog = fileModel::where('fileId', '=', $fileId)->get();
                    } else {
                        $filelog = null;
                    }
                }
                $telegramlog = notifyLogModel::where('processId', $processId)->get();
            } else if ($logModel == 'merge') {
                if ($groupId) {
                    $applog = appLogModel::where('groupId', $groupId)->get();
                    $datalog = mergeModel::where('groupId', $groupId)->get();
                    if (mergeModel::where('groupId', $groupId)->exists()) {
                        $fileId = mergeModel::where('groupId', $groupId)
                                                ->get()
                                                ->pluck('fileId');
                        if (!empty($fileId)) {
                            $filelog = fileModel::whereIn('fileId', $fileId)->get();
                        } else {
                            $filelog = null;
                        }
                    }
                } else {
                    $applog = appLogModel::where('processId', $processId)->get();
                    $datalog = mergeModel::where('processId', $processId)->get();
                    if (mergeModel::where('processId', '=', $processId)->exists()) {
                        $fileId = mergeModel::where('processId', '=', $processId)
                                                ->get()
                                                ->fileId;
                        $filelog = fileModel::where('fileId', '=', $fileId)->get();
                    } else {
                        $filelog = null;
                    }
                }
            } else if ($logModel == 'split') {
                if ($groupId) {
                    $applog = appLogModel::where('groupId', $groupId)->get();
                    $datalog = splitModel::where('groupId', $groupId)->get();
                    if (splitModel::where('groupId', $groupId)->exists()) {
                        $fileId = splitModel::where('groupId', $groupId)
                                                ->get()
                                                ->pluck('fileId');
                        if (!empty($fileId)) {
                            $filelog = fileModel::whereIn('fileId', $fileId)->get();
                        } else {
                            $filelog = null;
                        }
                    }
                } else {
                    $applog = appLogModel::where('processId', $processId)->get();
                    $datalog = splitModel::where('processId', $processId)->get();
                    if (splitModel::where('processId', '=', $processId)->exists()) {
                        $fileId = splitModel::where('processId', '=', $processId)
                                                ->get()
                                                ->fileId;
                        $filelog = fileModel::where('fileId', '=', $fileId)->get();
                    } else {
                        $filelog = null;
                    }
                }
            } else if ($logModel == 'watermark') {
                if ($groupId) {
                    $applog = appLogModel::where('groupId', $groupId)->get();
                    $datalog = watermarkModel::where('groupId', $groupId)->get();
                    if (watermarkModel::where('groupId', $groupId)->exists()) {
                        $fileId = watermarkModel::where('groupId', $groupId)
                                                ->get()
                                                ->pluck('fileId');
                        if (!empty($fileId)) {
                            $filelog = fileModel::whereIn('fileId', $fileId)->get();
                        } else {
                            $filelog = null;
                        }
                    }
                } else {
                    $applog = appLogModel::where('processId', $processId)->get();
                    $datalog = watermarkModel::where('processId', $processId)->get();
                    if (watermarkModel::where('processId', '=', $processId)->exists()) {
                        $fileId = watermarkModel::where('processId', '=', $processId)
                                                ->get()
                                                ->fileId;
                        $filelog = fileModel::where('fileId', '=', $fileId)->get();
                    } else {
                        $filelog = null;
                    }
                }
            }
            return $this->returnDataMessage(
                200,
                'Request generated',
                $applog,
                $datalog,
                $filelog,
                $groupId,
                null
            );
        } catch (QueryException $e) {
            return $this->returnDataMessage(
                500,
                'Database connection error !',
                null,
                null,
                null,
                $groupId,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            return $this->returnDataMessage(
                500,
                'Unknown Exception',
                null,
                null,
                null,
                $groupId,
                $e->getMessage()
            );
        }
    }

    public function getAllLogs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logCount' => 'required|int',
            'logResult' => ['required', 'in:true,false'],
            'logType' =>  ['required', 'in:app,file,jobs,notify,compress,convert,html,merge,split,watermark'],
            'logOrder' => ['required', 'in:asc,desc']
        ]);
        if ($validator->fails()) {
            return $this->returnDataMessage(
                400,
                'Validation failed',
                null,
                null,
                null,
                null,
                $validator->messages()->first()
            );
        }
        $logCount = $request->input('logCount');
        $logResult = $request->input('logResult');
        $logModel = $request->input('logType');
        $logOrder = $request->input('logOrder');
        try {
            if ($logModel == 'app') {
                $datalog = appLogModel::orderBy('created_at', $logOrder)->take($logCount)->get();
            } else if ($logModel == 'file') {
                $datalog = fileModel::orderBy('created_at', $logOrder)->take($logCount)->get();
            } else if ($logModel == 'jobs') {
                $datalog = jobLogModel::where('jobsResult', '=', $logResult)->orderBy('jobsId', $logOrder)->take($logCount)->get();
            } else if ($logModel == 'notify') {
                $datalog = notifyLogModel::orderBy('notifyId', $logOrder)->take($logCount)->get();
            } else if ($logModel == 'compress') {
                $datalog = compressModel::where('result', '=', $logResult)->orderBy('compressId', $logOrder)->take($logCount)->get();
            } else if ($logModel == 'convert') {
                $datalog = cnvModel::where('result', '=', $logResult)->orderBy('cnvId', $logOrder)->take($logCount)->get();
            } else if ($logModel == 'html') {
                $datalog = htmlModel::where('result', '=', $logResult)->orderBy('htmlId', $logOrder)->take($logCount)->get();
            } else if ($logModel == 'merge') {
                $datalog = mergeModel::where('result', '=', $logResult)->orderBy('mergeId', $logOrder)->take($logCount)->get();
            } else if ($logModel == 'split') {
                $datalog = splitModel::where('result', '=', $logResult)->orderBy('splitId', $logOrder)->take($logCount)->get();
            } else if ($logModel == 'watermark') {
                $datalog = watermarkModel::where('result', '=', $logResult)->orderBy('watermarkId', $logOrder)->take($logCount)->get();
            }
            $dataArrayLog = $datalog->toArray();
            return $this->returnDataMessage(
                200,
                'Request generated',
                null,
                $dataArrayLog,
                null,
                null,
                null
            );
        } catch (QueryException $e) {
            return $this->returnDataMessage(
                500,
                'Eloquent QueryException',
                null,
                null,
                null,
                null,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            return $this->returnDataMessage(
                500,
                'Unknown Exception',
                null,
                null,
                null,
                null,
                $e->getMessage()
            );
        }
    }
}
