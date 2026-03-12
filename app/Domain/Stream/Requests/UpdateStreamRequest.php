<?php

namespace App\Domain\Stream\Requests;

use App\Domain\Stream\Models\Stream;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStreamRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Stream $stream */
        $stream = $this->route('stream');

        return $this->user()->can('update', $stream);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:80'],
        ];
    }
}
