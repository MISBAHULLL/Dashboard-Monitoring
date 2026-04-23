<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { ListTodo, AlertCircle, Clock } from 'lucide-vue-next';

// 1. Menerima data dari Controller (Sama dengan admin, tapi isinya berbeda sedikit)
defineProps<{
    stats: {
        total_tasks: number;
        open_tasks: number;
        in_progress_tasks: number;
        completed_tasks: number;
    };
    my_tasks: any[];
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
    <Head title="Member Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-8">
        
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-primary">Selamat Datang!</h1>
            <p class="text-muted-foreground mt-1">Berikut adalah ringkasan task yang ditugaskan kepada Anda.</p>
        </div>

        <!-- 3. Kartu Statistik Utama -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <!-- Open Tasks -->
            <div class="relative overflow-hidden rounded-xl border border-sidebar-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Perlu Dikerjakan</p>
                        <p class="mt-2 text-3xl font-bold text-amber-600 dark:text-amber-500">{{ stats.open_tasks }}</p>
                    </div>
                    <div class="rounded-full bg-amber-100 p-3 dark:bg-amber-900/30">
                        <AlertCircle class="h-6 w-6 text-amber-600 dark:text-amber-500" />
                    </div>
                </div>
            </div>

            <!-- In Progress -->
            <div class="relative overflow-hidden rounded-xl border border-sidebar-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Sedang Dikerjakan</p>
                        <p class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-500">{{ stats.in_progress_tasks }}</p>
                    </div>
                    <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/30">
                        <Clock class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            <!-- Completed Tasks -->
            <div class="relative overflow-hidden rounded-xl border border-sidebar-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Tugas Selesai</p>
                        <p class="mt-2 text-3xl font-bold text-emerald-600 dark:text-emerald-500">{{ stats.completed_tasks }}</p>
                    </div>
                    <div class="rounded-full bg-emerald-100 p-3 dark:bg-emerald-900/30">
                        <ListTodo class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Tabel Task Milik Sendiri -->
        <div class="relative flex-1 rounded-xl border border-sidebar-border bg-card shadow-sm">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-primary">Tugas Anda yang Belum Selesai</h2>
                <p class="text-sm text-muted-foreground mb-4">Daftar task yang di-assign ke Anda dan membutuhkan perhatian.</p>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/50 text-muted-foreground border-b border-border">
                            <tr>
                                <th class="py-3 px-4 font-medium">Faskes / Client</th>
                                <th class="py-3 px-4 font-medium">Judul Task</th>
                                <th class="py-3 px-4 font-medium">Prioritas</th>
                                <th class="py-3 px-4 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="task in my_tasks" :key="task.id" class="border-b border-border last:border-0 hover:bg-muted/30">
                                <td class="py-3 px-4">{{ task.client?.name }}</td>
                                <td class="py-3 px-4 font-medium">{{ task.title }}</td>
                                <td class="py-3 px-4">
                                    <!-- Lencana Warna (Badge) untuk tingkat Prioritas -->
                                    <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-semibold capitalize"
                                        :class="{
                                            'border-red-200 text-red-600 dark:border-red-900/50 dark:text-red-400': task.priority === 'urgent',
                                            'border-amber-200 text-amber-600 dark:border-amber-900/50 dark:text-amber-400': task.priority === 'high',
                                            'border-blue-200 text-blue-600 dark:border-blue-900/50 dark:text-blue-400': task.priority === 'medium',
                                            'border-slate-200 text-slate-600 dark:border-slate-700 dark:text-slate-400': task.priority === 'low'
                                        }">
                                        {{ task.priority }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                        :class="{
                                            'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400': task.status === 'open',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400': task.status === 'in_progress',
                                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': task.status === 'revision'
                                        }">
                                        {{ task.status.replace('_', ' ') }}
                                    </span>
                                </td>
                            </tr>
                            
                            <tr v-if="my_tasks.length === 0">
                                <td colspan="4" class="py-8 text-center text-muted-foreground">
                                    Hebat! Anda tidak memiliki task yang tertunda. 🎉
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</template>
