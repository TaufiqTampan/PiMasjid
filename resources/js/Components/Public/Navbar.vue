<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Menu, MenuButton, MenuItems, MenuItem, Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { UsersIcon, HeartIcon, BanknotesIcon } from '@heroicons/vue/24/outline';

// No props needed for modal anymore as it's internal
const props = defineProps({
    activePage: String,
    transparentOnTop: {
        type: Boolean,
        default: true
    }
});

const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);
const showDonationModal = ref(false);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 50;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

const navigationItems = [
    { name: 'Beranda', href: '/', type: 'link' },
    {
        name: 'Profil',
        type: 'dropdown',
        items: [
            { name: 'Tentang Kami', href: '/profil/tentang' },
            { name: 'Struktur Pengurus', href: '/profil/struktur' },
        ],
    },
    {
        name: 'Transparansi',
        type: 'dropdown',
        items: [
            { name: 'Laporan Keuangan', href: '/transparansi/keuangan' },
            { name: 'Daftar Aset & Wakaf', href: '/transparansi/aset' },
        ],
    },
    {
        name: 'Layanan',
        type: 'dropdown',
        items: [
            { name: '🌙 Kalender Ramadhan & Imsakiyah', href: '/ramadhan' },
            { name: 'Galeri Kegiatan', href: '/galeri' },
            { name: 'Jadwal Sholat', href: '/ibadah/jadwal' },
            { name: 'Agenda', href: '/ibadah/agenda' },
            { name: 'Petugas Jumat', href: '/ibadah/jumat' },
            { name: '🏛️ Fasilitas & Booking', href: '/fasilitas' },
            { name: '🧭 Arah Kiblat', href: '/ibadah/kiblat' },
            { name: '📖 Al-Quran Digital', href: '/quran' },
            { name: '🎓 Tarbiyah & Kajian', href: '/tarbiyah' },
            { name: '🕌 Info Zakat', href: '/info/zakat' },
            { name: '🐑 Daftar Qurban', href: '/info/qurban' },
            { name: '🍱 Lumbung Pangan', href: '/lumbung-pangan' },
        ],
    },
];

// Expose open modal function for parent components if needed (optional)
defineExpose({ openDonationModal: () => showDonationModal.value = true });
</script>

