<?php

use App\Helpers\AppHelper;
use App\Helpers\NotificationHelper;
use App\Models\appLogModel;
use App\Models\jobLogModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Stringable;

// Generate unique UUID
$cacheClearUUIDProc = AppHelper::Instance()->generateUniqueUuid(jobLogModel::class, 'processId');
$cacheClearUUIDGroup = AppHelper::Instance()->generateUniqueUuid(jobLogModel::class, 'groupId');
$optimizeClearUUIDProc = AppHelper::Instance()->generateUniqueUuid(jobLogModel::class, 'processId');
$optimizeClearUUIDGroup = AppHelper::Instance()->generateUniqueUuid(jobLogModel::class, 'groupId');
$viewClearUUIDProc = AppHelper::Instance()->generateUniqueUuid(jobLogModel::class, 'processId');
$viewClearUUIDGroup = AppHelper::Instance()->generateUniqueUuid(jobLogModel::class, 'groupId');
$viewCacheUUIDProc = AppHelper::Instance()->generateUniqueUuid(jobLogModel::class, 'processId');
$viewCacheUUIDGroup = AppHelper::Instance()->generateUniqueUuid(jobLogModel::class, 'groupId');
$dailyReportUUIDProc = AppHelper::Instance()->generateUniqueUuid(jobLogModel::class, 'processId');
$dailyReportUUIDGroup = AppHelper::Instance()->generateUniqueUuid(jobLogModel::class, 'groupId');
$cleanSessionUUIDProc = AppHelper::Instance()->generateUniqueUuid(jobLogModel::class, 'processId');
$cleanSessionUUIDGroup = AppHelper::Instance()->generateUniqueUuid(jobLogModel::class, 'groupId');
$cleanStorageUUIDProc = AppHelper::Instance()->generateUniqueUuid(jobLogModel::class, 'processId');
$cleanStorageUUIDGroup = AppHelper::Instance()->generateUniqueUuid(jobLogModel::class, 'groupId');
$minioFileUUIDProc = AppHelper::Instance()->generateUniqueUuid(jobLogModel::class, 'processId');
$minioFileUUIDGroup = AppHelper::Instance()->generateUniqueUuid(jobLogModel::class, 'groupId');

// Carbon timezone
date_default_timezone_set('Asia/Jakarta');
$now = Carbon::now('Asia/Jakarta');
$startProc = $now->format('Y-m-d H:i:s');

Schedule::command('cache:clear')
    ->weekly()
    ->environments(env('APP_ENV'))
    ->timezone('Asia/Jakarta')
    ->before(function(AppHelper $helper) use($cacheClearUUIDProc, $cacheClearUUIDGroup, $startProc) {
        appLogModel::create([
            'processId' => $cacheClearUUIDProc,
            'groupId' => $cacheClearUUIDGroup,
            'errReason' => null,
            'errStatus' => null
        ]);
        jobLogModel::create([
            'jobsName' => 'cache:clear',
            'jobsEnv' => env('APP_ENV'),
            'jobsRuntime' => 'weekly',
            'jobsResult' => false,
            'groupId' => $cacheClearUUIDGroup,
            'processId' => $cacheClearUUIDProc,
            'procStartAt' => $startProc
        ]);
    })
    ->after(function(AppHelper $helper, Stringable $output) use($cacheClearUUIDProc, $cacheClearUUIDGroup, $startProc)  {
        $start = Carbon::parse($startProc);
        $end =  Carbon::parse($helper::instance()->getCurrentTimeZone());
        $duration = $end->diff($start);
        if ($output == null || $output == '' || empty($output) || str_contains($output, 'successfully')) {
            jobLogModel::where('processId', '=', $cacheClearUUIDProc)
                ->update([
                    'jobsResult' => true,
                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                    'procDuration' => $duration->s.' seconds'
                ]);
        } else {
            appLogModel::where('processId', '=', $cacheClearUUIDProc)
                ->update([
                    'errReason' => $output,
                    'errStatus' => 'Laravel Scheduler Error !'
                ]);
            jobLogModel::where('processId', '=', $cacheClearUUIDProc)
                ->update([
                    'jobsResult' => false,
                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                    'procDuration' => $duration->s.' seconds'
                ]);
            NotificationHelper::Instance()->sendSchedErrNotify(
                'cache:clear',
                'weekly',
                $cacheClearUUIDGroup,
                'FAIL',
                'Laravel Scheduler Error !',
                $output
            );
        }
    });

