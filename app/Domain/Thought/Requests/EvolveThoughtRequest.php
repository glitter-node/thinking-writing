<?php

namespace App\Domain\Thought\Requests;

use App\Domain\Thought\Models\Thought;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EvolveThoughtRequest extends FormRequest
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
            'content' => ['required', 'string', 'max:2000'],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'tags' => ['nullable'],
        ];
    }
}
