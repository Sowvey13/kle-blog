<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesPerPage;
use Illuminate\Foundation\Http\FormRequest;

class IndexPostRequest extends FormRequest
{
    use ValidatesPerPage;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            ...$this->perPageRules(),
            'search' => ['sometimes', 'string', 'max:255'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'date' => ['sometimes', 'date'],
        ];
    }
}
