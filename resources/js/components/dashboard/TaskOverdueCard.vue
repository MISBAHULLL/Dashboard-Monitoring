<script setup lang="ts">
import { computed } from 'vue';
import { ArrowRight } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { show as showTask } from '@/routes/tasks';
import type { DeadlineTask } from '@/types/dashboard';

/**
 * TaskOverdueCard Component
 *
 * Neo-brutalist styled card for "Task Overdue" — tasks that have passed
 * their release date. Matches the Figma redesign with:
 * - White background with red drop shadow
 * - Red-bordered task items with light red background
 * - Big red count number at bottom-right
 * - Clock+alert icon in navy blue
 *
 * Layout width follows HeroCard (col-span-2 horizontal).
 *
 * @see component redesign/Task Overdue saat ada yang overdue
 */

interface Props {
    /** Total count of overdue tasks */
    count: number;
    /** Array of overdue tasks to display (first 3 shown) */
    tasks: DeadlineTask[];
    /** URL to full overdue task list */
    viewAllLink?: string;
    /** Show loading skeleton */
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    viewAllLink: undefined,
    loading: false,
});

/** Limit visible tasks to 5 */
const displayTasks = computed(() => props.tasks.slice(0, 5));

/** Whether the list is empty */
const isEmpty = computed(() => props.count === 0 || props.tasks.length === 0);

/**
 * Format date as D - M - YYYY (spacing mirrors Figma reference design).
 */
const formatDate = (date?: string): string => {
    if (!date) return '-';
    const d = new Date(date);
    const dd = d.getDate();
    const mm = d.getMonth() + 1;
    const yyyy = d.getFullYear();
    return `${dd} - ${mm} - ${yyyy}`;
};
</script>

