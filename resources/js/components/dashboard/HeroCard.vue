<script setup lang="ts">
import { computed } from 'vue';

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
  chartData?: number[];
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  chartData: () => [3, 5, 8, 6, 10, 12],
  loading: false,
});

/**
 * Resolve chart data with fallback pattern so bars are always visible.
 */
const resolvedChartData = computed(() => {
  const data = props.chartData;
  if (!data || data.length === 0) return [3, 5, 8, 6, 10, 12];
  const hasNonZero = data.some((v) => v > 0);
  if (!hasNonZero) return [3, 5, 8, 6, 10, 12];
  return data.slice(0, 6);
});

/**
 * Bar heights as percentage with minimum floor.
 */
const maxValue = computed(() => Math.max(...resolvedChartData.value, 1));
const barHeights = computed(() =>
  resolvedChartData.value.map((value) => {
    const pct = (value / maxValue.value) * 100;
    return Math.max(pct, 20);
  })
);

/**
 * Bar colors — dark slate on left → bright emerald on right.
 */
const getBarColor = (index: number): string => {
  const total = resolvedChartData.value.length;
  if (index === total - 1) return 'bg-emerald-400';
  if (index === total - 2) return 'bg-emerald-600/75';
  if (index === total - 3) return 'bg-teal-800/65';
  if (index === total - 4) return 'bg-slate-600/60';
  if (index === total - 5) return 'bg-slate-700/70';
  return 'bg-slate-800/85';
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
    class="relative flex h-full flex-col overflow-hidden rounded-[18px] border-2 border-black bg-[#0f172a] p-4 shadow-[4px_6px_0_3px_rgba(0,0,0,0.6)]"
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
        <p class="mt-5 text-[18px] text-[#a4a3a3] font-['Solway',serif] leading-snug">
          kamu mempunyai
          <span
            class="inline-flex items-center rounded-[3px] px-1 py-[1px] text-[18px] font-normal bg-[rgba(199,153,36,0.28)] text-[#eab223] mx-0.5"
          >
            {{ pendingCount }} Pending
          </span>
          dan
          <br/>
          <span
            class="inline-flex items-center rounded-[3px] px-1 py-[1px] text-[18px] font-normal bg-[rgba(224,29,29,0.38)] text-[#e01d1d] ml-0 mt-1"
          >
            {{ overdueCount }} Overdue
          </span>
        </p>
      </div>

      <!-- Bottom: Total tasks + bar chart -->
      <div class="relative z-10 mt-auto flex items-end justify-between gap-3 pt-3">
        <!-- Left: TOTAL TASKS -->
        <div class="shrink-0">
          <p class="text-[20px] uppercase tracking-[0.15em] text-[#a4a3a3] font-['Solway',serif]">
            TOTAL TASKS
          </p>
          <p class="mt-0.5 text-[50px] leading-none font-normal text-white font-['Solway',serif]">
            {{ totalTasks }}
          </p>
        </div>

        <!-- Right: Bar chart — LARGE bars (wide + tall) -->
        <div class="flex items-end gap-1.5 h-[220px] flex-1 justify-end">
          <div
            v-for="(height, index) in barHeights"
            :key="index"
            class="w-[22px] rounded-[5px] transition-all duration-500 ease-out"
            :class="getBarColor(index)"
            :style="{ height: `${height}%` }"
          ></div>
        </div>
      </div>
    </template>
  </article>
</template>
