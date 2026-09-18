<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Post $post */
        $post = $this->route('post');

        return $this->user()?->can('update', $post) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'category_id' => [
                'sometimes',
                'required',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'content' => ['sometimes', 'required', 'string'],
            'is_approved' => ['sometimes', 'boolean'],
            'published_at' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'Seçilen kategori aktif değil veya bulunamadı.',
        ];
    }
}
