<?php

namespace App\Domain\ThoughtSynthesis\Requests;

use App\Domain\Space\Models\Space;
use Illuminate\Foundation\Http\FormRequest;

class StoreThoughtSynthesisRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Space $space */
        $space = $this->route('space');

        return $this->user()->can('view', $space);
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:2000'],
            'thought_ids' => ['required', 'array', 'min:2'],
            'thought_ids.*' => ['integer', 'distinct', 'exists:thoughts,id'],
        ];
    }
}
