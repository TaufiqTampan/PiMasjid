<?php

namespace App\Http\Controllers;

use App\Models\Zakat;
use App\Models\ZakatDistribution;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Setting;
use App\Exports\ZakatExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ZakatController extends Controller
{
    /**
     * Display a listing of zakats.
     */
    public function index(Request $request): Response
    {
        $query = Zakat::with('collectedBy')
            ->latest();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by year
        $requestedYear = $request->input('year');
        if ($request->filled('year') && $requestedYear != '') {
            $query->byYear($requestedYear);
        } else {
            $latestYear = Zakat::max('year');
            if ($latestYear) {
                $query->byYear($latestYear);
            } else {
                $query->currentYear();
            }
        }

        // Filter by payment type
        if ($request->filled('payment_type')) {
            $query->byPaymentType($request->payment_type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('muzakki_name', 'like', "%{$search}%")
                  ->orWhere('muzakki_nik', 'like', "%{$search}%");
            });
        }

        $zakats = $query->paginate(20);

        // Summary statistics
        // Summary statistics
        $currentYear = $request->filled('year') ? $request->year : (Zakat::max('year') ?? now()->year);
        $summary = [
            'total_uang' => Zakat::byYear($currentYear)->byPaymentType('uang')->sum('amount'),
            'total_beras_kg' => Zakat::byYear($currentYear)->byPaymentType('beras')->sum('rice_kg'),
            'total_muzakki' => Zakat::byYear($currentYear)->count(),
            'fitrah_count' => Zakat::fitrah()->byYear($currentYear)->count(),
            'mal_count' => Zakat::mal()->byYear($currentYear)->count(),
            'profesi_count' => Zakat::profesi()->byYear($currentYear)->count(),
        ];

        return Inertia::render('Zakat/Index', [
            'zakats' => $zakats,
            'summary' => $summary,
            'filters' => $request->only(['type', 'year', 'payment_type', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new zakat.
     */
    public function create(): Response
    {
        return Inertia::render('Zakat/Create', [
            'current_year' => now()->year,
        ]);
    }

    /**
     * Store a newly created zakat in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'muzakki_name' => 'required|string|max:255',
            'muzakki_nik' => 'nullable|string|max:20',
            'muzakki_phone' => 'nullable|string|max:20',
            'muzakki_address' => 'nullable|string',
            'type' => 'required|in:fitrah,mal,profesi',
            'amount' => 'required|numeric|min:0',
            'payment_type' => 'required|in:uang,beras',
            'rice_kg' => 'nullable|numeric|min:0',
            'person_count' => 'nullable|integer|min:1',
            'year' => 'required|integer',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $validated['collected_by'] = auth()->id();

        Zakat::create($validated);

        return redirect()->route('zakat.index')
            ->with('success', 'Zakat berhasil dicatat!');
    }

    /**
     * Calculate zakat amount (API endpoint).
     */
    public function calculate(Request $request)
    {
        $type = $request->input('type');
        
        if ($type === 'fitrah') {
            // Zakat Fitrah: 3.5 liter beras x harga beras per liter x jumlah jiwa
            $pricePerLiter = $request->input('rice_price_per_liter', 15000);
            $personCount = $request->input('person_count', 1);
            $amount = 3.5 * $pricePerLiter * $personCount;
            
            return response()->json([
                'amount' => $amount,
                'formula' => '3.5L x Rp ' . number_format($pricePerLiter) . ' x ' . $personCount . ' jiwa',
                'rice_kg' => 3.5 * $personCount,
            ]);
        }
        
        if ($type === 'mal') {
            // Zakat Mal: 2.5% dari (harta - hutang) jika >= nisab
            $harta = $request->input('harta', 0);
            $hutang = $request->input('hutang', 0);
            $goldPricePerGram = $request->input('gold_price', 1000000); // Default 1 juta per gram
            $nisab = 85 * $goldPricePerGram; // 85 gram emas
            
            $nettWealth = $harta - $hutang;
            $isAboveNisab = $nettWealth >= $nisab;
            $amount = $isAboveNisab ? $nettWealth * 0.025 : 0;
            
            return response()->json([
                'amount' => $amount,
                'nisab' => $nisab,
                'nett_wealth' => $nettWealth,
                'is_above_nisab' => $isAboveNisab,
                'formula' => $isAboveNisab ? '(Rp ' . number_format($harta) . ' - Rp ' . number_format($hutang) . ') x 2.5%' : 'Belum mencapai nisab',
            ]);
        }
        
        if ($type === 'profesi') {
            // Zakat Profesi: 2.5% dari penghasilan bulanan
            $penghasilan = $request->input('penghasilan', 0);
            $amount = $penghasilan * 0.025;
            
            return response()->json([
                'amount' => $amount,
                'formula' => 'Rp ' . number_format($penghasilan) . ' x 2.5%',
            ]);
        }
        
        return response()->json(['error' => 'Invalid type'], 400);
    }

    /**
     * Show distribution form.
     */
    public function distribute(): Response
    {
        $currentYear = Zakat::max('year') ?? now()->year;
        
        // Total collected
        $totalCollected = Zakat::byYear($currentYear)->sum('amount');
        $totalBerasKg = Zakat::byYear($currentYear)->byPaymentType('beras')->sum('rice_kg');
        
        // Total distributed
        $totalDistributed = ZakatDistribution::byYear($currentYear)->sum('amount');
        $totalBerasDistributedKg = ZakatDistribution::byYear($currentYear)->byType('beras')->sum('rice_kg');
        
        // Distribution history
        $distributions = ZakatDistribution::with('distributedBy')
            ->byYear($currentYear)
            ->latest()
            ->paginate(20);
        
        $asnafCategories = [
            'fakir' => 'Fakir (Tidak punya harta/penghasilan)',
            'miskin' => 'Miskin (Penghasilan kurang)',
            'amil' => 'Amil (Pengelola zakat)',
            'muallaf' => 'Muallaf',
            'riqab' => 'Riqab (Memerdekakan budak)',
            'gharim' => 'Gharim (Berhutang)',
            'sabilillah' => 'Sabilillah (Jihad fi sabilillah)',
            'ibnu_sabil' => 'Ibnu Sabil (Musafir)',
        ];
        
        return Inertia::render('Zakat/Distribute', [
            'distributions' => $distributions,
            'asnaf_categories' => $asnafCategories,
            'summary' => [
                'total_collected' => $totalCollected,
                'total_beras_kg' => $totalBerasKg,
                'total_distributed' => $totalDistributed,
                'total_beras_distributed_kg' => $totalBerasDistributedKg,
                'remaining' => $totalCollected - $totalDistributed,
                'remaining_beras_kg' => $totalBerasKg - $totalBerasDistributedKg,
            ],
        ]);
    }

    /**
     * Store distribution.
     */
    public function storeDistribution(Request $request)
    {
        $validated = $request->validate([
            'mustahik_name' => 'required|string|max:255',
            'mustahik_category' => 'required|in:fakir,miskin,amil,muallaf,riqab,gharim,sabilillah,ibnu_sabil',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:uang,beras',
            'rice_kg' => 'nullable|numeric|min:0',
            'year' => 'required|integer',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $validated['distributed_by'] = auth()->id();

        ZakatDistribution::create($validated);

        return redirect()->route('zakat.distribute')
            ->with('success', 'Zakat berhasil disalurkan!');
    }

    /**
     * Show reports page.
     */
    public function reports(Request $request): Response
    {
        $year = $request->input('year', Zakat::max('year') ?? now()->year);
        
        // Summary by type
        $fitrahTotal = Zakat::fitrah()->byYear($year)->sum('amount');
        $malTotal = Zakat::mal()->byYear($year)->sum('amount');
        $profesiTotal = Zakat::profesi()->byYear($year)->sum('amount');
        $grandTotal = $fitrahTotal + $malTotal + $profesiTotal;
        
        // Distribution by asnaf
        $distributionByAsnaf = ZakatDistribution::byYear($year)
            ->selectRaw('mustahik_category, SUM(amount) as total')
            ->groupBy('mustahik_category')
            ->get();
        
        $totalDistributed = ZakatDistribution::byYear($year)->sum('amount');
        
        return Inertia::render('Zakat/Reports', [
            'year' => $year,
            'summary' => [
                'fitrah_total' => $fitrahTotal,
                'mal_total' => $malTotal,
                'profesi_total' => $profesiTotal,
                'grand_total' => $grandTotal,
                'total_distributed' => $totalDistributed,
                'remaining' => $grandTotal - $totalDistributed,
            ],
            'distribution_by_asnaf' => $distributionByAsnaf,
        ]);
    }

    /**
     * Export reports to PDF or Excel
     */
    public function export(Request $request)
    {
        $year = $request->input('year', now()->year);
        $type = $request->input('type', 'pdf');
        
        $zakats = Zakat::byYear($year)->latest()->get();
        
        // Calculate Summary
        // Note: Logic simplified for specific export needs
        $totalUang = $zakats->where('payment_type', 'uang')->sum('amount');
        $totalBeras = $zakats->where('payment_type', 'beras')->sum('rice_kg');
        $totalMuzakki = $zakats->count();
        
        // Distribution stats
        $distributedUang = ZakatDistribution::byYear($year)->where('type', 'uang')->sum('amount');
        $distributedBeras = ZakatDistribution::byYear($year)->where('type', 'beras')->sum('rice_kg');
        
        // Distribution by asnaf for detailed report
        $distributionByAsnaf = ZakatDistribution::byYear($year)
            ->selectRaw('mustahik_category, SUM(amount) as total_amount, SUM(rice_kg) as total_rice')
            ->groupBy('mustahik_category')
            ->get();
        
        $summary = [
            'total_amount' => $totalUang,
            'total_rice' => $totalBeras,
            'total_muzakki' => $totalMuzakki,
            'distributed_amount' => $distributedUang,
            'distributed_rice' => $distributedBeras,
            'remaining_amount' => $totalUang - $distributedUang,
            'remaining_rice' => $totalBeras - $distributedBeras,
        ];

        $settings = Setting::all()->pluck('value', 'key')->toArray();

        $data = [
            'year' => $year,
            'zakats' => $zakats,
            'settings' => $settings,
            'summary' => $summary,
            'distribution_by_asnaf' => $distributionByAsnaf,
        ];

        if ($type === 'excel') {
            return Excel::download(new ZakatExport($data), "Laporan_Zakat_$year.xlsx");
        } else {
            $pdf = Pdf::loadView('exports.zakat_pdf', $data);
            $pdf->setPaper('a4', 'portrait');
            return $pdf->stream("Laporan_Zakat_$year.pdf");
        }
    }
}
