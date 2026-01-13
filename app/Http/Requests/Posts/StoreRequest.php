<?php

namespace App\Http\Requests\Posts;

use App\Traits\Http\ReturnJsonValidation;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    use ReturnJsonValidation;

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:video,news'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
