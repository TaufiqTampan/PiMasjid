<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import ModernTable from '@/Components/ModernTable.vue';
import Pagination from '@/Components/Pagination.vue';
import { useToast } from '@/Composables/useToast';
import { 
    PlusIcon, 
    PencilSquareIcon, 
    TrashIcon,
    MagnifyingGlassIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    schedules: Object,
});

const toast = useToast();
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    date: '',
    time: '12:00',
    khatib: '',
    imam: '',
    muadzin: '',
    bilal: '',
    title: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    // Set default date to next Friday? Maybe not needed, let user pick.
    showModal.value = true;
};

const openEditModal = (schedule) => {
    isEditing.value = true;
    editingId.value = schedule.id;
    form.date = schedule.date;
    form.time = schedule.time ? schedule.time.substring(0, 5) : '12:00';
    form.khatib = schedule.khatib;
    form.imam = schedule.imam;
    form.muadzin = schedule.muadzin;
    form.bilal = schedule.bilal;
    form.title = schedule.title;
    form.clearErrors();
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('friday-schedules.update', editingId.value), {
            onSuccess: () => {
                showModal.value = false;
                toast.success('Jadwal berhasil diperbarui');
                form.reset();
            },
        });
    } else {
        form.post(route('friday-schedules.store'), {
            onSuccess: () => {
                showModal.value = false;
                toast.success('Jadwal berhasil ditambahkan');
                form.reset();
            },
        });
    }
};

const deleteSchedule = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
        router.delete(route('friday-schedules.destroy', id), {
            onSuccess: () => toast.success('Jadwal berhasil dihapus'),
        });
    }
};

const columns = [
    { key: 'date', label: 'Tanggal' },
    { key: 'khatib', label: 'Khatib' },
    { key: 'imam', label: 'Imam' },
    { key: 'muadzin', label: 'Muadzin' },
    { key: 'bilal', label: 'Bilal' },
    { key: 'actions', label: 'Aksi' },
];
</script>

<template>
    <Head title="Manajemen Jadwal Jumat" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl leading-tight text-slate-900 dark:text-white">
                    🕌 Manajemen Jadwal Jumat
                </h2>
                <button 
                    @click="openCreateModal"
                    class="btn-primary flex items-center justify-center gap-2 px-4 py-2 rounded-lg shadow hover:shadow-md transition-all"
                >
                    <PlusIcon class="w-5 h-5" />
                    <span>Tambah Jadwal</span>
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <ModernTable
                    :columns="columns"
                    :data="schedules.data"
                >
                    <template #cell-date="{ row }">
                        <span class="font-medium whitespace-nowrap">
                            {{ new Date(row.date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
                        </span>
                        <div v-if="row.title" class="text-xs text-slate-500 mt-1 italic">
                            Topik: {{ row.title }}
                        </div>
                    </template>
                    
                    <template #cell-actions="{ row }">
                        <div class="flex gap-2">
                            <button 
                                @click="openEditModal(row)"
                                class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors"
                                title="Edit"
                            >
                                <PencilSquareIcon class="w-5 h-5" />
                            </button>
                            <button 
                                @click="deleteSchedule(row.id)"
                                class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors"
                                title="Hapus"
                            >
                                <TrashIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </template>

                    <template #empty>
                        <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                            <MagnifyingGlassIcon class="w-16 h-16 mb-4 opacity-50" />
                            <p class="text-lg font-medium text-slate-600">Belum ada jadwal Jumat</p>
                            <p class="text-sm">Silahkan tambah jadwal baru.</p>
                        </div>
                    </template>

                     <template #pagination>
                        <Pagination :links="schedules.links" />
                    </template>
                </ModernTable>

            </div>
        </div>

        <!-- Modal -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6">
                 <div class="flex justify-between items-center mb-6">
                     <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ isEditing ? 'Edit Jadwal Jumat' : 'Tambah Jadwal Jumat' }}
                     </h3>
                     <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <!-- Date & Time -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
                            <input v-model="form.date" type="date" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm" />
                            <p v-if="form.errors.date" class="mt-1 text-xs text-red-600">{{ form.errors.date }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jam</label>
                            <input v-model="form.time" type="time" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm" />
                            <p v-if="form.errors.time" class="mt-1 text-xs text-red-600">{{ form.errors.time }}</p>
                        </div>
                    </div>

                    <!-- Officers -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Khatib</label>
                            <input v-model="form.khatib" type="text" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm" placeholder="Nama Khatib" />
                            <p v-if="form.errors.khatib" class="mt-1 text-xs text-red-600">{{ form.errors.khatib }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Imam</label>
                            <input v-model="form.imam" type="text" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm" placeholder="Nama Imam" />
                            <p v-if="form.errors.imam" class="mt-1 text-xs text-red-600">{{ form.errors.imam }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Muadzin</label>
                            <input v-model="form.muadzin" type="text" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm" placeholder="Nama Muadzin" />
                            <p v-if="form.errors.muadzin" class="mt-1 text-xs text-red-600">{{ form.errors.muadzin }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Bilal</label>
                            <input v-model="form.bilal" type="text" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm" placeholder="Nama Bilal" />
                            <p v-if="form.errors.bilal" class="mt-1 text-xs text-red-600">{{ form.errors.bilal }}</p>
                        </div>
                    </div>

                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Topik / Judul Khutbah (Opsional)</label>
                        <input v-model="form.title" type="text" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm" placeholder="Contoh: Keutamaan Bulan Rajab" />
                        <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
                    </div>

                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg text-sm font-medium transition-colors">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium shadow-md transition-colors flex items-center gap-2">
                            <span v-if="form.processing" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                            Simpan
                        </button>
                    </div>

                </form>
            </div>
        </Modal>

        <!-- Toast -->
        <Transition name="fade">
            <div v-if="toast.show.value" :class="['fixed bottom-4 right-4 z-50 px-6 py-4 rounded-lg shadow-xl max-w-sm flex items-center gap-3', toast.type.value === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white']">
                <span class="text-xl">{{ toast.type.value === 'success' ? '✅' : '❌' }}</span>
                <p class="font-medium text-sm">{{ toast.message.value }}</p>
            </div>
        </Transition>

    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s, transform 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(10px); }
</style>
