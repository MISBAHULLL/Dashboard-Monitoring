<script setup lang="ts">
import { computed } from 'vue';
import type { Component } from 'vue';
import type { ColorTheme } from '@/types/dashboard';
import TrendBadge from './TrendBadge.vue';

/**
 * StatCard Component
 *
 * Displays numerical statistics with icon, label, and neo-brutalism styling.
 * Part of the Bento Grid dashboard system.
 *
 * @see Requirements: 2.1, 2.2, 2.4, 2.5, 2.6, 3.1-3.6, 8.1-8.5, 12.2, 16.1-16.5
 */

interface Props {
  /**
   * Statistic label text (e.g., "Total Tasks", "Open Tasks")
   */
  label: string;

  /**
   * Statistic value to display (number or formatted string)
   */
  value: number | string;

  /**
   * Lucide icon component to display
   */
  icon: Component;

  /**
   * Color theme for the card
   * @default 'neutral'
   */
  colorTheme?: ColorTheme;

  /**
   * Show loading skeleton instead of content
   * @default false
   */
  loading?: boolean;

  /**
   * Enable hover animations
   * @default true
   */
  animate?: boolean;

  /**
   * Trend direction for badge (optional)
   * 'up' for positive, 'down' for negative
   */
  trendDirection?: 'up' | 'down';

  /**
   * Trend value for badge (optional)
   */
  trendValue?: number;

  /**
   * Compact mode for grid layouts (smaller padding)
   * @default false
   */
  compact?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  colorTheme: 'neutral',
  loading: false,
  animate: true,
  compact: false,
});

/**
 * Color theme mappings for neo-brutalism styling
 * Each theme defines background, border, icon background, icon color, and value color
 */
const themeClasses = {
  navy: {
    bg: 'bg-tm-navy-pale dark:bg-tm-navy/20',
    border: 'border-tm-navy',
    iconBg: 'bg-tm-navy/10',
    iconColor: 'text-tm-navy',
    valueColor: 'text-tm-navy',
  },
  green: {
    bg: 'bg-tm-green-pale dark:bg-tm-green/20',
    border: 'border-tm-green',
    iconBg: 'bg-tm-green/10',
    iconColor: 'text-tm-green',
    valueColor: 'text-tm-green-dark',
  },
  red: {
    bg: 'bg-tm-danger-pale dark:bg-tm-danger/20',
    border: 'border-tm-danger',
    iconBg: 'bg-tm-danger/10',
    iconColor: 'text-tm-danger',
    valueColor: 'text-tm-danger',
  },
  amber: {
    bg: 'bg-tm-warning-pale dark:bg-tm-warning/20',
    border: 'border-tm-warning',
    iconBg: 'bg-tm-warning/10',
    iconColor: 'text-tm-warning',
    valueColor: 'text-amber-700 dark:text-amber-400',
  },
  neutral: {
    bg: 'bg-card',
    border: 'border-border',
    iconBg: 'bg-muted',
    iconColor: 'text-muted-foreground',
    valueColor: 'text-foreground',
  },
} as const;

/**
 * Get theme classes based on colorTheme prop
 */
const theme = computed(() => themeClasses[props.colorTheme]);

/**
 * Compute container classes including hover animations
 */
const containerClasses = computed(() => {
  const base = [
    'relative overflow-hidden rounded-2xl border-2 p-5',
    'shadow-[3px_3px_0_0_rgba(27,58,107,0.12)] dark:shadow-[3px_3px_0_0_rgba(27,58,107,0.25)]',
    theme.value.bg,
    theme.value.border,
  ];

  if (props.compact) {
    base[1] = 'p-4';
  }

  if (props.animate) {
    base.push(
      'transition-all duration-200 ease-out',
      'hover:shadow-[5px_5px_0_0_rgba(27,58,107,0.15)] dark:hover:shadow-[5px_5px_0_0_rgba(27,58,107,0.35)]',
      'hover:-translate-y-0.5'
    );
  }

  return base;
});

/**
 * ARIA label for accessibility
 * Format: "{label}: {value}"
 */
const ariaLabel = computed(() => `${props.label}: ${props.value}`);
</script>

<template>
  <article
    :class="containerClasses"
    :aria-label="ariaLabel"
  >
    <!-- Loading skeleton with pulse animation -->
    <div v-if="loading" class="animate-pulse">
      <div class="h-4 w-24 rounded bg-muted/50 mb-2"></div>
      <div class="h-8 w-16 rounded bg-muted/50"></div>
    </div>

    <!-- Content -->
    <div v-else class="flex items-start justify-between gap-3">
      <div class="min-w-0 flex-1">
        <p class="text-sm font-medium text-muted-foreground truncate">{{ label }}</p>
        <div class="mt-1 flex items-center gap-2">
          <p
            class="text-2xl font-extrabold tracking-tight"
            :class="theme.valueColor"
          >
            {{ value }}
          </p>
          <!-- Trend badge -->
          <TrendBadge
            v-if="trendDirection && trendValue !== undefined"
            :direction="trendDirection"
            :value="trendValue"
          />
        </div>
      </div>
      <!-- Icon container with nested shadow -->
      <div
        :class="[
          'shrink-0 rounded-xl p-2.5 border-2 shadow-[2px_2px_0_0_rgba(27,58,107,0.08)]',
          theme.iconBg,
          theme.border,
        ]"
      >
        <component
          :is="icon"
          class="h-5 w-5"
          :class="theme.iconColor"
        />
      </div>
    </div>
  </article>
</template>
