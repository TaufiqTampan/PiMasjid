<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    year: Number,
    summary: Object,
    by_animal_type: Array,
    distribution_by_type: Array,
});

const yearFilter = ref(props.year);

const applyFilter = () => {
    router.get(route('qurban.reports'), {
        year: yearFilter.value,
    }, {
        preserveState: true,
    });
};

const formatRupiah = (amount) => {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
};

const animalIcons = {
    kambing: '🐐',
    domba: '🐑',
    sapi: '🐄',
    kerbau: '🐃',
    unta: '🐪',
};

const recipientLabels = {
    mustahik: 'Mustahik',
    aqiqah: 'Aqiqah',
    participant: 'Peserta',
    masjid: 'Masjid',
};
</script>

<template>
    <Head title="Laporan Qurban" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-2xl leading-tight text-slate-900 dark:text-white">
                📊 Laporan Qurban
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Year Filter -->
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <h3 class="text-xl font-semibold text-slate-800 dark:text-slate-200">Laporan Tahun {{ yearFilter }}</h3>
                    
                    <div class="flex items-center gap-4">
                        <div class="flex gap-2 items-center">
                            <label class="text-sm text-slate-600">Tahun:</label>
                            <input v-model="yearFilter" @change="applyFilter" type="number"
                                   class="px-4 py-2 border border-slate-300 rounded-lg w-24" />
                        </div>
                        
                        <div class="flex gap-2">
                            <a :href="route('qurban.export', { year: yearFilter, type: 'pdf' })" target="_blank" 
                               class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                📄 PDF
                            </a>
                            <a :href="route('qurban.export', { year: yearFilter, type: 'excel' })" target="_blank" 
                               class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                📊 Excel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-6 text-white">
                        <div class="text-sm opacity-90">Total Peserta</div>
                        <div class="text-3xl font-bold mt-2">{{ summary.total_participants }}</div>
                        <div class="text-xs opacity-75 mt-1">Peserta Qurban</div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                        <div class="text-sm opacity-90">Total Harga</div>
                        <div class="text-2xl font-bold mt-2">{{ formatRupiah(summary.total_price) }}</div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                        <div class="text-sm opacity-90">Total Berat</div>
                        <div class="text-3xl font-bold mt-2">{{ summary.total_weight_kg }}</div>
                        <div class="text-xs opacity-75 mt-1">kilogram</div>
                    </div>

                    <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-6 text-white">
                        <div class="text-sm opacity-90">Daging Terdistribusi</div>
                        <div class="text-2xl font-bold mt-2">{{ summary.total_distributed_kg }} kg</div>
                        <div class="text-xs opacity-75 mt-1">Sisa: {{ summary.remaining_kg }} kg</div>
                    </div>
                </div>

                <!-- By Animal Type -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h4 class="font-semibold text-slate-800 dark:text-slate-200 mb-4">Breakdown per Jenis Hewan</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="animal in by_animal_type" :key="animal.animal_type"
                             class="border border-slate-200 rounded-lg p-4">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="text-3xl">{{ animalIcons[animal.animal_type] || '🐑' }}</span>
                                <div>
                                    <div class="font-semibold capitalize">{{ animal.animal_type }}</div>
                                    <div class="text-xs text-slate-500">{{ animal.count }} ekor</div>
                                </div>
                            </div>
                            <div class="text-sm text-slate-600">
                                Total: <span class="font-semibold text-emerald-600">{{ formatRupiah(animal.total_price) }}</span>
                            </div>
                        </div>
                        <div v-if="by_animal_type.length === 0" class="col-span-full text-center text-slate-400 py-8">
                            Belum ada data qurban
                        </div>
                    </div>
                </div>

                <!-- Distribution Stats -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h4 class="font-semibold text-slate-800 dark:text-slate-200 mb-4">Distribusi Daging per Kategori</h4>
                    <div class="space-y-3">
                        <div v-for="dist in distribution_by_type" :key="dist.recipient_type"
                             class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                            <span class="text-slate-700">{{ recipientLabels[dist.recipient_type] || dist.recipient_type }}:</span>
                            <span class="font-semibold text-emerald-600">{{ dist.total_kg }} kg</span>
                        </div>
                        <div v-if="distribution_by_type.length === 0" class="text-center text-slate-400 py-8">
                            Belum ada distribusi daging
                        </div>
                    </div>
                </div>

                <!-- Charts Placeholder -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h4 class="font-semibold text-slate-800 dark:text-slate-200 mb-4">Visualisasi Data</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Pie Chart Placeholder -->
                        <div class="border-2 border-dashed border-slate-300 rounded-lg p-8 text-center">
                            <div class="text-4xl mb-3">📊</div>
                            <div class="text-slate-600">Grafik Distribusi per Hewan</div>
                            <div class="text-sm text-slate-400 mt-2">(Chart.js dapat diintegrasikan)</div>
                        </div>

                        <!-- Bar Chart Placeholder -->
                        <div class="border-2 border-dashed border-slate-300 rounded-lg p-8 text-center">
                            <div class="text-4xl mb-3">📈</div>
                            <div class="text-slate-600">Grafik Distribusi Daging</div>
                            <div class="text-sm text-slate-400 mt-2">(Chart.js dapat diintegrasikan)</div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </AuthenticatedLayout>
</template>
