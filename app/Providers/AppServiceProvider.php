<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\FrontendSetting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share $frontendSetting with all frontend view templates
        View::composer('frontend.*', function ($view) {
            try {
                $frontendSetting = FrontendSetting::getActive();
                $view->with('frontendSetting', $frontendSetting);
            } catch (\Exception $e) {
                $view->with('frontendSetting', new FrontendSetting());
            }
        });
    }
}
