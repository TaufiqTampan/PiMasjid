<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    PlusIcon, 
    PencilSquareIcon, 
    TrashIcon, 
    MagnifyingGlassIcon,
    GiftIcon,
    CheckCircleIcon,
    ClockIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const props = defineProps({
    wishlists: Array,
    stats: Object,
});

const search = ref('');
const showModal = ref(false);
const editingItem = ref(null);

const form = useForm({
    item_name: '',
    description: '',
    target_qty: 1,
    fulfilled_qty: 0,
    unit_price: 0,
    status: 'active',
});

const filteredWishlists = computed(() => {
    if (!search.value) return props.wishlists;
    const query = search.value.toLowerCase();
    return props.wishlists.filter(item => 
        item.item_name.toLowerCase().includes(query) ||
        (item.description && item.description.toLowerCase().includes(query))
    );
});

const openModal = (item = null) => {
    editingItem.value = item;
    if (item) {
        form.item_name = item.item_name;
        form.description = item.description || '';
        form.target_qty = item.target_qty;
        form.fulfilled_qty = item.fulfilled_qty;
        form.unit_price = item.unit_price;
        form.status = item.status;
    } else {
        form.reset();
        form.target_qty = 1;
        form.fulfilled_qty = 0;
        form.unit_price = 0;
        form.status = 'active';
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingItem.value = null;
    form.reset();
};

const submit = () => {
    if (editingItem.value) {
        form.put(route('wishlists.update', editingItem.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('wishlists.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteItem = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus item kebutuhan ini?')) {
        router.delete(route('wishlists.destroy', id));
    }
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'active':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400';
        case 'pending':
            return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400';
        case 'completed':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        case 'cancelled':
            return 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400';
        default:
            return 'bg-slate-100 text-slate-800';
    }
};
</script>

<template>
    <Head title="Kebutuhan Masjid (Wishlist)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight flex items-center gap-2">
                        <span>🎁</span> Kebutuhan Masjid (Wishlist)
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Kelola kebutuhan pengadaan barang atau peralatan fasilitas masjid.
                    </p>
                </div>
                <button 
                    @click="openModal()"
                    class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/20 transition-all hover:scale-[1.02] active:scale-95"
                >
                    <PlusIcon class="w-5 h-5" />
                    Tambah Kebutuhan
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center gap-4">
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl text-emerald-600 dark:text-emerald-400">
                            <GiftIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Item</div>
                            <div class="text-xl font-bold text-slate-800 dark:text-white">{{ stats?.total || 0 }}</div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center gap-4">
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl text-emerald-600 dark:text-emerald-400">
                            <CheckCircleIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Aktif</div>
                            <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats?.active || 0 }}</div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center gap-4">
                        <div class="p-3 bg-amber-50 dark:bg-amber-900/30 rounded-xl text-amber-600 dark:text-amber-400">
                            <ClockIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Menunggu</div>
                            <div class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ stats?.pending || 0 }}</div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center gap-4">
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-600 dark:text-blue-400">
                            <CheckCircleIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Terpenuhi</div>
                            <div class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ stats?.completed || 0 }}</div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Box -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                    <!-- Search Bar -->
                    <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center gap-3">
                        <MagnifyingGlassIcon class="w-5 h-5 text-slate-400" />
                        <input 
                            v-model="search"
                            type="text" 
                            placeholder="Cari nama barang atau deskripsi..." 
                            class="border-none w-full focus:ring-0 text-slate-700 dark:text-slate-200 placeholder-slate-400 bg-transparent text-sm"
                        >
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">Nama Barang</th>
                                    <th class="px-6 py-4">Progres Qty</th>
                                    <th class="px-6 py-4">Harga / Total Target</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                <tr v-if="filteredWishlists.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                        Belum ada data kebutuhan masjid.
                                    </td>
                                </tr>
                                <tr 
                                    v-for="item in filteredWishlists" 
                                    :key="item.id"
                                    class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors"
                                >
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800 dark:text-white">{{ item.item_name }}</div>
                                        <div v-if="item.description" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1">
                                            {{ item.description }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 min-w-[180px]">
                                        <div class="flex justify-between text-xs font-semibold mb-1 text-slate-700 dark:text-slate-300">
                                            <span>{{ item.fulfilled_qty }} / {{ item.target_qty }} unit</span>
                                            <span>{{ item.progress_percentage }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                            <div 
                                                class="h-2 rounded-full transition-all duration-500"
                                                :class="item.progress_percentage >= 100 ? 'bg-blue-500' : 'bg-emerald-500'"
                                                :style="{ width: `${Math.min(100, item.progress_percentage)}%` }"
                                            ></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-800 dark:text-white">{{ item.formatted_total_target }}</div>
                                        <div class="text-xs text-slate-400">@ {{ item.formatted_unit_price }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span 
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
                                            :class="getStatusBadgeClass(item.status)"
                                        >
                                            {{ item.status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <button 
                                            @click="openModal(item)"
                                            class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-lg transition-colors"
                                            title="Edit"
                                        >
                                            <PencilSquareIcon class="w-5 h-5" />
                                        </button>
                                        <button 
                                            @click="deleteItem(item.id)"
                                            class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-colors"
                                            title="Hapus"
                                        >
                                            <TrashIcon class="w-5 h-5" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <TransitionRoot as="template" :show="showModal">
            <Dialog as="div" class="relative z-50" @close="closeModal">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <DialogPanel class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100 dark:border-slate-700">
                                <form @submit.prevent="submit">
                                    <div class="px-6 pt-6 pb-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                                        <DialogTitle as="h3" class="text-lg font-bold text-slate-800 dark:text-white">
                                            {{ editingItem ? 'Edit Item Kebutuhan' : 'Tambah Kebutuhan Baru' }}
                                        </DialogTitle>
                                        <button type="button" @click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                            <XMarkIcon class="w-6 h-6" />
                                        </button>
                                    </div>

                                    <div class="p-6 space-y-4">
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Nama Barang *</label>
                                            <input 
                                                v-model="form.item_name" 
                                                type="text" 
                                                required 
                                                placeholder="Contoh: Sound System Wireless" 
                                                class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                            />
                                            <div v-if="form.errors.item_name" class="text-rose-500 text-xs mt-1">{{ form.errors.item_name }}</div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Target Qty *</label>
                                                <input 
                                                    v-model.number="form.target_qty" 
                                                    type="number" 
                                                    min="1" 
                                                    required 
                                                    class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Terpenuhi Qty</label>
                                                <input 
                                                    v-model.number="form.fulfilled_qty" 
                                                    type="number" 
                                                    min="0" 
                                                    class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                                />
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Harga Per Unit (Rp) *</label>
                                                <input 
                                                    v-model.number="form.unit_price" 
                                                    type="number" 
                                                    min="0" 
                                                    required 
                                                    class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Status *</label>
                                                <select 
                                                    v-model="form.status"
                                                    class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                                >
                                                    <option value="active">Aktif</option>
                                                    <option value="pending">Menunggu</option>
                                                    <option value="completed">Selesai</option>
                                                    <option value="cancelled">Dibatalkan</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Deskripsi / Keterangan</label>
                                            <textarea 
                                                v-model="form.description" 
                                                rows="3" 
                                                placeholder="Detail spesifikasi barang atau keterangan tambahan..."
                                                class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                            ></textarea>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3 rounded-b-2xl">
                                        <button 
                                            type="button" 
                                            @click="closeModal" 
                                            class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
                                        >
                                            Batal
                                        </button>
                                        <button 
                                            type="submit" 
                                            :disabled="form.processing"
                                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-600/20 transition-all"
                                        >
                                            {{ form.processing ? 'Menyimpan...' : (editingItem ? 'Simpan Perubahan' : 'Tambah Kebutuhan') }}
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
