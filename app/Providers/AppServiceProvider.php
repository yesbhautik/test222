<?php

namespace App\Providers;

use App\Helpers\Classes\Helper;
use App\Models\Setting;
use App\Services\MemoryLimit;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Facades\Health;
use App\Models\SettingTwo;


// use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(ViewServiceProvider::class);
        $this->app->register(MacrosServiceProvider::class);
    }

    public function boot(): void
    {
        $dbConnectionStatus = Helper::dbConnectionStatus();

        Schema::defaultStringLength(191);
		
		$theme = 'default';
        # frontend setting shared
        if (Schema::hasTable('settings_two')) {

            $settings_two = SettingTwo::first();
            $theme = $settings_two?->theme ?? 'default';
        }
        # set app theme
        \Theme::set($theme);

        $this->forceSchemeHttps();

        app()->useLangPath(
            base_path('lang')
        );

        $locale = 'en';

        if($dbConnectionStatus)
        {

            if (Schema::hasTable('settings_two')) {
                $locale = Helper::settingTwo('languages_default') ?: $locale;
            }

            $this->configSet();

            $this->jobRuns();
        }

        app()->setLocale($locale);

        Health::checks([
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
            DatabaseCheck::new(),
            // UsedDiskSpaceCheck::new(),
            MemoryLimit::new(),
        ]);
    }

    public function jobRuns(): void
    {
        if (Schema::hasTable('jobs')) {
            $wordlist = DB::table('jobs')->where('id', '>', 0)->get();

            if (count($wordlist) > 0) {
                // change each job not default to default
                DB::table('jobs')
                    ->where('queue', '<>', 'default')
                    ->update(['queue' => 'default']);

                Artisan::call('queue:work --once');
            }
        }
    }

    public function configSet(): void
    {
        if (Schema::hasTable('settings'))
        {
            $settings = Setting::first();

            Config::set(['mail.mailers' => [
                (env('MAIL_SMTP') ?? 'smtp') => [
                    'transport' => env('MAIL_DRIVER') ?? 'smtp',
                    'host' => $settings->smtp_host ?? env('MAIL_HOST'),
                    'port' => (int) $settings->smtp_port ?? (int) env('MAIL_PORT'),
                    'encryption' => $settings->smtp_encryption ?? env('MAIL_ENCRYPTION'),
                    'username' => $settings->smtp_username ?? env('MAIL_USERNAME'),
                    'password' => $settings->smtp_password ?? env('MAIL_PASSWORD'),
                ],
                'timeout' => null,
                'local_domain' => env('MAIL_EHLO_DOMAIN'),
                'auth_mode' => null,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]]);

            Config::set(
                ['mail.from' => ['address' => $settings->smtp_email ?? env('MAIL_FROM_ADDRESS'), 'name' => $settings->smtp_sender_name ?? env('MAIL_FROM_NAME')]]
            );
        }
    }

    public function forceSchemeHttps(): void
    {
        if ($this->app->environment('production'))
        {
            \URL::forceScheme('https');
        }
    }
}