<script setup>
import { ref, watch, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import ModernTable from '@/Components/ModernTable.vue';
import Pagination from '@/Components/Pagination.vue';
import Badge from '@/Components/Badge.vue';
import { useToast } from '@/Composables/useToast';
import { 
    PlusIcon, 
    FunnelIcon, 
    ArrowDownTrayIcon, 
    DocumentTextIcon, 
    TableCellsIcon,
    TrashIcon,
    MagnifyingGlassIcon
} from '@heroicons/vue/24/outline'; // Importing icons

const props = defineProps({
    transactions: Object,
    filters: Object,
});

const toast = useToast();
const showCreateModal = ref(false);

// Filters State
const filterForm = useForm({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

// Watch filters and reload
const applyFilters = () => {
    filterForm.get(route('transactions.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filterForm.start_date = '';
    filterForm.end_date = '';
    applyFilters();
};

// Quick date helpers
const setToday = () => {
    const today = new Date().toISOString().split('T')[0];
    filterForm.start_date = today;
    filterForm.end_date = today;
    applyFilters();
};

const setThisMonth = () => {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
    filterForm.start_date = firstDay;
    filterForm.end_date = lastDay;
    applyFilters();
};

// Export
const exportData = (format) => {
    const params = new URLSearchParams({
        start_date: filterForm.start_date,
        end_date: filterForm.end_date,
        format: format,
    });
    window.location.href = `${route('transactions.export')}?${params.toString()}`;
};

// --- CREATE FORM LOGIC ---
const form = useForm({
    type: 'income',
    category: '',
    amount: '',
    description: '',
    date: new Date().toISOString().split('T')[0],
    proof_image: null,
});

const fileInput = ref(null);
const imagePreview = ref(null);

watch(() => form.type, (newType) => {
    if (newType === 'income') {
        form.proof_image = null;
        imagePreview.value = null;
        if (fileInput.value) fileInput.value.value = '';
    }
});

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.proof_image = file;
        const reader = new FileReader();
        reader.onload = (e) => { imagePreview.value = e.target.result; };
        reader.readAsDataURL(file);
    }
};

const submitCreate = () => {
    form.post(route('transactions.store'), {
        forceFormData: true,
        onSuccess: () => {
            toast.success('Transaksi berhasil disimpan!');
            form.reset();
            imagePreview.value = null;
            showCreateModal.value = false;
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0];
            toast.error(firstError || 'Gagal menyimpan transaksi');
        },
    });
};

// Delete
const deleteTransaction = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
        router.delete(route('transactions.destroy', id), {
            onSuccess: () => toast.success('Data berhasil dihapus'),
        });
    }
};

const categories = {
    income: ['Kotak Jumat', 'Infaq', 'Sedekah', 'Wakaf', 'Donasi', 'Lainnya'],
    expense: ['Operasional', 'Listrik & Air', 'Renovasi', 'Gaji Marbot', 'Kebersihan', 'Lainnya'],
};

// Table Columns
const columns = [
    { key: 'date', label: 'Tanggal' },
    { key: 'type', label: 'Tipe' },
    { key: 'category', label: 'Kategori' },
    { key: 'description', label: 'Keterangan' },
    { key: 'amount', label: 'Jumlah (Rp)' },
    { key: 'verified_by', label: 'Diverifikasi' },
    { key: 'actions', label: 'Aksi' },
];

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(val);
};
</script>

