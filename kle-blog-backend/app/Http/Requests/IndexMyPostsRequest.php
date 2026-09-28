<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesPerPage;
use Illuminate\Foundation\Http\FormRequest;

class IndexMyPostsRequest extends FormRequest
{
    use ValidatesPerPage;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return $this->perPageRules();
    }
}