Schedule::command('optimize:clear')
    ->weekly()
    ->environments(env('APP_ENV'))
    ->timezone('Asia/Jakarta')
    ->before(function(AppHelper $helper) use($optimizeClearUUIDProc, $optimizeClearUUIDGroup, $startProc) {
        appLogModel::create([
            'processId' => $optimizeClearUUIDProc,
            'groupId' => $optimizeClearUUIDGroup,
            'errReason' => null,
            'errStatus' => null
        ]);
        jobLogModel::create([
            'jobsName' => 'optimize:clear',
            'jobsEnv' => env('APP_ENV'),
            'jobsRuntime' => 'weekly',
            'jobsResult' => false,
            'groupId' => $optimizeClearUUIDGroup,
            'processId' => $optimizeClearUUIDProc,
            'procStartAt' => $startProc
        ]);
    })
    ->after(function(AppHelper $helper, Stringable $output) use($optimizeClearUUIDProc, $optimizeClearUUIDGroup, $startProc)  {
        $start = Carbon::parse($startProc);
        $end =  Carbon::parse($helper::instance()->getCurrentTimeZone());
        $duration = $end->diff($start);
        if ($output == null || $output == '' || empty($output) || str_contains($output, 'DONE')) {
            jobLogModel::where('processId', '=', $optimizeClearUUIDProc)
                ->update([
                    'jobsResult' => true,
                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                    'procDuration' => $duration->s.' seconds'
                ]);
        } else {
            appLogModel::where('processId', '=', $optimizeClearUUIDProc)
                ->update([
                    'errReason' => $output,
                    'errStatus' => 'Laravel Scheduler Error !'
                ]);
            jobLogModel::where('processId', '=', $optimizeClearUUIDProc)
                ->update([
                    'jobsResult' => false,
                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                    'procDuration' => $duration->s.' seconds'
                ]);
            NotificationHelper::Instance()->sendSchedErrNotify(
                'optimize:clear',
                'weekly',
                $optimizeClearUUIDGroup,
                'FAIL',
                'Laravel Scheduler Error !',
                $output
            );
        }
    });

Schedule::command('view:clear')
    ->weekly()
    ->environments(env('APP_ENV'))
    ->timezone('Asia/Jakarta')
    ->before(function(AppHelper $helper) use($viewClearUUIDProc, $viewClearUUIDGroup, $startProc) {
        appLogModel::create([
            'processId' => $viewClearUUIDProc,
            'groupId' => $viewClearUUIDGroup,
            'errReason' => null,
            'errStatus' => null
        ]);
        jobLogModel::create([
            'jobsName' => 'view:clear',
            'jobsEnv' => env('APP_ENV'),
            'jobsRuntime' => 'weekly',
            'jobsResult' => false,
            'groupId' => $viewClearUUIDGroup,
            'processId' => $viewClearUUIDProc,
            'procStartAt' => $startProc
        ]);
    })
    ->after(function(AppHelper $helper, Stringable $output) use($viewClearUUIDProc, $viewClearUUIDGroup, $startProc)  {
        $start = Carbon::parse($startProc);
        $end =  Carbon::parse($helper::instance()->getCurrentTimeZone());
        $duration = $end->diff($start);
        if ($output == null || $output == '' || empty($output) || str_contains($output, 'successfully')) {
            jobLogModel::where('processId', '=', $viewClearUUIDProc)
                ->update([
                    'jobsResult' => true,
                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                    'procDuration' => $duration->s.' seconds'
                ]);
        } else {
            appLogModel::where('processId', '=', $viewClearUUIDProc)
                ->update([
                    'errReason' => $output,
                    'errStatus' => 'Laravel Scheduler Error !',
                ]);
            jobLogModel::where('processId', '=', $viewClearUUIDProc)
                ->update([
                    'jobsResult' => false,
                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                    'procDuration' => $duration->s.' seconds'
                ]);
            NotificationHelper::Instance()->sendSchedErrNotify(
                'view:clear',
                'weekly',
                $viewClearUUIDGroup,
                'FAIL',
                'Laravel Scheduler Error !',
                $output
            );
        }
    });

