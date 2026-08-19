<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import {
    ArrowDownTrayIcon,
    PrinterIcon,
    ShareIcon,
    MapPinIcon,
    ClockIcon,
    SparklesIcon,
    CalendarDaysIcon,
    MagnifyingGlassIcon,
    CheckCircleIcon,
    BookOpenIcon,
    HeartIcon,
    SunIcon,
    MoonIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    scheduleData: {
        type: Object,
        required: true
    },
    todaySchedule: {
        type: Object,
        default: null
    },
    activeDayIndex: {
        type: Number,
        default: -1
    },
    currentParams: {
        type: Object,
        required: true
    },
    cityPresets: {
        type: Array,
        default: () => []
    },
    defaultMasjidLocation: {
        type: Object,
        default: () => ({})
    }
});

// State
const searchQuery = ref('');
const selectedPhase = ref('all'); // 'all', '1', '2', '3'
const activeTab = ref('niat'); // 'niat', 'buka', 'lailatul', 'amalan'
const isLocating = ref(false);
const toastMessage = ref('');
const showToast = ref(false);

// Live Countdown
const now = ref(new Date());
let timerInterval = null;

onMounted(() => {
    timerInterval = setInterval(() => {
        now.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

const triggerToast = (msg) => {
    toastMessage.value = msg;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3500);
};

// Filtered Schedule
const filteredSchedule = computed(() => {
    return props.scheduleData.schedule.filter(item => {
        // Phase filter
        if (selectedPhase.value === '1' && item.day > 10) return false;
        if (selectedPhase.value === '2' && (item.day <= 10 || item.day > 20)) return false;
        if (selectedPhase.value === '3' && item.day <= 20) return false;

        // Search query
        if (searchQuery.value.trim()) {
            const q = searchQuery.value.toLowerCase();
            const matchDay = item.day.toString().includes(q);
            const matchDate = item.formatted_date.toLowerCase().includes(q);
            const matchHijri = item.hijri_day.toLowerCase().includes(q);
            return matchDay || matchDate || matchHijri;
        }

        return true;
    });
});

// Countdown Target Calculation
const countdownInfo = computed(() => {
    const today = props.todaySchedule || props.scheduleData.schedule[0];
    if (!today) return { label: 'Menuju Berbuka', hours: '00', minutes: '00', seconds: '00', targetTime: '--:--', isBuka: true };

    const currentTime = now.value;
    const todayDateStr = currentTime.toISOString().split('T')[0];

    // Parse today's Imsak and Maghrib times
    const [imsakH, imsakM] = (today.imsak || '04:30').split(':').map(Number);
    const [maghribH, maghribM] = (today.maghrib || '18:15').split(':').map(Number);

    const imsakDate = new Date(currentTime);
    imsakDate.setHours(imsakH, imsakM, 0, 0);

    const maghribDate = new Date(currentTime);
    maghribDate.setHours(maghribH, maghribM, 0, 0);

    let targetDate = maghribDate;
    let label = 'Menuju Berbuka Puasa';
    let targetTime = today.maghrib;
    let isBuka = true;

    if (currentTime < imsakDate) {
        targetDate = imsakDate;
        label = 'Menuju Waktu Imsak (Sahur)';
        targetTime = today.imsak;
        isBuka = false;
    } else if (currentTime >= maghribDate) {
        // Countdown to tomorrow's imsak
        targetDate = new Date(imsakDate);
        targetDate.setDate(targetDate.getDate() + 1);
        label = 'Menuju Imsak Esok Hari';
        targetTime = today.imsak;
        isBuka = false;
    }

    const diff = Math.max(0, targetDate - currentTime);
    const hours = Math.floor(diff / (1000 * 60 * 60)).toString().padStart(2, '0');
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
    const seconds = Math.floor((diff % (1000 * 60)) / 1000).toString().padStart(2, '0');

    return {
        label,
        hours,
        minutes,
        seconds,
        targetTime,
        isBuka
    };
});

// Location handlers
const changeCity = (e) => {
    const selectedCity = props.cityPresets.find(c => c.name === e.target.value);
    if (selectedCity) {
        router.get('/ramadhan', {
            lat: selectedCity.lat,
            lng: selectedCity.lng,
            city: selectedCity.name,
            tz: selectedCity.timezone,
            year: props.currentParams.year
        }, { preserveScroll: true });
    }
};

const resetToMasjidLocation = () => {
    router.get('/ramadhan', {
        lat: props.defaultMasjidLocation.lat,
        lng: props.defaultMasjidLocation.lng,
        city: props.defaultMasjidLocation.city,
        year: props.currentParams.year
    }, { preserveScroll: true });
};

const useGpsLocation = () => {
    if (!navigator.geolocation) {
        triggerToast('Geolocation tidak didukung pada browser Anda.');
        return;
    }

    isLocating.value = true;
    navigator.geolocation.getCurrentPosition(
        (position) => {
            isLocating.value = false;
            router.get('/ramadhan', {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
                city: 'Lokasi Saya (GPS)',
                year: props.currentParams.year
            }, { preserveScroll: true });
            triggerToast('Jadwal diperbarui berdasarkan GPS perangkat Anda.');
        },
        (error) => {
            isLocating.value = false;
            triggerToast('Gagal mengakses GPS: ' + error.message);
        }
    );
};

// PDF Download
const downloadPdf = () => {
    const params = new URLSearchParams({
        lat: props.currentParams.lat,
        lng: props.currentParams.lng,
        city: props.currentParams.city,
        year: props.currentParams.year,
        tz: props.currentParams.tz
    });
    window.open(`/ramadhan/pdf?${params.toString()}`, '_blank');
};

// WhatsApp Share Copy
const copyTodaySchedule = () => {
    const today = props.todaySchedule || props.scheduleData.schedule[0];
    if (!today) return;

    const text = `🌙 *JADWAL IMSAKIYAH RAMADHAN ${props.scheduleData.hijri_year} H*
🕌 *${props.scheduleData.city} & Sekitarnya*
📅 ${today.hijri_day} (${today.formatted_date})

⏱️ *Imsak:* ${today.imsak} WIB
🌅 *Subuh:* ${today.subuh} WIB
☀️ *Terbit:* ${today.terbit} WIB
🌤️ *Dhuha:* ${today.dhuha} WIB
☀️ *Dzuhur:* ${today.dzuhur} WIB
⛅ *Ashar:* ${today.ashar} WIB
🌇 *Maghrib (Buka Puasa):* ${today.maghrib} WIB
🌙 *Isya:* ${today.isya} WIB

✨ *Doa Buka Puasa:*
_Dzahabaz zhama'u wabtallatil 'uruuqu wa tsabatal ajru in syaa-allaah_
(Telah hilang rasa haus, telah basah urat-urat, dan telah pasti pahala insya Allah)

📍 Jadwal lengkap 30 hari & download PDF:
${window.location.origin}/ramadhan`;

    navigator.clipboard.writeText(text).then(() => {
        triggerToast('✅ Jadwal hari ini berhasil disalin! Siap dibagikan ke WhatsApp.');
    });
};
</script>

<template>
    <Head title="Kalender Ramadhan & Jadwal Imsakiyah Interaktif" />

    <PublicLayout :transparentNav="false">
        <!-- Toast Notification -->
        <transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="showToast" 
                class="fixed bottom-6 right-6 z-50 max-w-md bg-slate-900 text-white px-5 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-emerald-500/30 backdrop-blur-xl"
            >
                <SparklesIcon class="w-6 h-6 text-amber-400 flex-shrink-0 animate-spin" />
                <span class="text-sm font-semibold">{{ toastMessage }}</span>
            </div>
        </transition>

        <div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-20 pt-8 sm:pt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- HERO SECTION -->
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-900 via-teal-950 to-slate-900 text-white shadow-2xl border border-emerald-800/40 p-6 sm:p-10">
                    <!-- Islamic Geometric Background Art -->
                    <div class="absolute -right-20 -top-20 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-20 -bottom-20 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
                    
                    <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                        <!-- Left Banner Content -->
                        <div class="lg:col-span-7 space-y-4">
                            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-bold uppercase tracking-wider backdrop-blur-md">
                                <SparklesIcon class="w-4 h-4 text-amber-400" />
                                <span>Bulan Suci Ramadhan {{ scheduleData.hijri_year }} H</span>
                            </div>

                            <h1 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                                Jadwal Imsakiyah & <br class="hidden sm:block" />
                                <span class="bg-gradient-to-r from-emerald-300 via-teal-200 to-amber-300 bg-clip-text text-transparent">
                                    Kalender Ramadhan
                                </span>
                            </h1>

                            <p class="text-slate-300 text-sm sm:text-base max-w-xl leading-relaxed">
                                Jadwal waktu sholat, imsak, dan buka puasa resmi 30 hari penuh untuk wilayah 
                                <strong class="text-amber-300 font-bold">{{ scheduleData.city }}</strong> 
                                dan sekitarnya berdasarkan hisab Kementerian Agama RI.
                            </p>

                            <!-- Quick Action Buttons on Hero -->
                            <div class="pt-2 flex flex-wrap gap-3">
                                <button
                                    @click="downloadPdf"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 font-bold text-sm shadow-lg shadow-amber-500/25 hover:shadow-amber-500/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200"
                                >
                                    <ArrowDownTrayIcon class="w-5 h-5" />
                                    <span>Download PDF Imsakiyah</span>
                                </button>

                                <button
                                    @click="copyTodaySchedule"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-sm border border-white/15 backdrop-blur-md hover:-translate-y-0.5 transition-all duration-200"
                                >
                                    <ShareIcon class="w-5 h-5 text-emerald-400" />
                                    <span>Salin Jadwal Hari Ini (WA)</span>
                                </button>
                            </div>
                        </div>

                        <!-- Right Live Countdown & Highlight Card -->
                        <div class="lg:col-span-5">
                            <div class="bg-white/10 dark:bg-slate-900/60 backdrop-blur-xl border border-white/20 dark:border-slate-800 rounded-2xl p-6 shadow-2xl relative overflow-hidden">
                                <!-- Top status -->
                                <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="relative flex h-3 w-3">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                        </span>
                                        <span class="text-xs font-bold text-emerald-300 uppercase tracking-wide">Live Timer</span>
                                    </div>
                                    <span class="text-xs text-slate-300 font-medium">
                                        {{ todaySchedule ? todaySchedule.hijri_day : 'Ramadhan ' + scheduleData.hijri_year + ' H' }}
                                    </span>
                                </div>

                                <!-- Countdown Info -->
                                <div class="text-center space-y-3">
                                    <div class="text-xs font-semibold uppercase tracking-widest text-slate-300 flex items-center justify-center gap-1.5">
                                        <MoonIcon v-if="countdownInfo.isBuka" class="w-4 h-4 text-amber-400" />
                                        <SunIcon v-else class="w-4 h-4 text-emerald-400" />
                                        <span>{{ countdownInfo.label }}</span>
                                        <span class="text-amber-300 font-bold">({{ countdownInfo.targetTime }} WIB)</span>
                                    </div>

                                    <!-- Digital Timer Digits -->
                                    <div class="flex items-center justify-center gap-2 sm:gap-3 py-2">
                                        <div class="flex flex-col items-center">
                                            <div class="w-16 sm:w-20 h-16 sm:h-20 bg-slate-950/80 rounded-2xl border border-emerald-500/30 flex items-center justify-center text-2xl sm:text-3xl font-black text-white font-mono shadow-inner">
                                                {{ countdownInfo.hours }}
                                            </div>
                                            <span class="text-[10px] uppercase font-bold text-slate-400 mt-1">Jam</span>
                                        </div>

                                        <span class="text-2xl font-bold text-emerald-400 pb-5 animate-pulse">:</span>

                                        <div class="flex flex-col items-center">
                                            <div class="w-16 sm:w-20 h-16 sm:h-20 bg-slate-950/80 rounded-2xl border border-emerald-500/30 flex items-center justify-center text-2xl sm:text-3xl font-black text-white font-mono shadow-inner">
                                                {{ countdownInfo.minutes }}
                                            </div>
                                            <span class="text-[10px] uppercase font-bold text-slate-400 mt-1">Menit</span>
                                        </div>

                                        <span class="text-2xl font-bold text-emerald-400 pb-5 animate-pulse">:</span>

                                        <div class="flex flex-col items-center">
                                            <div class="w-16 sm:w-20 h-16 sm:h-20 bg-slate-950/80 rounded-2xl border border-amber-500/30 flex items-center justify-center text-2xl sm:text-3xl font-black text-amber-300 font-mono shadow-inner">
                                                {{ countdownInfo.seconds }}
                                            </div>
                                            <span class="text-[10px] uppercase font-bold text-slate-400 mt-1">Detik</span>
                                        </div>
                                    </div>

                                    <!-- Quick Timing Badges -->
                                    <div class="grid grid-cols-2 gap-2.5 pt-2 border-t border-white/10">
                                        <div class="bg-white/5 rounded-xl p-2.5 text-center border border-white/10">
                                            <div class="text-[11px] text-slate-300 font-medium">Imsak Hari Ini</div>
                                            <div class="text-lg font-black text-emerald-300 font-mono">{{ todaySchedule?.imsak || '--:--' }}</div>
                                        </div>
                                        <div class="bg-white/5 rounded-xl p-2.5 text-center border border-white/10">
                                            <div class="text-[11px] text-slate-300 font-medium">Buka Puasa (Maghrib)</div>
                                            <div class="text-lg font-black text-amber-300 font-mono">{{ todaySchedule?.maghrib || '--:--' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CONTROLS & FILTER BAR -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-slate-800 space-y-4">
                    <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
                        
                        <!-- Location & GPS controls -->
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800 px-3 py-2 rounded-xl text-slate-700 dark:text-slate-200 text-sm font-medium">
                                <MapPinIcon class="w-5 h-5 text-emerald-600" />
                                <span>Kota:</span>
                                <select 
                                    :value="scheduleData.city"
                                    @change="changeCity"
                                    class="bg-transparent border-0 font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm cursor-pointer"
                                >
                                    <option v-for="preset in cityPresets" :key="preset.name" :value="preset.name" class="dark:bg-slate-900">
                                        {{ preset.name }}
                                    </option>
                                    <option v-if="!cityPresets.some(p => p.name === scheduleData.city)" :value="scheduleData.city" class="dark:bg-slate-900">
                                        {{ scheduleData.city }}
                                    </option>
                                </select>
                            </div>

                            <button
                                @click="useGpsLocation"
                                :disabled="isLocating"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 text-xs font-bold hover:bg-emerald-100 transition-colors disabled:opacity-50"
                                title="Gunakan koordinat GPS perangkat Anda"
                            >
                                <span v-if="isLocating" class="w-3.5 h-3.5 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin"></span>
                                <MapPinIcon v-else class="w-4 h-4" />
                                <span>{{ isLocating ? 'Mendeteksi...' : 'GPS Saya' }}</span>
                            </button>

                            <button
                                v-if="scheduleData.city !== defaultMasjidLocation.city"
                                @click="resetToMasjidLocation"
                                class="text-xs text-slate-500 hover:text-emerald-600 underline font-medium"
                            >
                                Reset ke Lokasi Masjid
                            </button>
                        </div>

                        <!-- Search & Filter Options -->
                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Search box -->
                            <div class="relative flex-1 sm:w-64">
                                <MagnifyingGlassIcon class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Cari hari / tgl..."
                                    class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 focus:border-emerald-500 focus:ring-emerald-500 text-slate-900 dark:text-white"
                                />
                            </div>

                            <!-- PDF Print Button -->
                            <button
                                @click="downloadPdf"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs shadow-md shadow-emerald-600/20 hover:bg-emerald-700 transition-colors"
                            >
                                <PrinterIcon class="w-4 h-4" />
                                <span>Cetak / PDF</span>
                            </button>
                        </div>
                    </div>

                    <!-- Phase Tabs (10 Hari ke-1, 2, 3) -->
                    <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-1">Fase Ramadhan:</span>
                        
                        <button
                            @click="selectedPhase = 'all'"
                            :class="[
                                'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all',
                                selectedPhase === 'all'
                                    ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 shadow-sm'
                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200'
                            ]"
                        >
                            Semua (30 Hari)
                        </button>

                        <button
                            @click="selectedPhase = '1'"
                            :class="[
                                'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all',
                                selectedPhase === '1'
                                    ? 'bg-emerald-600 text-white shadow-sm'
                                    : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100'
                            ]"
                        >
                            1-10 Ramadhan (Rahmat)
                        </button>

                        <button
                            @click="selectedPhase = '2'"
                            :class="[
                                'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all',
                                selectedPhase === '2'
                                    ? 'bg-teal-600 text-white shadow-sm'
                                    : 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-400 hover:bg-teal-100'
                            ]"
                        >
                            11-20 Ramadhan (Maghfirah)
                        </button>

                        <button
                            @click="selectedPhase = '3'"
                            :class="[
                                'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all',
                                selectedPhase === '3'
                                    ? 'bg-amber-600 text-white shadow-sm'
                                    : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 hover:bg-amber-100'
                            ]"
                        >
                            21-30 Ramadhan (Itqun Minan Nar)
                        </button>
                    </div>
                </div>

                <!-- 30-DAY IMSAKIYAH TABLE -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <CalendarDaysIcon class="w-6 h-6 text-emerald-600" />
                                <span>Tabel Lengkap Imsakiyah 1446 H</span>
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Menampilkan {{ filteredSchedule.length }} hari jadwal &bull; Standar waktu Kementerian Agama RI
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/40 text-amber-800 dark:text-amber-300 text-xs font-bold">
                                <span>Maghrib = Waktu Buka Puasa</span>
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-100 dark:bg-slate-800/80 text-[11px] uppercase tracking-wider font-bold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700">
                                    <th class="py-3.5 px-4 text-center">Ramadhan</th>
                                    <th class="py-3.5 px-4">Hari, Tanggal</th>
                                    <th class="py-3.5 px-3 text-center text-sky-700 dark:text-sky-400 bg-sky-50 dark:bg-sky-950/30">Imsak</th>
                                    <th class="py-3.5 px-3 text-center">Subuh</th>
                                    <th class="py-3.5 px-3 text-center text-slate-400">Terbit</th>
                                    <th class="py-3.5 px-3 text-center text-slate-400">Dhuha</th>
                                    <th class="py-3.5 px-3 text-center">Dzuhur</th>
                                    <th class="py-3.5 px-3 text-center">Ashar</th>
                                    <th class="py-3.5 px-3 text-center text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 font-black">Maghrib (Buka)</th>
                                    <th class="py-3.5 px-3 text-center">Isya</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs font-medium">
                                <tr 
                                    v-for="(row, idx) in filteredSchedule" 
                                    :key="row.day"
                                    :class="[
                                        'transition-colors',
                                        todaySchedule && todaySchedule.day === row.day 
                                            ? 'bg-emerald-50/90 dark:bg-emerald-950/40 ring-2 ring-emerald-500/50 font-semibold' 
                                            : idx % 2 === 0 ? 'bg-white dark:bg-slate-900' : 'bg-slate-50/50 dark:bg-slate-900/50 hover:bg-slate-100/60 dark:hover:bg-slate-800/60'
                                    ]"
                                >
                                    <!-- Day / Badge -->
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <span 
                                                class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs"
                                                :class="[
                                                    todaySchedule && todaySchedule.day === row.day 
                                                        ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 scale-110' 
                                                        : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'
                                                ]"
                                            >
                                                {{ row.day }}
                                            </span>
                                            <span v-if="todaySchedule && todaySchedule.day === row.day" class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-emerald-600 text-white tracking-wider animate-pulse">
                                                Hari Ini
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Date -->
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ row.formatted_date }}</div>
                                        <div class="text-[10px] text-slate-400">{{ row.phase }}</div>
                                    </td>

                                    <!-- Imsak -->
                                    <td class="py-3 px-3 text-center font-mono font-bold text-sky-700 dark:text-sky-300 bg-sky-50/50 dark:bg-sky-950/20">
                                        {{ row.imsak }}
                                    </td>

                                    <!-- Subuh -->
                                    <td class="py-3 px-3 text-center font-mono text-slate-800 dark:text-slate-200">
                                        {{ row.subuh }}
                                    </td>

                                    <!-- Terbit -->
                                    <td class="py-3 px-3 text-center font-mono text-slate-400">
                                        {{ row.terbit }}
                                    </td>

                                    <!-- Dhuha -->
                                    <td class="py-3 px-3 text-center font-mono text-slate-400">
                                        {{ row.dhuha }}
                                    </td>

                                    <!-- Dzuhur -->
                                    <td class="py-3 px-3 text-center font-mono text-slate-800 dark:text-slate-200">
                                        {{ row.dzuhur }}
                                    </td>

                                    <!-- Ashar -->
                                    <td class="py-3 px-3 text-center font-mono text-slate-800 dark:text-slate-200">
                                        {{ row.ashar }}
                                    </td>

                                    <!-- Maghrib (Buka Puasa) -->
                                    <td class="py-3 px-3 text-center font-mono font-black text-amber-900 dark:text-amber-300 bg-amber-100/50 dark:bg-amber-950/30 text-sm">
                                        {{ row.maghrib }}
                                    </td>

                                    <!-- Isya -->
                                    <td class="py-3 px-3 text-center font-mono text-slate-800 dark:text-slate-200">
                                        {{ row.isya }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PANDUAN, NIAT & DOA BULAN RAMADHAN -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200 dark:border-slate-800 space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <BookOpenIcon class="w-6 h-6 text-emerald-600" />
                                <span>Panduan Doa & Niat Ibadah Ramadhan</span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Lafaz Arab, tulisan latin, dan terjemahan lengkap</p>
                        </div>
                    </div>

                    <!-- Tabs Selector -->
                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="activeTab = 'niat'"
                            :class="[
                                'px-4 py-2 rounded-xl text-xs font-bold transition-all',
                                activeTab === 'niat' 
                                    ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' 
                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200'
                            ]"
                        >
                            🤲 Niat Puasa Ramadhan
                        </button>
                        <button
                            @click="activeTab = 'buka'"
                            :class="[
                                'px-4 py-2 rounded-xl text-xs font-bold transition-all',
                                activeTab === 'buka' 
                                    ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' 
                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200'
                            ]"
                        >
                            🍹 Doa Berbuka Puasa
                        </button>
                        <button
                            @click="activeTab = 'lailatul'"
                            :class="[
                                'px-4 py-2 rounded-xl text-xs font-bold transition-all',
                                activeTab === 'lailatul' 
                                    ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' 
                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200'
                            ]"
                        >
                            ✨ Doa Lailatul Qadar & Tarawih
                        </button>
                        <button
                            @click="activeTab = 'amalan'"
                            :class="[
                                'px-4 py-2 rounded-xl text-xs font-bold transition-all',
                                activeTab === 'amalan' 
                                    ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' 
                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200'
                            ]"
                        >
                            🌟 5 Amalan Utama Ramadhan
                        </button>
                    </div>

                    <!-- Tab Content 1: Niat Puasa -->
                    <div v-if="activeTab === 'niat'" class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-2xl border border-slate-200 dark:border-slate-700/60 space-y-3">
                            <div class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">1. Niat Harian (Setiap Malam)</div>
                            <div class="text-xl font-arabic text-right text-slate-900 dark:text-white leading-loose py-1" dir="rtl">
                                نَوَيْتُ صَوْمَ غَدٍ عَنْ أَدَاءِ فَرْضِ شَهْرِ رَمَضَانَ هَذِهِ السَّنَةِ لِلَّهِ تَعَالَى
                            </div>
                            <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 italic">
                                "Nawaitu shauma ghadin 'an adaa'i fardhi syahri ramadhaana hadzihis sanati lillaahi ta'aalaa"
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                <strong>Artinya:</strong> "Saya niat berpuasa esok hari untuk menunaikan fardhu bulan Ramadhan tahun ini karena Allah Ta'ala."
                            </div>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-2xl border border-slate-200 dark:border-slate-700/60 space-y-3">
                            <div class="text-xs font-bold uppercase tracking-wider text-teal-600 dark:text-teal-400">2. Niat Puasa Sebulan Penuh (Madzhab Maliki)</div>
                            <div class="text-xl font-arabic text-right text-slate-900 dark:text-white leading-loose py-1" dir="rtl">
                                نَوَيْتُ صَوْمَ جَمِيعِ شَهْرِ رَمَضَانِ هَذِهِ السَّنَةِ فَرْضًا لِلَّهِ تَعَالَى
                            </div>
                            <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 italic">
                                "Nawaitu shauma jamii'i syahri ramadhaani hadzihis sanati fardhan lillaahi ta'aalaa"
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                <strong>Artinya:</strong> "Saya niat berpuasa sebulan penuh di bulan Ramadhan tahun ini sebagai kewajiban karena Allah Ta'ala."
                            </div>
                        </div>
                    </div>

                    <!-- Tab Content 2: Doa Buka Puasa -->
                    <div v-if="activeTab === 'buka'" class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                        <div class="bg-amber-50/50 dark:bg-amber-950/20 p-5 rounded-2xl border border-amber-200 dark:border-amber-800/40 space-y-3">
                            <div class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Doa Shahih (HR. Abu Dawud)</div>
                            <div class="text-xl font-arabic text-right text-slate-900 dark:text-white leading-loose py-1" dir="rtl">
                                ذَهَبَ الظَّمَأُ وَابْتَلَّتِ الْعُرُوقُ وَثَبَتَ الأَجْرُ إِنْ شَاءَ اللَّهُ
                            </div>
                            <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 italic">
                                "Dzahabaz zhama'u wabtallatil 'uruuqu wa tsabatal ajru in syaa-allaah"
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                <strong>Artinya:</strong> "Telah hilang rasa haus, telah basah urat-urat, dan telah pasti pahala, insya Allah."
                            </div>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-2xl border border-slate-200 dark:border-slate-700/60 space-y-3">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Doa Buka Puasa Umum</div>
                            <div class="text-xl font-arabic text-right text-slate-900 dark:text-white leading-loose py-1" dir="rtl">
                                اللَّهُمَّ لَكَ صُمْتُ وَعَلَى رِزْقِكَ أَفْطَرْتُ
                            </div>
                            <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 italic">
                                "Allaahumma laka shumtu wa 'alaa rizqika afthartu"
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                <strong>Artinya:</strong> "Ya Allah, untuk-Mu aku berpuasa dan dengan rezeki-Mu aku berbuka."
                            </div>
                        </div>
                    </div>

                    <!-- Tab Content 3: Lailatul Qadar & Tarawih -->
                    <div v-if="activeTab === 'lailatul'" class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-2xl border border-slate-200 dark:border-slate-700/60 space-y-3">
                            <div class="text-xs font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">Doa Malam Lailatul Qadar (HR. Tirmidzi)</div>
                            <div class="text-xl font-arabic text-right text-slate-900 dark:text-white leading-loose py-1" dir="rtl">
                                اللَّهُمَّ إِنَّكَ عَفُوٌّ تُحِبُّ الْعَفْوَ فَاعْفُ عَنِّي
                            </div>
                            <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 italic">
                                "Allaahumma innaka 'afuwwun tuhibbul 'afwa fa'fu 'annii"
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                <strong>Artinya:</strong> "Ya Allah, sesungguhnya Engkau Maha Pemaaf dan menyukai ampunan, maka ampunilah aku."
                            </div>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-2xl border border-slate-200 dark:border-slate-700/60 space-y-3">
                            <div class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Niat Sholat Tarawih (Makmum)</div>
                            <div class="text-xl font-arabic text-right text-slate-900 dark:text-white leading-loose py-1" dir="rtl">
                                أُصَلِّي سُنَّةَ التَّرَاوِيحِ رَكْعَتَيْنِ مَأْمُومًا لِلَّهِ تَعَالَى
                            </div>
                            <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 italic">
                                "Ushalli sunnatat taraawiihi rak'ataini ma'muuman lillaahi ta'aalaa"
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                <strong>Artinya:</strong> "Aku niat sholat sunnah Tarawih dua rakaat sebagai makmum karena Allah Ta'ala."
                            </div>
                        </div>
                    </div>

                    <!-- Tab Content 4: 5 Amalan Utama -->
                    <div v-if="activeTab === 'amalan'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40">
                            <div class="text-2xl mb-2">📖</div>
                            <h4 class="font-bold text-sm text-slate-900 dark:text-white">1. Tadarus Al-Qur'an</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                                Targetkan one day one juz untuk mengkhatamkan Al-Qur'an di bulan diturunkannya wahyu.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40">
                            <div class="text-2xl mb-2">🤝</div>
                            <h4 class="font-bold text-sm text-slate-900 dark:text-white">2. Sedekah & Ifthar</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                                Memberi makan orang berbuka puasa mendapatkan pahala setara orang yang berpuasa tersebut.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-sky-50 dark:bg-sky-950/30 border border-sky-200 dark:border-sky-800/40">
                            <div class="text-2xl mb-2">🕌</div>
                            <h4 class="font-bold text-sm text-slate-900 dark:text-white">3. Qiyamul Lail & Tarawih</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                                Menghidupkan malam Ramadhan dengan sholat tarawih, tahajjud, dan witir berjamaah di masjid.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-purple-50 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-800/40">
                            <div class="text-2xl mb-2">⛺</div>
                            <h4 class="font-bold text-sm text-slate-900 dark:text-white">4. I'tikaf 10 Hari Terakhir</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                                Berdiam diri di masjid untuk mendekatkan diri kepada Allah dan memburu malam Lailatul Qadar.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&display=swap');

.font-arabic {
    font-family: 'Amiri', serif;
}
</style>
