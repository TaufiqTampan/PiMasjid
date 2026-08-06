<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Dialog, DialogPanel, TransitionChild, TransitionRoot, DialogTitle } from '@headlessui/vue';
import { 
    ClockIcon, 
    CurrencyDollarIcon, 
    HeartIcon, 
    ArrowRightIcon,
    CalendarIcon,
    MapPinIcon,
    ArrowPathIcon,
    UsersIcon
} from '@heroicons/vue/24/outline';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { usePrayerTimes } from '@/Composables/usePrayerTimes';

// Define layout
defineOptions({
    layout: PublicLayout
});

const props = defineProps({
    prayerTimes: Object,
    nextPrayer: Object,
    financialSummary: Object,
    slides: Array,
    committee: Object,
    jumatSchedule: Object,
    posts: Array,
});


const layoutRef = ref(null);

const openDonationModal = () => {
    // Access layout's method via ref
    if (layoutRef.value) {
        layoutRef.value.openDonationModal();
    }
};

// Committee helpers
const coreLeadership = computed(() => {
    return props.committee?.Inti?.sort((a, b) => a.order - b.order) || [];
});

const divisions = computed(() => {
    const { Inti, ...rest } = props.committee || {};
    return rest;
});

// Chart Data (Simple CSS calculation)
const incomePercent = computed(() => {
    if (!props.financialSummary) return 50;
    const total = props.financialSummary.income + props.financialSummary.expense;
    return total > 0 ? (props.financialSummary.income / total) * 100 : 50;
});

// --- Shared Geolocation Logic ---
const { 
    currentPrayerTimes, 
    isLocating, 
    usingUserLocation, 
    getUserLocation 
} = usePrayerTimes(props.prayerTimes);

// Next Prayer Logic (remains local as it depends on display needs, or could be moved)
const currentNextPrayer = ref(props.nextPrayer);

const getNextPrayerFromSchedule = (schedule) => {
    const now = new Date();
    const currentTime = now.getHours() * 60 + now.getMinutes();
    const prayers = [
        { name: 'Subuh', time: schedule.subuh },
        { name: 'Dzuhur', time: schedule.dzuhur },
        { name: 'Ashar', time: schedule.ashar },
        { name: 'Maghrib', time: schedule.maghrib },
        { name: 'Isya', time: schedule.isya },
    ];

    for (const prayer of prayers) {
        if (!prayer.time) continue; // Defense
        const [hours, minutes] = prayer.time.split(':').map(Number);
        const prayerTime = hours * 60 + minutes;
        
        if (prayerTime > currentTime) {
            const diff = prayerTime - currentTime;
            const diffHours = Math.floor(diff / 60);
            const diffMinutes = diff % 60;
            const countdown = `-${diffHours}j ${diffMinutes}m`;
            
            return {
                name: prayer.name,
                time: prayer.time,
                countdown: countdown
            };
        }
    }
    return { name: 'Subuh', time: schedule.subuh, countdown: 'Besok' };
};

// Watch for changes in times to update next prayer
watch(currentPrayerTimes, (newTimes) => {
    if (newTimes) {
        currentNextPrayer.value = getNextPrayerFromSchedule(newTimes);
    }
});
</script>

