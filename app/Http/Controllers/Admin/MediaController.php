<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaItem;
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
            ->where('model_type', MediaItem::class)
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
            // Create a MediaItem to attach the file to
            $mediaItem = MediaItem::create([
                'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'user_id' => $request->user()->id,
            ]);

            $media = $mediaItem->addMedia($file)
                ->toMediaCollection($collection);

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
    public function destroy(Request $request, Media $media): JsonResponse
    {
        // Only allow deleting media attached to MediaItem
        if ($media->model_type !== MediaItem::class) {
            return response()->json([
                'message' => 'Cannot delete this media item',
            ], 403);
        }

        // Check authorization - user can only delete their own media
        $mediaItem = MediaItem::find($media->model_id);
        if ($mediaItem && $mediaItem->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to delete this media',
            ], 403);
        }

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
            ->where('model_type', MediaItem::class)
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
