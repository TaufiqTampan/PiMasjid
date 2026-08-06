<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    available_qurbans: Array,
    distributions: Object,
});

const form = useForm({
    qurban_id: '',
    recipient_name: '',
    recipient_type: '',
    meat_kg: 0,
    date: new Date().toISOString().split('T')[0],
    notes: '',
});

const selectedQurban = computed(() => {
    return props.available_qurbans.find(q => q.id === form.qurban_id);
});

const submit = () => {
    form.post(route('qurban.distribution.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Distribusi Daging Qurban" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-2xl leading-tight text-slate-900 dark:text-white">
                📦 Distribusi Daging Qurban
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Distribution Form -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-8">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-6">Form Distribusi Daging</h3>
                    
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Pilih Qurban *</label>
                                <select v-model.number="form.qurban_id" required
                                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                                    <option value="">Pilih hewan yang sudah disembelih...</option>
                                    <option v-for="qurban in available_qurbans" :key="qurban.id" :value="qurban.id">
                                        {{ qurban.participant_name }} - {{ qurban.animal_type }} 
                                        (Sisa: {{ qurban.remaining_kg }} kg dari {{ qurban.animal_weight }} kg)
                                    </option>
                                </select>
                                <p v-if="form.errors.qurban_id" class="text-red-600 text-sm mt-1">{{ form.errors.qurban_id }}</p>
                            </div>

                            <!-- Qurban Info Card -->
                            <div v-if="selectedQurban" class="md:col-span-2 bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="grid grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span class="text-slate-600">Peserta:</span>
                                        <div class="font-semibold">{{ selectedQurban.participant_name }}</div>
                                    </div>
                                    <div>
                                        <span class="text-slate-600">Berat Total:</span>
                                        <div class="font-semibold">{{ selectedQurban.animal_weight }} kg</div>
                                    </div>
                                    <div>
                                        <span class="text-slate-600">Sisa:</span>
                                        <div class="font-semibold text-purple-600">{{ selectedQurban.remaining_kg }} kg</div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nama Penerima *</label>
                                <input v-model="form.recipient_name" type="text" required
                                       class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                                <p v-if="form.errors.recipient_name" class="text-red-600 text-sm mt-1">{{ form.errors.recipient_name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Kategori Penerima *</label>
                                <select v-model="form.recipient_type" required
                                        class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                                    <option value="">Pilih kategori...</option>
                                    <option value="mustahik">Mustahik (8 Asnaf)</option>
                                    <option value="aqiqah">Aqiqah</option>
                                    <option value="participant">Peserta Qurban</option>
                                    <option value="masjid">Masjid</option>
                                </select>
                                <p v-if="form.errors.recipient_type" class="text-red-600 text-sm mt-1">{{ form.errors.recipient_type }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Berat Daging (kg) *</label>
                                <input v-model.number="form.meat_kg" type="number" step="0.1" min="0" required
                                       class="w-full px-4 py-2 border border-slate-300 rounded-lg" />
                                <p v-if="form.errors.meat_kg" class="text-red-600 text-sm mt-1">{{ form.errors.meat_kg }}</p>
                                <p v-if="selectedQurban" class="text-xs text-slate-500 mt-1">
                                    Maksimal: {{ selectedQurban.remaining_kg }} kg
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Tanggal *</label>
                                <input v-model="form.date" type="date" required
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
                                <span v-else">📦 Distribusikan Daging</span>
                            </button>
                            <Link :href="route('qurban.index')" class="px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-lg">
                                Kembali
                            </Link>
                        </div>
                    </form>
                </div>

                <!-- Distribution History -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-b dark:border-slate-700">
                        <h3 class="font-semibold text-slate-800 dark:text-slate-100">Riwayat Distribusi</h3>
                    </div>

                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Qurban</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Penerima</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Berat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                            <tr v-for="dist in distributions.data" :key="dist.id" class="hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-slate-900 dark:text-white">
                                        {{ dist.qurban?.participant_name || '-' }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ dist.qurban?.animal_type || '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ dist.recipient_name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full"
                                          :class="{
                                              'bg-emerald-100 text-emerald-800': dist.recipient_type === 'mustahik',
                                              'bg-blue-100 text-blue-800': dist.recipient_type === 'aqiqah',
                                              'bg-purple-100 text-purple-800': dist.recipient_type === 'participant',
                                              'bg-amber-100 text-amber-800': dist.recipient_type === 'masjid',
                                          }">
                                        {{ dist.recipient_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-emerald-600">{{ dist.meat_kg }} kg</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ new Date(dist.date).toLocaleDateString('id-ID') }}
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
