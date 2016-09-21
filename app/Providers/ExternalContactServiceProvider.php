<?php

namespace App\Providers;

use App\ActiveCampaignContactRepo;
use App\ExternalContactRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class ExternalContactServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            ExternalContactRepositoryInterface::class,
            function () { return new ActiveCampaignContactRepo(); }
        );
    }
}
