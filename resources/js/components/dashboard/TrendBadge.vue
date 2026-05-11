<script setup lang="ts">
import { computed } from 'vue';
import { TrendingUp, TrendingDown } from 'lucide-vue-next';

/**
 * TrendBadge Component
 *
 * Small badge showing trend direction with value change indicator.
 * Used in stat cards to show +2, -2, or other trend metrics.
 *
 * Features:
 * - Up trend (green): + value indicator
 * - Down trend (red): - value indicator
 * - Icon + value layout
 * - Neo-brutalism border styling
 * - Dark mode support
 */

interface Props {
  /**
   * Trend direction
   * 'up' for positive/green, 'down' for negative/red
   */
  direction: 'up' | 'down';

  /**
   * Trend value to display (e.g., 2, -3, 5)
   * Will be prefixed with + or - automatically
   */
  value: number;

  /**
   * Show only the icon without value
   * @default false
   */
  iconOnly?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  iconOnly: false,
});

/**
 * Container classes based on trend direction
 */
const containerClasses = computed(() => [
  'inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-semibold border',
  'transition-colors duration-200',
  props.direction === 'up'
    ? 'bg-tm-green-pale text-tm-green-dark border-tm-green'
    : 'bg-tm-danger-pale text-tm-danger border-tm-danger',
  'dark:bg-opacity-20',
]);

/**
 * Format value with + or - prefix
 */
const formattedValue = computed(() => {
  const sign = props.value >= 0 ? '+' : '';
  return `${sign}${props.value}`;
});

/**
 * Icon component based on direction
 */
const trendIcon = computed(() =>
  props.direction === 'up' ? TrendingUp : TrendingDown
);
</script>

<template>
  <span :class="containerClasses">
    <component :is="trendIcon" class="h-3 w-3" />
    <span v-if="!iconOnly">{{ formattedValue }}</span>
  </span>
</template>
