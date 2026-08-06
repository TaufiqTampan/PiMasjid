<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import {
    BuildingOfficeIcon,
    CalendarDaysIcon,
    UserGroupIcon,
    ClockIcon,
    CheckCircleIcon,
    XMarkIcon,
    MagnifyingGlassIcon,
    InformationCircleIcon,
    TruckIcon,
    WrenchScrewdriverIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    facilities: {
        type: Array,
        default: () => [],
    },
});

// Category filter
const selectedCategory = ref('all');
const filteredFacilities = computed(() => {
    if (selectedCategory.value === 'all') return props.facilities;
    return props.facilities.filter(f => f.facility_type === selectedCategory.value);
});

// Modal Booking
const showBookingModal = ref(false);
const selectedFacility = ref(null);

const bookingForm = useForm({
    facility_id: null,
    borrower_name: '',
    borrower_phone: '',
    borrower_address: '',
    event_name: '',
    event_description: '',
    start_time: '',
    end_time: '',
});

const openBookingModal = (facility) => {
    selectedFacility.value = facility;
    bookingForm.reset();
    bookingForm.facility_id = facility.id;
    showBookingModal.value = true;
};

const closeBookingModal = () => {
    showBookingModal.value = false;
    selectedFacility.value = null;
    bookingForm.reset();
};

const submitBooking = () => {
    bookingForm.post(route('public.facilities.book'), {
        onSuccess: () => {
            closeBookingModal();
        },
    });
};

// Modal Cek Status
const showStatusModal = ref(false);
const searchCode = ref('');
const statusLoading = ref(false);
const statusResult = ref(null);
const statusError = ref('');

const checkStatus = async () => {
    if (!searchCode.value.trim()) return;
    
    statusLoading.value = true;
    statusError.value = '';
    statusResult.value = null;

    try {
        const response = await fetch(`/fasilitas/cek-status?code=${encodeURIComponent(searchCode.value.trim())}`);
        const data = await response.json();
        
        if (!response.ok) {
            statusError.value = data.error || 'Kode booking tidak ditemukan.';
        } else {
            statusResult.value = data;
        }
    } catch (err) {
        statusError.value = 'Gagal memeriksa status. Silakan coba lagi.';
    } finally {
        statusLoading.value = false;
    }
};

const getFacilityIcon = (type) => {
    switch (type) {
        case 'room': return BuildingOfficeIcon;
        case 'vehicle': return TruckIcon;
        case 'equipment': return WrenchScrewdriverIcon;
        default: return SparklesIcon;
    }
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'approved':
            return { label: 'Disetujui', class: 'bg-emerald-100 text-emerald-800 border-emerald-300' };
        case 'rejected':
            return { label: 'Ditolak', class: 'bg-red-100 text-red-800 border-red-300' };
        default:
            return { label: 'Menunggu Persetujuan', class: 'bg-amber-100 text-amber-800 border-amber-300' };
    }
};
</script>

