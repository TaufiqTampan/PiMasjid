<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { getWhatsAppUrl, buildAdminBookingStatusWAMessage } from '@/Utils/whatsapp';
import {
    BuildingOfficeIcon,
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    CheckIcon,
    XMarkIcon,
    EyeIcon,
    CalendarDaysIcon,
    UserGroupIcon,
    ClockIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';

const props = defineProps({
    facilities: { type: Array, default: () => [] },
    bookings: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

// Tabs
const activeTab = ref('facilities');

// Facility form
const showFacilityModal = ref(false);
const isEditingFacility = ref(false);
const editingFacilityId = ref(null);

const facilityForm = useForm({
    name: '',
    facility_type: 'room',
    capacity: '',
    description: '',
    terms: '',
    image: null,
    is_active: true,
});

const openAddFacility = () => {
    isEditingFacility.value = false;
    editingFacilityId.value = null;
    facilityForm.reset();
    facilityForm.is_active = true;
    facilityForm.facility_type = 'room';
    showFacilityModal.value = true;
};

const openEditFacility = (facility) => {
    isEditingFacility.value = true;
    editingFacilityId.value = facility.id;
    facilityForm.name = facility.name;
    facilityForm.facility_type = facility.facility_type;
    facilityForm.capacity = facility.capacity || '';
    facilityForm.description = facility.description || '';
    facilityForm.terms = facility.terms || '';
    facilityForm.is_active = facility.is_active;
    facilityForm.image = null;
    showFacilityModal.value = true;
};

const submitFacility = () => {
    const url = isEditingFacility.value
        ? route('facilities.update', editingFacilityId.value)
        : route('facilities.store');
    facilityForm.post(url, {
        forceFormData: true,
        onSuccess: () => {
            showFacilityModal.value = false;
            facilityForm.reset();
        }
    });
};

const deleteFacility = (id) => {
    if (confirm('Hapus fasilitas ini? Pastikan tidak ada booking aktif.')) {
        useForm({}).delete(route('facilities.destroy', id));
    }
};

// Booking actions
const showBookingModal = ref(false);
const selectedBooking = ref(null);
const bookingActionForm = useForm({
    status: '',
    admin_notes: '',
});

const openBookingAction = (booking, action) => {
    selectedBooking.value = booking;
    bookingActionForm.status = action;
    bookingActionForm.admin_notes = booking.admin_notes || '';
    showBookingModal.value = true;
};

const submitBookingAction = () => {
    bookingActionForm.patch(route('facilities.bookings.status', selectedBooking.value.id), {
        onSuccess: () => {
            showBookingModal.value = false;
        }
    });
};

const deleteBooking = (id) => {
    if (confirm('Hapus data booking ini?')) {
        useForm({}).delete(route('facilities.bookings.destroy', id));
    }
};

// Status badge variant
const statusVariant = (status) => {
    const map = {
        pending: 'warning',
        approved: 'success',
        rejected: 'danger',
        completed: 'neutral',
        cancelled: 'neutral',
    };
    return map[status] || 'neutral';
};

// Booking status filter
const filterStatus = ref(props.filters.status || '');

const applyFilter = () => {
    router.get(route('facilities.index'), { status: filterStatus.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Fasilitas Masjid" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-black text-2xl leading-tight text-slate-800 dark:text-white uppercase tracking-tight">
                    🏛️ Kelola Fasilitas Masjid
                </h2>
                <button
                    @click="openAddFacility"
                    class="btn bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-bold text-xs uppercase tracking-wider border-none shadow-lg shadow-emerald-600/10 flex items-center gap-1.5"
                >
                    <PlusIcon class="w-4 h-4" />
                    Tambah Fasilitas
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <!-- Alert -->
                <div v-if="$page.props.flash?.success" class="p-5 bg-emerald-50 border border-emerald-200 rounded-3xl flex items-start gap-4 shadow-sm">
                    <CheckCircleIcon class="w-6 h-6 text-emerald-600 shrink-0" />
                    <div>
                        <h4 class="font-extrabold text-emerald-900 text-sm">Operasi Sukses</h4>
                        <p class="text-xs text-emerald-700 font-medium mt-0.5">{{ $page.props.flash.success }}</p>
                    </div>
                </div>
                <div v-if="$page.props.flash?.error" class="p-5 bg-red-50 border border-red-200 rounded-3xl flex items-start gap-4 shadow-sm">
                    <ExclamationCircleIcon class="w-6 h-6 text-red-600 shrink-0" />
                    <div>
                        <h4 class="font-extrabold text-red-900 text-sm">Perhatian</h4>
                        <p class="text-xs text-red-700 font-medium mt-0.5">{{ $page.props.flash.error }}</p>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="card bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xl rounded-3xl p-6 relative overflow-hidden group">
                        <div class="absolute right-0 bottom-0 translate-y-3 translate-x-3 text-slate-100 dark:text-slate-700 opacity-20 scale-150">
                            <BuildingOfficeIcon class="w-24 h-24" />
                        </div>
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1 block">Total Fasilitas</span>
                        <span class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">{{ stats?.total_facilities || 0 }}</span>
                        <div class="text-[11px] text-emerald-600 font-bold mt-2">{{ stats?.active_facilities || 0 }} Aktif</div>
                    </div>
                    <div class="card bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xl rounded-3xl p-6 relative overflow-hidden group">
                        <div class="absolute right-0 bottom-0 translate-y-3 translate-x-3 text-slate-100 dark:text-slate-700 opacity-20 scale-150">
                            <ClockIcon class="w-24 h-24" />
                        </div>
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1 block">Booking Pending</span>
                        <span class="text-3xl font-black text-amber-500 tracking-tight">{{ stats?.pending_bookings || 0 }}</span>
                        <div class="text-[11px] text-amber-600 font-bold mt-2">Perlu Diproses</div>
                    </div>
                    <div class="card bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xl rounded-3xl p-6 relative overflow-hidden group">
                        <div class="absolute right-0 bottom-0 translate-y-3 translate-x-3 text-slate-100 dark:text-slate-700 opacity-20 scale-150">
                            <CalendarDaysIcon class="w-24 h-24" />
                        </div>
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1 block">Booking Aktif</span>
                        <span class="text-3xl font-black text-emerald-600 tracking-tight">{{ stats?.approved_bookings || 0 }}</span>
                        <div class="text-[11px] text-emerald-600 font-bold mt-2">Disetujui</div>
                    </div>
                    <div class="card bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xl rounded-3xl p-6 relative overflow-hidden group">
                        <div class="absolute right-0 bottom-0 translate-y-3 translate-x-3 text-slate-100 dark:text-slate-700 opacity-20 scale-150">
                            <UserGroupIcon class="w-24 h-24" />
                        </div>
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1 block">Total Peminjaman</span>
                        <span class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">{{ (bookings?.total) || 0 }}</span>
                        <div class="text-[11px] text-slate-500 font-bold mt-2">Semua Waktu</div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex gap-4 border-b border-slate-200 dark:border-slate-700 pb-3">
                    <button
                        @click="activeTab = 'facilities'"
                        :class="['px-6 py-2.5 rounded-full text-sm font-extrabold uppercase tracking-wide transition-all', activeTab === 'facilities' ? 'bg-slate-800 text-white dark:bg-slate-700' : 'bg-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800']"
                    >
                        Daftar Fasilitas ({{ facilities?.length || 0 }})
                    </button>
                    <button
                        @click="activeTab = 'bookings'"
                        :class="['px-6 py-2.5 rounded-full text-sm font-extrabold uppercase tracking-wide transition-all flex items-center gap-1.5', activeTab === 'bookings' ? 'bg-slate-800 text-white dark:bg-slate-700' : 'bg-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800']"
                    >
                        Riwayat Booking ({{ bookings?.total || 0 }})
                        <span v-if="stats?.pending_bookings > 0" class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    </button>
                </div>

                <!-- Facilities Tab -->
                <div v-if="activeTab === 'facilities'" class="space-y-6">
                    <div v-if="!facilities || facilities.length === 0" class="text-center py-16 text-slate-400">
                        <BuildingOfficeIcon class="w-16 h-16 mx-auto mb-4 opacity-30" />
                        <p class="text-lg font-semibold">Belum ada fasilitas</p>
                        <p class="text-sm">Klik "Tambah Fasilitas" untuk memulai.</p>
                    </div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div
                            v-for="facility in facilities"
                            :key="facility.id"
                            class="card bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-lg rounded-3xl overflow-hidden group hover:-translate-y-1 transition-transform"
                        >
                            <!-- Image -->
                            <div class="h-48 bg-gradient-to-br from-emerald-50 to-teal-100 dark:from-slate-700 dark:to-slate-600 overflow-hidden relative">
                                <img v-if="facility.image_url" :src="facility.image_url" :alt="facility.name" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <BuildingOfficeIcon class="w-20 h-20 text-emerald-300 dark:text-slate-500" />
                                </div>
                                <div class="absolute top-3 right-3 flex gap-2">
                                    <Badge :variant="facility.is_active ? 'success' : 'neutral'">
                                        {{ facility.is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </Badge>
                                </div>
                            </div>
                            <!-- Content -->
                            <div class="p-5">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <h3 class="font-black text-slate-900 dark:text-white text-lg leading-tight">{{ facility.name }}</h3>
                                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">{{ facility.facility_type_label }}</span>
                                    </div>
                                    <span v-if="facility.capacity" class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full flex items-center gap-1">
                                        <UserGroupIcon class="w-3 h-3" />
                                        {{ facility.capacity }}
                                    </span>
                                </div>
                                <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2 mb-4">{{ facility.description || 'Tidak ada deskripsi.' }}</p>
                                <div class="flex items-center justify-between">
                                    <div class="text-xs text-slate-400">
                                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ facility.pending_bookings_count }}</span> pending |
                                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ facility.bookings_count }}</span> total booking
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="openEditFacility(facility)" class="btn btn-square btn-ghost btn-sm text-slate-400 hover:text-slate-600">
                                            <PencilSquareIcon class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteFacility(facility.id)" class="btn btn-square btn-ghost btn-sm text-red-400 hover:text-red-600">
                                            <TrashIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bookings Tab -->
                <div v-if="activeTab === 'bookings'" class="space-y-4">
                    <!-- Filter Row -->
                    <div class="flex items-center gap-3 flex-wrap">
                        <select v-model="filterStatus" @change="applyFilter" class="select select-sm border border-slate-200 rounded-xl text-sm">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                        <button @click="applyFilter" class="btn btn-sm bg-slate-800 text-white border-none rounded-xl flex items-center gap-1.5">
                            <ArrowPathIcon class="w-4 h-4" />
                            Filter
                        </button>
                    </div>

                    <Card padding="none">
                        <div class="overflow-x-auto">
                            <table class="table-default w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                                        <th class="px-5 py-4">Kode Booking</th>
                                        <th class="px-5 py-4">Peminjam</th>
                                        <th class="px-5 py-4">Fasilitas</th>
                                        <th class="px-5 py-4">Acara</th>
                                        <th class="px-5 py-4">Waktu</th>
                                        <th class="px-5 py-4">Status</th>
                                        <th class="px-5 py-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!bookings?.data || bookings.data.length === 0">
                                        <td colspan="7" class="text-center py-10 text-slate-400">Belum ada booking.</td>
                                    </tr>
                                    <tr
                                        v-for="booking in (bookings?.data || [])"
                                        :key="booking.id"
                                        class="border-b border-slate-100 dark:border-slate-800 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50/50 dark:hover:bg-slate-800/20"
                                    >
                                        <td class="px-5 py-4">
                                            <span class="font-mono text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg">{{ booking.booking_code }}</span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="font-extrabold text-slate-900 dark:text-white">{{ booking.borrower_name }}</div>
                                            <div class="text-xs text-slate-400">{{ booking.borrower_phone }}</div>
                                        </td>
                                        <td class="px-5 py-4 text-xs">{{ booking.facility_name }}</td>
                                        <td class="px-5 py-4">
                                            <div class="font-semibold">{{ booking.event_name }}</div>
                                            <div class="text-xs text-slate-400 line-clamp-1">{{ booking.event_description }}</div>
                                        </td>
                                        <td class="px-5 py-4 text-xs">
                                            <div>{{ booking.start_time }}</div>
                                            <div class="text-slate-400">s/d {{ booking.end_time }}</div>
                                            <div class="text-emerald-600 font-bold">{{ booking.duration }}</div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <Badge :variant="statusVariant(booking.status)">{{ booking.status_label }}</Badge>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <div class="flex justify-end gap-1.5 flex-wrap">
                                                <template v-if="booking.status === 'pending'">
                                                    <button @click="openBookingAction(booking, 'approved')" class="btn btn-xs bg-emerald-100 text-emerald-700 border-none" title="Setujui">
                                                        <CheckIcon class="w-3 h-3" />
                                                        Setujui
                                                    </button>
                                                    <button @click="openBookingAction(booking, 'rejected')" class="btn btn-xs bg-rose-100 text-rose-700 border-none" title="Tolak">
                                                        <XMarkIcon class="w-3 h-3" />
                                                        Tolak
                                                    </button>
                                                </template>
                                                <template v-else-if="booking.status === 'approved'">
                                                    <button @click="openBookingAction(booking, 'completed')" class="btn btn-xs bg-blue-100 text-blue-700 border-none">
                                                        Selesai
                                                    </button>
                                                </template>
                                                <a
                                                    v-if="booking.borrower_phone"
                                                    :href="getWhatsAppUrl(booking.borrower_phone, buildAdminBookingStatusWAMessage({ borrowerName: booking.borrower_name, bookingCode: booking.booking_code, facilityName: booking.facility_name, eventName: booking.event_name, startTime: booking.start_time, status: booking.status, adminNotes: booking.admin_notes }))"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="btn btn-xs bg-emerald-600 hover:bg-emerald-700 text-white border-none flex items-center gap-1 font-bold"
                                                    title="Kirim Status WA ke Pemohon"
                                                >
                                                    <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24">
                                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                                    </svg>
                                                    WA
                                                </a>
                                                <button @click="deleteBooking(booking.id)" class="btn btn-square btn-xs bg-red-50 text-red-500 border-none hover:bg-red-100" title="Hapus">
                                                    <TrashIcon class="w-3 h-3" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </Card>
                </div>

            </div>
        </div>

        <!-- Add/Edit Facility Modal -->
        <TransitionRoot as="template" :show="showFacilityModal">
            <Dialog as="div" class="relative z-[60]" @close="showFacilityModal = false">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm" />
                </TransitionChild>
                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="ease-in duration-200" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="w-full max-w-xl transform overflow-hidden rounded-3xl bg-white p-6 shadow-2xl transition-all">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-lg font-black text-slate-800 uppercase">
                                        {{ isEditingFacility ? 'Edit Fasilitas' : 'Tambah Fasilitas' }}
                                    </h3>
                                    <button @click="showFacilityModal = false" class="btn btn-ghost btn-sm btn-square text-slate-400">
                                        <XMarkIcon class="w-5 h-5" />
                                    </button>
                                </div>
                                <form @submit.prevent="submitFacility" class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nama Fasilitas *</label>
                                            <input v-model="facilityForm.name" type="text" placeholder="contoh: Aula Utama" class="w-full px-4 py-3 border rounded-2xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required />
                                            <p v-if="facilityForm.errors.name" class="text-red-500 text-xs mt-1">{{ facilityForm.errors.name }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Jenis *</label>
                                            <select v-model="facilityForm.facility_type" class="w-full px-4 py-3 border rounded-2xl text-sm">
                                                <option value="room">Ruangan</option>
                                                <option value="equipment">Peralatan</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Kapasitas (orang)</label>
                                            <input v-model.number="facilityForm.capacity" type="number" min="1" placeholder="contoh: 200" class="w-full px-4 py-3 border rounded-2xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent" />
                                        </div>
                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Deskripsi</label>
                                            <textarea v-model="facilityForm.description" rows="2" placeholder="Deskripsi singkat fasilitas..." class="w-full px-4 py-3 border rounded-2xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"></textarea>
                                        </div>
                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Syarat & Ketentuan Penggunaan</label>
                                            <textarea v-model="facilityForm.terms" rows="3" placeholder="Syarat peminjaman, aturan penggunaan..." class="w-full px-4 py-3 border rounded-2xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"></textarea>
                                        </div>
                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Foto Fasilitas</label>
                                            <input type="file" @change="e => facilityForm.image = e.target.files[0]" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" />
                                        </div>
                                        <div class="col-span-2 flex items-center gap-3">
                                            <input type="checkbox" id="is_active" v-model="facilityForm.is_active" class="checkbox checkbox-sm checked:border-emerald-600 checked:bg-emerald-600" />
                                            <label for="is_active" class="text-sm font-semibold text-slate-700">Fasilitas aktif (dapat dibooking)</label>
                                        </div>
                                    </div>
                                    <div class="flex gap-3 pt-4">
                                        <button type="button" @click="showFacilityModal = false" class="btn btn-outline w-1/2 rounded-2xl">Batal</button>
                                        <button type="submit" :disabled="facilityForm.processing" class="btn bg-slate-800 text-white w-1/2 rounded-2xl">
                                            {{ facilityForm.processing ? 'Menyimpan...' : 'Simpan' }}
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Booking Action Modal -->
        <TransitionRoot as="template" :show="showBookingModal">
            <Dialog as="div" class="relative z-[60]" @close="showBookingModal = false">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm" />
                </TransitionChild>
                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="ease-in duration-200" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-3xl bg-white p-6 shadow-2xl transition-all">
                                <h3 class="text-lg font-black text-slate-800 uppercase mb-2">
                                    {{ bookingActionForm.status === 'approved' ? '✅ Setujui Booking' : bookingActionForm.status === 'rejected' ? '❌ Tolak Booking' : '🏁 Tandai Selesai' }}
                                </h3>
                                <div v-if="selectedBooking" class="bg-slate-50 rounded-2xl p-4 mb-4 text-sm space-y-1">
                                    <div class="font-bold text-slate-900">{{ selectedBooking.borrower_name }}</div>
                                    <div class="text-slate-500">{{ selectedBooking.event_name }} — {{ selectedBooking.facility_name }}</div>
                                    <div class="text-xs text-slate-400">{{ selectedBooking.start_time }} s/d {{ selectedBooking.end_time }}</div>
                                </div>
                                <form @submit.prevent="submitBookingAction" class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Catatan Admin (opsional)</label>
                                        <textarea v-model="bookingActionForm.admin_notes" rows="3" :placeholder="bookingActionForm.status === 'rejected' ? 'Alasan penolakan...' : 'Catatan untuk peminjam...'" class="w-full px-4 py-3 border rounded-2xl text-sm resize-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"></textarea>
                                    </div>
                                    <div class="flex gap-3">
                                        <button type="button" @click="showBookingModal = false" class="btn btn-outline w-1/2 rounded-2xl">Batal</button>
                                        <button type="submit" :disabled="bookingActionForm.processing" :class="['btn w-1/2 rounded-2xl text-white border-none', bookingActionForm.status === 'approved' || bookingActionForm.status === 'completed' ? 'bg-emerald-600' : 'bg-rose-600']">
                                            Konfirmasi
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

    </AuthenticatedLayout>
</template>
