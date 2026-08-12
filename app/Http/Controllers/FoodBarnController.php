<?php

namespace App\Http\Controllers;

use App\Models\FoodBarnDonation;
use App\Models\FoodBarnProgram;
use App\Models\FoodBarnRequest;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FoodBarnController extends Controller
{
    /**
     * Display the public Food Barn page.
     */
    public function publicIndex(): Response
    {
        $programs = FoodBarnProgram::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total_programs' => FoodBarnProgram::count(),
            'total_distributed' => FoodBarnProgram::sum('distributed_amount'),
            'total_donors' => FoodBarnDonation::where('status', 'approved')->count(),
            'total_collected' => FoodBarnProgram::sum('collected_amount'),
        ];

        return Inertia::render('Public/LumbungPangan', [
            'programs' => $programs,
            'stats' => $stats,
        ]);
    }

    /**
     * Submit a donation notification.
     */
    public function publicDonate(Request $request)
    {
        $validated = $request->validate([
            'donor_name' => 'required|string|max:255',
            'donor_phone' => 'required|string|max:20',
            'donation_type' => 'required|in:uang,barang',
            'food_barn_program_id' => 'nullable|exists:food_barn_programs,id',
            'amount' => 'required_if:donation_type,uang|nullable|numeric|min:0',
            'items' => 'required_if:donation_type,barang|nullable|string',
            'proof' => 'nullable|image|max:5120',
        ]);

        $proofUrl = null;
        if ($request->hasFile('proof')) {
            $result = CloudinaryService::upload($request->file('proof'), 'donations');
            $proofUrl = $result['url'];
        }

        // Remove 'proof' file object from array to prevent DB error and add 'proof_url'
        $data = collect($validated)->except(['proof'])->toArray();
        $data['proof_url'] = $proofUrl;
        $data['status'] = 'pending';

        FoodBarnDonation::create($data);

        return redirect()->back()->with('success', 'Pemberitahuan donasi Anda berhasil dikirim! Pengurus akan segera memverifikasi.');
    }

    /**
     * Submit an assistance request.
     */
    public function publicRequest(Request $request)
    {
        $validated = $request->validate([
            'food_barn_program_id' => 'required|exists:food_barn_programs,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'family_members' => 'required|integer|min:1',
            'reason' => 'required|string',
        ]);

        FoodBarnRequest::create($validated);

        return redirect()->back()->with('success', 'Permohonan bantuan Anda telah diajukan. Kami akan menghubungi Anda setelah proses verifikasi selesai.');
    }
}
