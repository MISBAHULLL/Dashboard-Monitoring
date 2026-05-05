<script setup lang="ts">
import { computed } from 'vue';
import { TriangleAlert, Clock3 } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import type { AlertType, DeadlineTask } from '@/types/dashboard';

/**
 * DeadlineAlertCard Component
 *
 * Displays overdue or due-soon tasks with urgency styling.
 * Part of the Bento Grid dashboard system.
 *
 * Features:
 * - Two variants: 'overdue' (red) and 'due_soon' (amber)
 * - Displays up to 10 tasks with title, client name, and release date
 * - Shows empty state message when no tasks
 * - Displays "Lihat Semua" link when tasks exceed 10
 * - Inline loading skeleton with pulse animation
 * - Semantic HTML with article element
 * - Dark mode support
 *
 * @see Requirements: 4.1-4.8, 14.1, 14.3, 16.1-16.5
 */

interface Props {
  /**
   * Alert type determining styling
   * - 'overdue': Red styling for overdue tasks
   * - 'due_soon': Amber styling for tasks due within 7 days
   */
  type: AlertType;

  /**
   * Total count of tasks (may exceed displayed tasks)
   */
  count: number;

  /**
   * Array of task objects to display
   * Only first 10 tasks will be shown
   */
  tasks: DeadlineTask[];

  /**
   * URL to full task list (optional)
   * Shown when tasks.length > 10
   */
  viewAllLink?: string;

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
 * Configuration for each alert type
 * Defines icon, label, color class, and empty state message
 */
const alertConfig = {
  overdue: {
    icon: TriangleAlert,
    label: 'Task Overdue',
    emptyMessage: 'Tidak ada task overdue.',
  },
  due_soon: {
    icon: Clock3,
    label: 'Task Due Soon (H-7)',
    emptyMessage: 'Tidak ada task due soon.',
  },
} as const;

/**
 * Get configuration for current alert type
 */
const config = computed(() => alertConfig[props.type]);

/**
 * Tasks to display (limited to 10)
 */
const displayTasks = computed(() => props.tasks.slice(0, 10));

/**
 * Whether to show "Lihat Semua" link
 */
const showViewAllLink = computed(() => props.tasks.length > 10 && props.viewAllLink);

/**
 * Format date for display (Indonesian locale)
 */
const formatDate = (date?: string): string => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID');
};

/**
 * ARIA label for accessibility
 */
const ariaLabel = computed(() => `${config.value.label}: ${props.count} tasks`);
</script>

<template>
  <article
    :class="[
      'relative overflow-hidden rounded-xl border-[1.5px] p-6',
      'shadow-[2px_2px_0_0_rgba(0,0,0,0.08)] dark:shadow-[2px_2px_0_0_rgba(255,255,255,0.05)]',
      'transition-all duration-200 ease-out',
      'hover:shadow-[4px_4px_0_0_rgba(0,0,0,0.1)] dark:hover:shadow-[4px_4px_0_0_rgba(255,255,255,0.08)]',
      'hover:-translate-y-0.5',
      type === 'overdue'
        ? 'border-tm-danger bg-tm-danger-pale/60 dark:bg-tm-danger/10'
        : 'border-tm-warning bg-tm-warning-pale/60 dark:bg-tm-warning/10',
    ]"
    :aria-label="ariaLabel"
  >
    <!-- Loading skeleton with pulse animation -->
    <div v-if="loading" class="animate-pulse space-y-4">
      <!-- Header skeleton -->
      <div class="flex items-center justify-between">
        <div class="flex-1">
          <div class="h-4 w-32 rounded bg-muted/50 mb-2"></div>
          <div class="h-8 w-16 rounded bg-muted/50"></div>
        </div>
        <div class="h-12 w-12 rounded-xl border-[1.5px] bg-muted/30 border-muted/50"></div>
      </div>
      <!-- Task items skeleton -->
      <div class="space-y-2">
        <div class="h-16 w-full rounded-lg border-[1.5px] bg-muted/20 border-muted/30"></div>
        <div class="h-16 w-full rounded-lg border-[1.5px] bg-muted/20 border-muted/30"></div>
        <div class="h-16 w-full rounded-lg border-[1.5px] bg-muted/20 border-muted/30"></div>
      </div>
    </div>

    <!-- Content -->
    <template v-else>
      <!-- Header with icon and count -->
      <div class="mb-4 flex items-center justify-between">
        <div>
          <p
            class="text-sm font-medium"
            :class="type === 'overdue' ? 'text-red-700 dark:text-red-300' : 'text-amber-700 dark:text-amber-300'"
          >
            {{ config.label }}
          </p>
          <p
            class="mt-2 text-3xl font-bold"
            :class="type === 'overdue' ? 'text-red-700 dark:text-red-300' : 'text-amber-700 dark:text-amber-300'"
          >
            {{ count }}
          </p>
        </div>
        <div
          :class="[
            'rounded-xl p-3 border-[1.5px]',
            type === 'overdue'
              ? 'bg-red-100 border-red-300 dark:bg-red-900/40 dark:border-red-900/50'
              : 'bg-amber-100 border-amber-300 dark:bg-amber-900/40 dark:border-amber-900/50',
          ]"
        >
          <component
            :is="config.icon"
            class="h-6 w-6"
            :class="type === 'overdue' ? 'text-red-600 dark:text-red-300' : 'text-amber-600 dark:text-amber-300'"
          />
        </div>
      </div>

      <!-- Task list -->
      <div class="space-y-2">
        <div
          v-for="task in displayTasks"
          :key="task.id"
          :class="[
            'rounded-lg border-[1.5px] bg-white p-3 dark:bg-card',
            'transition-colors duration-200',
            type === 'overdue'
              ? 'border-red-200/80 dark:border-red-900/30'
              : 'border-amber-200/80 dark:border-amber-900/30',
          ]"
        >
          <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">
            {{ task.title }}
          </p>
          <div class="mt-1 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
            <span class="truncate">{{ task.client?.name || '-' }}</span>
            <span
              class="font-semibold flex-shrink-0 ml-2"
              :class="type === 'overdue' ? 'text-red-600 dark:text-red-300' : 'text-amber-600 dark:text-amber-300'"
            >
              {{ formatDate(task.release_date) }}
            </span>
          </div>
        </div>

        <!-- Empty state -->
        <p
          v-if="tasks.length === 0"
          class="text-sm text-slate-500 dark:text-slate-400 py-4 text-center"
        >
          {{ config.emptyMessage }}
        </p>
      </div>

      <!-- View All Link -->
      <Link
        v-if="showViewAllLink"
        :href="viewAllLink!"
        class="mt-4 inline-flex items-center text-sm font-medium text-primary hover:text-primary/80 transition-colors"
      >
        Lihat Semua
        <svg
          class="ml-1 h-4 w-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 5l7 7-7 7"
          />
        </svg>
      </Link>
    </template>
  </article>
</template>
