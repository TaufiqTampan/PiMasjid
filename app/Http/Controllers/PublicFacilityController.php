<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\FacilityBooking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicFacilityController extends Controller
{
    /**
     * Display public facility listing page.
     */
    public function index(): Response
    {
        $facilities = Facility::active()
            ->orderBy('facility_type')
            ->orderBy('name')
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
            ]);

        return Inertia::render('Public/Facilities', [
            'facilities' => $facilities,
        ]);
    }

    /**
     * Submit a booking request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'borrower_name' => 'required|string|max:255',
            'borrower_phone' => 'required|string|max:20',
            'borrower_address' => 'nullable|string',
            'event_name' => 'required|string|max:255',
            'event_description' => 'nullable|string',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        $facility = Facility::findOrFail($validated['facility_id']);

        // Check availability
        if (! $facility->isAvailable($validated['start_time'], $validated['end_time'])) {
            return redirect()->back()
                ->withErrors(['start_time' => 'Fasilitas tidak tersedia pada waktu tersebut. Silakan pilih waktu lain.'])
                ->withInput();
        }

        $booking = FacilityBooking::create([
            ...$validated,
            'booking_code' => FacilityBooking::generateBookingCode(),
            'status' => 'pending',
        ]);

        return redirect()->back()
            ->with('success', 'Permohonan peminjaman berhasil dikirim! Kode booking Anda adalah '.$booking->booking_code)
            ->with('booking', [
                'id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'borrower_name' => $booking->borrower_name,
                'borrower_phone' => $booking->borrower_phone,
                'event_name' => $booking->event_name,
                'facility_name' => $facility->name,
                'start_time' => $booking->start_time?->format('d M Y H:i'),
                'end_time' => $booking->end_time?->format('d M Y H:i'),
            ]);
    }

    /**
     * Check booking status by code.
     */
    public function checkStatus(Request $request)
    {
        $code = $request->input('code');

        if (! $code) {
            return response()->json(['error' => 'Kode booking diperlukan.'], 422);
        }

        $booking = FacilityBooking::with('facility')
            ->where('booking_code', strtoupper($code))
            ->first();

        if (! $booking) {
            return response()->json(['error' => 'Kode booking tidak ditemukan.'], 404);
        }

        return response()->json([
            'booking_code' => $booking->booking_code,
            'facility_name' => $booking->facility->name ?? '-',
            'borrower_name' => $booking->borrower_name,
            'event_name' => $booking->event_name,
            'start_time' => $booking->start_time?->format('d M Y H:i'),
            'end_time' => $booking->end_time?->format('d M Y H:i'),
            'status' => $booking->status,
            'status_label' => $booking->status_label,
            'admin_notes' => $booking->admin_notes,
        ]);
    }
}
