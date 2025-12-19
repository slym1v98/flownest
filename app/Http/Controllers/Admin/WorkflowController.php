<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostPendingReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class WorkflowController extends Controller
{
    /**
     * Submit a post for review.
     */
    public function submitForReview(Request $request, Post $post): RedirectResponse
    {
        // Check if user can edit posts
        if (! $request->user()->can('edit-posts')) {
            abort(403);
        }

        // Check if user owns the post or is admin
        if ($post->user_id !== $request->user()->id && ! $request->user()->hasRole('Admin')) {
            abort(403);
        }

        // Update post status
        $post->update([
            'status' => 'pending_review',
        ]);

        // Create revision
        $post->createRevision('Submitted for review');

        // Notify publishers
        $publishers = User::role('Publisher')->get();
        if ($publishers->count() > 0) {
            Notification::send($publishers, new PostPendingReview($post));
        }

        return redirect()->back()->with('success', 'Post submitted for review.');
    }

    /**
     * Approve a post.
     */
    public function approve(Request $request, Post $post): RedirectResponse
    {
        // Check if user can publish posts
        if (! $request->user()->can('publish-posts')) {
            abort(403);
        }

        $validated = $request->validate([
            'review_notes' => 'nullable|string|max:1000',
        ]);

        // Update post status
        $post->update([
            'status' => 'published',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'] ?? null,
        ]);

        // Create revision
        $post->createRevision('Post approved and published');

        return redirect()->back()->with('success', 'Post approved and published.');
    }

    /**
     * Reject a post.
     */
    public function reject(Request $request, Post $post): RedirectResponse
    {
        // Check if user can publish posts
        if (! $request->user()->can('publish-posts')) {
            abort(403);
        }

        $validated = $request->validate([
            'review_notes' => 'required|string|max:1000',
        ]);

        // Update post status back to draft
        $post->update([
            'status' => 'draft',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'],
        ]);

        // Create revision
        $post->createRevision('Post rejected: ' . $validated['review_notes']);

        // Notify post author
        $post->user->notify(new \Illuminate\Notifications\Messages\MailMessage());

        return redirect()->back()->with('success', 'Post rejected and returned to draft.');
    }

    /**
     * Restore a post from revision.
     */
    public function restore(Request $request, Post $post, int $revisionId): RedirectResponse
    {
        // Check if user can edit posts
        if (! $request->user()->can('edit-posts')) {
            abort(403);
        }

        $revision = $post->revisions()->findOrFail($revisionId);

        // Create a revision before restoring
        $post->createRevision('Before restoring to revision #' . $revisionId);

        // Restore post from revision
        $post->update([
            'title' => $revision->title,
            'slug' => $revision->slug,
            'content' => $revision->content,
            'excerpt' => $revision->excerpt,
            'is_featured' => $revision->is_featured,
            'seo_data' => $revision->seo_data,
        ]);

        // Create new revision for the restore action
        $post->createRevision('Restored from revision #' . $revisionId);

        return redirect()->back()->with('success', 'Post restored from revision.');
    }
}
