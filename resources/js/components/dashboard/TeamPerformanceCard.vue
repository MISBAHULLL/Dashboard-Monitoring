<script setup lang="ts">
import { computed } from 'vue';
import type { TeamPerformance } from '@/types/dashboard';

/**
 * TeamPerformanceCard Component
 *
 * Displays team performance data in table format with neo-brutalism styling.
 * Part of the Bento Grid dashboard system.
 *
 * Features:
 * - Displays table with columns: Team, Total, Selesai, Overdue, Completion Rate
 * - Renders completion rate as badge with navy styling
 * - Shows empty state when no teams
 * - Inline loading skeleton with pulse animation
 * - Semantic table elements with proper headers
 * - Dark mode support
 * - Responsive design with horizontal scroll on mobile
 * - Automatically sorts by total_tasks descending and limits to 10 teams
 *
 * @see 
 */

interface Props {
  /**
   * Array of team performance data
   * Will be sorted by total_tasks descending and limited to 10 teams
   */
  teams: TeamPerformance[];

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
 * Sorted and limited teams for display
 * Requirement 6.5: Display up to 10 teams ordered by total_tasks descending
 */
const displayedTeams = computed(() => {
  return [...props.teams]
    .sort((a, b) => b.total_tasks - a.total_tasks)
    .slice(0, 10);
});
</script>

<template>
  <article
    class="relative rounded-xl border-[1.5px] border-border bg-card
           shadow-[2px_2px_0_0_rgba(0,0,0,0.08)] dark:shadow-[2px_2px_0_0_rgba(255,255,255,0.05)]
           transition-all duration-200 ease-out
           hover:shadow-[4px_4px_0_0_rgba(0,0,0,0.1)] dark:hover:shadow-[4px_4px_0_0_rgba(255,255,255,0.08)]"
  >
    <div class="p-6">
      <!-- Header -->
      <header class="mb-4">
        <h2 class="text-lg font-semibold text-primary">Ringkasan Performa Tim Product</h2>
        <p class="text-sm text-muted-foreground">
          Progress task per tim berdasarkan total, selesai, dan overdue.
        </p>
      </header>

      <!-- Loading skeleton with pulse animation -->
      <div v-if="loading" class="animate-pulse space-y-3">
        <!-- Table header skeleton -->
        <div class="h-10 w-full rounded bg-muted/30"></div>
        <!-- Table rows skeleton -->
        <div class="h-10 w-full rounded bg-muted/20"></div>
        <div class="h-10 w-full rounded bg-muted/20"></div>
        <div class="h-10 w-full rounded bg-muted/20"></div>
      </div>

      <!-- Content -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm" aria-label="Ringkasan performa tim product">
          <caption class="sr-only">
            Tabel performa tim menampilkan nama tim, total task, task selesai, task overdue, dan tingkat penyelesaian
          </caption>
          <thead class="bg-muted/50 text-muted-foreground border-b border-border">
            <tr>
              <th class="py-3 px-4 font-medium">Tim</th>
              <th class="py-3 px-4 font-medium text-center">Total</th>
              <th class="py-3 px-4 font-medium text-center">Selesai</th>
              <th class="py-3 px-4 font-medium text-center">Overdue</th>
              <th class="py-3 px-4 font-medium text-center">Completion Rate</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="team in displayedTeams"
              :key="team.id"
              class="border-b border-border last:border-0 hover:bg-muted/30 transition-colors"
            >
              <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-200">
                {{ team.name }}
              </td>
              <td class="py-3 px-4 text-center text-slate-700 dark:text-slate-300">
                {{ team.total_tasks }}
              </td>
              <td class="py-3 px-4 text-center text-tm-green font-semibold">
                {{ team.completed_tasks }}
              </td>
              <td class="py-3 px-4 text-center text-tm-danger font-semibold">
                {{ team.overdue_tasks }}
              </td>
              <td class="py-3 px-4 text-center">
                <span
                  class="inline-flex items-center rounded-full bg-tm-navy-pale px-2.5 py-0.5 text-xs font-semibold text-tm-navy dark:bg-tm-navy/20 dark:text-tm-navy-pale"
                >
                  {{ team.completion_rate }}%
                </span>
              </td>
            </tr>

            <!-- Empty state -->
            <tr v-if="teams.length === 0">
              <td colspan="5" class="py-8 text-center text-muted-foreground">
                Belum ada data performa tim.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </article>
</template>
