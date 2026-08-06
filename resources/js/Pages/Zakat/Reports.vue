<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    year: Number,
    summary: Object,
    distribution_by_asnaf: Array,
});

const yearFilter = ref(props.year);

const applyFilter = () => {
    router.get(route('zakat.reports'), {
        year: yearFilter.value,
    }, {
        preserveState: true,
    });
};

const formatRupiah = (amount) => {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
};

const asnafLabels = {
    fakir: 'Fakir',
    miskin: 'Miskin',
    amil: 'Amil',
    muallaf: 'Muallaf',
    riqab: 'Riqab',
    gharim: 'Gharim',
    sabilillah: 'Sabilillah',
    ibnu_sabil: 'Ibnu Sabil',
};
</script>

<template>
    <Head title="Laporan Zakat" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-2xl leading-tight text-slate-900 dark:text-white">
                📊 Laporan Zakat
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Year Filter -->
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <h3 class="text-xl font-semibold text-slate-800 dark:text-slate-100">Laporan Tahun {{ yearFilter }}</h3>
                    
                    <div class="flex items-center gap-4">
                        <div class="flex gap-2 items-center">
                            <label class="text-sm text-slate-600">Tahun:</label>
                            <input v-model="yearFilter" @change="applyFilter" type="number"
                                   class="px-4 py-2 border border-slate-300 rounded-lg w-24" />
                        </div>
                        
                        <div class="flex gap-2">
                            <a :href="route('zakat.export', { year: yearFilter, type: 'pdf' })" target="_blank" 
                               class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                📄 PDF
                            </a>
                            <a :href="route('zakat.export', { year: yearFilter, type: 'excel' })" target="_blank" 
                               class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                📊 Excel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-6 text-white">
                        <div class="text-sm opacity-90">Zakat Fitrah</div>
                        <div class="text-2xl font-bold mt-2">{{ formatRupiah(summary.fitrah_total) }}</div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                        <div class="text-sm opacity-90">Zakat Mal</div>
                        <div class="text-2xl font-bold mt-2">{{ formatRupiah(summary.mal_total) }}</div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                        <div class="text-sm opacity-90">Zakat Profesi</div>
                        <div class="text-2xl font-bold mt-2">{{ formatRupiah(summary.profesi_total) }}</div>
                    </div>

                    <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-6 text-white">
                        <div class="text-sm opacity-90">Total Zakat</div>
                        <div class="text-2xl font-bold mt-2">{{ formatRupiah(summary.grand_total) }}</div>
                    </div>
                </div>

                <!-- Distribution Summary -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6">
                        <h4 class="font-semibold text-slate-800 dark:text-slate-100 mb-4">Ringkasan Penyaluran</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-600">Total Terkumpul:</span>
                                <span class="font-semibold text-emerald-600">{{ formatRupiah(summary.grand_total) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-600">Total Disalurkan:</span>
                                <span class="font-semibold text-blue-600">{{ formatRupiah(summary.total_distributed) }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-3 border-t">
                                <span class="text-slate-800 dark:text-slate-100 font-medium">Sisa:</span>
                                <span class="font-bold text-purple-600">{{ formatRupiah(summary.remaining) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6">
                        <h4 class="font-semibold text-slate-800 dark:text-slate-100 mb-4">Penyaluran per Asnaf</h4>
                        <div class="space-y-2">
                            <div v-for="dist in distribution_by_asnaf" :key="dist.mustahik_category"
                                 class="flex justify-between items-center text-sm">
                                <span class="text-slate-600">{{ asnafLabels[dist.mustahik_category] || dist.mustahik_category }}:</span>
                                <span class="font-semibold">{{ formatRupiah(dist.total) }}</span>
                            </div>
                            <div v-if="distribution_by_asnaf.length === 0" class="text-center text-slate-400 py-4">
                                Belum ada penyaluran
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Placeholder -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6">
                    <h4 class="font-semibold text-slate-800 dark:text-slate-100 mb-4">Visualisasi Data</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Pie Chart Placeholder -->
                        <div class="border-2 border-dashed border-slate-300 rounded-lg p-8 text-center">
                            <div class="text-4xl mb-3">📊</div>
                            <div class="text-slate-600">Grafik Distribusi Zakat</div>
                            <div class="text-sm text-slate-400 mt-2">(Chart.js dapat diintegrasikan)</div>
                        </div>

                        <!-- Bar Chart Placeholder -->
                        <div class="border-2 border-dashed border-slate-300 rounded-lg p-8 text-center">
                            <div class="text-4xl mb-3">📈</div>
                            <div class="text-slate-600">Grafik Penyaluran per Asnaf</div>
                            <div class="text-sm text-slate-400 mt-2">(Chart.js dapat diintegrasikan)</div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </AuthenticatedLayout>
</template>
