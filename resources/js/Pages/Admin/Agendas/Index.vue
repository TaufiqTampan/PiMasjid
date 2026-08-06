<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { 
    PlusIcon, 
    PencilSquareIcon, 
    TrashIcon, 
    MagnifyingGlassIcon,
    CalendarIcon,
    MapPinIcon,
    ClockIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const props = defineProps({
    agendas: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const showModal = ref(false);
const editingAgenda = ref(null);
const confirmDeleteId = ref(null);

const form = useForm({
    title: '',
    description: '',
    date: '',
    time: '',
    location: 'Masjid Utama',
    is_active: true,
});

watch(search, (value) => {
    router.get('/agendas', { search: value }, { preserveState: true, preserveScroll: true, replace: true });
});

const openModal = (agenda = null) => {
    editingAgenda.value = agenda;
    if (agenda) {
        form.title = agenda.title;
        form.description = agenda.description;
        form.date = agenda.date; // Ensure format YYYY-MM-DD
        form.time = agenda.time.substring(0, 5); // Ensure HH:mm
        form.location = agenda.location;
        form.is_active = Boolean(agenda.is_active);
    } else {
        form.reset();
        form.location = 'Masjid Utama';
        form.is_active = true;
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingAgenda.value = null;
    form.reset();
};

const submit = () => {
    if (editingAgenda.value) {
        form.put(route('agendas.update', editingAgenda.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('agendas.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteAgenda = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus agenda ini?')) {
        router.delete(route('agendas.destroy', id));
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};
</script>

<template>
    <Head title="Kelola Agenda" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight flex items-center gap-2">
                    <span>🗓️</span> Kelola Agenda
                </h2>
                <button 
                    @click="openModal()"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition-colors"
                >
                    <PlusIcon class="w-5 h-5" />
                    Tambah Agenda
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-4 mb-6 flex items-center gap-2">
                    <MagnifyingGlassIcon class="w-5 h-5 text-slate-400" />
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Cari agenda..." 
                        class="border-none w-full focus:ring-0 text-slate-700 dark:text-slate-200 placeholder-slate-400 dark:bg-slate-800"
                    >
                </div>

                <!-- Agenda List -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="agenda in agendas.data" :key="agenda.id" 
                         class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden hover:shadow-md transition-shadow relative"
                    >
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-bold text-lg text-slate-800 dark:text-white line-clamp-2">{{ agenda.title }}</h3>
                                    <span 
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mt-1"
                                        :class="agenda.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'"
                                    >
                                        {{ agenda.is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </div>
                                <div class="flex gap-1">
                                    <button @click="openModal(agenda)" class="p-1.5 text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-colors">
                                        <PencilSquareIcon class="w-5 h-5" />
                                    </button>
                                    <button @click="deleteAgenda(agenda.id)" class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                            
                            <div class="space-y-2 text-sm text-slate-600 mb-4">
                                <div class="flex items-center gap-2">
                                    <CalendarIcon class="w-4 h-4 text-emerald-500" />
                                    <span class="text-slate-600 dark:text-slate-400">{{ formatDate(agenda.date) }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <ClockIcon class="w-4 h-4 text-emerald-500" />
                                    <span class="text-slate-600 dark:text-slate-400">{{ agenda.time.substring(0, 5) }} WIB</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <MapPinIcon class="w-4 h-4 text-emerald-500" />
                                    <span class="text-slate-600 dark:text-slate-400">{{ agenda.location }}</span>
                                </div>
                            </div>
                            
                            <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-3">
                                {{ agenda.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="agendas.data.length === 0" class="text-center py-20 bg-white dark:bg-slate-800 rounded-xl border border-dashed border-slate-300 dark:border-slate-700">
                    <CalendarIcon class="w-16 h-16 text-slate-300 dark:text-slate-600 mx-auto mb-4" />
                    <h3 class="text-lg font-medium text-slate-900 dark:text-white">Belum ada agenda</h3>
                    <p class="text-slate-500 dark:text-slate-400 mb-6">Mulai dengan menambahkan agenda baru untuk masjid.</p>
                    <button 
                        @click="openModal()"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors"
                    >
                        Tambah Agenda
                    </button>
                </div>

                <!-- Pagination -->
                <div v-if="agendas.links.length > 3" class="mt-6 flex justify-center">
                    <div class="flex flex-wrap gap-1">
                        <template v-for="(link, k) in agendas.links" :key="k">
                            <div v-if="link.url === null"  class="px-4 py-2 text-sm text-slate-400 border rounded-lg bg-white" v-html="link.label"></div>
                            <Link v-else :href="link.url" class="px-4 py-2 text-sm border rounded-lg bg-white hover:bg-slate-50" :class="{'bg-emerald-50 text-emerald-600 border-emerald-200 font-bold': link.active}" v-html="link.label"></Link>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <TransitionRoot as="template" :show="showModal">
            <Dialog as="div" class="relative z-50" @close="closeModal">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-slate-900/75 transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <DialogPanel class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                                <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                    <div class="flex justify-between items-center mb-5">
                                        <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-slate-900 dark:text-white">
                                            {{ editingAgenda ? 'Edit Agenda' : 'Tambah Agenda Baru' }}
                                        </DialogTitle>
                                        <button @click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                            <XMarkIcon class="w-6 h-6" />
                                        </button>
                                    </div>
                                    
                                    <form @submit.prevent="submit" class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Judul Agenda</label>
                                            <input v-model="form.title" type="text" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" required placeholder="Contoh: Kajian Rutin Sabtu">
                                            <div v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
                                                <input v-model="form.date" type="date" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" required>
                                                <div v-if="form.errors.date" class="text-red-500 text-xs mt-1">{{ form.errors.date }}</div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Waktu</label>
                                                <input v-model="form.time" type="time" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" required>
                                                <div v-if="form.errors.time" class="text-red-500 text-xs mt-1">{{ form.errors.time }}</div>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Lokasi</label>
                                            <input v-model="form.location" type="text" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" required>
                                            <div v-if="form.errors.location" class="text-red-500 text-xs mt-1">{{ form.errors.location }}</div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Deskripsi</label>
                                            <textarea v-model="form.description" rows="3" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" placeholder="Detail kegiatan..."></textarea>
                                            <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                                        </div>

                                        <div class="flex items-center">
                                            <input v-model="form.is_active" type="checkbox" id="is_active" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600">
                                            <label for="is_active" class="ml-2 block text-sm text-slate-900">Aktifkan Agenda Ini</label>
                                        </div>

                                        <div class="mt-6 flex justify-end gap-3">
                                            <button type="button" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors font-medium" @click="closeModal">Batal</button>
                                            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium shadow-sm" :disabled="form.processing">
                                                {{ editingAgenda ? 'Simpan Perubahan' : 'Buat Agenda' }}
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
    </AuthenticatedLayout>
</template>
