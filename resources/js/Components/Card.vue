<script setup>
import { computed } from 'vue';

const props = defineProps({
    padding: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value),
    },
    hoverable: {
        type: Boolean,
        default: false,
    },
    clickable: {
        type: Boolean,
        default: false,
    },
    variant: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'islamic', 'gold'].includes(value),
    },
});

const paddingClasses = computed(() => {
    const sizes = {
        sm: 'p-4',
        md: 'p-6',
        lg: 'p-8',
        xl: 'p-10',
    };
    return sizes[props.padding];
});

const variantClasses = computed(() => {
    const variants = {
        default: 'bg-white dark:bg-slate-800 border-slate-100 dark:border-slate-700',
        islamic: 'bg-gradient-to-br from-primary-50 to-white dark:from-slate-900 dark:to-slate-800 border-primary-200 dark:border-slate-700',
        gold: 'bg-gradient-to-br from-secondary-50 to-white dark:from-slate-900 dark:to-slate-800 border-secondary-200 dark:border-slate-700',
    };
    return variants[props.variant];
});

const cardClasses = computed(() => {
    return [
        'rounded-xl shadow-soft border transition-all duration-200',
        paddingClasses.value,
        variantClasses.value,
        props.hoverable && 'hover:shadow-lg hover:-translate-y-1',
        props.clickable && 'cursor-pointer',
    ].filter(Boolean).join(' ');
});
</script>

<template>
    <div :class="cardClasses">
        <slot />
    </div>
</template>
