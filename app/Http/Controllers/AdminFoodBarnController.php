<?php

namespace App\Http\Controllers;

use App\Models\FoodBarnProgram;
use App\Models\FoodBarnDonation;
use App\Models\FoodBarnRequest;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class AdminFoodBarnController extends Controller
{
    /**
     * Display a listing of resources for admin.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('manage_operations');

        $programs = FoodBarnProgram::orderBy('created_at', 'desc')->get();
        $donations = FoodBarnDonation::with('program')->orderBy('created_at', 'desc')->get();
        $requests = FoodBarnRequest::with('program')->orderBy('created_at', 'desc')->get();

        $stats = [
            'pending_donations' => FoodBarnDonation::where('status', 'pending')->count(),
            'pending_requests' => FoodBarnRequest::where('status', 'pending')->count(),
            'total_programs' => FoodBarnProgram::count(),
            'total_distributed' => FoodBarnProgram::sum('distributed_amount'),
        ];

        return Inertia::render('Admin/LumbungPangan/Index', [
            'programs' => $programs,
            'donations' => $donations,
            'requests' => $requests,
            'stats' => $stats,
        ]);
    }

    /**
     * Store a new program.
     */
    public function storeProgram(Request $request)
    {
        Gate::authorize('manage_operations');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'target_amount' => 'required|integer|min:1',
            'image' => 'nullable|image|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $result = CloudinaryService::upload($request->file('image'), 'programs');
            $imagePath = $result['url'];
        }

        FoodBarnProgram::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'target_amount' => $validated['target_amount'],
            'collected_amount' => 0,
            'distributed_amount' => 0,
            'status' => 'active',
            'image_url' => $imagePath,
        ]);

        return redirect()->back()->with('success', 'Program lumbung pangan berhasil ditambahkan!');
    }

    /**
     * Update a program.
     */
    public function updateProgram(Request $request, FoodBarnProgram $program)
    {
        Gate::authorize('manage_operations');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'target_amount' => 'required|integer|min:1',
            'collected_amount' => 'required|integer|min:0',
            'distributed_amount' => 'required|integer|min:0',
            'status' => 'required|in:active,completed',
            'image' => 'nullable|image|max:5120',
        ]);

        $imagePath = $program->image_url;
        if ($request->hasFile('image')) {
            $result = CloudinaryService::upload($request->file('image'), 'programs');
            $imagePath = $result['url'];
        }

        $program->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'target_amount' => $validated['target_amount'],
            'collected_amount' => $validated['collected_amount'],
            'distributed_amount' => $validated['distributed_amount'],
            'status' => $validated['status'],
            'image_url' => $imagePath,
        ]);

        return redirect()->back()->with('success', 'Program lumbung pangan berhasil diperbarui!');
    }

    /**
     * Delete a program.
     */
    public function destroyProgram(FoodBarnProgram $program)
    {
        Gate::authorize('manage_operations');

        $program->delete();

        return redirect()->back()->with('success', 'Program lumbung pangan berhasil dihapus!');
    }

    /**
     * Update donation status.
     */
    public function updateDonationStatus(Request $request, FoodBarnDonation $donation)
    {
        Gate::authorize('manage_operations');

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $oldStatus = $donation->status;
        $newStatus = $validated['status'];

        $donation->update(['status' => $newStatus]);

        // Automatically update collected amount in the associated program (if linked)
        if ($donation->food_barn_program_id) {
            $program = FoodBarnProgram::find($donation->food_barn_program_id);
            if ($program) {
                // Use actual donation amount (or 1 paket for non-monetary donations)
                $donationValue = ($donation->donation_type === 'uang')
                    ? ($donation->amount ?? 0)
                    : 1;
                if ($oldStatus !== 'approved' && $newStatus === 'approved') {
                    $program->increment('collected_amount', $donationValue);
                } elseif ($oldStatus === 'approved' && $newStatus !== 'approved') {
                    $program->decrement('collected_amount', $donationValue);
                }
            }
        }

        return redirect()->back()->with('success', 'Status donasi berhasil diperbarui!');
    }

    /**
     * Delete a donation.
     */
    public function destroyDonation(FoodBarnDonation $donation)
    {
        Gate::authorize('manage_operations');

        // Decrement program collected_amount if approved donation is deleted
        if ($donation->status === 'approved' && $donation->food_barn_program_id) {
            $program = FoodBarnProgram::find($donation->food_barn_program_id);
            if ($program) {
                $donationValue = ($donation->donation_type === 'uang')
                    ? ($donation->amount ?? 0)
                    : 1;
                $program->decrement('collected_amount', $donationValue);
            }
        }

        $donation->delete();

        return redirect()->back()->with('success', 'Log donasi berhasil dihapus!');
    }

    /**
     * Update assistance request status.
     */
    public function updateRequestStatus(Request $request, FoodBarnRequest $requestItem)
    {
        Gate::authorize('manage_operations');

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,distributed',
        ]);

        $oldStatus = $requestItem->status;
        $newStatus = $validated['status'];

        $requestItem->update(['status' => $newStatus]);

        $program = FoodBarnProgram::find($requestItem->food_barn_program_id);
        if ($program) {
            // Logic for transitions
            if ($oldStatus !== 'distributed' && $newStatus === 'distributed') {
                $program->increment('distributed_amount');
                if ($program->collected_amount > 0) {
                    $program->decrement('collected_amount');
                }
            } elseif ($oldStatus === 'distributed' && $newStatus !== 'distributed') {
                $program->decrement('distributed_amount');
                $program->increment('collected_amount');
            }
        }

        return redirect()->back()->with('success', 'Status permohonan bantuan berhasil diperbarui!');
    }

    /**
     * Delete an assistance request.
     */
    public function destroyRequest(FoodBarnRequest $requestItem)
    {
        Gate::authorize('manage_operations');

        if ($requestItem->status === 'distributed') {
            $program = FoodBarnProgram::find($requestItem->food_barn_program_id);
            if ($program) {
                $program->decrement('distributed_amount');
            }
        }

        $requestItem->delete();

        return redirect()->back()->with('success', 'Permohonan bantuan berhasil dihapus!');
    }
}
