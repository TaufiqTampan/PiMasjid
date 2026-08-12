<?php

namespace App\Http\Controllers;

use App\Models\CommitteeMember;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommitteeMemberController extends Controller
{
    public function index()
    {
        $members = CommitteeMember::orderBy('division')
            ->orderBy('order')
            ->paginate(20);

        return Inertia::render('CommitteeMembers/Index', [
            'members' => $members,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'division' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'order' => 'required|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            if (env('CLOUDINARY_URL')) {
                $result = CloudinaryService::upload($request->file('photo'), 'committee');
                $validated['photo_path'] = $result['url'];
                $validated['cloudinary_public_id'] = $result['public_id'];
            } else {
                $path = $request->file('photo')->store('committee', 'public');
                $validated['photo_path'] = $path;
            }
        }

        CommitteeMember::create($validated);

        return redirect()->back()->with('success', 'Pengurus berhasil ditambahkan.');
    }

    public function update(Request $request, CommitteeMember $committeeMember)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'division' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'order' => 'required|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            if (env('CLOUDINARY_URL')) {
                // Delete old photo from Cloudinary
                if ($committeeMember->cloudinary_public_id) {
                    CloudinaryService::delete($committeeMember->cloudinary_public_id);
                }

                $result = CloudinaryService::upload($request->file('photo'), 'committee');
                $validated['photo_path'] = $result['url'];
                $validated['cloudinary_public_id'] = $result['public_id'];
            } else {
                // Delete old local photo
                if ($committeeMember->photo_path && ! str_starts_with($committeeMember->photo_path, 'http')) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($committeeMember->photo_path);
                }

                $path = $request->file('photo')->store('committee', 'public');
                $validated['photo_path'] = $path;
                $validated['cloudinary_public_id'] = null;
            }
        }

        $committeeMember->update($validated);

        return redirect()->back()->with('success', 'Data pengurus berhasil diperbarui.');
    }

    public function destroy(CommitteeMember $committeeMember)
    {
        // Delete from Cloudinary
        if ($committeeMember->cloudinary_public_id) {
            CloudinaryService::delete($committeeMember->cloudinary_public_id);
        }

        $committeeMember->delete();

        return redirect()->back()->with('success', 'Pengurus berhasil dihapus.');
    }
}
