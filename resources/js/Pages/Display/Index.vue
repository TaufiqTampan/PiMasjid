<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useIntervalFn, useNow } from '@vueuse/core';
import { TransitionGroup } from 'vue';

const props = defineProps({
    currentTime: String,
    todayPrayerTimes: Object,
    nextPrayer: Object,
    isFriday: Boolean,
    fridaySchedule: Object,
    slides: Array,
    recentDonations: Array,
    monthlyStats: Object,
    wishlists: Array,
    displaySettings: Object,
});

// Audio & Synthesizer State (Web Audio API)
const audioMuted = ref(false);
let audioCtx = null;

const initAudio = () => {
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
};

const playChime = (type = 'adhan') => {
    if (audioMuted.value) return;
    try {
        initAudio();
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        
        if (type === 'adhan') {
            const notes = [523.25, 659.25, 783.99]; // C5, E5, G5
            notes.forEach((freq, index) => {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, audioCtx.currentTime + index * 0.4);
                gain.gain.setValueAtTime(0.4, audioCtx.currentTime + index * 0.4);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + index * 0.4 + 1.2);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(audioCtx.currentTime + index * 0.4);
                osc.stop(audioCtx.currentTime + index * 0.4 + 1.2);
            });
        } else if (type === 'beep') {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(880, audioCtx.currentTime);
            gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.3);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.3);
        } else if (type === 'final_beep') {
            [0, 0.25].forEach((delay) => {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'square';
                osc.frequency.setValueAtTime(1046.50, audioCtx.currentTime + delay);
                gain.gain.setValueAtTime(0.5, audioCtx.currentTime + delay);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + delay + 0.2);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(audioCtx.currentTime + delay);
                osc.stop(audioCtx.currentTime + delay + 0.2);
            });
        }
    } catch (e) {
        console.warn('Audio play error:', e);
    }
};

// Real-time Clock
const now = useNow({ interval: 1000 });

const formattedTime = computed(() => now.value.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
const formattedDate = computed(() => now.value.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));

// Display Modes: 'NORMAL' | 'ADHAN' | 'IQAMAH' | 'PRAYER_STANDBY'
const displayMode = ref('NORMAL');
const activePrayerName = ref('');
const iqamahSecondsRemaining = ref(0);
const adhanSecondsRemaining = ref(0);
const standbySecondsRemaining = ref(0);
const showDemoControls = ref(false);

const getIqamahDuration = (prayerName) => {
    const settings = props.displaySettings || {};
    const nameLower = (prayerName || '').toLowerCase();
    if (nameLower.includes('subuh')) return (settings.iqamah_subuh || 10) * 60;
    if (nameLower.includes('dhuhr') || nameLower.includes('dzuhur')) return (settings.iqamah_dhuhr || 10) * 60;
    if (nameLower.includes('asr') || nameLower.includes('ashar')) return (settings.iqamah_asr || 10) * 60;
    if (nameLower.includes('maghrib')) return (settings.iqamah_maghrib || 7) * 60;
    if (nameLower.includes('isha') || nameLower.includes('isya')) return (settings.iqamah_isha || 10) * 60;
    return 10 * 60;
};

const formatCountdown = (totalSec) => {
    const mins = Math.floor(Math.max(0, totalSec) / 60);
    const secs = Math.max(0, totalSec) % 60;
    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
};

// Automatic Timer Loop
let adhanTriggeredToday = {};

