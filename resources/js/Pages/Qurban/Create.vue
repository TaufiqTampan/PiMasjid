<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    current_year: Number,
    available_share_groups: Array,
    qurban: Object, // Added prop for edit mode
});

const isEditMode = computed(() => !!props.qurban);

const form = useForm({
    participant_name: props.qurban?.participant_name || '',
    participant_nik: props.qurban?.participant_nik || '',
    participant_phone: props.qurban?.participant_phone || '',
    participant_address: props.qurban?.participant_address || '',
    animal_type: props.qurban?.animal_type || 'kambing',
    animal_weight: props.qurban?.animal_weight || '',
    animal_price: props.qurban?.animal_price || 0,
    is_shared: props.qurban?.is_shared ? true : false,
    share_count: props.qurban?.share_count || 1,
    share_position: props.qurban?.share_position || 1,
    share_group_id: props.qurban?.share_group_id || '',
    year: props.qurban?.year || props.current_year,
    registration_date: props.qurban?.registration_date || new Date().toISOString().split('T')[0],
    notes: props.qurban?.notes || '',
    status: props.qurban?.status || 'paid', // Default to paid
});

const maxShareCount = computed(() => {
    if (['kambing', 'domba'].includes(form.animal_type)) {
        return 1;
    }
    return 7; // sapi, kerbau, unta
});

const canShare = computed(() => {
    return !['kambing', 'domba'].includes(form.animal_type);
});

// Watch animal type changes
watch(() => form.animal_type, (newType) => {
    if (['kambing', 'domba'].includes(newType)) {
        form.is_shared = false;
        form.share_count = 1;
        form.share_position = 1;
    }
});

// Watch is_shared changes
watch(() => form.is_shared, (isShared) => {
    if (!isShared) {
        form.share_count = 1;
        form.share_position = 1;
        form.share_group_id = '';
    }
});

const submit = () => {
    if (isEditMode.value) {
        form.put(route('qurban.update', props.qurban.id));
    } else {
        form.post(route('qurban.store'));
    }
};

const formatRupiah = (amount) => {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
};

const pricePerShare = computed(() => {
    if (form.is_shared && form.share_count > 0) {
        return Math.round(form.animal_price / form.share_count);
    }
    return form.animal_price;
});

const filteredShareGroups = computed(() => {
    if (!props.available_share_groups) return [];
    return props.available_share_groups.filter(g => g.animal_type === form.animal_type);
});

// Computed property for taken positions in selected group
const takenPositions = computed(() => {
    if (!form.share_group_id) return [];
    
    const group = props.available_share_groups.find(g => g.share_group_id === form.share_group_id);
    if (!group || !group.taken_positions) return [];
    
    // Parse taken positions string "1,2,3" into array of numbers
    return group.taken_positions.split(',').map(Number);
});

// Watch share_group_id changes
watch(() => form.share_group_id, (newGroupId) => {
    if (newGroupId) {
        const group = props.available_share_groups.find(g => g.share_group_id === newGroupId);
        if (group) {
            form.share_count = group.max_share;
            form.animal_price = group.avg_price;
            form.animal_weight = group.max_weight;

            // Auto set position to first available numeric slot
            const taken = group.taken_positions ? group.taken_positions.split(',').map(Number) : [];
            for (let i = 1; i <= group.max_share; i++) {
                if (!taken.includes(i)) {
                    form.share_position = i;
                    break;
                }
            }
        }
    }
});
</script>

