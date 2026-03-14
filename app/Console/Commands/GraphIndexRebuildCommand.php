<?php

namespace App\Console\Commands;

use App\Domain\Thought\Services\ThoughtGraphIndexService;
use Illuminate\Console\Command;

class GraphIndexRebuildCommand extends Command
{
    protected $signature = 'graph:index:rebuild';

    protected $description = 'Rebuild the thought graph index';

    public function __construct(
        private readonly ThoughtGraphIndexService $thoughtGraphIndexService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->thoughtGraphIndexService->rebuildGraphIndex();
        $this->info('Thought graph index rebuilt.');

        return self::SUCCESS;
    }
}
