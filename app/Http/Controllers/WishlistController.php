<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    /**
     * Display a listing of all wishlists for admin.
     */
    public function index(): Response
    {
        Gate::authorize('manage_operations');

        $wishlists = Wishlist::orderBy('created_at', 'desc')->get()->map(fn ($w) => [
            'id' => $w->id,
            'item_name' => $w->item_name,
            'target_qty' => $w->target_qty,
            'fulfilled_qty' => $w->fulfilled_qty,
            'remaining_qty' => $w->remaining_qty,
            'unit_price' => $w->unit_price,
            'formatted_unit_price' => $w->formatted_unit_price,
            'formatted_total_target' => $w->formatted_total_target,
            'progress_percentage' => $w->progress_percentage,
            'status' => $w->status,
            'status_label' => $w->status_label,
            'description' => $w->description,
            'created_at' => $w->created_at->format('d M Y'),
        ]);

        $stats = [
            'total' => Wishlist::count(),
            'active' => Wishlist::active()->count(),
            'completed' => Wishlist::completed()->count(),
            'pending' => Wishlist::pending()->count(),
        ];

        return Inertia::render('Admin/Wishlists/Index', [
            'wishlists' => $wishlists,
            'stats' => $stats,
        ]);
    }

    /**
     * Store a new wishlist item.
     */
    public function store(Request $request)
    {
        Gate::authorize('manage_operations');

        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_qty' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,pending,completed,cancelled',
        ]);

        Wishlist::create($validated);

        return redirect()->back()->with('success', 'Item wishlist berhasil ditambahkan!');
    }

    /**
     * Update an existing wishlist item.
     */
    public function update(Request $request, Wishlist $wishlist)
    {
        Gate::authorize('manage_operations');

        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_qty' => 'required|integer|min:1',
            'fulfilled_qty' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,pending,completed,cancelled',
        ]);

        $wishlist->update($validated);

        // Auto-complete if fulfilled >= target
        if ($wishlist->fulfilled_qty >= $wishlist->target_qty && $wishlist->status === 'active') {
            $wishlist->update(['status' => 'completed']);
        }

        return redirect()->back()->with('success', 'Item wishlist berhasil diperbarui!');
    }

    /**
     * Delete a wishlist item.
     */
    public function destroy(Wishlist $wishlist)
    {
        Gate::authorize('manage_operations');

        $wishlist->delete();

        return redirect()->back()->with('success', 'Item wishlist berhasil dihapus!');
    }
}
