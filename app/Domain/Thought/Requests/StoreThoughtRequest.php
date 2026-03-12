<?php

namespace App\Domain\Thought\Requests;

use App\Domain\Stream\Models\Stream;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreThoughtRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Stream $stream */
        $stream = $this->route('stream');

        return $this->user()->id === $stream->space->user_id;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:2000'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'tags' => ['nullable', 'string', 'max:255'],
        ];
    }
}
