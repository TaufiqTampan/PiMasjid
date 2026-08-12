<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\CloudinaryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    /**
     * Display public transparency page.
     */
    public function publicIndex(): Response
    {
        // Calculate overall balance
        $totalIncome = Transaction::income()->sum('amount');
        $totalExpense = Transaction::expense()->sum('amount');
        $currentBalance = $totalIncome - $totalExpense;

        // Monthly stats
        $monthlyIncome = Transaction::income()->thisMonth()->sum('amount');
        $monthlyExpense = Transaction::expense()->thisMonth()->sum('amount');

        // Chart data: Last 6 months
        $chartData = $this->getChartData();

        // Recent transactions (last 20)
        $recentTransactions = TransactionResource::collection(
            Transaction::with('verifiedBy')
                ->latest()
                ->take(20)
                ->get()
        )->resolve();

        return Inertia::render('Keuangan/Index', [
            'stats' => [
                'currentBalance' => $currentBalance,
                'formattedBalance' => 'Rp '.number_format($currentBalance, 0, ',', '.'),
                'monthlyIncome' => $monthlyIncome,
                'formattedMonthlyIncome' => 'Rp '.number_format($monthlyIncome, 0, ',', '.'),
                'monthlyExpense' => $monthlyExpense,
                'formattedMonthlyExpense' => 'Rp '.number_format($monthlyExpense, 0, ',', '.'),
            ],
            'chartData' => $chartData,
            'transactions' => $recentTransactions,
        ]);
    }

    /**
     * Store a new transaction.
     */
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Transaction::query()->with('verifiedBy');

        // Filter by Date Range
        if ($request->has(['start_date', 'end_date'])) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        // Quick Filters can be handled on frontend by setting date params,
        // OR here if specific logic is needed.

        $transactions = $query->latest('date')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['start_date', 'end_date']),
        ]);
    }

    /**
     * Export transactions.
     */
    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,excel',
        ]);

        $format = $request->format;
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? Carbon::now()->endOfMonth()->toDateString();

        // Use the Export class for Excel
        if ($format === 'excel') {
            $filename = 'laporan-keuangan-'.Carbon::now()->format('Y-m-d').'.xlsx';

            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\TransactionExport($startDate, $endDate),
                $filename
            );
        }

        // Handle PDF manually
        if ($format === 'pdf') {
            $transactions = Transaction::whereBetween('date', [$startDate, $endDate])
                ->orderBy('date')
                ->get();

            $settingsNodes = \App\Models\Setting::whereIn('key', [
                'site_name', 'address', 'phone', 'email', 'logo_path',
                'chairman_name', 'treasurer_name', 'location_city',
            ])->get()->pluck('value', 'key');

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.transactions_pdf', [
                'transactions' => $transactions,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'settings' => $settingsNodes,
                // Pass helper for stats
                'totalIncome' => $transactions->where('type', 'income')->sum('amount'),
                'totalExpense' => $transactions->where('type', 'expense')->sum('amount'),
            ]);

            return $pdf->download('laporan-keuangan.pdf');
        }
    }

    /**
     * Store a new transaction.
     */
    public function store(StoreTransactionRequest $request)
    {
        $data = $request->validated();

        // Handle file upload for expense proof to Cloudinary
        if ($request->hasFile('proof_image')) {
            $result = CloudinaryService::upload($request->file('proof_image'), 'transactions');
            $data['proof_image_path'] = $result['url'];
            $data['cloudinary_public_id'] = $result['public_id'];
        }

        // Add verified_by (current user)
        $data['verified_by'] = auth()->id();

        Transaction::create($data);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil disimpan!');
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(int $id)
    {
        $transaction = Transaction::findOrFail($id);

        // Delete proof image from Cloudinary
        if ($transaction->cloudinary_public_id) {
            CloudinaryService::delete($transaction->cloudinary_public_id);
        }

        $transaction->delete();

        return redirect()->back()
            ->with('success', 'Transaksi berhasil dihapus!');
    }

    /**
     * Get chart data for last 6 months.
     */
    private function getChartData(): array
    {
        $months = [];
        $incomeData = [];
        $expenseData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->translatedFormat('M Y');

            $income = Transaction::income()
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('amount');

            $expense = Transaction::expense()
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('amount');

            $months[] = $monthName;
            $incomeData[] = (float) $income;
            $expenseData[] = (float) $expense;
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $incomeData,
                    'backgroundColor' => '#10b981',
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expenseData,
                    'backgroundColor' => '#ef4444',
                ],
            ],
        ];
    }
}
