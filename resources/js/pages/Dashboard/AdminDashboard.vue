<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { index as tasksIndex } from '@/routes/tasks';
import { Users, Building2, ListTodo, AlertCircle, Clock } from 'lucide-vue-next';
import type { ApexOptions } from 'apexcharts';

// Import Bento Grid components
import BentoGrid from '@/components/dashboard/BentoGrid.vue';
import BentoGridItem from '@/components/dashboard/BentoGridItem.vue';
import HeroCard from '@/components/dashboard/HeroCard.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import ActionsCard from '@/components/dashboard/ActionsCard.vue';
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

// Chart data for hero mini bar chart (last 7 days)
const heroChartData = computed(() => {
    return props.chart_area.data.slice(-7);
});

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

import { computed } from 'vue';
</script>

<template>
    <Head title="Admin Dashboard" />

    <div
        id="main-content"
        class="flex h-full flex-1 flex-col gap-5 overflow-x-auto rounded-xl p-4 md:p-6"
        role="main"
        aria-label="Dashboard Admin"
    >
        <!-- Bento Grid Layout - 4 columns on large screens -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 auto-rows-min">

            <!-- ROW 1 LEFT: Hero Card (spans 2 columns) -->
            <div class="col-span-1 md:col-span-2 lg:col-span-2 row-span-2">
                <HeroCard
                    user-name="Admin PO"
                    :pending-count="stats.open_tasks"
                    :overdue-count="overdue_count"
                    :total-tasks="stats.total_tasks"
                    :chart-data="heroChartData"
                />
            </div>

            <!-- ROW 1 RIGHT: Task Due Soon (top right) -->
            <div class="col-span-1">
                <DeadlineAlertCard
                    type="due_soon"
                    :count="due_soon_count"
                    :tasks="due_soon_tasks"
                    :view-all-link="due_soon_tasks.length > 10 ? tasksIndex({ query: { status: 'due_soon' } }).url : undefined"
                />
            </div>

            <!-- ROW 1 RIGHT: Rasio Status Task - Donut Chart (top right) -->
            <div class="col-span-1">
                <ChartCard
                    title="Rasio Status Task"
                    chartType="donut"
                    :options="donutOptions"
                    :series="donutSeries"
                    :height="200"
                />
            </div>

            <!-- ROW 2 RIGHT: Actions Card (middle right) -->
            <div class="col-span-1">
                <ActionsCard />
            </div>

            <!-- ROW 2 RIGHT: Stats Grid in Green Container (bottom right - spans full width of right side) -->
            <div class="col-span-1 md:col-span-2 lg:col-span-2">
                <div
                    class="relative overflow-hidden rounded-xl border-2 border-tm-green bg-tm-green-pale/60 p-3 shadow-[3px_3px_0_0_rgba(43,174,110,0.15)] dark:bg-tm-green/10 dark:border-tm-green/50"
                >
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <StatCard
                            label="Total Tasks"
                            :value="stats.total_tasks"
                            :icon="ListTodo"
                            colorTheme="neutral"
                            trend-direction="up"
                            :trend-value="2"
                            compact
                        />
                        <StatCard
                            label="Total Tim"
                            :value="stats.total_teams"
                            :icon="Users"
                            colorTheme="navy"
                            compact
                        />
                        <StatCard
                            label="Menunggu Dikerjakan"
                            :value="stats.open_tasks"
                            :icon="Clock"
                            colorTheme="amber"
                            trend-direction="down"
                            :trend-value="2"
                            compact
                        />
                        <StatCard
                            label="Total Faskes"
                            :value="stats.total_clients"
                            :icon="Building2"
                            colorTheme="green"
                            trend-direction="up"
                            :trend-value="2"
                            compact
                        />
                    </div>
                </div>
            </div>

            <!-- ROW 3 LEFT: Task Overdue -->
            <div class="col-span-1">
                <DeadlineAlertCard
                    type="overdue"
                    :count="overdue_count"
                    :tasks="overdue_tasks"
                    :view-all-link="overdue_tasks.length > 10 ? tasksIndex({ query: { status: 'overdue' } }).url : undefined"
                />
            </div>

            <!-- ROW 3 RIGHT: Tren Task 7 Days (spans 3 columns) -->
            <div class="col-span-1 md:col-span-1 lg:col-span-3">
                <ChartCard
                    title="Tren Task 7 Days"
                    subtitle="Perkembangan pembuatan task per minggu"
                    chartType="area"
                    :options="areaOptions"
                    :series="areaSeries"
                    :height="260"
                />
            </div>

            <!-- ROW 4 LEFT: Ringkasan Performa Team -->
            <div class="col-span-1 md:col-span-1 lg:col-span-2">
                <TeamPerformanceCard :teams="team_performance" />
            </div>

            <!-- ROW 4 RIGHT: 5 Task Terbaru -->
            <div class="col-span-1 md:col-span-1 lg:col-span-2">
                <TaskListCard variant="recent" :tasks="recent_tasks" />
            </div>

        </div>

    </div>
</template>
