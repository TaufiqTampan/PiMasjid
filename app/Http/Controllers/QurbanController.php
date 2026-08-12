<?php

namespace App\Http\Controllers;

use App\Exports\QurbanExport;
use App\Models\Qurban;
use App\Models\QurbanDistribution;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class QurbanController extends Controller
{
    /**
     * Display a listing of qurbans.
     */
    public function index(Request $request): Response
    {
        $query = Qurban::with('registeredBy')
            ->latest();

        // Filter by animal type
        if ($request->filled('animal_type')) {
            $query->byAnimalType($request->animal_type);
        }

        // Filter by year
        $requestedYear = $request->input('year');
        if ($request->filled('year') && $requestedYear != '') {
            $query->byYear($requestedYear);
        } else {
            $latestYear = Qurban::max('year');
            if ($latestYear) {
                $query->byYear($latestYear);
            } else {
                $query->currentYear();
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter shared/individual
        if ($request->filled('share_type')) {
            if ($request->share_type === 'shared') {
                $query->shared();
            } elseif ($request->share_type === 'individual') {
                $query->individual();
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('participant_name', 'like', "%{$search}%")
                    ->orWhere('participant_nik', 'like', "%{$search}%");
            });
        }

        // Default sort: Grouped by share_group (so they stay together), then position
        $query->orderByRaw('share_group_id DESC, share_position ASC, created_at DESC');

        $qurbans = $query->paginate(20);

        // Summary statistics
        // Summary statistics
        $currentYear = $request->filled('year') ? $request->year : (Qurban::max('year') ?? now()->year);

        $individualPrice = Qurban::byYear($currentYear)->where('is_shared', false)->sum('animal_price');
        $sharedPrice = Qurban::byYear($currentYear)
            ->where('is_shared', true)
            ->selectRaw('share_group_id, AVG(animal_price) as price')
            ->groupBy('share_group_id')
            ->get()
            ->sum('price');

        $summary = [
            'total_participants' => Qurban::byYear($currentYear)->count(),
            'total_price' => $individualPrice + $sharedPrice,
            'kambing_count' => Qurban::byYear($currentYear)->byAnimalType('kambing')->count(),
            'sapi_count' => Qurban::byYear($currentYear)->byAnimalType('sapi')->where('is_shared', false)->count() +
                           Qurban::byYear($currentYear)->byAnimalType('sapi')->where('is_shared', true)->distinct('share_group_id')->count('share_group_id'),
            'registered_count' => Qurban::byYear($currentYear)->byStatus('registered')->count(),
            'paid_count' => Qurban::byYear($currentYear)->byStatus('paid')->count(),
            'slaughtered_count' => Qurban::byYear($currentYear)->byStatus('slaughtered')->count(),
            'distributed_count' => Qurban::byYear($currentYear)->byStatus('distributed')->count(),
        ];

        return Inertia::render('Qurban/Index', [
            'qurbans' => $qurbans,
            'summary' => $summary,
            'filters' => $request->only(['animal_type', 'year', 'status', 'share_type', 'search']),
            'current_year' => $currentYear,
        ]);
    }

    /**
     * Show the form for creating a new qurban registration.
     */
    public function create(): Response
    {
        // Get existing share groups for this year (for patungan)
        $currentYear = now()->year;
        $shareGroups = Qurban::byYear($currentYear)
            ->shared()
            ->whereNotNull('share_group_id')
            ->selectRaw('share_group_id, animal_type, COUNT(*) as participant_count, MAX(share_count) as max_share, AVG(animal_price) as avg_price, MAX(animal_weight) as max_weight, GROUP_CONCAT(share_position) as taken_positions')
            ->groupBy('share_group_id', 'animal_type')
            ->havingRaw('COUNT(*) < MAX(share_count)') // Only groups that aren't full
            ->get();

        return Inertia::render('Qurban/Create', [
            'current_year' => $currentYear,
            'available_share_groups' => $shareGroups,
        ]);
    }

    /**
     * Store a newly created qurban registration.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'participant_name' => 'required|string|max:255',
            'participant_nik' => 'nullable|string|max:20',
            'participant_phone' => 'required|string|max:20',
            'participant_address' => 'nullable|string',
            'animal_type' => 'required|in:kambing,domba,sapi,kerbau,unta',
            'animal_weight' => 'nullable|numeric|min:0',
            'animal_price' => 'required|numeric|min:0',
            'is_shared' => 'required|boolean',
            'share_count' => 'nullable|integer|min:1|max:7',
            'share_position' => 'nullable|integer|min:1',
            'share_group_id' => 'nullable|string',
            'year' => 'required|integer',
            'registration_date' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:registered,paid',
        ]);

        // Validation: kambing/domba max 1 share
        if (in_array($validated['animal_type'], ['kambing', 'domba']) && $validated['share_count'] > 1) {
            return back()->withErrors(['share_count' => 'Kambing/Domba tidak bisa dipatungankan.']);
        }

        // Validation: sapi/kerbau/unta max 7 shares
        if (in_array($validated['animal_type'], ['sapi', 'kerbau', 'unta']) && $validated['share_count'] > 7) {
            return back()->withErrors(['share_count' => 'Maksimal 7 orang untuk sapi/kerbau/unta.']);
        }

        $validated['registered_by'] = auth()->id();

        Qurban::create($validated);

        return redirect()->route('qurban.index')
            ->with('success', 'Qurban berhasil didaftarkan!');
    }

    /**
     * Show the form for editing the specified qurban.
     */
    public function edit(Qurban $qurban): Response
    {
        // Get existing share groups for this year (for patungan) - same as create
        $currentYear = now()->year;
        $shareGroups = Qurban::byYear($currentYear)
            ->shared()
            // Exclude current qurban's group from "full" check so we can keep it
            ->whereNotNull('share_group_id')
            ->selectRaw('share_group_id, animal_type, COUNT(*) as participant_count, MAX(share_count) as max_share, AVG(animal_price) as avg_price, MAX(animal_weight) as max_weight, GROUP_CONCAT(share_position) as taken_positions')
            ->groupBy('share_group_id', 'animal_type')
            ->havingRaw('COUNT(*) < MAX(share_count)') // Only groups that aren't full
            ->get();

        return Inertia::render('Qurban/Create', [
            'current_year' => $currentYear,
            'available_share_groups' => $shareGroups,
            'qurban' => $qurban,
        ]);
    }

    /**
     * Update the specified qurban in storage.
     */
    public function update(Request $request, Qurban $qurban)
    {
        $validated = $request->validate([
            'participant_name' => 'required|string|max:255',
            'participant_nik' => 'nullable|string|max:20',
            'participant_phone' => 'required|string|max:20',
            'participant_address' => 'nullable|string',
            'animal_type' => 'required|in:kambing,domba,sapi,kerbau,unta',
            'animal_weight' => 'nullable|numeric|min:0',
            'animal_price' => 'required|numeric|min:0',
            'is_shared' => 'required|boolean',
            'share_count' => 'nullable|integer|min:1|max:7',
            'share_position' => 'nullable|integer|min:1',
            'share_group_id' => 'nullable|string',
            'year' => 'required|integer',
            'registration_date' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:registered,paid',
        ]);

        // Validation: kambing/domba max 1 share
        if (in_array($validated['animal_type'], ['kambing', 'domba']) && $validated['share_count'] > 1) {
            return back()->withErrors(['share_count' => 'Kambing/Domba tidak bisa dipatungankan.']);
        }

        // Validation: sapi/kerbau/unta max 7 shares
        if (in_array($validated['animal_type'], ['sapi', 'kerbau', 'unta']) && $validated['share_count'] > 7) {
            return back()->withErrors(['share_count' => 'Maksimal 7 orang untuk sapi/kerbau/unta.']);
        }

        $qurban->update($validated);

        return redirect()->route('qurban.index')
            ->with('success', 'Data qurban berhasil diperbarui!');
    }

    /**
     * Update qurban status.
     */
    public function updateStatus(Request $request, Qurban $qurban)
    {
        $validated = $request->validate([
            'status' => 'required|in:registered,paid,slaughtered,distributed',
        ]);

        $qurban->update($validated);

        return redirect()->route('qurban.index')
            ->with('success', 'Status berhasil diupdate!');
    }

    /**
     * Show distribution form.
     */
    public function distribute(): Response
    {
        $currentYear = Qurban::max('year') ?? now()->year;

        // Get slaughtered qurbans (ready for distribution)
        $availableQurbans = Qurban::byYear($currentYear)
            ->whereIn('status', ['slaughtered', 'distributed'])
            ->with(['distributions'])
            ->get()
            ->map(function ($qurban) {
                $totalDistributed = $qurban->distributions->sum('meat_kg');

                return [
                    'id' => $qurban->id,
                    'participant_name' => $qurban->participant_name,
                    'animal_type' => $qurban->animal_type,
                    'animal_weight' => $qurban->animal_weight,
                    'total_distributed_kg' => $totalDistributed,
                    'remaining_kg' => $qurban->animal_weight - $totalDistributed,
                    'share_info' => $qurban->shareInfo,
                ];
            });

        // Distribution history
        $distributions = QurbanDistribution::with(['qurban', 'distributedBy'])
            ->whereHas('qurban', function ($q) use ($currentYear) {
                $q->byYear($currentYear);
            })
            ->latest()
            ->paginate(20);

        return Inertia::render('Qurban/Distribute', [
            'available_qurbans' => $availableQurbans,
            'distributions' => $distributions,
        ]);
    }

    /**
     * Store distribution.
     */
    public function storeDistribution(Request $request)
    {
        $validated = $request->validate([
            'qurban_id' => 'required|exists:qurbans,id',
            'recipient_name' => 'required|string|max:255',
            'recipient_type' => 'required|in:mustahik,aqiqah,participant,masjid',
            'meat_kg' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Check if enough meat remaining
        $qurban = Qurban::findOrFail($validated['qurban_id']);
        $totalDistributed = $qurban->distributions()->sum('meat_kg');
        $remaining = $qurban->animal_weight - $totalDistributed;

        if ($validated['meat_kg'] > $remaining) {
            return back()->withErrors(['meat_kg' => "Hanya tersisa {$remaining} kg daging."]);
        }

        $validated['distributed_by'] = auth()->id();

        QurbanDistribution::create($validated);

        // Update qurban status to distributed if all meat distributed
        if (($totalDistributed + $validated['meat_kg']) >= $qurban->animal_weight) {
            $qurban->update(['status' => 'distributed']);
        }

        return redirect()->route('qurban.distribute')
            ->with('success', 'Daging berhasil didistribusikan!');
    }

    /**
     * Show reports page.
     */
    public function reports(Request $request): Response
    {
        $year = $request->input('year', Qurban::max('year') ?? now()->year);

        // Summary
        $qurbans = Qurban::byYear($year)->get();

        // Calculate totals handling shared groups correctly
        $totalParticipants = $qurbans->count();

        $totalPrice = 0;
        $totalWeight = 0;

        // Individual
        $individual = $qurbans->where('is_shared', false);
        $totalPrice += $individual->sum('animal_price');
        $totalWeight += $individual->sum('animal_weight');

        // Shared
        $sharedGroups = $qurbans->where('is_shared', true)->groupBy('share_group_id');
        foreach ($sharedGroups as $group) {
            if ($group->isNotEmpty()) {
                $totalPrice += $group->avg('animal_price');
                $totalWeight += $group->max('animal_weight');
            }
        }

        // Breakdown by Animal Type
        $byAnimalType = $qurbans->groupBy('animal_type')->map(function ($items, $type) {
            $count = 0;
            $price = 0;

            // Individual
            $indiv = $items->where('is_shared', false);
            $count += $indiv->count();
            $price += $indiv->sum('animal_price');

            // Shared
            $shared = $items->where('is_shared', true)->groupBy('share_group_id');
            $count += $shared->count(); // Count animals (unique groups)

            foreach ($shared as $g) {
                if ($g->isNotEmpty()) {
                    $price += $g->avg('animal_price');
                }
            }

            return [
                'animal_type' => ucfirst($type),
                'count' => $count,
                'total_price' => $price,
            ];
        })->values();

        // Distribution stats
        $totalDistributed = QurbanDistribution::whereHas('qurban', function ($q) use ($year) {
            $q->byYear($year);
        })->sum('meat_kg');

        $distributionByType = QurbanDistribution::whereHas('qurban', function ($q) use ($year) {
            $q->byYear($year);
        })
            ->selectRaw('recipient_type, SUM(meat_kg) as total_kg')
            ->groupBy('recipient_type')
            ->get();

        return Inertia::render('Qurban/Reports', [
            'year' => $year,
            'summary' => [
                'total_participants' => $totalParticipants,
                'total_price' => $totalPrice,
                'total_weight_kg' => $totalWeight,
                'total_distributed_kg' => $totalDistributed,
                'remaining_kg' => $totalWeight - $totalDistributed,
            ],
            'by_animal_type' => $byAnimalType,
            'distribution_by_type' => $distributionByType,
        ]);
    }

    /**
     * Export reports to PDF or Excel
     */
    public function export(Request $request)
    {
        $year = $request->input('year', Qurban::max('year') ?? now()->year);
        $type = $request->input('type', 'pdf');

        $qurbans = Qurban::byYear($year)->orderByRaw('share_group_id DESC, share_position ASC, created_at DESC')->get();

        // Calculate totals handling shared groups correctly (Same logic as reports)
        // Note: For DRY, this should be refactored to a service, but for now copying ensures stability

        $totalParticipants = $qurbans->count(); // Use local collection count

        $totalPrice = 0;
        $totalWeight = 0;

        // Individual
        $individual = $qurbans->where('is_shared', false);
        $totalPrice += $individual->sum('animal_price');
        $totalWeight += $individual->sum('animal_weight');

        // Shared
        $sharedGroups = $qurbans->where('is_shared', true)->groupBy('share_group_id');
        foreach ($sharedGroups as $group) {
            if ($group->isNotEmpty()) {
                $totalPrice += $group->avg('animal_price');
                $totalWeight += $group->max('animal_weight');
            }
        }

        // Breakdown by Animal Type
        $byAnimalType = $qurbans->groupBy('animal_type')->map(function ($items, $type) {
            $count = 0;
            $price = 0;

            // Individual
            $indiv = $items->where('is_shared', false);
            $count += $indiv->count();
            $price += $indiv->sum('animal_price');

            // Shared
            $shared = $items->where('is_shared', true)->groupBy('share_group_id');
            $count += $shared->count();

            $details = [];
            if ($indiv->count() > 0) {
                $details[] = $indiv->count().' Individu';
            }
            if ($shared->count() > 0) {
                $details[] = $shared->count().' Grup Patungan';
            }

            foreach ($shared as $g) {
                if ($g->isNotEmpty()) {
                    $price += $g->avg('animal_price');
                }
            }

            return [
                'animal_type' => ucfirst($type),
                'count' => $count,
                'total_price' => $price,
                'details' => implode(', ', $details),
            ];
        })->values();

        $settings = Setting::all()->pluck('value', 'key')->toArray();

        $data = [
            'year' => $year,
            'qurbans' => $qurbans,
            'settings' => $settings,
            'summary' => [
                'total_participants' => $totalParticipants,
                'total_price' => $totalPrice,
                'total_weight_kg' => $totalWeight,
            ],
            'by_animal_type' => $byAnimalType,
        ];

        if ($type === 'excel') {
            return Excel::download(new QurbanExport($data), "Laporan_Qurban_$year.xlsx");
        } else {
            $pdf = Pdf::loadView('exports.qurban_pdf', $data);
            $pdf->setPaper('a4', 'portrait');

            return $pdf->stream("Laporan_Qurban_$year.pdf");
        }
    }

    /**
     * Public registration from website (no auth required).
     */
    public function publicRegister(Request $request)
    {
        $validated = $request->validate([
            'participant_name' => 'required|string|max:255',
            'participant_phone' => 'required|string|max:20',
            'participant_address' => 'nullable|string',
            'animal_type' => 'required|in:kambing,domba,sapi,kerbau',
            'is_shared' => 'required|boolean',
            'notes' => 'nullable|string',
        ]);

        // Auto-fill default values for public registration
        $validated['participant_nik'] = null;
        $validated['animal_weight'] = null;
        $validated['animal_price'] = 0; // Admin will update this
        $validated['share_count'] = $validated['is_shared'] ? 7 : 1;
        $validated['share_position'] = 1;
        $validated['share_group_id'] = null;
        $validated['year'] = now()->year;
        $validated['registration_date'] = now()->toDateString();
        $validated['registered_by'] = null; // No auth user
        $validated['status'] = 'registered';
        $validated['notes'] = ($validated['notes'] ?? '').' [DAFTAR VIA WEBSITE]';

        Qurban::create($validated);

        return back()->with('success', 'Pendaftaran berhasil! Pengurus masjid akan menghubungi Anda segera.');
    }

    /**
     * Delete qurban registration.
     */
    public function destroy(Qurban $qurban)
    {
        $qurban->delete();

        return redirect()->route('qurban.index')
            ->with('success', 'Peserta qurban berhasil dihapus!');
    }
}
