<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\PaymentPlans;
use App\Models\YokassaSubscriptions;
use Carbon\Carbon;
use App\Console\CustomScheduler;
use App\Console\Commands\CheckSubscriptionEnd;
use Spatie\Health\Commands\RunHealthChecksCommand;
use App\Services\GatewaySelector;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $customSchedulerPath = app_path('Console/CustomScheduler.php');

        if (file_exists($customSchedulerPath)) {
            require_once($customSchedulerPath);
            CustomScheduler::scheduleTasks($schedule);
        }

        $schedule->command("app:check-coingate-command")->everyFiveMinutes();

        $schedule->command("subscription:check-end")->everyFiveMinutes();

        $schedule->call(function () {
            $activeSub_yokassa = YokassaSubscriptions::where('subscription_status', 'active')->orWhere('subscription_status', 'yokassa_approved')->get();
            foreach($activeSub_yokassa as $activeSub) {
                $data_now = Carbon::now();
                $data_end_sub = $activeSub->next_pay_at;
                if($data_now->gt($data_end_sub)) $result = GatewaySelector::selectGateway('yokassa')::handleSubscribePay($activeSub->id);
            }
        })->daily();
        
    }
    // $schedule->command(RunHealthChecksCommand::class)->everyFiveMinutes();
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
