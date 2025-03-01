<?php

namespace App\Http\Controllers\Api\File;

use App\Helpers\AppHelper;
use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\appLogModel;
use App\Models\fileModel;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class fileController extends Controller
{
    public function getTemporaryURL(Request $request) {
        $validator = Validator::make($request->all(),[
			'file' => [
                'required',
                'string',
                'regex:/\.doc|\.docx|\.xls|\.xlsx|\.pptx|\.ppt|.pdf|\.jpg|\.jpeg|\.png/i'
            ]
		]);

		if ($validator->fails()) {
            return $this->returnFileMessage(
                400,
                'Validation failed',
                null,
                $validator->messages()->first()
            );
		} else {
			$pdfUpload_Location = env('PDF_UPLOAD');
            $fileName = $request->post('file');
            if (Storage::disk('minio')->exists($pdfUpload_Location.'/'.$fileName)) {
                return $this->returnFileMessage(
                    200,
                    'OK',
                    Storage::disk('minio')->temporaryUrl(
                        $pdfUpload_Location.'/'.$fileName,
                        now()->addMinutes(5)
                    ),
                    null
                );
            } else {
                return $this->returnFileMessage(
                    400,
                    'File not found in the object storage !',
                    $fileName,
                    $fileName.' could not be found in the object storage'
                );
            }
        }
    }

    public function upload(Request $request) {
		$validator = Validator::make($request->all(),[
			'file' => 'required|mimes:pdf,pptx,docx,doc,xls,xlsx,jpg,png,jpeg|max:25600'
		]);

         // Carbon timezone
         date_default_timezone_set('Asia/Jakarta');
         $now = Carbon::now('Asia/Jakarta');
         $startProc = $now->format('Y-m-d H:i:s');

         // Generate Uni UUID
         $uuid = AppHelper::Instance()->generateUniqueUuid(fileModel::class, 'processId');

		if ($validator->fails()) {
            appLogModel::create([
                'processId' => $uuid,
                'groupId' => $uuid,
                'errReason' => $validator->messages()->first(),
                'errStatus' => 'Validation Failed!'
            ]);
            NotificationHelper::Instance()->sendErrNotify(
                null,
                null,
                $uuid,
                'FAIL',
                'file',
                'Validation failed',
                $validator->messages()->first()
            );
            return $this->returnFileMessage(
                400,
                'Validation failed',
                null,
                $validator->messages()->first()
            );
		} else {
			if($request->hasfile('file')) {
                $str = rand(1000,10000000);
                $pdfUpload_Location = env('PDF_UPLOAD');
                $file = $request->file('file');
                $pdfName = $file->getClientOriginalName();
                $currentFileName = basename($pdfName);
                $pdfFileName = str_replace(' ', '_', $currentFileName);
                $fileSize = filesize($file);
                $newFileSize = AppHelper::instance()->convert($fileSize, "MB");
                Storage::disk('minio')->put($pdfUpload_Location.'/'.$pdfFileName, file_get_contents($file));
                if (Storage::disk('minio')->exists($pdfUpload_Location.'/'.$pdfFileName)) {
                    AppHelper::instance()->fileModelHelper(
                            $pdfFileName,
                            AppHelper::instance()->convert(Storage::disk('minio')->size($pdfUpload_Location.'/'.$pdfFileName), "MB"),
                            $uuid,
                            $uuid,
                            false,
                            null
                    );
                    return $this->returnFileMessage(
                        201,
                        'File uploaded successfully !',
                        Storage::disk('minio')->exists($pdfUpload_Location.'/'.$pdfFileName),
                        null,
                    );
                } else {
                    appLogModel::create([
                        'processId' => $uuid,
                        'groupId' => $uuid,
                        'errReason' => $pdfFileName.' could not be found in the object storage',
                        'errStatus' => 'File not found in the object storage !'
                    ]);
                    return $this->returnFileMessage(
                        400,
                        'Failed to upload file !',
                        $pdfFileName,
                        $pdfFileName.' could not be found in the object storage'
                    );
                }
            } else {
                appLogModel::create([
                    'processId' => $uuid,
                    'groupId' => $uuid,
                    'errReason' => 'Requested file could not be found in the server',
                    'errStatus' => 'Failed to upload file !'
                ]);
                return $this->returnFileMessage(
                    400,
                    'Failed to upload file !',
                    null,
                    'Requested file could not be found in the server'
                );
            }
        }
    }

    public function remove(Request $request) {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'string',
                'regex:/\.doc|\.docx|\.xls|\.xlsx|\.pptx|\.ppt|.pdf|\.jpg|\.jpeg|\.png/i'
            ]
        ]);

        // Carbon timezone
        date_default_timezone_set('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');
        $startProc = $now->format('Y-m-d H:i:s');

        // Generate Uni UUID
        $uuid = AppHelper::Instance()->generateUniqueUuid(fileModel::class, 'processId');

		if ($validator->fails()) {
            appLogModel::create([
                'processId' => $uuid,
                'groupId' => $uuid,
                'errReason' => $validator->messages()->first(),
                'errStatus' => 'Validation Failed!'
            ]);
            NotificationHelper::Instance()->sendErrNotify(
                null,
                null,
                $uuid,
                'FAIL',
                'file',
                'Validation failed',
                $validator->messages()->first()
            );
            return $this->returnFileMessage(
                400,
                'Validation failed',
                null,
                $validator->messages()->first()
            );
		} else {
			if($request->has('file')) {
                $pdfUpload_Location = env('PDF_UPLOAD');
                $file = $request->input('file');
                $pdfName = basename($file);
                $currentFileName = basename($pdfName);
                $pdfFileName = str_replace(' ', '_', $currentFileName);
                $pdfNewPath = Storage::disk('local')->path($pdfUpload_Location.'/'.$pdfFileName);
                if (file_exists($pdfNewPath)) {
                    unlink($pdfNewPath);
                }
                if (Storage::disk('minio')->exists($pdfUpload_Location.'/'.$pdfFileName)) {
                    Storage::disk('minio')->delete($pdfUpload_Location.'/'.$pdfFileName);
                    $prevProcID = fileModel::where('fileName', '=', $pdfFileName)
                                            ->where('isDeleted', '=', false)
                                            ->first()
                                            ->processId;
                    appLogModel::where('processId', '=', $prevProcID)
                        ->update([
                            'errReason' => null,
                            'errStatus' => null
                        ]);
                    fileModel::where('processId', '=', $prevProcID)
                        ->where('isDeleted', '=', false)
                        ->update([
                            'isDeleted' => true,
                            'deletedAt' => $startProc
                        ]);
                    return $this->returnFileMessage(
                        200,
                        'File removed successfully !',
                        Storage::disk('minio')->exists($pdfUpload_Location.'/'.$pdfFileName),
                        null
                    );
                } else {
                    if (fileModel::where('fileName', '=', $pdfFileName)->where('isDeleted', '=', false)->exists()) {
                        $prevProcID = fileModel::where('fileName', '=', $pdfFileName)
                                                ->where('isDeleted', '=', false)
                                                ->first()
                                                ->processId;
                        appLogModel::where('processId', '=', $prevProcID)
                            ->update([
                                'errReason' => $pdfFileName.' could not be found in the server',
                                'errStatus' => 'Failed to remove file !',
                            ]);
                    }
                    return $this->returnFileMessage(
                        400,
                        'Failed to remove file !',
                        $pdfFileName,
                        $pdfFileName.' could not be found in the server'
                    );
                }
            } else {
                if (fileModel::where('fileName', '=', $pdfFileName)->where('isDeleted', '=', false)->exists()) {
                    $prevProcID = fileModel::where('fileName', '=', $pdfFileName)
                                            ->where('isDeleted', '=', false)
                                            ->first()
                                            ->processId;
                    appLogModel::where('processId', '=', $prevProcID)
                                ->update([
                                    'errReason' => 'Requested file could not be found in the server',
                                    'errStatus' => 'Failed to remove file !',
                                ]);
                }
                return $this->returnFileMessage(
                    400,
                    'Failed to remove file !',
                    null,
                    'Requested file could not be found in the server'
                );
            }
        }
    }

    public function getTotalPagesPDF(Request $request) {
		$validator = Validator::make($request->all(),[
			'fileName' => [
                'required',
                'string',
                'regex:/\.pdf/i'
            ]
		]);

		if ($validator->fails()) {
            return $this->returnFileMessage(
                400,
                'Validation failed',
                null,
                $validator->messages()->first()
            );
		} else {
			if($request->has('fileName')) {
                $pdfName = $request->post('fileName');
                $currentFileName = basename($pdfName);
                $currentFileNameExtension = pathinfo($currentFileName, PATHINFO_EXTENSION);
                $pdfUpload_Location = env('PDF_UPLOAD');
                if (Storage::disk('minio')->exists($pdfUpload_Location.'/'.$currentFileName)) {
                    if ($currentFileNameExtension == 'pdf') {
                        $minioUpload = Storage::disk('minio')->get($pdfUpload_Location.'/'.$currentFileName);
                        Storage::disk('local')->put($pdfUpload_Location.'/'.$currentFileName, $minioUpload);
                        $newFilePath = Storage::disk('local')->path($pdfUpload_Location.'/'.$currentFileName);
                        $pdfTotalPages = AppHelper::instance()->count($newFilePath);
                        Storage::disk('local')->delete($pdfUpload_Location.'/'.$currentFileName);
                        return $this->returnDataMessage(
                            200,
                            'PDF Page successfully counted',
                            null,
                            $pdfTotalPages,
                            null,
                            null,
                            null
                        );
                    } else {
                        return $this->returnDataMessage(
                            400,
                            'File '.$currentFileName.' is not PDF file !',
                            null,
                            null,
                            null,
                            null,
                            'FILE_FORMAT_VALIDATION_EXCEPTION'
                        );
                    }
                } else {
                    return $this->returnDataMessage(
                        400,
                        'File '.$currentFileName.' not found !',
                        null,
                        null,
                        null,
                        null,
                        'FILE_NOT_FOUND_EXCEPTION'
                    );
                }
            } else {
                return $this->returnFileMessage(
                    400,
                    'Failed to upload file !',
                    null,
                    'Requested file could not be found in the server'
                );
            }
        }
    }
}