useIntervalFn(() => {
    const currentHHMM = now.value.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
    
    if (displayMode.value === 'NORMAL' && props.todayPrayerTimes) {
        const prayers = [
            { name: 'Subuh', time: props.todayPrayerTimes.subuh },
            { name: 'Dzuhur', time: props.todayPrayerTimes.dhuhr },
            { name: 'Ashar', time: props.todayPrayerTimes.asr },
            { name: 'Maghrib', time: props.todayPrayerTimes.maghrib },
            { name: 'Isya', time: props.todayPrayerTimes.isha },
        ];
        
        for (const p of prayers) {
            if (p.time && currentHHMM === p.time && !adhanTriggeredToday[p.name]) {
                triggerAdhan(p.name);
                adhanTriggeredToday[p.name] = true;
                break;
            }
        }
    }

    if (displayMode.value === 'ADHAN') {
        adhanSecondsRemaining.value -= 1;
        if (adhanSecondsRemaining.value <= 0) {
            triggerIqamah(activePrayerName.value);
        }
    } else if (displayMode.value === 'IQAMAH') {
        iqamahSecondsRemaining.value -= 1;
        if (iqamahSecondsRemaining.value <= 10 && iqamahSecondsRemaining.value > 0) {
            playChime('beep');
        }
        if (iqamahSecondsRemaining.value <= 0) {
            playChime('final_beep');
            triggerStandby();
        }
    } else if (displayMode.value === 'PRAYER_STANDBY') {
        standbySecondsRemaining.value -= 1;
        if (standbySecondsRemaining.value <= 0) {
            displayMode.value = 'NORMAL';
        }
    }
}, 1000);

const triggerAdhan = (prayerName = 'Dzuhur') => {
    activePrayerName.value = prayerName;
    adhanSecondsRemaining.value = (props.displaySettings?.adhan_duration || 3) * 60;
    displayMode.value = 'ADHAN';
    playChime('adhan');
};

const triggerIqamah = (prayerName = 'Dzuhur') => {
    activePrayerName.value = prayerName;
    iqamahSecondsRemaining.value = getIqamahDuration(prayerName);
    displayMode.value = 'IQAMAH';
    playChime('beep');
};

const triggerStandby = () => {
    standbySecondsRemaining.value = (props.displaySettings?.sholat_duration || 15) * 60;
    displayMode.value = 'PRAYER_STANDBY';
};

const resetToNormal = () => {
    displayMode.value = 'NORMAL';
};

// Countdown to next prayer for card
const prayerCountdown = computed(() => {
    if (!props.nextPrayer) return '00:00';
    
    const [hours, minutes] = props.nextPrayer.time.split(':');
    const prayerTime = new Date(now.value);
    prayerTime.setHours(parseInt(hours), parseInt(minutes), 0, 0);
    
    if (props.nextPrayer.tomorrow) {
        prayerTime.setDate(prayerTime.getDate() + 1);
    }
    
    const diff = prayerTime - now.value;
    if (diff < 0) return '00:00';
    
    const totalMinutes = Math.floor(diff / 60000);
    const hrs = Math.floor(totalMinutes / 60);
    const mins = totalMinutes % 60;
    
    return hrs > 0 ? `${hrs}j ${mins}m` : `${mins} mnt`;
});

// Carousel management with progress bar
const SLIDE_DURATION_MS = 9000;
const currentSlideIndex = ref(0);
const slideProgress = ref(0);
const totalSlides = computed(() => (props.slides?.length || 0) + 2);

useIntervalFn(() => {
    if (displayMode.value === 'NORMAL') {
        slideProgress.value += (100 / (SLIDE_DURATION_MS / 100));
        if (slideProgress.value >= 100) {
            slideProgress.value = 0;
            currentSlideIndex.value = (currentSlideIndex.value + 1) % totalSlides.value;
        }
    }
}, 100);

const currentSlide = computed(() => {
    const index = currentSlideIndex.value;
    const slidesCount = props.slides?.length || 0;
    
    if (index < slidesCount) {
        return { type: 'info', data: props.slides[index] };
    } else if (index === slidesCount) {
        return { type: 'financial', data: props.monthlyStats };
    } else {
        return { type: 'wishlist', data: props.wishlists };
    }
});

// Running Ticker Content
const tickerContent = computed(() => {
    const settingsText = props.displaySettings?.running_text || 'Selamat datang di MasjidVision. Mohon heningkan HP Anda saat di ruang sholat.';
    const donations = props.recentDonations?.map(d => `💚 Donasi ${d.category}: ${d.amount}`) || [];
    return `${settingsText}   •   ${donations.join('   •   ')}   •   `;
});

// Reload Inertia state every 60s
useIntervalFn(() => {
    router.reload({
        only: ['todayPrayerTimes', 'nextPrayer', 'recentDonations', 'slides', 'monthlyStats', 'wishlists'],
        preserveScroll: true,
    });
}, 60000);

