<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    BanknotesIcon, 
    UserGroupIcon, 
    ArchiveBoxIcon, 
    BellAlertIcon, 
    ServerIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    DocumentPlusIcon,
    DocumentMinusIcon,
    TvIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    XCircleIcon,
    HandThumbUpIcon,
    HandThumbDownIcon
} from '@heroicons/vue/24/outline';

// Import chart components
import FinancialLineChart from '@/Components/Charts/FinancialLineChart.vue';
import CategoryDonutChart from '@/Components/Charts/CategoryDonutChart.vue';
import MonthlyBarChart from '@/Components/Charts/MonthlyBarChart.vue';
import PerformanceMetricsChart from '@/Components/Charts/PerformanceMetricsChart.vue';

const props = defineProps({
    userRole: String,
    dashboardType: String,
    stats: Object,
    recentTransactions: Array,
    chartData: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const approveTransaction = (id) => {
    if (confirm('Apakah Anda yakin ingin menyetujui transaksi ini?')) {
        router.post(`/approvals/${id}/approve`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: show toast notification
            }
        });
    }
};

const rejectTransaction = (id) => {
    const reason = prompt('Alasan penolakan:');
    if (reason !== null && reason.trim() !== '') {
        router.post(`/approvals/${id}/reject`, { rejection_reason: reason }, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-slate-900 dark:text-white leading-tight flex items-center gap-2">
                    <span>📊</span> Dashboard Overview
                </h2>
                <div class="text-sm text-slate-500">
                    <span class="capitalize font-bold text-emerald-600">{{ userRole.replace('_', ' ') }}</span> View
                </div>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            
            <!-- SUPER ADMIN VIEW (God Mode) -->
            <div v-if="dashboardType === 'admin'" class="space-y-6">
                <!-- Top Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500 uppercase font-bold">Total Users</p>
                            <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ stats.totalUsers }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                            <UserGroupIcon class="w-6 h-6" />
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500 uppercase font-bold">Transactions</p>
                            <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ stats.totalTransactions }}</p>
                        </div>
                         <div class="p-3 bg-indigo-50 text-indigo-600 rounded-lg">
                            <ArchiveBoxIcon class="w-6 h-6" />
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500 uppercase font-bold">System Status</p>
                            <p class="text-xl font-bold text-emerald-600">{{ stats.systemHealth }}</p>
                        </div>
                         <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
                            <ServerIcon class="w-6 h-6" />
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between relative overflow-hidden">
                        <div v-if="stats.pendingApprovals > 0" class="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-full animate-ping"></div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase font-bold">Pending Actions</p>
                            <p class="text-2xl font-bold text-amber-600">{{ stats.pendingApprovals }}</p>
                        </div>
                         <div class="p-3 bg-amber-50 text-amber-600 rounded-lg">
                            <BellAlertIcon class="w-6 h-6" />
                        </div>
                    </div>
                </div>

                <!-- Middle Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Chart Area -->
                    <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                        <div class="w-full text-left mb-4">
                            <h3 class="font-bold text-slate-700 dark:text-slate-200">Financial Overview (12 Months)</h3>
                            <p class="text-xs text-slate-500 mt-1">Trend pemasukan dan pengeluaran 12 bulan terakhir</p>
                        </div>
                        <FinancialLineChart 
                            v-if="chartData?.financialTrend" 
                            :data="chartData.financialTrend" 
                            :height="320" 
                        />
                    </div>

                    <!-- Recent Activity Log (Condensed) -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                         <h3 class="font-bold text-slate-700 dark:text-slate-200 mb-4">Recent Transactions</h3>
                         <div class="space-y-4">
                            <div v-for="tx in recentTransactions.slice(0, 5)" :key="tx.id" class="flex gap-3 items-start text-sm">
                                <div :class="tx.type === 'income' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'" class="p-1.5 rounded shrink-0">
                                    <component :is="tx.type === 'income' ? ArrowTrendingUpIcon : ArrowTrendingDownIcon" class="w-4 h-4" />
                                </div>
                                <div>
                                    <p class="font-medium text-slate-800 dark:text-white line-clamp-1">{{ tx.description }}</p>
                                    <p class="text-xs text-slate-500">{{ tx.formatted_amount }} • {{ tx.user_name }}</p>
                                </div>
                            </div>
                         </div>
                    </div>
                </div>

                <!-- Performance Metrics Section (Super Admin Only) -->
                <div v-if="chartData?.performanceMetrics" class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                    <div class="mb-4">
                        <h3 class="font-bold text-slate-700 dark:text-slate-200">Performance Metrics (7 Days)</h3>
                        <p class="text-xs text-slate-500 mt-1">Average response time, memory usage, and database queries</p>
                    </div>
                    <PerformanceMetricsChart 
                        :data="chartData.performanceMetrics" 
                        :height="240" 
                    />
                </div>
            </div>

            <!-- CHAIRPERSON VIEW (Ketua DKM) -->
            <div v-else-if="dashboardType === 'executive'" class="space-y-6">
                 <!-- Top Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                        <p class="text-slate-500 text-sm font-medium">Total Saldo Kas</p>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-white mt-1">{{ stats.formattedBalance }}</h3>
                        <div class="mt-4 flex gap-4 text-sm">
                            <span class="text-emerald-600 flex items-center gap-1">
                                <ArrowTrendingUpIcon class="w-4 h-4" />
                                +{{ formatCurrency(stats.monthlyIncome) }}
                            </span>
                             <span class="text-red-500 flex items-center gap-1">
                                <ArrowTrendingDownIcon class="w-4 h-4" />
                                -{{ formatCurrency(stats.monthlyExpense) }}
                            </span>
                        </div>
                    </div>
                     <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                             <div>
                                <p class="text-slate-500 text-sm font-medium">Pending Approvals</p>
                                <h3 class="text-3xl font-bold text-amber-600 mt-1">{{ stats.pendingApprovals }}</h3>
                             </div>
                             <BellAlertIcon class="w-8 h-8 text-amber-200" />
                        </div>
                        <div class="mt-2 text-xs text-slate-400">Transactions waiting for your review</div>
                    </div>
                     <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col justify-between">
                         <div class="flex justify-between items-start">
                             <div>
                                <p class="text-slate-500 text-sm font-medium">Total Aset</p>
                                <h3 class="text-3xl font-bold text-blue-600 mt-1">{{ stats.totalAssets }}</h3>
                             </div>
                             <ArchiveBoxIcon class="w-8 h-8 text-blue-200" />
                        </div>
                        <div class="mt-2 text-xs text-slate-400">Recorded inventory items</div>
                    </div>
                </div>

                <!-- QURBAN MONITORING SECTION (Seasonal) -->
                <div v-if="chartData?.qurbanStats" class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl shadow-xl overflow-hidden text-white">
                    <div class="px-6 py-4 bg-white/10 backdrop-blur-md flex justify-between items-center border-b border-white/10">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-white/20 rounded-lg">
                                <BanknotesIcon class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg">Monitoring Qurban {{ chartData.qurbanStats.year }}</h3>
                                <p class="text-xs text-emerald-100">Persiapan Idul Adha terpusat</p>
                            </div>
                        </div>
                        <Link href="/qurban" class="text-xs font-bold bg-white text-emerald-700 px-3 py-1.5 rounded-full hover:bg-emerald-50 transition-colors">
                            Detail Laporan
                        </Link>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="space-y-1">
                            <p class="text-emerald-100 text-xs font-medium uppercase tracking-wider">Total Partisipan</p>
                            <h4 class="text-3xl font-bold">{{ chartData.qurbanStats.total_participants }} <span class="text-sm font-normal opacity-80">Orang</span></h4>
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <p class="text-emerald-100 text-xs font-medium uppercase tracking-wider">Total Dana Terkumpul</p>
                            <h4 class="text-3xl font-bold">{{ chartData.qurbanStats.formatted_total_funds }}</h4>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-xs font-medium">
                                <span class="text-emerald-100 uppercase">Progres Pelunasan</span>
                                <span>{{ chartData.qurbanStats.payment_progress.percentage }}%</span>
                            </div>
                            <div class="w-full bg-emerald-900/30 rounded-full h-2 overflow-hidden border border-white/10">
                                <div class="bg-white h-full transition-all duration-1000" :style="{ width: chartData.qurbanStats.payment_progress.percentage + '%' }"></div>
                            </div>
                            <p class="text-[10px] text-emerald-100 italic">*{{ chartData.qurbanStats.payment_progress.paid }} dari {{ chartData.qurbanStats.payment_progress.total }} peserta sudah lunas</p>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-emerald-800/50 flex flex-wrap gap-4">
                        <div v-for="type in chartData.qurbanStats.by_type" :key="type.type" class="flex items-center gap-2 bg-white/10 px-3 py-1 rounded-lg border border-white/5">
                            <span class="text-xs font-bold">{{ type.count }}</span>
                            <span class="text-xs opacity-80">{{ type.type }}</span>
                        </div>
                    </div>
                </div>

                <!-- FINANCIAL TREND CHART -->
                <div v-if="chartData?.monthlyTrend" class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="font-bold text-slate-800 dark:text-white">Tren Keuangan Masjid</h3>
                            <p class="text-xs text-slate-500 mt-1">Pemasukan vs Pengeluaran dalam 12 bulan terakhir</p>
                        </div>
                    </div>
                    <div class="h-80">
                        <FinancialLineChart :data="chartData.monthlyTrend" />
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left: Waiting for Approval & Qurban Status -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Need Approval -->
                        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
                            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-amber-50/50 dark:bg-amber-900/20">
                                <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 text-sm">
                                    <BellAlertIcon class="w-5 h-5 text-amber-500" />
                                    Need Approval
                                </h3>
                                <Link href="/transactions" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">View All</Link>
                            </div>
                            <div v-if="stats.pendingApprovals === 0" class="p-8 text-center text-slate-500">
                                <CheckCircleIcon class="w-8 h-8 text-emerald-200 mx-auto mb-2" />
                                <p class="text-sm">No pending approvals.</p>
                            </div>
                            <div v-else class="divide-y divide-slate-100">
                                <div v-for="tx in recentTransactions.filter(t => t.status === 'pending').slice(0, 5)" :key="tx.id" class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" :class="tx.type === 'income' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'">
                                            <component :is="tx.type === 'income' ? ArrowTrendingUpIcon : ArrowTrendingDownIcon" class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 dark:text-white text-sm">{{ tx.description }}</p>
                                            <p class="text-[11px] text-slate-500">{{ tx.date }} • {{ tx.formatted_amount }}</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="approveTransaction(tx.id)" class="px-2.5 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white border border-emerald-200 text-[10px] font-bold rounded-lg transition-all flex items-center gap-1">
                                            <HandThumbUpIcon class="w-3 h-3" />
                                            Setujui
                                        </button>
                                        <button @click="rejectTransaction(tx.id)" class="px-2.5 py-1.5 bg-red-50 text-red-700 hover:bg-red-600 hover:text-white border border-red-200 text-[10px] font-bold rounded-lg transition-all flex items-center gap-1">
                                            <HandThumbDownIcon class="w-3 h-3" />
                                            Tolak
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Category Breakdown -->
                        <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                            <h3 class="font-bold text-slate-700 dark:text-slate-200 mb-6 w-full text-left">Sumber Pemasukan</h3>
                            <div v-if="chartData?.categoryBreakdown" class="w-full h-64">
                                <CategoryDonutChart 
                                    :data="chartData.categoryBreakdown" 
                                    type="income"
                                />
                            </div>
                            <div class="w-full mt-6 space-y-2">
                                <div class="flex justify-between text-sm px-4">
                                    <span class="text-slate-500 font-medium">Total Pemasukan Bulan Ini</span>
                                    <span class="font-bold text-emerald-600">{{ formatCurrency(stats.monthlyIncome) }}</span>
                                </div>
                                <div class="flex justify-between text-sm px-4">
                                    <span class="text-slate-500 font-medium">Total Pengeluaran Bulan Ini</span>
                                    <span class="font-bold text-red-500">{{ formatCurrency(stats.monthlyExpense) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Agenda & Info -->
                    <div class="space-y-6">
                        <!-- Upcoming Agendas -->
                        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
                            <div class="p-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                                <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 text-sm">
                                    <CalendarIcon class="w-5 h-5 text-emerald-600" />
                                    Agenda Mendatang
                                </h3>
                            </div>
                            <div v-if="!chartData?.upcomingAgendas?.length" class="p-8 text-center text-slate-500">
                                <p class="text-sm">Belum ada agenda terdekat.</p>
                            </div>
                            <div v-else class="p-2 space-y-1">
                                <div v-for="agenda in chartData.upcomingAgendas" :key="agenda.id" class="p-3 rounded-lg hover:bg-slate-50 transition-colors border-b last:border-0 border-slate-100">
                                    <div class="flex justify-between items-start mb-1">
                                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">{{ agenda.date }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium">{{ agenda.time }}</span>
                                    </div>
                                    <h4 class="font-bold text-slate-800 dark:text-white text-sm leading-tight">{{ agenda.title }}</h4>
                                    <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                                        <ServerIcon class="w-3 h-3 text-slate-400" />
                                        {{ agenda.location }}
                                    </p>
                                </div>
                            </div>
                            <div class="p-3 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700">
                                <Link href="/agendas" class="block text-center text-xs font-bold text-emerald-600 hover:text-emerald-700">Lihat Semua Agenda</Link>
                            </div>
                        </div>

                        <!-- Quick Info / Support -->
                        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-5 text-white shadow-lg">
                            <h4 class="font-bold mb-2 flex items-center gap-2 text-sm">
                                <Cog6ToothIcon class="w-4 h-4 text-emerald-400" />
                                Bantuan Teknis
                            </h4>
                            <p class="text-[11px] text-slate-400 mb-4 leading-relaxed">Jika ada kendala dalam penggunaan dashboard, hubungi tim IT Masjid Al-Hidayah.</p>
                            <a href="#" class="inline-block w-full text-center py-2 bg-emerald-600 hover:bg-emerald-700 rounded-lg text-xs font-bold transition-all shadow-md">Hubungi Support</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BENDAHARA VIEW (Finance) -->
            <div v-else-if="dashboardType === 'finance'" class="space-y-8">
                 <!-- Quick Action Buttons -->
                 <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <button class="flex items-center justify-center gap-3 p-6 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl shadow-lg shadow-emerald-200 transition-all transform hover:-translate-y-1">
                        <DocumentPlusIcon class="w-8 h-8" />
                        <span class="text-lg font-bold">Catat Pemasukan</span>
                    </button>
                    <button class="flex items-center justify-center gap-3 p-6 bg-red-600 hover:bg-red-700 text-white rounded-2xl shadow-lg shadow-red-200 transition-all transform hover:-translate-y-1">
                        <DocumentMinusIcon class="w-8 h-8" />
                        <span class="text-lg font-bold">Catat Pengeluaran</span>
                    </button>
                    <Link href="/transactions" class="flex items-center justify-center gap-3 p-6 bg-white dark:bg-slate-800 border-2 border-dashed border-slate-300 dark:border-slate-600 hover:border-blue-500 text-slate-600 dark:text-slate-400 hover:text-blue-600 rounded-2xl transition-all">
                         <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                            <BanknotesIcon class="w-6 h-6" />
                        </div>
                        <span class="text-lg font-bold">Lihat Semua Transaksi</span>
                    </Link>
                 </div>

                 <!-- Charts Section -->
                 <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Monthly Comparison Chart -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                        <h3 class="font-bold text-slate-700 dark:text-slate-200 mb-4">6 Months Comparison</h3>
                        <p class="text-xs text-slate-500 mb-4">Perbandingan pemasukan dan pengeluaran 6 bulan terakhir</p>
                        <MonthlyBarChart 
                            v-if="chartData?.monthlyComparison" 
                            :data="chartData.monthlyComparison" 
                            :height="280"
                            class="dark:invert dark:hue-rotate-180"
                        />
                    </div>

                    <!-- Expense Breakdown Chart -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                        <h3 class="font-bold text-slate-700 dark:text-slate-200 mb-4">Expense Breakdown (This Month)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Kategori pengeluaran bulan ini</p>
                        <div class="w-full h-64">
                            <CategoryDonutChart 
                                v-if="chartData?.expenseBreakdown" 
                                :data="chartData.expenseBreakdown" 
                                type="expense"
                            />
                        </div>
                    </div>
                 </div>

                 <!-- Main Stats & Table -->
                 <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex flex-col md:flex-row justify-between md:items-center gap-4">
                        <div>
                             <h3 class="text-lg font-bold text-slate-800 dark:text-white">Transaksi Terbaru</h3>
                             <p class="text-sm text-slate-500 dark:text-slate-400">10 transaksi terakhir yang tercatat dalam sistem</p>
                        </div>
                        <div class="flex gap-4">
                            <div class="px-4 py-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg border border-emerald-100 dark:border-emerald-800">
                                <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold uppercase block">Pemasukan (Bulan Ini)</span>
                                <span class="text-lg font-bold text-emerald-700 dark:text-emerald-300">{{ stats.formattedMonthlyIncome }}</span>
                            </div>
                            <div class="px-4 py-2 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800">
                                <span class="text-xs text-red-600 dark:text-red-400 font-bold uppercase block">Pengeluaran (Bulan Ini)</span>
                                <span class="text-lg font-bold text-red-700 dark:text-red-300">{{ stats.formattedMonthlyExpense }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 dark:bg-slate-900 text-slate-500 dark:text-slate-400 text-xs uppercase font-bold">
                                <tr>
                                    <th class="px-6 py-4">Tanggal</th>
                                    <th class="px-6 py-4">Kategori</th>
                                    <th class="px-6 py-4">Keterangan</th>
                                    <th class="px-6 py-4">Nominal</th>
                                    <th class="px-6 py-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="tx in recentTransactions" :key="tx.id" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-mono text-slate-600">{{ tx.date }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize" :class="tx.type === 'income' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'">
                                            {{ tx.type === 'income' ? 'Masuk' : 'Keluar' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-800 dark:text-white font-medium">{{ tx.description }}</td>
                                    <td class="px-6 py-4 font-mono font-bold" :class="tx.type === 'income' ? 'text-emerald-600' : 'text-red-600'">
                                        {{ tx.formatted_amount }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize" 
                                            :class="{
                                                'bg-green-100 text-green-800': tx.status === 'approved',
                                                'bg-yellow-100 text-yellow-800': tx.status === 'pending',
                                                'bg-red-100 text-red-800': tx.status === 'rejected'
                                            }">
                                            {{ tx.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                 </div>
            </div>

            <!-- MARBOT VIEW (Operations) -->
            <div v-if="dashboardType === 'operations'" class="space-y-6">
                <!-- Status Header -->
                 <div class="bg-slate-900 text-white rounded-2xl p-8 shadow-xl relative overflow-hidden">
                    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="flex items-center gap-6">
                            <div class="p-4 bg-emerald-500/20 rounded-full border border-emerald-500/50 backdrop-blur-sm animate-pulse">
                                <TvIcon class="w-12 h-12 text-emerald-400" />
                            </div>
                            <div>
                                <h3 class="text-3xl font-bold">System Online</h3>
                                <p class="text-slate-400">{{ stats.description }}</p>
                            </div>
                        </div>
                         <div class="flex gap-4">
                            <div class="text-center px-6 border-r border-slate-700">
                                <div class="text-2xl font-bold">{{ stats.activeSlides }}</div>
                                <div class="text-xs uppercase text-slate-500 font-bold">Active Slides</div>
                            </div>
                            <div class="text-center px-6">
                                <div class="text-2xl font-bold text-amber-500">{{ stats.brokenAssets }}</div>
                                <div class="text-xs uppercase text-slate-500 font-bold">Asset Alerts</div>
                            </div>
                        </div>
                    </div>
                    <!-- Background Pattern -->
                     <div class="absolute inset-0 z-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiZmZmYiIC8+PC9zdmc+')]"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Live Preview Simulation -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow p-6 border border-slate-100 dark:border-slate-700">
                        <h4 class="font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                             <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                             Live TV Preview
                        </h4>
                        <div class="aspect-video bg-slate-900 rounded-lg flex items-center justify-center text-slate-500 border-4 border-slate-800 shadow-inner">
                            <div class="text-center">
                                <TvIcon class="w-16 h-16 mx-auto mb-2 opacity-50" />
                                <span class="text-sm">Preview Screen</span>
                            </div>
                        </div>
                         <div class="mt-4 flex gap-2">
                            <Link href="/slides" class="flex-1 py-2 text-center bg-slate-100 hover:bg-slate-200 rounded font-bold text-sm text-slate-700 dark:text-slate-200 transition">Manage Slides</Link>
                            <a href="/display" target="_blank" class="flex-1 py-2 text-center bg-emerald-600 hover:bg-emerald-700 rounded font-bold text-sm text-white transition">Open Display Mode</a>
                        </div>
                    </div>

                    <!-- Asset Alerts -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow p-6 border border-slate-100 dark:border-slate-700">
                         <h4 class="font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                             <ExclamationTriangleIcon class="w-5 h-5 text-amber-500" />
                             Perlu Perhatian (Aset Rusak)
                        </h4>
                        <div v-if="stats.brokenAssets > 0" class="space-y-3">
                            <!-- Placeholder list if assets are fetched -->
                            <div class="p-3 bg-red-50 border-l-4 border-red-500 rounded-r">
                                <p class="font-bold text-red-800 text-sm">AC Ruang Utama</p>
                                <p class="text-xs text-red-600">Rusak Berat - Perlu Service</p>
                            </div>
                             <div class="p-3 bg-amber-50 border-l-4 border-amber-500 rounded-r">
                                <p class="font-bold text-amber-800 text-sm">Sound System Hall</p>
                                <p class="text-xs text-amber-600">Rusak Ringan - Kabel Putus</p>
                            </div>
                        </div>
                        <div v-else class="h-32 flex flex-col items-center justify-center text-slate-400">
                            <CheckCircleIcon class="w-12 h-12 mb-2 text-emerald-200" />
                            <p class="text-sm">Semua aset dalam kondisi baik.</p>
                        </div>
                         <div class="mt-4">
                            <Link href="/assets" class="block w-full py-2 text-center bg-slate-100 hover:bg-slate-200 rounded font-bold text-sm text-slate-700 dark:text-slate-200 transition">Update Kondisi Aset</Link>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
