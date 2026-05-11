<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { index as tasksIndex } from '@/routes/tasks';
import { Users, Building2, ListTodo, Clock } from 'lucide-vue-next';
import type { ApexOptions } from 'apexcharts';

import HeroCard from '@/components/dashboard/HeroCard.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import ActionsCard from '@/components/dashboard/ActionsCard.vue';
import DeadlineAlertCard from '@/components/dashboard/DeadlineAlertCard.vue';
import ChartCard from '@/components/dashboard/ChartCard.vue';
import TeamPerformanceCard from '@/components/dashboard/TeamPerformanceCard.vue';
import TaskListCard from '@/components/dashboard/TaskListCard.vue';

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

const donutOptions: ApexOptions = {
    chart: { type: 'donut', fontFamily: 'inherit' },
    labels: ['Open', 'In Progress', 'Revisi', 'Completed'],
    colors: ['#f59e0b', '#3b82f6', '#ef4444', '#10b981'],
    plotOptions: { pie: { donut: { size: '70%' } } },
    dataLabels: { enabled: false },
    legend: { position: 'bottom', fontSize: '11px' }
};
const donutSeries = props.chart_donut;

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
const areaSeries = [{ name: 'Task Dibuat', data: props.chart_area.data }];

const heroChartData = computed(() => props.chart_area.data.slice(-6));

// Ambil nama user yang sedang login dari Inertia shared props
const page = usePage();
const authUserName = computed(() => (page.props.auth as any)?.user?.name ?? 'User');

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <div
        id="main-content"
        class="flex h-full flex-1 flex-col gap-3 overflow-x-auto rounded-xl p-3 md:p-4"
        role="main"
        aria-label="Dashboard Admin"
    >
        <!-- Bento Grid — kolom diatur via .dashboard-grid di app.css -->
        <div class="dashboard-grid grid gap-3 auto-rows-[120px]">

            <!-- HERO (narrow + tall): col-span-1, row-span-5 = 600px tall, 25% width -->
            <div class="col-span-1 md:col-span-1 lg:col-span-1 row-span-5">
                <HeroCard
                    :user-name="authUserName"
                    :pending-count="stats.open_tasks"
                    :overdue-count="overdue_count"
                    :total-tasks="stats.total_tasks"
                    :chart-data="heroChartData"
                />
            </div>

            <!-- Task Due Soon (row 1-2, col 2) -->
            <div class="col-span-1 row-span-2">
                <DeadlineAlertCard
                    type="due_soon"
                    :count="due_soon_count"
                    :tasks="due_soon_tasks"
                    :view-all-link="due_soon_tasks.length > 10 ? tasksIndex({ query: { status: 'due_soon' } }).url : undefined"
                />
            </div>

            <!-- Donut (row 1-2, col 3) -->
            <div class="col-span-1 row-span-2">
                <ChartCard
                    title="Rasio Status Task"
                    chartType="donut"
                    :options="donutOptions"
                    :series="donutSeries"
                    :height="160"
                />
            </div>

            <!-- Actions (row 1-2, col 4) -->
            <div class="col-span-1 row-span-2">
                <ActionsCard />
            </div>

            <!-- Stats 2x2 green container (row 3-4, col 2-4 span-3) -->
            <div class="col-span-1 md:col-span-2 lg:col-span-3 row-span-2">
                <div
                    class="relative h-full overflow-hidden rounded-xl border-2 border-tm-green bg-tm-green-pale/60 p-2 shadow-[2px_2px_0_0_rgba(43,174,110,0.15)] dark:bg-tm-green/10 dark:border-tm-green/50"
                >
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 h-full">
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
                            label="Menunggu"
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

            <!-- Task Overdue (row 5-6, col 1-2) -->
            <div class="col-span-1 md:col-span-2 lg:col-span-2 row-span-2">
                <DeadlineAlertCard
                    type="overdue"
                    :count="overdue_count"
                    :tasks="overdue_tasks"
                    :view-all-link="overdue_tasks.length > 10 ? tasksIndex({ query: { status: 'overdue' } }).url : undefined"
                />
            </div>

            <!-- Tren Task 7 Days (row 5-6, col 3-4) -->
            <div class="col-span-1 md:col-span-2 lg:col-span-2 row-span-2">
                <ChartCard
                    title="Tren Task 7 Days"
                    subtitle="Perkembangan pembuatan task"
                    chartType="area"
                    :options="areaOptions"
                    :series="areaSeries"
                    :height="200"
                />
            </div>

            <!-- Ringkasan Performa Team (row 7-9, col 1-2) -->
            <div class="col-span-1 md:col-span-1 lg:col-span-2 row-span-3">
                <TeamPerformanceCard :teams="team_performance" />
            </div>

            <!-- 5 Task Terbaru (row 7-9, col 3-4) -->
            <div class="col-span-1 md:col-span-1 lg:col-span-2 row-span-3">
                <TaskListCard variant="recent" :tasks="recent_tasks" />
            </div>

        </div>
    </div>
</template>
