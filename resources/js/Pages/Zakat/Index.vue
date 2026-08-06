<script setup>
import { ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    zakats: Object,
    summary: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');
const yearFilter = ref(props.filters.year || new Date().getFullYear());
const paymentTypeFilter = ref(props.filters.payment_type || '');

const applyFilters = () => {
    router.get(route('zakat.index'), {
        search: search.value,
        type: typeFilter.value,
        year: yearFilter.value,
        payment_type: paymentTypeFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatRupiah = (amount) => {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
};
</script>

<template>
    <Head title="Data Zakat" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-2xl leading-tight text-slate-900 dark:text-white">
                🕌 Data Zakat
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-6 text-white shadow-lg">
                        <div class="text-sm opacity-90">Total Uang</div>
                        <div class="text-3xl font-bold mt-2">{{ formatRupiah(summary.total_uang) }}</div>
                        <div class="text-sm opacity-75 mt-2">{{ summary.total_muzakki }} Muzakki</div>
                    </div>

                    <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-6 text-white shadow-lg">
                        <div class="text-sm opacity-90">Total Beras</div>
                        <div class="text-3xl font-bold mt-2">{{ summary.total_beras_kg }} kg</div>
                        <div class="text-sm opacity-75 mt-2">Tahun {{ yearFilter }}</div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
                        <div class="text-sm opacity-90">Breakdown</div>
                        <div class="mt-2 space-y-1">
                            <div class="flex justify-between text-sm">
                                <span>Fitrah:</span>
                                <span class="font-semibold">{{ summary.fitrah_count }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span>Mal:</span>
                                <span class="font-semibold">{{ summary.mal_count }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span>Profesi:</span>
                                <span class="font-semibold">{{ summary.profesi_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters & Actions -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <input
                            v-model="search"
                            @input="applyFilters"
                            type="text"
                            placeholder="Cari nama/NIK..."
                            class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                        />

                        <select v-model="typeFilter" @change="applyFilters" class="px-4 py-2 border border-slate-300 rounded-lg">
                            <option value="">Semua Type</option>
                            <option value="fitrah">Fitrah</option>
                            <option value="mal">Mal</option>
                            <option value="profesi">Profesi</option>
                        </select>

                        <select v-model="paymentTypeFilter" @change="applyFilters" class="px-4 py-2 border border-slate-300 rounded-lg">
                            <option value="">Uang & Beras</option>
                            <option value="uang">Uang</option>
                            <option value="beras">Beras</option>
                        </select>

                        <input v-model="yearFilter" @change="applyFilters" type="number" class="px-4 py-2 border border-slate-300 rounded-lg" />

                        <Link :href="route('zakat.create')" class="btn-primary text-center">
                            ➕ Tambah Zakat
                        </Link>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Muzakki</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Petugas</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                            <tr v-for="zakat in zakats.data" :key="zakat.id" class="hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ zakat.muzakki_name }}</div>
                                    <div v-if="zakat.muzakki_nik" class="text-sm text-slate-500">NIK: {{ zakat.muzakki_nik }}</div>
                                    <div v-if="zakat.person_count" class="text-sm text-slate-500">{{ zakat.person_count }} jiwa</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full"
                                          :class="{
                                              'bg-emerald-100 text-emerald-800': zakat.type === 'fitrah',
                                              'bg-blue-100 text-blue-800': zakat.type === 'mal',
                                              'bg-purple-100 text-purple-800': zakat.type === 'profesi',
                                          }">
                                        {{ zakat.type === 'fitrah' ? 'Fitrah' : zakat.type === 'mal' ? 'Mal' : 'Profesi' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="zakat.payment_type === 'uang'" class="font-semibold text-emerald-600">
                                        {{ formatRupiah(zakat.amount) }}
                                    </div>
                                    <div v-else class="font-semibold text-amber-600">
                                        {{ zakat.rice_kg }} kg beras
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ new Date(zakat.date).toLocaleDateString('id-ID') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ zakat.collected_by?.name || '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t dark:border-slate-700">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-slate-600">
                                Showing {{ zakats.from }} - {{ zakats.to }} of {{ zakats.total }}
                            </div>
                            <div class="flex gap-2">
                                <template v-for="link in zakats.links" :key="link.label">
                                    <Link v-if="link.url"
                                          :href="link.url"
                                          :class="[
                                              'px-3 py-1 rounded',
                                              link.active ? 'bg-emerald-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-100'
                                          ]"
                                          v-html="link.label">
                                    </Link>
                                    <span v-else
                                          :class="['px-3 py-1 rounded bg-slate-100 text-slate-400 cursor-not-allowed']"
                                          v-html="link.label">
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="mt-6 flex gap-4">
                    <Link :href="route('zakat.distribute')" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                        📤 Penyaluran Zakat
                    </Link>
                    <Link :href="route('zakat.reports')" class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg">
                        📊 Laporan
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
