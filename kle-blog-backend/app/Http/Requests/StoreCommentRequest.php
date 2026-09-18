<?php

namespace App\Http\Requests;

use App\Rules\PublishedPost;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'post_id' => ['required', 'integer', new PublishedPost],
            'content' => ['required', 'string'],
        ];
    }
}
