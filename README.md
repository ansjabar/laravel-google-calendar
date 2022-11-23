# laravel-google-calendar

Laravel handler to add events to Google Calendar.

## Installation

Require this package with composer.

```bash
$ composer require ansjabar/laravel-google-calendar
```

## Integration

```bash
$ php artisan vendor:publish --provider="AnsJabar\LaravelGoogleCalendar\CalendarServiceProvider"
```

Add `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` and '`GOOGLE_REDIRECT_BACK` to your `.env` file.
Add result of following code to Redirect URL

```php
config('app.url').'/google-calendar/callback' // http://localhost:8000/azure-calendar/callback
```

## Usasge
```php
(new \AnsJabar\LaravelGoogleCalendar\Calendar(
    $from, \\ Must be instance of Carbon
    $to, \\ Must be instance of Carbon
    'Summary of the event'
))->createEvent();
```
## License

This laravel-teams-logger package is available under the MIT license. See the LICENSE file for more info.
