<?php

namespace AnsJabar\LaravelGoogleCalendar;

use Illuminate\Support\ServiceProvider;

class CalendarServiceProvider extends ServiceProvider
{

    /**
     * True when booted.
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/google-calendar.php', 'google-calendar'
        );
    }
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->publishes([
            __DIR__ . '/config/google-calendar.php' => config_path('google-calendar.php'),
        ]);
        $this->publishes([
            __DIR__.'/database/migrations/' => database_path('migrations')
        ], 'google-calendar-migrations');
    }
}
