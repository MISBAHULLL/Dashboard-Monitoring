<script setup lang="ts">
import { computed } from 'vue';
import type { Task, TaskListVariant, TaskStatus, TaskPriority } from '@/types/dashboard';

/**
 * TaskListCard Component
 *
 * Displays task list in table format for both admin (recent tasks) and member (assigned tasks).
 * Part of the Bento Grid dashboard system.
 *
 * Features:
 * - Two variants: 'recent' (admin dashboard) and 'assigned' (member dashboard)
 * - Renders table with appropriate columns per variant
 * - Status badges with correct colors (amber=open, blue=in_progress, red=revision, green=completed)
 * - Priority badges with correct colors (red=urgent, amber=high, blue=medium, slate=low)
 * - Displays empty state messages per variant
 * - Filters out completed tasks for 'assigned' variant
 * - Inline loading skeleton with pulse animation
 * - Semantic HTML with article element and table structure
 * - Dark mode support
 * - Responsive design with horizontal scroll on mobile
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
    emptyMessage: 'Belum ada task yang dibuat.',
    colspan: 5,
  },
  assigned: {
    title: 'Tugas Anda yang Belum Selesai',
    description: 'Daftar task yang di-assign ke Anda dan membutuhkan perhatian.',
    emptyMessage: 'Hebat! Anda tidak memiliki task yang tertunda. 🎉',
    colspan: 4,
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
 * Requirement 9.1: Display assigned tasks that are not completed
 */
const displayTasks = computed(() => {
  if (props.variant === 'assigned') {
    return props.tasks.filter((task) => task.status !== 'completed');
  }
  return props.tasks;
});

/**
 * Status badge color classes
 * Requirement 7.3, 9.4: Color-coded status badges
 * - open: amber
 * - in_progress: blue
 * - revision: red
 * - completed: green (emerald)
 */
const statusBadgeClasses: Record<TaskStatus, string> = {
  open: 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-900/50',
  in_progress: 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-900/50',
  revision: 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-900/50',
  completed: 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-900/50',
};

/**
 * Priority badge color classes
 * Requirement 9.3: Color-coded priority badges
 * - urgent: red
 * - high: amber
 * - medium: blue
 * - low: slate
 */
const priorityBadgeClasses: Record<TaskPriority, string> = {
  urgent: 'border-red-200 text-red-600 dark:border-red-900/50 dark:text-red-400',
  high: 'border-amber-200 text-amber-600 dark:border-amber-900/50 dark:text-amber-400',
  medium: 'border-blue-200 text-blue-600 dark:border-blue-900/50 dark:text-blue-400',
  low: 'border-slate-200 text-slate-600 dark:border-slate-700 dark:text-slate-400',
};

/**
 * Format status for display
 * Replace underscores with spaces for better readability
 */
const formatStatus = (status: TaskStatus): string => {
  return status.replace('_', ' ');
};

/**
 * Format date for display (Indonesian locale)
 */
const formatDate = (date: string): string => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID');
};

/**
 * Get status badge classes for a task
 */
const getStatusClasses = (status: TaskStatus): string => {
  return statusBadgeClasses[status];
};

/**
 * Get priority badge classes for a task
 */
const getPriorityClasses = (priority: TaskPriority): string => {
  return priorityBadgeClasses[priority];
};
</script>

<template>
  <article
    class="relative flex-1 rounded-xl border-[1.5px] border-border bg-card
           shadow-[2px_2px_0_0_rgba(0,0,0,0.08)] dark:shadow-[2px_2px_0_0_rgba(255,255,255,0.05)]
           transition-all duration-200 ease-out
           hover:shadow-[4px_4px_0_0_rgba(0,0,0,0.1)] dark:hover:shadow-[4px_4px_0_0_rgba(255,255,255,0.08)]"
  >
    <div class="p-6">
      <!-- Header -->
      <header class="mb-4">
        <h2 class="text-lg font-semibold text-primary">
          {{ config.title }}
        </h2>
        <p class="text-sm text-muted-foreground">
          {{ config.description }}
        </p>
      </header>

      <!-- Loading skeleton with pulse animation -->
      <div v-if="loading" class="animate-pulse space-y-3">
        <!-- Table header skeleton -->
        <div class="h-10 w-full rounded bg-muted/30"></div>
        <!-- Table rows skeleton -->
        <div class="h-10 w-full rounded bg-muted/20"></div>
        <div class="h-10 w-full rounded bg-muted/20"></div>
        <div class="h-10 w-full rounded bg-muted/20"></div>
      </div>

      <!-- Content -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-muted/50 text-muted-foreground border-b border-border">
            <tr>
              <th class="py-3 px-4 font-medium">Faskes / Client</th>
              <th class="py-3 px-4 font-medium">Judul Task</th>
              <th v-if="variant === 'recent'" class="py-3 px-4 font-medium">Modul</th>
              <th v-if="variant === 'assigned'" class="py-3 px-4 font-medium">Prioritas</th>
              <th class="py-3 px-4 font-medium">Status</th>
              <th v-if="variant === 'recent'" class="py-3 px-4 font-medium">Tanggal Dibuat</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="task in displayTasks"
              :key="task.id"
              class="border-b border-border last:border-0 hover:bg-muted/30 transition-colors"
            >
              <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                {{ task.client?.name || '-' }}
              </td>
              <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-200">
                {{ task.title }}
              </td>
              <td v-if="variant === 'recent'" class="py-3 px-4 text-slate-600 dark:text-slate-400">
                {{ task.modul || '-' }}
              </td>
              <td v-if="variant === 'assigned'" class="py-3 px-4">
                <span
                  v-if="task.priority"
                  class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-semibold capitalize"
                  :class="getPriorityClasses(task.priority)"
                >
                  {{ task.priority }}
                </span>
                <span v-else class="text-slate-400">-</span>
              </td>
              <td class="py-3 px-4">
                <span
                  class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold capitalize"
                  :class="getStatusClasses(task.status)"
                >
                  {{ formatStatus(task.status) }}
                </span>
              </td>
              <td v-if="variant === 'recent'" class="py-3 px-4 text-slate-600 dark:text-slate-400">
                {{ formatDate(task.created_at) }}
              </td>
            </tr>

            <!-- Empty state -->
            <tr v-if="displayTasks.length === 0">
              <td :colspan="config.colspan" class="py-8 text-center text-muted-foreground">
                {{ config.emptyMessage }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </article>
</template>