<template>
    <Head title="Fasilitas & Peminjaman Masjid" />

    <PublicLayout>
        <!-- Hero Section -->
        <div class="relative bg-gradient-to-br from-emerald-800 via-teal-900 to-slate-900 pt-32 pb-20 lg:pt-40 lg:pb-28 overflow-hidden text-white">
            <div class="absolute inset-0 opacity-10 bg-pattern-islamic bg-repeat"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md text-emerald-300 text-xs font-bold uppercase tracking-widest mb-4 border border-white/20">
                    <SparklesIcon class="w-4 h-4" /> Layanan Fasilitas Umat
                </span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black mb-6 tracking-tight leading-tight">
                    Peminjaman & Booking Fasilitas Masjid
                </h1>
                <p class="text-white/80 text-lg max-w-3xl mx-auto mb-10 leading-relaxed font-light">
                    Masjid menyediakan sarana aula, peralatan, dan kendaraan operasional untuk keperluan kegiatan ibadah, sosial, kemasyarakatan, maupun pernikahan jamaah.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center justify-center gap-4">
                    <a
                        href="#daftar-fasilitas"
                        class="px-8 py-4 bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold rounded-2xl shadow-2xl shadow-emerald-500/30 transition-all hover:-translate-y-0.5 flex items-center gap-2 text-base"
                    >
                        <BuildingOfficeIcon class="w-5 h-5" />
                        Lihat Katalog Fasilitas
                    </a>
                    <button
                        @click="showStatusModal = true"
                        class="px-8 py-4 bg-white/10 hover:bg-white/20 backdrop-blur-md text-white font-bold rounded-2xl border border-white/30 transition-all flex items-center gap-2 text-base"
                    >
                        <MagnifyingGlassIcon class="w-5 h-5" />
                        Cek Status Booking
                    </button>
                </div>
            </div>
        </div>

        <!-- Notification Toast -->
        <div v-if="$page.props.flash?.success" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
            <div class="p-6 bg-emerald-50 border-2 border-emerald-300 rounded-3xl flex items-start gap-4 shadow-lg">
                <CheckCircleIcon class="w-8 h-8 text-emerald-600 shrink-0 mt-0.5" />
                <div>
                    <h3 class="font-extrabold text-emerald-900 text-base">Berhasil Dikirim!</h3>
                    <p class="text-emerald-800 text-sm mt-1 leading-relaxed">{{ $page.props.flash.success }}</p>
                </div>
            </div>
        </div>

        <!-- Main Content Section -->
        <div id="daftar-fasilitas" class="bg-slate-50 min-h-screen py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Filter Tabs -->
                <div class="flex flex-wrap justify-center gap-3 mb-12">
                    <button
                        @click="selectedCategory = 'all'"
                        :class="['px-6 py-3 rounded-2xl text-sm font-extrabold transition-all shadow-sm', selectedCategory === 'all' ? 'bg-emerald-600 text-white shadow-emerald-600/20' : 'bg-white text-slate-600 hover:bg-slate-100']"
                    >
                        Semua Fasilitas ({{ facilities.length }})
                    </button>
                    <button
                        @click="selectedCategory = 'room'"
                        :class="['px-6 py-3 rounded-2xl text-sm font-extrabold transition-all shadow-sm flex items-center gap-2', selectedCategory === 'room' ? 'bg-emerald-600 text-white shadow-emerald-600/20' : 'bg-white text-slate-600 hover:bg-slate-100']"
                    >
                        <BuildingOfficeIcon class="w-4 h-4" /> Ruangan / Aula
                    </button>
                    <button
                        @click="selectedCategory = 'vehicle'"
                        :class="['px-6 py-3 rounded-2xl text-sm font-extrabold transition-all shadow-sm flex items-center gap-2', selectedCategory === 'vehicle' ? 'bg-emerald-600 text-white shadow-emerald-600/20' : 'bg-white text-slate-600 hover:bg-slate-100']"
                    >
                        <TruckIcon class="w-4 h-4" /> Kendaraan
                    </button>
                    <button
                        @click="selectedCategory = 'equipment'"
                        :class="['px-6 py-3 rounded-2xl text-sm font-extrabold transition-all shadow-sm flex items-center gap-2', selectedCategory === 'equipment' ? 'bg-emerald-600 text-white shadow-emerald-600/20' : 'bg-white text-slate-600 hover:bg-slate-100']"
                    >
                        <WrenchScrewdriverIcon class="w-4 h-4" /> Peralatan & Tenda
                    </button>
                </div>

                <!-- Empty State -->
                <div v-if="filteredFacilities.length === 0" class="text-center py-20 bg-white rounded-3xl p-8 border border-slate-200 shadow-sm max-w-xl mx-auto">
                    <BuildingOfficeIcon class="w-20 h-20 text-slate-300 mx-auto mb-4" />
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Belum ada fasilitas</h3>
                    <p class="text-slate-500 text-sm">Fasilitas pada kategori ini belum didaftarkan oleh pengurus masjid.</p>
                </div>

                <!-- Facility Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div
                        v-for="facility in filteredFacilities"
                        :key="facility.id"
                        class="bg-white rounded-3xl border border-slate-200/80 shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden flex flex-col group hover:-translate-y-1"
                    >
                        <!-- Card Header / Image -->
                        <div class="h-52 bg-gradient-to-br from-emerald-100 to-teal-50 relative overflow-hidden">
                            <img
                                v-if="facility.image_url"
                                :src="facility.image_url"
                                :alt="facility.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <component :is="getFacilityIcon(facility.facility_type)" class="w-24 h-24 text-emerald-400/50" />
                            </div>
                            
                            <!-- Type Badge -->
                            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-md px-3 py-1 rounded-full text-xs font-black text-emerald-700 shadow-sm uppercase tracking-wider">
                                {{ facility.facility_type_label }}
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-2xl font-black text-slate-900 mb-3 group-hover:text-emerald-600 transition-colors">
                                    {{ facility.name }}
                                </h3>

                                <div v-if="facility.capacity" class="flex items-center gap-2 text-xs font-bold text-slate-500 mb-4 bg-slate-100 rounded-xl px-3 py-2 w-fit">
                                    <UserGroupIcon class="w-4 h-4 text-emerald-600" />
                                    Kapasitas: {{ facility.capacity }} orang / unit
                                </div>

                                <p class="text-slate-600 text-sm leading-relaxed mb-4 line-clamp-3">
                                    {{ facility.description || 'Fasilitas umum masjid yang dapat dipinjam oleh jamaah.' }}
                                </p>

                                <div v-if="facility.terms" class="text-xs text-slate-500 bg-emerald-50/70 border border-emerald-100 rounded-2xl p-3 mb-6">
                                    <span class="font-extrabold text-emerald-800 block mb-1">Ketentuan & Syarat:</span>
                                    {{ facility.terms }}
                                </div>
                            </div>

                            <button
                                @click="openBookingModal(facility)"
                                class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-2xl shadow-lg shadow-emerald-600/20 transition-all flex items-center justify-center gap-2 text-sm"
                            >
                                <CalendarDaysIcon class="w-5 h-5" />
                                Ajukan Peminjaman
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL BOOKING -->
        <div v-if="showBookingModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl overflow-hidden animate-fade-in relative my-8">
                <!-- Modal Header -->
                <div class="bg-emerald-600 p-6 text-white flex justify-between items-center">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-200">Form Peminjaman</span>
                        <h3 class="text-2xl font-black">{{ selectedFacility?.name }}</h3>
                    </div>
                    <button @click="closeBookingModal" class="p-2 rounded-full hover:bg-white/10 text-white">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <!-- Modal Body Form -->
                <form @submit.prevent="submitBooking" class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Peminjam / Lembaga *</label>
                            <input
                                v-model="bookingForm.borrower_name"
                                type="text"
                                required
                                placeholder="Contoh: Ahmad Subagyo / DKM Masjid Al-Hikmah"
                                class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium"
                            />
                            <span v-if="bookingForm.errors.borrower_name" class="text-xs text-red-600 mt-1 block">{{ bookingForm.errors.borrower_name }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">No. WhatsApp / HP *</label>
                            <input
                                v-model="bookingForm.borrower_phone"
                                type="text"
                                required
                                placeholder="081234567890"
                                class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium"
                            />
                            <span v-if="bookingForm.errors.borrower_phone" class="text-xs text-red-600 mt-1 block">{{ bookingForm.errors.borrower_phone }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Alamat / Instansi Pemohon</label>
                        <input
                            v-model="bookingForm.borrower_address"
                            type="text"
                            placeholder="Alamat domisili atau instansi pemohon"
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Acara / Kegiatan *</label>
                        <input
                            v-model="bookingForm.event_name"
                            type="text"
                            required
                            placeholder="Contoh: Acara Akad Nikah / Pengajian Rutin RT"
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium"
                        />
                        <span v-if="bookingForm.errors.event_name" class="text-xs text-red-600 mt-1 block">{{ bookingForm.errors.event_name }}</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Waktu Mulai *</label>
                            <input
                                v-model="bookingForm.start_time"
                                type="datetime-local"
                                required
                                class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium"
                            />
                            <span v-if="bookingForm.errors.start_time" class="text-xs text-red-600 mt-1 block">{{ bookingForm.errors.start_time }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Waktu Selesai *</label>
                            <input
                                v-model="bookingForm.end_time"
                                type="datetime-local"
                                required
                                class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium"
                            />
                            <span v-if="bookingForm.errors.end_time" class="text-xs text-red-600 mt-1 block">{{ bookingForm.errors.end_time }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Keterangan / Catatan Tambahan</label>
                        <textarea
                            v-model="bookingForm.event_description"
                            rows="3"
                            placeholder="Catatan kebutuhan khusus (perlengkapan tambahan, dll)"
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium"
                        ></textarea>
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                        <button
                            type="button"
                            @click="closeBookingModal"
                            class="px-6 py-3 rounded-2xl border border-slate-300 text-slate-700 font-bold text-sm hover:bg-slate-100"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="bookingForm.processing"
                            class="px-8 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm shadow-lg shadow-emerald-600/20 disabled:opacity-50"
                        >
                            {{ bookingForm.processing ? 'Mengirim...' : 'Kirim Pengajuan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL CEK STATUS -->
        <div v-if="showStatusModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden animate-fade-in relative">
                <div class="bg-slate-800 p-6 text-white flex justify-between items-center">
                    <h3 class="text-xl font-black">Cek Status Booking Fasilitas</h3>
                    <button @click="showStatusModal = false" class="p-2 rounded-full hover:bg-white/10 text-white">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <div class="p-6 space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Kode Booking</label>
                        <div class="flex gap-2">
                            <input
                                v-model="searchCode"
                                type="text"
                                placeholder="Contoh: BOOK-20260729-A89F"
                                class="flex-1 px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 font-mono text-sm uppercase font-bold"
                            />
                            <button
                                @click="checkStatus"
                                :disabled="statusLoading"
                                class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl"
                            >
                                {{ statusLoading ? 'Cari...' : 'Cari' }}
                            </button>
                        </div>
                    </div>

                    <!-- Error Alert -->
                    <div v-if="statusError" class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm font-semibold rounded-2xl">
                        {{ statusError }}
                    </div>

                    <!-- Result Card -->
                    <div v-if="statusResult" class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-4">
                        <div class="flex justify-between items-center border-b border-slate-200 pb-3">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kode Booking</span>
                            <span class="font-mono font-black text-slate-900 text-base">{{ statusResult.booking_code }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500">Fasilitas:</span>
                            <span class="font-bold text-slate-900 text-sm">{{ statusResult.facility_name }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500">Peminjam:</span>
                            <span class="font-semibold text-slate-800 text-sm">{{ statusResult.borrower_name }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500">Nama Acara:</span>
                            <span class="font-semibold text-slate-800 text-sm">{{ statusResult.event_name }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500">Waktu:</span>
                            <span class="font-semibold text-slate-800 text-xs">{{ statusResult.start_time }} s/d {{ statusResult.end_time }}</span>
                        </div>

                        <div class="pt-2 flex justify-between items-center border-t border-slate-200">
                            <span class="text-xs font-bold text-slate-500">Status Persetujuan:</span>
                            <span :class="['px-3 py-1 rounded-full text-xs font-black border', getStatusBadge(statusResult.status).class]">
                                {{ getStatusBadge(statusResult.status).label }}
                            </span>
                        </div>

                        <div v-if="statusResult.admin_notes" class="p-3 bg-white rounded-xl border border-slate-200 text-xs text-slate-700">
                            <span class="font-bold block text-slate-900 mb-1">Catatan Admin:</span>
                            {{ statusResult.admin_notes }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
