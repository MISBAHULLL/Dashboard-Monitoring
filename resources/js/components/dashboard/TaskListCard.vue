<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import ActionTooltip from '@/components/ActionTooltip.vue';
import { show as showTask, index as tasksIndex } from '@/routes/tasks';
import type { Task, TaskListVariant, TaskStatus, TaskPriority } from '@/types/dashboard';

/**
 * TaskListCard Component
 *
 * Displays task list as a card-based timeline for both admin (recent tasks) and member (assigned tasks).
 * Part of the Bento Grid dashboard system.
 *
 * Features:
 * - Two variants: 'recent' (admin dashboard) and 'assigned' (member dashboard)
 * - Card-based timeline layout with status accent bar
 * - Status badges with correct colors (amber=open, blue=in_progress, red=revision, green=completed)
 * - Priority badges with correct colors (red=urgent, amber=high, blue=medium, slate=low)
 * - Displays empty state messages per variant
 * - Filters out completed tasks for 'assigned' variant
 * - Inline loading skeleton with pulse animation
 * - Semantic HTML with article element
 * - Dark mode support
 * - Hover lift effect on each task card
 *
 * @see Requirements: 7.1-7.5, 9.1-9.6, 16.1-16.5
 */

interface Props {
  /**
   * Task list variant
   * - 'recent': For admin dashboard, shows 5 most recent tasks
   * - 'assigned': For member dashboard, shows user's pending tasks (excludes completed)
   */
  variant: TaskListVariant;

  /**
   * Array of task objects to display
   * For 'assigned' variant, completed tasks will be filtered out
   */
  tasks: Task[];

  /**
   * Show loading skeleton instead of content
   * @default false
   */
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
});

/**
 * Configuration for each variant
 * Defines title, description, and empty state message
 */
const variantConfig = {
  recent: {
    title: '5 Task Terbaru',
    description: 'Task yang baru saja dibuat ke dalam sistem.',
    emptyTitle: 'Belum ada task terbaru',
    emptyMessage: 'Task baru akan muncul di sini setelah dibuat atau setelah data restore berhasil masuk ke tabel task.',
    emptyHint: 'Gunakan daftar task untuk memastikan filter periode atau hasil restore sudah sesuai.',
    emptyActionLabel: 'Buka Daftar Task',
  },
  assigned: {
    title: 'Tugas Anda yang Belum Selesai',
    description: 'Daftar task yang di-assign ke Anda dan membutuhkan perhatian.',
    emptyTitle: 'Tidak ada tugas tertunda',
    emptyMessage: 'Anda belum memiliki task aktif yang perlu dikerjakan. Task yang baru di-assign akan muncul otomatis di kartu ini.',
    emptyHint: 'Cek daftar task jika ingin melihat riwayat atau task yang sudah selesai.',
    emptyActionLabel: 'Lihat Task',
  },
} as const;

/**
 * Get configuration for current variant
 */
const config = computed(() => variantConfig[props.variant]);

/**
 * Tasks to display
 * - For 'recent' variant: show all tasks as-is
 * - For 'assigned' variant: filter out completed tasks
 */
const displayTasks = computed(() => {
  if (props.variant === 'assigned') {
    return props.tasks.filter((task) => task.status !== 'completed');
  }
  return props.tasks;
});

/**
 * Status accent bar colors (left border of each card)
 */
const statusAccentClasses: Record<TaskStatus, string> = {
  open: 'border-l-amber-400 dark:border-l-amber-500',
  in_progress: 'border-l-blue-400 dark:border-l-blue-500',
  revision: 'border-l-red-400 dark:border-l-red-500',
  completed: 'border-l-emerald-400 dark:border-l-emerald-500',
};

/**
 * Status dot colors for the timeline connector
 */
const statusDotClasses: Record<TaskStatus, string> = {
  open: 'bg-amber-400 dark:bg-amber-500',
  in_progress: 'bg-blue-400 dark:bg-blue-500',
  revision: 'bg-red-400 dark:bg-red-500',
  completed: 'bg-emerald-400 dark:bg-emerald-500',
};

/**
 * Status badge color classes
 */
const statusBadgeClasses: Record<TaskStatus, string> = {
  open: 'bg-amber-50 text-amber-700 ring-amber-200/80 dark:bg-amber-900/20 dark:text-amber-400 dark:ring-amber-800/40',
  in_progress: 'bg-blue-50 text-blue-700 ring-blue-200/80 dark:bg-blue-900/20 dark:text-blue-400 dark:ring-blue-800/40',
  revision: 'bg-red-50 text-red-700 ring-red-200/80 dark:bg-red-900/20 dark:text-red-400 dark:ring-red-800/40',
  completed: 'bg-emerald-50 text-emerald-700 ring-emerald-200/80 dark:bg-emerald-900/20 dark:text-emerald-400 dark:ring-emerald-800/40',
};

