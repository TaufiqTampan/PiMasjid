<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Sidebar from '@/Components/Sidebar.vue';
import { Link } from '@inertiajs/vue3';
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline';

const showingSidebar = ref(false);

const stopImpersonation = () => {
    useForm({}).post(route('users.stopImpersonation'));
};
</script>

<template>
    <div class="h-screen w-screen bg-slate-50 dark:bg-slate-900 transition-colors relative flex overflow-hidden">
        
        <!-- Mobile Sidebar Overlay -->
        <div 
            v-if="showingSidebar" 
            class="fixed inset-0 bg-black/60 z-40 lg:hidden backdrop-blur-sm transition-opacity"
            @click="showingSidebar = false"
        ></div>

        <!-- Sidebar -->
        <aside 
            class="fixed lg:static inset-y-0 left-0 z-50 w-64 h-screen bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transform transition-transform duration-300 lg:transform-none lg:block flex flex-col shrink-0 overflow-hidden"
            :class="showingSidebar ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:shadow-none'"
        >
            <Sidebar />
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
            
            <!-- Top Header -->
            <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 h-16 flex items-center justify-between px-4 lg:px-8 shadow-sm flex-shrink-0 z-30">
                <div class="flex items-center gap-4">
                    <button 
                        @click="showingSidebar = true" 
                        class="p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg lg:hidden"
                    >
                        <Bars3Icon class="w-6 h-6" />
                    </button>
                    <Link :href="route('dashboard')" class="lg:hidden">
                        <ApplicationLogo class="block h-8 w-auto fill-current text-emerald-600" />
                    </Link>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-lg border border-transparent bg-white dark:bg-slate-800 px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 transition hover:text-slate-900 dark:hover:text-white focus:outline-none"
                                >
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 flex items-center justify-center font-bold text-xs uppercase">
                                        {{ $page.props.auth.user.name.charAt(0) }}
                                    </div>
                                    <span class="hidden md:inline">{{ $page.props.auth.user.name }}</span>
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </button>
                            </template>

                            <template #content>
                                <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-700 md:hidden">
                                    <p class="text-xs font-semibold text-slate-400 uppercase">User</p>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200 truncate">{{ $page.props.auth.user.name }}</p>
                                </div>
                                <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button"> Log Out </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <!-- Impersonation Banner -->
            <div v-if="$page.props.isImpersonating" class="bg-amber-500 text-white px-4 lg:px-8 py-2 flex items-center justify-between shadow-md flex-shrink-0">
                <div class="flex items-center gap-2 text-sm font-medium">
                    <span>⚠️ Mode Impersonation: <strong>{{ $page.props.auth.user.name }}</strong></span>
                </div>
                <button @click="stopImpersonation" class="bg-white/20 hover:bg-white/30 px-3 py-1 rounded text-xs font-bold transition-colors">
                    Kembali
                </button>
            </div>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 transition-colors custom-scrollbar overscroll-contain">
                <div v-if="$slots.header" class="bg-white dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700 shadow-sm">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 text-slate-900 dark:text-white">
                        <slot name="header" />
                    </div>
                </div>

                <div class="p-4 sm:p-6 lg:p-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar {
    overscroll-behavior: contain;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155;
}
</style>
