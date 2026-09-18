<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesPerPage;
use Illuminate\Foundation\Http\FormRequest;

class IndexCategoryRequest extends FormRequest
{
    use ValidatesPerPage;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->perPageRules();
    }
}
