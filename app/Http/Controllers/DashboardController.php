<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\PerformanceLog;
use App\Models\Slide;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response|\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();
        $role = $user->role;

        // Non-pengelola users should not access internal management dashboard
        if (! in_array($role, ['super_admin', 'admin', 'ketua', 'bendahara', 'sekretaris', 'marbot'], true)) {
            return redirect('/');
        }

        $dashboardType = $this->getDashboardType($role);

        $stats = $this->getStatsByRole($role);
        $recentTransactions = $this->getRecentTransactions($role);
        $chartData = $this->getChartDataByRole($role);

        return Inertia::render('Dashboard', [
            'userRole' => $role,
            'dashboardType' => $dashboardType,
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'chartData' => $chartData,
            // Additional data specific to roles can be pushed here
        ]);
    }

    private function getDashboardType($role)
    {
        return match ($role) {
            'super_admin' => 'admin',
            'ketua' => 'executive',
            'bendahara' => 'finance',
            'marbot' => 'operations',
            default => 'default',
        };
    }

    private function getStatsByRole($role)
    {
        // Common Financial Stats
        $totalIncome = Transaction::income()->approved()->sum('amount');
        $totalExpense = Transaction::expense()->approved()->sum('amount');
        $balance = $totalIncome - $totalExpense;

        $baseStats = [
            'formattedBalance' => 'Rp '.number_format($balance, 0, ',', '.'),
            'balance' => $balance,
        ];

        switch ($role) {
            case 'super_admin':
                return array_merge($baseStats, [
                    'totalUsers' => User::count(),
                    'totalTransactions' => Transaction::count(),
                    'pendingApprovals' => Transaction::pending()->count(),
                    'systemHealth' => 'Online',
                    'monthlyIncome' => $this->getMonthlySum('income'),
                    'monthlyExpense' => $this->getMonthlySum('expense'),
                ]);

            case 'ketua':
                return array_merge($baseStats, [
                    'pendingApprovals' => Transaction::pending()->count(),
                    'totalAssets' => Asset::count(),
                    'monthlyIncome' => $this->getMonthlySum('income'),
                    'monthlyExpense' => $this->getMonthlySum('expense'),
                ]);

            case 'bendahara':
                return array_merge($baseStats, [
                    'formattedMonthlyIncome' => 'Rp '.number_format($this->getMonthlySum('income'), 0, ',', '.'),
                    'formattedMonthlyExpense' => 'Rp '.number_format($this->getMonthlySum('expense'), 0, ',', '.'),
                ]);

            case 'marbot':
                $activeSlides = Slide::where('is_active', true)->count();
                $brokenAssets = Asset::where('condition', '!=', 'Baik')->count();

                return [
                    'activeSlides' => $activeSlides,
                    'brokenAssets' => $brokenAssets,
                    'message' => 'Sistem TV Online',
                    'description' => "Saat ini ada $activeSlides slide aktif yang ditampilkan.",
                ];

            default:
                return [];
        }
    }

    private function getRecentTransactions($role)
    {
        if ($role === 'marbot') {
            return [];
        }

        $query = Transaction::with('user');

        if ($role === 'ketua') {
            // For Chairperson, prioritize Pending items
            $query->orderByRaw("CASE status 
                WHEN 'pending' THEN 1 
                WHEN 'approved' THEN 2 
                WHEN 'rejected' THEN 3 
                ELSE 4 END");
        }

        return $query->latest()
            ->take(10)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'type' => $t->type,
                'category' => $t->category,
                'amount' => $t->amount,
                'formatted_amount' => $t->formatted_amount,
                'description' => $t->description,
                'status' => $t->status,
                'date' => $t->date->format('d M Y'),
                'user_name' => $t->user->name ?? 'System',
            ]);
    }

    private function getMonthlySum($type)
    {
        return Transaction::where('type', $type)
            ->approved()
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
    }

    /**
     * Get chart data based on user role.
     */
    private function getChartDataByRole($role)
    {
        return match ($role) {
            'super_admin' => [
                'financialTrend' => $this->getMonthlyTrends(12),
                'performanceMetrics' => $this->getPerformanceMetrics(),
            ],
            'ketua' => [
                'monthlyTrend' => $this->getMonthlyTrends(12),
                'categoryBreakdown' => $this->getCategoryBreakdown('income'),
                'qurbanStats' => $this->getQurbanStats(),
                'upcomingAgendas' => \App\Models\Agenda::active()
                    ->where('date', '>=', now())
                    ->orderBy('date', 'asc')
                    ->orderBy('time', 'asc')
                    ->limit(5)
                    ->get()
                    ->map(fn ($agenda) => [
                        'id' => $agenda->id,
                        'title' => $agenda->title,
                        'date' => $agenda->date->format('d M Y'),
                        'time' => $agenda->time ? date('H:i', strtotime($agenda->time)) : 'Selesai',
                        'location' => $agenda->location,
                    ]),
            ],
            'bendahara' => [
                'monthlyComparison' => $this->getMonthlyTrends(6),
                'expenseBreakdown' => $this->getCategoryBreakdown('expense'),
            ],
            default => [],
        };
    }

    /**
     * Get monthly income/expense trends for the last N months.
     */
    private function getMonthlyTrends($months = 12)
    {
        return Cache::remember("dashboard.monthly_trends.{$months}", 300, function () use ($months) {
            $labels = [];
            $income = [];
            $expense = [];

            for ($i = $months - 1; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $labels[] = $date->format('M Y');

                $monthlyIncome = Transaction::income()
                    ->approved()
                    ->whereYear('date', $date->year)
                    ->whereMonth('date', $date->month)
                    ->sum('amount');

                $monthlyExpense = Transaction::expense()
                    ->approved()
                    ->whereYear('date', $date->year)
                    ->whereMonth('date', $date->month)
                    ->sum('amount');

                $income[] = (float) $monthlyIncome;
                $expense[] = (float) $monthlyExpense;
            }

            return [
                'labels' => $labels,
                'income' => $income,
                'expense' => $expense,
            ];
        });
    }

    /**
     * Get category-wise breakdown for income or expense.
     */
    private function getCategoryBreakdown($type)
    {
        return Cache::remember("dashboard.category_breakdown.{$type}", 300, function () use ($type) {
            $transactions = Transaction::where('type', $type)
                ->approved()
                ->thisMonth()
                ->selectRaw('category, SUM(amount) as total')
                ->groupBy('category')
                ->orderByDesc('total')
                ->get();

            if ($transactions->isEmpty()) {
                return [
                    'labels' => ['Tidak Ada Data'],
                    'amounts' => [0],
                ];
            }

            return [
                'labels' => $transactions->pluck('category')->toArray(),
                'amounts' => $transactions->pluck('total')->map(fn ($t) => (float) $t)->toArray(),
            ];
        });
    }

    /**
     * Get performance metrics for the last 7 days (Super Admin only).
     */
    private function getPerformanceMetrics()
    {
        return Cache::remember('dashboard.performance_metrics', 300, function () {
            $labels = [];
            $responseTime = [];
            $memoryUsage = [];
            $queryCount = [];

            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $labels[] = $date->format('D');

                $dailyLogs = PerformanceLog::whereDate('created_at', $date->toDateString())->get();

                $responseTime[] = $dailyLogs->isEmpty() ? 0 : round($dailyLogs->avg('response_time_ms'), 2);
                $memoryUsage[] = $dailyLogs->isEmpty() ? 0 : round($dailyLogs->avg('memory_usage_mb'), 2);
                $queryCount[] = $dailyLogs->isEmpty() ? 0 : round($dailyLogs->avg('query_count'), 2);
            }

            return [
                'labels' => $labels,
                'responseTime' => $responseTime,
                'memoryUsage' => $memoryUsage,
                'queryCount' => $queryCount,
            ];
        });
    }

    /**
     * Get qurban statistics for the current year.
     */
    private function getQurbanStats()
    {
        $year = now()->year;

        // We'll also support 1446 or other Hijri years if they are used in the DB
        $latestYear = \App\Models\Qurban::max('year') ?? $year;

        $stats = \App\Models\Qurban::where('year', $latestYear)->get();

        if ($stats->isEmpty()) {
            return null;
        }

        return [
            'year' => $latestYear,
            'total_participants' => $stats->count(),
            'total_funds' => $stats->sum('animal_price'),
            'formatted_total_funds' => 'Rp '.number_format($stats->sum('animal_price'), 0, ',', '.'),
            'by_type' => $stats->groupBy('animal_type')->map(fn ($group) => [
                'count' => $group->count(),
                'type' => $group->first()->animal_type_label,
            ])->values(),
            'by_status' => $stats->groupBy('status')->map(fn ($group) => [
                'count' => $group->count(),
                'label' => $group->first()->status_label,
            ])->values(),
            'payment_progress' => [
                'paid' => $stats->where('status', '!=', 'registered')->count(),
                'total' => $stats->count(),
                'percentage' => $stats->count() > 0 ? round(($stats->where('status', '!=', 'registered')->count() / $stats->count()) * 100) : 0,
            ],
        ];
    }
}
