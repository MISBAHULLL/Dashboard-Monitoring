<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { Users, Building2, ListTodo, AlertCircle, TriangleAlert, Clock3 } from 'lucide-vue-next';
import VueApexCharts from "vue3-apexcharts";
import type { ApexOptions } from 'apexcharts';

// 1. Menerima data dari Controller
const props = defineProps<{
    stats: {
        total_tasks: number;
        open_tasks: number;
        in_progress_tasks: number;
        completed_tasks: number;
        total_clients: number;
        total_teams: number;
    };
    chart_donut: number[];
    chart_area: {
        categories: string[];
        data: number[];
    };
    overdue_count: number;
    due_soon_count: number;
    overdue_tasks: Array<any>;
    due_soon_tasks: Array<any>;
    team_performance: Array<{
        id: number;
        name: string;
        total_tasks: number;
        completed_tasks: number;
        open_tasks: number;
        in_progress_tasks: number;
        revision_tasks: number;
        overdue_tasks: number;
        completion_rate: number;
    }>;
    recent_tasks: any[];
}>();

// Konfigurasi Donut Chart
const donutOptions: ApexOptions = {
    chart: { type: 'donut', fontFamily: 'inherit' },
    labels: ['Open', 'In Progress', 'Revisi', 'Completed'],
    colors: ['#f59e0b', '#3b82f6', '#ef4444', '#10b981'],
    plotOptions: { pie: { donut: { size: '70%' } } },
    dataLabels: { enabled: false },
    legend: { position: 'bottom' }
};
const donutSeries = props.chart_donut;

// Konfigurasi Area Chart
const areaOptions: ApexOptions = {
    chart: { type: 'area', fontFamily: 'inherit', toolbar: { show: false } },
    colors: ['#0ea5e9'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: { categories: props.chart_area.categories },
    fill: {
        type: 'gradient',
        gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.1, stops: [0, 90, 100] }
    }
};
const areaSeries = [{
    name: 'Task Dibuat',
    data: props.chart_area.data
}];

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

        <!-- 4. Informasi Prioritas Deadline -->
        <div class="grid gap-4 md:grid-cols-2">
            <div class="relative overflow-hidden rounded-xl border border-red-200 bg-red-50/60 p-6 shadow-sm dark:border-red-900/40 dark:bg-red-950/20">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-700 dark:text-red-300">Task Overdue</p>
                        <p class="mt-2 text-3xl font-bold text-red-700 dark:text-red-300">{{ overdue_count }}</p>
                    </div>
                    <div class="rounded-full bg-red-100 p-3 dark:bg-red-900/40">
                        <TriangleAlert class="h-6 w-6 text-red-600 dark:text-red-300" />
                    </div>
                </div>
                <div class="space-y-2">
                    <div v-for="task in overdue_tasks" :key="task.id" class="rounded-lg border border-red-200/80 bg-white p-3 dark:border-red-900/30 dark:bg-red-950/30">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ task.title }}</p>
                        <div class="mt-1 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                            <span>{{ task.client?.name || '-' }}</span>
                            <span class="font-semibold text-red-600 dark:text-red-300">{{ task.release_date ? new Date(task.release_date).toLocaleDateString('id-ID') : '-' }}</span>
                        </div>
                    </div>
                    <p v-if="overdue_tasks.length === 0" class="text-sm text-slate-500 dark:text-slate-400">
                        Tidak ada task overdue.
                    </p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-xl border border-amber-200 bg-amber-50/60 p-6 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-amber-700 dark:text-amber-300">Task Due Soon (H-7)</p>
                        <p class="mt-2 text-3xl font-bold text-amber-700 dark:text-amber-300">{{ due_soon_count }}</p>
                    </div>
                    <div class="rounded-full bg-amber-100 p-3 dark:bg-amber-900/40">
                        <Clock3 class="h-6 w-6 text-amber-600 dark:text-amber-300" />
                    </div>
                </div>
                <div class="space-y-2">
                    <div v-for="task in due_soon_tasks" :key="task.id" class="rounded-lg border border-amber-200/80 bg-white p-3 dark:border-amber-900/30 dark:bg-amber-950/30">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ task.title }}</p>
                        <div class="mt-1 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                            <span>{{ task.client?.name || '-' }}</span>
                            <span class="font-semibold text-amber-600 dark:text-amber-300">{{ task.release_date ? new Date(task.release_date).toLocaleDateString('id-ID') : '-' }}</span>
                        </div>
                    </div>
                    <p v-if="due_soon_tasks.length === 0" class="text-sm text-slate-500 dark:text-slate-400">
                        Tidak ada task due soon.
                    </p>
                </div>
            </div>
        </div>

        <!-- 5. Area Charts (Visualisasi Data) -->
        <div class="grid gap-4 md:grid-cols-3">
            <!-- Area Chart (Trend) -->
            <div class="col-span-1 md:col-span-2 relative overflow-hidden rounded-xl border border-sidebar-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
                <h2 class="text-lg font-semibold text-primary mb-4">Tren Pembuatan Task (7 Hari Terakhir)</h2>
                <div class="w-full">
                    <VueApexCharts type="area" height="300" :options="areaOptions" :series="areaSeries" />
                </div>
            </div>

            <!-- Donut Chart (Status) -->
            <div class="col-span-1 relative overflow-hidden rounded-xl border border-sidebar-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
                <h2 class="text-lg font-semibold text-primary mb-4">Rasio Status Task</h2>
                <div class="w-full flex justify-center">
                    <VueApexCharts type="donut" height="320" :options="donutOptions" :series="donutSeries" />
                </div>
            </div>
        </div>

        <!-- 6. Ringkasan Performa Tim -->
        <div class="relative rounded-xl border border-sidebar-border bg-card shadow-sm">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-primary">Ringkasan Performa Tim Product</h2>
                <p class="text-sm text-muted-foreground mb-4">Progress task per tim berdasarkan total, selesai, dan overdue.</p>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/50 text-muted-foreground border-b border-border">
                            <tr>
                                <th class="py-3 px-4 font-medium">Tim</th>
                                <th class="py-3 px-4 font-medium text-center">Total</th>
                                <th class="py-3 px-4 font-medium text-center">Selesai</th>
                                <th class="py-3 px-4 font-medium text-center">Overdue</th>
                                <th class="py-3 px-4 font-medium text-center">Completion Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="team in team_performance" :key="team.id" class="border-b border-border last:border-0 hover:bg-muted/30">
                                <td class="py-3 px-4 font-medium">{{ team.name }}</td>
                                <td class="py-3 px-4 text-center">{{ team.total_tasks }}</td>
                                <td class="py-3 px-4 text-center text-emerald-600 dark:text-emerald-400">{{ team.completed_tasks }}</td>
                                <td class="py-3 px-4 text-center text-red-600 dark:text-red-400">{{ team.overdue_tasks }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                                        {{ team.completion_rate }}%
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="team_performance.length === 0">
                                <td colspan="5" class="py-8 text-center text-muted-foreground">
                                    Belum ada data performa tim.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 7. Tabel 5 Task Terakhir -->
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
