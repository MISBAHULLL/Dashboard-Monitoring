<script setup lang="ts">
/**
 * Satu baris navigasi di sidebar Lucide variant.
 *
 * - Ikon Lucide 20×20 + label Plus Jakarta Sans 14 / 600 (putih)
 * - Active state lewat `useCurrentUrl` → `data-active="true"` + `aria-current="page"`
 * - Tooltip muncul saat sidebar collapsed (dihandle oleh `SidebarMenuButton`)
 */
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

import { SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItemLucide } from '@/types/navigation';

interface Props {
    item: NavItemLucide;
}

const props = defineProps<Props>();

const { isCurrentUrl } = useCurrentUrl();
const isActive = computed(() => isCurrentUrl(props.item.href));
</script>

<template>
    <SidebarMenuItem>
        <SidebarMenuButton
            as-child
            :is-active="isActive"
            :tooltip="item.title"
            class="sidebar-lucide-item"
        >
            <Link
                :href="item.href"
                :aria-current="isActive ? 'page' : undefined"
            >
                <component
                    :is="item.icon"
                    class="sidebar-lucide-icon shrink-0"
                    :stroke-width="1.75"
                    aria-hidden="true"
                />
                <span class="sidebar-lucide-label">{{ item.title }}</span>
            </Link>
        </SidebarMenuButton>
    </SidebarMenuItem>
</template>
