<?php

namespace App\Http\Requests\Posts;

use App\Traits\Http\ReturnJsonValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    use ReturnJsonValidation;

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', 'in:video,news'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
