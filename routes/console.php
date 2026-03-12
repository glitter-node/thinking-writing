<?php

use App\Jobs\BuildCooccurrenceJob;
use App\Jobs\BuildTagClustersJob;
use App\Jobs\RebuildGraphIndexJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new RebuildGraphIndexJob())->daily();
Schedule::job(new BuildTagClustersJob())->daily();
Schedule::job(new BuildCooccurrenceJob())->daily();
