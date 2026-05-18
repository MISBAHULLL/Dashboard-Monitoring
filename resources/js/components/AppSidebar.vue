<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
    Building2,
    FileText,
    History,
    KanbanSquare,
    LayoutDashboard,
    ListChecks,
    Settings,
    Users,
    UserSquare2,
} from 'lucide-vue-next';
import { computed } from 'vue';

import NavUser from '@/components/NavUser.vue';
import SidebarBrandLogo from '@/components/sidebar-lucide/SidebarBrandLogo.vue';
import SidebarNavLucide from '@/components/sidebar-lucide/SidebarNavLucide.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as activityLogsIndex } from '@/routes/activity-logs';
import { index as clientsIndex } from '@/routes/clients';
import { index as documentsIndex } from '@/routes/documents';
import { edit as profileEdit } from '@/routes/profile';
import { index as tasksIndex } from '@/routes/tasks';
import { index as teamsIndex } from '@/routes/teams';
import { index as usersIndex } from '@/routes/users';

import type { NavItemLucide } from '@/types/navigation';

// Data user yang login (dari middleware HandleInertiaRequests)
const page = usePage();
const user = computed(() => page.props.auth.user);

// Menu utama (5 item dasar + 3 admin-only)
const mainNavItems = computed<NavItemLucide[]>(() => {
    const items: NavItemLucide[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutDashboard,
        },
        {
            title: 'Tabel Task',
            href: tasksIndex.url(),
            icon: ListChecks,
        },
        {
            title: 'Kanban Board',
            href: '/tasks-kanban',
            icon: KanbanSquare,
        },
        {
            title: 'Dokumen',
            href: documentsIndex.url(),
            icon: FileText,
        },
    ];

    if (user.value?.role === 'admin') {
        items.push({
            title: 'Audit Trail',
            href: activityLogsIndex.url(),
            icon: History,
        });
        items.push({
            title: 'Faskes / Client',
            href: clientsIndex.url(),
            icon: Building2,
        });
        items.push({
            title: 'Master Team',
            href: teamsIndex.url(),
            icon: Users,
        });
        items.push({
            title: 'Master User',
            href: usersIndex.url(),
            icon: UserSquare2,
        });
    }

    return items;
});

const footerNavItems: NavItemLucide[] = [
    {
        title: 'Pengaturan Sistem',
        href: profileEdit(),
        icon: Settings,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="sidebar-lucide-navy">
        <SidebarHeader class="p-0">
            <SidebarBrandLogo />
        </SidebarHeader>

        <SidebarContent>
            <SidebarNavLucide :items="mainNavItems" label="Main navigation" />
        </SidebarContent>

        <SidebarFooter class="gap-1 p-0">
            <SidebarNavLucide :items="footerNavItems" />
            <div class="px-3 pb-2 group-data-[collapsible=icon]:px-0">
                <NavUser variant="lucide" />
            </div>
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
