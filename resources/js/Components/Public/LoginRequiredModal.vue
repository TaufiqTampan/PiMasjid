<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { UserIcon, ArrowRightOnRectangleIcon, UserPlusIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Akses Fitur Memerlukan Login',
    },
    message: {
        type: String,
        default: 'Untuk dapat menggunakan fitur ini, Anda perlu masuk (login) sebagai Jamaah terlebih dahulu.',
    },
    targetUrl: {
        type: String,
        default: '/',
    },
});

const emit = defineEmits(['close']);

function closeModal() {
    emit('close');
}

function goToLogin() {
    closeModal();
    router.visit(`/login?intended=${encodeURIComponent(props.targetUrl)}`);
}

function goToRegister() {
    closeModal();
    router.visit(`/register?intended=${encodeURIComponent(props.targetUrl)}`);
}
</script>

<template>
    <TransitionRoot appear :show="show" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-50">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-3xl bg-white dark:bg-slate-900 p-6 text-left align-middle shadow-2xl transition-all border border-slate-100 dark:border-slate-800">
                            <!-- Close Button -->
                            <button
                                @click="closeModal"
                                class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
                            >
                                <XMarkIcon class="w-5 h-5" />
                            </button>

                            <!-- Header Icon -->
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 mb-4 border border-emerald-100 dark:border-emerald-900/50">
                                <UserIcon class="w-7 h-7" />
                            </div>

                            <!-- Title & Message -->
                            <DialogTitle as="h3" class="text-xl font-bold text-center text-slate-900 dark:text-white">
                                {{ title }}
                            </DialogTitle>
                            
                            <p class="mt-2 text-sm text-center text-slate-600 dark:text-slate-400 leading-relaxed">
                                {{ message }}
                            </p>

                            <!-- Action Buttons -->
                            <div class="mt-6 flex flex-col gap-3">
                                <button
                                    type="button"
                                    @click="goToLogin"
                                    class="w-full inline-flex justify-center items-center gap-2 px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold text-sm transition-all shadow-lg shadow-emerald-600/30"
                                >
                                    <ArrowRightOnRectangleIcon class="w-5 h-5" />
                                    Login Sekarang
                                </button>

                                <button
                                    type="button"
                                    @click="goToRegister"
                                    class="w-full inline-flex justify-center items-center gap-2 px-5 py-3 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-sm transition-all"
                                >
                                    <UserPlusIcon class="w-5 h-5" />
                                    Daftar Akun Jamaah
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