<template>
    <!-- Desktop & Mobile Navbar -->
    <nav
        :class="[
            'fixed top-0 left-0 right-0 z-50 transition-all duration-500',
            (isScrolled || !transparentOnTop) 
                ? 'bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border-b border-slate-100 dark:border-slate-800 py-2 shadow-xl shadow-slate-200/50 dark:shadow-none' 
                : 'bg-transparent py-4 text-white',
        ]"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-3 font-black text-2xl tracking-tighter transition-all hover:scale-105">
                    <div class="flex items-center justify-center p-1 bg-white/10 backdrop-blur-sm rounded-xl">
                        <img 
                            v-if="$page.props.settings?.logo_path" 
                            :src="$page.props.settings.logo_path" 
                            alt="Logo" 
                            class="h-10 w-auto object-contain"
                        />
                        <span v-else class="text-2xl">🕌</span>
                    </div>
                    <span :class="(isScrolled || !transparentOnTop) ? 'text-bakri-teal' : 'text-white'" class="hidden sm:block">
                        {{ $page.props.settings?.site_name || 'pimasjid' }}
                    </span>
                </Link>
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-2">
                    <template v-for="item in navigationItems" :key="item.name">
                        <Link 
                            v-if="item.type === 'link'"
                            :href="item.href"
                            :class="[
                                (isScrolled || !transparentOnTop) 
                                    ? item.current ? 'text-bakri-teal bg-bakri-teal/5' : 'text-slate-600 hover:text-bakri-teal hover:bg-bakri-teal/5' 
                                    : 'text-white/90 hover:text-white hover:bg-white/10'
                            ]"
                            class="px-4 py-2 rounded-full text-sm font-bold transition-all uppercase tracking-wide"
                        >
                            {{ item.name }}
                        </Link>

                        <!-- Dropdown Menu -->
                        <Menu v-else as="div" class="relative">
                            <MenuButton 
                                :class="[
                                    'px-4 py-2 rounded-full transition-all duration-300 font-bold text-sm uppercase tracking-wide flex items-center gap-1.5',
                                    (isScrolled || !transparentOnTop) 
                                        ? 'text-slate-600 hover:text-bakri-teal hover:bg-bakri-teal/5' 
                                        : 'text-white/90 hover:text-white hover:bg-white/10'
                                ]"
                            >
                                {{ item.name }}
                                <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </MenuButton>
                            <transition
                                enter-active-class="transition duration-100 ease-out"
                                enter-from-class="transform scale-95 opacity-0"
                                enter-to-class="transform scale-100 opacity-100"
                                leave-active-class="transition duration-75 ease-in"
                                leave-from-class="transform scale-100 opacity-100"
                                leave-to-class="transform scale-95 opacity-0"
                            >
                                <MenuItems class="absolute left-0 mt-3 w-64 origin-top-left rounded-2xl bg-white dark:bg-slate-900 shadow-2xl ring-1 ring-slate-200 dark:ring-slate-800 focus:outline-none py-3 overflow-hidden border-t-4 border-bakri-teal">
                                    <div class="px-4 py-2 mb-1 border-b border-slate-50 dark:border-slate-800">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ item.name }}</span>
                                    </div>
                                    <MenuItem v-for="subItem in item.items" :key="subItem.name" v-slot="{ active }" as="template">
                                        <component
                                            :is="subItem.external ? 'a' : Link"
                                            :href="subItem.external ? subItem.href : (subItem.href.startsWith('#') && $page.url !== '/' ? '/' + subItem.href : subItem.href)"
                                            :class="[
                                                active ? 'bg-bakri-teal/5 text-bakri-teal' : 'text-slate-700 dark:text-slate-300',
                                                'flex items-center gap-3 px-4 py-2.5 text-sm font-bold transition-colors uppercase tracking-tight',
                                            ]"
                                        >
                                            <div class="w-1.5 h-1.5 rounded-full bg-bakri-teal opacity-0 transition-opacity" :class="{'opacity-100': active}"></div>
                                            {{ subItem.name }}
                                        </component>
                                    </MenuItem>
                                </MenuItems>
                            </transition>
                        </Menu>
                    </template>

                    <!-- User / Login -->
                    <div class="h-6 w-px bg-slate-200 dark:bg-slate-700 mx-3 hidden lg:block"></div>

                    <!-- Donasi Button -->
                    <button
                        @click="showDonationModal = true"
                        class="group relative inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-amber-400 to-amber-600 text-white rounded-full font-black text-xs uppercase tracking-widest shadow-xl shadow-amber-500/30 hover:shadow-amber-500/50 hover:-translate-y-1 transition-all duration-300 ring-4 ring-amber-500/10 hover:ring-amber-500/20 active:scale-95 overflow-hidden"
                    >
                        <!-- Shine effect -->
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -translate-x-full group-hover:animate-shine"></div>
                        
                        <HeartIcon class="w-4 h-4 text-white animate-pulse" />
                        <span class="relative">Dana Masjid</span>
                    </button>

                    <Link 
                        href="/login" 
                        class="btn btn-square btn-ghost btn-sm rounded-xl ml-1"
                        :class="(isScrolled || !transparentOnTop) ? 'text-slate-400 hover:text-emerald-600' : 'text-white/60 hover:text-white'"
                    >
                        <UsersIcon class="w-5 h-5" />
                    </Link>
                </div>

                <button
                    @click="isMobileMenuOpen = !isMobileMenuOpen"
                    class="btn btn-square btn-ghost lg:hidden"
                    :class="(isScrolled || !transparentOnTop) ? 'text-slate-900' : 'text-white'"
                >
                    <svg v-if="!isMobileMenuOpen" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div
                v-if="isMobileMenuOpen"
                class="md:hidden border-t border-slate-200 py-4 space-y-2 bg-white rounded-b-xl shadow-lg absolute left-0 right-0 px-4"
            >
                <template v-for="item in navigationItems" :key="item.name">
                    <Link
                        v-if="item.type === 'link'"
                        :href="item.href"
                        class="block px-4 py-2 text-slate-700 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg font-medium"
                        @click="isMobileMenuOpen = false"
                    >
                        {{ item.name }}
                    </Link>
                    
                    <div v-else class="space-y-1">
                        <div class="px-4 py-2 text-slate-800 font-bold text-sm">{{ item.name }}</div>
                        <component
                            :is="subItem.external ? 'a' : Link"
                            v-for="subItem in item.items"
                            :key="subItem.name"
                            :href="subItem.external ? subItem.href : (subItem.href.startsWith('#') && $page.url !== '/' ? '/' + subItem.href : subItem.href)"
                            class="block pl-8 pr-4 py-2 text-sm text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg"
                            @click="isMobileMenuOpen = false"
                        >
                            {{ subItem.name }}
                        </component>
                    </div>
                </template>
                
                <button
                    @click="showDonationModal = true; isMobileMenuOpen = false"
                    class="w-full bg-gradient-to-r from-amber-500 to-amber-600 text-white px-4 py-3 rounded-lg font-bold mt-4"
                >
                    💰 Donasi Sekarang
                </button>
            </div>
        </div>
    </nav>

    <!-- Donation Modal (Self-Contained) -->
    <TransitionRoot as="template" :show="showDonationModal">
        <Dialog as="div" class="relative z-[60]" @close="showDonationModal = false">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-slate-900/75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <span class="text-2xl">💰</span>
                                    </div>
                                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                        <h3 class="text-lg font-semibold leading-6 text-slate-900" id="modal-title">Infaq & Shodaqoh</h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-slate-500 mb-4">
                                                Salurkan donasi terbaik Anda untuk kemakmuran masjid melalui scan QRIS berikut:
                                            </p>
                                            <div class="bg-slate-100 p-4 rounded-xl flex items-center justify-center border-2 border-dashed border-slate-300">
                                                <!-- QR Code -->
                                                <div class="text-center w-full">
                                                    <img 
                                                        v-if="$page.props.settings?.donation_qris_image"
                                                        :src="$page.props.settings.donation_qris_image" 
                                                        alt="QRIS Masjid" 
                                                        class="mx-auto w-48 h-auto object-contain mb-3 rounded-lg shadow-sm" 
                                                    />
                                                    <img 
                                                        v-else
                                                        src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=example-donation-link" 
                                                        alt="QRIS Placeholder" 
                                                        class="mx-auto mix-blend-multiply w-48 h-48 opacity-50" 
                                                    />
                                                    
                                                    <p class="text-xs font-bold text-slate-500 mt-2 whitespace-pre-line leading-relaxed">
                                                        {{ $page.props.settings?.donation_bank_info || 'Bank Syariah Indonesia (BSI)\nNo. Rek: -\nA.n Masjid Al-Hidayah' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 sm:mt-0 sm:w-auto" @click="showDonationModal = false">Tutup</button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
