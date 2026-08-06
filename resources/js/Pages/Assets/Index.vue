<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import ModernTable from '@/Components/ModernTable.vue';

const props = defineProps({
    assets: Array,
});

// Create/Edit form
const showForm = ref(false);
const editingAsset = ref(null);

const form = useForm({
    name: '',
    condition: 'good',
    quantity: 1,
    purchase_date: '',
    purchase_price: '',
    notes: '',
});

const openCreate = () => {
    form.reset();
    editingAsset.value = null;
    showForm.value = true;
};

const openEdit = (asset) => {
    editingAsset.value = asset;
    form.name = asset.name;
    form.condition = asset.condition;
    form.quantity = asset.quantity;
    form.purchase_date = asset.purchase_date;
    form.purchase_price = asset.purchase_price;
    form.notes = asset.notes;
    showForm.value = true;
};

const saveAsset = () => {
    if (editingAsset.value) {
        form.put(route('assets.update', editingAsset.value.id), {
            onSuccess: () => { showForm.value = false; },
        });
    } else {
        form.post(route('assets.store'), {
            onSuccess: () => { showForm.value = false; form.reset(); },
        });
    }
};

const deleteAsset = (id) => {
    if (confirm('Hapus aset ini?')) {
        useForm({}).delete(route('assets.destroy', id));
    }
};

const printQR = (asset) => {
    window.open(`https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=ASSET-${asset.id}-${asset.name}`, '_blank');
};

// Table columns
const columns = [
    { key: 'name', label: 'Nama Aset', sortable: true },
    { key: 'condition', label: 'Kondisi', sortable: false },
    { key: 'quantity', label: 'Jumlah', sortable: true },
    { key: 'purchase_date', label: 'Tgl Beli', sortable: true },
    { key: 'actions', label: 'Aksi', sortable: false },
];
</script>

<template>
    <Head title="Inventaris Aset" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-2xl leading-tight text-slate-900 dark:text-white">
                    🏛️ Inventaris Aset Masjid
                </h2>
                <div class="flex gap-2">
                    <a :href="route('assets.export', { type: 'pdf' })" class="inline-flex items-center gap-1 px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm transition-colors">
                        📄 PDF
                    </a>
                    <a :href="route('assets.export', { type: 'excel' })" class="inline-flex items-center gap-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm transition-colors">
                        📊 Excel
                    </a>
                    <button @click="openCreate" class="btn-primary">
                        + Tambah Aset
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Create/Edit Form -->
                <Card v-if="showForm" padding="lg">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ editingAsset ? 'Edit Aset' : 'Tambah Aset Baru' }}</h3>
                        <button @click="showForm = false" class="text-slate-400 hover:text-slate-600">✕</button>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300">Nama Aset *</label>
                            <input v-model="form.name" type="text" class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white" />
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300">Kondisi *</label>
                            <select v-model="form.condition" class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                                <option value="good">Baik</option>
                                <option value="damaged">Rusak</option>
                                <option value="lost">Hilang</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300">Jumlah *</label>
                            <input v-model="form.quantity" type="number" min="1" class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white" />
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300">Tanggal Pembelian</label>
                            <input v-model="form.purchase_date" type="date" class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white" />
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300">Harga Pembelian</label>
                            <input v-model="form.purchase_price" type="number" min="0" class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white" placeholder="Rp" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm text-slate-700 dark:text-slate-300">Catatan</label>
                            <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white"></textarea>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-4">
                        <button @click="saveAsset" :disabled="form.processing" class="btn-primary">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                        <button @click="showForm = false" class="btn-secondary">Batal</button>
                    </div>
                </Card>

                <!-- Assets Table -->
                <Card padding="sm">
                    <ModernTable :columns="columns" :data="assets" hoverable striped>
                        <template #cell-condition="{ value }">
                            <Badge
                                :variant="value === 'good' ? 'success' : value === 'damaged' ? 'warning' : 'danger'"
                                size="sm"
                            >
                                {{ value === 'good' ? 'Baik' : value === 'damaged' ? 'Rusak' : 'Hilang' }}
                            </Badge>
                        </template>

                        <template #cell-actions="{ row }">
                            <div class="flex gap-2">
                                <button @click="printQR(row)" class="text-sm bg-primary-100 text-primary-700 hover:bg-primary-200 px-2 py-1 rounded">
                                    📱 QR
                                </button>
                                <button @click="openEdit(row)" class="text-sm bg-amber-100 text-amber-700 hover:bg-amber-200 px-2 py-1 rounded">
                                    ✏️ Edit
                                </button>
                                <button @click="deleteAsset(row.id)" class="text-sm bg-red-100 text-red-700 hover:bg-red-200 px-2 py-1 rounded">
                                    🗑️
                                </button>
                            </div>
                        </template>
                    </ModernTable>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
