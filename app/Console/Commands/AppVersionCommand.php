<?php

namespace App\Console\Commands;

use App\Services\AppVersionService;
use Illuminate\Console\Command;

class AppVersionCommand extends Command
{
    protected $signature = 'app:version';

    protected $description = 'Display the current application version';

    public function __construct(
        private readonly AppVersionService $appVersionService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->line('Application Version: '.$this->appVersionService->getVersion());

        return self::SUCCESS;
    }
}
