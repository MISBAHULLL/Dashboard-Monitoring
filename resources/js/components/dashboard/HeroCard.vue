<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import ActionTooltip from '@/components/ActionTooltip.vue';
import { index as tasksIndex } from '@/routes/tasks';

/**
 * HeroCard Component
 *
 * Greeting card with dark navy background.
 */

interface Props {
  userName: string;
  pendingCount: number;
  overdueCount: number;
  totalTasks: number;
  totalTasksWithTrashed?: number;
  trashedTasks?: number;
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
});

/**
 * CHART_SCALE — pengali universal untuk semua bar.
 * Ubah angka ini untuk membuat semua bar lebih tinggi/pendek secara proporsional.
 * Contoh: 1.0 = normal, 1.5 = 50% lebih tinggi, 2.0 = 2x lebih tinggi
 */
const CHART_SCALE = 1.4;

/**
 * Fixed decorative bar heights dalam pixel — pola Figma.
 * Rasio antar bar tetap sama, hanya CHART_SCALE yang mengatur tinggi universal.
 */
const BASE_HEIGHTS = [70, 45, 110, 80, 140, 175];
const barHeights = BASE_HEIGHTS.map(h => Math.round(h * CHART_SCALE));

/**
 * Bar colors — dark navy → teal → bright emerald (kiri ke kanan).
 * Mengikuti warna di Figma persis.
 */
const getBarColor = (index: number): string => {
  const colors = [
    'bg-[#1e2d45]',       // bar 1 — navy gelap
    'bg-[#2a3a52]',       // bar 2 — navy medium
    'bg-[#2a3a52]',       // bar 3 — navy medium (sama dengan bar 2)
    'bg-[#1a5c4a]',       // bar 4 — teal gelap
    'bg-[#1a7a5e]',       // bar 5 — teal medium
    'bg-[#34d399]',       // bar 6 — emerald terang
  ];
  return colors[index] ?? 'bg-[#34d399]';
};

const greeting = computed(() => {
  const hour = new Date().getHours();
  if (hour < 12) return 'Good Morning,';
  if (hour < 17) return 'Good Afternoon,';
  return 'Good Evening,';
});
</script>

<template>
  <article
    class="relative flex h-full flex-col overflow-hidden rounded-[18px] border-2 border-black bg-[#0f172a] p-4 shadow-[4px_6px_5px_1px_rgba(0,0,0,0.6)] transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-[6px_10px_8px_2px_rgba(0,0,0,0.7)] hover:border-emerald-500/40 dark:border-slate-700/80 dark:bg-[#0f1a2d] dark:shadow-[0_16px_36px_rgba(0,0,0,0.45),0_0_0_1px_rgba(148,163,184,0.08)] dark:hover:border-emerald-400/45 dark:hover:shadow-[0_20px_44px_rgba(0,0,0,0.55),0_0_0_1px_rgba(52,211,153,0.18)]"
  >
    <!-- Loading skeleton -->
    <div v-if="loading" class="flex h-full flex-col justify-between animate-pulse">
      <div class="space-y-2">
        <div class="h-4 w-28 rounded bg-white/20"></div>
        <div class="h-4 w-32 rounded bg-white/15"></div>
        <div class="h-3 w-48 rounded bg-white/10 mt-2"></div>
      </div>
      <div class="flex items-end justify-between gap-3">
        <div>
          <div class="h-2 w-16 rounded bg-white/15 mb-1.5"></div>
          <div class="h-6 w-10 rounded bg-white/20"></div>
        </div>
        <div class="flex items-end gap-1 h-20">
          <div v-for="i in 6" :key="i" class="w-5 bg-white/20 rounded" :style="{ height: `${25 + i * 10}%` }"></div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <template v-else>
      <!-- Top: Greeting -->
      <div class="relative z-10">
        <h1 class="font-['Solway',serif] text-[60px] leading-tight font-normal text-white">
          {{ greeting }}
        </h1>

        <!-- User name (gradient) + hand emoji -->
        <div class="mt-0.5 flex items-center gap-1">
          <span
            class="font-['Solway',serif] text-[60px] leading-tight font-normal bg-clip-text text-transparent"
            style="background-image: linear-gradient(96deg, #ffffff 25%, #03a472 105%)"
          >
            {{ userName }}
          </span>
          <span class="text-[60px] leading-none">👋</span>
        </div>

        <!-- Subtitle with inline badges -->
        <p class="mt-5 text-[18px] text-[#a4a3a3] font-['Solway',serif] leading-snug dark:text-slate-300">
          kamu mempunyai
          <ActionTooltip label="Lihat task open yang menunggu dikerjakan" side="top">
            <Link
              :href="tasksIndex({ query: { status: 'open' } }).url"
              class="inline-flex items-center rounded-[3px] px-1 py-[1px] text-[18px] font-normal bg-[rgba(199,153,36,0.28)] text-[#eab223] mx-0.5 transition-colors hover:bg-amber-400/30 hover:text-amber-200 dark:bg-amber-400/15 dark:text-amber-300"
            >
              {{ pendingCount }} Pending
            </Link>
          </ActionTooltip>
          dan
          <br/>
          <ActionTooltip label="Lihat task overdue" side="bottom">
            <Link
              :href="tasksIndex({ query: { status: 'overdue' } }).url"
              class="inline-flex items-center rounded-[3px] px-1 py-[1px] text-[18px] font-normal bg-[rgba(224,29,29,0.38)] text-[#e01d1d] ml-0 mt-1 transition-colors hover:bg-red-500/30 hover:text-red-200 dark:bg-red-500/20 dark:text-red-300"
            >
              {{ overdueCount }} Overdue
            </Link>
          </ActionTooltip>
        </p>
      </div>

      <!-- Bottom: Total tasks + bar chart -->
      <div class="relative z-10 mt-auto flex items-end justify-between gap-3 pt-3">
        <!-- Left: TOTAL TASKS -->
        <div class="shrink-0">
          <p class="text-[20px] uppercase tracking-[0.15em] text-[#a4a3a3] font-['Solway',serif] dark:text-slate-300">
            TASK AKTIF
          </p>
          <ActionTooltip label="Lihat semua task aktif" side="top">
            <Link
              :href="tasksIndex.url()"
              class="mt-0.5 block text-[50px] leading-none font-normal text-white font-['Solway',serif] transition-colors hover:text-emerald-200"
            >
              {{ totalTasks }}
            </Link>
          </ActionTooltip>
          <p
            v-if="totalTasksWithTrashed !== undefined || trashedTasks !== undefined"
            class="mt-1 text-[12px] font-semibold tracking-wide text-slate-400 dark:text-slate-400"
          >
            Total {{ totalTasksWithTrashed ?? totalTasks }} · Terhapus {{ trashedTasks ?? 0 }}
          </p>
        </div>

        <!-- Right: Bar chart — pola Figma, tinggi absolut bukan persentase -->
        <div class="flex items-end gap-[7px] flex-1 justify-end">
          <div
            v-for="(height, index) in barHeights"
            :key="index"
            class="w-[30px] rounded-[8px] shrink-0"
            :class="getBarColor(index)"
            :style="{ height: `${height}px` }"
          ></div>
        </div>
      </div>
    </template>
  </article>
</template>
