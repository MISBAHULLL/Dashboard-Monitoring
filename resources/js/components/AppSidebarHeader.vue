<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import NotificationBell from '@/components/NotificationBell.vue';
import SearchSpotlight from '@/components/SearchSpotlight.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Search, Command } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const spotlightRef = ref<InstanceType<typeof SearchSpotlight> | null>(null);

function openSearch() {
    spotlightRef.value?.open();
}
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div class="ml-auto flex items-center gap-2">
            <NotificationBell />
            <SearchSpotlight ref="spotlightRef" />
            <button
                @click="openSearch"
                class="group flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-500 transition-all hover:border-slate-300 hover:bg-white hover:text-slate-700 hover:shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700"
            >
                <Search class="h-3.5 w-3.5" />
                <span class="hidden sm:inline">Cari...</span>
                <kbd class="hidden rounded border border-slate-200 bg-white px-1 py-0 text-[10px] font-semibold text-slate-400 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300 md:inline-block">
                    <span class="flex items-center gap-0.5"><Command class="h-2.5 w-2.5" />K</span>
                </kbd>
            </button>
        </div>
    </header>
</template>