<template>
    <Head title="Daftar Qurban" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-2xl leading-tight text-slate-900 dark:text-white">
                {{ isEditMode ? '✏️ Edit Qurban' : '➕ Registrasi Qurban' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit">
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-8 space-y-6">
                        <!-- Data Peserta -->
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">👤 Data Peserta</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nama Peserta *</label>
                                    <input v-model="form.participant_name" type="text" required
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                                    <p v-if="form.errors.participant_name" class="text-red-600 text-sm mt-1">{{ form.errors.participant_name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">NIK</label>
                                    <input v-model="form.participant_nik" type="text"
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">No HP *</label>
                                    <input v-model="form.participant_phone" type="text" required
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                                    <p v-if="form.errors.participant_phone" class="text-red-600 text-sm mt-1">{{ form.errors.participant_phone }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Tanggal Registrasi *</label>
                                    <input v-model="form.registration_date" type="date" required
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Alamat</label>
                                    <textarea v-model="form.participant_address" rows="2"
                                              class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Jenis Hewan -->
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">🐑 Jenis Hewan</h3>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                                <label class="cursor-pointer">
                                    <input v-model="form.animal_type" type="radio" value="kambing" class="peer hidden" />
                                    <div class="border-2 border-slate-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 rounded-lg p-4 text-center transition">
                                        <div class="text-3xl mb-2">🐐</div>
                                        <div class="font-semibold">Kambing</div>
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input v-model="form.animal_type" type="radio" value="domba" class="peer hidden" />
                                    <div class="border-2 border-slate-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 rounded-lg p-4 text-center transition">
                                        <div class="text-3xl mb-2">🐑</div>
                                        <div class="font-semibold">Domba</div>
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input v-model="form.animal_type" type="radio" value="sapi" class="peer hidden" />
                                    <div class="border-2 border-slate-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 rounded-lg p-4 text-center transition">
                                        <div class="text-3xl mb-2">🐄</div>
                                        <div class="font-semibold">Sapi</div>
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input v-model="form.animal_type" type="radio" value="kerbau" class="peer hidden" />
                                    <div class="border-2 border-slate-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 rounded-lg p-4 text-center transition">
                                        <div class="text-3xl mb-2">🐃</div>
                                        <div class="font-semibold">Kerbau</div>
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input v-model="form.animal_type" type="radio" value="unta" class="peer hidden" />
                                    <div class="border-2 border-slate-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 rounded-lg p-4 text-center transition">
                                        <div class="text-3xl mb-2">🐪</div>
                                        <div class="font-semibold">Unta</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Detail Hewan -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Estimasi Berat (kg)</label>
                                <input v-model.number="form.animal_weight" type="number" step="0.1" min="0"
                                       class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Harga Total (Rp) *</label>
                                <input v-model.number="form.animal_price" type="number" min="0" required
                                       class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Status Pembayaran</label>
                                <select v-model="form.status" required
                                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                                    <option value="paid">Sudah Bayar</option>
                                    <option value="registered">Belum Bayar</option>
                                </select>
                            </div>
                        </div>

                        <!-- Sistem Patungan -->
                        <div v-if="canShare" class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-6">
                            <h4 class="font-semibold text-purple-800 mb-4">👥 Sistem Patungan</h4>
                            
                            <label class="flex items-center gap-3 mb-4 cursor-pointer">
                                <input v-model="form.is_shared" type="checkbox" class="w-5 h-5 text-purple-600 rounded" />
                                <span class="font-medium">Qurban Patungan (Max {{ maxShareCount }} orang)</span>
                            </label>

                            <div v-if="form.is_shared" class="space-y-4">
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Gabung Grup Patungan (Opsional)</label>
                                        <select v-model="form.share_group_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                                            <option value="">-- Buat Grup Baru --</option>
                                            <option v-for="group in filteredShareGroups" :key="group.share_group_id" :value="group.share_group_id">
                                                Grup #{{ group.share_group_id.substr(0, 8) }} ({{ group.participant_count }}/{{ group.max_share }} orang)
                                            </option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Jumlah Patungan *</label>
                                        <input v-model.number="form.share_count" type="number" :max="maxShareCount" min="1" required
                                               :readonly="!!form.share_group_id"
                                               class="w-full px-4 py-2 border border-slate-300 rounded-lg"
                                               :class="{'bg-slate-100': !!form.share_group_id}" />
                                        <p class="text-xs text-slate-500 mt-1">Maksimal {{ maxShareCount }} orang</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Posisi Anda *</label>
                                        <select v-model.number="form.share_position" required
                                                class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                                            <option v-for="n in form.share_count" :key="n" :value="n" 
                                                    :disabled="takenPositions.includes(n)">
                                                {{ n }}/{{ form.share_count }} {{ takenPositions.includes(n) ? '(Sudah Terisi)' : '' }}
                                            </option>
                                        </select>
                                    </div>

                                <div class="bg-white rounded-lg p-4 border border-purple-300">
                                    <div class="text-sm space-y-2">
                                        <div class="flex justify-between">
                                            <span>Harga Total:</span>
                                            <strong>{{ formatRupiah(form.animal_price) }}</strong>
                                        </div>
                                        <div class="flex justify-between font-semibold text-purple-600">
                                            <span>Harga per Orang:</span>
                                            <span>{{ formatRupiah(pricePerShare) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-xs text-slate-600">
                                    💡 Anda bisa bergabung dengan grup patungan yang sudah ada atau membuat grup baru
                                </p>
                            </div>
                        </div>

                        <div v-else class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <p class="text-sm text-amber-800">
                                ℹ️ Kambing dan Domba tidak bisa dipatungankan (1 ekor = 1 orang)
                            </p>
                        </div>

                        <!-- Catatan -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Catatan</label>
                            <textarea v-model="form.notes" rows="3"
                                      class="w-full px-4 py-2 border border-slate-300 rounded-lg"></textarea>
                        </div>

                        <!-- Submit -->
                        <div class="flex gap-4 pt-4">
                            <button type="submit" :disabled="form.processing"
                                    class="flex-1 btn-primary"
                                    :class="{ 'opacity-50': form.processing }">
                                <span v-if="form.processing">Menyimpan...</span>
                                <span v-else>{{ isEditMode ? '💾 Simpan Perubahan' : '🐑 Daftar Qurban' }}</span>
                            </button>
                            <button type="button" @click="$inertia.visit(route('qurban.index'))"
                                    class="px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-lg">
                                Batal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
