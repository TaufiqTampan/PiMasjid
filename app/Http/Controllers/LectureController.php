<?php

namespace App\Http\Controllers;

use App\Models\Lecture;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LectureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lectures = Lecture::latest('date')
            ->when(request('search'), function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('speaker', 'like', "%{$search}%");
            })
            ->get();

        return Inertia::render('Lectures/Index', [
            'lectures' => $lectures,
            'filters' => request()->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'speaker' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $imagePath = null;
        $cloudinaryPublicId = null;

        if ($request->hasFile('photo')) {
            $result = CloudinaryService::upload($request->file('photo'), 'lectures');
            $imagePath = $result['url'];
            $cloudinaryPublicId = $result['public_id'];
        }

        Lecture::create([
            'title' => $validated['title'],
            'speaker' => $validated['speaker'],
            'date' => $validated['date'],
            'time' => $validated['time'],
            'location' => $validated['location'],
            'image_path' => $imagePath,
            'cloudinary_public_id' => $cloudinaryPublicId,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->back()->with('success', 'Kajian berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lecture $lecture)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'speaker' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old from Cloudinary
            if ($lecture->cloudinary_public_id) {
                CloudinaryService::delete($lecture->cloudinary_public_id);
            }

            $result = CloudinaryService::upload($request->file('photo'), 'lectures');
            $lecture->image_path = $result['url'];
            $lecture->cloudinary_public_id = $result['public_id'];
        }

        $lecture->title = $validated['title'];
        $lecture->speaker = $validated['speaker'];
        $lecture->date = $validated['date'];
        $lecture->time = $validated['time'];
        $lecture->location = $validated['location'];
        $lecture->is_active = $validated['is_active'];

        $lecture->save();

        return redirect()->back()->with('success', 'Kajian berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lecture $lecture)
    {
        // Delete from Cloudinary
        if ($lecture->cloudinary_public_id) {
            CloudinaryService::delete($lecture->cloudinary_public_id);
        }

        $lecture->delete();

        return redirect()->back()->with('success', 'Kajian berhasil dihapus.');
    }
}
