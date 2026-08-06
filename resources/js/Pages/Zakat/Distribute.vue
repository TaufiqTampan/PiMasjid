<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    distributions: Object,
    asnaf_categories: Object,
    summary: Object,
});

const form = useForm({
    mustahik_name: '',
    mustahik_category: '',
    amount: 0,
    type: 'uang',
    rice_kg: 0,
    year: new Date().getFullYear(),
    date: new Date().toISOString().split('T')[0],
    notes: '',
});

const submit = () => {
    form.post(route('zakat.distribution.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const formatRupiah = (amount) => {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
};
</script>

<template>
    <Head title="Penyaluran Zakat" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-2xl leading-tight text-slate-900 dark:text-white">
                📤 Penyaluran Zakat
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6">
                        <div class="text-sm text-slate-500">Total Terkumpul</div>
                        <div class="text-2xl font-bold text-emerald-600 mt-2">{{ formatRupiah(summary.total_collected) }}</div>
                        <div class="text-xs text-slate-500 mt-1">{{ summary.total_beras_kg }} kg beras</div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6">
                        <div class="text-sm text-slate-500">Total Disalurkan</div>
                        <div class="text-2xl font-bold text-blue-600 mt-2">{{ formatRupiah(summary.total_distributed) }}</div>
                        <div class="text-xs text-slate-500 mt-1">{{ summary.total_beras_distributed_kg }} kg beras</div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6">
                        <div class="text-sm text-slate-500">Sisa</div>
                        <div class="text-2xl font-bold text-purple-600 mt-2">{{ formatRupiah(summary.remaining) }}</div>
                        <div class="text-xs text-slate-500 mt-1">{{ summary.remaining_beras_kg }} kg beras</div>
                    </div>
                </div>

                <!-- Distribution Form -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-8">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-6">Form Penyaluran</h3>
                    
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nama Mustahik *</label>
                                <input v-model="form.mustahik_name" type="text" required
                                       class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white" />
                                <p v-if="form.errors.mustahik_name" class="text-red-600 text-sm mt-1">{{ form.errors.mustahik_name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Kategori Asnaf *</label>
                                <select v-model="form.mustahik_category" required
                                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                                    <option value="">Pilih Asnaf...</option>
                                    <option v-for="(label, key) in asnaf_categories" :key="key" :value="key">
                                        {{ label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.mustahik_category" class="text-red-600 text-sm mt-1">{{ form.errors.mustahik_category }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Jenis Pemberian *</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center cursor-pointer">
                                        <input v-model="form.type" type="radio" value="uang" class="w-4 h-4" />
                                        <span class="ml-2">💰 Uang</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input v-model="form.type" type="radio" value="beras" class="w-4 h-4" />
                                        <span class="ml-2">🌾 Beras</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Tanggal *</label>
                                <input v-model="form.date" type="date" required
                                       class="w-full px-4 py-2 border border-slate-300 rounded-lg" />
                            </div>

                            <div v-if="form.type === 'uang'">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Jumlah (Rp) *</label>
                                <input v-model.number="form.amount" type="number" min="0" required
                                       class="w-full px-4 py-2 border border-slate-300 rounded-lg" />
                            </div>

                            <div v-else>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Jumlah Beras (kg) *</label>
                                <input v-model.number="form.rice_kg" type="number" step="0.1" min="0" required
                                       class="w-full px-4 py-2 border border-slate-300 rounded-lg" />
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Catatan</label>
                                <textarea v-model="form.notes" rows="2"
                                          class="w-full px-4 py-2 border border-slate-300 rounded-lg"></textarea>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" :disabled="form.processing"
                                    class="btn-primary"
                                    :class="{ 'opacity-50': form.processing }">
                                <span v-if="form.processing">Menyimpan...</span>
                                <span v-else>📤 Salurkan Zakat</span>
                            </button>
                            <Link :href="route('zakat.index')" class="px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-lg">
                                Kembali
                            </Link>
                        </div>
                    </form>
                </div>

                <!-- Distribution History -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-b dark:border-slate-700">
                        <h3 class="font-semibold text-slate-800 dark:text-slate-100">Riwayat Penyaluran</h3>
                    </div>

                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Mustahik</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Asnaf</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Petugas</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                            <tr v-for="dist in distributions.data" :key="dist.id" class="hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ dist.mustahik_name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                        {{ dist.mustahik_category }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="dist.type === 'uang'" class="font-semibold text-emerald-600">
                                        {{ formatRupiah(dist.amount) }}
                                    </div>
                                    <div v-else class="font-semibold text-amber-600">
                                        {{ dist.rice_kg }} kg beras
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ new Date(dist.date).toLocaleDateString('id-ID') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ dist.distributed_by?.name || '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t dark:border-slate-700">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-slate-600">
                                Showing {{ distributions.from }} - {{ distributions.to }} of {{ distributions.total }}
                            </div>
                            <div class="flex gap-2">
                                <Link v-for="link in distributions.links" :key="link.label"
                                      :href="link.url"
                                      :class="[
                                          'px-3 py-1 rounded',
                                          link.active ? 'bg-emerald-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-100'
                                      ]"
                                      v-html="link.label">
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