/**
 * Priority badge color classes
 */
const priorityBadgeClasses: Record<TaskPriority, string> = {
  urgent: 'bg-red-50 text-red-600 ring-red-200/80 dark:bg-red-900/20 dark:text-red-400 dark:ring-red-800/40',
  high: 'bg-amber-50 text-amber-600 ring-amber-200/80 dark:bg-amber-900/20 dark:text-amber-400 dark:ring-amber-800/40',
  medium: 'bg-blue-50 text-blue-600 ring-blue-200/80 dark:bg-blue-900/20 dark:text-blue-400 dark:ring-blue-800/40',
  low: 'bg-slate-50 text-slate-600 ring-slate-200/80 dark:bg-slate-800/30 dark:text-slate-400 dark:ring-slate-700/40',
};

/**
 * URL for "Lihat Semua" link — filters to only the displayed task IDs
 */
const viewAllUrl = computed(() => {
  const ids = displayTasks.value.map((task) => task.id).join(',');
  if (!ids) {
    return tasksIndex.url();
  }

  return tasksIndex({ query: { ids } }).url;
});

/**
 * Format status for display
 */
const formatStatus = (status: TaskStatus): string => {
  return status.replace('_', ' ');
};

/**
 * Format date for display (Indonesian locale, short format)
 */
const formatDate = (date: string): string => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
};

/**
 * Get status accent class for card border
 */
const getAccentClass = (status: TaskStatus): string => {
  return statusAccentClasses[status];
};

/**
 * Get status dot class for timeline
 */
const getDotClass = (status: TaskStatus): string => {
  return statusDotClasses[status];
};

/**
 * Get status badge classes
 */
const getStatusClasses = (status: TaskStatus): string => {
  return statusBadgeClasses[status];
};

/**
 * Get priority badge classes
 */
const getPriorityClasses = (priority: TaskPriority): string => {
  return priorityBadgeClasses[priority];
};
</script>

