<?php

namespace App\Domain\Thought\Services;

use App\Domain\Thought\Models\Thought;
use App\Models\User;

class ThoughtExportService
{
    public function export(User $user, string $format = 'json'): string
    {
        $thoughts = Thought::query()
            ->withTrashed()
            ->with(['stream.space', 'versions', 'events'])
            ->where('user_id', $user->id)
            ->orderBy('id')
            ->get();

        if ($format === 'markdown') {
            return $this->toMarkdown($thoughts->all());
        }

        return json_encode($thoughts->map(function (Thought $thought): array {
            return [
                'id' => $thought->id,
                'space' => $thought->stream->space->title,
                'stream' => $thought->stream->title,
                'content' => $thought->content,
                'priority' => $thought->priority,
                'stage' => $thought->stage,
                'deleted_at' => $thought->deleted_at?->toIso8601String(),
                'versions' => $thought->versions->map(fn ($version) => [
                    'version' => $version->version,
                    'content' => $version->content,
                    'created_at' => $version->created_at->toIso8601String(),
                ])->all(),
                'events' => $thought->events->map(fn ($event) => [
                    'event_type' => $event->event_type,
                    'metadata' => $event->metadata ?? [],
                    'created_at' => $event->created_at->toIso8601String(),
                ])->all(),
            ];
        })->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '[]';
    }

    private function toMarkdown(array $thoughts): string
    {
        $lines = ['# ThinkWrite Thought Export', ''];

        foreach ($thoughts as $thought) {
            $lines[] = '## Thought '.$thought->id;
            $lines[] = '- Space: '.$thought->stream->space->title;
            $lines[] = '- Stream: '.$thought->stream->title;
            $lines[] = '- Priority: '.$thought->priority;
            $lines[] = '- Stage: '.($thought->stage ?? 'thought');
            $lines[] = '- Archived: '.($thought->deleted_at ? 'yes' : 'no');
            $lines[] = '';
            $lines[] = $thought->content;
            $lines[] = '';
            $lines[] = '### Versions';

            foreach ($thought->versions as $version) {
                $lines[] = '- v'.$version->version.': '.$version->content;
            }

            $lines[] = '### Events';

            foreach ($thought->events as $event) {
                $lines[] = '- '.$event->event_type.' @ '.$event->created_at->toIso8601String();
            }

            $lines[] = '';
        }

        return implode("\n", $lines);
    }
}
