<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useIntervalFn, useNow } from '@vueuse/core';

const props = defineProps({
    currentTime: String,
    todayPrayerTimes: Object,
    nextPrayer: Object,
    isFriday: Boolean,
    fridaySchedule: Object,
    slides: {
        type: Array,
        default: () => []
    },
    recentDonations: {
        type: Array,
        default: () => []
    },
    monthlyStats: {
        type: Object,
        default: () => ({ income: '0', expense: '0', balance: '0' })
    },
    wishlists: {
        type: Array,
        default: () => []
    },
    displaySettings: {
        type: Object,
        default: () => ({})
    },
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

// Fullscreen toggle
const isFullscreen = ref(false);
const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
        isFullscreen.value = true;
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen().catch(() => {});
            isFullscreen.value = false;
        }
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
    if (!props.nextPrayer || !props.nextPrayer.time) return '00:00:00';
    
    const [hours, minutes] = props.nextPrayer.time.split(':');
    const prayerTime = new Date(now.value);
    prayerTime.setHours(parseInt(hours, 10), parseInt(minutes, 10), 0, 0);
    
    if (props.nextPrayer.tomorrow) {
        prayerTime.setDate(prayerTime.getDate() + 1);
    }
    
    const diff = prayerTime - now.value;
    if (diff < 0) return '00:00:00';
    
    const totalSeconds = Math.floor(diff / 1000);
    const hrs = Math.floor(totalSeconds / 3600);
    const mins = Math.floor((totalSeconds % 3600) / 60);
    const secs = totalSeconds % 60;
    
    if (hrs > 0) {
        return `-${String(hrs).padStart(2, '0')}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }
    return `-${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
});

// Carousel management
const SLIDE_DURATION_MS = 10000;
const currentSlideIndex = ref(0);

// Slide List Definition (Consistent, Fixed-Length Sequence)
const slideItems = computed(() => {
    const list = [];
    
    // Dynamic slides from database
    if (props.slides && props.slides.length > 0) {
        props.slides.forEach((slide) => {
            list.push({ type: 'info', data: slide });
        });
    } else {
        // Default welcome slide if empty
        list.push({
            type: 'info',
            data: {
                title: 'Selamat Datang di ' + (props.displaySettings?.site_name || 'MasjidVision'),
                content: props.displaySettings?.running_text || 'Mari makmurkan masjid kita dengan sholat berjamaah dan menjaga ketertiban serta kebersihan rumah Allah.',
                image_url: null
            }
        });
    }
    
    // Friday schedule slide (if available)
    if (props.fridaySchedule && props.fridaySchedule.khatib && props.fridaySchedule.khatib !== '-') {
        list.push({ type: 'friday', data: props.fridaySchedule });
    }

    // Financial summary slide
    list.push({ type: 'financial', data: props.monthlyStats });

    // Wishlist / wakaf slide (if exists)
    if (props.wishlists && props.wishlists.length > 0) {
        list.push({ type: 'wishlist', data: props.wishlists });
    }

    return list;
});

const totalSlides = computed(() => slideItems.value.length);

useIntervalFn(() => {
    if (displayMode.value === 'NORMAL' && totalSlides.value > 0) {
        currentSlideIndex.value = (currentSlideIndex.value + 1) % totalSlides.value;
    }
}, SLIDE_DURATION_MS);

const currentSlide = computed(() => {
    if (totalSlides.value === 0) return null;
    const safeIndex = currentSlideIndex.value % totalSlides.value;
    return slideItems.value[safeIndex];
});

// Running Ticker Content
const tickerContent = computed(() => {
    const settingsText = props.displaySettings?.running_text || 'Selamat datang di rumah Allah. Mohon heningkan nada dering ponsel Anda dan luruskan shaf.';
    const donations = props.recentDonations?.map(d => `💚 Donasi ${d.category}: ${d.amount}`) || [];
    return `${settingsText}   ✦   ${donations.length > 0 ? donations.join('   ✦   ') + '   ✦   ' : ''}`;
});

// Reload Inertia state every 60s
useIntervalFn(() => {
    router.reload({
        only: ['todayPrayerTimes', 'nextPrayer', 'recentDonations', 'slides', 'monthlyStats', 'wishlists', 'fridaySchedule'],
        preserveScroll: true,
    });
}, 60000);

// Key shortcuts ('D' for Demo, 'M' for Mute, 'F' for Fullscreen)
const handleKeyDown = (e) => {
    if (e.key === 'd' || e.key === 'D') {
        showDemoControls.value = !showDemoControls.value;
    } else if (e.key === 'm' || e.key === 'M') {
        audioMuted.value = !audioMuted.value;
    } else if (e.key === 'f' || e.key === 'F') {
        toggleFullscreen();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

// Clean Prayer Times 6-Pillar List
const prayerTimesList = computed(() => {
    if (!props.todayPrayerTimes) return [];
    return [
        { name: 'Subuh', time: props.todayPrayerTimes.subuh || '04:30', isPrayer: true },
        { name: 'Terbit', time: props.todayPrayerTimes.sunrise || '05:45', isPrayer: false },
        { name: 'Dzuhur', time: props.todayPrayerTimes.dhuhr || '12:05', isPrayer: true },
        { name: 'Ashar', time: props.todayPrayerTimes.asr || '15:20', isPrayer: true },
        { name: 'Maghrib', time: props.todayPrayerTimes.maghrib || '18:10', isPrayer: true },
        { name: 'Isya', time: props.todayPrayerTimes.isha || '19:25', isPrayer: true },
    ];
});

const isNextPrayer = (prayerName) => {
    if (!props.nextPrayer || !props.nextPrayer.name) return false;
    const p1 = prayerName.toLowerCase();
    const p2 = props.nextPrayer.name.toLowerCase();
    return p1 === p2 || (p1 === 'dzuhur' && p2 === 'dhuhr') || (p1 === 'ashar' && p2 === 'asr') || (p1 === 'isya' && p2 === 'isha');
};
</script>

<template>
    <Head title="Display Digital TV Masjid" />

    <!-- Root App Shell - Fixed 100vh Landscape TV Layout (Strictly No Scroll) -->
    <div style="background-color: #030712; color: #ffffff;" class="h-screen w-screen max-h-screen overflow-hidden flex flex-col justify-between select-none font-sans">
        
        <!-- ------------------------------------------------------------- -->
        <!-- STATE 1: ADHAN SCREEN OVERLAY                                  -->
        <!-- ------------------------------------------------------------- -->
        <Transition name="fade">
            <div v-if="displayMode === 'ADHAN'" style="background-color: #030712;" class="fixed inset-0 z-50 flex flex-col items-center justify-center p-8 text-center">
                <div class="relative z-10 space-y-6 max-w-4xl">
                    <div style="background-color: rgba(16, 185, 129, 0.2); border-color: #34d399;" class="w-24 h-24 mx-auto rounded-full border-4 flex items-center justify-center text-5xl shadow-2xl animate-bounce">
                        🕌
                    </div>
                    <div style="background-color: rgba(16, 185, 129, 0.2); border-color: #34d399; color: #6ee7b7;" class="inline-block px-6 py-2 rounded-full border font-extrabold uppercase tracking-widest text-sm">
                        WAKTU SHOLAT TIBA
                    </div>
                    <h1 style="color: #ffffff;" class="text-6xl md:text-8xl font-black tracking-tight uppercase drop-shadow-lg">
                        ADHAN {{ activePrayerName }}
                    </h1>
                    <p style="color: #a7f3d0;" class="text-2xl font-light">
                        Telah masuk waktu sholat {{ activePrayerName }} untuk wilayah {{ displaySettings?.site_address || 'Masjid' }} dan sekitarnya
                    </p>
                    <div class="pt-4">
                        <div style="color: #fbbf24;" class="text-6xl md:text-7xl font-mono font-black tracking-tight">
                            {{ formatCountdown(adhanSecondsRemaining) }}
                        </div>
                        <span style="color: #9ca3af;" class="text-xs font-bold uppercase tracking-widest mt-2 block">Menuju Hitung Mundur Iqamah</span>
                    </div>
                    <button @click="triggerIqamah(activePrayerName)" style="background-color: #10b981; color: #030712;" class="mt-6 px-8 py-3 rounded-full font-black text-base shadow-2xl transition cursor-pointer border-none">
                        Lanjut ke Iqamah Sekarang ⏩
                    </button>
                </div>
            </div>
        </Transition>

        <!-- ------------------------------------------------------------- -->
        <!-- STATE 2: IQAMAH COUNTDOWN OVERLAY                             -->
        <!-- ------------------------------------------------------------- -->
        <Transition name="fade">
            <div v-if="displayMode === 'IQAMAH'" style="background-color: #030712;" class="fixed inset-0 z-50 flex flex-col items-center justify-between p-8 text-center">
                <div class="relative z-10 pt-4">
                    <span style="background-color: rgba(245, 158, 11, 0.2); border-color: #fbbf24; color: #fcd34d;" class="px-6 py-2 rounded-full border font-extrabold uppercase tracking-widest text-sm">
                        HITUNG MUNDUR IQAMAH — {{ activePrayerName }}
                    </span>
                </div>

                <div class="relative z-10 my-auto space-y-4">
                    <div style="color: #fbbf24;" class="text-[9rem] md:text-[12rem] leading-none font-mono font-black tracking-tighter">
                        {{ formatCountdown(iqamahSecondsRemaining) }}
                    </div>
                    <h2 style="color: #ffffff;" class="text-3xl md:text-5xl font-black tracking-tight max-w-3xl mx-auto uppercase">
                        LURUSKAN DAN RAPATKAN SHAF
                    </h2>
                    <p style="color: #fde68a;" class="text-xl md:text-2xl font-medium">
                        Mohon heningkan / matikan nada dering ponsel Anda
                    </p>
                </div>

                <div class="relative z-10 pb-4 flex gap-4">
                    <button @click="triggerStandby" style="background-color: #f59e0b; color: #030712;" class="px-8 py-3 rounded-full font-black text-base shadow-xl cursor-pointer border-none">
                        Mulai Sholat (Layar Senyap) ⏹️
                    </button>
                    <button @click="resetToNormal" style="background-color: #1f2937; color: #d1d5db; border-color: #374151;" class="px-6 py-3 rounded-full font-bold text-base border cursor-pointer">
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
                    <div class="text-7xl opacity-80 animate-pulse">📵</div>
                    <h1 style="color: #ffffff;" class="text-4xl md:text-6xl font-black tracking-tight leading-tight uppercase">
                        LURUSKAN & RAPATKAN SHAF
                    </h1>
                    <p style="color: #cbd5e1;" class="text-xl md:text-2xl font-light">
                        Mohon heningkan HP demi menjaga kekhusyukan ibadah sholat
                    </p>
                    <div style="color: #64748b;" class="font-mono text-sm pt-6">
                        Layar kembali aktif dalam {{ formatCountdown(standbySecondsRemaining) }}
                    </div>
                    <button @click="resetToNormal" style="background-color: #111827; border-color: #374151; color: #94a3b8;" class="mt-4 px-6 py-2 rounded-full border text-xs cursor-pointer">
                        Kembali ke Tampilan Utama
                    </button>
                </div>
            </div>
        </Transition>

        <!-- ------------------------------------------------------------- -->
        <!-- TOP HEADER BAR (Compact, Clean & High-Contrast)               -->
        <!-- ------------------------------------------------------------- -->
        <header style="height: 72px; background-color: #0f172a; border-bottom: 2px solid #1e293b;" class="px-6 flex items-center justify-between shadow-xl z-20 shrink-0">
            <!-- Left: Mosque Identity -->
            <div class="flex items-center gap-3.5">
                <div style="background-color: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #34d399;" class="w-11 h-11 rounded-2xl flex items-center justify-center text-2xl shadow-inner">
                    🕌
                </div>
                <div>
                    <h1 style="color: #ffffff;" class="text-xl font-black uppercase tracking-wider leading-tight">
                        {{ displaySettings?.site_name || 'MASJIDVISION' }}
                    </h1>
                    <p style="color: #94a3b8;" class="text-xs font-medium tracking-wide truncate max-w-md">
                        {{ displaySettings?.site_address || 'Pusat Ibadah & Dakwah Umat' }}
                    </p>
                </div>
            </div>

            <!-- Center: Next Prayer Countdown Badge -->
            <div v-if="nextPrayer" style="background-color: #020617; border: 1px solid #10b981;" class="hidden md:flex items-center gap-3 px-4 py-1.5 rounded-2xl shadow-md">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                <div class="flex flex-col">
                    <span style="color: #34d399;" class="text-[10px] font-black uppercase tracking-widest">
                        Menuju {{ nextPrayer.name }} ({{ nextPrayer.time }})
                    </span>
                    <span style="color: #fbbf24;" class="text-base font-black font-mono leading-none mt-0.5">
                        {{ prayerCountdown }}
                    </span>
                </div>
            </div>

            <!-- Right: Date & Big Digital Clock -->
            <div class="flex items-center gap-6">
                <!-- Hijri & Gregorian Dates -->
                <div class="text-right hidden sm:flex flex-col justify-center">
                    <span v-if="todayPrayerTimes?.hijri_date" style="color: #34d399;" class="text-xs font-bold tracking-wide">
                        {{ todayPrayerTimes.hijri_date }}
                    </span>
                    <span style="color: #cbd5e1;" class="text-xs font-semibold">
                        {{ formattedDate }}
                    </span>
                </div>

                <!-- Live Digital Clock -->
                <div style="border-left: 1px solid #334155;" class="pl-4 flex items-center">
                    <span style="color: #34d399;" class="text-3xl md:text-4xl font-black font-mono tracking-tight">
                        {{ formattedTime }}
                    </span>
                </div>
            </div>
        </header>

        <!-- ------------------------------------------------------------- -->
        <!-- MAIN INFORMATION STAGE (Fixed-Height Stable Slide Area)      -->
        <!-- ------------------------------------------------------------- -->
        <main class="flex-1 w-full px-6 py-4 flex flex-col justify-center overflow-hidden relative">
            
            <!-- Fixed Frame Container (Layout Never Shifts or Jumps) -->
            <div style="background-color: #0b1329; border: 2px solid #1e293b;" class="w-full h-full rounded-3xl p-6 shadow-2xl flex flex-col justify-between relative overflow-hidden">
                
                <!-- Slide Header: Category Pill + Progress Indicator -->
                <div style="border-bottom: 1px solid #1e293b;" class="w-full flex items-center justify-between pb-3 shrink-0">
                    <div class="flex items-center gap-3">
                        <span style="background-color: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #6ee7b7;" class="px-3.5 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            {{ currentSlide?.type === 'info' ? 'INFORMASI MASJID' : currentSlide?.type === 'financial' ? 'LAPORAN KAS' : currentSlide?.type === 'friday' ? 'JADWAL JUMAT' : 'PROGRAM WAKAF' }}
                        </span>
                    </div>

                    <!-- Slide Dots -->
                    <div class="flex items-center gap-3">
                        <span style="color: #94a3b8;" class="text-xs font-semibold">
                            Slide {{ currentSlideIndex + 1 }} / {{ totalSlides }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            <div 
                                v-for="i in totalSlides" 
                                :key="i"
                                :style="currentSlideIndex === i - 1 ? 'background-color: #fbbf24; width: 20px;' : 'background-color: #1e293b; width: 8px;'"
                                class="h-2 rounded-full transition-all duration-300"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE VIEWPORT (Fixed Geometry & Serene Absolute Crossfade) -->
                <div class="flex-1 w-full h-full relative overflow-hidden">
                    <Transition name="peaceful-fade">
                        <div 
                            v-if="currentSlide" 
                            :key="`slide-${currentSlideIndex}`"
                            class="absolute inset-0 w-full h-full flex items-center justify-center"
                        >
                            <!-- 1. INFO / ANNOUNCEMENT SLIDE -->
                            <div 
                                v-if="currentSlide.type === 'info'" 
                                class="w-full h-full flex items-center justify-center"
                            >
                                <!-- Layout A: With Poster Image (Horizontal Split) -->
                                <div v-if="currentSlide.data.image_url" class="w-full h-full flex flex-row items-center justify-center gap-8 px-4">
                                    <div class="h-full max-h-[300px] w-auto max-w-[45%] flex items-center justify-center shrink-0">
                                        <img 
                                            :src="currentSlide.data.image_url" 
                                            :alt="currentSlide.data.title" 
                                            style="border: 2px solid #334155;"
                                            class="h-full max-h-[300px] w-auto object-contain rounded-2xl shadow-2xl"
                                        />
                                    </div>
                                    <div class="flex-1 flex flex-col justify-center text-left space-y-3 max-w-2xl">
                                        <h2 style="color: #fbbf24;" class="text-3xl md:text-4xl font-black uppercase tracking-tight leading-tight line-clamp-2">
                                            {{ currentSlide.data.title }}
                                        </h2>
                                        <p style="color: #f1f5f9;" class="text-lg md:text-xl font-medium leading-relaxed line-clamp-6">
                                            {{ currentSlide.data.content }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Layout B: Text Only Announcement (Centered Grand Typography) -->
                                <div v-else class="w-full h-full flex flex-col items-center justify-center text-center px-8 max-w-4xl mx-auto space-y-4">
                                    <h2 style="color: #fbbf24;" class="text-3xl md:text-5xl font-black uppercase tracking-tight leading-tight">
                                        {{ currentSlide.data.title }}
                                    </h2>
                                    <p style="color: #f1f5f9;" class="text-xl md:text-2xl font-medium leading-relaxed max-w-3xl">
                                        {{ currentSlide.data.content }}
                                    </p>
                                </div>
                            </div>

                            <!-- 2. FINANCIAL REPORT SLIDE -->
                            <div 
                                v-else-if="currentSlide.type === 'financial'" 
                                class="w-full h-full flex flex-col items-center justify-center text-center px-4"
                            >
                                <h2 style="color: #ffffff;" class="text-2xl md:text-3xl font-black uppercase tracking-tight mb-6">
                                    Rekapitulasi Kas & Infaq Masjid Bulan Ini
                                </h2>

                                <div class="grid grid-cols-3 gap-6 w-full max-w-4xl">
                                    <div style="background-color: #020617; border: 2px solid #059669;" class="p-6 rounded-3xl shadow-xl flex flex-col items-center justify-center">
                                        <span style="color: #34d399;" class="text-xs font-black uppercase tracking-widest mb-2">Total Pemasukan</span>
                                        <span style="color: #ffffff;" class="text-2xl md:text-3xl font-black font-mono">Rp {{ monthlyStats?.income || '0' }}</span>
                                    </div>
                                    <div style="background-color: #020617; border: 2px solid #e11d48;" class="p-6 rounded-3xl shadow-xl flex flex-col items-center justify-center">
                                        <span style="color: #fb7185;" class="text-xs font-black uppercase tracking-widest mb-2">Total Pengeluaran</span>
                                        <span style="color: #ffffff;" class="text-2xl md:text-3xl font-black font-mono">Rp {{ monthlyStats?.expense || '0' }}</span>
                                    </div>
                                    <div style="background-color: #020617; border: 2px solid #d97706;" class="p-6 rounded-3xl shadow-xl flex flex-col items-center justify-center">
                                        <span style="color: #fbbf24;" class="text-xs font-black uppercase tracking-widest mb-2">Saldo Kas Terkini</span>
                                        <span style="color: #fde68a;" class="text-2xl md:text-3xl font-black font-mono">Rp {{ monthlyStats?.balance || '0' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. FRIDAY OFFICERS SCHEDULE SLIDE -->
                            <div 
                                v-else-if="currentSlide.type === 'friday'" 
                                class="w-full h-full flex flex-col items-center justify-center text-center px-4"
                            >
                                <h2 style="color: #ffffff;" class="text-2xl md:text-3xl font-black uppercase tracking-tight mb-6">
                                    Petugas Sholat Jumat ({{ fridaySchedule?.date }})
                                </h2>

                                <div class="grid grid-cols-3 gap-6 w-full max-w-4xl">
                                    <div style="background-color: #020617; border: 2px solid #059669;" class="p-6 rounded-3xl shadow-xl">
                                        <span style="color: #34d399;" class="text-xs font-black uppercase tracking-widest block mb-2">Khatib</span>
                                        <span style="color: #ffffff;" class="text-xl md:text-2xl font-extrabold">{{ fridaySchedule?.khatib || '-' }}</span>
                                    </div>
                                    <div style="background-color: #020617; border: 2px solid #059669;" class="p-6 rounded-3xl shadow-xl">
                                        <span style="color: #34d399;" class="text-xs font-black uppercase tracking-widest block mb-2">Imam</span>
                                        <span style="color: #ffffff;" class="text-xl md:text-2xl font-extrabold">{{ fridaySchedule?.imam || '-' }}</span>
                                    </div>
                                    <div style="background-color: #020617; border: 2px solid #059669;" class="p-6 rounded-3xl shadow-xl">
                                        <span style="color: #34d399;" class="text-xs font-black uppercase tracking-widest block mb-2">Muadzin & Bilal</span>
                                        <span style="color: #ffffff;" class="text-lg md:text-xl font-bold">{{ fridaySchedule?.muadzin || '-' }} / {{ fridaySchedule?.bilal || '-' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. WISHLIST / WAKAF PROGRAM SLIDE -->
                            <div 
                                v-else-if="currentSlide.type === 'wishlist'" 
                                class="w-full h-full flex flex-col items-center justify-center text-center px-4"
                            >
                                <h2 style="color: #ffffff;" class="text-2xl md:text-3xl font-black uppercase tracking-tight mb-5">
                                    Program Pengadaan & Wakaf Kebutuhan Masjid
                                </h2>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full max-w-4xl">
                                    <div 
                                        v-for="item in wishlists.slice(0, 2)" 
                                        :key="item.id"
                                        style="background-color: #020617; border: 1px solid #1e293b;"
                                        class="p-4 rounded-2xl text-left flex flex-col justify-between"
                                    >
                                        <div class="flex justify-between items-center mb-2">
                                            <span style="color: #ffffff;" class="text-base font-extrabold truncate pr-2">{{ item.item_name }}</span>
                                            <span style="color: #34d399;" class="text-lg font-black font-mono">{{ item.progress_percentage || 0 }}%</span>
                                        </div>
                                        <div style="background-color: #1e293b;" class="w-full rounded-full h-3 overflow-hidden mb-2">
                                            <div 
                                                style="background: linear-gradient(to right, #10b981, #fbbf24);"
                                                class="h-full rounded-full transition-all duration-700"
                                                :style="{ width: Math.min(item.progress_percentage || 0, 100) + '%' }"
                                            ></div>
                                        </div>
                                        <div style="color: #94a3b8;" class="flex justify-between text-[11px] font-bold">
                                            <span>Terkumpul: <strong style="color: #6ee7b7;">{{ item.formatted_total_fulfilled }}</strong></span>
                                            <span>Target: <strong style="color: #ffffff;">{{ item.formatted_total_target }}</strong></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>

            </div>

        </main>

        <!-- ------------------------------------------------------------- -->
        <!-- PRAYER TIMES BAR (6 Balanced, Clear, Legible Slots)          -->
        <!-- ------------------------------------------------------------- -->
        <section style="height: 100px; background-color: #0f172a; border-top: 2px solid #1e293b;" class="px-6 py-2.5 flex items-center justify-between shrink-0 z-20">
            <div style="display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; width: 100%; height: 100%;">
                
                <div 
                    v-for="prayer in prayerTimesList" 
                    :key="prayer.name"
                    :style="isNextPrayer(prayer.name) 
                        ? 'background: linear-gradient(135deg, #065f46 0%, #047857 100%); border: 2px solid #34d399; box-shadow: 0 0 16px rgba(16, 185, 129, 0.3);' 
                        : 'background-color: #020617; border: 1px solid #1e293b;'"
                    class="h-full rounded-2xl flex flex-col items-center justify-center p-1.5 transition-all duration-300 relative overflow-hidden"
                >
                    <!-- Active Indicator Pill for Next Prayer -->
                    <div 
                        v-if="isNextPrayer(prayer.name)" 
                        style="background-color: #fbbf24; color: #020617;"
                        class="px-2 py-0.5 text-[8px] font-black uppercase tracking-wider rounded-full mb-0.5 leading-none shadow"
                    >
                        SELANJUTNYA
                    </div>

                    <!-- Prayer Name -->
                    <span 
                        :style="isNextPrayer(prayer.name) ? 'color: #a7f3d0;' : 'color: #94a3b8;'"
                        :class="isNextPrayer(prayer.name) ? 'font-black' : 'font-bold'"
                        class="text-xs uppercase tracking-wider leading-none"
                    >
                        {{ prayer.name }}
                    </span>

                    <!-- Prayer Time (Clear, Legible, Crisp Digital Number) -->
                    <span 
                        :style="isNextPrayer(prayer.name) ? 'color: #ffffff;' : 'color: #ffffff;'"
                        class="font-mono font-black text-2xl md:text-3xl tracking-tight leading-none mt-1"
                    >
                        {{ prayer.time }}
                    </span>
                </div>

            </div>
        </section>

        <!-- ------------------------------------------------------------- -->
        <!-- RUNNING TICKER FOOTER BAR                                     -->
        <!-- ------------------------------------------------------------- -->
        <footer style="height: 44px; background-color: #064e3b; border-top: 2px solid #047857;" class="flex items-center overflow-hidden relative shrink-0 z-20">
            <!-- Label Badge -->
            <div style="background-color: #047857; color: #ffffff;" class="h-full px-5 flex items-center font-black text-xs uppercase tracking-wider shrink-0 z-10 shadow-lg gap-2">
                <span>📢</span> PENGUMUMAN
            </div>
            
            <!-- Marquee Running Text -->
            <div class="flex-1 overflow-hidden">
                <div style="color: #ecfdf5;" class="animate-marquee whitespace-nowrap text-sm md:text-base font-bold">
                    {{ tickerContent }}{{ tickerContent }}
                </div>
            </div>

            <!-- Quick Action Buttons: Mute / Fullscreen / Demo -->
            <div style="background-color: #020617; border-left: 1px solid #1e293b;" class="h-full flex items-center shrink-0 z-10">
                <button 
                    @click="audioMuted = !audioMuted" 
                    :title="audioMuted ? 'Suara Dimatikan' : 'Suara Aktif'"
                    style="color: #94a3b8;"
                    class="h-full px-3 text-xs hover:text-white transition flex items-center gap-1 cursor-pointer bg-transparent border-none"
                >
                    {{ audioMuted ? '🔇' : '🔊' }}
                </button>
                <button 
                    @click="toggleFullscreen" 
                    title="Layar Penuh [F]"
                    style="color: #94a3b8; border-left: 1px solid #1e293b;"
                    class="h-full px-3 text-xs hover:text-white transition flex items-center gap-1 cursor-pointer bg-transparent border-none"
                >
                    {{ isFullscreen ? '⤓' : '⤢' }}
                </button>
                <button 
                    @click="showDemoControls = !showDemoControls" 
                    title="Panel Uji Coba [D]"
                    style="color: #94a3b8; border-left: 1px solid #1e293b;"
                    class="h-full px-3 text-xs font-mono hover:text-white transition cursor-pointer bg-transparent border-none"
                >
                    [D]
                </button>
            </div>
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
                    <button @click="showDemoControls = false" style="color: #94a3b8;" class="hover:text-white font-bold cursor-pointer bg-transparent border-none">✕</button>
                </div>

                <div class="space-y-2">
                    <button @click="triggerAdhan('Dzuhur')" style="background-color: rgba(16, 185, 129, 0.2); border: 1px solid #059669; color: #a7f3d0;" class="w-full text-left px-3 py-2 rounded-xl font-bold flex justify-between items-center cursor-pointer">
                        <span>🔔 Tes Layar Adhan & Bel</span>
                        <span>▶</span>
                    </button>

                    <button @click="triggerIqamah('Dzuhur')" style="background-color: rgba(245, 158, 11, 0.2); border: 1px solid #d97706; color: #fde68a;" class="w-full text-left px-3 py-2 rounded-xl font-bold flex justify-between items-center cursor-pointer">
                        <span>⏳ Tes Countdown Iqamah</span>
                        <span>▶</span>
                    </button>

                    <button @click="triggerStandby" style="background-color: rgba(244, 63, 94, 0.2); border: 1px solid #e11d48; color: #fecdd3;" class="w-full text-left px-3 py-2 rounded-xl font-bold flex justify-between items-center cursor-pointer">
                        <span>📱 Tes Standby Sholat</span>
                        <span>▶</span>
                    </button>

                    <button @click="resetToNormal" style="background-color: #1e293b; color: #cbd5e1; border: 1px solid #334155;" class="w-full text-left px-3 py-2 rounded-xl font-bold flex justify-between items-center cursor-pointer">
                        <span>🔄 Reset Tampilan Normal</span>
                        <span>↺</span>
                    </button>
                </div>
            </div>
        </Transition>

    </div>
</template>

<style scoped>
/* Peaceful & Soothing Slide Transition for Mosque TV */
.peaceful-fade-enter-active,
.peaceful-fade-leave-active {
    transition: opacity 0.8s cubic-bezier(0.25, 1, 0.5, 1), transform 0.8s cubic-bezier(0.25, 1, 0.5, 1);
    position: absolute;
    width: 100%;
    height: 100%;
    inset: 0;
}

.peaceful-fade-enter-from {
    opacity: 0;
    transform: scale(0.97);
}

.peaceful-fade-leave-to {
    opacity: 0;
    transform: scale(1.02);
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
