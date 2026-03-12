<?php

namespace App\Domain\Thought\Requests;

use App\Domain\Stream\Repositories\StreamRepository;
use App\Domain\Thought\Models\Thought;
use Illuminate\Foundation\Http\FormRequest;

class MoveThoughtRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Thought $thought */
        $thought = $this->route('thought');

        return $this->user()->can('update', $thought);
    }

    public function rules(): array
    {
        return [
            'stream_id' => ['required', 'integer', 'exists:streams,id'],
            'position' => ['required', 'integer', 'min:1'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                /** @var Thought $thought */
                $thought = $this->route('thought');
                $stream = app(StreamRepository::class)->findByIdOrNull($this->integer('stream_id'));

                if (! $stream || $stream->space_id !== $thought->stream->space_id) {
                    $validator->errors()->add('stream_id', 'Thoughts can only move within the same space.');
                }
            },
        ];
    }
}
