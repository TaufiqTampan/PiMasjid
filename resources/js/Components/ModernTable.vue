<script setup>
import { computed } from 'vue';

const props = defineProps({
    columns: {
        type: Array,
        required: true,
        // Expected format: [{ key: 'name', label: 'Name', sortable: true }]
    },
    data: {
        type: Array,
        default: () => [],
    },
    striped: {
        type: Boolean,
        default: false,
    },
    hoverable: {
        type: Boolean,
        default: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    stickyHeader: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['sort']);

const handleSort = (column) => {
    if (column.sortable) {
        emit('sort', column.key);
    }
};

const isEmpty = computed(() => !props.loading && props.data.length === 0);
</script>

<template>
    <div class="w-full overflow-x-auto scrollbar-thin rounded-xl border border-slate-200">
        <table class="w-full border-collapse">
            <thead :class="['bg-gradient-to-br from-teal-700 to-teal-900 text-white', stickyHeader && 'sticky top-0 z-10']">
                <tr>
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        :class="[
                            'px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider',
                            column.sortable && 'cursor-pointer hover:bg-primary-700 transition-colors',
                        ]"
                        @click="handleSort(column)"
                    >
                        <div class="flex items-center gap-2">
                            <slot :name="`header-${column.key}`" :column="column">
                                {{ column.label }}
                            </slot>
                            <svg
                                v-if="column.sortable"
                                class="w-4 h-4 opacity-50"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"
                                />
                            </svg>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <!-- Loading State -->
                <tr v-if="loading">
                    <td :colspan="columns.length" class="px-6 py-12">
                        <div class="flex flex-col items-center justify-center gap-3">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
                            <p class="text-sm text-slate-500">Memuat data...</p>
                        </div>
                    </td>
                </tr>

                <!-- Empty State -->
                <tr v-else-if="isEmpty">
                    <td :colspan="columns.length" class="px-6 py-12">
                        <div class="flex flex-col items-center justify-center gap-3">
                            <slot name="empty">
                                <svg class="w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                                    />
                                </svg>
                                <p class="text-slate-600 font-medium">Tidak ada data</p>
                                <p class="text-sm text-slate-500">Data belum tersedia saat ini</p>
                            </slot>
                        </div>
                    </td>
                </tr>

                <!-- Data Rows -->
                <tr
                    v-else
                    v-for="(row, index) in data"
                    :key="index"
                    :class="[
                        'border-b border-slate-100 transition-colors duration-150',
                        hoverable && 'hover:bg-slate-50',
                        striped && index % 2 === 1 && 'bg-slate-50/50',
                    ]"
                >
                    <td
                        v-for="column in columns"
                        :key="column.key"
                        class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300"
                    >
                        <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                            {{ row[column.key] }}
                        </slot>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination Slot -->
        <div v-if="$slots.pagination" class="border-t border-slate-200 bg-white px-6 py-4">
            <slot name="pagination" />
        </div>
    </div>
</template>
