<?php

namespace Rhyslees\NovaAudio;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use RhysLees\NovaAbout\NovaAbout;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('audio', __DIR__.'/../dist/js/field.js');
            Nova::style('audio', __DIR__.'/../dist/css/field.css');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        NovaAbout::addPackage('rhyslees/nova-audio');
    }
}
