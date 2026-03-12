<?php

use App\Services\AppVersionService;

if (! function_exists('app_version')) {
    function app_version(): string
    {
        return app(AppVersionService::class)->getVersion();
    }
}
