<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'author' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'featured_image' => $this->getFirstMediaUrl('images', 'preview') ?: null,
            'thumbnail' => $this->getFirstMediaUrl('images', 'thumb') ?: null,
            'images' => $this->getMedia('images')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'name' => $media->name,
                    'url' => $media->getUrl(),
                    'preview_url' => $media->getUrl('preview'),
                    'thumb_url' => $media->getUrl('thumb'),
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                ];
            }),
            'seo' => [
                'meta_title' => $this->seo_data['meta_title'] ?? $this->title,
                'meta_description' => $this->seo_data['meta_description'] ?? $this->excerpt,
                'meta_keywords' => $this->seo_data['meta_keywords'] ?? '',
                'og_title' => $this->seo_data['og_title'] ?? null,
                'og_description' => $this->seo_data['og_description'] ?? null,
                'og_image' => $this->seo_data['og_image'] ?? null,
            ],
        ];
    }
}
