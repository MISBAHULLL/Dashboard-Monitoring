<script setup lang="ts">
import { computed } from 'vue';
import { Clock, ArrowRight } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { show as showTask } from '@/routes/tasks';
import type { DeadlineTask } from '@/types/dashboard';

/**
 * TaskDueSoonCard Component
 *
 * Neo-brutalist styled card for "Task Due Soon (H-7)" — tasks whose release
 * date falls within the next 7 days.
 *
 * Two visual states mirror the Figma redesign:
 * - Empty: Solway font + big amber "0" + yellow drop shadow (playful)
 * - Filled: Plus Jakarta Sans + task cards on left + big amber count at
 *   bottom-right (information-dense)
 *
 * @see component redesign/Task Due Soon {jika tidak ada task nya, saat ada task}
 */

interface Props {
    /** Total count of due-soon tasks (may exceed displayed tasks) */
    count: number;
    /** Array of tasks to display (first 3 are shown) */
    tasks: DeadlineTask[];
    /** URL to full task list (optional) */
    viewAllLink?: string;
    /** Show loading skeleton */
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    viewAllLink: undefined,
    loading: false,
});

/** Limit visible tasks to keep card compact */
const displayTasks = computed(() => props.tasks.slice(0, 3));

/** Whether the list is empty */
const isEmpty = computed(() => props.count === 0 || props.tasks.length === 0);

/**
 * Format a relative "due in X" label from a release date.
 * Examples: "Due Today", "Due Tomorrow", "Due 3d", "Due Overdue"
 */
const dueLabel = (date?: string): string => {
    if (!date) return 'Due -';
    const now = new Date();
    now.setHours(0, 0, 0, 0);
    const target = new Date(date);
    target.setHours(0, 0, 0, 0);
    const diffDays = Math.round((target.getTime() - now.getTime()) / 86400000);
    if (diffDays < 0) return 'Overdue';
    if (diffDays === 0) return 'Due Today';
    if (diffDays === 1) return 'Due Tomorrow';
    return `Due ${diffDays}d`;
};

/**
 * Format date as DD-MM-YYYY (spacing mirrors reference design).
 */
const formatDate = (date?: string): string => {
    if (!date) return '-';
    const d = new Date(date);
    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const yyyy = d.getFullYear();
    return `${dd}- ${mm}- ${yyyy}`;
};
</script>

