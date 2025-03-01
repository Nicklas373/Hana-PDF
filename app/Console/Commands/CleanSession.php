<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Helpers\AppHelper;
use App\Helpers\NotificationHelper;

class CleanSession extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hana:clean-session';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove unused Laravel sessions, since using JWT Auth. This should be cleanup!';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sessionCount = DB::table('sessions')
            ->where('ip_address', 'NOT LIKE', '172.18.0.%')
            ->count();

        if ($sessionCount > 0) {
            try {
                DB::table('sessions')
                    ->where('ip_address', 'NOT LIKE', '172.18.0.%')
                    ->delete();
            } catch (QueryException $e) {
                NotificationHelper::Instance()->sendErrGlobalNotify(
                    'Console Command',
                    'hana:clean-session',
                    'FAIL',
                    'N/A',
                    'Database connection error!',
                    $e->getMessage(),
                    false
                );
            } catch (\Exception $e) {
                NotificationHelper::Instance()->sendErrGlobalNotify(
                    'Console Command',
                    'hana:clean-session',
                    'FAIL',
                    'N/A',
                    'Eloquent transaction error!',
                    $e->getMessage(),
                    false
                );
            }
        }
    }
}
