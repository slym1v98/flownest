<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    /**
     * Display a listing of the media.
     */
    public function index(Request $request): Response
    {
        $query = Media::query()
            ->orderBy('created_at', 'desc');

        // Filter by collection
        if ($request->input('collection')) {
            $query->where('collection_name', $request->input('collection'));
        }

        // Filter by search term
        if ($request->input('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        $media = $query->paginate(24)->through(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'file_name' => $item->file_name,
                'mime_type' => $item->mime_type,
                'size' => $item->size,
                'collection_name' => $item->collection_name,
                'url' => $item->getUrl(),
                'thumbnail_url' => $item->hasGeneratedConversion('thumb') 
                    ? $item->getUrl('thumb') 
                    : $item->getUrl(),
                'created_at' => $item->created_at->toISOString(),
                'updated_at' => $item->updated_at->toISOString(),
            ];
        });

        return Inertia::render('admin/media/Index', [
            'media' => $media,
            'filters' => [
                'search' => $request->input('search'),
                'collection' => $request->input('collection'),
            ],
        ]);
    }

    /**
     * Upload media files.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx|max:10240',
            'collection' => 'nullable|string',
        ]);

        $collection = $request->input('collection', 'default');
        $uploadedMedia = [];

        foreach ($request->file('files') as $file) {
            // Create a temporary model to attach media
            // Since we don't have a specific model, we'll use a generic approach
            // For now, we'll store media without a model association
            $media = Media::create([
                'model_type' => 'standalone',
                'model_id' => 0,
                'collection_name' => $collection,
                'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'disk' => config('media-library.disk_name', 'public'),
                'size' => $file->getSize(),
                'manipulations' => [],
                'custom_properties' => [],
                'generated_conversions' => [],
                'responsive_images' => [],
            ]);

            $media->copyMedia($file->getRealPath());

            $uploadedMedia[] = [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'collection_name' => $media->collection_name,
                'url' => $media->getUrl(),
                'thumbnail_url' => $media->hasGeneratedConversion('thumb') 
                    ? $media->getUrl('thumb') 
                    : $media->getUrl(),
                'created_at' => $media->created_at->toISOString(),
                'updated_at' => $media->updated_at->toISOString(),
            ];
        }

        return response()->json([
            'message' => 'Files uploaded successfully',
            'media' => $uploadedMedia,
        ], 201);
    }

    /**
     * Remove the specified media.
     */
    public function destroy(Media $media): JsonResponse
    {
        $media->delete();

        return response()->json([
            'message' => 'Media deleted successfully',
        ]);
    }

    /**
     * Get media items for selection (API endpoint).
     */
    public function list(Request $request): JsonResponse
    {
        $query = Media::query()
            ->orderBy('created_at', 'desc');

        // Filter by collection
        if ($request->input('collection')) {
            $query->where('collection_name', $request->input('collection'));
        }

        // Filter by search term
        if ($request->input('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        $media = $query->limit(50)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'file_name' => $item->file_name,
                'mime_type' => $item->mime_type,
                'size' => $item->size,
                'collection_name' => $item->collection_name,
                'url' => $item->getUrl(),
                'thumbnail_url' => $item->hasGeneratedConversion('thumb') 
                    ? $item->getUrl('thumb') 
                    : $item->getUrl(),
                'created_at' => $item->created_at->toISOString(),
                'updated_at' => $item->updated_at->toISOString(),
            ];
        });

        return response()->json([
            'media' => $media,
        ]);
    }
}
