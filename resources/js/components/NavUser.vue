<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ChevronsUpDown, CircleUserRound } from 'lucide-vue-next';
import { computed } from 'vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import UserInfo from '@/components/UserInfo.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';

interface Props {
    /** `'lucide'` dipakai oleh sidebar redesign (navy + Lucide icons). */
    variant?: 'default' | 'lucide';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'default',
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const { isMobile, state } = useSidebar();
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        size="lg"
                        :class="[
                            'data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground',
                            props.variant === 'lucide' ? 'sidebar-lucide-item' : '',
                        ]"
                        data-test="sidebar-menu-button"
                    >
                        <template v-if="props.variant === 'lucide'">
                            <CircleUserRound
                                class="sidebar-lucide-icon shrink-0"
                                :stroke-width="1.75"
                                aria-hidden="true"
                            />
                            <span
                                class="sidebar-lucide-label flex-1 truncate text-left"
                            >{{ user.name }}</span>
                            <ChevronsUpDown
                                class="ml-auto size-4 shrink-0 text-white/80"
                                :stroke-width="2"
                            />
                        </template>
                        <template v-else>
                            <UserInfo :user="user" />
                            <ChevronsUpDown class="ml-auto size-4" />
                        </template>
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                    :side="
                        isMobile
                            ? 'bottom'
                            : state === 'collapsed'
                              ? 'left'
                              : 'bottom'
                    "
                    align="end"
                    :side-offset="4"
                >
                    <UserMenuContent :user="user" />
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
