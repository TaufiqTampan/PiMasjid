<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { getWhatsAppUrl } from '@/Utils/whatsapp';
import { 
    HeartIcon, 
    GiftIcon, 
    UsersIcon, 
    SparklesIcon, 
    PlusIcon, 
    CheckCircleIcon,
    XMarkIcon,
    InboxArrowDownIcon,
    PencilSquareIcon,
    TrashIcon,
    CheckIcon,
    HandThumbDownIcon,
    EyeIcon
} from '@heroicons/vue/24/outline';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';

const props = defineProps({
    programs: {
        type: Array,
        default: () => []
    },
    donations: {
        type: Array,
        default: () => []
    },
    requests: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            pending_donations: 0,
            pending_requests: 0,
            total_programs: 0,
            total_distributed: 0
        })
    },
});

// Admin state
const activeTab = ref('programs'); // programs, donations, requests
const showProgramModal = ref(false);
const isEditing = ref(false);
const editingProgramId = ref(null);
const showProofModal = ref(false);
const activeProofUrl = ref('');

// Program Form
const programForm = useForm({
    title: '',
    description: '',
    target_amount: 10,
    collected_amount: 0,
    distributed_amount: 0,
    status: 'active',
    image: null,
});

// Helpers
const openAddProgram = () => {
    isEditing.value = false;
    editingProgramId.value = null;
    programForm.clearErrors();
    programForm.reset();
    showProgramModal.value = true;
};

const openEditProgram = (program) => {
    isEditing.value = true;
    editingProgramId.value = program.id;
    programForm.clearErrors();
    programForm.title = program.title || '';
    programForm.description = program.description || '';
    programForm.target_amount = program.target_amount || 0;
    programForm.collected_amount = program.collected_amount || 0;
    programForm.distributed_amount = program.distributed_amount || 0;
    programForm.status = program.status || 'active';
    programForm.image = null;
    showProgramModal.value = true;
};

const submitProgram = () => {
    if (isEditing.value) {
        programForm.post(route('lumbung-pangan.programs.update', editingProgramId.value), {
            forceFormData: true,
            onSuccess: () => {
                showProgramModal.value = false;
                programForm.reset();
            }
        });
    } else {
        programForm.post(route('lumbung-pangan.programs.store'), {
            forceFormData: true,
            onSuccess: () => {
                showProgramModal.value = false;
                programForm.reset();
            }
        });
    }
};

const deleteProgram = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus program lumbung pangan ini?')) {
        useForm({}).delete(route('lumbung-pangan.programs.destroy', id));
    }
};

// Donation Actions
const updateDonationStatus = (id, status) => {
    if (confirm(`Ubah status donasi menjadi ${status.toUpperCase()}?`)) {
        useForm({ status }).patch(route('lumbung-pangan.donations.status', id));
    }
};

const deleteDonation = (id) => {
    if (confirm('Hapus log donasi ini?')) {
        useForm({}).delete(route('lumbung-pangan.donations.destroy', id));
    }
};

// Request Actions
const updateRequestStatus = (id, status) => {
    if (confirm(`Ubah status permohonan bantuan menjadi ${status.toUpperCase()}?`)) {
        useForm({ status }).patch(route('lumbung-pangan.requests.status', id));
    }
};

const deleteRequest = (id) => {
    if (confirm('Hapus permohonan bantuan ini?')) {
        useForm({}).delete(route('lumbung-pangan.requests.destroy', id));
    }
};

const viewProof = (url) => {
    activeProofUrl.value = url;
    showProofModal.value = true;
};

const formatRupiah = (amount) => {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount || 0);
};
</script>