<template>
  <article
    class="relative flex h-full w-full max-w-full flex-col rounded-xl border-[1.5px] border-border bg-card dark:border-slate-700/80 dark:bg-[#111c2e]
           shadow-[2px_2px_0_0_rgba(0,0,0,0.08)] dark:shadow-[0_14px_32px_rgba(0,0,0,0.42),0_0_0_1px_rgba(148,163,184,0.08)]
           transition-all duration-200 ease-out
           hover:shadow-[4px_4px_0_0_rgba(0,0,0,0.1)] dark:hover:shadow-[0_18px_40px_rgba(0,0,0,0.5),0_0_0_1px_rgba(52,211,153,0.2)]"
  >
    <div class="flex flex-col h-full p-5">
      <!-- Header -->
      <header class="mb-4 flex-shrink-0">
        <div class="flex items-start justify-between gap-2">
          <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
              <svg class="h-4 w-4 text-primary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8Z" />
                <path d="M15 3v4a2 2 0 0 0 2 2h4" />
              </svg>
            </div>
            <div>
              <h2 class="text-base font-semibold text-primary leading-tight">
                {{ config.title }}
              </h2>
              <p class="text-xs text-muted-foreground">
                {{ config.description }}
              </p>
            </div>
          </div>
          <!-- View all link -->
          <ActionTooltip label="Lihat semua task pada daftar ini" side="left">
          <Link
            :href="viewAllUrl"
            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-primary
                   hover:bg-primary/10 transition-colors flex-shrink-0"
          >
            Lihat Semua
            <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12h14" />
              <path d="m12 5 7 7-7 7" />
            </svg>
          </Link>
          </ActionTooltip>
        </div>
      </header>

      <!-- Loading skeleton -->
      <div v-if="loading" class="flex-1 animate-pulse space-y-3">
        <div v-for="i in 4" :key="i" class="flex gap-3">
          <div class="h-3 w-3 rounded-full bg-muted/40 mt-1.5"></div>
          <div class="flex-1 space-y-2 rounded-lg bg-muted/20 p-3">
            <div class="h-3.5 w-3/4 rounded bg-muted/40"></div>
            <div class="h-3 w-1/2 rounded bg-muted/30"></div>
          </div>
        </div>
      </div>

      <!-- Timeline Content -->
      <div v-else class="flex-1 overflow-y-auto pr-1 -mr-1">
        <!-- Empty state -->
        <div
          v-if="displayTasks.length === 0"
          class="flex h-full items-center justify-center px-4 py-8"
        >
          <div class="flex max-w-[420px] flex-col items-center gap-3 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-dashed border-primary/30 bg-primary/5 text-primary">
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8Z" />
                <path d="M15 3v4a2 2 0 0 0 2 2h4" />
                <path d="M8 13h8" />
                <path d="M8 17h5" />
              </svg>
            </div>

            <div class="space-y-1.5">
              <h3 class="text-sm font-semibold text-foreground">
                {{ config.emptyTitle }}
              </h3>
              <p class="text-sm leading-relaxed text-muted-foreground">
                {{ config.emptyMessage }}
              </p>
              <p class="text-xs leading-relaxed text-muted-foreground/80">
                {{ config.emptyHint }}
              </p>
            </div>

            <ActionTooltip :label="config.emptyActionLabel" side="bottom">
            <Link
              :href="viewAllUrl"
              class="inline-flex items-center gap-1.5 rounded-md border border-border bg-background px-3 py-1.5 text-xs font-semibold text-primary transition-colors hover:bg-primary/10 dark:border-slate-700 dark:bg-slate-950/30"
            >
              {{ config.emptyActionLabel }}
              <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14" />
                <path d="m12 5 7 7-7 7" />
              </svg>
            </Link>
            </ActionTooltip>
          </div>
        </div>

        <!-- Task timeline list -->
        <div v-else class="relative space-y-2.5">
          <!-- Timeline vertical line -->
          <div
            class="absolute left-[7px] top-3 bottom-3 w-px bg-border dark:bg-border/60"
            aria-hidden="true"
          ></div>

          <!-- Task cards -->
          <div
            v-for="(task, index) in displayTasks"
            :key="task.id"
            class="relative flex gap-3 group"
          >
            <!-- Timeline dot -->
            <div class="relative z-10 flex-shrink-0 mt-3">
              <div
                class="h-3.5 w-3.5 rounded-full ring-[3px] ring-card transition-transform duration-200 group-hover:scale-125"
                :class="getDotClass(task.status)"
              ></div>
            </div>

            <!-- Task card (clickable link to task detail) -->
            <ActionTooltip :label="`Buka task: ${task.title}`" side="top">
            <Link
              :href="showTask(task.id).url"
              class="flex-1 rounded-lg border-l-[3px] bg-muted/30 p-3 dark:bg-slate-950/20
                     border border-border/50 dark:border-slate-700/70
                     transition-all duration-200 ease-out
                     hover:bg-muted/50 dark:hover:bg-slate-800/50
                     hover:translate-x-0.5 hover:shadow-sm
                     focus:outline-none focus:ring-2 focus:ring-primary/30 focus:ring-offset-1
                     cursor-pointer block"
              :class="getAccentClass(task.status)"
            >
              <!-- Title row -->
              <div class="flex items-start justify-between gap-2 mb-1.5">
                <h3 class="text-sm font-semibold text-foreground leading-tight line-clamp-1">
                  {{ task.title }}
                </h3>
                <span
                  class="inline-flex flex-shrink-0 items-center rounded-md px-1.5 py-0.5 text-[10px] font-semibold capitalize ring-1 ring-inset"
                  :class="getStatusClasses(task.status)"
                >
                  {{ formatStatus(task.status) }}
                </span>
              </div>

              <!-- Meta info row -->
              <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground">
                <!-- Client -->
                <span class="inline-flex items-center gap-1">
                  <svg class="h-3 w-3 opacity-60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                  </svg>
                  {{ task.client?.name || '-' }}
                </span>

                <!-- Modul (recent variant) -->
                <span v-if="variant === 'recent' && task.modul" class="inline-flex items-center gap-1">
                  <svg class="h-3 w-3 opacity-60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="7" height="7" x="3" y="3" rx="1" />
                    <rect width="7" height="7" x="14" y="3" rx="1" />
                    <rect width="7" height="7" x="14" y="14" rx="1" />
                    <rect width="7" height="7" x="3" y="14" rx="1" />
                  </svg>
                  {{ task.modul }}
                </span>

                <!-- Priority (assigned variant) -->
                <span
                  v-if="variant === 'assigned' && task.priority"
                  class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-semibold capitalize ring-1 ring-inset"
                  :class="getPriorityClasses(task.priority)"
                >
                  {{ task.priority }}
                </span>

                <!-- Date (recent variant) -->
                <span v-if="variant === 'recent'" class="inline-flex items-center gap-1 ml-auto">
                  <svg class="h-3 w-3 opacity-60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                    <line x1="16" x2="16" y1="2" y2="6" />
                    <line x1="8" x2="8" y1="2" y2="6" />
                    <line x1="3" x2="21" y1="10" y2="10" />
                  </svg>
                  {{ formatDate(task.created_at) }}
                </span>
              </div>
            </Link>
            </ActionTooltip>
          </div>
        </div>
      </div>
    </div>
  </article>
</template>
