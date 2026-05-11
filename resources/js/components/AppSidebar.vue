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

import { index as activityLogsIndex } from '@/actions/App/Http/Controllers/ActivityLogController';
import { index as clientsIndex } from '@/actions/App/Http/Controllers/ClientController';
import { index as documentsIndex } from '@/actions/App/Http/Controllers/DocumentController';
import { index as tasksIndex } from '@/actions/App/Http/Controllers/TaskController';
import { index as teamsIndex } from '@/actions/App/Http/Controllers/TeamController';
import { index as usersIndex } from '@/actions/App/Http/Controllers/UserController';

import NavUser from '@/components/NavUser.vue';
import SidebarBrandLogo from '@/components/sidebar-lucide/SidebarBrandLogo.vue';
import SidebarNavLucide from '@/components/sidebar-lucide/SidebarNavLucide.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';

// Route generator otomatis dari Laravel Wayfinder
import { dashboard } from '@/routes';

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
            title: 'Audit Trail',
            href: '/activity-logs',
            icon: History,
        },
        {
            title: 'Dokumen',
            href: documentsIndex.url(),
            icon: FileText,
        },
    ];

    if (user.value?.role === 'admin') {
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
        href: '#',
        icon: Settings,
    },
];

// `activityLogsIndex` diimpor untuk preservasi Wayfinder signature
// meski href literal dipakai agar konsisten dengan versi sebelumnya.
void activityLogsIndex;
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="sidebar-lucide-navy">
        <SidebarHeader class="p-0">
            <SidebarBrandLogo />
        </SidebarHeader>

        <SidebarContent>
            <SidebarNavLucide
                :items="mainNavItems"
                label="Main navigation"
            />
        </SidebarContent>

        <SidebarFooter class="gap-1 p-0">
            <SidebarNavLucide :items="footerNavItems" />
            <div class="px-3 pb-2">
                <NavUser variant="lucide" />
            </div>
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
