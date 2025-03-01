<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function returnCoreMessage($status, $message, $fileName, $fileSource, $proc, $procId, $curFileSize, $newFileSize, $compMethod, $errors) {
        if ($proc == 'compress') {
            return response()->json([
                'status' => $status,
                'message' => $message,
                'fileName' => $fileName,
                'fileSource' => $fileSource,
                'proc' => $proc,
                'groupId' => $procId,
                'curFileSize' => $curFileSize,
                'newFileSize' => $newFileSize,
                'compMethod' => $compMethod,
                'errors' => $errors
            ], $status);
        } else {
            return response()->json([
                'status' => $status,
                'message' => $message,
                'fileName' => $fileName,
                'fileSource' => $fileSource,
                'proc' => $proc,
                'groupId' => $procId,
                'errors' => $errors
            ], $status);
        }
    }

    protected function returnDataMessage($status, $message, $app, $data, $file, $groupId, $errors)
    {
        return response()->json([
            'status' => $status,
            'message'=> $message,
            'app' => $app,
            'data' => $data,
            'file' => $file,
            'groupId' => $groupId,
            'errors' => $errors
        ], $status);
    }

    protected function returnFileMessage($status, $message, $files, $errors)
    {
        return response()->json([
            'status' => $status,
            'message'=> $message,
            'files' => $files,
            'errors' => $errors
        ], $status);
    }

    protected function returnLimitMessage($status, $message, $remaining, $total, $errors) {
        return response()->json([
            'status' => $status,
            'message'=> $message,
            'remaining' => $remaining,
            'total' => $total,
            'errors' => $errors
        ], $status);
    }

    protected function returnTokenMessage($status, $message, $info, $access_token, $token, $expire)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'info' => $info,
            'access_token' => $access_token,
            'token_type' => $token,
            'expires_in' => $expire,
        ], $status);
    }

    protected function returnVersioningMessage($status, $message, $beVersioning, $beGitVersioning, $feVersioning, $feGitVersioning, $errors)
    {
        return response()->json([
            'status' => $status,
            'message'=> $message,
            'beVersioning' => $beVersioning,
            'beGitVersioning' => $beGitVersioning,
            'feVersioning' => $feVersioning,
            'feGitVersioning' => $feGitVersioning,
            'errors' => $errors
        ], $status);
    }
}