Schedule::command('view:cache')
    ->weekly()
    ->environments(env('APP_ENV'))
    ->timezone('Asia/Jakarta')
    ->before(function(AppHelper $helper) use($viewCacheUUIDProc, $viewCacheUUIDGroup, $startProc) {
        appLogModel::create([
            'processId' => $viewCacheUUIDProc,
            'groupId' => $viewCacheUUIDGroup,
            'errReason' => null,
            'errStatus' => null
        ]);
        jobLogModel::create([
            'jobsName' => 'view:cache',
            'jobsEnv' => env('APP_ENV'),
            'jobsRuntime' => 'weekly',
            'jobsResult' => false,
            'groupId' => $viewCacheUUIDGroup,
            'processId' => $viewCacheUUIDProc,
            'procStartAt' => $startProc
        ]);
    })
    ->after(function(AppHelper $helper, Stringable $output) use($viewCacheUUIDProc, $viewCacheUUIDGroup, $startProc)  {
        $start = Carbon::parse($startProc);
        $end =  Carbon::parse($helper::instance()->getCurrentTimeZone());
        $duration = $end->diff($start);
        if ($output == null || $output == '' || empty($output) || str_contains($output, 'successfully')) {
            jobLogModel::where('processId', '=', $viewCacheUUIDProc)
                ->update([
                    'jobsResult' => true,
                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                    'procDuration' => $duration->s.' seconds'
                ]);
        } else {
            appLogModel::where('processId', '=', $viewCacheUUIDProc)
                ->update([
                    'errReason' => $output,
                    'errStatus' => 'Laravel Scheduler Error !',
                ]);
            jobLogModel::where('processId', '=', $viewCacheUUIDProc)
                ->update([
                    'jobsResult' => false,
                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                    'procDuration' => $duration->s.' seconds'
                ]);
            NotificationHelper::Instance()->sendSchedErrNotify(
                'view:cache',
                'weekly',
                $viewCacheUUIDGroup,
                'FAIL',
                'Laravel Scheduler Error !',
                $output
            );
        }
    });

Schedule::command('hana:clean-storage')
    ->hourly()
    ->timezone('Asia/Jakarta')
    ->environments(env('APP_ENV'))
    ->before(function(AppHelper $helper) use($cleanStorageUUIDProc, $cleanStorageUUIDGroup, $startProc) {
        appLogModel::create([
            'processId' => $cleanStorageUUIDProc,
            'groupId' => $cleanStorageUUIDGroup,
            'errReason' => null,
            'errStatus' => null
        ]);
        jobLogModel::create([
            'jobsName' => 'hana:clean-storage',
            'jobsEnv' => env('APP_ENV'),
            'jobsRuntime' => 'hourly',
            'jobsResult' => false,
            'groupId' => $cleanStorageUUIDGroup,
            'processId' => $cleanStorageUUIDProc,
            'procStartAt' => $startProc
        ]);
    })
    ->after(function(AppHelper $helper, Stringable $output) use($cleanStorageUUIDProc, $cleanStorageUUIDGroup, $startProc)  {
        $start = Carbon::parse($startProc);
        $end =  Carbon::parse($helper::instance()->getCurrentTimeZone());
        $duration = $end->diff($start);
        if ($output == null || $output == '' || empty($output)) {
            jobLogModel::where('processId', '=', $cleanStorageUUIDProc)
                ->update([
                    'jobsResult' => true,
                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                    'procDuration' => $duration->s.' seconds'
                ]);
        } else {
            appLogModel::where('processId', '=', $cleanStorageUUIDProc)
                ->update([
                    'errReason' => $output,
                    'errStatus' => 'Laravel Scheduler Error !'
                ]);
            jobLogModel::where('processId', '=', $cleanStorageUUIDProc)
                ->update([
                    'jobsResult' => false,
                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                    'procDuration' => $duration->s.' seconds'
                ]);
            NotificationHelper::Instance()->sendSchedErrNotify(
                'hana:clean-storage',
                'hourly',
                $cleanStorageUUIDGroup,
                'FAIL',
                'Laravel Scheduler Error !',
                $output
            );
        }
    });

Schedule::command('hana:daily-report')
    ->dailyAt('19:00')
    ->timezone('Asia/Jakarta')
    ->environments(env('APP_ENV'))
    ->before(function(AppHelper $helper) use($dailyReportUUIDProc, $dailyReportUUIDGroup, $startProc) {
        appLogModel::create([
            'processId' => $dailyReportUUIDProc,
            'groupId' => $dailyReportUUIDGroup,
            'errReason' => null,
            'errStatus' => null
        ]);
        jobLogModel::create([
            'jobsName' => 'hana:daily-report',
            'jobsEnv' => env('APP_ENV'),
            'jobsRuntime' => 'daily at 19:00',
            'jobsResult' => false,
            'groupId' => $dailyReportUUIDGroup,
            'processId' => $dailyReportUUIDProc,
            'procStartAt' => $startProc
        ]);
    })
    ->after(function(AppHelper $helper, Stringable $output) use($dailyReportUUIDProc, $dailyReportUUIDGroup, $startProc)  {
        $start = Carbon::parse($startProc);
        $end =  Carbon::parse($helper::instance()->getCurrentTimeZone());
        $duration = $end->diff($start);
        if ($output == null || $output == '' || empty($output)) {
            jobLogModel::where('processId', '=', $dailyReportUUIDProc)
                ->update([
                    'jobsResult' => true,
                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                    'procDuration' => $duration->s.' seconds'
                ]);
        } else {
            appLogModel::where('processId', '=', $dailyReportUUIDProc)
                ->update([
                    'errReason' => $output,
                    'errStatus' => 'Laravel Scheduler Error !',
                ]);
            jobLogModel::where('processId', '=', $dailyReportUUIDProc)
                ->update([
                    'jobsResult' => false,
                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                    'procDuration' => $duration->s.' seconds'
                ]);
            NotificationHelper::Instance()->sendSchedErrNotify(
                'hana:daily-report',
                'daily at 19:00',
                $dailyReportUUIDGroup,
                'FAIL',
                'Laravel Scheduler Error !',
                $output
            );
        }
    });

