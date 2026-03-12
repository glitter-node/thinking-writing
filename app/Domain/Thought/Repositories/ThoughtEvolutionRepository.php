<?php

namespace App\Domain\Thought\Repositories;

use App\Domain\Thought\Models\Thought;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ThoughtEvolutionRepository
{
    public function getThreadThoughts(Thought $thought): Collection
    {
        $rows = DB::select(
            <<<'SQL'
                WITH RECURSIVE ancestors AS (
                    SELECT id, parent_id
                    FROM thoughts
                    WHERE id = ?
                    UNION ALL
                    SELECT t.id, t.parent_id
                    FROM thoughts t
                    INNER JOIN ancestors a ON a.parent_id = t.id
                ),
                descendants AS (
                    SELECT id, parent_id
                    FROM thoughts
                    WHERE id = ?
                    UNION ALL
                    SELECT t.id, t.parent_id
                    FROM thoughts t
                    INNER JOIN descendants d ON t.parent_id = d.id
                )
                SELECT DISTINCT id
                FROM (
                    SELECT id FROM ancestors
                    UNION ALL
                    SELECT id FROM descendants
                ) thread_ids
            SQL,
            [$thought->id, $thought->id],
        );

        $ids = collect($rows)->pluck('id')->all();

        return Thought::query()
            ->with(['stream:id,space_id,title'])
            ->whereIn('id', $ids)
            ->orderBy('created_at')
            ->get();
    }
}