<template>
    <!-- EMPTY STATE -->
    <article
        v-if="isEmpty && !loading"
        class="due-soon-card relative flex h-full flex-col overflow-hidden rounded-[18px] border-[2.5px] border-black bg-white transition-all duration-300 ease-out hover:-translate-y-1 cursor-default dark:border-slate-700/80 dark:bg-[#111c2e]"
        :aria-label="`Task Due Soon H-7: 0 tasks`"
    >
        <!-- Header -->
        <div class="flex items-center justify-between px-6 pt-5 pb-2">
            <div class="flex items-center gap-3">
                <Clock
                    :size="34"
                    :stroke-width="2"
                    class="flex-shrink-0 text-[#0b2a6b] dark:text-tm-navy-pale"
                />
                <h2
                    class="whitespace-nowrap text-[#0b2a6b] dark:text-tm-navy-pale font-['Solway',serif] font-bold text-[20px] sm:text-[24px] lg:text-[26px] leading-tight"
                >
                    Task Due Soon (H-7)
                </h2>
            </div>

            <Link
                v-if="viewAllLink"
                :href="viewAllLink"
                class="flex items-center gap-1 rounded-full border-2 border-black bg-white px-4 py-1 text-[#111] transition-all duration-200 hover:bg-[#111] hover:text-white hover:scale-105 dark:bg-slate-900/40 dark:text-slate-100 dark:border-slate-500 dark:hover:bg-slate-100 dark:hover:text-[#111] font-['Solway',serif] text-[14px] font-medium"
            >
                View All
                <ArrowRight :size="14" :stroke-width="2.5" />
            </Link>
        </div>

        <div class="mx-6 border-t border-gray-100 dark:border-border/60" />

        <!-- Big zero -->
        <div class="flex flex-1 flex-col items-center justify-center gap-3 py-10">
            <span
                class="font-['Solway',serif] font-bold leading-none text-[#FAA700]"
                style="font-size: 72px"
            >
                0
            </span>
            <p
                class="font-['Solway',serif] font-normal text-[15px] text-gray-500 dark:text-muted-foreground"
            >
                Tidak ada task due soon.
            </p>
        </div>
    </article>

    <!-- LOADING SKELETON -->
    <article
        v-else-if="loading"
        class="relative flex h-full flex-col overflow-hidden rounded-2xl border-[2.5px] border-black bg-white p-4 animate-pulse dark:border-slate-700/80 dark:bg-[#111c2e]"
    >
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <div class="h-8 w-8 rounded bg-muted/40"></div>
                <div class="h-6 w-48 rounded bg-muted/40"></div>
            </div>
            <div class="h-7 w-24 rounded-lg bg-muted/30"></div>
        </div>
        <div class="flex flex-col gap-2.5" style="width: 56%">
            <div class="h-14 rounded-xl border-2 border-amber-200 bg-amber-50/30"></div>
            <div class="h-14 rounded-xl border-2 border-amber-200 bg-amber-50/30"></div>
        </div>
    </article>

    <!-- FILLED STATE -->
    <article
        v-else
        class="due-soon-card relative flex h-full flex-col overflow-hidden rounded-2xl border-[2.5px] border-black bg-white px-[18px] pt-4 pb-3.5 transition-all duration-300 ease-out hover:-translate-y-1 cursor-default dark:border-slate-700/80 dark:bg-[#111c2e]"
        :aria-label="`Task Due Soon H-7: ${count} tasks`"
    >
        <!-- Header -->
        <div class="flex items-center justify-between mb-3">
            <!-- Left: icon + title -->
            <div class="flex items-center gap-2">
                <Clock
                    :size="30"
                    :stroke-width="2.2"
                    class="flex-shrink-0 text-[#0a2463] dark:text-tm-navy-pale"
                />
                <h2
                    class="whitespace-nowrap text-[#0a2463] dark:text-tm-navy-pale font-extrabold tracking-[-0.3px] leading-tight text-[20px] sm:text-[22px] lg:text-[24px]"
                >
                    Task Due Soon (H-7)
                </h2>
            </div>

            <!-- Right: View All button (rectangle rounded, not pill) -->
            <Link
                v-if="viewAllLink"
                :href="viewAllLink"
                class="flex flex-shrink-0 items-center gap-1 rounded-lg border-[1.5px] border-black bg-white px-2.5 py-1 text-[13px] font-semibold text-[#111] leading-tight transition-all duration-200 hover:bg-[#111] hover:text-white hover:scale-105 dark:bg-slate-900/40 dark:text-slate-100 dark:border-slate-500 dark:hover:bg-slate-100 dark:hover:text-[#111]"
            >
                View All&nbsp;<ArrowRight :size="13" :stroke-width="2.5" />
            </Link>
        </div>

        <!-- Task list (left side ~56% width) -->
        <div
            class="task-list-scroll flex flex-col gap-2 pr-[35%] overflow-y-auto"
            style="max-height: 160px; padding-bottom: 24px;"
            role="list"
            aria-label="Daftar task due soon"
        >
            <div
                v-for="task in displayTasks"
                :key="task.id"
                role="listitem"
                class="due-soon-item relative rounded-xl border-2 border-[#f5ab00] bg-white px-[10px] pt-2 pb-2 pl-3 transition-all duration-200 ease-out hover:bg-amber-50/60 hover:shadow-[0_4px_10px_2px_rgba(245,171,0,0.35)] dark:border-amber-400/55 dark:bg-slate-950/20 dark:hover:bg-amber-400/10"
            >
                <!-- Date top-right -->
                <span
                    class="absolute right-2.5 top-[7px] text-[11.5px] font-normal text-gray-500 tracking-[0.1px] dark:text-slate-400"
                >
                    {{ formatDate(task.release_date) }}
                </span>

                <!-- Title -->
                <Link
                    :href="showTask(task.id).url"
                    class="m-0 block truncate pr-20 text-[14.5px] font-medium leading-[1.4] text-[#111] hover:text-sky-600 transition-colors dark:text-slate-100 dark:hover:text-sky-400"
                >
                    {{ task.title }}
                </Link>

                <!-- Due sub-label -->
                <p
                    class="m-0 mt-px text-[11.5px] font-normal leading-[1.4] text-gray-500 dark:text-slate-400"
                >
                    {{ dueLabel(task.release_date) }}
                </p>
            </div>
        </div>

        <!-- More indicator → arahkan ke View All -->
        <Link
            v-if="tasks.length > 3 && viewAllLink"
            :href="viewAllLink"
            class="mt-1.5 inline-block rounded px-1.5 py-0.5 text-[11.5px] font-semibold text-[#f5ab00] transition-all duration-200 hover:text-emerald-400 dark:hover:text-amber-300"
        >
            +{{ tasks.length - 3 }} task lainnya → lihat semua
        </Link>

        <!-- Big count number bottom-right -->
        <span
            class="absolute bottom-2 right-[18px] select-none font-black leading-none text-[#f5ab00]"
            style="font-size: clamp(28px, 4vw, 48px)"
            aria-hidden="true"
        >
            {{ count }}
        </span>
    </article>
</template>

<style scoped>
/* Scrollbar selalu tersembunyi — scroll tetap bisa via mouse wheel/touch */
.task-list-scroll {
    scrollbar-width: none;
}

.task-list-scroll::-webkit-scrollbar {
    display: none;
}

.due-soon-card {
    box-shadow: 1px 3px 7px 1px #faa700;
}

.due-soon-card:hover {
    box-shadow: 2px 6px 12px 2px #faa700;
}

.due-soon-item {
    box-shadow: 0 2px 5px 0 #f5ab00;
}

:global(.dark) .due-soon-card {
    box-shadow:
        0 0 0 1px rgba(251, 191, 36, 0.12),
        0 14px 32px rgba(0, 0, 0, 0.48);
}

:global(.dark) .due-soon-card:hover {
    box-shadow:
        0 0 0 1px rgba(251, 191, 36, 0.32),
        0 18px 40px rgba(0, 0, 0, 0.56);
}

:global(.dark) .due-soon-item {
    box-shadow:
        0 0 0 1px rgba(251, 191, 36, 0.1) inset,
        0 8px 18px rgba(0, 0, 0, 0.25);
}
</style>
