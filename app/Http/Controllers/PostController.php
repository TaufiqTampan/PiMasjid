<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::with('author')
            ->latest('published_at')
            ->get();

        return Inertia::render('Posts/Index', [
            'posts' => $posts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'photo' => 'nullable|image|max:2048',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $imagePath = null;
        $cloudinaryPublicId = null;

        if ($request->hasFile('photo')) {
            $result = CloudinaryService::upload($request->file('photo'), 'posts');
            $imagePath = $result['url'];
            $cloudinaryPublicId = $result['public_id'];
        }

        $excerpt = $validated['excerpt'];
        if (empty($excerpt)) {
            $excerpt = strip_tags($validated['content']);
            $excerpt = mb_substr($excerpt, 0, 160).(mb_strlen($excerpt) > 160 ? '...' : '');
        }

        Post::create([
            'title' => $validated['title'],
            'excerpt' => $excerpt,
            'content' => $validated['content'],
            'image_path' => $imagePath,
            'cloudinary_public_id' => $cloudinaryPublicId,
            'is_published' => $validated['is_published'] ?? false,
            'published_at' => ! empty($validated['published_at']) ? $validated['published_at'] : (($validated['is_published'] ?? false) ? now() : null),
            'author_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Berita berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'photo' => 'nullable|image|max:2048',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old from Cloudinary
            if ($post->cloudinary_public_id) {
                CloudinaryService::delete($post->cloudinary_public_id);
            }

            $result = CloudinaryService::upload($request->file('photo'), 'posts');
            $post->image_path = $result['url'];
            $post->cloudinary_public_id = $result['public_id'];
        }

        $post->title = $validated['title'];
        $post->excerpt = $validated['excerpt'];

        if (empty($post->excerpt)) {
            $excerpt = strip_tags($validated['content']);
            $post->excerpt = mb_substr($excerpt, 0, 160).(mb_strlen($excerpt) > 160 ? '...' : '');
        }

        $post->content = $validated['content'];
        $post->is_published = $validated['is_published'];

        if (isset($validated['published_at'])) {
            $post->published_at = $validated['published_at'];
        } elseif ($post->is_published && ! $post->published_at) {
            $post->published_at = now();
        }

        $post->save();

        return redirect()->back()->with('success', 'Berita berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        // Delete from Cloudinary
        if ($post->cloudinary_public_id) {
            CloudinaryService::delete($post->cloudinary_public_id);
        }

        $post->delete();

        return redirect()->back()->with('success', 'Berita berhasil dihapus.');
    }
}
