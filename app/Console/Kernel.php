<?php

namespace App\Console;

use App\Helpers\AppHelper;
use App\Helpers\NotificationHelper;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Stringable;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $cacheClearGUID = AppHelper::instance()->get_guid();
        $optimizeClearGUID = AppHelper::instance()->get_guid();
        $viewClearGUID = AppHelper::instance()->get_guid();
        $viewCacheGUID = AppHelper::instance()->get_guid();

        // Carbon timezone
        date_default_timezone_set('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');
        $startProc = $now->format('Y-m-d H:i:s');

        $schedule
            ->command('cache:clear')
            ->weekly()
            ->environments(['production'])
            ->timezone('Asia/Jakarta')
            ->before(function(AppHelper $helper) use($cacheClearGUID) {
				DB::table('appLogs')
					->insert([
						'processId' => $cacheClearGUID,
						'errReason' => null,
						'errStatus' => null,
					]);
                DB::table('jobLogs')->insert([
                    'jobsName' => 'cache:clear',
                    'jobsEnv' => 'production-fe',
                    'jobsRuntime' => 'weekly',
                    'jobsResult' => false,
                    'processId' => $cacheClearGUID,
                    'procStartAt' => $helper::instance()->getCurrentTimeZone(),
                    'procEndAt' => null
                ]);
            })
            ->after(function(AppHelper $helper, Stringable $output) use($cacheClearGUID,$startProc) {
                $start = Carbon::parse($startProc);
                $end =  Carbon::parse($helper::instance()->getCurrentTimeZone());
                $duration = $end->diff($start);
                if ($output == null || $output == '' || empty($output) || str_contains($output, 'successfully')) {
                    DB::table('jobLogs')
                    ->where('processId', '=', $cacheClearGUID)
                    ->update([
                        'jobsResult' => true,
                        'procEndAt' => $end,
                        'procDuration' => $duration->s.' seconds'
					]);
                } else {
                    DB::table('jobLogs')
                        ->where('processId', '=', $cacheClearGUID)
                        ->update([
                            'jobsResult' => false,
                            'procEndAt' => $end,
                            'procDuration' => $duration->s.' seconds'
                    ]);
                    DB::table('appLogs')
                        ->where('processId', '=', $cacheClearGUID)
                        ->update([
                            'errReason' => 'Laravel Scheduler Error !',
                            'errStatus' => $output,
                    ]);
                    NotificationHelper::Instance()->sendSchedErrNotify('cache:clear','weekly', $cacheClearGUID, 'FAIL','Laravel Scheduler Error !',$output);
                }
            });
        $schedule
            ->command('optimize:clear')
            ->weekly()
            ->environments(['production'])
            ->timezone('Asia/Jakarta')
            ->before(function(AppHelper $helper) use($optimizeClearGUID) {
				DB::table('appLogs')
					->insert([
						'processId' => $optimizeClearGUID,
						'errReason' => null,
						'errStatus' => null,
					]);
                DB::table('jobLogs')->insert([
                    'jobsName' => 'optimize:clear',
                    'jobsEnv' => 'production-fe',
                    'jobsRuntime' => 'weekly',
                    'jobsResult' => false,
                    'processId' => $optimizeClearGUID,
                    'procStartAt' => $helper::instance()->getCurrentTimeZone(),
                    'procEndAt' => null
                ]);
            })
            ->after(function(AppHelper $helper, Stringable $output) use($optimizeClearGUID,$startProc) {
                $start = Carbon::parse($startProc);
                $end =  Carbon::parse($helper::instance()->getCurrentTimeZone());
                $duration = $end->diff($start);
                if ($output == null || $output == '' || empty($output) || str_contains($output, 'DONE')) {
                    DB::table('jobLogs')
                    ->where('processId', '=', $optimizeClearGUID)
                    ->update([
                        'jobsResult' => true,
                        'procEndAt' => $end,
                        'procDuration' => $duration->s.' seconds'
					]);
                } else {
                    DB::table('jobLogs')
                        ->where('processId', '=', $optimizeClearGUID)
                        ->update([
                            'jobsResult' => false,
                            'procEndAt' => $end,
                            'procDuration' => $duration->s.' seconds'
                    ]);
                    DB::table('appLogs')
                        ->where('processId', '=', $optimizeClearGUID)
                        ->update([
                            'errReason' => 'Laravel Scheduler Error !',
                            'errStatus' => $output,
                    ]);
                    NotificationHelper::Instance()->sendSchedErrNotify('optimize:clear','weekly', $optimizeClearGUID, 'FAIL','Laravel Scheduler Error !',$output);
                }
            });
        $schedule
            ->command('view:clear')
            ->weekly()
            ->environments(['production'])
            ->timezone('Asia/Jakarta')
            ->before(function(AppHelper $helper) use($viewClearGUID) {
				DB::table('appLogs')
					->insert([
						'processId' => $viewClearGUID,
						'errReason' => null,
						'errStatus' => null,
					]);
                DB::table('jobLogs')->insert([
                    'jobsName' => 'view:clear',
                    'jobsEnv' => 'production-fe',
                    'jobsRuntime' => 'weekly',
                    'jobsResult' => false,
                    'processId' => $viewClearGUID,
                    'procStartAt' => $helper::instance()->getCurrentTimeZone(),
                    'procEndAt' => null
                ]);
            })
            ->after(function(AppHelper $helper, Stringable $output) use($viewClearGUID,$startProc) {
                $start = Carbon::parse($startProc);
                $end =  Carbon::parse($helper::instance()->getCurrentTimeZone());
                $duration = $end->diff($start);
                if ($output == null || $output == '' || empty($output) || str_contains($output, 'successfully')) {
                    DB::table('jobLogs')
                    ->where('processId', '=', $viewClearGUID)
                    ->update([
                        'jobsResult' => true,
                        'procEndAt' => $end,
                        'procDuration' => $duration->s.' seconds'
					]);
                } else {
                    DB::table('jobLogs')
                        ->where('processId', '=', $viewClearGUID)
                        ->update([
                            'jobsResult' => false,
                            'procEndAt' => $end,
                            'procDuration' => $duration->s.' seconds'
                    ]);
                    DB::table('appLogs')
                        ->where('processId', '=', $viewClearGUID)
                        ->update([
                            'errReason' => 'Laravel Scheduler Error !',
                            'errStatus' => $output,
                    ]);
                    NotificationHelper::Instance()->sendSchedErrNotify('view:clear','weekly', $viewClearGUID, 'FAIL','Laravel Scheduler Error !',$output);
                }
            });
        $schedule
            ->command('view:cache')
            ->weekly()
            ->environments(['production'])
            ->timezone('Asia/Jakarta')
            ->before(function(AppHelper $helper) use($viewCacheGUID) {
				DB::table('appLogs')
					->insert([
						'processId' => $viewCacheGUID,
						'errReason' => null,
						'errStatus' => null,
					]);
                DB::table('jobLogs')->insert([
                    'jobsName' => 'view:cache',
                    'jobsEnv' => 'production-fe',
                    'jobsRuntime' => 'weekly',
                    'jobsResult' => false,
                    'processId' => $viewCacheGUID,
                    'procStartAt' => $helper::instance()->getCurrentTimeZone(),
                    'procEndAt' => null
                ]);
            })
            ->after(function(AppHelper $helper, Stringable $output) use($viewCacheGUID,$startProc) {
                $start = Carbon::parse($startProc);
                $end =  Carbon::parse($helper::instance()->getCurrentTimeZone());
                $duration = $end->diff($start);
                if ($output == null || $output == '' || empty($output) || str_contains($output, 'successfully')) {
                    DB::table('jobLogs')
                    ->where('processId', '=', $viewCacheGUID)
                    ->update([
                        'jobsResult' => true,
                        'procEndAt' => $end,
                        'procDuration' => $duration->s.' seconds'
					]);
                } else {
                    DB::table('jobLogs')
                        ->where('processId', '=', $viewCacheGUID)
                        ->update([
                            'jobsResult' => false,
                            'procEndAt' => $end,
                            'procDuration' => $duration->s.' seconds'
                    ]);
                    DB::table('appLogs')
                        ->where('processId', '=', $viewCacheGUID)
                        ->update([
                            'errReason' => 'Laravel Scheduler Error !',
                            'errStatus' => $output,
                    ]);
                    NotificationHelper::Instance()->sendSchedErrNotify('view:cache','weekly', $viewCacheGUID, 'FAIL','Laravel Scheduler Error !',$output);
                }
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
