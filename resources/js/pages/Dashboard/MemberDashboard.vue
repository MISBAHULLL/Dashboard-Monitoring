<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { AlertCircle, ArrowRight, CalendarDays, Clock, ListTodo } from 'lucide-vue-next';

// Import Bento Grid components
import BentoGrid from '@/components/dashboard/BentoGrid.vue';
import BentoGridItem from '@/components/dashboard/BentoGridItem.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import TaskListCard from '@/components/dashboard/TaskListCard.vue';
import { dashboard } from '@/routes';
import { index as tasksIndex, show as showTask } from '@/routes/tasks';
import type { MemberDashboardProps } from '@/types/dashboard';

// 1. Menerima data dari Controller (Sama dengan admin, tapi isinya berbeda sedikit)
const props = defineProps<MemberDashboardProps>();

const overdueTasks = computed(() => props.overdue_tasks ?? []);
const dueSoonTasks = computed(() => props.due_soon_tasks ?? []);
const overdueCount = computed(() => props.stats.overdue_tasks ?? overdueTasks.value.length);
const dueSoonCount = computed(() => props.stats.due_soon_tasks ?? dueSoonTasks.value.length);

const overdueUrl = tasksIndex({ query: { status: 'overdue' } }).url;
const dueSoonUrl = tasksIndex({ query: { status: 'due_soon' } }).url;

