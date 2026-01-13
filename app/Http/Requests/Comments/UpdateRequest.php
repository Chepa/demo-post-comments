<?php

namespace App\Http\Requests\Comments;

use App\Traits\Http\ReturnJsonValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    use ReturnJsonValidation;

    public function rules(): array
    {
        return [
            'body' => ['required', 'string'],
        ];
    }
}
