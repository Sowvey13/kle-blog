<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'is_approved' => $this->is_approved,
            'created_at' => $this->created_at?->toDateTimeString(),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}