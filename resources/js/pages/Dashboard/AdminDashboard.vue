<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ActionsCard from '@/components/dashboard/ActionsCard.vue';
import GridTaskCard from '@/components/dashboard/GridTaskCard.vue';
import HeroCard from '@/components/dashboard/HeroCard.vue';
import RasioStatusTaskCard from '@/components/dashboard/RasioStatusTaskCard.vue';
import TaskDueSoonCard from '@/components/dashboard/TaskDueSoonCard.vue';
import TaskListCard from '@/components/dashboard/TaskListCard.vue';
import TaskOverdueCard from '@/components/dashboard/TaskOverdueCard.vue';
import TaskTrendCard from '@/components/dashboard/TaskTrendCard.vue';
import TeamPerformanceCard from '@/components/dashboard/TeamPerformanceCard.vue';
import { dashboard } from '@/routes';
import { index as tasksIndex } from '@/routes/tasks';
import type { AdminDashboardProps, DashboardPeriod } from '@/types/dashboard';

/** Build /tasks?status=due_soon - filter deadline efektif dari TaskController */
const dueSoonViewAllUrl = computed(() => {
    return tasksIndex({ query: { status: 'due_soon' } }).url;
});

const props = defineProps<AdminDashboardProps>();

// Ambil nama user yang sedang login dari Inertia shared props
const page = usePage();
const authUserName = computed(() => (page.props.auth as any)?.user?.name ?? 'User');

function setDashboardPeriod(period: DashboardPeriod) {
    if (period === props.dashboard_period) {
        return;
    }

    router.visit(dashboard({ query: { period } }).url, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

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
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">
                    Periode Dashboard
                </p>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                    Data ringkasan mengikuti {{ dashboard_period_label }}
                </p>
            </div>

            <div
                class="inline-flex w-full items-center gap-1 rounded-lg border border-slate-300 bg-white p-1 shadow-sm sm:w-auto dark:border-slate-700 dark:bg-slate-900"
                role="group"
                aria-label="Filter periode dashboard"
            >
                <button
                    v-for="option in dashboard_period_options"
                    :key="option.value"
                    type="button"
                    :class="[
                        'min-h-9 rounded-md px-3 text-sm font-semibold transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1B3A6B] dark:focus-visible:outline-[#7AA2F7]',
                        option.value === dashboard_period
                            ? 'bg-[#1B3A6B] text-white shadow-sm dark:bg-[#7AA2F7] dark:text-slate-950'
                            : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800',
                    ]"
                    :aria-pressed="option.value === dashboard_period"
                    @click="setDashboardPeriod(option.value)"
                >
                    {{ option.label }}
                </button>
            </div>
        </div>
        <!-- Bento Grid — kolom diatur via .dashboard-grid di app.css -->
        <div class="dashboard-grid grid gap-3 auto-rows-[120px]">

            <!-- HERO (narrow + tall): col-span-1, row-span-5 — sejajar dengan Due Soon + Actions stack -->
            <div class="col-span-1 md:col-span-1 lg:col-span-1 row-span-5">
                <HeroCard
                    :user-name="authUserName"
                    :pending-count="stats.open_tasks"
                    :overdue-count="overdue_count"
                    :total-tasks="stats.total_tasks"
                    :total-tasks-with-trashed="stats.total_tasks_with_trashed"
                    :trashed-tasks="stats.trashed_tasks"
                />
            </div>

            <!-- Task Due Soon (row 1-2, col 2-3 — lebar 2 kolom sesuai desain) -->
            <div class="col-span-1 md:col-span-2 lg:col-span-2 row-span-2">
                <TaskDueSoonCard
                    :count="due_soon_count"
                    :tasks="due_soon_tasks"
                    :view-all-link="dueSoonViewAllUrl"
                />
            </div>

            <!-- Rasio Status Task donut (row 1-2, col 4 — sejajar di samping Task Due Soon) -->
            <div class="col-span-1 row-span-2">
                <RasioStatusTaskCard :series="chart_donut" />
            </div>

            <!-- Actions (row 3-5, col 2 — sejajar bottom dengan Hero) -->
            <div class="col-span-1 row-span-3">
                <ActionsCard />
            </div>

            <!-- Grid Task Stats 2x2 (row 3-5, col 3-4 span-2 — sejajar bottom dengan Hero) -->
            <div class="col-span-1 md:col-span-2 lg:col-span-2 row-span-3">
                <GridTaskCard
                    :total-tasks="stats.total_tasks"
                    :total-tasks-with-trashed="stats.total_tasks_with_trashed"
                    :trashed-tasks="stats.trashed_tasks"
                    :total-teams="stats.total_teams"
                    :pending-tasks="stats.open_tasks"
                    :total-clients="stats.total_clients"
                    :trends="trends"
                />
            </div>

            <!-- Task Overdue (row 6-7, col 1 — sejajar lebar Hero / kolom pertama 2fr) -->
            <div class="col-span-1 md:col-span-1 lg:col-span-1 row-span-3">
                <TaskOverdueCard
                    :count="overdue_count"
                    :tasks="overdue_tasks"
                    :view-all-link="overdue_tasks.length > 3 ? tasksIndex({ query: { status: 'overdue' } }).url : undefined"
                />
            </div>

            <!-- Tren Task 7 Days (row 6-7, col 2-3-4 — memanjang mengisi sisa ruang di samping Overdue) -->
            <div class="col-span-1 md:col-span-2 lg:col-span-3 row-span-3">
                <TaskTrendCard
                    :categories="chart_area.categories"
                    :data="chart_area.data"
                    :monthly-categories="chart_month.categories"
                    :monthly-data="chart_month.data"
                    :period-label="dashboard_period_label"
                    :show-mode-toggle="false"
                    title="Tren Task"
                />
            </div>

            <!-- Ringkasan Performa Team + 5 Task Terbaru — 50/50 di semua breakpoint -->
            <div class="col-span-1 md:col-span-2 lg:col-span-4 row-span-3 flex flex-col gap-3 md:flex-row">
                <div class="min-w-0 max-w-full md:basis-0 md:flex-1">
                    <TeamPerformanceCard :teams="team_performance" />
                </div>
                <div class="min-w-0 max-w-full md:basis-0 md:flex-1">
                    <TaskListCard variant="recent" :tasks="recent_tasks" />
                </div>
            </div>

        </div>
    </div>
</template>
