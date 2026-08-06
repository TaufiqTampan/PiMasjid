<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { Bar } from 'vue-chartjs';
import{Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import ModernTable from '@/Components/ModernTable.vue';
import { BanknotesIcon, ArrowTrendingUpIcon, ChartBarIcon } from '@heroicons/vue/24/outline';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    stats: Object,
    chartData: Object,
    transactions: Array,
});

// Proof image modal state
const showProofModal = ref(false);
const selectedProof = ref(null);

const openProofModal = (transaction) => {
    selectedProof.value = transaction;
    showProofModal.value = true;
};

const closeProofModal = () => {
    showProofModal.value = false;
    selectedProof.value = null;
};

// Chart options
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: (value) => 'Rp ' + value.toLocaleString('id-ID'),
            },
        },
    },
};

// Table columns
const columns = [
    { key: 'date', label: 'Tanggal', sortable: true },
    { key: 'category', label: 'Kategori', sortable: false },
    { key: 'type', label: 'Tipe', sortable: false },
    { key: 'formatted_amount', label: 'Jumlah', sortable: true },
    { key: 'description', label: 'Keterangan', sortable: false },
    { key: 'proof', label: 'Bukti', sortable: false },
];
</script>

<template>
    <Head title="Transparansi Keuangan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-2xl leading-tight text-slate-900 dark:text-white">
                    💰 Transparansi Keuangan Masjid
                </h2>
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('transactions.index')"
                    class="btn-primary text-sm"
                >
                    + Tambah Transaksi
                </Link>
            </div>
        </template>

        <div class="py-12 bg-slate-50">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- Summary Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <StatCard
                        title="Total Saldo"
                        :value="stats.formattedBalance"
                        color="slate"
                        subtitle="Saldo keseluruhan"
                    >
                        <template #icon>
                            <ChartBarIcon class="w-full h-full" />
                        </template>
                    </StatCard>

                    <StatCard
                        title="Pemasukan Bulan Ini"
                        :value="stats.formattedMonthlyIncome"
                        trend="up"
                        color="emerald"
                        subtitle="Total pemasukan"
                    >
                        <template #icon>
                            <BanknotesIcon class="w-full h-full" />
                        </template>
                    </StatCard>

                    <StatCard
                        title="Pengeluaran Bulan Ini"
                        :value="stats.formattedMonthlyExpense"
                        trend="down"
                        color="amber"
                        subtitle="Total pengeluaran"
                    >
                        <template #icon>
                            <ArrowTrendingUpIcon class="w-full h-full" />
                        </template>
                    </StatCard>
                </div>

                <!-- Cashflow Chart -->
                <Card padding="lg">
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-6">📊 Grafik Arus Kas (6 Bulan Terakhir)</h3>
                    <div class="h-80">
                        <Bar :data="chartData" :options="chartOptions" />
                    </div>
                </Card>

                <!-- Transaction Table -->
                <Card padding="sm">
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-4">📋 Transaksi Terbaru</h3>
                    </div>
                    <ModernTable
                        :columns="columns"
                        :data="transactions"
                        hoverable
                        striped
                    >
                        <template #cell-type="{ value }">
                            <Badge
                                :variant="value === 'income' ? 'success' : 'warning'"
                                size="sm"
                            >
                                {{ value === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                            </Badge>
                        </template>

                        <template #cell-formatted_amount="{ row, value }">
                            <span :class="row.type === 'income' ? 'text-emerald-600 font-semibold' : 'text-red-600 font-semibold'">
                                {{ value }}
                            </span>
                        </template>

                        <template #cell-proof="{ row }">
                            <button
                                v-if="row.has_proof"
                                @click="openProofModal(row)"
                                class="text-sm bg-primary-100 text-primary-700 hover:bg-primary-200 px-3 py-1 rounded-md transition-colors"
                            >
                                🔍 Lihat Bukti
                            </button>
                            <span v-else class="text-slate-400 text-sm">-</span>
                        </template>
                    </ModernTable>
                </Card>
            </div>
        </div>

        <!-- Proof Image Modal -->
        <TransitionRoot as="template" :show="showProofModal">
            <Dialog as="div" class="relative z-50" @close="closeProofModal">
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-slate-900/75 transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <TransitionChild
                            as="template"
                            enter="ease-out duration-300"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                        >
                            <DialogPanel class="relative transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all w-full max-w-4xl">
                                <div class="bg-gradient-to-br from-teal-700 to-teal-900 px-6 py-4">
                                    <DialogTitle class="text-xl font-semibold text-white">
                                        Bukti Transaksi
                                    </DialogTitle>
                                    <p v-if="selectedProof" class="text-emerald-100 text-sm mt-1">
                                        {{ selectedProof.category }} - {{ selectedProof.formatted_date }}
                                    </p>
                                </div>

                                <div class="p-6">
                                    <div v-if="selectedProof" class="bg-slate-100 rounded-lg p-4 flex items-center justify-center min-h-[400px]">
                                        <img
                                            :src="selectedProof.proof_url"
                                            :alt="`Bukti ${selectedProof.category}`"
                                            class="max-w-full max-h-[600px] rounded-lg shadow-lg"
                                        />
                                    </div>
                                </div>

                                <div class="bg-slate-50 px-6 py-4 flex justify-between items-center">
                                    <div v-if="selectedProof" class="text-sm text-slate-600">
                                        <span class="font-medium">Jumlah:</span> {{ selectedProof.formatted_amount }}
                                    </div>
                                    <div class="flex gap-3">
                                        <a
                                            v-if="selectedProof"
                                            :href="selectedProof.proof_url"
                                            target="_blank"
                                            download
                                            class="btn-secondary text-sm"
                                        >
                                            📥 Download
                                        </a>
                                        <button
                                            @click="closeProofModal"
                                            class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-2 px-6 rounded-lg transition-colors"
                                        >
                                            Tutup
                                        </button>
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </AuthenticatedLayout>
</template>
