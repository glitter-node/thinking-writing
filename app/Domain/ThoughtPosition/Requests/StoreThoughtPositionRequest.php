<?php

namespace App\Domain\ThoughtPosition\Requests;

use App\Domain\Thought\Models\Thought;
use Illuminate\Foundation\Http\FormRequest;

class StoreThoughtPositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Thought $thought */
        $thought = $this->route('thought');

        return $this->user()->id === $thought->user_id;
    }

    public function rules(): array
    {
        return [
            'x' => ['required', 'integer', 'between:-10000,10000'],
            'y' => ['required', 'integer', 'between:-10000,10000'],
        ];
    }
}
