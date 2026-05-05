<script setup lang="ts">
import { computed } from 'vue';
import type { GridColumns } from '@/types/dashboard';

/**
 * BentoGrid Container Component
 *
 * A CSS Grid container component that manages the Bento Grid layout with responsive behavior.
 * This component creates an asymmetric grid pattern inspired by Japanese bento boxes,
 * providing visual hierarchy and interest through varied card sizes.
 *
 * @see Requirements: 1.2, 1.3, 10.1, 10.2, 10.3
 */

interface Props {
  /**
   * Column configuration for responsive breakpoints
   * @default { default: 1, md: 2, lg: 4 }
   */
  columns?: GridColumns;

  /**
   * Gap between grid items (Tailwind gap class)
   * @default 'gap-4 md:gap-6'
   */
  gap?: string;
}

const props = withDefaults(defineProps<Props>(), {
  columns: () => ({ default: 1, md: 2, lg: 4 }),
  gap: 'gap-4 md:gap-6',
});

/**
 * Compute the grid column classes based on columns prop
 * Maps column numbers to Tailwind grid-cols-* classes at each breakpoint
 */
const gridClasses = computed(() => {
  const { default: defaultCols, md, lg, xl } = props.columns;

  const classes: string[] = [
    'grid',
    `grid-cols-${defaultCols}`,
  ];

  // Add responsive breakpoint classes
  if (md !== undefined) {
    classes.push(`md:grid-cols-${md}`);
  }

  if (lg !== undefined) {
    classes.push(`lg:grid-cols-${lg}`);
  }

  if (xl !== undefined) {
    classes.push(`xl:grid-cols-${xl}`);
  }

  return classes;
});
</script>

<template>
  <div
    :class="[...gridClasses, gap, 'auto-rows-min']"
  >
    <!-- Default slot for BentoGridItem components -->
    <slot />
  </div>
</template>
