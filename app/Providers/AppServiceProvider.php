<?php

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

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
        Blueprint::macro('personName', function () {
            $this->string('first_name');
            $this->string('middle_name')->nullable();
            $this->string('last_name');
            $this->string('name_extension')->nullable();
            $this->index(['first_name', 'last_name']);
        });

        Blueprint::macro('auditable', function () {
            $this->foreignId('created_by')->nullable()->constrained('users');
            $this->foreignId('updated_by')->nullable()->constrained('users');
        });
    }
}
