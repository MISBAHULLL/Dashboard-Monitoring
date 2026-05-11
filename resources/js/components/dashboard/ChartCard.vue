<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import type { ApexOptions } from 'apexcharts';
import type { ChartType } from '@/types/dashboard';

/**
 * ChartCard Component
 *
 * Container for ApexCharts with neo-brutalism styling.
 * Part of the Bento Grid dashboard system.
 *
 * Features:
 * - Supports 'area' and 'donut' chart types
 * - Wraps VueApexCharts with consistent card styling
 * - Inline loading skeleton for chart area
 * - Semantic role="img" with aria-label for accessibility
 * - Preserves all existing ApexCharts configuration
 * - Dark mode support
 *
 * @see Requirements: 5.1-5.6, 12.4, 16.1-16.5
 */

interface Props {
  /**
   * Chart title
   */
  title: string;

  /**
   * Optional subtitle for additional context
   */
  subtitle?: string;

  /**
   * Chart type to render
   * - 'area': Area chart for trends
   * - 'donut': Donut chart for distributions
   */
  chartType: ChartType;

  /**
   * ApexCharts configuration options
   * All existing options are preserved
   */
  options: ApexOptions;

  /**
   * Chart data series
   */
  series: any;

  /**
   * Chart height in pixels
   * @default 300
   */
  height?: number;

  /**
   * Show loading skeleton instead of chart
   * @default false
   */
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  height: 300,
  loading: false,
});

/**
 * Container classes with neo-brutalism styling
 */
const containerClasses = computed(() => [
  'relative overflow-hidden rounded-xl border-2 border-border bg-card p-5',
  'shadow-[2px_2px_0_0_rgba(0,0,0,0.08)] dark:shadow-[2px_2px_0_0_rgba(255,255,255,0.05)]',
  'transition-all duration-200 ease-out',
  'hover:shadow-[4px_4px_0_0_rgba(0,0,0,0.1)] dark:hover:shadow-[4px_4px_0_0_rgba(255,255,255,0.08)]',
  'hover:-translate-y-0.5',
]);

/**
 * ARIA label for accessibility
 */
const ariaLabel = computed(() => {
  let label = props.title;
  if (props.subtitle) {
    label += ` - ${props.subtitle}`;
  }
  return label;
});
</script>

<template>
  <article
    :class="containerClasses"
    role="img"
    :aria-label="ariaLabel"
  >
    <!-- Loading skeleton with pulse animation -->
    <div v-if="loading" class="animate-pulse">
      <!-- Title skeleton -->
      <div class="h-5 w-48 rounded bg-muted/50 mb-4"></div>
      <!-- Subtitle skeleton (if subtitle prop provided) -->
      <div v-if="subtitle" class="h-4 w-64 rounded bg-muted/40 mb-4"></div>
      <!-- Chart area skeleton -->
      <div 
        class="w-full rounded bg-muted/30"
        :style="{ height: `${height}px` }"
      ></div>
    </div>

    <!-- Content -->
    <template v-else>
      <!-- Header -->
      <header class="mb-4">
        <h2 class="text-lg font-semibold text-primary">{{ title }}</h2>
        <p v-if="subtitle" class="text-sm text-muted-foreground mt-1">
          {{ subtitle }}
        </p>
      </header>

      <!-- Chart container -->
      <div 
        class="w-full"
        :class="{ 'flex justify-center': chartType === 'donut' }"
      >
        <VueApexCharts
          :type="chartType"
          :height="height"
          :options="options"
          :series="series"
        />
      </div>
    </template>
  </article>
</template>