<template>
    <Head title="Pusat Ibadah & Kegiatan Umat" />

    <PublicLayout ref="layoutRef">
        <!-- 1. Hero Section -->
        <div class="relative min-h-[700px] flex items-center justify-center overflow-hidden">
            <!-- Background Image with dynamic parallax-like effect -->
            <div class="absolute inset-0 z-0">
                <img 
                    :src="$page.props.settings?.hero_bg_image || 'https://images.unsplash.com/photo-1519817650390-64a93db51149?q=80&w=2000&auto=format&fit=crop'" 
                    alt="Masjid Background" 
                    class="w-full h-full object-cover scale-105"
                />
                <!-- Advanced Multi-layer Overlay - Bakri Teal #068d9e -->
                <div class="absolute inset-0 bg-[#068d9e] opacity-90"></div>
                <!-- Pattern Overlay -->
                <div class="absolute inset-0 opacity-[0.05] pointer-events-none bg-pattern-islamic bg-repeat"></div>
            </div>

            <!-- Content Container -->
            <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-32 lg:pt-32 lg:pb-48 text-center md:text-left">
                <div class="max-w-3xl">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white text-xs font-bold uppercase tracking-widest mb-6 animate-fade-in">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-bakri-lime opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-bakri-lime"></span>
                        </span>
                        Masjid Bakri: Pemuda Rabbani
                    </div>

                    <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-[1.1] tracking-tight animate-slide-up">
                        {{ $page.props.settings?.hero_title || 'Pusat Ibadah & Kegiatan Umat' }}
                    </h1>
                    
                    <p class="text-lg md:text-xl text-white/80 mb-10 font-light leading-relaxed animate-slide-up [animation-delay:200ms]">
                        {{ $page.props.settings?.hero_subtitle || 'Jembatan Tengah - Baubau' }}
                    </p>
                    
                    <div class="flex flex-wrap gap-4 items-center justify-center md:justify-start animate-slide-up [animation-delay:400ms]">
                        <button 
                            @click="openDonationModal"
                            class="btn bg-bakri-lime hover:bg-lime-500 text-bakri-navy btn-lg rounded-full shadow-lg shadow-bakri-navy/20 px-8 hover:-translate-y-1 transition-all border-none font-black"
                        >
                            <span>Infaq & Sedekah</span>
                            <HeartIcon class="w-5 h-5 ml-1" />
                        </button>
                        
                        <Link 
                            href="/ibadah/jadwal" 
                            class="btn btn-outline border-white/30 text-white hover:bg-white/10 btn-lg rounded-full px-8 backdrop-blur-sm hover:-translate-y-1 transition-all"
                        >
                            <span>Jadwal Sholat</span>
                            <ArrowRightIcon class="w-4 h-4 ml-1" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Quick Info Cards (Overlap) -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20 -mt-24 lg:-mt-32">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                <!-- Prayer Times Card -->
                <div class="card bg-white dark:bg-slate-800 shadow-2xl border-none overflow-hidden group hover:-translate-y-2 transition-all duration-300">
                    <div class="h-1.5 w-full bg-persian-blue"></div>
                    <div class="card-body p-6 lg:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="p-3 bg-persian-blue/5 dark:bg-persian-blue/10 rounded-2xl text-persian-blue group-hover:scale-110 transition-transform">
                                <ClockIcon v-if="!isLocating" class="w-7 h-7" />
                                <ArrowPathIcon v-else class="w-7 h-7 animate-spin" />
                            </div>
                            <div v-if="currentNextPrayer" class="text-right">
                                <span class="badge badge-soft bg-persian-blue/10 text-persian-blue border-none animate-pulse">{{ currentNextPrayer.countdown }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 uppercase tracking-tight">Waktu Sholat</h3>
                             <button 
                                @click="getUserLocation" 
                                class="btn btn-ghost btn-circle btn-sm text-slate-400 hover:text-persian-blue"
                            >
                                <MapPinIcon class="w-5 h-5" :class="{'text-persian-blue': usingUserLocation}" />
                            </button>
                        </div>
                        
                        <div v-if="isLocating" class="space-y-3">
                             <div class="h-10 bg-slate-100 dark:bg-slate-700 rounded-lg animate-pulse"></div>
                             <div class="h-4 bg-slate-100 dark:bg-slate-700 rounded-lg animate-pulse w-2/3"></div>
                        </div>
                        <div v-else>
                            <div v-if="currentNextPrayer" class="flex items-baseline gap-2 mb-1">
                                <span class="text-4xl font-black text-persian-navy dark:text-white tracking-tighter">{{ currentNextPrayer.name }}</span>
                                <span class="text-xl text-persian-blue/60 font-medium">{{ currentNextPrayer.time }}</span>
                            </div>
                            <p v-else class="text-slate-400 animate-pulse">Mengkalkulasi...</p>
                            
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1 my-2">
                                <span v-if="usingUserLocation" class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-persian-blue"></span> Lokasi Presisi</span>
                                <span v-else class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Jadwal Masjid</span>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-slate-50 dark:border-slate-700 flex justify-between items-center">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }) }}</span>
                            <Link href="/ibadah/jadwal" class="text-persian-blue text-sm font-black hover:gap-1 transition-all flex items-center gap-0.5">
                                Detail <ArrowRightIcon class="w-3.5 h-3.5" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Balance Card -->
                <div class="card bg-white dark:bg-slate-800 shadow-2xl border-none overflow-hidden group hover:-translate-y-2 transition-all duration-300">
                    <div class="h-1.5 w-full bg-persian-gold"></div>
                    <div class="card-body p-6 lg:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="p-3 bg-persian-gold/10 dark:bg-persian-gold/20 rounded-2xl text-persian-gold group-hover:scale-110 transition-transform">
                                <CurrencyDollarIcon class="w-7 h-7" />
                            </div>
                            <span class="badge badge-soft bg-persian-gold/10 text-persian-gold border-none uppercase text-[10px] font-bold tracking-wider">Update Harian</span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-2 uppercase tracking-tight">Kas Masjid</h3>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Saldo Saat Ini</span>
                            <span class="text-3xl lg:text-4xl font-black text-persian-navy dark:text-white tracking-tighter">
                                {{ financialSummary.formatted_balance }}
                            </span>
                        </div>

                        <div class="mt-6 pt-6 border-t border-slate-50 dark:border-slate-700 flex justify-between items-center">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Baitul Maal</span>
                            <Link href="/transparansi/keuangan" class="text-persian-gold text-sm font-black hover:gap-1 transition-all flex items-center gap-0.5">
                                Laporan <ArrowRightIcon class="w-3.5 h-3.5" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Services Card -->
                <div class="card bg-white dark:bg-slate-800 shadow-2xl border-none overflow-hidden group hover:-translate-y-2 transition-all duration-300">
                    <div class="h-1.5 w-full bg-persian-navy"></div>
                    <div class="card-body p-6 lg:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="p-3 bg-persian-navy/5 dark:bg-persian-navy/20 rounded-2xl text-persian-navy group-hover:scale-110 transition-transform">
                                <HeartIcon class="w-7 h-7" />
                            </div>
                            <span class="badge badge-soft bg-persian-navy/5 text-persian-navy border-none uppercase text-[10px] font-bold tracking-wider">Amanah Umat</span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-2 uppercase tracking-tight">Layanan Umat</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed line-clamp-2">
                            Program santunan, bantuan kesehatan, dan layanan sosial kemasyarakatan yang terintegrasi.
                        </p>

                        <div class="mt-auto pt-6 border-t border-slate-50 dark:border-slate-700 flex justify-between items-center">
                            <div class="flex -space-x-2">
                                <div class="w-7 h-7 rounded-full bg-persian-blue/10 border-2 border-white dark:border-slate-800 flex items-center justify-center text-[10px]">🕌</div>
                                <div class="w-7 h-7 rounded-full bg-persian-gold/10 border-2 border-white dark:border-slate-800 flex items-center justify-center text-[10px]">🐑</div>
                                <div class="w-7 h-7 rounded-full bg-persian-navy/10 border-2 border-white dark:border-slate-800 flex items-center justify-center text-[10px]">🍱</div>
                            </div>
                            <a href="#" class="text-persian-navy text-sm font-black hover:gap-1 transition-all flex items-center gap-0.5">
                                Selengkapnya <ArrowRightIcon class="w-3.5 h-3.5" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Transparency Section -->
        <section class="py-24 bg-bakri-teal/5 dark:bg-slate-950 overflow-hidden relative">
            <div class="absolute top-0 right-0 w-64 h-64 bg-bakri-teal/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-bakri-lime/10 rounded-full -ml-32 -mb-32 blur-3xl"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-16">
                    <span class="badge badge-soft bg-bakri-teal/10 text-bakri-teal border-none uppercase tracking-widest font-bold mb-3">Akuntabilitas</span>
                    <h2 class="text-3xl md:text-5xl font-black text-bakri-teal dark:text-white tracking-tight leading-none mb-4">Transparansi Keuangan</h2>
                    <p class="text-slate-500 dark:text-slate-400 max-w-2xl mx-auto text-sm font-medium">Setiap rupiah amanah jamaah kami kelola dengan profesional dan dilaporkan secara terbuka.</p>
                </div>

                <div class="card bg-white dark:bg-slate-900 shadow-2xl border-none p-0 overflow-hidden rounded-[2.5rem]">
                    <div class="grid grid-cols-1 lg:grid-cols-2">
                        <!-- Left: Visual Chart Area -->
                        <div class="p-10 lg:p-16 bg-slate-50 dark:bg-slate-800/50 flex flex-col items-center justify-center relative overflow-hidden">
                            <div class="relative w-72 h-72 lg:w-80 lg:h-80 group">
                                <!-- Donut Chart Approximation -->
                                <div 
                                    class="w-full h-full rounded-full transition-transform duration-700 group-hover:rotate-12 shadow-2xl"
                                    :style="`background: conic-gradient(#068d9e 0% ${incomePercent}%, #f43f5e ${incomePercent}% 100%);`"
                                ></div>
                                <div class="absolute inset-8 bg-white dark:bg-slate-900 rounded-full flex items-center justify-center flex-col shadow-inner">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Pemasukan</span>
                                    <span class="text-3xl lg:text-4xl font-black text-bakri-teal dark:text-white tracking-tighter">{{ incomePercent.toFixed(0) }}%</span>
                                    <span class="text-[10px] text-bakri-teal font-bold uppercase tracking-widest mt-1">Cashflow</span>
                                </div>
                            </div>
                            <!-- Legend -->
                            <div class="grid grid-cols-2 gap-8 mt-12 w-full max-w-sm px-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-5 h-5 rounded-lg bg-bakri-teal shadow-lg shadow-bakri-teal/30"></div>
                                    <div class="flex flex-col leading-none">
                                        <span class="text-[10px] uppercase font-black text-slate-400 mb-1">Masuk</span>
                                        <span class="text-sm font-black text-bakri-teal dark:text-slate-200">Dana Infaq</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-5 h-5 rounded-lg bg-rose-500 shadow-lg shadow-rose-500/30"></div>
                                    <div class="flex flex-col leading-none">
                                        <span class="text-[10px] uppercase font-black text-slate-400 mb-1">Keluar</span>
                                        <span class="text-sm font-black text-bakri-teal dark:text-slate-200">Ops / Sosial</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Summary & Action -->
                        <div class="p-10 lg:p-16 flex flex-col justify-center">
                            <h3 class="text-2xl lg:text-3xl font-black text-bakri-teal dark:text-white mb-8 tracking-tight">Ringkasan Dana</h3>
                            
                            <div class="space-y-8 mb-10">
                                <div class="group">
                                    <div class="flex justify-between items-end mb-3">
                                        <span class="text-slate-500 font-black text-[10px] uppercase tracking-widest">Total Pemasukan</span>
                                        <span class="text-bakri-teal dark:text-bakri-teal font-black text-2xl tracking-tighter">{{ financialSummary.formatted_income }}</span>
                                    </div>
                                    <div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                        <div class="h-full bg-bakri-teal w-full group-hover:opacity-80 transition-opacity"></div>
                                    </div>
                                </div>

                                <div class="group">
                                    <div class="flex justify-between items-end mb-3">
                                        <span class="text-slate-500 font-black text-[10px] uppercase tracking-widest">Total Pengeluaran</span>
                                        <span class="text-rose-600 dark:text-rose-400 font-black text-2xl tracking-tighter">{{ financialSummary.formatted_expense }}</span>
                                    </div>
                                    <div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full bg-rose-500 group-hover:opacity-80 transition-opacity" 
                                            :style="`width: ${Math.min((financialSummary.expense / (financialSummary.income || 1)) * 100, 100)}%`"
                                        ></div>
                                    </div>
                                </div>

                                <div class="pt-8 border-t border-slate-100 dark:border-slate-800">
                                    <div class="flex justify-between items-center bg-bakri-teal/5 dark:bg-slate-800/50 p-6 lg:p-8 rounded-3xl border border-dashed border-bakri-teal/20">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Saldo Abadi</span>
                                            <span class="text-3xl lg:text-4xl font-black text-bakri-teal dark:text-bakri-teal tracking-tighter">{{ financialSummary.formatted_balance }}</span>
                                        </div>
                                        <div class="p-4 bg-bakri-teal text-white rounded-2xl shadow-lg shadow-bakri-teal/30">
                                            <CurrencyDollarIcon class="w-8 h-8" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <Link 
                                href="/transparansi/keuangan"
                                class="btn bg-bakri-teal hover:bg-slate-900 border-none btn-lg rounded-2xl shadow-xl w-full flex items-center justify-center gap-2 font-black tracking-tight text-white hover:-translate-y-1 transition-transform"
                            >
                                <span>Laporan Transparan</span>
                                <ArrowRightIcon class="w-5 h-5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. Layanan & Fasilitas Umat Section -->
        <section class="py-24 bg-white dark:bg-slate-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-20">
                    <span class="badge badge-soft bg-persian-blue/10 text-persian-blue border-none uppercase tracking-widest font-bold mb-3">Program Unggulan</span>
                    <h2 class="text-3xl md:text-5xl font-black text-persian-navy dark:text-white tracking-tight leading-none">Layanan & Fasilitas</h2>
                    <div class="w-16 h-1 bg-persian-gold mx-auto mt-6 rounded-full"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Modern Service Card 1 -->
                    <div class="card bg-slate-50 dark:bg-slate-800/50 border-none hover:bg-white dark:hover:bg-slate-800 shadow-none hover:shadow-2xl transition-all duration-500 p-8 rounded-[2.5rem] group">
                        <div class="w-16 h-16 bg-persian-blue text-white rounded-[1.25rem] flex items-center justify-center mb-8 shadow-xl shadow-persian-blue/20 group-hover:scale-110 group-hover:-rotate-6 transition-all">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-black text-persian-navy dark:text-white mb-4 uppercase tracking-tighter leading-none">Fasilitas & Booking</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed mb-8">Peminjaman aula, perlengkapan, dan ambulans siaga 24 jam untuk keperluan jamaah.</p>
                        <Link href="/fasilitas" class="text-persian-blue font-black text-[10px] uppercase tracking-widest flex items-center gap-1 group-hover:gap-2 transition-all">Pinjam / Sewa <ArrowRightIcon class="w-3 h-3" /></Link>
                    </div>

                    <!-- Modern Service Card 2 -->
                    <div class="card bg-slate-50 dark:bg-slate-800/50 border-none hover:bg-white dark:hover:bg-slate-800 shadow-none hover:shadow-2xl transition-all duration-500 p-8 rounded-[2.5rem] group">
                        <div class="w-16 h-16 bg-persian-gold text-white rounded-[1.25rem] flex items-center justify-center mb-8 shadow-xl shadow-persian-gold/20 group-hover:scale-110 group-hover:-rotate-6 transition-all">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-black text-persian-navy dark:text-white mb-4 uppercase tracking-tighter leading-none">Lumbung Pangan</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed mb-8">Program berbagi kebutuhan pokok untuk jamaah yang membutuhkan agar tiada lara dalam lapar.</p>
                        <Link href="/lumbung-pangan?action=donate" class="text-persian-gold font-black text-[10px] uppercase tracking-widest flex items-center gap-1 group-hover:gap-2 transition-all">Bantu <ArrowRightIcon class="w-3 h-3" /></Link>
                    </div>

                    <!-- Modern Service Card 3 -->
                    <div class="card bg-slate-50 dark:bg-slate-800/50 border-none hover:bg-white dark:hover:bg-slate-800 shadow-none hover:shadow-2xl transition-all duration-500 p-8 rounded-[2.5rem] group">
                        <div class="w-16 h-16 bg-persian-navy text-white rounded-[1.25rem] flex items-center justify-center mb-8 shadow-xl shadow-persian-navy/20 group-hover:scale-110 group-hover:-rotate-6 transition-all">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-black text-persian-navy dark:text-white mb-4 uppercase tracking-tighter leading-none">TPA / Tahfidz</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed mb-8">Mencetak generasi rabbani melalui pendidikan Al-Quran yang intensif dan berjenjang.</p>
                        <a href="#" class="text-persian-navy font-black text-[10px] uppercase tracking-widest flex items-center gap-1 group-hover:gap-2 transition-all">Daftar <ArrowRightIcon class="w-3 h-3" /></a>
                    </div>

                    <!-- Modern Service Card 4 -->
                    <div class="card bg-slate-50 dark:bg-slate-800/50 border-none hover:bg-white dark:hover:bg-slate-800 shadow-none hover:shadow-2xl transition-all duration-500 p-8 rounded-[2.5rem] group">
                        <div class="w-16 h-16 bg-persian-azure text-white rounded-[1.25rem] flex items-center justify-center mb-8 shadow-xl shadow-persian-azure/20 group-hover:scale-110 group-hover:-rotate-6 transition-all">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-black text-persian-navy dark:text-white mb-4 uppercase tracking-tighter leading-none">Kajian Rutin</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed mb-8">Mendalami ilmu agama bersama guru-guru pilihan dalam suasana yang sejuk dan ukhuwah.</p>
                        <Link :href="route('public.tarbiyah', { tab: 'lectures' })" class="text-persian-azure font-black text-[10px] uppercase tracking-widest flex items-center gap-1 group-hover:gap-2 transition-all">Jadwal <ArrowRightIcon class="w-3 h-3" /></Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. Berita & Kegiatan Section -->
        <section v-if="posts && posts.length > 0" class="py-24 bg-persian-navy/5 dark:bg-slate-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                    <div>
                        <span class="badge badge-soft bg-persian-blue/10 text-persian-blue border-none uppercase tracking-widest font-bold mb-3">Informasi Terkini</span>
                        <h2 class="text-3xl md:text-5xl font-black text-persian-navy dark:text-white tracking-tight leading-none">Eksplorasi Masjid</h2>
                    </div>
                    <Link :href="route('public.berita')" class="btn btn-ghost hover:bg-persian-blue/5 text-persian-blue font-black tracking-tight flex items-center gap-2 group">
                        Lihat Semua Berita
                        <ArrowRightIcon class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 lg:gap-16">
                    <Link v-for="post in posts" :key="post.id" :href="route('public.post', post.slug)" class="group space-y-6">
                        <!-- Modern Post Image -->
                        <div class="aspect-[4/3] rounded-[2.5rem] overflow-hidden shadow-2xl shadow-slate-200 dark:shadow-none bg-slate-200 dark:bg-slate-800 flex items-center justify-center relative">
                            <img :src="post.image_url" :alt="post.title" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                            <!-- Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-persian-navy/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-8">
                                <span class="text-white text-[10px] font-black uppercase tracking-[0.2em] border-b-2 border-persian-blue pb-1">Baca Selengkapnya</span>
                            </div>
                        </div>
                        
                        <!-- Post Meta & Content -->
                        <div class="px-2 space-y-4">
                            <div class="flex items-center gap-4 text-[10px] font-black uppercase tracking-widest text-slate-400">
                                <span class="flex items-center gap-2">
                                    <div class="w-1 h-1 rounded-full bg-persian-blue"></div>
                                    {{ post.published_at }}
                                </span>
                                <span class="flex items-center gap-2">
                                    <div class="w-1 h-1 rounded-full bg-persian-gold"></div>
                                    Mimin
                                </span>
                            </div>
                            
                            <h3 class="text-xl lg:text-2xl font-black text-persian-navy dark:text-white leading-[1.3] group-hover:text-persian-blue transition-colors line-clamp-2 uppercase tracking-tighter">
                                {{ post.title }}
                            </h3>
                            
                            <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed line-clamp-2 font-medium">
                                {{ post.excerpt }}
                            </p>
                        </div>
                    </Link>
                </div>
            </div>
        </section>


        <!-- 6. Footer -->
        <footer class="bg-slate-900 text-slate-300 py-16 border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                    <!-- Brand -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 text-white font-bold text-2xl">
                            <img 
                                v-if="$page.props.settings?.logo_path" 
                                :src="$page.props.settings.logo_path" 
                                alt="Logo" 
                                class="h-8 w-auto"
                            />
                            <span v-else class="text-3xl">🕌</span>
                            <span>{{ $page.props.settings?.site_name || 'MasjidVision' }}</span>
                        </div>
                        <p class="text-sm text-slate-400 leading-relaxed">
                            {{ $page.props.settings?.footer_text || 'Membangun peradaban umat melalui masjid yang transparan, modern, dan melayani dengan sepenuh hati.' }}
                        </p>
                        <!-- Socials -->
                        <div class="flex gap-4 pt-2">
                             <a v-if="$page.props.settings?.facebook_url" :href="$page.props.settings.facebook_url" class="text-slate-400 hover:text-emerald-500 transition-colors">Facebook</a>
                             <a v-if="$page.props.settings?.instagram_url" :href="$page.props.settings.instagram_url" class="text-slate-400 hover:text-emerald-500 transition-colors">Instagram</a>
                             <a v-if="$page.props.settings?.youtube_url" :href="$page.props.settings.youtube_url" class="text-slate-400 hover:text-emerald-500 transition-colors">YouTube</a>
                        </div>
                    </div>

                    <!-- Contact -->
                    <div>
                        <h4 class="text-white font-bold mb-6">Hubungi Kami</h4>
                        <div class="space-y-3 text-sm">
                            <p class="flex items-start gap-3">
                                <span class="text-emerald-500">📍</span>
                                <span>{{ $page.props.settings?.address || 'Jl. Contoh No. 123, Jakarta Selatan' }}</span>
                            </p>
                            <p class="flex items-center gap-3">
                                <span class="text-emerald-500">📞</span>
                                <span>{{ $page.props.settings?.phone || '(021) 7890-1234' }}</span>
                            </p>
                            <p class="flex items-center gap-3">
                                <span class="text-emerald-500">📧</span>
                                <span>{{ $page.props.settings?.email || 'info@masjidvision.com' }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Links -->
                    <div>
                        <h4 class="text-white font-bold mb-6">Akses Cepat</h4>
                        <ul class="space-y-3 text-sm">
                            <li><a href="/ibadah/jadwal" class="hover:text-emerald-400 transition-colors">Jadwal Sholat</a></li>
                            <li><Link href="/transparansi/keuangan" class="hover:text-emerald-400 transition-colors">Laporan Keuangan</Link></li>
                            <li><Link href="/transparansi/aset" class="hover:text-emerald-400 transition-colors">Data Aset</Link></li>
                            <li><Link href="/login" class="hover:text-emerald-400 transition-colors">Login Pengurus</Link></li>
                        </ul>
                    </div>

                    <!-- Donation Info -->
                    <div>
                        <h4 class="text-white font-bold mb-6">Rekening Donasi</h4>
                        <div class="bg-slate-800 p-4 rounded-xl border border-slate-700">
                             <div v-if="$page.props.settings?.donation_bank_info">
                                <div 
                                    v-for="(line, index) in $page.props.settings.donation_bank_info.split('\n')" 
                                    :key="index"
                                    :class="[
                                        index === 0 ? 'text-xs text-slate-400 uppercase tracking-wider mb-1' : '',
                                        index === 1 ? 'text-xl font-mono text-white font-bold mb-2' : '',
                                        index > 1 ? 'text-sm text-slate-400' : ''
                                    ]"
                                >
                                    {{ line }}
                                </div>
                            </div>
                            <div v-else>
                                <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">Bank Syariah Indonesia</div>
                                <div class="text-xl font-mono text-white font-bold mb-2">1234 5678 90</div>
                                <div class="text-sm text-slate-400">a.n. DKM {{ $page.props.settings?.site_name || 'Masjid' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-16 pt-8 border-t border-slate-800 text-center text-sm text-slate-500">
                    {{ $page.props.settings?.copyright_text || '&copy; 2026 MasjidVision. All rights reserved.' }}
                </div>
            </div>
        </footer>
    </PublicLayout>
</template>

<style>
@keyframes scan {
    0% { top: 0%; opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { top: 100%; opacity: 0; }
}
</style>

