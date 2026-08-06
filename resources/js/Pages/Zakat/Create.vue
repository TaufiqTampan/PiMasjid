<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    current_year: Number,
});

const form = useForm({
    muzakki_name: '',
    muzakki_nik: '',
    muzakki_phone: '',
    muzakki_address: '',
    type: 'fitrah',
    amount: 0,
    payment_type: 'uang',
    rice_kg: 0,
    person_count: 1,
    year: props.current_year,
    date: new Date().toISOString().split('T')[0],
    notes: '',
});

// Calculator states
const ricePricePerLiter = ref(15000);
const goldPricePerGram = ref(1000000);
const harta = ref(0);
const hutang = ref(0);
const penghasilan = ref(0);

const calculatedAmount = computed(() => {
    if (form.type === 'fitrah' && form.payment_type === 'uang') {
        return 3.5 * ricePricePerLiter.value * form.person_count;
    }
    if (form.type === 'mal') {
        const nisab = 85 * goldPricePerGram.value;
        const nettWealth = harta.value - hutang.value;
        return nettWealth >= nisab ? nettWealth * 0.025 : 0;
    }
    if (form.type === 'profesi') {
        return penghasilan.value * 0.025;
    }
    return 0;
});

const nisab = computed(() => 85 * goldPricePerGram.value);

const isAboveNisab = computed(() => {
    if (form.type === 'mal') {
        return (harta.value - hutang.value) >= nisab.value;
    }
    return true;
});

// Auto-fill rice_kg for fitrah
watch([() => form.type, () => form.payment_type, () => form.person_count], () => {
    if (form.type === 'fitrah' && form.payment_type === 'beras') {
        form.rice_kg = 3.5 * form.person_count;
    }
});

// Auto-fill amount from calculator
watch(calculatedAmount, (newValue) => {
    if (form.payment_type === 'uang') {
        form.amount = Math.round(newValue);
    }
});

const submit = () => {
    form.post(route('zakat.store'));
};

const formatRupiah = (amount) => {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
};
</script>

