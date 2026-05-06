<script setup lang="ts">
/**
 * SkipLink Component
 *
 * Provides a skip navigation link for keyboard users to jump directly to main content.
 * This is a critical accessibility feature for WCAG 2.1 Level AA compliance.
 *
 * The link is hidden by default and becomes visible when focused.
 *
 * @see Requirements: 12.5
 */

interface Props {
  /**
   * Target element ID to skip to (e.g., "main-content")
   */
  targetId: string;

  /**
   * Link text to display
   * @default "Skip to main content"
   */
  label?: string;
}

const props = withDefaults(defineProps<Props>(), {
  label: 'Skip to main content',
});
</script>

<template>
  <a
    :href="`#${targetId}`"
    class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50
           focus:rounded-lg focus:border-[1.5px] focus:border-tm-navy focus:bg-tm-navy-pale
           focus:px-4 focus:py-2 focus:text-tm-navy focus:font-medium
           focus:shadow-[2px_2px_0_0_rgba(0,0,0,0.1)]
           focus:outline-none focus:ring-2 focus:ring-tm-navy/50 focus:ring-offset-2
           transition-all duration-200"
  >
    {{ label }}
  </a>
</template>
