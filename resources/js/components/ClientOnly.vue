<script setup lang="ts">
import { onMounted, ref } from 'vue';

/**
 * ClientOnly — render default slot hanya di browser.
 * Berguna untuk membungkus komponen yang mengakses browser API (window, document, dll.)
 * agar tidak dieksekusi saat Inertia SSR.
 *
 * Slot `fallback` dipakai selama SSR + hydration awal.
 */

const mounted = ref(false);

onMounted(() => {
    mounted.value = true;
});
</script>

<template>
    <template v-if="mounted">
        <slot />
    </template>
    <template v-else>
        <slot name="fallback" />
    </template>
</template>
