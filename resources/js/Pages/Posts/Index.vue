<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ModernTable from '@/Components/ModernTable.vue';
import Modal from '@/Components/Modal.vue';
import { PlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    posts: Array,
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    title: '',
    excerpt: '',
    content: '',
    photo: null,
    is_published: true,
    published_at: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.is_published = true;
    form.published_at = new Date().toISOString().slice(0, 16); // Set to current datetime
    form.transform((data) => data); // Reset transform to prevent sticky _method: put
    isModalOpen.value = true;
};

const openEditModal = (post) => {
    isEditing.value = true;
    editingId.value = post.id;
    form.title = post.title;
    form.excerpt = post.excerpt;
    form.content = post.content;
    form.is_published = Boolean(post.is_published);
    form.published_at = post.published_at ? new Date(post.published_at).toISOString().slice(0, 16) : '';
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
        })).post(route('posts.update', editingId.value), {
            onSuccess: () => closeModal(),
            forceFormData: true,
        });
    } else {
        form.post(route('posts.store'), {
            onSuccess: () => closeModal(),
            forceFormData: true,
        });
    }
};

const deleteResult = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus berita ini?')) {
        form.delete(route('posts.destroy', id));
    }
};

const columns = [
    { key: 'image_url', label: 'Gambar', type: 'image' },
    { key: 'title', label: 'Judul' },
    { key: 'excerpt', label: 'Ringkasan' },
    { key: 'is_published', label: 'Status', type: 'boolean' },
    { key: 'author_name', label: 'Penulis' },
    { key: 'actions', label: 'Aksi' }
];

const handleImageUpload = (e) => {
    form.photo = e.target.files[0];
};
</script>

<template>
    <Head title="Manajemen Berita & Kegiatan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl leading-tight text-slate-900 dark:text-white flex items-center gap-2">
                    <span>📰</span> Manajemen Berita & Kegiatan
                </h2>
                <button @click="openCreateModal" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors shadow-sm">
                    <PlusIcon class="w-5 h-5" />
                    Tambah Berita
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-xl border border-slate-200 dark:border-slate-700">
                    <div class="p-6">
                         <ModernTable 
                            :columns="columns" 
                            :data="posts"
                        >
                            <template #cell-title="{ value }">
                                <span class="font-semibold text-slate-900 dark:text-white">{{ value }}</span>
                            </template>
                            <template #cell-excerpt="{ value }">
                                <span class="text-slate-600 dark:text-slate-400">{{ value }}</span>
                            </template>
                            <template #cell-author_name="{ value }">
                                <span class="text-slate-500 dark:text-slate-400 font-medium">{{ value }}</span>
                            </template>
                            <template #cell-image_url="{ value }">
                                <div class="h-12 w-20 rounded-md overflow-hidden bg-slate-100">
                                    <img 
                                        v-if="value" 
                                        :src="value" 
                                        class="h-full w-full object-cover" 
                                        alt="Thumbnail" 
                                    />
                                    <div v-else class="h-full w-full flex items-center justify-center text-slate-400 text-xs">
                                        No Img
                                    </div>
                                </div>
                            </template>

                            <template #cell-is_published="{ value }">
                                <span 
                                    :class="[
                                        'px-2 py-1 rounded-full text-xs font-semibold',
                                        value 
                                            ? 'bg-emerald-100 text-emerald-800' 
                                            : 'bg-slate-100 text-slate-800'
                                    ]"
                                >
                                    {{ value ? 'Terbit' : 'Draft' }}
                                </span>
                            </template>

                            <template #cell-actions="{ row }">
                                <div class="flex items-center gap-2">
                                    <button 
                                        @click="openEditModal(row)" 
                                        class="p-1 text-blue-600 hover:bg-blue-50 rounded transition-colors"
                                        title="Edit"
                                    >
                                        <PencilSquareIcon class="w-5 h-5" />
                                    </button>
                                    <button 
                                        @click="deleteResult(row.id)" 
                                        class="p-1 text-red-600 hover:bg-red-50 rounded transition-colors"
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

        <!-- Modal -->
        <Modal :show="isModalOpen" maxWidth="2xl" @close="closeModal">
            <div class="p-6 bg-slate-900 rounded-lg">
                <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <span class="p-2 bg-emerald-500/20 rounded-lg text-emerald-400">
                         <PlusIcon v-if="!isEditing" class="w-5 h-5" />
                         <PencilSquareIcon v-else class="w-5 h-5" />
                    </span>
                    {{ isEditing ? 'Edit Berita' : 'Tambah Berita Baru' }}
                </h2>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2 uppercase tracking-wider">Judul Berita</label>
                        <input v-model="form.title" type="text" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" placeholder="Judul Berita/Kegiatan">
                        <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
                    </div>

                    <!-- Excerpt -->
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2 uppercase tracking-wider">Ringkasan (Pendek)</label>
                        <textarea v-model="form.excerpt" rows="2" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" placeholder="Ringkasan singkat untuk tampilan awal..."></textarea>
                    </div>

                    <!-- Content -->
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2 uppercase tracking-wider">Isi Berita Lengkap</label>
                        <textarea v-model="form.content" rows="6" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" placeholder="Tulis isi berita selengkapnya di sini..."></textarea>
                        <div v-if="form.errors.content" class="text-red-500 text-sm mt-1">{{ form.errors.content }}</div>
                    </div>

                    <!-- Image -->
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2 uppercase tracking-wider">Gambar Utama</label>
                        <div class="flex items-center gap-4">
                            <input type="file" @change="handleImageUpload" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        </div>
                         <div v-if="form.errors.photo" class="text-red-500 text-sm mt-1">{{ form.errors.photo }}</div>
                    </div>

                    <!-- Published At -->
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2 uppercase tracking-wider">Tanggal & Waktu Terbit</label>
                        <input v-model="form.published_at" type="datetime-local" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700">
                        <div v-if="form.errors.published_at" class="text-red-500 text-sm mt-1">{{ form.errors.published_at }}</div>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center gap-3 p-3 bg-white/5 rounded-lg border border-white/10">
                        <input v-model="form.is_published" type="checkbox" id="is_published" class="w-5 h-5 rounded border-white/20 text-emerald-500 focus:ring-emerald-500 bg-slate-800">
                        <label for="is_published" class="text-sm font-medium text-white">Terbitkan Langsung ke Publik</label>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 border-t border-white/10 pt-6">
                        <button type="button" @click="closeModal" class="px-5 py-2.5 border border-white/20 rounded-xl text-white hover:bg-white/10 transition-colors font-medium">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 disabled:opacity-50 font-bold shadow-lg shadow-emerald-900/20 transition-all active:scale-95">
                            {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Perbarui Berita' : 'Simpan Berita') }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
