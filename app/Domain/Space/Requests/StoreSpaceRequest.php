<?php

namespace App\Domain\Space\Requests;

use App\Domain\Space\Models\Space;
use Illuminate\Foundation\Http\FormRequest;

class StoreSpaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Space::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
