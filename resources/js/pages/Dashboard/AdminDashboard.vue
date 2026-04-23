<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { Users, Building2, ListTodo, AlertCircle } from 'lucide-vue-next';

// 1. Menerima data dari Controller
defineProps<{
    stats: {
        total_tasks: number;
        open_tasks: number;
        in_progress_tasks: number;
        completed_tasks: number;
        total_clients: number;
        total_teams: number;
    };
    recent_tasks: any[];
}>();

// 2. Mengatur Breadcrumbs (Navigasi Header)
defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-8">
        
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-primary">Dashboard Admin</h1>
            <p class="text-muted-foreground mt-1">Pantau seluruh aktivitas task dan performa tim di satu tempat.</p>
        </div>

        <!-- 3. Kartu Statistik Utama (Menggunakan Grid Tailwind) -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <!-- Total Tasks -->
            <div class="relative overflow-hidden rounded-xl border border-sidebar-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Total Tasks</p>
                        <p class="mt-2 text-3xl font-bold">{{ stats.total_tasks }}</p>
                    </div>
                    <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/30">
                        <ListTodo class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            <!-- Open Tasks -->
            <div class="relative overflow-hidden rounded-xl border border-sidebar-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Menunggu Dikerjakan</p>
                        <p class="mt-2 text-3xl font-bold text-amber-600 dark:text-amber-500">{{ stats.open_tasks }}</p>
                    </div>
                    <div class="rounded-full bg-amber-100 p-3 dark:bg-amber-900/30">
                        <AlertCircle class="h-6 w-6 text-amber-600 dark:text-amber-500" />
                    </div>
                </div>
            </div>

            <!-- Total Clients -->
            <div class="relative overflow-hidden rounded-xl border border-sidebar-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Total Faskes</p>
                        <p class="mt-2 text-3xl font-bold">{{ stats.total_clients }}</p>
                    </div>
                    <div class="rounded-full bg-emerald-100 p-3 dark:bg-emerald-900/30">
                        <Building2 class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                    </div>
                </div>
            </div>

            <!-- Total Teams -->
            <div class="relative overflow-hidden rounded-xl border border-sidebar-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Total Tim</p>
                        <p class="mt-2 text-3xl font-bold">{{ stats.total_teams }}</p>
                    </div>
                    <div class="rounded-full bg-indigo-100 p-3 dark:bg-indigo-900/30">
                        <Users class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Tabel 5 Task Terakhir -->
        <div class="relative flex-1 rounded-xl border border-sidebar-border bg-card shadow-sm">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-primary">5 Task Terbaru</h2>
                <p class="text-sm text-muted-foreground mb-4">Task yang baru saja dibuat ke dalam sistem.</p>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/50 text-muted-foreground border-b border-border">
                            <tr>
                                <th class="py-3 px-4 font-medium">Faskes / Client</th>
                                <th class="py-3 px-4 font-medium">Judul Task</th>
                                <th class="py-3 px-4 font-medium">Modul</th>
                                <th class="py-3 px-4 font-medium">Status</th>
                                <th class="py-3 px-4 font-medium">Tanggal Dibuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Looping data dari Controller -->
                            <tr v-for="task in recent_tasks" :key="task.id" class="border-b border-border last:border-0 hover:bg-muted/30">
                                <td class="py-3 px-4">{{ task.client?.name }}</td>
                                <td class="py-3 px-4 font-medium">{{ task.title }}</td>
                                <td class="py-3 px-4">{{ task.modul || '-' }}</td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                        :class="{
                                            'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400': task.status === 'open',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400': task.status === 'in_progress',
                                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': task.status === 'revision',
                                            'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400': task.status === 'completed'
                                        }">
                                        {{ task.status.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">{{ new Date(task.created_at).toLocaleDateString('id-ID') }}</td>
                            </tr>
                            
                            <!-- State jika data kosong -->
                            <tr v-if="recent_tasks.length === 0">
                                <td colspan="5" class="py-8 text-center text-muted-foreground">
                                    Belum ada task yang dibuat.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</template>
