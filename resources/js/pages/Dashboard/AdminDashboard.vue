<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
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
import type { AdminDashboardProps } from '@/types/dashboard';

/** Build /tasks?status=due_soon - filter deadline efektif dari TaskController */
const dueSoonViewAllUrl = computed(() => {
    return tasksIndex({ query: { status: 'due_soon' } }).url;
});

defineProps<AdminDashboardProps>();

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

            <!-- HERO (narrow + tall): col-span-1, row-span-5 — sejajar dengan Due Soon + Actions stack -->
            <div class="col-span-1 md:col-span-1 lg:col-span-1 row-span-5">
                <HeroCard
                    :user-name="authUserName"
                    :pending-count="stats.open_tasks"
                    :overdue-count="overdue_count"
                    :total-tasks="stats.total_tasks"
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
                />
            </div>

            <!-- Ringkasan Performa Team + 5 Task Terbaru — 50/50 di semua breakpoint -->
            <div class="col-span-1 md:col-span-2 lg:col-span-4 row-span-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                <TeamPerformanceCard :teams="team_performance" />
                <TaskListCard variant="recent" :tasks="recent_tasks" />
            </div>

        </div>
    </div>
</template>