Schedule::command('hana:clean-session')
    ->everyFifteenMinutes()
    ->environments(env('APP_ENV'))
    ->timezone('Asia/Jakarta')
    ->before(function(AppHelper $helper) use($cleanSessionUUIDProc, $cleanSessionUUIDGroup, $startProc) {
        appLogModel::create([
            'processId' => $cleanSessionUUIDProc,
            'groupId' => $cleanSessionUUIDGroup,
            'errReason' => null,
            'errStatus' => null
        ]);
        jobLogModel::create([
            'jobsName' => 'hana:clean-session',
            'jobsEnv' => env('APP_ENV'),
            'jobsRuntime' => 'every 15 minutes',
            'jobsResult' => false,
            'groupId' => $cleanSessionUUIDGroup,
            'processId' => $cleanSessionUUIDProc,
            'procStartAt' => $startProc
        ]);
    })
    ->after(function(AppHelper $helper, Stringable $output) use($cleanSessionUUIDProc, $cleanSessionUUIDGroup, $startProc)  {
        $start = Carbon::parse($startProc);
        $end =  Carbon::parse($helper::instance()->getCurrentTimeZone());
        $duration = $end->diff($start);
        if ($output == null || $output == '' || empty($output) || str_contains($output, 'successfully')) {
            jobLogModel::where('processId', '=', $cleanSessionUUIDProc)
            ->update([
                'jobsResult' => true,
                'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                'procDuration' => $duration->s.' seconds'
            ]);
        } else {
            appLogModel::where('processId', '=', $cleanSessionUUIDProc)
            ->update([
                'errReason' => $output,
                'errStatus' => 'Laravel Scheduler Error !',
            ]);
            jobLogModel::where('processId', '=', $cleanSessionUUIDProc)
                ->update([
                    'jobsResult' => false,
                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                    'procDuration' => $duration->s.' seconds'
                ]);
            NotificationHelper::Instance()->sendSchedErrNotify(
                'hana:daily-session',
                'every 15 minutes',
                $cleanSessionUUIDGroup,
                'FAIL',
                'Laravel Scheduler Error !',
                $output
            );
        }
    });

Schedule::command('hana:minio-cleanup')
    ->hourly()
    ->environments(env('APP_ENV'))
    ->timezone('Asia/Jakarta')
    ->before(function(AppHelper $helper) use($minioFileUUIDProc, $minioFileUUIDGroup, $startProc) {
        appLogModel::create([
            'processId' => $minioFileUUIDProc,
            'groupId' => $minioFileUUIDGroup,
            'errReason' => null,
            'errStatus' => null
        ]);
        jobLogModel::create([
            'jobsName' => 'hana:minio-cleanup',
            'jobsEnv' => env('APP_ENV'),
            'jobsRuntime' => 'every 1 hours',
            'jobsResult' => false,
            'groupId' => $minioFileUUIDGroup,
            'processId' => $minioFileUUIDProc,
            'procStartAt' => $startProc
        ]);
    })
    ->after(function(AppHelper $helper, Stringable $output) use($minioFileUUIDProc, $minioFileUUIDGroup, $startProc)  {
        $start = Carbon::parse($startProc);
        $end =  Carbon::parse($helper::instance()->getCurrentTimeZone());
        $duration = $end->diff($start);
        if ($output == null || $output == '' || empty($output) || str_contains($output, 'successfully')) {
            jobLogModel::where('processId', '=', $minioFileUUIDProc)
            ->update([
                'jobsResult' => true,
                'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                'procDuration' => $duration->s.' seconds'
            ]);
        } else {
            appLogModel::where('processId', '=', $minioFileUUIDProc)
            ->update([
                'errReason' => $output,
                'errStatus' => 'Laravel Scheduler Error !',
            ]);
            jobLogModel::where('processId', '=', $minioFileUUIDProc)
                ->update([
                    'jobsResult' => false,
                    'procEndAt' => AppHelper::instance()->getCurrentTimeZone(),
                    'procDuration' => $duration->s.' seconds'
                ]);
            NotificationHelper::Instance()->sendSchedErrNotify(
                'hana:minio-cleanup',
                'every 1 hours',
                $minioFileUUIDGroup,
                'FAIL',
                'Laravel Scheduler Error !',
                $output
            );
        }
    });
