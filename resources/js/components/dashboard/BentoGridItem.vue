<script setup lang="ts">
import { computed } from 'vue';
import type { GridSpan } from '@/types/dashboard';

/**
 * BentoGridItem Component
 *
 * An individual grid item with configurable span for asymmetric layout.
 * This component wraps content and applies CSS Grid column span classes
 * to create the bento box-style asymmetric grid pattern.
 */

interface Props {
  /**
   * CSS Grid column span configuration for responsive breakpoints
   * Supports TailwindCSS col-span-* classes
   *
   * @example
   * // Small card (1 column)
   * span: { default: 'col-span-1' }
   *
   * // Medium card (2 columns on desktop)
   * span: { default: 'col-span-1', md: 'col-span-2' }
   *
   * // Large card (3 columns on large screens)
   * span: { default: 'col-span-1', md: 'col-span-2', lg: 'col-span-3' }
   *
   * // Full width card
   * span: { default: 'col-span-full' }
   *
   * @default { default: 'col-span-1' }
   */
  span?: GridSpan;
}

const props = withDefaults(defineProps<Props>(), {
  span: () => ({ default: 'col-span-1' }),
});

/**
 * Compute the span classes based on the span prop
 * Applies TailwindCSS col-span-* classes at each breakpoint
 */
const spanClasses = computed(() => {
  const { default: defaultSpan, md, lg } = props.span;

  const classes: string[] = [defaultSpan];

  // Add responsive breakpoint classes
  if (md !== undefined) {
    classes.push(`md:${md}`);
  }

  if (lg !== undefined) {
    classes.push(`lg:${lg}`);
  }

  return classes;
});
</script>

<template>
  <div :class="spanClasses">
    <!-- Default slot for content -->
    <slot />
  </div>
</template>