<template>
    <Head title="Manajemen Keuangan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
                <h2 class="font-bold text-2xl leading-tight text-slate-900 dark:text-white flex items-center gap-2">
                    <span>💰</span> Manajemen Keuangan
                </h2>
                <button 
                    @click="showCreateModal = true"
                    class="btn-primary flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg shadow-lg hover:shadow-xl transition-all"
                >
                    <PlusIcon class="w-5 h-5" />
                    <span>Tambah Transaksi</span>
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Filters & Export -->
                <Card padding="md" class="border border-slate-200">
                    <div class="flex flex-col xl:flex-row gap-6 justify-between items-start xl:items-end">
                        <!-- Filters -->
                        <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Dari Tanggal</label>
                                <input 
                                    v-model="filterForm.start_date" 
                                    type="date" 
                                    class="w-full rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 text-sm bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                                    @change="applyFilters"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Sampai Tanggal</label>
                                <input 
                                    v-model="filterForm.end_date" 
                                    type="date" 
                                    class="w-full rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 text-sm bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                                    @change="applyFilters"
                                />
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="setToday" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-medium transition-colors">Hari Ini</button>
                                <button type="button" @click="setThisMonth" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-medium transition-colors">Bulan Ini</button>
                                <button type="button" @click="resetFilters" class="px-3 py-2 text-red-500 hover:bg-red-50 rounded-lg text-xs font-medium transition-colors border border-transparent hover:border-red-200">Reset</button>
                            </div>
                        </div>

                        <!-- Exports -->
                         <div class="flex gap-2 w-full md:w-auto">
                            <button 
                                @click="exportData('pdf')"
                                class="flex-1 md:flex-none flex items-center justify-center gap-2 px-4 py-2 bg-white border border-red-500 text-red-600 rounded-lg hover:bg-red-50 transition-colors text-sm font-medium"
                            >
                                <DocumentTextIcon class="w-4 h-4" />
                                <span>PDF</span>
                            </button>
                            <button 
                                @click="exportData('excel')"
                                class="flex-1 md:flex-none flex items-center justify-center gap-2 px-4 py-2 bg-white border border-green-600 text-green-700 rounded-lg hover:bg-green-50 transition-colors text-sm font-medium"
                            >
                                <TableCellsIcon class="w-4 h-4" />
                                <span>Excel</span>
                            </button>
                        </div>
                    </div>
                </Card>

                <!-- Transactions Table -->
                <ModernTable
                    :columns="columns"
                    :data="transactions.data"
                >
                    <template #cell-date="{ row }">
                        <span class="whitespace-nowrap">{{ new Date(row.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}</span>
                    </template>
                    
                    <template #cell-type="{ row }">
                        <Badge :type="row.type === 'income' ? 'success' : 'danger'">
                            {{ row.type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                        </Badge>
                    </template>

                    <template #cell-category="{ row }">
                        <Badge type="info" outline>{{ row.category }}</Badge>
                    </template>

                    <template #cell-description="{ row }">
                        <div class="max-w-xs truncate" :title="row.description">{{ row.description || '-' }}</div>
                    </template>
                    
                    <template #cell-amount="{ row }">
                        <span :class="['font-mono font-medium', row.type === 'income' ? 'text-emerald-600' : 'text-rose-600']">
                            {{ row.type === 'income' ? '+' : '-' }} {{ formatCurrency(row.amount) }}
                        </span>
                    </template>

                    <template #cell-verified_by="{ row }">
                        <span class="text-xs text-slate-500">{{ row.verified_by?.name || 'Sistem' }}</span>
                    </template>
                    
                    <template #cell-actions="{ row }">
                        <button 
                            @click="deleteTransaction(row.id)"
                            class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors"
                            title="Hapus"
                        >
                            <TrashIcon class="w-5 h-5" />
                        </button>
                    </template>

                     <template #empty>
                        <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                            <MagnifyingGlassIcon class="w-16 h-16 mb-4 opacity-50" />
                            <p class="text-lg font-medium text-slate-600">Belum ada data transaksi</p>
                            <p class="text-sm">Silahkan tambah transaksi baru atau ubah filter pencarian.</p>
                        </div>
                    </template>

                    <template #pagination>
                        <Pagination :links="transactions.links" />
                    </template>
                </ModernTable>

            </div>
        </div>

        <!-- Create Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                     <h3 class="text-lg font-bold text-slate-900 dark:text-white">Input Transaksi Baru</h3>
                     <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                 <form @submit.prevent="submitCreate" class="space-y-4">
                        <!-- Type Selection -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Tipe Transaksi</label>
                            <div class="flex gap-4">
                                <label class="flex items-center cursor-pointer p-3 border rounded-lg hover:bg-emerald-50 peer-checked:bg-emerald-50 transition w-full" :class="form.type === 'income' ? 'border-emerald-500 bg-emerald-50 ring-1 ring-emerald-500' : 'border-slate-200'">
                                    <input v-model="form.type" type="radio" value="income" class="hidden" />
                                    <span class="text-sm font-medium" :class="form.type === 'income' ? 'text-emerald-700': 'text-slate-600'">💰 Pemasukan</span>
                                </label>
                                <label class="flex items-center cursor-pointer p-3 border rounded-lg hover:bg-rose-50 peer-checked:bg-rose-50 transition w-full" :class="form.type === 'expense' ? 'border-rose-500 bg-rose-50 ring-1 ring-rose-500' : 'border-slate-200'">
                                    <input v-model="form.type" type="radio" value="expense" class="hidden" />
                                    <span class="text-sm font-medium" :class="form.type === 'expense' ? 'text-rose-700': 'text-slate-600'">💸 Pengeluaran</span>
                                </label>
                            </div>
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kategori</label>
                            <select v-model="form.category" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700">
                                <option value="">Pilih Kategori</option>
                                <option v-for="cat in categories[form.type]" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                            <p v-if="form.errors.category" class="mt-1 text-xs text-red-600">{{ form.errors.category }}</p>
                        </div>

                        <!-- Amount & Date -->
                         <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jumlah (Rp)</label>
                                <input v-model="form.amount" type="number" min="0" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" placeholder="Contoh: 500000" />
                                <p v-if="form.errors.amount" class="mt-1 text-xs text-red-600">{{ form.errors.amount }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
                                <input v-model="form.date" type="date" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" />
                                <p v-if="form.errors.date" class="mt-1 text-xs text-red-600">{{ form.errors.date }}</p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Keterangan</label>
                            <textarea v-model="form.description" rows="2" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm bg-white text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700" placeholder="Detail transaksi..."></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
                        </div>

                        <!-- Proof Image -->
                        <div v-if="form.type === 'expense'" class="border border-dashed border-amber-300 rounded-lg p-4 bg-amber-50">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">📸 Bukti (Wajib untuk pengeluaran)</label>
                            <input ref="fileInput" type="file" @change="handleFileChange" accept="image/*,.pdf" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" />
                            <p v-if="form.errors.proof_image" class="mt-1 text-xs text-red-600">{{ form.errors.proof_image }}</p>
                            <div v-if="imagePreview" class="mt-2">
                                <img :src="imagePreview" class="h-20 rounded shadow-sm" />
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end gap-3">
                            <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg text-sm font-medium transition-colors">Batal</button>
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
