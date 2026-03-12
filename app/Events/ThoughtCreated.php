<?php

namespace App\Events;

use App\Domain\Thought\Models\Thought;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ThoughtCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Thought $thought,
        public array $context = [],
    ) {
    }
}
