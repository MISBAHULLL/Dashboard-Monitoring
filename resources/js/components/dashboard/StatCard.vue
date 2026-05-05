<script setup lang="ts">
import { computed } from 'vue';
import type { Component } from 'vue';
import type { ColorTheme } from '@/types/dashboard';

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
}

const props = withDefaults(defineProps<Props>(), {
  colorTheme: 'neutral',
  loading: false,
  animate: true,
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
    'relative overflow-hidden rounded-xl border-[1.5px] p-6',
    'shadow-[2px_2px_0_0_rgba(0,0,0,0.08)] dark:shadow-[2px_2px_0_0_rgba(255,255,255,0.05)]',
    theme.value.bg,
    theme.value.border,
  ];

  if (props.animate) {
    base.push(
      'transition-all duration-200 ease-out',
      'hover:shadow-[4px_4px_0_0_rgba(0,0,0,0.1)] dark:hover:shadow-[4px_4px_0_0_rgba(255,255,255,0.08)]',
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
    <div v-else class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-muted-foreground">{{ label }}</p>
        <p
          class="mt-2 text-3xl font-bold"
          :class="theme.valueColor"
        >
          {{ value }}
        </p>
      </div>
      <div
        :class="[
          'rounded-xl p-3 border-[1.5px]',
          theme.iconBg,
          theme.border,
        ]"
      >
        <component
          :is="icon"
          class="h-6 w-6"
          :class="theme.iconColor"
        />
      </div>
    </div>
  </article>
</template>
