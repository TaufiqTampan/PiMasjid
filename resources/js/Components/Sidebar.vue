<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    HomeIcon, 
    CheckBadgeIcon, 
    BanknotesIcon, 
    PencilSquareIcon,
    CalendarIcon,
    CalendarDaysIcon,
    PresentationChartBarIcon,
    ArchiveBoxIcon,
    TvIcon,
    NewspaperIcon,
    Cog6ToothIcon,
    UsersIcon,
    PaintBrushIcon,
    GiftIcon,
    CakeIcon,
    AcademicCapIcon,
    HeartIcon,
    BuildingOfficeIcon
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = computed(() => page.props.auth.user);
const pendingCount = computed(() => page.props.pendingApprovalsCount || 0);

// Role-based menu items grouped by category
const menuGroups = computed(() => {
    const role = user.value?.role;
    if (!role) return [];
    
    const groups = [
        {
            title: 'Menu Utama',
            items: [
                { name: 'Dashboard', route: 'dashboard', icon: HomeIcon, visible: true }
            ]
        }
    ];

    // Approvals
    if (['ketua', 'super_admin', 'admin'].includes(role)) {
        groups.push({
            title: 'Persetujuan',
            items: [
                { name: 'Persetujuan', route: 'approvals.index', icon: CheckBadgeIcon, badge: pendingCount.value, visible: true }
            ]
        });
    }
    
    // Keuangan
    if (['bendahara', 'super_admin', 'admin'].includes(role)) {
        groups.push({
            title: 'Keuangan & Sosial',
            items: [
                { name: 'Transaksi', route: 'transactions.index', icon: BanknotesIcon, visible: true },
                { name: 'Zakat', route: 'zakat.index', icon: GiftIcon, visible: true },
                { name: 'Qurban', route: 'qurban.index', icon: CakeIcon, visible: true }
            ]
        });
    }

    // Operasional
    if (['marbot', 'super_admin', 'admin', 'ketua'].includes(role)) {
        groups.push({
            title: 'Operasional Masjid',
            items: [
                { name: 'Fasilitas & Booking', route: 'facilities.index', icon: BuildingOfficeIcon, visible: true },
                { name: 'Lumbung Pangan', route: 'lumbung-pangan.index', icon: HeartIcon, visible: true },
                { name: 'Jadwal Jumat', route: 'friday-schedules.index', icon: CalendarDaysIcon, visible: true },
                { name: 'Kelola Agenda', route: 'agendas.index', icon: CalendarIcon, visible: true },
                { name: 'Kelola Slide TV', route: 'slides.index', icon: PresentationChartBarIcon, visible: true },
                { name: 'Inventaris Aset', route: 'assets.index', icon: ArchiveBoxIcon, visible: true },
                { name: 'Kebutuhan Masjid', route: 'wishlists.index', icon: GiftIcon, visible: true },
                { name: 'TV Display', route: 'display.index', icon: TvIcon, visible: true, external: true }
            ]
        });
    }

    // Konten
    if (['super_admin', 'ketua'].includes(role)) {
        groups.push({
            title: 'Publikasi',
            items: [
                { name: 'Berita & Kegiatan', route: 'posts.index', icon: NewspaperIcon, visible: true },
                { name: 'Kelola Kajian', route: 'lectures.index', icon: AcademicCapIcon, visible: true }
            ]
        });
    }

    // Configuration
    if (role === 'super_admin') {
        groups.push({
            title: 'Konfigurasi Sistem',
            items: [
                { name: 'Pengaturan Web', route: 'settings.index', icon: Cog6ToothIcon, visible: true },
                { name: 'User Management', route: 'users.index', icon: UsersIcon, visible: true },
                { name: 'Struktur Pengurus', route: 'committee-members.index', icon: UsersIcon, visible: true },
                { name: 'UI Components', route: 'components.showcase', icon: PaintBrushIcon, visible: true }
            ]
        });
    }
    
    return groups;
});
</script>

<template>
    <nav class="bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 w-64 h-full flex flex-col overflow-hidden">
        <!-- User Info Compact -->
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-bold text-lg shadow-islamic">
                    {{ user.name.charAt(0) }}
                </div>
                <div class="min-w-0">
                    <div class="font-bold text-slate-900 dark:text-white truncate text-sm">{{ user.name }}</div>
                    <div class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
                        {{ user.role.replace('_', ' ') }}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Menu Scroll Area -->
        <div class="flex-1 overflow-y-auto py-4 px-3 custom-scrollbar overscroll-contain">
            <div v-for="group in menuGroups" :key="group.title" class="mb-6 last:mb-0">
                <div class="px-3 mb-2 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                    {{ group.title }}
                </div>
                <ul class="space-y-0.5">
                    <li v-for="item in group.items" :key="item.route">
                        <Link
                            v-if="!item.external"
                            :href="route(item.route)"
                            :class="[
                                'flex items-center justify-between px-3 py-2 rounded-lg transition-all duration-200 group',
                                route().current(item.route + '*')
                                    ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 font-semibold shadow-sm shadow-emerald-100/50 dark:shadow-none'
                                    : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:hover:text-slate-200'
                            ]"
                        >
                            <div class="flex items-center gap-2.5">
                                <component 
                                    :is="item.icon" 
                                    class="w-5 h-5 transition-colors"
                                    :class="route().current(item.route + '*') ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300'"
                                />
                                <span class="text-[13px]">{{ item.name }}</span>
                            </div>
                            <span
                                v-if="item.badge && item.badge > 0"
                                class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-rose-500 rounded-full animate-pulse"
                            >
                                {{ item.badge }}
                            </span>
                        </Link>
                        
                        <a
                            v-else
                            :href="route(item.route)"
                            target="_blank"
                            class="flex items-center justify-between px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:hover:text-slate-200 transition-all duration-200 group"
                        >
                            <div class="flex items-center gap-2.5">
                                <component 
                                    :is="item.icon" 
                                    class="w-5 h-5 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-colors"
                                />
                                <span class="text-[13px]">{{ item.name }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity">↗</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Role Badge Footer -->
        <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 shrink-0">
            <div class="flex items-center justify-between text-[11px]">
                <span class="text-slate-500 dark:text-slate-400">Access Level</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">
                    {{ user.role === 'super_admin' ? 'Full' : 'Standard' }}
                </span>
            </div>
        </div>
    </nav>
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
