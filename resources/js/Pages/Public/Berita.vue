<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';

defineProps({
    posts: Object, // Paginator object
});
</script>

<template>
    <Head title="Berita & Kegiatan" />

    <PublicLayout :transparent-nav="false">
    <div class="pt-32 pb-16 bg-slate-50 dark:bg-slate-950 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Page Header -->
            <div class="text-center mb-16">
                <span class="badge badge-soft bg-persian-blue/10 text-persian-blue border-none uppercase tracking-widest font-bold mb-3">Informasi Terkini</span>
                <h1 class="text-3xl md:text-5xl font-black text-persian-navy dark:text-white tracking-tight leading-none mb-4">Berita & Kegiatan</h1>
                <div class="w-16 h-1 bg-persian-gold mx-auto rounded-full"></div>
                <p class="text-slate-500 dark:text-slate-400 max-w-2xl mx-auto mt-6 text-sm font-medium">
                    Informasi terbaru seputar kegiatan, agenda, dan program Masjid.
                </p>
            </div>

            <!-- Posts Grid -->
            <div v-if="posts && posts.data && posts.data.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 lg:gap-16">
                    <Link
                        v-for="post in posts.data"
                        :key="post.id"
                        :href="route('public.post', post.slug)"
                        class="group space-y-6"
                    >
                        <!-- Post Image -->
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
                                    {{ post.author_name }}
                                </span>
                            </div>

                            <h2 class="text-xl lg:text-2xl font-black text-persian-navy dark:text-white leading-[1.3] group-hover:text-persian-blue transition-colors line-clamp-2 uppercase tracking-tighter">
                                {{ post.title }}
                            </h2>

                            <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed line-clamp-2 font-medium">
                                {{ post.excerpt }}
                            </p>

                            <span class="inline-flex items-center gap-1 text-persian-blue font-black text-[10px] uppercase tracking-widest group-hover:gap-2 transition-all">
                                Baca Selengkapnya <ArrowRightIcon class="w-3 h-3" />
                            </span>
                        </div>
                    </Link>
                </div>

                <!-- Pagination -->
                <div class="mt-16 flex justify-center gap-2">
                    <Link
                        v-for="(link, key) in posts.links"
                        :key="key"
                        :href="link.url"
                        v-html="link.label"
                        :class="[
                            'px-4 py-2 rounded-xl text-sm font-bold transition-all',
                            link.active ? 'bg-persian-blue text-white shadow-lg' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700',
                            !link.url && 'opacity-40 cursor-not-allowed hidden'
                        ]"
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-24 bg-white dark:bg-slate-900 rounded-[2.5rem] border border-dashed border-slate-200 dark:border-slate-800">
                <span class="text-6xl block mb-6">📰</span>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight mb-2">Belum Ada Berita</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm max-w-sm mx-auto">Belum ada berita atau kegiatan yang diterbitkan saat ini. Silakan kunjungi kembali nanti.</p>
                <Link href="/" class="mt-8 inline-flex items-center gap-2 px-6 py-3 bg-persian-blue text-white rounded-2xl font-bold text-sm hover:-translate-y-1 transition-all shadow-lg">
                    Kembali ke Beranda <ArrowRightIcon class="w-4 h-4" />
                </Link>
            </div>

        </div>
    </div>
    </PublicLayout>
</template>
