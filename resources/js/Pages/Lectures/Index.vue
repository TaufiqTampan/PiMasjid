<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ModernTable from '@/Components/ModernTable.vue';
import Modal from '@/Components/Modal.vue';
import { PlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    lectures: Array,
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    title: '',
    speaker: '',
    date: '',
    time: '',
    location: '',
    photo: null,
    is_active: true,
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.is_active = true;
    form.date = new Date().toISOString().slice(0, 10); // Current date YYYY-MM-DD
    form.time = '09:00 - 11:30 WIB';
    form.location = 'Ruang Utama Masjid Al-Hidayah';
    form.transform((data) => data);
    isModalOpen.value = true;
};

const openEditModal = (lecture) => {
    isEditing.value = true;
    editingId.value = lecture.id;
    form.title = lecture.title;
    form.speaker = lecture.speaker;
    form.date = lecture.date ? lecture.date.slice(0, 10) : '';
    form.time = lecture.time;
    form.location = lecture.location;
    form.is_active = Boolean(lecture.is_active);
    form.photo = null; // Reset photo input
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(route('lectures.update', editingId.value), {
            onSuccess: () => closeModal(),
            forceFormData: true,
        });
    } else {
        form.post(route('lectures.store'), {
            onSuccess: () => closeModal(),
            forceFormData: true,
        });
    }
};

const deleteResult = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus kajian ini?')) {
        form.delete(route('lectures.destroy', id));
    }
};

const columns = [
    { key: 'image_url', label: 'Poster', type: 'image' },
    { key: 'title', label: 'Judul Kajian' },
    { key: 'speaker', label: 'Ustadz' },
    { key: 'date', label: 'Tanggal' },
    { key: 'time', label: 'Waktu' },
    { key: 'location', label: 'Lokasi' },
    { key: 'is_active', label: 'Status', type: 'boolean' },
    { key: 'actions', label: 'Aksi' }
];

const handleImageUpload = (e) => {
    form.photo = e.target.files[0];
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
};
</script>

