<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { getWhatsAppUrl, buildDonationWAMessage, buildRequestWAMessage } from '@/Utils/whatsapp';
import { 
    HeartIcon, 
    GiftIcon, 
    UsersIcon, 
    SparklesIcon, 
    PlusIcon, 
    CheckCircleIcon,
    XMarkIcon,
    InboxArrowDownIcon
} from '@heroicons/vue/24/outline';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import LoginRequiredModal from '@/Components/Public/LoginRequiredModal.vue';

const props = defineProps({
    programs: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            total_programs: 0,
            total_donors: 0,
            total_collected: 0,
            total_distributed: 0
        })
    },
});

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);

// Layout configuration
defineOptions({
    layout: PublicLayout
});

// Modals state
const showDonateModal = ref(false);
const showRequestModal = ref(false);
const showSuccessModal = ref(false);
const showLoginModal = ref(false);
const loginTargetUrl = ref('/lumbung-pangan');
const successType = ref('');
const successData = ref(null);
const activeProgram = ref(null);

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('action') === 'donate') {
        openDonate();
    }
});

// Form configurations
const donateForm = useForm({
    donor_name: '',
    donor_phone: '',
    donation_type: 'barang', // uang, barang
    food_barn_program_id: '',
    amount: '',
    items: '',
    proof: null,
    website_hp: '',
    hp_time: Math.floor(Date.now() / 1000),
});

const requestForm = useForm({
    food_barn_program_id: '',
    name: '',
    phone: '',
    address: '',
    family_members: 1,
    reason: '',
    website_hp: '',
    hp_time: Math.floor(Date.now() / 1000),
});

// Modal control helpers
const openDonate = (program = null) => {
    if (!isAuthenticated.value) {
        loginTargetUrl.value = '/lumbung-pangan';
        showLoginModal.value = true;
        return;
    }

    donateForm.reset();
    donateForm.clearErrors();
    activeProgram.value = program;
    donateForm.donor_name = page.props.auth?.user?.name || '';
    donateForm.donor_phone = page.props.auth?.user?.phone || '';
    donateForm.food_barn_program_id = program ? program.id : '';
    showDonateModal.value = true;
};

const openRequest = (program) => {
    if (!isAuthenticated.value) {
        loginTargetUrl.value = '/lumbung-pangan';
        showLoginModal.value = true;
        return;
    }

    requestForm.reset();
    requestForm.clearErrors();
    activeProgram.value = program;
    requestForm.name = page.props.auth?.user?.name || '';
    requestForm.phone = page.props.auth?.user?.phone || '';
    requestForm.food_barn_program_id = program.id;
    showRequestModal.value = true;
};

const submitDonation = () => {
    const submittedValues = {
        donor_name: donateForm.donor_name,
        donor_phone: donateForm.donor_phone,
        donation_type: donateForm.donation_type,
        amount: donateForm.amount,
        items: donateForm.items,
        programTitle: activeProgram.value ? activeProgram.value.title : 'Umum',
    };
    donateForm.post(route('public.lumbung-pangan.donate'), {
        forceFormData: true,
        onSuccess: () => {
            showDonateModal.value = false;
            successType.value = 'donation';
            successData.value = submittedValues;
            showSuccessModal.value = true;
            donateForm.reset();
        }
    });
};

const submitRequest = () => {
    const submittedValues = {
        name: requestForm.name,
        phone: requestForm.phone,
        family_members: requestForm.family_members,
        reason: requestForm.reason,
        programTitle: activeProgram.value ? activeProgram.value.title : 'Umum',
    };
    requestForm.post(route('public.lumbung-pangan.request'), {
        onSuccess: () => {
            showRequestModal.value = false;
            successType.value = 'request';
            successData.value = submittedValues;
            showSuccessModal.value = true;
            requestForm.reset();
        }
    });
};

const getFoodBarnWALink = computed(() => {
    if (!successData.value) return '#';
    const waNo = page.props.settings?.whatsapp || '6281234567890';
    const siteName = page.props.settings?.site_name || 'Masjid';

    let msg = '';
    if (successType.value === 'donation') {
        msg = buildDonationWAMessage({
            siteName,
            donorName: successData.value.donor_name,
            donorPhone: successData.value.donor_phone,
            donationType: successData.value.donation_type,
            programTitle: successData.value.programTitle,
            amount: successData.value.amount,
            items: successData.value.items,
        });
    } else {
        msg = buildRequestWAMessage({
            siteName,
            name: successData.value.name,
            phone: successData.value.phone,
            programTitle: successData.value.programTitle,
            familyMembers: successData.value.family_members,
            reason: successData.value.reason,
        });
    }
    return getWhatsAppUrl(waNo, msg);
});

