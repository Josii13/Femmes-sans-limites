<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('campaigns:dispatch')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('members:expire')
    ->dailyAt('07:00')
    ->withoutOverlapping();