const deadlineOf = (task: { sla_due_date?: string; release_date?: string }) => task.sla_due_date ?? task.release_date;
const formatDate = (date?: string) => {
    if (!date) return 'Belum dijadwalkan';

    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

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

    <div 
        id="main-content"
        class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-8"
        role="main"
        aria-label="Dashboard Member"
    >
        
        <!-- Header -->
        <header>
            <h1 class="text-3xl font-bold tracking-tight text-primary">Selamat Datang!</h1>
            <p class="text-muted-foreground mt-1">Berikut adalah ringkasan task yang ditugaskan kepada Anda.</p>
        </header>

        <!-- Bento Grid Layout -->
        <BentoGrid :columns="{ default: 1, md: 3, lg: 3 }">
            
            <!-- Row 1: Stats (3 cards) -->
            <BentoGridItem :span="{ default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' }">
                <StatCard 
                    label="Perlu Dikerjakan" 
                    :value="stats.open_tasks" 
                    :icon="AlertCircle" 
                    colorTheme="amber" 
                />
            </BentoGridItem>

            <BentoGridItem :span="{ default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' }">
                <StatCard 
                    label="Sedang Dikerjakan" 
                    :value="stats.in_progress_tasks" 
                    :icon="Clock" 
                    colorTheme="navy" 
                />
            </BentoGridItem>

            <BentoGridItem :span="{ default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' }">
                <StatCard 
                    label="Tugas Selesai" 
                    :value="stats.completed_tasks" 
                    :icon="ListTodo" 
                    colorTheme="green" 
                />
            </BentoGridItem>

            <!-- Row 2: Work focus + Task List (full width) -->
            <BentoGridItem :span="{ default: 'col-span-full' }">
                <div class="flex flex-col gap-4">
                    <section class="grid grid-cols-1 gap-4 lg:grid-cols-2" aria-label="Fokus kerja member">
                        <article class="rounded-xl border-[1.5px] border-red-300 bg-red-50/80 p-4 shadow-[2px_2px_0_0_rgba(239,68,68,0.12)] dark:border-red-300/35 dark:bg-red-400/10">
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-white text-red-600 dark:border-red-300/35 dark:bg-slate-950/30 dark:text-red-300">
                                        <AlertCircle class="h-4 w-4" />
                                    </div>
                                    <div>
                                        <h2 class="text-base font-bold text-red-700 dark:text-red-200">Task Overdue</h2>
                                        <p class="text-xs text-red-700/70 dark:text-red-200/70">{{ overdueCount }} task melewati deadline</p>
                                    </div>
                                </div>

                                <Link :href="overdueUrl" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-bold text-red-700 transition hover:bg-red-100 dark:text-red-200 dark:hover:bg-red-400/10">
                                    Lihat
                                    <ArrowRight class="h-3.5 w-3.5" />
                                </Link>
                            </div>

                            <div v-if="overdueTasks.length > 0" class="space-y-2">
                                <Link
                                    v-for="task in overdueTasks.slice(0, 3)"
                                    :key="task.id"
                                    :href="showTask(task.id).url"
                                    class="flex items-center justify-between gap-3 rounded-lg border border-red-200 bg-white px-3 py-2 text-sm transition hover:border-red-300 hover:bg-red-50 dark:border-red-300/20 dark:bg-slate-950/25 dark:hover:bg-red-400/10"
                                >
                                    <span class="min-w-0">
                                        <span class="block truncate font-semibold text-slate-900 dark:text-slate-100">{{ task.title }}</span>
                                        <span class="block truncate text-xs text-slate-500 dark:text-slate-400">{{ task.client?.name || '-' }}</span>
                                    </span>
                                    <span class="shrink-0 text-xs font-bold text-red-600 dark:text-red-300">{{ formatDate(deadlineOf(task)) }}</span>
                                </Link>
                            </div>
                            <p v-else class="rounded-lg border border-red-100 bg-white/70 px-3 py-3 text-sm font-medium text-red-700/70 dark:border-red-300/15 dark:bg-slate-950/20 dark:text-red-200/70">
                                Tidak ada task overdue. Aman untuk sekarang.
                            </p>
                        </article>

                        <article class="rounded-xl border-[1.5px] border-amber-300 bg-amber-50/80 p-4 shadow-[2px_2px_0_0_rgba(245,158,11,0.12)] dark:border-amber-300/35 dark:bg-amber-400/10">
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg border border-amber-200 bg-white text-amber-600 dark:border-amber-300/35 dark:bg-slate-950/30 dark:text-amber-300">
                                        <CalendarDays class="h-4 w-4" />
                                    </div>
                                    <div>
                                        <h2 class="text-base font-bold text-amber-800 dark:text-amber-100">SLA Warning</h2>
                                        <p class="text-xs text-amber-800/70 dark:text-amber-100/70">{{ dueSoonCount }} task mendekati deadline</p>
                                    </div>
                                </div>

                                <Link :href="dueSoonUrl" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-bold text-amber-800 transition hover:bg-amber-100 dark:text-amber-100 dark:hover:bg-amber-400/10">
                                    Lihat
                                    <ArrowRight class="h-3.5 w-3.5" />
                                </Link>
                            </div>

                            <div v-if="dueSoonTasks.length > 0" class="space-y-2">
                                <Link
                                    v-for="task in dueSoonTasks.slice(0, 3)"
                                    :key="task.id"
                                    :href="showTask(task.id).url"
                                    class="flex items-center justify-between gap-3 rounded-lg border border-amber-200 bg-white px-3 py-2 text-sm transition hover:border-amber-300 hover:bg-amber-50 dark:border-amber-300/20 dark:bg-slate-950/25 dark:hover:bg-amber-400/10"
                                >
                                    <span class="min-w-0">
                                        <span class="block truncate font-semibold text-slate-900 dark:text-slate-100">{{ task.title }}</span>
                                        <span class="block truncate text-xs text-slate-500 dark:text-slate-400">{{ task.client?.name || '-' }}</span>
                                    </span>
                                    <span class="shrink-0 text-xs font-bold text-amber-700 dark:text-amber-200">{{ formatDate(deadlineOf(task)) }}</span>
                                </Link>
                            </div>
                            <p v-else class="rounded-lg border border-amber-100 bg-white/70 px-3 py-3 text-sm font-medium text-amber-800/70 dark:border-amber-300/15 dark:bg-slate-950/20 dark:text-amber-100/70">
                                Belum ada task yang masuk warning SLA.
                            </p>
                        </article>
                    </section>

                    <TaskListCard variant="assigned" :tasks="my_tasks" />
                </div>
            </BentoGridItem>

        </BentoGrid>

    </div>
</template>
