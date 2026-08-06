<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';

const props = defineProps({
    pendingTransactions: Array,
    threshold: Number,
});

// Rejection modal state
const showRejectModal = ref(false);
const selectedTransaction = ref(null);
const rejectForm = useForm({
    rejection_reason: '',
});

// Open rejection modal
const openRejectModal = (transaction) => {
    selectedTransaction.value = transaction;
    rejectForm.rejection_reason = '';
    showRejectModal.value = true;
};

// Close rejection modal
const closeRejectModal = () => {
    showRejectModal.value = false;
    selectedTransaction.value = null;
    rejectForm.reset();
};

// Approve transaction
const approve = (transaction) => {
    if (confirm(`Yakin setujui pengeluaran ${transaction.category} sebesar ${transaction.formatted_amount}?`)) {
        useForm({}).post(route('approvals.approve', transaction.id), {
            onSuccess: () => {
                // Transaction approved successfully
            },
        });
    }
};

// Reject transaction
const reject = () => {
    if (!rejectForm.rejection_reason) {
        alert('Alasan penolakan harus diisi!');
        return;
    }
    
    rejectForm.post(route('approvals.reject', selectedTransaction.value.id), {
        onSuccess: () => {
            closeRejectModal();
        },
    });
};

// View proof image
const viewProof = (url) => {
    window.open(url, '_blank');
};
</script>

<template>
    <Head title="Persetujuan Transaksi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl dark:text-white leading-tight">
                ✅ Menunggu Persetujuan
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Info Alert -->
                <div class="mb-6 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-lg">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl">⚠️</span>
                        <div>
                            <h3 class="font-semibold text-amber-900">Transaksi Menunggu Persetujuan Anda</h3>
                            <p class="text-sm text-amber-800 mt-1">
                                Pengeluaran di atas <strong>Rp {{ threshold.toLocaleString('id-ID') }}</strong> memerlukan persetujuan Ketua.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pending Transactions List -->
                <div v-if="pendingTransactions.length > 0" class="space-y-4">
                    <Card
                        v-for="transaction in pendingTransactions"
                        :key="transaction.id"
                        padding="lg"
                        hoverable
                    >
                        <div class="flex items-start justify-between gap-6">
                            <!-- Transaction Info -->
                            <div class="flex-1">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-800 dark:text-white">{{ transaction.category }}</h3>
                                        <p class="text-sm text-slate-500 mt-1">
                                            Diajukan oleh: <strong>{{ transaction.verified_by_name }}</strong>
                                        </p>
                                    </div>
                                    <Badge variant="warning" size="lg" dot>PENDING</Badge>
                                </div>

                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <div class="text-xs text-slate-500 uppercase">Tanggal</div>
                                        <div class="text-lg font-semibold text-slate-700 dark:text-slate-300">{{ transaction.formatted_date }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-500 uppercase">Jumlah</div>
                                        <div class="text-2xl font-bold text-red-600">{{ transaction.formatted_amount }}</div>
                                    </div>
                                </div>

                                <div v-if="transaction.description" class="mb-4">
                                    <div class="text-xs text-slate-500 uppercase mb-1">Keterangan</div>
                                    <p class="text-slate-700 dark:text-slate-300">{{ transaction.description }}</p>
                                </div>

                                <!-- Proof Thumbnail -->
                                <div v-if="transaction.proof_url" class="mb-4">
                                    <div class="text-xs text-slate-500 uppercase mb-2">Bukti Transaksi</div>
                                    <button
                                        @click="viewProof(transaction.proof_url)"
                                        class="relative group"
                                    >
                                        <img
                                            :src="transaction.proof_url"
                                            alt="Proof"
                                            class="h-32 rounded-lg border-2 border-slate-200 group-hover:border-primary-500 transition-colors"
                                        />
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 rounded-lg transition-colors flex items-center justify-center">
                                            <span class="text-white opacity-0 group-hover:opacity-100 text-sm font-semibold">🔍 Lihat</span>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col gap-3 min-w-[160px]">
                                <button
                                    @click="approve(transaction)"
                                    class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-4 px-6 rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105 text-lg"
                                >
                                    ✓ Setujui
                                </button>
                                <button
                                    @click="openRejectModal(transaction)"
                                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-4 px-6 rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105 text-lg"
                                >
                                    ✗ Tolak
                                </button>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Empty State -->
                <Card v-else padding="xl">
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">🎉</div>
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">Tidak Ada Transaksi Pending</h3>
                        <p class="text-slate-600 dark:text-slate-400">Semua transaksi sudah disetujui atau ditolak.</p>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Rejection Modal -->
        <TransitionRoot as="template" :show="showRejectModal">
            <Dialog as="div" class="relative z-50" @close="closeRejectModal">
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-slate-900/75 transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <TransitionChild
                            as="template"
                            enter="ease-out duration-300"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                        >
                            <DialogPanel class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 shadow-2xl transition-all w-full max-w-md">
                                <div class="bg-red-500 px-6 py-4">
                                    <DialogTitle class="text-xl font-semibold text-white">
                                        Tolak Transaksi
                                    </DialogTitle>
                                </div>

                                <div class="p-6">
                                    <p v-if="selectedTransaction" class="text-slate-700 dark:text-slate-300 mb-4">
                                        Anda akan menolak pengeluaran <strong>{{ selectedTransaction.category }}</strong> 
                                        sebesar <strong class="text-red-600 dark:text-red-400">{{ selectedTransaction.formatted_amount }}</strong>.
                                    </p>

                                    <label class="block mb-2 font-semibold text-slate-700 dark:text-slate-300">
                                        Alasan Penolakan <span class="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        v-model="rejectForm.rejection_reason"
                                        rows="4"
                                        class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:border-red-500 focus:ring-red-500"
                                        placeholder="Jelaskan alasan penolakan dengan jelas..."
                                        required
                                    ></textarea>
                                    <p v-if="rejectForm.errors.rejection_reason" class="mt-1 text-sm text-red-600">
                                        {{ rejectForm.errors.rejection_reason }}
                                    </p>
                                </div>

                                <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-4 flex justify-end gap-3">
                                    <button
                                        @click="closeRejectModal"
                                        class="px-6 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-lg transition-colors"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        @click="reject"
                                        :disabled="rejectForm.processing"
                                        class="px-6 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition-colors disabled:opacity-50"
                                    >
                                        <span v-if="rejectForm.processing">Memproses...</span>
                                        <span v-else>Tolak Transaksi</span>
                                    </button>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </AuthenticatedLayout>
</template>