<template>
    <Head title="Kelola Lumbung Pangan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-black text-2xl leading-tight text-slate-800 dark:text-white uppercase tracking-tight">
                    🍱 Kelola Lumbung Pangan
                </h2>
                <button 
                    @click="openAddProgram" 
                    class="btn bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-bold text-xs uppercase tracking-wider border-none shadow-lg shadow-emerald-600/10 flex items-center gap-1.5"
                >
                    <PlusIcon class="w-4 h-4" />
                    Tambah Program
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <!-- Alert Message -->
                <div v-if="$page.props.flash && $page.props.flash.success" class="p-5 bg-emerald-50 border border-emerald-200 rounded-3xl flex items-start gap-4 shadow-sm">
                    <CheckCircleIcon class="w-6 h-6 text-emerald-600 shrink-0" />
                    <div>
                        <h4 class="font-extrabold text-emerald-900 text-sm">Operasi Sukses</h4>
                        <p class="text-xs text-emerald-700 font-medium mt-0.5">{{ $page.props.flash.success }}</p>
                    </div>
                </div>

                <!-- Stats Summary Row -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="card bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xl rounded-3xl p-6 relative overflow-hidden group">
                        <div class="absolute right-0 bottom-0 translate-y-3 translate-x-3 text-slate-100 dark:text-slate-700 opacity-20 scale-150 group-hover:scale-175 transition-transform">
                            <InboxArrowDownIcon class="w-24 h-24" />
                        </div>
                        <span class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1 block">Donasi Pending</span>
                        <span class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">{{ stats?.pending_donations || 0 }}</span>
                        <div class="text-[11px] text-amber-600 font-bold mt-2">Perlu Verifikasi</div>
                    </div>

                    <div class="card bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xl rounded-3xl p-6 relative overflow-hidden group">
                        <div class="absolute right-0 bottom-0 translate-y-3 translate-x-3 text-slate-100 dark:text-slate-700 opacity-20 scale-150 group-hover:scale-175 transition-transform">
                            <UsersIcon class="w-24 h-24" />
                        </div>
                        <span class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1 block">Permohonan Pending</span>
                        <span class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">{{ stats?.pending_requests || 0 }}</span>
                        <div class="text-[11px] text-amber-600 font-bold mt-2">Butuh Persetujuan</div>
                    </div>

                    <div class="card bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xl rounded-3xl p-6 relative overflow-hidden group">
                        <div class="absolute right-0 bottom-0 translate-y-3 translate-x-3 text-slate-100 dark:text-slate-700 opacity-20 scale-150 group-hover:scale-175 transition-transform">
                            <GiftIcon class="w-24 h-24" />
                        </div>
                        <span class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1 block">Total Program</span>
                        <span class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">{{ stats?.total_programs || 0 }}</span>
                        <div class="text-[11px] text-emerald-600 font-bold mt-2">Daftar Aktif</div>
                    </div>

                    <div class="card bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xl rounded-3xl p-6 relative overflow-hidden group">
                        <div class="absolute right-0 bottom-0 translate-y-3 translate-x-3 text-slate-100 dark:text-slate-700 opacity-20 scale-150 group-hover:scale-175 transition-transform">
                            <CheckCircleIcon class="w-24 h-24" />
                        </div>
                        <span class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1 block">Tersalurkan</span>
                        <span class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">{{ stats?.total_distributed || 0 }}</span>
                        <div class="text-[11px] text-emerald-600 font-bold mt-2">Paket Sembako</div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="flex gap-4 border-b border-slate-200 dark:border-slate-700 pb-3">
                    <button 
                        @click="activeTab = 'programs'"
                        :class="[
                            'px-6 py-2.5 rounded-full text-sm font-extrabold uppercase tracking-wide transition-all',
                            activeTab === 'programs' ? 'bg-slate-800 text-white dark:bg-slate-700' : 'bg-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800'
                        ]"
                    >
                        Program Sembako ({{ programs?.length || 0 }})
                    </button>
                    <button 
                        @click="activeTab = 'donations'"
                        :class="[
                            'px-6 py-2.5 rounded-full text-sm font-extrabold uppercase tracking-wide transition-all flex items-center gap-1.5',
                            activeTab === 'donations' ? 'bg-slate-800 text-white dark:bg-slate-700' : 'bg-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800'
                        ]"
                    >
                        Donasi Masuk ({{ donations?.length || 0 }})
                        <span v-if="stats?.pending_donations > 0" class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    </button>
                    <button 
                        @click="activeTab = 'requests'"
                        :class="[
                            'px-6 py-2.5 rounded-full text-sm font-extrabold uppercase tracking-wide transition-all flex items-center gap-1.5',
                            activeTab === 'requests' ? 'bg-slate-800 text-white dark:bg-slate-700' : 'bg-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800'
                        ]"
                    >
                        Permohonan Bantuan ({{ requests?.length || 0 }})
                        <span v-if="stats?.pending_requests > 0" class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    </button>
                </div>

                <!-- Tab Content -->
                <div v-if="activeTab === 'programs'" class="space-y-6 animate-fade-in">
                    <Card padding="none">
                        <div class="overflow-x-auto">
                            <table class="table-default w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                                        <th class="px-6 py-4">Program</th>
                                        <th class="px-6 py-4">Target Paket</th>
                                        <th class="px-6 py-4">Stok Terumpul</th>
                                        <th class="px-6 py-4">Tersalurkan</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="program in (programs || [])" :key="program.id" class="border-b border-slate-100 dark:border-slate-800 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50/50 dark:hover:bg-slate-800/20">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-4">
                                                <div class="w-14 h-10 rounded-lg overflow-hidden bg-slate-200 shrink-0">
                                                    <img :src="program.image_url" alt="" class="w-full h-full object-cover" />
                                                </div>
                                                <div>
                                                    <h4 class="font-extrabold text-slate-900 dark:text-white line-clamp-1">{{ program.title }}</h4>
                                                    <p class="text-xs text-slate-400 dark:text-slate-500 line-clamp-1">{{ program.description }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">{{ program.target_amount }} Paket</td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-slate-950 dark:text-white">{{ program.collected_amount }}</span>
                                                <span class="text-xs text-slate-400">({{ program.formatted_progress }})</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-emerald-600 dark:text-emerald-400 font-extrabold">{{ program.distributed_amount }} Paket</td>
                                        <td class="px-6 py-4">
                                            <Badge :variant="program.status === 'active' ? 'success' : 'neutral'">
                                                {{ program.status === 'active' ? 'Aktif' : 'Selesai' }}
                                            </Badge>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <button @click="openEditProgram(program)" class="btn btn-square btn-ghost btn-sm text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                                    <PencilSquareIcon class="w-5 h-5" />
                                                </button>
                                                <button @click="deleteProgram(program.id)" class="btn btn-square btn-ghost btn-sm text-red-400 hover:text-red-600">
                                                    <TrashIcon class="w-5 h-5" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!programs || programs.length === 0">
                                        <td colspan="6" class="text-center py-10 text-slate-400">Belum ada program sembako.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </Card>
                </div>

                <!-- Tab Donations -->
                <div v-if="activeTab === 'donations'" class="space-y-6 animate-fade-in">
                    <Card padding="none">
                        <div class="overflow-x-auto">
                            <table class="table-default w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                                        <th class="px-6 py-4">Donatur</th>
                                        <th class="px-6 py-4">Program</th>
                                        <th class="px-6 py-4">Jenis</th>
                                        <th class="px-6 py-4">Detail</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="donation in (donations || [])" :key="donation.id" class="border-b border-slate-100 dark:border-slate-800 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50/50 dark:hover:bg-slate-800/20">
                                        <td class="px-6 py-4">
                                            <div class="font-extrabold text-slate-900 dark:text-white">{{ donation.donor_name }}</div>
                                            <div class="text-xs text-slate-400">{{ donation.donor_phone }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-xs">{{ donation.program?.title || 'Umum' }}</td>
                                        <td class="px-6 py-4">
                                            <Badge :variant="donation.donation_type === 'uang' ? 'info' : 'primary'">{{ donation.donation_type?.toUpperCase() }}</Badge>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ donation.donation_type === 'uang' ? formatRupiah(donation.amount) : donation.items }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <Badge :variant="donation.status === 'approved' ? 'success' : (donation.status === 'rejected' ? 'danger' : 'warning')">
                                                {{ donation.status?.toUpperCase() }}
                                            </Badge>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-1.5 flex-wrap">
                                                <template v-if="donation.status === 'pending'">
                                                    <button @click="updateDonationStatus(donation.id, 'approved')" class="btn btn-square btn-xs bg-emerald-100 text-emerald-700 border-none" title="Setujui"><CheckIcon class="w-4 h-4" /></button>
                                                    <button @click="updateDonationStatus(donation.id, 'rejected')" class="btn btn-square btn-xs bg-rose-100 text-rose-700 border-none" title="Tolak"><HandThumbDownIcon class="w-4 h-4" /></button>
                                                </template>
                                                <a
                                                    v-if="donation.donor_phone"
                                                    :href="getWhatsAppUrl(donation.donor_phone, `Assalamu'alaikum Bpk/Ibu ${donation.donor_name}, mengenai donasi Lumbung Pangan untuk program ${donation.program?.title || 'Masjid'}...`)"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="btn btn-square btn-xs bg-emerald-600 hover:bg-emerald-700 text-white border-none"
                                                    title="Hubungi Donatur via WA"
                                                >
                                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                                    </svg>
                                                </a>
                                                <button v-if="donation.proof_url" @click="viewProof(donation.proof_url)" class="btn btn-square btn-xs btn-ghost text-slate-400" title="Lihat Bukti"><EyeIcon class="w-4 h-4" /></button>
                                                <button @click="deleteDonation(donation.id)" class="btn btn-square btn-xs bg-red-50 text-red-500 border-none hover:bg-red-100" title="Hapus"><TrashIcon class="w-4 h-4" /></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </Card>
                </div>

                <!-- Tab Requests -->
                <div v-if="activeTab === 'requests'" class="space-y-6 animate-fade-in">
                    <Card padding="none">
                        <div class="overflow-x-auto">
                            <table class="table-default w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                                        <th class="px-6 py-4">Pemohon</th>
                                        <th class="px-6 py-4">Program</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="req in (requests || [])" :key="req.id" class="border-b border-slate-100 dark:border-slate-800 text-sm font-medium text-slate-700 dark:text-slate-300">
                                        <td class="px-6 py-4">
                                            <div class="font-extrabold">{{ req.name }}</div>
                                            <div class="text-xs text-slate-400">{{ req.phone }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-xs">{{ req.program?.title }}</td>
                                        <td class="px-6 py-4">
                                            <Badge :variant="req.status === 'distributed' ? 'success' : 'warning'">{{ req.status?.toUpperCase() }}</Badge>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2 flex-wrap">
                                                <template v-if="req.status === 'pending'">
                                                    <button @click="updateRequestStatus(req.id, 'approved')" class="btn btn-xs bg-blue-100 text-blue-700 border-none">Setujui</button>
                                                    <button @click="updateRequestStatus(req.id, 'rejected')" class="btn btn-xs bg-rose-100 text-rose-700 border-none">Tolak</button>
                                                </template>
                                                <button v-else-if="req.status === 'approved'" @click="updateRequestStatus(req.id, 'distributed')" class="btn btn-xs bg-emerald-600 text-white border-none">Salurkan</button>
                                                <a
                                                    v-if="req.phone"
                                                    :href="getWhatsAppUrl(req.phone, `Assalamu'alaikum Bpk/Ibu ${req.name}, mengenai pengajuan permohonan sembako Lumbung Pangan...`)"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="btn btn-xs bg-emerald-600 hover:bg-emerald-700 text-white border-none flex items-center gap-1 font-bold"
                                                    title="Hubungi Pemohon via WA"
                                                >
                                                    <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24">
                                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                                    </svg>
                                                    WA
                                                </a>
                                                <button @click="deleteRequest(req.id)" class="btn btn-square btn-xs bg-red-50 text-red-500 border-none hover:bg-red-100" title="Hapus"><TrashIcon class="w-4 h-4" /></button>
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

        <!-- Modals would go here (Add/Edit Program, View Proof) -->
        <TransitionRoot as="template" :show="showProgramModal">
            <Dialog as="div" class="relative z-[60]" @close="showProgramModal = false">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" />
                </TransitionChild>
                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="ease-in duration-200" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="w-full max-w-lg transform overflow-hidden rounded-3xl bg-white p-6 shadow-2xl transition-all">
                                <h3 class="text-lg font-black text-slate-800 uppercase mb-6">{{ isEditing ? 'Edit Program' : 'Tambah Program' }}</h3>
                                <form @submit.prevent="submitProgram" class="space-y-4">
                                    <input v-model="programForm.title" type="text" placeholder="Judul Program" class="w-full px-4 py-3 border rounded-2xl" required />
                                    <textarea v-model="programForm.description" placeholder="Deskripsi" class="w-full px-4 py-3 border rounded-2xl" required></textarea>
                                    <div class="grid grid-cols-2 gap-4">
                                        <input v-model.number="programForm.target_amount" type="number" placeholder="Target Paket" class="w-full px-4 py-3 border rounded-2xl" required />
                                        <select v-model="programForm.status" class="w-full px-4 py-3 border rounded-2xl">
                                            <option value="active">Aktif</option>
                                            <option value="completed">Selesai</option>
                                        </select>
                                    </div>
                                    <div class="flex gap-3 pt-4">
                                        <button type="button" @click="showProgramModal = false" class="btn btn-outline w-1/2 rounded-2xl">Batal</button>
                                        <button type="submit" :disabled="programForm.processing" class="btn bg-slate-800 text-white w-1/2 rounded-2xl">Simpan</button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Proof Modal -->
        <TransitionRoot as="template" :show="showProofModal">
            <Dialog as="div" class="relative z-[60]" @close="showProofModal = false">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" />
                </TransitionChild>
                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <DialogPanel class="w-full max-w-lg transform overflow-hidden rounded-3xl bg-white p-6 shadow-2xl">
                            <img :src="activeProofUrl" alt="Bukti" class="w-full rounded-2xl" />
                            <button @click="showProofModal = false" class="mt-4 btn btn-ghost w-full">Tutup</button>
                        </DialogPanel>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

    </AuthenticatedLayout>
</template>
