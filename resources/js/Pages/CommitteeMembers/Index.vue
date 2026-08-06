<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import ModernTable from '@/Components/ModernTable.vue';
import Pagination from '@/Components/Pagination.vue';
import { useToast } from '@/Composables/useToast';
import { 
    PlusIcon, 
    PencilSquareIcon, 
    TrashIcon,
    MagnifyingGlassIcon,
    PhotoIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    members: Object,
});

const toast = useToast();
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const previewImage = ref(null);

const form = useForm({
    name: '',
    position: '',
    division: 'Inti',
    photo: null,
    order: 0,
    is_active: true,
});

const divisionOptions = [
    'Inti',
    'Ibadah',
    'Pembangunan',
    'Pendidikan',
    'Sosial',
    'Humas',
    'Keamanan',
    'Kebersihan'
];

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    previewImage.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (member) => {
    isEditing.value = true;
    editingId.value = member.id;
    form.name = member.name;
    form.position = member.position;
    form.division = member.division;
    form.order = member.order;
    form.is_active = !!member.is_active;
    form.photo = null; // Don't preset file input
    previewImage.value = member.photo_url;
    form.clearErrors();
    showModal.value = true;
};

const handleImageUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.photo = file;
        previewImage.value = URL.createObjectURL(file);
    }
};

const submitForm = () => {
    if (isEditing.value) {
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(route('committee-members.update', editingId.value), {
            onSuccess: () => {
                showModal.value = false;
                toast.success('Pengurus berhasil diperbarui');
                form.reset();
            },
            forceFormData: true, 
        });
    } else {
        form.post(route('committee-members.store'), {
            onSuccess: () => {
                showModal.value = false;
                toast.success('Pengurus berhasil ditambahkan');
                form.reset();
            },
        });
    }
};

const deleteMember = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus pengurus ini?')) {
        router.delete(route('committee-members.destroy', id), {
            onSuccess: () => toast.success('Pengurus berhasil dihapus'),
        });
    }
};

const columns = [
    { key: 'photo', label: 'Foto' },
    { key: 'name', label: 'Nama' },
    { key: 'position', label: 'Jabatan' },
    { key: 'division', label: 'Bidang' },
    { key: 'order', label: 'Urutan' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Aksi' },
];
</script>

<template>
    <Head title="Manajemen Pengurus" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl leading-tight text-slate-900 dark:text-white flex items-center gap-2">
                    <span>👥</span> Struktur Pengurus
                </h2>
                <button 
                    @click="openCreateModal"
                    class="btn-primary flex items-center justify-center gap-2 px-4 py-2 rounded-lg shadow hover:shadow-md transition-all"
                >
                    <PlusIcon class="w-5 h-5" />
                    <span>Tambah Pengurus</span>
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <ModernTable
                    :columns="columns"
                    :data="members.data"
                >
                    <template #cell-photo="{ row }">
                        <img :src="row.photo_url" :alt="row.name" class="w-10 h-10 rounded-full object-cover border border-slate-200" />
                    </template>
                    
                    <template #cell-status="{ row }">
                        <span 
                            class="px-2 py-1 text-xs font-bold rounded-full"
                            :class="row.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'"
                        >
                            {{ row.is_active ? 'Aktif' : 'Non-Aktif' }}
                        </span>
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
                                @click="deleteMember(row.id)"
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
                            <p class="text-lg font-medium text-slate-600">Belum ada data pengurus</p>
                            <p class="text-sm">Silahkan tambah pengurus baru.</p>
                        </div>
                    </template>

                     <template #pagination>
                        <Pagination :links="members.links" />
                    </template>
                </ModernTable>

            </div>
        </div>

        <!-- Modal -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6">
                 <div class="flex justify-between items-center mb-6">
                     <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ isEditing ? 'Edit Pengurus' : 'Tambah Pengurus' }}
                     </h3>
                     <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    
                    <!-- Photo Upload -->
                    <div class="flex items-center gap-4">
                        <div class="shrink-0 relative w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200">
                            <img v-if="previewImage" :src="previewImage" class="w-full h-full object-cover" />
                            <PhotoIcon v-else class="w-8 h-8 text-slate-400" />
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Foto Profil</label>
                            <input 
                                type="file" 
                                @change="handleImageUpload" 
                                accept="image/*"
                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition-colors" 
                            />
                            <p v-if="form.errors.photo" class="mt-1 text-xs text-red-600">{{ form.errors.photo }}</p>
                            <p class="text-xs text-slate-500 mt-1">Format: JPG, PNG. Max: 2MB.</p>
                        </div>
                    </div>

                    <!-- Name & Position -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap</label>
                            <input v-model="form.name" type="text" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" placeholder="Nama lengkap + gelar" />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jabatan</label>
                            <input v-model="form.position" type="text" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" placeholder="Contoh: Ketua, Anggota" />
                            <p v-if="form.errors.position" class="mt-1 text-xs text-red-600">{{ form.errors.position }}</p>
                        </div>
                    </div>

                    <!-- Division & Order -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Bidang / Divisi</label>
                            <select v-model="form.division" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700">
                                <option v-for="opt in divisionOptions" :key="opt" :value="opt">{{ opt }}</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            <p v-if="form.errors.division" class="mt-1 text-xs text-red-600">{{ form.errors.division }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Urutan Tampil</label>
                            <input v-model="form.order" type="number" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" />
                            <p class="text-xs text-slate-500 mt-1">Semakin kecil angka, semakin di atas.</p>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center gap-2">
                         <input v-model="form.is_active" type="checkbox" id="is_active" class="rounded border-slate-300 text-emerald-600 shadow-sm focus:border-emerald-300 focus:ring focus:ring-emerald-200 focus:ring-opacity-50" />
                         <label for="is_active" class="text-sm text-slate-700 dark:text-slate-300 font-medium">Status Aktif</label>
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
