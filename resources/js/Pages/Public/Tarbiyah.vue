<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    AcademicCapIcon, 
    BookOpenIcon, 
    CalendarDaysIcon, 
    UserIcon, 
    ClockIcon, 
    MapPinIcon,
    MagnifyingGlassIcon,
    ChevronRightIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline';
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';

// Define layout
defineOptions({
    layout: PublicLayout
});

const props = defineProps({
    classes: Array,
    lectures: Array
});

// State
const searchQuery = ref('');
const selectedCategory = ref('Semua');
const showRegModal = ref(false);
const activeTab = ref('programs'); // 'programs' or 'lectures'
const selectedClassForReg = ref(null);

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab && ['programs', 'lectures'].includes(tab)) {
        activeTab.value = tab;
    }
});

// Form state for registration
const regForm = ref({
    name: '',
    phone: '',
    gender: 'ikhwan',
    notes: ''
});
const isSubmitted = ref(false);

// Unique categories list
const categories = computed(() => {
    const list = new Set(props.classes.map(c => c.category));
    return ['Semua', ...Array.from(list)];
});

// Filtered classes
const filteredClasses = computed(() => {
    return props.classes.filter(c => {
        const matchesSearch = c.title.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                              c.description.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                              c.instructor.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesCategory = selectedCategory.value === 'Semua' || c.category === selectedCategory.value;
        return matchesSearch && matchesCategory;
    });
});

// Open registration modal
const openRegistration = (item) => {
    selectedClassForReg.value = item;
    regForm.value = {
        name: '',
        phone: '',
        gender: 'ikhwan',
        notes: ''
    };
    isSubmitted.value = false;
    showRegModal.value = true;
};

// Handle registration submission (Simulation)
const submitRegistration = () => {
    if (!regForm.value.name || !regForm.value.phone) {
        alert('Silakan isi nama dan nomor telepon Anda.');
        return;
    }
    
    // Simulate API call
    isSubmitted.value = true;
    
    // Generate WA URL as alternative / action
    setTimeout(() => {
        const message = `Bismillah, saya ingin mendaftar Program Tarbiyah:\n\n*Program*: ${selectedClassForReg.value.title}\n*Nama*: ${regForm.value.name}\n*No. HP*: ${regForm.value.phone}\n*Gender*: ${regForm.value.gender === 'ikhwan' ? 'Laki-laki (Ikhwan)' : 'Perempuan (Akhwat)'}\n*Catatan*: ${regForm.value.notes || '-'}`;
        const encoded = encodeURIComponent(message);
        // Replace with dynamic WhatsApp number from settings or placeholder
        window.open(`https://wa.me/6281234567890?text=${encoded}`, '_blank');
        showRegModal.value = false;
    }, 1200);
};
</script>

