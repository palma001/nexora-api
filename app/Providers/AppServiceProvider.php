<?php

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;
use App\Models\Company;
use App\Observers\CompanyObserver;

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
        Blueprint::macro('auditable', function () {
            $this->softDeletes();
            $this->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $this->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $this->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
        });

        // Register Observers
        \App\Models\Company::observe(\App\Observers\CompanyObserver::class);
        \App\Models\Product::observe(\App\Observers\ProductObserver::class);
        \App\Models\Category::observe(\App\Observers\CategoryObserver::class);

        // Disable JSON resource wrapping
        \Illuminate\Http\Resources\Json\JsonResource::withoutWrapping();

        // Super Admin Access
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->is_root ? true : null;
        });
    }
}