<template>
    <!-- EMPTY STATE -->
    <article
        v-if="isEmpty && !loading"
        class="overdue-card relative flex h-full flex-col overflow-hidden rounded-[18px] border-[2.5px] border-black bg-white transition-all duration-300 ease-out hover:-translate-y-1 cursor-default dark:border-slate-700/80 dark:bg-[#111c2e]"
        aria-label="Task Overdue: 0 tasks"
    >
        <!-- Header -->
        <div class="flex items-center justify-between px-5 pt-4 pb-2">
            <div class="flex items-center gap-2.5">
                <!-- Clock+Alert SVG icon -->
                <svg
                    class="h-[28px] w-[28px] flex-shrink-0"
                    viewBox="0 0 30 30"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="M6.7536 14.1637C6.90893 13.8516 7.1482 13.589 7.44453 13.4054C7.74086 13.2218 8.08251 13.1244 8.43113 13.1242C8.77974 13.124 9.1215 13.221 9.41804 13.4043C9.71457 13.5876 9.95413 13.8499 10.1098 14.1619L16.6722 27.285C16.8155 27.571 16.8833 27.8889 16.869 28.2085C16.8548 28.5281 16.759 28.8387 16.5908 29.1108C16.4227 29.3829 16.1876 29.6075 15.9082 29.7631C15.6287 29.9188 15.314 30.0003 14.9941 30H1.87866C1.55861 30.0006 1.24372 29.9193 0.963986 29.7638C0.684248 29.6083 0.448972 29.3838 0.280562 29.1116C0.112151 28.8394 0.0162128 28.5287 0.00188104 28.209C-0.0124507 27.8892 0.0553016 27.5711 0.198687 27.285L6.7536 14.1637ZM8.43171 26.2481C8.18307 26.2481 7.94461 26.3469 7.7688 26.5227C7.59299 26.6985 7.49422 26.937 7.49422 27.1856C7.49422 27.4343 7.59299 27.6727 7.7688 27.8485C7.94461 28.0243 8.18307 28.1231 8.43171 28.1231C8.68034 28.1231 8.9188 28.0243 9.09461 27.8485C9.27042 27.6727 9.36919 27.4343 9.36919 27.1856C9.36919 26.937 9.27042 26.6985 9.09461 26.5227C8.9188 26.3469 8.68034 26.2481 8.43171 26.2481ZM16.8728 2.03066e-07C20.2372 -0.000591236 23.4732 1.29078 25.9129 3.60747C28.3525 5.92415 29.8093 9.08922 29.9826 12.4491C30.1558 15.809 29.0322 19.1071 26.8438 21.6625C24.6555 24.2178 21.5694 25.8352 18.2228 26.1806L17.3135 24.3619C20.2737 24.2719 23.079 23.0178 25.1203 20.872C27.1615 18.7261 28.2738 15.8616 28.2158 12.9005C28.1578 9.93938 26.9341 7.12068 24.8104 5.0564C22.6866 2.99213 19.8344 1.84892 16.8728 1.875C13.9981 1.87531 11.2326 2.9761 9.14403 4.9514C7.05543 6.9267 5.80225 9.62662 5.64174 12.4969C5.41935 12.747 5.23143 13.0257 5.083 13.3256L3.97301 15.5437C3.61762 13.6483 3.6843 11.6978 4.1683 9.83096C4.6523 7.96418 5.54173 6.22698 6.77332 4.74297C8.00491 3.25895 9.5484 2.06458 11.294 1.24481C13.0396 0.425046 14.9444 3.02357e-05 16.8728 2.03066e-07ZM8.43171 16.8656C8.18307 16.8656 7.94461 16.9644 7.7688 17.1402C7.59299 17.316 7.49422 17.5545 7.49422 17.8031V23.4319C7.49422 23.6805 7.59299 23.919 7.7688 24.0948C7.94461 24.2706 8.18307 24.3694 8.43171 24.3694C8.68034 24.3694 8.9188 24.2706 9.09461 24.0948C9.27042 23.919 9.36919 23.6805 9.36919 23.4319V17.8031C9.36919 17.5545 9.27042 17.316 9.09461 17.1402C8.9188 16.9644 8.68034 16.8656 8.43171 16.8656ZM15.9354 5.625C16.184 5.625 16.4224 5.72377 16.5983 5.89959C16.7741 6.0754 16.8728 6.31386 16.8728 6.5625V13.125H21.5603C21.8089 13.125 22.0474 13.2238 22.2232 13.3996C22.399 13.5754 22.4978 13.8139 22.4978 14.0625C22.4978 14.3111 22.399 14.5496 22.2232 14.7254C22.0474 14.9012 21.8089 15 21.5603 15H15.9354C15.6867 15 15.4483 14.9012 15.2725 14.7254C15.0966 14.5496 14.9979 14.3111 14.9979 14.0625V6.5625C14.9979 6.31386 15.0966 6.0754 15.2725 5.89959C15.4483 5.72377 15.6867 5.625 15.9354 5.625Z"
                        fill="#093B70"
                    />
                </svg>
                <h2
                    class="whitespace-nowrap text-[#093b70] dark:text-blue-200 font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[20px] sm:text-[22px] lg:text-[24px] leading-tight"
                >
                    Task Overdue
                </h2>
            </div>

            <Link
                v-if="viewAllLink"
                :href="viewAllLink"
                class="flex items-center gap-1 rounded-lg border-[1.5px] border-black bg-white px-2.5 py-1 text-[13px] font-semibold text-[#111] leading-tight transition-all duration-200 hover:bg-[#111] hover:text-white hover:scale-105 dark:bg-slate-900/40 dark:text-slate-100 dark:border-slate-500 dark:hover:bg-slate-100 dark:hover:text-[#111]"
            >
                View All&nbsp;<ArrowRight :size="13" :stroke-width="2.5" />
            </Link>
        </div>

        <div class="mx-5 border-t border-gray-100 dark:border-border/60" />

        <!-- Big zero -->
        <div class="flex flex-1 flex-col items-center justify-center gap-3 py-10">
            <span
                class="font-['Plus_Jakarta_Sans',sans-serif] font-bold leading-none text-[#f24040]"
                style="font-size: 72px"
            >
                0
            </span>
            <p
                class="font-['Plus_Jakarta_Sans',sans-serif] font-normal text-[15px] text-gray-500 dark:text-muted-foreground"
            >
                Tidak ada task overdue.
            </p>
        </div>
    </article>

    <!-- LOADING SKELETON -->
    <article
        v-else-if="loading"
        class="relative flex h-full flex-col overflow-hidden rounded-[18px] border-[2.5px] border-black bg-white p-4 animate-pulse dark:border-slate-700/80 dark:bg-[#111c2e]"
    >
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <div class="h-7 w-7 rounded bg-muted/40"></div>
                <div class="h-6 w-40 rounded bg-muted/40"></div>
            </div>
            <div class="h-7 w-20 rounded-lg bg-muted/30"></div>
        </div>
        <div class="flex flex-col gap-2.5">
            <div class="h-[56px] rounded-[9px] border border-red-200 bg-red-50/30"></div>
            <div class="h-[56px] rounded-[9px] border border-red-200 bg-red-50/30"></div>
            <div class="h-[56px] rounded-[9px] border border-red-200 bg-red-50/30"></div>
        </div>
    </article>

    <!-- FILLED STATE -->
    <article
        v-else
        class="overdue-card relative flex h-full flex-col overflow-hidden rounded-[18px] border-[2.5px] border-black bg-white px-[18px] pt-4 pb-3.5 transition-all duration-300 ease-out hover:-translate-y-1 cursor-default dark:border-slate-700/80 dark:bg-[#111c2e]"
        :aria-label="`Task Overdue: ${count} tasks`"
    >
        <!-- Header -->
        <div class="flex items-center justify-between mb-3">
            <!-- Left: icon + title -->
            <div class="flex items-center gap-2">
                <!-- Clock+Alert SVG icon -->
                <svg
                    class="h-[28px] w-[28px] flex-shrink-0"
                    viewBox="0 0 30 30"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="M6.7536 14.1637C6.90893 13.8516 7.1482 13.589 7.44453 13.4054C7.74086 13.2218 8.08251 13.1244 8.43113 13.1242C8.77974 13.124 9.1215 13.221 9.41804 13.4043C9.71457 13.5876 9.95413 13.8499 10.1098 14.1619L16.6722 27.285C16.8155 27.571 16.8833 27.8889 16.869 28.2085C16.8548 28.5281 16.759 28.8387 16.5908 29.1108C16.4227 29.3829 16.1876 29.6075 15.9082 29.7631C15.6287 29.9188 15.314 30.0003 14.9941 30H1.87866C1.55861 30.0006 1.24372 29.9193 0.963986 29.7638C0.684248 29.6083 0.448972 29.3838 0.280562 29.1116C0.112151 28.8394 0.0162128 28.5287 0.00188104 28.209C-0.0124507 27.8892 0.0553016 27.5711 0.198687 27.285L6.7536 14.1637ZM8.43171 26.2481C8.18307 26.2481 7.94461 26.3469 7.7688 26.5227C7.59299 26.6985 7.49422 26.937 7.49422 27.1856C7.49422 27.4343 7.59299 27.6727 7.7688 27.8485C7.94461 28.0243 8.18307 28.1231 8.43171 28.1231C8.68034 28.1231 8.9188 28.0243 9.09461 27.8485C9.27042 27.6727 9.36919 27.4343 9.36919 27.1856C9.36919 26.937 9.27042 26.6985 9.09461 26.5227C8.9188 26.3469 8.68034 26.2481 8.43171 26.2481ZM16.8728 2.03066e-07C20.2372 -0.000591236 23.4732 1.29078 25.9129 3.60747C28.3525 5.92415 29.8093 9.08922 29.9826 12.4491C30.1558 15.809 29.0322 19.1071 26.8438 21.6625C24.6555 24.2178 21.5694 25.8352 18.2228 26.1806L17.3135 24.3619C20.2737 24.2719 23.079 23.0178 25.1203 20.872C27.1615 18.7261 28.2738 15.8616 28.2158 12.9005C28.1578 9.93938 26.9341 7.12068 24.8104 5.0564C22.6866 2.99213 19.8344 1.84892 16.8728 1.875C13.9981 1.87531 11.2326 2.9761 9.14403 4.9514C7.05543 6.9267 5.80225 9.62662 5.64174 12.4969C5.41935 12.747 5.23143 13.0257 5.083 13.3256L3.97301 15.5437C3.61762 13.6483 3.6843 11.6978 4.1683 9.83096C4.6523 7.96418 5.54173 6.22698 6.77332 4.74297C8.00491 3.25895 9.5484 2.06458 11.294 1.24481C13.0396 0.425046 14.9444 3.02357e-05 16.8728 2.03066e-07ZM8.43171 16.8656C8.18307 16.8656 7.94461 16.9644 7.7688 17.1402C7.59299 17.316 7.49422 17.5545 7.49422 17.8031V23.4319C7.49422 23.6805 7.59299 23.919 7.7688 24.0948C7.94461 24.2706 8.18307 24.3694 8.43171 24.3694C8.68034 24.3694 8.9188 24.2706 9.09461 24.0948C9.27042 23.919 9.36919 23.6805 9.36919 23.4319V17.8031C9.36919 17.5545 9.27042 17.316 9.09461 17.1402C8.9188 16.9644 8.68034 16.8656 8.43171 16.8656ZM15.9354 5.625C16.184 5.625 16.4224 5.72377 16.5983 5.89959C16.7741 6.0754 16.8728 6.31386 16.8728 6.5625V13.125H21.5603C21.8089 13.125 22.0474 13.2238 22.2232 13.3996C22.399 13.5754 22.4978 13.8139 22.4978 14.0625C22.4978 14.3111 22.399 14.5496 22.2232 14.7254C22.0474 14.9012 21.8089 15 21.5603 15H15.9354C15.6867 15 15.4483 14.9012 15.2725 14.7254C15.0966 14.5496 14.9979 14.3111 14.9979 14.0625V6.5625C14.9979 6.31386 15.0966 6.0754 15.2725 5.89959C15.4483 5.72377 15.6867 5.625 15.9354 5.625Z"
                        fill="#093B70"
                    />
                </svg>
                <h2
                    class="whitespace-nowrap text-[#093b70] dark:text-blue-200 font-['Plus_Jakarta_Sans',sans-serif] font-extrabold tracking-[-0.3px] leading-tight text-[20px] sm:text-[22px] lg:text-[24px]"
                >
                    Task Overdue
                </h2>
            </div>

            <!-- Right: View All button -->
            <Link
                v-if="viewAllLink"
                :href="viewAllLink"
                class="flex flex-shrink-0 items-center gap-1 rounded-lg border-[1.5px] border-black bg-white px-2.5 py-1 text-[13px] font-semibold text-[#111] leading-tight transition-all duration-200 hover:bg-[#111] hover:text-white hover:scale-105 dark:bg-slate-900/40 dark:text-slate-100 dark:border-slate-500 dark:hover:bg-slate-100 dark:hover:text-[#111]"
            >
                View All&nbsp;<ArrowRight :size="13" :stroke-width="2.5" />
            </Link>
        </div>

        <!-- Task list — scrollable, shows up to 5 tasks -->
        <div
            class="task-list-scroll flex flex-col gap-2 overflow-y-auto"
            style="max-height: 280px; padding-bottom: 4px;"
            role="list"
            aria-label="Daftar task overdue"
        >
            <div
                v-for="task in displayTasks"
                :key="task.id"
                role="listitem"
                class="relative rounded-[9px] border border-[#ef4444] bg-white px-[10px] pt-2 pb-2 pl-3 transition-all duration-200 ease-out hover:bg-[#fff5f5] hover:shadow-[4px_4px_2.8px_1px_rgba(239,68,68,0.25)] dark:border-red-400/55 dark:bg-slate-950/20 dark:hover:bg-red-500/10"
            >
                <!-- Date top-right -->
                <span
                    class="absolute right-2.5 top-[7px] text-[11.5px] font-normal text-gray-600 tracking-[0.1px] dark:text-slate-400"
                >
                    {{ formatDate(task.sla_due_date ?? task.release_date) }}
                </span>

                <!-- Title -->
                <Link
                    :href="showTask(task.id).url"
                    class="m-0 block truncate pr-24 text-[15px] font-medium leading-[1.4] text-[#111] hover:text-red-700 transition-colors dark:text-slate-100 dark:hover:text-red-400"
                >
                    {{ task.title }}
                </Link>

                <!-- Client name -->
                <p
                    class="m-0 mt-px text-[11.5px] font-normal leading-[1.4] text-gray-600 dark:text-slate-400"
                >
                    {{ task.client?.name || '-' }}
                </p>
            </div>
        </div>

        <!-- More indicator + big count row -->
        <div class="flex items-end justify-between mt-2 flex-shrink-0">
            <Link
                v-if="tasks.length > 5 && viewAllLink"
                :href="viewAllLink"
                class="inline-block rounded px-1.5 py-0.5 text-[11.5px] font-semibold text-[#f24040] transition-all duration-200 hover:text-red-700 dark:hover:text-red-300"
            >
                +{{ tasks.length - 5 }} task lainnya → lihat semua
            </Link>
            <span v-else class="flex-1" />

            <!-- Big count number bottom-right -->
            <span
                class="select-none font-['Plus_Jakarta_Sans',sans-serif] font-bold leading-none text-[#f24040]"
                style="font-size: clamp(28px, 4vw, 48px)"
                aria-hidden="true"
            >
                {{ count }}
            </span>
        </div>
    </article>
</template>

<style scoped>
/* Hide scrollbar but keep scroll functionality */
.task-list-scroll {
    scrollbar-width: none;
}

.task-list-scroll::-webkit-scrollbar {
    display: none;
}

.overdue-card {
    box-shadow: 2px 4px 4px 0 #e01d1d;
}

.overdue-card:hover {
    box-shadow: 2px 6px 12px 2px #e01d1d;
}

:global(.dark) .overdue-card {
    box-shadow:
        0 0 0 1px rgba(248, 113, 113, 0.12),
        0 14px 32px rgba(0, 0, 0, 0.48);
}

:global(.dark) .overdue-card:hover {
    box-shadow:
        0 0 0 1px rgba(248, 113, 113, 0.3),
        0 18px 40px rgba(0, 0, 0, 0.56);
}
</style>
