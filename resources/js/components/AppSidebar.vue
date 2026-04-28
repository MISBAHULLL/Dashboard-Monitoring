<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, ListTodo, Users, Building2, UsersRound, Settings, Columns3, Activity } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';

// Import route generator otomatis dari Laravel Wayfinder
import { dashboard } from '@/routes';
import { index as activityLogsIndex } from '@/actions/App/Http/Controllers/ActivityLogController';
import { index as tasksIndex } from '@/actions/App/Http/Controllers/TaskController';
import { index as usersIndex } from '@/actions/App/Http/Controllers/UserController';
import { index as teamsIndex } from '@/actions/App/Http/Controllers/TeamController';
import { index as clientsIndex } from '@/actions/App/Http/Controllers/ClientController';

import type { NavItem } from '@/types';
import { computed } from 'vue';

// Mengambil data user yang login dari middleware HandleInertiaRequests yang kita buat
const page = usePage();
const user = computed(() => page.props.auth.user);

// Menu utama (Bisa diakses semua user)
const mainNavItems = computed<NavItem[]>(() => {
    const items = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Tabel Task',
            href: tasksIndex.url(),
            icon: ListTodo,
        },
        {
            title: 'Kanban Board',
            href: '/tasks-kanban',
            icon: Columns3,
        },
        {
            title: 'Audit Trail',
            href: '/activity-logs',
            icon: Activity,
        },
    ];

    // Menu master data HANYA ditambahkan jika user adalah admin
    if (user.value?.role === 'admin') {
        items.push({
            title: 'Faskes / Client',
            href: clientsIndex.url(),
            icon: Building2,
        });
        items.push({
            title: 'Master Team',
            href: teamsIndex.url(),
            icon: UsersRound,
        });
        items.push({
            title: 'Master User',
            href: usersIndex.url(),
            icon: Users,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Pengaturan Sistem',
        href: '#', // Nanti kita arahkan ke halaman pengaturan SLA dsb
        icon: Settings,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <!-- Daftar Menu -->
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            
            <!-- Profil User & Tombol Logout -->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
