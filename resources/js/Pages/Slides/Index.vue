<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Switch } from '@headlessui/vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';

const props = defineProps({
    slides: Array,
});

// Upload form
const uploadForm = useForm({
    title: '',
    image: null,
    order: '',
});

const imagePreview = ref(null);
const fileInput = ref(null);

// Handle file selection
const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        uploadForm.image = file;
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

// Upload slide
const uploadSlide = () => {
    uploadForm.post(route('slides.store'), {
        forceFormData: true,
        onSuccess: () => {
            uploadForm.reset();
            imagePreview.value = null;
            if (fileInput.value) fileInput.value.value = '';
        },
    });
};

// Toggle slide active status
const toggleSlide = (slideId) => {
    useForm({}).post(route('slides.toggle', slideId));
};

// Delete slide
const deleteSlide = (slideId) => {
    if (confirm('Hapus slide ini?')) {
        useForm({}).delete(route('slides.destroy', slideId));
    }
};
</script>

<template>
    <Head title="Kelola Slide TV" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-2xl leading-tight text-slate-900 dark:text-white">
                📺 Kelola Slide TV Display
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- Upload New Slide -->
                <Card padding="lg" variant="islamic">
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-6">
                        ➕ Upload Slide Baru
                    </h3>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Preview Area -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Preview Gambar
                            </label>
                            <div
                                v-if="imagePreview"
                                class="relative aspect-video bg-slate-100 rounded-xl overflow-hidden border-2 border-primary-300"
                            >
                                <img :src="imagePreview" alt="Preview" class="w-full h-full object-cover" />
                                <div class="absolute top-2 right-2">
                                    <Badge variant="success" dot>Preview</Badge>
                                </div>
                            </div>
                            <div
                                v-else
                                class="aspect-video bg-slate-100 rounded-xl border-2 border-dashed border-slate-300 flex items-center justify-center"
                            >
                                <div class="text-center text-slate-400">
                                    <svg class="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-sm">Pilih gambar untuk preview</p>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Form -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Judul Slide
                                </label>
                                <input
                                    v-model="uploadForm.title"
                                    type="text"
                                    class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Contoh: Pengumuman Jumat"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    File Gambar
                                </label>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    @change="handleFileSelect"
                                    accept="image/*"
                                    class="hidden"
                                />
                                <button
                                    type="button"
                                    @click="$refs.fileInput.click()"
                                    class="w-full py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-semibold rounded-lg transition-colors border-2 border-slate-300"
                                >
                                    📁 Pilih Gambar
                                </button>
                                <p class="mt-1 text-xs text-slate-500">Format: JPG, PNG. Maksimal 5MB</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Urutan (Optional)
                                </label>
                                <input
                                    v-model="uploadForm.order"
                                    type="number"
                                    class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Auto"
                                />
                            </div>

                            <button
                                @click="uploadSlide"
                                :disabled="!uploadForm.title || !uploadForm.image || uploadForm.processing"
                                class="w-full btn-primary text-lg disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ uploadForm.processing ? 'Mengupload...' : '💾 Simpan Slide' }}
                            </button>
                        </div>
                    </div>
                </Card>

                <!-- Slides Grid -->
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-4">
                        📋 Slide yang Tersedia ({{ slides.length }})
                    </h3>

                    <div v-if="slides.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <Card
                            v-for="slide in slides"
                            :key="slide.id"
                            padding="none"
                            hoverable
                            class="overflow-hidden"
                        >
                            <!-- Image -->
                            <div class="relative aspect-video bg-slate-200">
                                <img
                                    :src="slide.image_url"
                                    :alt="slide.title"
                                    class="w-full h-full object-cover"
                                />
                                <div v-if="slide.is_active" class="absolute top-3 right-3">
                                    <Badge variant="success" size="lg" dot>
                                        📺 LIVE
                                    </Badge>
                                </div>
                                <div v-else class="absolute top-3 right-3">
                                    <Badge variant="neutral" size="lg">
                                        OFF
                                    </Badge>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="p-4 space-y-3">
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-white">{{ slide.title }}</h4>
                                    <p class="text-xs text-slate-500">Urutan: {{ slide.order }}</p>
                                </div>

                                <!-- Toggle Switch -->
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-slate-700">Tampilkan di TV:</span>
                                    <Switch
                                        :model-value="slide.is_active"
                                        @update:model-value="toggleSlide(slide.id)"
                                        :class="slide.is_active ? 'bg-emerald-500' : 'bg-slate-300'"
                                        class="relative inline-flex h-7 w-14 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                    >
                                        <span
                                            :class="slide.is_active ? 'translate-x-8' : 'translate-x-1'"
                                            class="inline-block h-5 w-5 transform rounded-full bg-white transition-transform"
                                        />
                                    </Switch>
                                </div>

                                <!-- Delete Button -->
                                <button
                                    @click="deleteSlide(slide.id)"
                                    class="w-full py-2 bg-red-50 hover:bg-red-100 text-red-600 font-semibold rounded-lg transition-colors text-sm"
                                >
                                    🗑️ Hapus
                                </button>
                            </div>
                        </Card>
                    </div>

                    <!-- Empty State -->
                    <Card v-else padding="xl">
                        <div class="text-center py-12">
                            <div class="text-6xl mb-4">📺</div>
                            <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Belum Ada Slide</h3>
                            <p class="text-slate-600 dark:text-slate-400">Upload slide pertama untuk menampilkan konten di TV</p>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
