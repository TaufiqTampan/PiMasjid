<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { UserIcon, EnvelopeIcon, LockClosedIcon, PhoneIcon } from '@heroicons/vue/24/outline';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    website_hp: '',
    hp_time: Math.floor(Date.now() / 1000),
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Daftar Akun" />

        <div class="mb-10 text-center">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white font-arabic tracking-wide mb-2 uppercase italic text-primary-600">Ahlan Wa Sahlan</h1>
            <p class="text-gray-500 dark:text-gray-400 font-medium">
                Bergabunglah dalam Ekosistem Digital Masjid
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Honeypot anti-spam hidden inputs -->
            <div style="display: none !important;" aria-hidden="true">
                <input type="text" name="website_hp" v-model="form.website_hp" tabindex="-1" autocomplete="off" />
                <input type="hidden" name="hp_time" v-model="form.hp_time" />
            </div>
            <div>
                <InputLabel for="name" value="Nama Lengkap" class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5 ml-1" />
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary-500 transition-colors">
                        <UserIcon class="h-5 w-5" />
                    </div>
                    <TextInput
                        id="name"
                        type="text"
                        class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-gray-800/50 border-gray-200 dark:border-gray-700 focus:border-primary-500 focus:ring-4 focus:ring-primary-100 dark:focus:ring-primary-900/30 rounded-2xl transition-all duration-200 text-gray-900 dark:text-white placeholder-gray-400 shadow-sm"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Masukkan nama lengkap Anda"
                    />
                </div>
                <InputError class="mt-2 ml-1" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5 ml-1" />
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary-500 transition-colors">
                        <EnvelopeIcon class="h-5 w-5" />
                    </div>
                    <TextInput
                        id="email"
                        type="email"
                        class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-gray-800/50 border-gray-200 dark:border-gray-700 focus:border-primary-500 focus:ring-4 focus:ring-primary-100 dark:focus:ring-primary-900/30 rounded-2xl transition-all duration-200 text-gray-900 dark:text-white placeholder-gray-400 shadow-sm"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        placeholder="Masukkan alamat email"
                    />
                </div>
                <InputError class="mt-2 ml-1" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="phone" value="No. WhatsApp / HP (Opsional)" class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5 ml-1" />
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary-500 transition-colors">
                        <PhoneIcon class="h-5 w-5" />
                    </div>
                    <TextInput
                        id="phone"
                        type="tel"
                        class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-gray-800/50 border-gray-200 dark:border-gray-700 focus:border-primary-500 focus:ring-4 focus:ring-primary-100 dark:focus:ring-primary-900/30 rounded-2xl transition-all duration-200 text-gray-900 dark:text-white placeholder-gray-400 shadow-sm"
                        v-model="form.phone"
                        autocomplete="tel"
                        placeholder="08xxxxxxxxxx"
                    />
                </div>
                <InputError class="mt-2 ml-1" :message="form.errors.phone" />
            </div>

            <div>
                <InputLabel for="password" value="Password" class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5 ml-1" />
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary-500 transition-colors">
                        <LockClosedIcon class="h-5 w-5" />
                    </div>
                    <TextInput
                        id="password"
                        type="password"
                        class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-gray-800/50 border-gray-200 dark:border-gray-700 focus:border-primary-500 focus:ring-4 focus:ring-primary-100 dark:focus:ring-primary-900/30 rounded-2xl transition-all duration-200 text-gray-900 dark:text-white placeholder-gray-400 shadow-sm"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                        placeholder="Buat password minimal 8 karakter"
                    />
                </div>
                <InputError class="mt-2 ml-1" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Konfirmasi Password" class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5 ml-1" />
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary-500 transition-colors">
                        <LockClosedIcon class="h-5 w-5" />
                    </div>
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-gray-800/50 border-gray-200 dark:border-gray-700 focus:border-primary-500 focus:ring-4 focus:ring-primary-100 dark:focus:ring-primary-900/30 rounded-2xl transition-all duration-200 text-gray-900 dark:text-white placeholder-gray-400 shadow-sm"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Ulangi password Anda"
                    />
                </div>
                <InputError class="mt-2 ml-1" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-4">
                <PrimaryButton
                    class="w-full flex items-center justify-center py-4 bg-primary-600 hover:bg-primary-700 text-white text-lg font-bold rounded-2xl shadow-islamic transition-all duration-300 transform active:scale-95 group relative overflow-hidden"
                    :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <span v-if="!form.processing" class="relative z-10 flex items-center gap-2">
                        Daftar Akun
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 transition-transform group-hover:translate-x-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </span>
                    <span v-else class="relative z-10 flex items-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Mendaftar...
                    </span>
                </PrimaryButton>
            </div>

            <div class="mt-8 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Sudah punya akun? 
                    <Link :href="route('login')" class="text-primary-600 font-bold hover:text-primary-700 transition-colors">Masuk di sini</Link>
                </p>
            </div>
        </form>
    </AuthLayout>
</template>
