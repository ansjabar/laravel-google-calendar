<?php

use Illuminate\Support\Facades\Route;

Route::get('google-calendar/callback', '\AnsJabar\LaravelGoogleCalendar\Calendar@callbackFromGoogle');