const handleFileChange = (e) => {
    donateForm.proof = e.target.files[0];
};

const formatRupiah = (amount) => {
    if (!amount) return 'Rp 0';
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
};
</script>

<template>
    <Head title="Lumbung Pangan" />

    <!-- Hero Header with Bakri Teal and lime touches -->
    <div class="relative bg-gradient-to-br from-[#068d9e] via-teal-800 to-slate-900 pt-32 pb-20 text-white overflow-hidden">
        <!-- Parallax effect backgrounds -->
        <div class="absolute inset-0 opacity-[0.03] pointer-events-none bg-pattern-islamic bg-repeat"></div>
        <div class="absolute -top-32 -right-32 w-96 h-96 bg-bakri-lime/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white text-xs font-extrabold uppercase tracking-widest mb-4">
                <SparklesIcon class="w-4 h-4 text-bakri-lime animate-pulse" />
                Lumbung Pangan Masjid Bakri
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black mb-6 tracking-tight uppercase leading-none">
                Agar Tiada Lara <span class="text-bakri-lime">Dalam Lapar</span>
            </h1>
            <p class="text-slate-200 text-lg sm:text-xl max-w-3xl mx-auto font-light leading-relaxed mb-8">
                Program penyaluran dan lumbung bahan makanan pokok bagi keluarga jamaah yang kurang mampu, yatim, dhuafa, dan warga sekitar masjid.
            </p>
            <div class="flex justify-center gap-4">
                <button 
                    @click="openDonate()" 
                    class="btn bg-bakri-lime hover:bg-lime-500 text-bakri-navy btn-lg rounded-full font-black px-8 border-none shadow-lg shadow-bakri-navy/20 hover:-translate-y-0.5 transition-transform"
                >
                    <span>Salurkan Sembako</span>
                    <HeartIcon class="w-5 h-5 ml-1" />
                </button>
            </div>
        </div>
    </div>

    <div class="bg-slate-50 min-h-screen py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Dynamic Notification Alerts -->
            <div v-if="$page.props.flash && $page.props.flash.success" class="mb-10 p-5 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-start gap-4 shadow-sm animate-fade-in">
                <CheckCircleIcon class="w-6 h-6 text-emerald-600 shrink-0" />
                <div>
                    <h4 class="font-extrabold text-emerald-900 text-sm">Berhasil!</h4>
                    <p class="text-xs text-emerald-700 font-medium mt-0.5">{{ $page.props.flash.success }}</p>
                </div>
            </div>

            <!-- Dashboard Stats Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
                <div class="card bg-white border border-slate-100 shadow-xl shadow-slate-100 rounded-3xl p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform">
                    <div class="absolute right-0 bottom-0 translate-y-3 translate-x-3 text-slate-100 opacity-20 scale-150 group-hover:scale-175 transition-transform">
                        <GiftIcon class="w-24 h-24" />
                    </div>
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1 block">Total Program</span>
                    <span class="text-3xl font-black text-slate-800 tracking-tight">{{ stats.total_programs || 0 }}</span>
                    <div class="text-[11px] text-teal-600 font-bold mt-2">Aktif & Berjalan</div>
                </div>

                <div class="card bg-white border border-slate-100 shadow-xl shadow-slate-100 rounded-3xl p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform">
                    <div class="absolute right-0 bottom-0 translate-y-3 translate-x-3 text-slate-100 opacity-20 scale-150 group-hover:scale-175 transition-transform">
                        <HeartIcon class="w-24 h-24" />
                    </div>
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1 block">Total Donatur</span>
                    <span class="text-3xl font-black text-slate-800 tracking-tight">{{ stats.total_donors || 0 }}</span>
                    <div class="text-[11px] text-teal-600 font-bold mt-2">Telah Terverifikasi</div>
                </div>

                <div class="card bg-white border border-slate-100 shadow-xl shadow-slate-100 rounded-3xl p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform">
                    <div class="absolute right-0 bottom-0 translate-y-3 translate-x-3 text-slate-100 opacity-20 scale-150 group-hover:scale-175 transition-transform">
                        <InboxArrowDownIcon class="w-24 h-24" />
                    </div>
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1 block">Stok Sembako</span>
                    <span class="text-3xl font-black text-slate-800 tracking-tight">{{ stats.total_collected || 0 }}</span>
                    <div class="text-[11px] text-teal-600 font-bold mt-2">Paket Siap Distribusi</div>
                </div>

                <div class="card bg-white border border-slate-100 shadow-xl shadow-slate-100 rounded-3xl p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform">
                    <div class="absolute right-0 bottom-0 translate-y-3 translate-x-3 text-slate-100 opacity-20 scale-150 group-hover:scale-175 transition-transform">
                        <UsersIcon class="w-24 h-24" />
                    </div>
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1 block">Telah Tersalurkan</span>
                    <span class="text-3xl font-black text-slate-800 tracking-tight">{{ stats.total_distributed || 0 }}</span>
                    <div class="text-[11px] text-teal-600 font-bold mt-2">Penerima Manfaat</div>
                </div>
            </div>

            <!-- Section Title -->
            <div class="mb-12 text-center lg:text-left">
                <span class="badge bg-[#068d9e]/10 text-[#068d9e] border-none font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-3 text-xs">Program Sembako</span>
                <h2 class="text-3xl lg:text-4xl font-black text-slate-800 tracking-tight leading-none">Daftar Kebutuhan Sembako</h2>
                <div class="w-12 h-1 bg-bakri-lime mt-4 rounded-full mx-auto lg:mx-0"></div>
            </div>

            <!-- Active Programs Grid -->
            <div v-if="programs && programs.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div 
                    v-for="program in programs" 
                    :key="program.id" 
                    class="card bg-white border border-slate-100 rounded-[2rem] overflow-hidden hover:shadow-2xl shadow-xl shadow-slate-200/40 hover:-translate-y-1.5 transition-all duration-300 flex flex-col group"
                >
                    <!-- Program Image with Badge overlay -->
                    <div class="relative h-52 overflow-hidden bg-slate-200">
                        <img 
                            :src="program.image_url" 
                            :alt="program.title" 
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" 
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                        <div class="absolute top-4 right-4">
                            <span 
                                :class="[
                                    'badge border-none font-extrabold uppercase text-[9px] px-3 py-1.5 rounded-full tracking-wider',
                                    program.status === 'active' ? 'bg-bakri-lime text-bakri-navy' : 'bg-slate-500 text-white'
                                ]"
                            >
                                {{ program.status === 'active' ? 'Aktif' : 'Selesai' }}
                            </span>
                        </div>
                    </div>

                    <!-- Program Content -->
                    <div class="p-8 flex flex-col flex-1">
                        <h3 class="text-xl font-black text-slate-800 mb-3 line-clamp-2 uppercase tracking-tighter leading-snug">
                            {{ program.title }}
                        </h3>
                        <p class="text-slate-500 text-sm leading-relaxed mb-6 font-medium line-clamp-3">
                            {{ program.description }}
                        </p>

                        <!-- Progress Bar Section -->
                        <div class="mt-auto space-y-2 mb-6">
                            <div class="flex justify-between items-end text-xs font-bold uppercase tracking-wider text-slate-400">
                                <span>Terumpul: <strong class="text-slate-700">{{ program.collected_amount }}</strong> / {{ program.target_amount }} Paket</span>
                                <span class="text-[#068d9e]">{{ program.formatted_progress }}</span>
                            </div>
                            <div class="h-2.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div 
                                    class="h-full bg-gradient-to-r from-teal-500 to-[#068d9e] rounded-full group-hover:opacity-90 transition-all duration-500" 
                                    :style="`width: ${program.formatted_progress}`"
                                ></div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-3 mt-4" v-if="program.status === 'active'">
                            <button 
                                @click="openDonate(program)" 
                                class="btn bg-[#068d9e] hover:bg-teal-700 text-white rounded-2xl font-bold text-xs uppercase tracking-wider border-none hover:shadow-lg shadow-teal-500/10"
                            >
                                <HeartIcon class="w-4 h-4 mr-1 shrink-0" />
                                Donasi
                            </button>
                            <button 
                                @click="openRequest(program)" 
                                class="btn btn-outline border-slate-200 text-slate-700 hover:bg-slate-50 rounded-2xl font-bold text-xs uppercase tracking-wider"
                            >
                                <InboxArrowDownIcon class="w-4 h-4 mr-1 shrink-0" />
                                Butuh Bantuan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-20 bg-white rounded-[2rem] border border-dashed border-slate-300">
                <InboxArrowDownIcon class="w-16 h-16 text-slate-300 mx-auto mb-4" />
                <h3 class="text-xl font-extrabold text-slate-700">Belum Ada Program Aktif</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">Masjid sedang menyiapkan program pembagian kebutuhan pokok berikutnya. Terima kasih atas dukungan Anda.</p>
            </div>

            <!-- Info Section: Cara Berbagi & Penyaluran -->
            <div class="mt-24">
                <div class="text-center mb-16">
                    <span class="badge bg-bakri-lime/10 text-emerald-800 border-none font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-3 text-xs">Peta Operasional</span>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight leading-none">Bagaimana Lumbung Bekerja?</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="card bg-white p-8 rounded-3xl border border-slate-100 text-center relative hover:shadow-xl transition-shadow">
                        <div class="w-12 h-12 bg-bakri-lime/20 rounded-2xl flex items-center justify-center text-bakri-navy mx-auto mb-6 font-black text-lg">1</div>
                        <h3 class="text-lg font-black text-slate-800 mb-2 uppercase tracking-tight">Penerimaan & Donasi</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Jamaah mendonasikan uang atau bahan sembako (beras, minyak, dll.) secara langsung atau melalui notifikasi.</p>
                    </div>

                    <div class="card bg-white p-8 rounded-3xl border border-slate-100 text-center relative hover:shadow-xl transition-shadow">
                        <div class="w-12 h-12 bg-[#068d9e]/15 rounded-2xl flex items-center justify-center text-[#068d9e] mx-auto mb-6 font-black text-lg">2</div>
                        <h3 class="text-lg font-black text-slate-800 mb-2 uppercase tracking-tight">Verifikasi Kelayakan</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Pengurus masjid memverifikasi donasi masuk dan data jamaah pemohon bantuan agar tepat sasaran.</p>
                    </div>

                    <div class="card bg-white p-8 rounded-3xl border border-slate-100 text-center relative hover:shadow-xl transition-shadow">
                        <div class="w-12 h-12 bg-teal-800/10 rounded-2xl flex items-center justify-center text-teal-800 mx-auto mb-6 font-black text-lg">3</div>
                        <h3 class="text-lg font-black text-slate-800 mb-2 uppercase tracking-tight">Penyaluran Amanah</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Bantuan diserahkan secara terjadwal langsung kepada jamaah penerima manfaat dengan pencatatan digital penuh.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Donation Modal (Notify Donation) -->
    <TransitionRoot as="template" :show="showDonateModal">
        <Dialog as="div" class="relative z-[60]" @close="showDonateModal = false">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                            <div class="bg-white px-6 pt-6 pb-6">
                                <div class="flex justify-between items-center pb-4 border-b border-slate-100 mb-6">
                                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Formulir Donasi Sembako</h3>
                                    <button @click="showDonateModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                                        <XMarkIcon class="w-5 h-5" />
                                    </button>
                                </div>

                                <form @submit.prevent="submitDonation" class="space-y-4">
                                    <div style="display: none !important;" aria-hidden="true">
                                        <input type="text" name="website_hp" v-model="donateForm.website_hp" tabindex="-1" autocomplete="off" />
                                        <input type="hidden" name="hp_time" v-model="donateForm.hp_time" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Nama Lengkap Donatur</label>
                                        <input 
                                            v-model="donateForm.donor_name" 
                                            type="text" 
                                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-[#068d9e] focus:outline-none" 
                                            required 
                                        />
                                        <div v-if="donateForm.errors.donor_name" class="text-red-500 text-xs mt-1 font-bold">{{ donateForm.errors.donor_name }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Nomor WhatsApp</label>
                                        <input 
                                            v-model="donateForm.donor_phone" 
                                            type="text" 
                                            placeholder="Contoh: 081234567890"
                                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-[#068d9e] focus:outline-none" 
                                            required 
                                        />
                                        <div v-if="donateForm.errors.donor_phone" class="text-red-500 text-xs mt-1 font-bold">{{ donateForm.errors.donor_phone }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Jenis Donasi</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <button 
                                                type="button"
                                                @click="donateForm.donation_type = 'barang'"
                                                :class="[
                                                    'py-3 text-sm font-bold rounded-2xl border transition-all',
                                                    donateForm.donation_type === 'barang' ? 'bg-[#068d9e] border-[#068d9e] text-white' : 'bg-white border-slate-200 text-slate-700'
                                                ]"
                                            >
                                                📦 Barang (Sembako)
                                            </button>
                                            <button 
                                                type="button"
                                                @click="donateForm.donation_type = 'uang'"
                                                :class="[
                                                    'py-3 text-sm font-bold rounded-2xl border transition-all',
                                                    donateForm.donation_type === 'uang' ? 'bg-[#068d9e] border-[#068d9e] text-white' : 'bg-white border-slate-200 text-slate-700'
                                                ]"
                                            >
                                                💵 Uang (Nominal)
                                            </button>
                                        </div>
                                    </div>

                                    <!-- If money -->
                                    <div v-if="donateForm.donation_type === 'uang'">
                                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Nominal Transfer (Rp)</label>
                                        <input 
                                            v-model.number="donateForm.amount" 
                                            type="number" 
                                            placeholder="Masukkan nominal donasi"
                                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-[#068d9e] focus:outline-none" 
                                            required 
                                        />
                                        <div v-if="donateForm.errors.amount" class="text-red-500 text-xs mt-1 font-bold">{{ donateForm.errors.amount }}</div>
                                        
                                        <!-- QRIS / Bank Transfer Instructions -->
                                        <div class="mt-3 p-4 bg-slate-50 rounded-2xl border border-slate-100 text-xs space-y-2">
                                            <div class="font-bold text-slate-700">Instruksi Transfer:</div>
                                            <div class="whitespace-pre-line leading-relaxed text-slate-500">
                                                {{ $page.props.settings?.donation_bank_info || 'Bank Syariah Indonesia (BSI)\nNo. Rek: -\nA.n Masjid Al-Hidayah' }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- If goods -->
                                    <div v-if="donateForm.donation_type === 'barang'">
                                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Deskripsi Barang Sembako</label>
                                        <textarea 
                                            v-model="donateForm.items" 
                                            rows="3" 
                                            placeholder="Contoh: Beras 10kg, Minyak Goreng 2 Liter, Mie Instan 1 Dus"
                                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-[#068d9e] focus:outline-none" 
                                            required 
                                        ></textarea>
                                        <div v-if="donateForm.errors.items" class="text-red-500 text-xs mt-1 font-bold">{{ donateForm.errors.items }}</div>
                                    </div>

                                    <!-- Proof file upload -->
                                    <div>
                                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Foto Barang / Bukti Transfer</label>
                                        <input 
                                            type="file" 
                                            @change="handleFileChange"
                                            class="w-full px-4 py-2 border border-slate-200 rounded-2xl text-sm focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#068d9e]/10 file:text-[#068d9e] hover:file:bg-[#068d9e]/20" 
                                        />
                                        <div v-if="donateForm.errors.proof" class="text-red-500 text-xs mt-1 font-bold">{{ donateForm.errors.proof }}</div>
                                    </div>

                                    <div class="pt-4 flex gap-3">
                                        <button 
                                            type="button" 
                                            @click="showDonateModal = false" 
                                            class="btn btn-outline border-slate-200 text-slate-700 hover:bg-slate-50 w-1/2 rounded-2xl font-bold uppercase tracking-wider"
                                        >
                                            Batal
                                        </button>
                                        <button 
                                            type="submit" 
                                            :disabled="donateForm.processing"
                                            class="btn bg-[#068d9e] hover:bg-teal-700 text-white w-1/2 rounded-2xl font-bold uppercase tracking-wider border-none"
                                        >
                                            Kirim
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>

    <!-- Request Modal (Apply for Aid) -->
    <TransitionRoot as="template" :show="showRequestModal">
        <Dialog as="div" class="relative z-[60]" @close="showRequestModal = false">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                            <div class="bg-white px-6 pt-6 pb-6">
                                <div class="flex justify-between items-center pb-4 border-b border-slate-100 mb-6">
                                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Formulir Permohonan Bantuan</h3>
                                    <button @click="showRequestModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                                        <XMarkIcon class="w-5 h-5" />
                                    </button>
                                </div>

                                <div class="p-4 bg-amber-50 text-amber-900 border border-amber-200 rounded-2xl text-xs leading-relaxed font-semibold mb-6 flex gap-3">
                                    <span>⚠️</span>
                                    <span>Mohon masukkan informasi yang benar dan valid untuk mempermudah proses verifikasi kelayakan oleh pengurus masjid.</span>
                                </div>

                                <form @submit.prevent="submitRequest" class="space-y-4">
                                    <div style="display: none !important;" aria-hidden="true">
                                        <input type="text" name="website_hp" v-model="requestForm.website_hp" tabindex="-1" autocomplete="off" />
                                        <input type="hidden" name="hp_time" v-model="requestForm.hp_time" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Nama Lengkap Kepala Keluarga</label>
                                        <input 
                                            v-model="requestForm.name" 
                                            type="text" 
                                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-[#068d9e] focus:outline-none" 
                                            required 
                                        />
                                        <div v-if="requestForm.errors.name" class="text-red-500 text-xs mt-1 font-bold">{{ requestForm.errors.name }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Nomor WhatsApp / Telepon</label>
                                        <input 
                                            v-model="requestForm.phone" 
                                            type="text" 
                                            placeholder="Contoh: 081234567890"
                                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-[#068d9e] focus:outline-none" 
                                            required 
                                        />
                                        <div v-if="requestForm.errors.phone" class="text-red-500 text-xs mt-1 font-bold">{{ requestForm.errors.phone }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Jumlah Anggota Keluarga (Jiwa)</label>
                                        <input 
                                            v-model.number="requestForm.family_members" 
                                            type="number" 
                                            min="1"
                                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-[#068d9e] focus:outline-none" 
                                            required 
                                        />
                                        <div v-if="requestForm.errors.family_members" class="text-red-500 text-xs mt-1 font-bold">{{ requestForm.errors.family_members }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Alamat Tempat Tinggal</label>
                                        <textarea 
                                            v-model="requestForm.address" 
                                            rows="3" 
                                            placeholder="Alamat lengkap beserta RT/RW"
                                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-[#068d9e] focus:outline-none" 
                                            required 
                                        ></textarea>
                                        <div v-if="requestForm.errors.address" class="text-red-500 text-xs mt-1 font-bold">{{ requestForm.errors.address }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Alasan Mengajukan Bantuan</label>
                                        <textarea 
                                            v-model="requestForm.reason" 
                                            rows="3" 
                                            placeholder="Contoh: Pekerjaan tidak tetap, sedang mengalami kesulitan finansial pasca sakit..."
                                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-[#068d9e] focus:outline-none" 
                                            required 
                                        ></textarea>
                                        <div v-if="requestForm.errors.reason" class="text-red-500 text-xs mt-1 font-bold">{{ requestForm.errors.reason }}</div>
                                    </div>

                                    <div class="pt-4 flex gap-3">
                                        <button 
                                            type="button" 
                                            @click="showRequestModal = false" 
                                            class="btn btn-outline border-slate-200 text-slate-700 hover:bg-slate-50 w-1/2 rounded-2xl font-bold uppercase tracking-wider"
                                        >
                                            Batal
                                        </button>
                                        <button 
                                            type="submit" 
                                            :disabled="requestForm.processing"
                                            class="btn bg-[#068d9e] hover:bg-teal-700 text-white w-1/2 rounded-2xl font-bold uppercase tracking-wider border-none"
                                        >
                                            Ajukan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>

    <!-- Modal Sukses & WhatsApp Direct Link -->
    <div v-if="showSuccessModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 text-center relative overflow-hidden animate-in fade-in zoom-in duration-200">
            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <CheckCircleIcon class="w-10 h-10" />
            </div>

            <h3 class="text-2xl font-black text-slate-900 mb-2">
                {{ successType === 'donation' ? 'Donasi Berhasil Dikirim!' : 'Permohonan Berhasil Dikirim!' }}
            </h3>
            <p class="text-slate-600 text-sm mb-6">
                {{ successType === 'donation' 
                    ? 'Terima kasih atas kepedulian Anda. Konfirmasikan donasi Anda ke Pengurus Masjid melalui WhatsApp untuk proses verifikasi.' 
                    : 'Permohonan bantuan Anda telah dicatat. Kirimkan konfirmasi via WhatsApp untuk respon lebih cepat dari pengurus.' 
                }}
            </p>

            <!-- WhatsApp Quick Action Button -->
            <div class="space-y-3">
                <a
                    :href="getFoodBarnWALink"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-full py-4 px-6 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-2xl shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2 text-base"
                >
                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                    <span>Konfirmasi via WhatsApp</span>
                </a>

                <button
                    @click="showSuccessModal = false"
                    class="w-full py-3 px-6 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-2xl transition-all text-sm"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Login Required Modal -->
    <LoginRequiredModal
        :show="showLoginModal"
        :targetUrl="loginTargetUrl"
        @close="showLoginModal = false"
    />
</template>
