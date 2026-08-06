<script setup>
import { Link } from '@inertiajs/vue3';
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { 
    XMarkIcon,
    BuildingLibraryIcon,
    InformationCircleIcon,
    BanknotesIcon,
    BuildingOfficeIcon,
    HeartIcon,
    ClockIcon,
    CalendarIcon,
    PhoneIcon,
    AcademicCapIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    open: {
        type: Boolean,
        required: true
    }
});

const emit = defineEmits(['close']);

const closeDrawer = () => {
    emit('close');
};

const menuSections = [
    {
        title: 'Profil',
        icon: InformationCircleIcon,
        items: [
            { label: 'Tentang Kami', href: '/profil/tentang', icon: BuildingLibraryIcon },
            { label: 'Struktur Organisasi', href: '/profil/struktur', icon: BuildingOfficeIcon }
        ]
    },
    {
        title: 'Transparansi',
        icon: BanknotesIcon,
        items: [
            { label: 'Laporan Keuangan', href: '/transparansi/keuangan', icon: BanknotesIcon },
            { label: 'Inventaris Aset', href: '/transparansi/aset', icon: BuildingOfficeIcon }
        ]
    },
    {
        title: 'Layanan',
        icon: HeartIcon,
        items: [
            { label: 'Jadwal Sholat', href: '/ibadah/jadwal', icon: ClockIcon },
            { label: 'Agenda Kegiatan', href: '/ibadah/agenda', icon: CalendarIcon },
            { label: 'Jadwal Jumat', href: '/ibadah/jumat', icon: CalendarIcon },
            { label: 'Tarbiyah & Kajian', href: '/tarbiyah', icon: AcademicCapIcon }
        ]
    }
];
</script>

<template>
    <TransitionRoot as="template" :show="open">
        <Dialog as="div" class="relative z-50" @close="closeDrawer">
            <!-- Backdrop -->
            <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" />
            </TransitionChild>

            <!-- Drawer Panel -->
            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-x-0 bottom-0 flex max-h-[80vh]">
                        <TransitionChild
                            as="template"
                            enter="transform transition ease-in-out duration-300"
                            enter-from="translate-y-full"
                            enter-to="translate-y-0"
                            leave="transform transition ease-in-out duration-200"
                            leave-from="translate-y-0"
                            leave-to="translate-y-full"
                        >
                            <DialogPanel class="pointer-events-auto w-screen">
                                <div class="flex h-full flex-col bg-white rounded-t-3xl shadow-2xl">
                                    <!-- Header -->
                                    <div class="px-6 py-6 border-b border-slate-100">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h2 class="text-2xl font-bold text-slate-900">Menu Navigasi</h2>
                                                <p class="text-sm text-slate-500 mt-1">Jelajahi informasi masjid</p>
                                            </div>
                                            <button
                                                type="button"
                                                class="rounded-full bg-slate-100 p-2 text-slate-600 hover:bg-slate-200 transition-colors"
                                                @click="closeDrawer"
                                            >
                                                <XMarkIcon class="h-6 w-6" />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 overflow-y-auto px-6 py-6">
                                        <div class="space-y-8">
                                            <!-- Each Section -->
                                            <div v-for="section in menuSections" :key="section.title">
                                                <div class="flex items-center gap-2 mb-4">
                                                    <component :is="section.icon" class="w-5 h-5 text-emerald-600" />
                                                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                                                        {{ section.title }}
                                                    </h3>
                                                </div>
                                                <div class="space-y-2">
                                                    <Link
                                                        v-for="item in section.items"
                                                        :key="item.href"
                                                        :href="item.href"
                                                        @click="closeDrawer"
                                                        class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:border-emerald-200 border border-transparent transition-all duration-200 group"
                                                    >
                                                        <div class="p-2 rounded-lg bg-white group-hover:bg-emerald-100 transition-colors">
                                                            <component :is="item.icon" class="w-5 h-5 text-slate-600 group-hover:text-emerald-600 transition-colors" />
                                                        </div>
                                                        <span class="text-slate-700 font-medium group-hover:text-emerald-700 transition-colors">
                                                            {{ item.label }}
                                                        </span>
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer -->
                                    <div class="border-t border-slate-100 px-6 py-4 bg-slate-50">
                                        <a 
                                            href="tel:+62123456789" 
                                            class="flex items-center justify-center gap-2 text-emerald-600 hover:text-emerald-700 font-semibold transition-colors"
                                        >
                                            <PhoneIcon class="w-5 h-5" />
                                            <span>Hubungi Kami</span>
                                        </a>
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
