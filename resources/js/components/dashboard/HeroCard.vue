<script setup lang="ts">
import { computed } from 'vue';
import type { Component } from 'vue';

/**
 * HeroCard Component
 *
 * Large greeting card with dark navy background, mini bar chart,
 * and key metrics display. Designed as the focal point of the dashboard.
 *
 * Features:
 * - Dark navy gradient background (#112548 to #1B3A6B)
 * - Greeting message with emoji
 * - Subtitle with highlighted pending/overdue counts
 * - Mini bar chart visualization
 * - Total tasks display with large typography
 * - Neo-brutalism border styling
 * - Full dark mode support
 */

interface Props {
  /**
   * User name for greeting (e.g., "Admin PO")
   */
  userName: string;

  /**
   * Pending tasks count for subtitle highlight
   */
  pendingCount: number;

  /**
   * Overdue tasks count for subtitle highlight
   */
  overdueCount: number;

  /**
   * Total tasks count for big display
   */
  totalTasks: number;

  /**
   * Mini chart data (7 days or last 5 bars)
   * @default [3, 5, 2, 8, 6, 9, 12]
   */
  chartData?: number[];

  /**
   * Show loading skeleton instead of content
   * @default false
   */
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  chartData: () => [3, 5, 2, 8, 6, 9, 12],
  loading: false,
});

/**
 * Container classes with dark navy gradient background
 */
const containerClasses = computed(() => [
  'relative overflow-hidden rounded-xl border-2 border-tm-navy p-5',
  'bg-gradient-to-br from-[#112548] to-[#1B3A6B]',
  'shadow-[3px_3px_0_0_rgba(27,58,107,0.25)]',
  'transition-all duration-200 ease-out',
  'hover:shadow-[5px_5px_0_0_rgba(27,58,107,0.35)]',
  'hover:-translate-y-0.5',
]);

/**
 * Calculate bar heights as percentages for the mini chart
 */
const maxValue = computed(() => Math.max(...props.chartData, 1));
const barHeights = computed(() =>
  props.chartData.map((value) => (value / maxValue.value) * 100)
);

/**
 * Get bar color based on position (gradient effect)
 */
const getBarColor = (index: number): string => {
  const colors = [
    'bg-[#2D5090]/40', // Darkest
    'bg-[#3B6AB8]/50',
    'bg-[#4A7FC5]/60',
    'bg-[#5A94D1]/70',
    'bg-[#6BA9DD]/80',
    'bg-[#2BAE6E]/90', // Green accent
    'bg-[#2BAE6E]',    // Full green (last bar)
  ];
  return colors[index] || 'bg-[#2BAE6E]';
};

/**
 * Get greeting based on time of day
 */
const greeting = computed(() => {
  const hour = new Date().getHours();
  if (hour < 12) return 'Good Morning';
  if (hour < 17) return 'Good Afternoon';
  return 'Good Evening';
});
</script>

<template>
  <article :class="containerClasses">
    <!-- Loading skeleton -->
    <div v-if="loading" class="animate-pulse space-y-4">
      <div class="h-8 w-48 rounded bg-white/20"></div>
      <div class="h-4 w-64 rounded bg-white/15"></div>
      <div class="flex items-end gap-1 h-24 mt-8">
        <div v-for="i in 7" :key="i" class="w-4 bg-white/20 rounded-t" :style="{ height: `${20 + i * 10}%` }"></div>
      </div>
      <div class="h-8 w-24 rounded bg-white/20"></div>
    </div>

    <!-- Content -->
    <template v-else>
      <div class="relative z-10">
        <!-- Greeting -->
        <h1 class="text-3xl font-bold text-white tracking-tight">
          {{ greeting }},<br />
          <span class="text-tm-green">{{ userName }}</span> 👋
        </h1>

        <!-- Subtitle with highlighted counts -->
        <p class="mt-3 text-sm text-[#9AAAB8]">
          kamu mempunyai
          <span class="inline-flex items-center gap-1 mx-1">
            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-semibold bg-tm-green-pale text-tm-green">
              {{ pendingCount }} Pending
            </span>
          </span>
          dan
          <span class="inline-flex items-center gap-1 mx-1">
            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-semibold bg-tm-danger-pale text-tm-danger">
              {{ overdueCount }} Overdue
            </span>
          </span>
        </p>

        <!-- Mini bar chart -->
        <div class="mt-8 flex items-end justify-end gap-1.5 h-24">
          <div
            v-for="(height, index) in barHeights"
            :key="index"
            class="w-4 rounded-t transition-all duration-500 ease-out"
            :class="getBarColor(index)"
            :style="{ height: `${height}%` }"
          ></div>
        </div>

        <!-- Total tasks display -->
        <div class="mt-4">
          <p class="text-xs uppercase tracking-wider text-[#9AAAB8] font-medium">
            TOTAL TASKS
          </p>
          <p class="text-4xl font-extrabold text-white tracking-tight">
            {{ totalTasks }}
          </p>
        </div>
      </div>

      <!-- Decorative gradient overlay -->
      <div
        class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-[#2BAE6E]/10 to-transparent pointer-events-none"
      ></div>
    </template>
  </article>
</template>
