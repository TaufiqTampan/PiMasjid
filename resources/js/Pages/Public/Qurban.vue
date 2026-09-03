<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import LoginRequiredModal from '@/Components/Public/LoginRequiredModal.vue';

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);
const showLoginModal = ref(false);
const loginTargetUrl = ref('/info/qurban');

const form = useForm({
    participant_name: page.props.auth?.user?.name || '',
    participant_phone: page.props.auth?.user?.phone || '',
    participant_address: '',
    animal_type: 'kambing',
    is_shared: false,
    notes: '',
    website_hp: '',
    hp_time: Math.floor(Date.now() / 1000),
});

const submit = () => {
    if (!isAuthenticated.value) {
        showLoginModal.value = true;
        return;
    }

    form.post(route('public.qurban.register'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Pendaftaran Qurban" />

    <PublicLayout>
        <div class="bg-emerald-700 pt-32 pb-16 text-center text-white mb-8">
            <h1 class="text-4xl font-bold mb-4">🐑 Pendaftaran Qurban</h1>
            <p class="text-emerald-100 max-w-2xl mx-auto">
                Daftarkan diri Anda untuk berqurban di Masjid kami. Form ini hanya untuk pendaftaran awal,
                pengurus akan menghubungi Anda untuk konfirmasi.
            </p>
        </div>
        <div class="pb-12 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Info Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="text-3xl mb-3">🐐</div>
                        <h3 class="font-bold text-lg mb-2">Kambing/Domba</h3>
                        <p class="text-slate-600 text-sm mb-2">1 ekor untuk 1 orang</p>
                        <div class="text-emerald-600 font-semibold">Rp 2.500.000 - 4.000.000</div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="text-3xl mb-3">🐄</div>
                        <h3 class="font-bold text-lg mb-2">Sapi/Kerbau</h3>
                        <p class="text-slate-600 text-sm mb-2">1 ekor untuk max 7 orang (patungan)</p>
                        <div class="text-emerald-600 font-semibold">Rp 15.000.000 - 25.000.000</div>
                    </div>
                </div>

                <!-- Registration Form -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h3 class="text-2xl font-bold text-slate-900 mb-6">Form Pendaftaran</h3>
                    
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Honeypot anti-spam hidden inputs -->
                        <div style="display: none !important;" aria-hidden="true">
                            <input type="text" name="website_hp" v-model="form.website_hp" tabindex="-1" autocomplete="off" />
                            <input type="hidden" name="hp_time" v-model="form.hp_time" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Nama Lengkap *</label>
                            <input v-model="form.participant_name" type="text" required
                                   class="w-full px-4 py-3 border border-slate-300 rounded-lg" 
                                   placeholder="Masukkan nama lengkap" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">No. WhatsApp *</label>
                            <input v-model="form.participant_phone" type="tel" required
                                   class="w-full px-4 py-3 border border-slate-300 rounded-lg"
                                   placeholder="08xxxxxxxxxx" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Alamat</label>
                            <textarea v-model="form.participant_address" rows="3"
                                      class="w-full px-4 py-3 border border-slate-300 rounded-lg"
                                      placeholder="Alamat lengkap"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-4">Jenis Hewan *</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <label class="cursor-pointer">
                                    <input v-model="form.animal_type" type="radio" value="kambing" class="peer hidden" />
                                    <div class="border-2 border-slate-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 rounded-lg p-4 text-center transition">
                                        <div class="text-2xl mb-1">🐐</div>
                                        <div class="text-sm font-semibold">Kambing</div>
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input v-model="form.animal_type" type="radio" value="domba" class="peer hidden" />
                                    <div class="border-2 border-slate-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 rounded-lg p-4 text-center transition">
                                        <div class="text-2xl mb-1">🐑</div>
                                        <div class="text-sm font-semibold">Domba</div>
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input v-model="form.animal_type" type="radio" value="sapi" class="peer hidden" />
                                    <div class="border-2 border-slate-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 rounded-lg p-4 text-center transition">
                                        <div class="text-2xl mb-1">🐄</div>
                                        <div class="text-sm font-semibold">Sapi</div>
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input v-model="form.animal_type" type="radio" value="kerbau" class="peer hidden" />
                                    <div class="border-2 border-slate-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 rounded-lg p-4 text-center transition">
                                        <div class="text-2xl mb-1">🐃</div>
                                        <div class="text-sm font-semibold">Kerbau</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div v-if="['sapi', 'kerbau'].includes(form.animal_type)">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input v-model="form.is_shared" type="checkbox" class="w-5 h-5 text-emerald-600 rounded" />
                                <span class="font-medium">Saya ingin ikut patungan (max 7 orang)</span>
                            </label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Catatan</label>
                            <textarea v-model="form.notes" rows="2"
                                      class="w-full px-4 py-3 border border-slate-300 rounded-lg"
                                      placeholder="Tambahan keterangan (opsional)"></textarea>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-blue-800">
                                📌 <strong>Catatan:</strong> Ini adalah form pendaftaran awal. Pengurus masjid akan menghubungi Anda
                                melalui WhatsApp untuk konfirmasi harga, pembayaran, dan detail lainnya.
                            </p>
                        </div>

                        <button type="submit" :disabled="form.processing"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 rounded-lg transition"
                                :class="{ 'opacity-50': form.processing }">
                            <span v-if="form.processing">Mengirim...</span>
                            <span v-else>📝 Daftar Qurban</span>
                        </button>
                    </form>
                </div>

                <!-- FAQ Section -->
                <div class="mt-12 bg-white rounded-xl shadow-md p-8">
                    <h3 class="text-xl font-bold text-slate-900 mb-6">❓ Pertanyaan Umum</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-semibold text-slate-800 mb-2">Kapan waktu penyembelihan?</h4>
                            <p class="text-slate-600 text-sm">Penyembelihan dilakukan pada hari Raya Idul Adha (10 Dzulhijjah) hingga hari Tasyrik (11-13 Dzulhijjah).</p>
                        </div>

                        <div>
                            <h4 class="font-semibold text-slate-800 mb-2">Bagaimana cara pembayaran?</h4>
                            <p class="text-slate-600 text-sm">Pembayaran dapat dilakukan secara tunai atau transfer. Detail akan diinformasikan oleh pengurus setelah pendaftaran.</p>
                        </div>

                        <div>
                            <h4 class="font-semibold text-slate-800 mb-2">Apakah daging bisa diambil?</h4>
                            <p class="text-slate-600 text-sm">Ya, peserta qurban dapat mengambil sebagian daging setelah penyembelihan. Sisanya akan didistribusikan kepada mustahik.</p>
                        </div>

                        <div>
                            <h4 class="font-semibold text-slate-800 mb-2">Berapa batas waktu pendaftaran?</h4>
                            <p class="text-slate-600 text-sm">Pendaftaran dibuka hingga H-7 sebelum Idul Adha atau sampai kuota terpenuhi.</p>
                        </div>
                    </div>
                </div>

                <!-- Contact -->
                <div class="mt-8 text-center p-6 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl text-white">
                    <h4 class="font-bold mb-2">Ada Pertanyaan?</h4>
                    <p class="mb-4">Hubungi pengurus masjid untuk informasi lebih lanjut</p>
                    <a :href="$page.props.settings?.whatsapp ? `https://wa.me/${$page.props.settings.whatsapp}` : '#'" target="_blank"
                       class="inline-block px-6 py-3 bg-white text-emerald-600 font-bold rounded-lg hover:bg-emerald-50 transition">
                        💬 Hubungi via WhatsApp
                    </a>
                </div>
            </div>
        </div>

        <!-- Login Required Modal -->
        <LoginRequiredModal
            :show="showLoginModal"
            :targetUrl="loginTargetUrl"
            @close="showLoginModal = false"
        />
    </PublicLayout>
</template>
