<script setup lang="ts">
/**
 * List navigasi untuk sidebar Lucide variant. Membungkus `SidebarMenu`
 * shadcn-vue supaya keyboard navigation, collapsed tooltip, dan mobile
 * Sheet behaviour tetap bekerja.
 */
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
} from '@/components/ui/sidebar';
import type { NavItemLucide } from '@/types/navigation';

import SidebarNavItemLucide from './SidebarNavItemLucide.vue';

interface Props {
    items: NavItemLucide[];
    /** Accessible label — rendered sebagai `sr-only` supaya tidak terlihat. */
    label?: string;
}

defineProps<Props>();
</script>

<template>
    <SidebarGroup class="px-3 py-0">
        <SidebarGroupLabel v-if="label" class="sr-only">{{ label }}</SidebarGroupLabel>
        <SidebarMenu class="gap-0.5">
            <SidebarNavItemLucide
                v-for="item in items"
                :key="item.title"
                :item="item"
            />
        </SidebarMenu>
    </SidebarGroup>
</template>
