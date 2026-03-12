<?php

namespace App\Domain\ThoughtReview\Requests;

use App\Domain\Thought\Models\Thought;
use App\Domain\ThoughtReview\Models\ThoughtReview;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreThoughtReviewRequest extends FormRequest
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
            'review_score' => ['required', Rule::in([
                ThoughtReview::SCORE_USEFUL,
                ThoughtReview::SCORE_ARCHIVE,
                ThoughtReview::SCORE_EVOLVE,
            ])],
        ];
    }
}
