<?php

namespace App\Domain\Space\Requests;

use App\Domain\Space\Models\Space;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSpaceRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
