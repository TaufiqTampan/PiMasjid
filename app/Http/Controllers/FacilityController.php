<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class FacilityController extends Controller
{
    /**
     * Display admin panel for facilities and bookings.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('manage_operations');

        $facilities = Facility::withCount(['bookings', 'pendingBookings'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'slug' => $f->slug,
                'facility_type' => $f->facility_type,
                'facility_type_label' => $f->facility_type_label,
                'capacity' => $f->capacity,
                'description' => $f->description,
                'terms' => $f->terms,
                'image_url' => $f->image_url,
                'is_active' => $f->is_active,
                'bookings_count' => $f->bookings_count,
                'pending_bookings_count' => $f->pending_bookings_count,
            ]);

        $bookings = FacilityBooking::with('facility', 'approvedBy')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('facility_id'), fn ($q) => $q->where('facility_id', $request->facility_id))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(fn ($b) => [
                'id' => $b->id,
                'booking_code' => $b->booking_code,
                'facility_name' => $b->facility->name ?? '-',
                'facility_id' => $b->facility_id,
                'borrower_name' => $b->borrower_name,
                'borrower_phone' => $b->borrower_phone,
                'borrower_address' => $b->borrower_address,
                'event_name' => $b->event_name,
                'event_description' => $b->event_description,
                'start_time' => $b->start_time?->format('d M Y H:i'),
                'end_time' => $b->end_time?->format('d M Y H:i'),
                'duration' => $b->duration,
                'status' => $b->status,
                'status_label' => $b->status_label,
                'admin_notes' => $b->admin_notes,
                'approved_by_name' => $b->approvedBy?->name,
                'approved_at' => $b->approved_at?->format('d M Y H:i'),
                'created_at' => $b->created_at->format('d M Y H:i'),
            ]);

        $stats = [
            'total_facilities' => Facility::count(),
            'active_facilities' => Facility::active()->count(),
            'pending_bookings' => FacilityBooking::pending()->count(),
            'approved_bookings' => FacilityBooking::approved()->count(),
        ];

        return Inertia::render('Admin/Facilities/Index', [
            'facilities' => $facilities,
            'bookings' => $bookings,
            'stats' => $stats,
            'filters' => $request->only(['status', 'facility_id']),
        ]);
    }

    /**
     * Store a new facility.
     */
    public function store(Request $request)
    {
        Gate::authorize('manage_operations');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'facility_type' => 'required|in:room,equipment',
            'capacity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'terms' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'is_active' => 'boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $result = CloudinaryService::upload($request->file('image'), 'facilities');
            $imagePath = $result['url'];
        }

        Facility::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::random(4),
            'facility_type' => $validated['facility_type'],
            'capacity' => $validated['capacity'] ?? null,
            'description' => $validated['description'] ?? null,
            'terms' => $validated['terms'] ?? null,
            'image_path' => $imagePath,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->back()->with('success', 'Fasilitas berhasil ditambahkan!');
    }

    /**
     * Update a facility.
     */
    public function update(Request $request, Facility $facility)
    {
        Gate::authorize('manage_operations');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'facility_type' => 'required|in:room,equipment',
            'capacity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'terms' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'is_active' => 'boolean',
        ]);

        $imagePath = $facility->image_path;
        if ($request->hasFile('image')) {
            $result = CloudinaryService::upload($request->file('image'), 'facilities');
            $imagePath = $result['url'];
        }

        $facility->update([
            'name' => $validated['name'],
            'facility_type' => $validated['facility_type'],
            'capacity' => $validated['capacity'] ?? null,
            'description' => $validated['description'] ?? null,
            'terms' => $validated['terms'] ?? null,
            'image_path' => $imagePath,
            'is_active' => $validated['is_active'] ?? $facility->is_active,
        ]);

        return redirect()->back()->with('success', 'Fasilitas berhasil diperbarui!');
    }

    /**
     * Delete a facility.
     */
    public function destroy(Facility $facility)
    {
        Gate::authorize('manage_operations');

        // Check for active/pending bookings before deleting
        if ($facility->bookings()->whereIn('status', ['pending', 'approved'])->exists()) {
            return redirect()->back()->with('error', 'Fasilitas tidak dapat dihapus karena masih ada booking aktif!');
        }

        $facility->delete();

        return redirect()->back()->with('success', 'Fasilitas berhasil dihapus!');
    }

    /**
     * Update booking status (approve/reject/complete).
     */
    public function updateBookingStatus(Request $request, FacilityBooking $booking)
    {
        Gate::authorize('manage_operations');

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,completed,cancelled',
            'admin_notes' => 'nullable|string',
        ]);

        $updateData = [
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $booking->admin_notes,
        ];

        if (in_array($validated['status'], ['approved', 'rejected'])) {
            $updateData['approved_by'] = auth()->id();
            $updateData['approved_at'] = now();
        }

        $booking->update($updateData);

        $messages = [
            'approved' => 'Booking berhasil disetujui!',
            'rejected' => 'Booking berhasil ditolak.',
            'completed' => 'Booking ditandai selesai.',
            'cancelled' => 'Booking dibatalkan.',
        ];

        return redirect()->back()->with('success', $messages[$validated['status']]);
    }

    /**
     * Delete a booking.
     */
    public function destroyBooking(FacilityBooking $booking)
    {
        Gate::authorize('manage_operations');

        $booking->delete();

        return redirect()->back()->with('success', 'Data booking berhasil dihapus!');
    }
}