// Key shortcuts ('D' for Demo, 'M' for Mute)
const handleKeyDown = (e) => {
    if (e.key === 'd' || e.key === 'D') {
        showDemoControls.value = !showDemoControls.value;
    } else if (e.key === 'm' || e.key === 'M') {
        audioMuted.value = !audioMuted.value;
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

const prayerTimesList = computed(() => {
    if (!props.todayPrayerTimes) return [];
    return [
        { name: 'Subuh', time: props.todayPrayerTimes.subuh },
        { name: 'Dzuhur', time: props.todayPrayerTimes.dhuhr },
        { name: 'Ashar', time: props.todayPrayerTimes.asr },
        { name: 'Maghrib', time: props.todayPrayerTimes.maghrib },
        { name: 'Isya', time: props.todayPrayerTimes.isha },
    ];
});
</script>

<template>
    <Head title="TV Display Digital - Pusat Informasi Masjid" />

    <!-- Root App Shell - Solid High-Contrast Container -->
    <div style="background-color: #060911; color: #ffffff;" class="min-h-screen flex flex-col justify-between overflow-hidden relative select-none font-sans">
        
        <!-- ------------------------------------------------------------- -->
        <!-- STATE 1: ADHAN SCREEN OVERLAY                                  -->
        <!-- ------------------------------------------------------------- -->
        <Transition name="fade">
            <div v-if="displayMode === 'ADHAN'" style="background-color: #060911;" class="fixed inset-0 z-50 flex flex-col items-center justify-center p-8 text-center">
                <div class="relative z-10 space-y-6 max-w-4xl">
                    <div style="background-color: rgba(16, 185, 129, 0.2); border-color: #34d399;" class="w-28 h-28 mx-auto rounded-full border-4 flex items-center justify-center text-5xl shadow-2xl animate-bounce">
                        🕌
                    </div>
                    <div style="background-color: rgba(16, 185, 129, 0.2); border-color: #34d399; color: #6ee7b7;" class="inline-block px-6 py-2 rounded-full border font-extrabold uppercase tracking-widest text-sm">
                        Adhan — Waktu Sholat Tiba
                    </div>
                    <h1 style="color: #ffffff;" class="text-6xl md:text-8xl font-black tracking-tight uppercase drop-shadow-lg">
                        WAKTU SHOLAT {{ activePrayerName }}
                    </h1>
                    <p style="color: #a7f3d0;" class="text-2xl md:text-3xl font-light">
                        Telah Tiba Untuk Wilayah {{ displaySettings?.site_address || 'Masjid' }} dan Sekitarnya
                    </p>
                    <div class="pt-4">
                        <div style="color: #fbbf24;" class="text-6xl md:text-7xl font-mono font-black tracking-tighter">
                            {{ formatCountdown(adhanSecondsRemaining) }}
                        </div>
                        <span style="color: #9ca3af;" class="text-xs font-bold uppercase tracking-widest mt-2 block">Menuju Countdown Iqamah</span>
                    </div>
                    <button @click="triggerIqamah(activePrayerName)" style="background-color: #10b981; color: #060911;" class="mt-6 px-8 py-3.5 rounded-full font-black text-lg shadow-2xl transition-all border-none cursor-pointer">
                        Lanjut ke Iqamah Sekarang ⏩
                    </button>
                </div>
            </div>
        </Transition>

        <!-- ------------------------------------------------------------- -->
        <!-- STATE 2: IQAMAH COUNTDOWN OVERLAY                             -->
        <!-- ------------------------------------------------------------- -->
        <Transition name="fade">
            <div v-if="displayMode === 'IQAMAH'" style="background-color: #060911;" class="fixed inset-0 z-50 flex flex-col items-center justify-between p-8 text-center">
                <div class="relative z-10 pt-4">
                    <span style="background-color: rgba(245, 158, 11, 0.2); border-color: #fbbf24; color: #fcd34d;" class="px-6 py-2 rounded-full border font-extrabold uppercase tracking-widest text-sm">
                        HITUNG MUNDUR IQAMAH — {{ activePrayerName }}
                    </span>
                </div>

                <div class="relative z-10 my-auto space-y-4">
                    <div style="color: #fbbf24;" class="text-[9rem] md:text-[13rem] leading-none font-mono font-black tracking-tighter">
                        {{ formatCountdown(iqamahSecondsRemaining) }}
                    </div>
                    <h2 style="color: #ffffff;" class="text-3xl md:text-4xl font-extrabold tracking-tight max-w-3xl mx-auto uppercase">
                        Luruskan dan Rapatkan Shaf
                    </h2>
                    <p style="color: #fde68a;" class="text-xl font-medium">
                        Mohon Nonaktifkan / Heningkan Nada Dering Telepon Genggam Anda
                    </p>
                </div>

                <div class="relative z-10 pb-4 flex gap-4">
                    <button @click="triggerStandby" style="background-color: #f59e0b; color: #060911;" class="px-8 py-3.5 rounded-full font-black text-base shadow-xl border-none cursor-pointer">
                        Mulai Sholat (Layar Senyap) ⏹️
                    </button>
                    <button @click="resetToNormal" style="background-color: #1f2937; color: #d1d5db; border-color: #374151;" class="px-6 py-3.5 rounded-full font-bold text-base border cursor-pointer">
                        Batalkan
                    </button>
                </div>
            </div>
        </Transition>

        <!-- ------------------------------------------------------------- -->
        <!-- STATE 3: PRAYER STANDBY SCREEN (SHOLAT MODE)                  -->
        <!-- ------------------------------------------------------------- -->
        <Transition name="fade">
            <div v-if="displayMode === 'PRAYER_STANDBY'" style="background-color: #000000;" class="fixed inset-0 z-50 flex flex-col items-center justify-center p-8 text-center">
                <div class="space-y-6 max-w-3xl">
                    <div class="text-7xl opacity-90">📵</div>
                    <h1 style="color: #ffffff;" class="text-4xl md:text-6xl font-black tracking-tight leading-tight uppercase">
                        LURUSKAN & RAPATKAN SHAF
                    </h1>
                    <p style="color: #d1d5db;" class="text-xl md:text-2xl font-light">
                        Mohon Nonaktifkan HP Demi Kekhusyukan Sholat
                    </p>
                    <div style="color: #6b7280;" class="font-mono text-xs pt-6">
                        Layar kembali aktif otomatis dalam {{ formatCountdown(standbySecondsRemaining) }}
                    </div>
                    <button @click="resetToNormal" style="background-color: #111827; border-color: #374151; color: #9ca3af;" class="mt-4 px-6 py-2 rounded-full border text-xs cursor-pointer">
                        Kembali ke Tampilan Utama
                    </button>
                </div>
            </div>
        </Transition>

        <!-- ------------------------------------------------------------- -->
        <!-- MAIN TOP HEADER BAR                                           -->
        <!-- ------------------------------------------------------------- -->
        <header style="background-color: #0f172a; border-bottom: 2px solid #1e293b;" class="px-6 py-3 flex items-center justify-between shadow-xl z-20 shrink-0">
            <!-- Brand Logo & Title -->
            <div class="flex items-center gap-3">
                <div style="background-color: rgba(16, 185, 129, 0.2); border-color: #34d399; color: #34d399;" class="w-10 h-10 rounded-xl border flex items-center justify-center text-xl">
                    🕌
                </div>
                <div>
                    <h1 style="color: #ffffff;" class="text-xl font-black uppercase tracking-wider">{{ displaySettings?.site_name || 'MASJIDVISION' }}</h1>
                    <p style="color: #94a3b8;" class="text-[11px] font-medium tracking-wide">{{ displaySettings?.site_address || 'Pusat Ibadah Umat' }}</p>
                </div>
            </div>

            <!-- Header Clock & Hijri Date -->
            <div class="flex items-center gap-6">
                <div v-if="todayPrayerTimes?.hijri_date" class="text-right hidden sm:block">
                    <span style="color: #34d399;" class="text-[10px] font-bold uppercase tracking-widest block">Tanggal Hijriah</span>
                    <span style="color: #e2e8f0;" class="text-xs font-semibold">{{ todayPrayerTimes.hijri_date }}</span>
                </div>
                <div style="border-left: 1px solid #334155;" class="text-right pl-4">
                    <span style="color: #94a3b8;" class="text-[10px] font-bold uppercase tracking-widest block">{{ formattedDate }}</span>
                    <span style="color: #34d399;" class="text-2xl font-black font-mono tracking-tight">{{ formattedTime }}</span>
                </div>
            </div>
        </header>

        <!-- ------------------------------------------------------------- -->
        <!-- MAIN DISPLAY LAYOUT: 100% UNCLIPPED HERO SLIDE CENTER STAGE   -->
        <!-- ------------------------------------------------------------- -->
        <main class="flex-1 p-4 md:p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 overflow-hidden">
            
            <!-- HERO CENTER STAGE (8 Cols on LG): FULLY VISIBLE SLIDE PANEL -->
            <div class="lg:col-span-8 flex flex-col">
                <div style="background-color: #0f172a; border: 2px solid #10b981;" class="rounded-3xl p-6 md:p-8 shadow-2xl flex flex-col justify-between flex-1 min-h-[420px] md:min-h-[500px]">
                    
                    <!-- Top Slide Header & Progress Line Bar -->
                    <div class="w-full flex flex-col gap-3 pb-3 border-b border-slate-800 shrink-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span style="background-color: rgba(245, 158, 11, 0.2); border: 1px solid #f59e0b; color: #fbbf24;" class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-widest flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                                    📢 INFORMASI TERKINI MASJID
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span style="color: #94a3b8;" class="text-xs font-bold uppercase tracking-wider mr-2">
                                    Slide {{ currentSlideIndex + 1 }} dari {{ totalSlides }}
                                </span>
                                <div 
                                    v-for="i in totalSlides" 
                                    :key="i"
                                    :style="currentSlideIndex === i - 1 ? 'background-color: #fbbf24; width: 28px;' : 'background-color: #1e293b; width: 8px;'"
                                    class="h-2 rounded-full transition-all duration-300"
                                ></div>
                            </div>
                        </div>

                        <!-- Slide Progress Line Bar -->
                        <div style="background-color: #1e293b;" class="w-full h-1.5 rounded-full overflow-hidden">
                            <div 
                                style="background: linear-gradient(to right, #10b981, #fbbf24);" 
                                class="h-full rounded-full transition-all duration-100 ease-linear"
                                :style="{ width: slideProgress + '%' }"
                            ></div>
                        </div>
                    </div>

                    <!-- SLIDE CONTENT HERO DISPLAY CONTAINER (FULLY VISIBLE, NO ABSOLUTE CLIPPING) -->
                    <div class="flex-1 flex flex-col items-center justify-center py-4 my-auto w-full text-center">
                        
                        <!-- Slide Type 1: General Info & Announcements -->
                        <div 
                            v-if="currentSlide.type === 'info'" 
                            :key="`slide-${currentSlideIndex}`"
                            class="w-full flex flex-col items-center justify-center text-center animate-fade-in"
                        >
                            <!-- Poster Image Display -->
                            <div v-if="currentSlide.data.image_url" class="mb-4 flex items-center justify-center">
                                <img 
                                    :src="currentSlide.data.image_url" 
                                    :alt="currentSlide.data.title" 
                                    style="border: 2px solid #334155;"
                                    class="max-h-56 md:max-h-72 w-auto max-w-full rounded-2xl object-contain shadow-2xl"
                                />
                            </div>

                            <!-- Grand Title -->
                            <h2 style="color: #fbbf24;" class="text-3xl md:text-5xl font-black mb-3 tracking-tight uppercase leading-tight">
                                {{ currentSlide.data.title }}
                            </h2>
                            
                            <!-- Body Content -->
                            <p style="color: #f1f5f9;" class="text-lg md:text-2xl max-w-3xl font-medium leading-relaxed">
                                {{ currentSlide.data.content }}
                            </p>
                        </div>

                        <!-- Slide Type 2: Grand Financial Transparency -->
                        <div 
                            v-else-if="currentSlide.type === 'financial'" 
                            :key="'financial'"
                            class="w-full flex flex-col items-center justify-center text-center animate-fade-in"
                        >
                            <div style="background-color: rgba(16, 185, 129, 0.15); border: 1px solid #059669; color: #34d399;" class="px-5 py-1.5 rounded-full text-xs font-extrabold uppercase tracking-widest mb-4">
                                Transparansi Baitul Maal
                            </div>
                            <h2 style="color: #ffffff;" class="text-3xl md:text-4xl font-black mb-6 uppercase tracking-tight">
                                Laporan Kas Masjid Bulan Ini
                            </h2>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-4xl my-auto">
                                <div style="background-color: #020617; border: 2px solid #059669;" class="p-6 rounded-3xl shadow-2xl text-center">
                                    <div style="color: #34d399;" class="text-xs font-black uppercase tracking-widest mb-2">Total Pemasukan</div>
                                    <div style="color: #ffffff;" class="text-3xl md:text-4xl font-black font-mono">Rp {{ monthlyStats?.income || '0' }}</div>
                                </div>
                                <div style="background-color: #020617; border: 2px solid #f43f5e;" class="p-6 rounded-3xl shadow-2xl text-center">
                                    <div style="color: #fb7185;" class="text-xs font-black uppercase tracking-widest mb-2">Total Pengeluaran</div>
                                    <div style="color: #ffffff;" class="text-3xl md:text-4xl font-black font-mono">Rp {{ monthlyStats?.expense || '0' }}</div>
                                </div>
                                <div style="background-color: #020617; border: 2px solid #f59e0b;" class="p-6 rounded-3xl shadow-2xl text-center">
                                    <div style="color: #fbbf24;" class="text-xs font-black uppercase tracking-widest mb-2">Saldo Akhir Kas</div>
                                    <div style="color: #fde68a;" class="text-3xl md:text-4xl font-black font-mono">Rp {{ monthlyStats?.balance || '0' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Slide Type 3: Wakaf & Community Fundraising Progress -->
                        <div 
                            v-else-if="currentSlide.type === 'wishlist'" 
                            :key="'wishlist'"
                            class="w-full flex flex-col items-center justify-center text-center animate-fade-in"
                        >
                            <div style="background-color: rgba(245, 158, 11, 0.15); border: 1px solid #d97706; color: #fbbf24;" class="px-5 py-1.5 rounded-full text-xs font-extrabold uppercase tracking-widest mb-4">
                                Peluang Amal Jariah
                            </div>
                            <h2 style="color: #ffffff;" class="text-3xl md:text-4xl font-black mb-6 uppercase tracking-tight">
                                Program Wakaf & Kebutuhan Masjid
                            </h2>

                            <div class="w-full max-w-3xl my-auto space-y-4 text-left">
                                <div v-if="!wishlists || wishlists.length === 0" style="background-color: #020617; border: 1px solid #1e293b; color: #94a3b8;" class="rounded-3xl p-8 text-center text-base">
                                    Belum ada daftar program wakaf aktif saat ini.
                                </div>
                                <div 
                                    v-else
                                    v-for="item in wishlists" 
                                    :key="item.id"
                                    style="background-color: #020617; border: 1px solid #1e293b;"
                                    class="p-5 rounded-3xl shadow-2xl"
                                >
                                    <div class="flex justify-between items-center mb-2">
                                        <h3 style="color: #ffffff;" class="text-xl md:text-2xl font-black uppercase tracking-tight truncate pr-4">{{ item.item_name }}</h3>
                                        <span style="color: #34d399;" class="text-2xl font-black font-mono shrink-0">{{ item.progress_percentage || 0 }}%</span>
                                    </div>
                                    <div style="background-color: #1e293b;" class="w-full rounded-full h-4 overflow-hidden mb-3">
                                        <div 
                                            style="background: linear-gradient(to right, #10b981, #fbbf24);"
                                            class="h-full rounded-full transition-all duration-1000"
                                            :style="{ width: Math.min(item.progress_percentage || 0, 100) + '%' }"
                                        ></div>
                                    </div>
                                    <div style="color: #94a3b8;" class="flex justify-between text-xs font-bold uppercase tracking-wider">
                                        <span>Terkumpul: <strong style="color: #6ee7b7;">{{ item.formatted_total_fulfilled }}</strong></span>
                                        <span>Target: <strong style="color: #ffffff;">{{ item.formatted_total_target }}</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <!-- RIGHT SIDEBAR (4 Cols on LG): PRAYER TIMES ANCHOR -->
            <div class="lg:col-span-4 flex flex-col gap-4">
                
                <!-- Next Prayer Highlight Card -->
                <div v-if="nextPrayer" style="background-color: #0f172a; border: 2px solid #10b981;" class="rounded-3xl p-5 shadow-2xl relative overflow-hidden">
                    <div class="flex justify-between items-center mb-2">
                        <span style="background-color: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid #059669;" class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest">
                            Sholat Berikutnya
                        </span>
                        <span style="background-color: #020617; color: #34d399; border: 1px solid #1e293b;" class="text-[10px] font-mono font-bold px-2.5 py-0.5 rounded-full">
                            {{ nextPrayer.tomorrow ? 'Besok' : 'Hari Ini' }}
                        </span>
                    </div>

                    <div class="flex justify-between items-baseline my-2">
                        <span style="color: #ffffff;" class="text-3xl font-black uppercase tracking-tight">{{ nextPrayer.name }}</span>
                        <span style="color: #fbbf24;" class="text-4xl font-black font-mono">{{ nextPrayer.time }}</span>
                    </div>

                    <div style="background-color: #020617; border: 1px solid #1e293b;" class="rounded-2xl p-3 flex justify-between items-center mt-3">
                        <span style="color: #94a3b8;" class="text-[11px] font-bold uppercase tracking-wider">Hitung Mundur:</span>
                        <span style="color: #34d399;" class="text-lg font-black font-mono animate-pulse">{{ prayerCountdown }}</span>
                    </div>
                </div>

                <!-- Prayer Times List Container -->
                <div style="background-color: #0f172a; border: 1px solid #1e293b;" class="rounded-3xl p-4 flex-1 shadow-2xl flex flex-col justify-between">
                    <div style="border-bottom: 1px solid #1e293b;" class="flex items-center justify-between mb-3 pb-2">
                        <h3 style="color: #cbd5e1;" class="text-xs font-black uppercase tracking-widest flex items-center gap-2">
                            <span>⏱️</span> Jadwal Sholat Hari Ini
                        </h3>
                        <button @click="audioMuted = !audioMuted" style="background-color: #1e293b; color: #94a3b8; border: 1px solid #334155;" class="text-[10px] px-2 py-0.5 rounded cursor-pointer">
                            {{ audioMuted ? '🔇 Mute' : '🔊 Audio On' }}
                        </button>
                    </div>

                    <!-- Prayer Time List Rows -->
                    <div class="space-y-2 flex-1 flex flex-col justify-center">
                        <div 
                            v-for="prayer in prayerTimesList" 
                            :key="prayer.name"
                            :style="nextPrayer?.name === prayer.name 
                                ? 'background-color: #059669; border: 2px solid #34d399; color: #ffffff;' 
                                : 'background-color: #020617; border: 1px solid #1e293b; color: #f1f5f9;'"
                            class="flex justify-between items-center px-4 py-2.5 rounded-2xl transition-all duration-300 shadow-md"
                        >
                            <span class="text-sm font-extrabold uppercase tracking-wide">{{ prayer.name }}</span>
                            <span class="text-xl font-black font-mono">{{ prayer.time }}</span>
                        </div>
                    </div>

                    <!-- Friday Officers Box (Shown on Fridays) -->
                    <div v-if="isFriday && fridaySchedule" style="border-top: 1px solid #1e293b;" class="mt-3 pt-3 text-[11px] space-y-1">
                        <div style="color: #34d399;" class="font-bold uppercase tracking-wider mb-1">🕌 Petugas Sholat Jumat</div>
                        <div style="color: #cbd5e1;" class="flex justify-between"><span style="color: #64748b;">Khatib:</span><span class="font-bold">{{ fridaySchedule.khatib }}</span></div>
                        <div style="color: #cbd5e1;" class="flex justify-between"><span style="color: #64748b;">Imam:</span><span class="font-bold">{{ fridaySchedule.imam }}</span></div>
                    </div>
                </div>
            </div>

        </main>

        <!-- ------------------------------------------------------------- -->
        <!-- BOTTOM RUNNING TICKER FOOTER                                  -->
        <!-- ------------------------------------------------------------- -->
        <footer style="background-color: #064e3b; border-top: 2px solid #047857;" class="flex items-center overflow-hidden shadow-2xl relative shrink-0">
            <div style="background-color: #047857; color: #ffffff;" class="px-6 py-3 font-black text-sm md:text-base tracking-wider uppercase shrink-0 shadow-2xl z-20 flex items-center gap-2">
                <span>📢</span> INFORMASI MASJID
            </div>
            
            <div class="flex-1 overflow-hidden">
                <div style="color: #ecfdf5;" class="animate-marquee whitespace-nowrap text-base md:text-lg font-bold tracking-wide">
                    {{ tickerContent }}{{ tickerContent }}
                </div>
            </div>

            <!-- Demo Button Shortcut Hint -->
            <button 
                @click="showDemoControls = !showDemoControls" 
                style="background-color: #0f172a; color: #94a3b8; border-left: 1px solid #1e293b;"
                class="shrink-0 px-3 py-3 text-[11px] font-mono transition-colors z-20 cursor-pointer"
            >
                ⌨️ Demo [D]
            </button>
        </footer>

        <!-- ------------------------------------------------------------- -->
        <!-- DEMO / PRESENTATION TESTING PANEL                              -->
        <!-- ------------------------------------------------------------- -->
        <Transition name="fade">
            <div v-if="showDemoControls" style="background-color: #0f172a; border: 1px solid #10b981;" class="fixed bottom-14 right-4 z-50 rounded-3xl p-5 shadow-2xl max-w-xs text-xs space-y-3">
                <div style="border-bottom: 1px solid #1e293b;" class="flex justify-between items-center pb-2">
                    <h4 style="color: #34d399;" class="font-extrabold uppercase tracking-wider text-xs flex items-center gap-1.5">
                        🎛️ Panel Uji Coba Display
                    </h4>
                    <button @click="showDemoControls = false" style="color: #94a3b8;" class="font-bold cursor-pointer">✕</button>
                </div>

                <div class="space-y-2">
                    <button @click="triggerAdhan('Dzuhur')" style="background-color: rgba(16, 185, 129, 0.2); border: 1px solid #059669; color: #a7f3d0;" class="w-full text-left px-3 py-2 rounded-xl font-bold text-xs flex justify-between items-center cursor-pointer">
                        <span>🔔 Tes Layar Adhan & Bel</span>
                        <span>▶</span>
                    </button>

                    <button @click="triggerIqamah('Dzuhur')" style="background-color: rgba(245, 158, 11, 0.2); border: 1px solid #d97706; color: #fde68a;" class="w-full text-left px-3 py-2 rounded-xl font-bold text-xs flex justify-between items-center cursor-pointer">
                        <span>⏳ Tes Hitung Mundur Iqamah</span>
                        <span>▶</span>
                    </button>

                    <button @click="triggerStandby" style="background-color: rgba(244, 63, 94, 0.2); border: 1px solid #e11d48; color: #fecdd3;" class="w-full text-left px-3 py-2 rounded-xl font-bold text-xs flex justify-between items-center cursor-pointer">
                        <span>📱 Tes Standby Sholat</span>
                        <span>▶</span>
                    </button>

                    <button @click="resetToNormal" style="background-color: #1e293b; color: #cbd5e1;" class="w-full text-left px-3 py-2 rounded-xl font-bold text-xs flex justify-between items-center cursor-pointer">
                        <span>🔄 Reset Tampilan Normal</span>
                        <span>↺</span>
                    </button>
                </div>
            </div>
        </Transition>

    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.98); }
    to { opacity: 1; transform: scale(1); }
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

@keyframes marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

.animate-marquee {
    display: inline-block;
    animation: marquee 35s linear infinite;
}
</style>
