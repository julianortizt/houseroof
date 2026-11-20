<?php

use Illuminate\Support\Facades\Schedule;

// Schedule the queue worker to run every minute
Schedule::command('queue:work --stop-when-empty')->everyMinute();
