<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { index as tasksIndex } from '@/routes/tasks';
import { Users, Building2, ListTodo, AlertCircle } from 'lucide-vue-next';
import type { ApexOptions } from 'apexcharts';

// Import Bento Grid components
import BentoGrid from '@/components/dashboard/BentoGrid.vue';
import BentoGridItem from '@/components/dashboard/BentoGridItem.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import DeadlineAlertCard from '@/components/dashboard/DeadlineAlertCard.vue';
import ChartCard from '@/components/dashboard/ChartCard.vue';
import TeamPerformanceCard from '@/components/dashboard/TeamPerformanceCard.vue';
import TaskListCard from '@/components/dashboard/TaskListCard.vue';

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
    overdue_tasks: Array<{
        id: number;
        title: string;
        client?: { name: string };
        release_date?: string;
    }>;
    due_soon_tasks: Array<{
        id: number;
        title: string;
        client?: { name: string };
        release_date?: string;
    }>;
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
    recent_tasks: Array<{
        id: number;
        title: string;
        modul?: string;
        status: 'open' | 'in_progress' | 'revision' | 'completed';
        client?: { name: string };
        created_at: string;
    }>;
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

        <!-- Bento Grid Layout -->
        <BentoGrid :columns="{ default: 1, md: 2, lg: 4 }">
            
            <!-- Row 1: Stats (4 small cards) -->
            <BentoGridItem :span="{ default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' }">
                <StatCard 
                    label="Total Tasks" 
                    :value="stats.total_tasks" 
                    :icon="ListTodo" 
                    colorTheme="neutral" 
                />
            </BentoGridItem>

            <BentoGridItem :span="{ default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' }">
                <StatCard 
                    label="Menunggu Dikerjakan" 
                    :value="stats.open_tasks" 
                    :icon="AlertCircle" 
                    colorTheme="amber" 
                />
            </BentoGridItem>

            <BentoGridItem :span="{ default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' }">
                <StatCard 
                    label="Total Faskes" 
                    :value="stats.total_clients" 
                    :icon="Building2" 
                    colorTheme="green" 
                />
            </BentoGridItem>

            <BentoGridItem :span="{ default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' }">
                <StatCard 
                    label="Total Tim" 
                    :value="stats.total_teams" 
                    :icon="Users" 
                    colorTheme="navy" 
                />
            </BentoGridItem>

            <!-- Row 2: Deadline Alerts (2 medium cards) -->
            <BentoGridItem :span="{ default: 'col-span-1', md: 'col-span-1', lg: 'col-span-2' }">
                <DeadlineAlertCard 
                    type="overdue" 
                    :count="overdue_count" 
                    :tasks="overdue_tasks"
                    :view-all-link="overdue_tasks.length > 10 ? tasksIndex({ query: { status: 'overdue' } }).url : undefined"
                />
            </BentoGridItem>

            <BentoGridItem :span="{ default: 'col-span-1', md: 'col-span-1', lg: 'col-span-2' }">
                <DeadlineAlertCard 
                    type="due_soon" 
                    :count="due_soon_count" 
                    :tasks="due_soon_tasks"
                    :view-all-link="due_soon_tasks.length > 10 ? tasksIndex({ query: { status: 'due_soon' } }).url : undefined"
                />
            </BentoGridItem>

            <!-- Row 3: Charts (1 large + 1 small) -->
            <BentoGridItem :span="{ default: 'col-span-1', md: 'col-span-2', lg: 'col-span-3' }">
                <ChartCard 
                    title="Tren Pembuatan Task (7 Hari Terakhir)" 
                    chartType="area" 
                    :options="areaOptions" 
                    :series="areaSeries"
                    :height="300"
                />
            </BentoGridItem>

            <BentoGridItem :span="{ default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' }">
                <ChartCard 
                    title="Rasio Status Task" 
                    chartType="donut" 
                    :options="donutOptions" 
                    :series="donutSeries"
                    :height="320"
                />
            </BentoGridItem>

            <!-- Row 4: Team Performance (full width) -->
            <BentoGridItem :span="{ default: 'col-span-full' }">
                <TeamPerformanceCard :teams="team_performance" />
            </BentoGridItem>

            <!-- Row 5: Recent Tasks (full width) -->
            <BentoGridItem :span="{ default: 'col-span-full' }">
                <TaskListCard variant="recent" :tasks="recent_tasks" />
            </BentoGridItem>

        </BentoGrid>

    </div>
</template>
