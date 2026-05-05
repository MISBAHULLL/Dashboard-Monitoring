<script setup lang="ts">
import { AlertTriangle, RefreshCw } from 'lucide-vue-next';

/**
 * ErrorState Component
 *
 * Displays error message with retry action for dashboard components.
 * Part of the Bento Grid dashboard system.
 *
 * @see Requirements: 17.1-17.6
 */

interface Props {
  /**
   * Error message to display (e.g., "Gagal memuat data task overdue")
   */
  message: string;

  /**
   * Retry callback function - when provided, shows "Coba Lagi" button
   */
  onRetry?: () => void;
}

defineProps<Props>();
</script>

<template>
  <div
    class="flex flex-col items-center justify-center py-8 text-center min-h-[200px]"
    role="alert"
    aria-live="polite"
  >
    <!-- Error icon with neo-brutalism styling -->
    <div
      class="rounded-xl border-[1.5px] border-tm-danger bg-tm-danger-pale p-4 mb-4
             shadow-[2px_2px_0_0_rgba(232,69,69,0.2)] dark:shadow-[2px_2px_0_0_rgba(232,69,69,0.3)]"
    >
      <AlertTriangle class="h-8 w-8 text-tm-danger" />
    </div>

    <!-- Error message -->
    <p class="text-sm text-muted-foreground mb-4 max-w-md">
      {{ message }}
    </p>

    <!-- Retry button (conditional) -->
    <button
      v-if="onRetry"
      @click="onRetry"
      class="inline-flex items-center gap-2 rounded-lg border-[1.5px] border-tm-navy bg-tm-navy-pale px-4 py-2
             text-sm font-medium text-tm-navy
             hover:bg-tm-navy/10 dark:hover:bg-tm-navy/20
             transition-colors duration-200
             focus:outline-none focus:ring-2 focus:ring-tm-navy/50 focus:ring-offset-2"
    >
      <RefreshCw class="h-4 w-4" />
      Coba Lagi
    </button>
  </div>
</template>
