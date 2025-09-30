<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use App\Console\Kernel as AppConsoleKernel;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ConsoleKernel::class, AppConsoleKernel::class);


    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach (glob(app_path('Helpers') . '/*.php') as $filename) {
            require_once $filename;
        }

        $settings = Cache::rememberForever('settings', function () {
            return \App\Models\Setting::all();
        });

        $settings->each(function ($setting) {
            config()->set('settings.' . $setting->key, $setting->value);
        });

        $log = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/request_bank_jatim.log')
        ]);
    }
}