<template>
    <Head title="Input Zakat" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-2xl leading-tight text-slate-900 dark:text-white">
                ➕ Input Zakat
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit">
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-8 space-y-6">
                        <!-- Data Muzakki -->
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">📋 Data Muzakki</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nama *</label>
                                    <input v-model="form.muzakki_name" type="text" required
                                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white" />
                                    <p v-if="form.errors.muzakki_name" class="text-red-600 text-sm mt-1">{{ form.errors.muzakki_name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">NIK</label>
                                    <input v-model="form.muzakki_nik" type="text"
                                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">No HP</label>
                                    <input v-model="form.muzakki_phone" type="text"
                                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Tanggal *</label>
                                    <input v-model="form.date" type="date" required
                                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white" />
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Alamat</label>
                                    <textarea v-model="form.muzakki_address" rows="2"
                                              class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Tipe Zakat -->
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">🕌 Tipe Zakat</h3>
                            <div class="flex gap-4">
                                <label class="flex items-center cursor-pointer">
                                    <input v-model="form.type" type="radio" value="fitrah" class="w-4 h-4 text-emerald-600" />
                                    <span class="ml-2">Zakat Fitrah</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input v-model="form.type" type="radio" value="mal" class="w-4 h-4 text-blue-600" />
                                    <span class="ml-2">Zakat Mal</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input v-model="form.type" type="radio" value="profesi" class="w-4 h-4 text-purple-600" />
                                    <span class="ml-2">Zakat Profesi</span>
                                </label>
                            </div>
                        </div>

                        <!-- Fitrah Form -->
                        <div v-if="form.type === 'fitrah'" class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-6">
                            <h4 class="font-semibold text-emerald-800 dark:text-emerald-400 mb-4">Zakat Fitrah</h4>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Jumlah Jiwa *</label>
                                    <input v-model.number="form.person_count" type="number" min="1" required
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Pembayaran *</label>
                                    <div class="flex gap-4">
                                        <label class="flex items-center cursor-pointer">
                                            <input v-model="form.payment_type" type="radio" value="uang" class="w-4 h-4" />
                                            <span class="ml-2">💰 Uang</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer">
                                            <input v-model="form.payment_type" type="radio" value="beras" class="w-4 h-4" />
                                            <span class="ml-2">🌾 Beras</span>
                                        </label>
                                    </div>
                                </div>

                                <div v-if="form.payment_type === 'uang'">
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Harga Beras per Liter</label>
                                    <input v-model.number="ricePricePerLiter" type="number" min="0"
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg" />
                                    <p class="text-sm text-slate-600 mt-2">
                                        Kalkulasi: 3.5L x {{ formatRupiah(ricePricePerLiter) }} x {{ form.person_count }} jiwa
                                        = <strong class="text-emerald-600">{{ formatRupiah(calculatedAmount) }}</strong>
                                    </p>
                                </div>

                                <div v-else>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Jumlah Beras (kg) *</label>
                                    <input v-model.number="form.rice_kg" type="number" step="0.1" min="0"
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg" />
                                    <p class="text-sm text-slate-600 mt-2">
                                        Standar: 3.5L = ~2.5kg per jiwa
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Mal Form -->
                        <div v-if="form.type === 'mal'" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                            <h4 class="font-semibold text-blue-800 mb-4">Zakat Mal (2.5% dari harta)</h4>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Total Harta (Rp)</label>
                                    <input v-model.number="harta" type="number" min="0"
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Total Hutang (Rp)</label>
                                    <input v-model.number="hutang" type="number" min="0"
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Harga Emas per Gram</label>
                                    <input v-model.number="goldPricePerGram" type="number" min="0"
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg" />
                                </div>

                                <div class="bg-white rounded-lg p-4 border border-blue-300">
                                    <div class="text-sm space-y-2">
                                        <div class="flex justify-between">
                                            <span>Nisab (85 gram emas):</span>
                                            <strong>{{ formatRupiah(nisab) }}</strong>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Harta Bersih:</span>
                                            <strong>{{ formatRupiah(harta - hutang) }}</strong>
                                        </div>
                                        <div class="flex justify-between font-semibold" :class="isAboveNisab ? 'text-emerald-600' : 'text-red-600'">
                                            <span>Zakat (2.5%):</span>
                                            <span>{{ formatRupiah(calculatedAmount) }}</span>
                                        </div>
                                        <p v-if="!isAboveNisab" class="text-red-600 text-xs mt-2">
                                            ⚠️ Harta belum mencapai nisab. Zakat mal tidak wajib.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Profesi Form -->
                        <div v-if="form.type === 'profesi'" class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-6">
                            <h4 class="font-semibold text-purple-800 mb-4">Zakat Profesi (2.5% dari penghasilan)</h4>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Penghasilan Bulanan (Rp)</label>
                                    <input v-model.number="penghasilan" type="number" min="0"
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg" />
                                </div>

                                <div class="bg-white rounded-lg p-4 border border-purple-300">
                                    <div class="text-sm space-y-2">
                                        <div class="flex justify-between">
                                            <span>Penghasilan:</span>
                                            <strong>{{ formatRupiah(penghasilan) }}</strong>
                                        </div>
                                        <div class="flex justify-between font-semibold text-purple-600">
                                            <span>Zakat (2.5%):</span>
                                            <span>{{ formatRupiah(calculatedAmount) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Final Amount (if uang) -->
                        <div v-if="form.payment_type === 'uang'">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Jumlah Dibayar (Rp) *</label>
                            <input v-model.number="form.amount" type="number" min="0" required
                                   class="w-full px-4 py-2 border border-slate-300 rounded-lg text-lg font-semibold" />
                        </div>

                        <!-- Notes -->
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
                                <span v-else>💾 Simpan Zakat</span>
                            </button>
                            <button type="button" @click="$inertia.visit(route('zakat.index'))"
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