<template>
    <Head title="Manajemen Kajian Umum & Live" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl leading-tight text-slate-900 dark:text-white flex items-center gap-2">
                    <span>📚</span> Manajemen Kajian Umum & Live
                </h2>
                <button @click="openCreateModal" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors shadow-sm">
                    <PlusIcon class="w-5 h-5" />
                    Tambah Kajian
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-xl border border-slate-200 dark:border-slate-700">
                    <div class="p-6">
                         <ModernTable 
                            :columns="columns" 
                            :data="lectures"
                        >
                            <template #cell-title="{ value }">
                                <span class="font-semibold text-slate-900 dark:text-white">{{ value }}</span>
                            </template>
                            <template #cell-speaker="{ value }">
                                <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ value }}</span>
                            </template>
                            <template #cell-date="{ value }">
                                <span class="text-slate-600 dark:text-slate-400 text-sm">{{ formatDate(value) }}</span>
                            </template>
                            <template #cell-time="{ value }">
                                <span class="text-slate-500 dark:text-slate-400 text-sm font-medium">{{ value }}</span>
                            </template>
                            <template #cell-location="{ value }">
                                <span class="text-slate-600 dark:text-slate-400 text-sm line-clamp-1" :title="value">{{ value }}</span>
                            </template>
                            <template #cell-image_url="{ value }">
                                <div class="h-12 w-20 rounded-md overflow-hidden bg-slate-100">
                                    <img 
                                        v-if="value" 
                                        :src="value" 
                                        class="h-full w-full object-cover" 
                                        alt="Poster" 
                                    />
                                    <div v-else class="h-full w-full flex items-center justify-center text-slate-400 text-xs">
                                        No Image
                                    </div>
                                </div>
                            </template>

                            <template #cell-is_active="{ value }">
                                <span 
                                    :class="[
                                        'px-2 py-1 rounded-full text-xs font-semibold',
                                        value 
                                            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900' 
                                            : 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-400'
                                    ]"
                                >
                                    {{ value ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </template>

                            <template #cell-actions="{ row }">
                                <div class="flex items-center gap-2">
                                    <button 
                                        @click="openEditModal(row)" 
                                        class="p-1 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/35 rounded transition-colors"
                                        title="Edit"
                                    >
                                        <PencilSquareIcon class="w-5 h-5" />
                                    </button>
                                    <button 
                                        @click="deleteResult(row.id)" 
                                        class="p-1 text-red-600 hover:bg-red-50 dark:hover:bg-red-950/35 rounded transition-colors"
                                        title="Hapus"
                                    >
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </template>
                         </ModernTable>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Form -->
        <Modal :show="isModalOpen" maxWidth="2xl" @close="closeModal">
            <div class="p-6 bg-slate-900 rounded-lg">
                <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <span class="p-2 bg-emerald-500/20 rounded-lg text-emerald-400">
                         <PlusIcon v-if="!isEditing" class="w-5 h-5" />
                         <PencilSquareIcon v-else class="w-5 h-5" />
                    </span>
                    {{ isEditing ? 'Edit Kajian Umum' : 'Tambah Kajian Baru' }}
                </h2>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2 uppercase tracking-wider">Judul Kajian</label>
                        <input v-model="form.title" type="text" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" placeholder="Contoh: Urgensi Adab Sebelum Ilmu">
                        <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
                    </div>

                    <!-- Speaker/Ustadz -->
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2 uppercase tracking-wider">Pengisi / Ustadz</label>
                        <input v-model="form.speaker" type="text" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" placeholder="Contoh: Ustadz Dr. Firanda Andirja, Lc., M.A.">
                        <div v-if="form.errors.speaker" class="text-red-500 text-sm mt-1">{{ form.errors.speaker }}</div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Date -->
                        <div>
                            <label class="block text-sm font-semibold text-white mb-2 uppercase tracking-wider">Tanggal</label>
                            <input v-model="form.date" type="date" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700">
                            <div v-if="form.errors.date" class="text-red-500 text-sm mt-1">{{ form.errors.date }}</div>
                        </div>

                        <!-- Time -->
                        <div>
                            <label class="block text-sm font-semibold text-white mb-2 uppercase tracking-wider">Waktu / Jam</label>
                            <input v-model="form.time" type="text" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" placeholder="Contoh: 09:00 - 11:30 WIB atau Ba'da Maghrib">
                            <div v-if="form.errors.time" class="text-red-500 text-sm mt-1">{{ form.errors.time }}</div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2 uppercase tracking-wider">Lokasi / Tempat</label>
                        <input v-model="form.location" type="text" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" placeholder="Contoh: Ruang Utama Masjid Al-Hidayah">
                        <div v-if="form.errors.location" class="text-red-500 text-sm mt-1">{{ form.errors.location }}</div>
                    </div>

                    <!-- Poster / Image -->
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2 uppercase tracking-wider">Poster Kajian (Gambar)</label>
                        <input type="file" @change="handleImageUpload" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        <div v-if="form.errors.photo" class="text-red-500 text-sm mt-1">{{ form.errors.photo }}</div>
                    </div>

                    <!-- Status Active -->
                    <div class="flex items-center gap-3 p-3 bg-white/5 rounded-lg border border-white/10">
                        <input v-model="form.is_active" type="checkbox" id="is_active" class="w-5 h-5 rounded border-white/20 text-emerald-500 focus:ring-emerald-500 bg-slate-800">
                        <label for="is_active" class="text-sm font-medium text-white">Aktifkan (Tampilkan di Halaman Publik)</label>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 border-t border-white/10 pt-6">
                        <button type="button" @click="closeModal" class="px-5 py-2.5 border border-white/20 rounded-xl text-white hover:bg-white/10 transition-colors font-medium">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 disabled:opacity-50 font-bold shadow-lg shadow-emerald-900/20 transition-all active:scale-95">
                            {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Perbarui Kajian' : 'Simpan Kajian') }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
