<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tool Lending Module - Loan Reminders Scheduler
Schedule::command('loans:check-reminders')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        info('Loan reminders check completed successfully');
    })
    ->onFailure(function () {
        logger()->error('Loan reminders check failed');
    });