<template>
    <Head title="Tarbiyah & Kajian Keislaman" />

    <div>
        <!-- 1. Hero Section -->
        <div class="relative min-h-[450px] flex items-center justify-center overflow-hidden">
            <div class="absolute inset-0 z-0">
                <img 
                    src="https://images.unsplash.com/photo-1507679799987-c73779587ccf?q=80&w=2000&auto=format&fit=crop" 
                    alt="Tarbiyah Background" 
                    class="w-full h-full object-cover scale-105 filter brightness-75"
                />
                <!-- Teal Overlay matching Bakri theme -->
                <div class="absolute inset-0 bg-gradient-to-r from-bakri-teal to-emerald-950 opacity-90 mix-blend-multiply"></div>
                <!-- Islamic pattern overlay -->
                <div class="absolute inset-0 opacity-[0.06] pointer-events-none bg-pattern-islamic bg-repeat"></div>
            </div>

            <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-16 text-center">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white text-xs font-bold uppercase tracking-widest mb-6">
                    📚 Lembaga Pendidikan Masjid
                </span>
                <h1 class="text-4xl md:text-6xl font-black text-white mb-6 leading-tight tracking-tight">
                    Tarbiyah & Kajian Keislaman
                </h1>
                <p class="text-lg md:text-xl text-white/80 max-w-3xl mx-auto font-light leading-relaxed">
                    Membina aqidah sahihah, menyempurnakan ibadah, serta menanamkan akhlakul karimah melalui sistem pembelajaran terstruktur untuk mewujudkan generasi rabbani.
                </p>
            </div>
        </div>

        <!-- 2. Main Navigation Tabs -->
        <div class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-16 z-30 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-center sm:justify-start space-x-8 h-16">
                    <button 
                        @click="activeTab = 'programs'"
                        :class="[
                            activeTab === 'programs' 
                                ? 'border-bakri-teal text-bakri-teal dark:text-emerald-400' 
                                : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:border-slate-300',
                            'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-bold uppercase tracking-wider transition-all gap-2'
                        ]"
                    >
                        <AcademicCapIcon class="w-5 h-5" />
                        Kelas Tarbiyah
                    </button>
                    <button 
                        @click="activeTab = 'lectures'"
                        :class="[
                            activeTab === 'lectures' 
                                ? 'border-bakri-teal text-bakri-teal dark:text-emerald-400' 
                                : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:border-slate-300',
                            'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-bold uppercase tracking-wider transition-all gap-2'
                        ]"
                    >
                        <CalendarDaysIcon class="w-5 h-5" />
                        Kajian Umum
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab Content 1: Kelas Tarbiyah (Sistem Pembelajaran Terstruktur) -->
        <div v-if="activeTab === 'programs'" class="py-12 bg-slate-50 dark:bg-slate-950 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Filter & Search Panel -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-soft border border-slate-100 dark:border-slate-800 mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <!-- Search Input -->
                    <div class="relative flex-grow max-w-md">
                        <MagnifyingGlassIcon class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 transform -translate-y-1/2" />
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            placeholder="Cari program belajar atau ustadz..."
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white border-none rounded-2xl focus:ring-2 focus:ring-bakri-teal/30 focus:bg-white transition-all text-sm"
                        />
                    </div>
                    
                    <!-- Categories filter pills -->
                    <div class="flex flex-wrap gap-2 items-center">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mr-2">Kategori:</span>
                        <button 
                            v-for="cat in categories" 
                            :key="cat" 
                            @click="selectedCategory = cat"
                            :class="[
                                selectedCategory === cat 
                                    ? 'bg-bakri-teal text-white shadow-lg shadow-bakri-teal/20' 
                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700',
                                'px-4 py-2 rounded-full text-xs font-bold transition-all'
                            ]"
                        >
                            {{ cat }}
                        </button>
                    </div>
                </div>

                <!-- Classes Grid -->
                <div v-if="filteredClasses.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div 
                        v-for="(item, index) in filteredClasses" 
                        :key="index"
                        class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-soft border border-slate-100 dark:border-slate-800 overflow-hidden flex flex-col hover:-translate-y-2 hover:shadow-2xl transition-all duration-300 group"
                    >
                        <!-- Top header -->
                        <div class="p-6 sm:p-8 pb-4 flex justify-between items-start">
                            <div class="w-12 h-12 bg-bakri-teal/10 dark:bg-bakri-teal/20 text-bakri-teal dark:text-emerald-400 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                                {{ item.icon }}
                            </div>
                            <span :class="[
                                item.badge.includes('Buka') ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900' : 
                                item.badge.includes('Baru') ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 border border-amber-100 dark:border-amber-900' : 
                                'bg-slate-150 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                                'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider'
                            ]">
                                {{ item.badge }}
                            </span>
                        </div>

                        <!-- Card Body -->
                        <div class="px-6 sm:px-8 pb-6 flex-grow">
                            <span class="text-[10px] font-black uppercase tracking-widest text-bakri-teal dark:text-emerald-400">{{ item.category }}</span>
                            <h3 class="text-xl font-black text-slate-800 dark:text-white mt-1 group-hover:text-bakri-teal transition-colors line-clamp-1">
                                {{ item.title }}
                            </h3>
                            <p class="text-slate-500 dark:text-slate-400 text-sm mt-3 leading-relaxed line-clamp-3">
                                {{ item.description }}
                            </p>
                        </div>

                        <!-- Card Details list -->
                        <div class="px-6 sm:px-8 py-5 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400 space-y-2.5">
                            <div class="flex items-center gap-2.5">
                                <UserIcon class="w-4 h-4 text-slate-400 shrink-0" />
                                <span>Pengajar: <strong class="text-slate-800 dark:text-white">{{ item.instructor }}</strong></span>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <ClockIcon class="w-4 h-4 text-slate-400 shrink-0" />
                                <span>Jadwal: <span class="font-medium">{{ item.schedule }}</span></span>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <BookOpenIcon class="w-4 h-4 text-slate-400 shrink-0" />
                                <span>Tingkatan: <span class="font-medium bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded text-[10px]">{{ item.level }}</span></span>
                            </div>
                        </div>

                        <!-- Action button -->
                        <div class="p-6 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
                            <button 
                                @click="openRegistration(item)"
                                class="w-full py-3 bg-bakri-teal hover:bg-slate-900 text-white rounded-xl font-bold text-sm tracking-wide uppercase transition-all flex items-center justify-center gap-1.5 hover:-translate-y-0.5 active:scale-95 shadow-lg shadow-bakri-teal/10"
                            >
                                Daftar Sekarang
                                <ChevronRightIcon class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-20 bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-soft">
                    <span class="text-5xl block mb-4">🔍</span>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Program Tidak Ditemukan</h3>
                    <p class="text-slate-400 mt-2 max-w-sm mx-auto">Kami tidak dapat menemukan program tarbiyah yang cocok dengan pencarian atau filter Anda.</p>
                    <button @click="searchQuery = ''; selectedCategory = 'Semua'" class="mt-6 px-6 py-2.5 bg-bakri-teal text-white rounded-full font-bold text-xs uppercase tracking-widest hover:bg-slate-900 transition-colors">
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab Content 2: Kajian Umum (Lecture Schedules) -->
        <div v-else class="py-12 bg-slate-50 dark:bg-slate-950 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-12">
                    <span class="badge badge-soft bg-bakri-teal/10 text-bakri-teal dark:text-emerald-400 border-none uppercase tracking-widest font-black mb-3">Agenda Terdekat</span>
                    <h2 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">Kajian Umum & Tabligh Akbar</h2>
                    <p class="text-slate-500 dark:text-slate-400 mt-2 max-w-xl mx-auto text-sm">Hadirilah majelis ilmu terbuka untuk umum yang menghadirkan asatidzah berkompeten dari berbagai kota.</p>
                </div>

                <!-- Lectures List/Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                    <div 
                        v-for="(item, index) in lectures" 
                        :key="index"
                        class="bg-white dark:bg-slate-900 rounded-3xl shadow-soft border border-slate-100 dark:border-slate-800 overflow-hidden flex flex-col sm:flex-row lg:flex-col xl:flex-row hover:shadow-2xl transition-all duration-300"
                    >
                        <!-- Cover image -->
                        <div class="w-full sm:w-2/5 lg:w-full xl:w-2/5 aspect-[16/9] sm:aspect-auto lg:aspect-[16/9] xl:aspect-auto relative min-h-[200px] sm:min-h-full lg:min-h-[200px] xl:min-h-full shrink-0">
                            <img :src="item.image" :alt="item.title" class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-gradient-to-t sm:bg-gradient-to-r lg:bg-gradient-to-t xl:bg-gradient-to-r from-slate-950/90 via-slate-950/40 to-transparent flex items-end sm:items-center lg:items-end xl:items-center p-6 sm:p-8 lg:p-6 xl:p-8">
                                <span class="bg-bakri-lime text-bakri-navy px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">Live Streaming</span>
                            </div>
                        </div>

                        <!-- Content Details -->
                        <div class="p-6 sm:p-8 lg:p-6 xl:p-8 flex-grow flex flex-col justify-between sm:w-3/5 lg:w-full xl:w-3/5">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Kajian Akbar</span>
                                <h3 class="text-xl font-black text-slate-800 dark:text-white mt-1 leading-snug">
                                    {{ item.title }}
                                </h3>
                                <p class="text-sm font-bold text-bakri-teal dark:text-emerald-400 mt-2 flex items-center gap-1.5">
                                    <UserIcon class="w-4 h-4" />
                                    {{ item.speaker }}
                                </p>
                            </div>

                            <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800 space-y-3 text-xs text-slate-600 dark:text-slate-400">
                                <div class="flex items-center gap-2">
                                    <CalendarDaysIcon class="w-5 h-5 text-slate-400 shrink-0" />
                                    <span>{{ item.date }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <ClockIcon class="w-5 h-5 text-slate-400 shrink-0" />
                                    <span>{{ item.time }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <MapPinIcon class="w-5 h-5 text-slate-400 shrink-0" />
                                    <span class="line-clamp-1">{{ item.location }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weekly Routine Study schedule -->
                <div class="mt-20">
                    <div class="bg-gradient-to-br from-slate-800 to-slate-950 rounded-[2.5rem] p-8 md:p-12 text-white shadow-2xl relative overflow-hidden">
                        <!-- Decorative glow -->
                        <div class="absolute top-0 right-0 w-80 h-80 bg-bakri-teal/20 rounded-full blur-3xl -mr-40 -mt-40"></div>
                        
                        <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-10">
                            <div class="max-w-2xl text-center lg:text-left">
                                <span class="bg-white/10 text-bakri-lime border border-white/10 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest mb-4 inline-block">📋 Protokol Kajian</span>
                                <h3 class="text-2xl md:text-3xl font-black tracking-tight leading-tight">Tata Tertib Menghadiri Majelis Ilmu</h3>
                                <p class="text-slate-400 mt-4 text-sm leading-relaxed">
                                    Untuk kenyamanan dan keberkahan majelis ilmu bersama, para jamaah dimohon untuk memperhatikan adab belajar seperti meluruskan niat, berpakaian rapi dan menutup aurat, merapatkan barisan shaf, menjaga ketenangan, serta membawa alat tulis untuk mencatat pelajaran.
                                </p>
                            </div>
                            <div class="shrink-0 w-full lg:w-auto text-center">
                                <Link 
                                    href="/ibadah/agenda" 
                                    class="inline-block px-8 py-4 bg-bakri-lime hover:bg-lime-500 text-bakri-navy font-black rounded-2xl shadow-xl hover:-translate-y-1 transition-all text-sm uppercase tracking-wider"
                                >
                                    Lihat Agenda Masjid
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- 3. General Registration CTA banner -->
        <section class="py-20 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
            <div class="max-w-4xl mx-auto px-4 text-center">
                <span class="text-5xl">💬</span>
                <h2 class="text-3xl md:text-4xl font-black text-slate-800 dark:text-white mt-4 tracking-tight">Konsultasi Program Tarbiyah</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-2 max-w-lg mx-auto text-sm leading-relaxed">
                    Masih bingung memilih program tarbiyah yang cocok untuk Anda atau anak Anda? Hubungi tim pelayanan pengurus kami untuk konsultasi gratis.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a 
                        href="https://wa.me/6281234567890" 
                        target="_blank" 
                        class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-full shadow-lg shadow-emerald-500/20 text-xs uppercase tracking-widest hover:-translate-y-1 transition-all"
                    >
                        Hubungi via WhatsApp
                    </a>
                </div>
            </div>
        </section>

        <!-- 4. Registration Modal -->
        <TransitionRoot as="template" :show="showRegModal">
            <Dialog as="div" class="relative z-[60]" @close="showRegModal = false">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-slate-900/75 dark:bg-slate-950/80 transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <DialogPanel class="relative transform overflow-hidden rounded-[2.5rem] bg-white dark:bg-slate-900 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                                <div class="bg-white dark:bg-slate-900 px-6 pb-6 pt-8 sm:p-8">
                                    <div v-if="!isSubmitted">
                                        <div class="flex items-center gap-3 mb-6">
                                            <div class="w-12 h-12 rounded-2xl bg-bakri-teal/10 text-bakri-teal dark:text-emerald-400 flex items-center justify-center text-xl shrink-0">
                                                {{ selectedClassForReg?.icon }}
                                            </div>
                                            <div>
                                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pendaftaran Kelas</span>
                                                <h3 class="text-xl font-black text-slate-800 dark:text-white leading-tight">{{ selectedClassForReg?.title }}</h3>
                                            </div>
                                        </div>

                                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
                                            Lengkapi formulir di bawah ini. Anda juga akan diarahkan langsung untuk memvalidasi pendaftaran melalui pengurus via WhatsApp.
                                        </p>

                                        <form @submit.prevent="submitRegistration" class="space-y-4">
                                            <div>
                                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nama Lengkap</label>
                                                <input 
                                                    v-model="regForm.name" 
                                                    type="text" 
                                                    required 
                                                    placeholder="Nama Anda"
                                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-bakri-teal/30 focus:border-bakri-teal transition-all text-sm font-medium"
                                                />
                                            </div>

                                            <div>
                                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nomor WhatsApp/HP</label>
                                                <input 
                                                    v-model="regForm.phone" 
                                                    type="tel" 
                                                    required 
                                                    placeholder="Contoh: 081234567890"
                                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-bakri-teal/30 focus:border-bakri-teal transition-all text-sm font-medium"
                                                />
                                            </div>

                                            <div>
                                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">Jenis Kelamin</label>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <label :class="[
                                                        regForm.gender === 'ikhwan' ? 'bg-bakri-teal/10 border-bakri-teal text-bakri-teal' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400',
                                                        'border rounded-xl p-3 flex items-center justify-center gap-2 cursor-pointer font-bold text-sm transition-all'
                                                    ]">
                                                        <input type="radio" value="ikhwan" v-model="regForm.gender" class="sr-only" />
                                                        👨 Ikhwan (Laki-laki)
                                                    </label>
                                                    <label :class="[
                                                        regForm.gender === 'akhwat' ? 'bg-bakri-teal/10 border-bakri-teal text-bakri-teal' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400',
                                                        'border rounded-xl p-3 flex items-center justify-center gap-2 cursor-pointer font-bold text-sm transition-all'
                                                    ]">
                                                        <input type="radio" value="akhwat" v-model="regForm.gender" class="sr-only" />
                                                        👩 Akhwat (Perempuan)
                                                    </label>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">Catatan / Keterangan Tambahan (Opsional)</label>
                                                <textarea 
                                                    v-model="regForm.notes" 
                                                    rows="2"
                                                    placeholder="Sebutkan tingkat pemahaman dasar Anda atau riwayat belajar jika ada..."
                                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-bakri-teal/30 focus:border-bakri-teal transition-all text-sm font-medium"
                                                ></textarea>
                                            </div>

                                            <div class="pt-4 flex gap-3">
                                                <button 
                                                    type="button" 
                                                    class="flex-1 py-3 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-500 rounded-xl text-sm font-bold transition-all"
                                                    @click="showRegModal = false"
                                                >
                                                    Batalkan
                                                </button>
                                                <button 
                                                    type="submit" 
                                                    class="flex-1 py-3 bg-bakri-teal hover:bg-slate-900 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-bakri-teal/10"
                                                >
                                                    Kirim Pendaftaran
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Success State -->
                                    <div v-else class="text-center py-8">
                                        <CheckCircleIcon class="w-16 h-16 text-emerald-500 mx-auto animate-bounce" />
                                        <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-4">Formulir Dikirim!</h3>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 max-w-sm mx-auto leading-relaxed">
                                            Bismillah, data pendaftaran Anda sedang diproses. Anda sekarang akan dialihkan ke WhatsApp untuk konfirmasi instan.
                                        </p>
                                        <div class="mt-8">
                                            <div class="w-12 h-12 border-4 border-bakri-teal border-t-transparent rounded-full animate-spin mx-auto"></div>
                                        </div>
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

    </div>
</template>
