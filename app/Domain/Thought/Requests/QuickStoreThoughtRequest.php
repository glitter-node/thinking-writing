<?php

namespace App\Domain\Thought\Requests;

use App\Domain\Space\Models\Space;
use Illuminate\Foundation\Http\FormRequest;

class QuickStoreThoughtRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Space $space */
        $space = $this->route('space');

        return $this->user()->can('update', $space);
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:2000'],
        ];
    }
}
