<?php

namespace App\Http\Requests\Comments;

use App\Traits\Http\ReturnJsonValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    use ReturnJsonValidation;

    public function rules(): array
    {
        return [
            'body' => ['required', 'string'],
            'target_type' => ['required', Rule::in(['post', 'comment'])],
            'target_id' => ['required', 'integer'],
        ];
    }
}
