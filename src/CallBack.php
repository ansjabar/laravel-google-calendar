<?php

namespace AnsJabar\LaravelGoogleCalendar;

use Carbon\Carbon;;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Validation\ValidatesRequests;
use AnsJabar\LaravelGoogleCalendar\Models\GoogleCalendarToken;
use AnsJabar\LaravelGoogleCalendar\Calendar;

class CallBack
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    public function callbackFromGoogle(Request $request)
    {
        if (isset($_SESSION['google_calendar_access_token']) && $token_details = GoogleCalendarToken::whereToken( $_SESSION['google_calendar_access_token'] )->where('expiry', '>', now()->toDateString())->first()) 
        {
            $access_token = $token_details->token;
            $timezone = $token_details->timezone;
        }
        else
        {
            $response = Calendar::getAccessToken($request->code);
            $timezone = Calendar::getUserCalendarTimezone($response->access_token);
            $access_token = $response->access_token;
            $_SESSION['google_calendar_access_token'] = $access_token;
            GoogleCalendarToken::create(['token' => $access_token, 'expiry' => now()->addSeconds($response->expires_in)->subSeconds(60)->toDateTimeString(), 'timezone' => $timezone]);
        }
        return Calendar::createCalendarEvent($access_token, $timezone);
    }
}
