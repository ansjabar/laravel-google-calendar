<?php

namespace AnsJabar\LaravelGoogleCalendar;

use Carbon\Carbon;;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use AnsJabar\LaravelGoogleCalendar\Models\GoogleCalendarToken;

class Calendar
{
    private $from, $to, $summary, $callBackUrl;
    public function __construct(Carbon $from = null, Carbon $to = null, string $summary = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if($this->from = $from && $this->to = $to && $this->summary = $summary)
        {
            $_SESSION['google_calendar_event_from'] = $from->format('Y-m-d').'T'.$from->startOfMinute()->format('H:i:s');
            $_SESSION['google_calendar_event_to'] = $to->format('Y-m-d').'T'.$to->startOfMinute()->format('H:i:s');
            $_SESSION['google_calendar_event_summary'] = $summary;
        }
        $this->callBackUrl = config('app.url').'/google-calendar/callback';
    }
    public function createEvent()
    {
        if(!$this->from || !$this->to || !$this->summary)
            throw new \Exception("From , To and Summary properties are missing.");
        return $this->redirectToGoogle();
    }
    private function redirectToGoogle()
    {
        if (isset($_SESSION['google_calendar_access_token']) && $token_details = GoogleCalendarToken::whereToken( $_SESSION['google_calendar_access_token'] )->where('expiry', '>', now()->toDateString())->first()) 
        {
            $access_token = $token_details->token;
            $timezone = $token_details->timezone;
            return $this->createCalendarEvent($access_token, $timezone);
        }
        else 
        {
            $login_url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar') . '&redirect_uri=' . urlencode($this->callBackUrl) . '&response_type=code&client_id=' . config('google-calendar.client_id') . '&access_type=online';
            return redirect($login_url);
        }
    }
	public function getAccessToken($code) 
    {
		$url = 'https://accounts.google.com/o/oauth2/token';
        $request = [
            'client_id' => config('google-calendar.client_id'),
            'redirect_uri' => config('app.url').'/google-calendar/callback',
            'client_secret' => config('google-calendar.client_secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];
        $response = Http::post($url, $request);
        $body = json_decode($response->body());
        return $body;
	}
    
	public function getUserCalendarTimezone($access_token)
    {
		$url = 'https://www.googleapis.com/calendar/v3/users/me/settings/timezone';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $access_token
        ])->get($url);
        $body = json_decode($response->body());
        return $body->value;
	}

	public function createCalendarEvent($access_token, $timezone) 
    {
		$url = 'https://www.googleapis.com/calendar/v3/calendars/primary/events';
        $headers = ['Authorization' => 'Bearer ' . $access_token];
        $request = [
            'start' => ['dateTime' => $_SESSION['google_calendar_event_from'], 'timeZone' => $timezone],
            'end' => ['dateTime' => $_SESSION['google_calendar_event_to'], 'timeZone' => $timezone],
            'summary' => $_SESSION['google_calendar_event_summary'],
        ];
        $response = Http::withHeaders($headers)->post($url, $request);
        return redirect()->route(config('google-calendar.client_redirect_url'));
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
            $response = $this->getAccessToken($request->code);
            $timezone = $this->getUserCalendarTimezone($response->access_token);
            $access_token = $response->access_token;
            $_SESSION['google_calendar_access_token'] = $access_token;
            GoogleCalendarToken::create(['token' => $access_token, 'expiry' => now()->addSeconds($response->expires_in)->subSeconds(60)->toDateTimeString(), 'timezone' => $timezone]);
        }
        return $this->createCalendarEvent($access_token, $timezone);
    }
}
