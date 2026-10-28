<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(base_path('config/types/user.php'), 'types_user');
        $this->mergeConfigFrom(base_path('config/types/course.php'), 'types_course');
        $this->mergeConfigFrom(base_path('config/types/order.php'), 'types_order');
        $this->mergeConfigFrom(base_path('config/types/analytics.php'), 'types_analytics');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
