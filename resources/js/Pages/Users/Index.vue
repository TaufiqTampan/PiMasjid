<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import ModernTable from '@/Components/ModernTable.vue';
import Modal from '@/Components/Modal.vue';
import { PlusIcon } from '@heroicons/vue/20/solid';
import { TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    users: Array,
    isImpersonating: Boolean,
});

const isModalOpen = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'marbot',
    is_active: true,
});

const openModal = () => {
    form.reset();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    form.post(route('users.store'), {
        onSuccess: () => closeModal(),
    });
};

// Impersonate user
const impersonate = (userId) => {
    if (confirm('Login sebagai user ini?')) {
        useForm({}).post(route('users.impersonate', userId));
    }
};

// Stop impersonation
const stopImpersonation = () => {
    useForm({}).post(route('users.stopImpersonation'));
};

// Delete user
const deleteUser = (userId) => {
    if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
        useForm({}).delete(route('users.destroy', userId));
    }
};

// Table columns
const columns = [
    { key: 'name', label: 'Nama', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'role_label', label: 'Role', sortable: false },
    { key: 'is_active', label: 'Status', sortable: false },
    { key: 'created_at', label: 'Terdaftar', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false },
];
</script>

<template>
    <Head title="User Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-2xl leading-tight text-slate-900 dark:text-white">
                    👥 User Management
                </h2>
                <div class="flex gap-3">
                    <button
                        @click="openModal"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors shadow-sm"
                    >
                        <PlusIcon class="w-5 h-5" />
                        Tambah User
                    </button>
                    <button
                        v-if="isImpersonating"
                        @click="stopImpersonation"
                        class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors"
                    >
                        ← Kembali ke Admin
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Impersonation Alert -->
                <div v-if="isImpersonating" class="mb-6 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-lg">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">⚠️</span>
                        <div>
                            <h3 class="font-semibold text-amber-900">Mode Impersonation Aktif</h3>
                            <p class="text-sm text-amber-800">Anda sedang login sebagai user lain. Klik "Kembali ke Admin" untuk kembali.</p>
                        </div>
                    </div>
                </div>

                <Card padding="sm">
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Daftar Pengguna</h3>
                        <p class="text-slate-600 dark:text-slate-400 mb-4">Kelola akses pengguna dan impersonate untuk testing.</p>
                    </div>
                    
                    <ModernTable
                        :columns="columns"
                        :data="users"
                        hoverable
                        striped
                    >
                        <template #cell-role_label="{ value, row }">
                            <Badge
                                :variant="row.role === 'super_admin' ? 'primary' : row.role === 'ketua' ? 'info' : row.role === 'bendahara' ? 'success' : 'neutral'"
                                size="sm"
                            >
                                {{ value }}
                            </Badge>
                        </template>

                        <template #cell-is_active="{ value }">
                            <Badge :variant="value ? 'success' : 'danger'" size="sm" dot>
                                {{ value ? 'Active' : 'Inactive' }}
                            </Badge>
                        </template>

                        <template #cell-actions="{ row }">
                            <div class="flex items-center gap-2">
                                <button
                                    @click="impersonate(row.id)"
                                    class="text-sm bg-primary-100 text-primary-700 hover:bg-primary-200 px-3 py-1 rounded-md transition-colors"
                                >
                                    🔑 Login As
                                </button>
                                <button
                                    @click="deleteUser(row.id)"
                                    class="p-1 text-red-600 hover:bg-red-50 rounded transition-colors"
                                    title="Hapus User"
                                >
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </template>
                    </ModernTable>
                </Card>

                <!-- Info Card -->
                <Card padding="lg" class="mt-6">
                    <div class="flex items-start gap-4">
                        <span class="text-4xl">💡</span>
                        <div>
                            <h4 class="font-bold text-slate-800 dark:text-white mb-2">Tentang Impersonation</h4>
                            <ul class="text-sm text-slate-600 dark:text-slate-400 space-y-1">
                                <li>• <strong>Login As:</strong> Masuk sebagai user lain untuk testing tanpa password</li>
                                <li>• <strong>Segregation:</strong> Setiap role memiliki akses berbeda (bendahara, ketua, marbot)</li>
                                <li>• <strong>Audit:</strong> Semua aktivitas impersonation tercatat di session</li>
                                <li>• <strong>Security:</strong> Hanya Super Admin yang bisa impersonate</li>
                            </ul>
                        </div>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Create User Modal -->
        <Modal :show="isModalOpen" maxWidth="2xl" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-slate-900 dark:text-white mb-6">
                    Tambah User Baru
                </h2>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap</label>
                        <input v-model="form.name" type="text" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500" placeholder="Nama lengkap user">
                        <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
                        <input v-model="form.email" type="email" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500" placeholder="email@example.com">
                        <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Password</label>
                        <input v-model="form.password" type="password" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500" placeholder="Minimal 8 karakter">
                        <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Role</label>
                        <select v-model="form.role" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="marbot">Marbot</option>
                            <option value="bendahara">Bendahara</option>
                            <option value="ketua">Ketua</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                        <div v-if="form.errors.role" class="text-red-500 text-sm mt-1">{{ form.errors.role }}</div>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center gap-2">
                        <input v-model="form.is_active" type="checkbox" id="is_active" class="rounded text-emerald-600 focus:ring-emerald-500">
                        <label for="is_active" class="text-sm text-slate-700">User Aktif</label>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="closeModal" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
