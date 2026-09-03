<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { EnvelopeIcon, LockClosedIcon, EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
    website_hp: '',
    hp_time: Math.floor(Date.now() / 1000),
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Masuk Akun" />

        <div class="mb-10 text-center">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white font-arabic tracking-wide mb-2 uppercase italic text-primary-600">Bismillah</h1>
            <p class="text-gray-500 dark:text-gray-400 font-medium">
                Satu Akun untuk Seluruh Layanan Masjid
            </p>
        </div>

        <div v-if="status" class="mb-6 text-sm font-medium text-green-700 bg-green-50 dark:bg-green-900/20 dark:text-green-400 p-4 rounded-xl border border-green-100 dark:border-green-800 animate-fade-in">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Honeypot anti-spam hidden inputs -->
            <div style="display: none !important;" aria-hidden="true">
                <input type="text" name="website_hp" v-model="form.website_hp" tabindex="-1" autocomplete="off" />
                <input type="hidden" name="hp_time" v-model="form.hp_time" />
            </div>
            <div>
                <InputLabel for="email" value="Email atau Nomor Telepon" class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-1.5 ml-1" />
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary-500 transition-colors">
                        <EnvelopeIcon class="h-5 w-5" />
                    </div>
                    <TextInput
                        id="email"
                        type="text"
                        class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-gray-800/50 border-gray-200 dark:border-gray-700 focus:border-primary-500 focus:ring-4 focus:ring-primary-100 dark:focus:ring-primary-900/30 rounded-2xl transition-all duration-200 text-gray-900 dark:text-white placeholder-gray-400 shadow-sm"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="Masukkan email atau no. WhatsApp (08xxx)"
                    />
                </div>
                <InputError class="mt-2 ml-1" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5 ml-1">
                    <InputLabel for="password" value="Password" class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs text-primary-600 hover:text-primary-700 font-bold hover:underline"
                    >
                        Lupa?
                    </Link>
                </div>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary-500 transition-colors">
                        <LockClosedIcon class="h-5 w-5" />
                    </div>
                    <TextInput
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="block w-full pl-11 pr-12 py-3.5 bg-gray-50 dark:bg-gray-800/50 border-gray-200 dark:border-gray-700 focus:border-primary-500 focus:ring-4 focus:ring-primary-100 dark:focus:ring-primary-900/30 rounded-2xl transition-all duration-200 text-gray-900 dark:text-white placeholder-gray-400 shadow-sm"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan password"
                    />
                    <button 
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors focus:outline-none"
                    >
                        <EyeIcon v-if="!showPassword" class="h-5 w-5" />
                        <EyeSlashIcon v-else class="h-5 w-5" />
                    </button>
                </div>
                <InputError class="mt-2 ml-1" :message="form.errors.password" />
            </div>

            <div class="flex items-center ml-1">
                <label class="flex items-center cursor-pointer group">
                    <Checkbox name="remember" v-model:checked="form.remember" class="w-5 h-5 rounded-lg border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 transition-all cursor-pointer" />
                    <span class="ms-3 text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Ingat akun saya</span>
                </label>
            </div>

            <div class="pt-4">
                <PrimaryButton
                    class="w-full flex items-center justify-center py-4 bg-primary-600 hover:bg-primary-700 text-white text-lg font-bold rounded-2xl shadow-islamic transition-all duration-300 transform active:scale-95 group relative overflow-hidden"
                    :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <span v-if="!form.processing" class="relative z-10 flex items-center gap-2">
                        Masuk Sekarang
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 transition-transform group-hover:translate-x-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </span>
                    <span v-else class="relative z-10 flex items-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                </PrimaryButton>
            </div>
            
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Belum punya akun? 
                    <Link :href="route('register')" class="text-primary-600 font-bold hover:text-primary-700 transition-colors">Daftar di sini</Link>
                </p>
            </div>
        </form>
    </AuthLayout>
</template>
