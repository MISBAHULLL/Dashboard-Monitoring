<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import ClientOnly from '@/components/ClientOnly.vue';
import VueApexCharts from 'vue3-apexcharts';
import type { ApexOptions } from 'apexcharts';
import { useAppearance } from '@/composables/useAppearance';

/** Reactive dark-mode flag — chart palette ikut berubah saat user toggle theme. */
const { resolvedAppearance } = useAppearance();
const isDark = computed(() => resolvedAppearance.value === 'dark');

/**
 * TaskTrendCard Component
 *
 * Neo-brutalist styled card for "Tren Task 7 Days".
 * Chart: monotoneCubic area — follows real ups/downs faithfully,
 * no over-smoothing, no dots, gradient fill navy→green.
 */

interface Props {
    categories: string[];
    data: number[];
    monthlyCategories?: string[];
    monthlyData?: number[];
    loading?: boolean;
    title?: string;
    periodLabel?: string;
    showModeToggle?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    monthlyCategories: undefined,
    monthlyData: undefined,
    loading: false,
    title: 'Tren Task 7 Days',
    periodLabel: undefined,
    showModeToggle: true,
});

const activeMode = ref<'week' | 'month'>('week');

const activeData = computed(() =>
    !props.showModeToggle || activeMode.value === 'week' ? props.data : (props.monthlyData ?? props.data),
);

const activeCategories = computed(() =>
    !props.showModeToggle || activeMode.value === 'week' ? props.categories : (props.monthlyCategories ?? props.categories),
);

const totalTasks = computed(() =>
    activeData.value.reduce((sum, v) => sum + v, 0),
);

/** Compare second half vs first half of the period */
const trendValue = computed(() => {
    const d = activeData.value;
    if (d.length < 2) return 0;
    const mid = Math.floor(d.length / 2);
    return d.slice(mid).reduce((s, v) => s + v, 0) - d.slice(0, mid).reduce((s, v) => s + v, 0);
});

const isTrendUp = computed(() => trendValue.value >= 0);

const maxValue = computed(() => Math.max(...activeData.value, 1));
const peakLabel = computed(() => {
    const peak = Math.max(...activeData.value, 0);
    const index = activeData.value.indexOf(peak);

    return {
        value: peak,
        label: index >= 0 ? activeCategories.value[index] : '-',
    };
});

const xaxisTickAmount = computed(() => (activeCategories.value.length > 12 ? 6 : undefined));

const chartOptions = computed<ApexOptions>(() => ({
    chart: {
        type: 'area',
        fontFamily: 'Plus Jakarta Sans, sans-serif',
        toolbar: { show: false },
        zoom: { enabled: false },
        sparkline: { enabled: false },
        animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 650,
            dynamicAnimation: { enabled: true, speed: 350 },
        },
    },
    colors: [isDark.value ? '#7AA2F7' : '#1B3A6B'],
    stroke: {
        curve: 'smooth',
        width: 3,
        lineCap: 'round',
    },
    fill: {
        type: 'gradient',
        gradient: {
            shade: isDark.value ? 'dark' : 'light',
            type: 'vertical',
            shadeIntensity: 0.25,
            opacityFrom: isDark.value ? 0.38 : 0.32,
            opacityTo: 0.04,
            stops: [0, 72, 100],
        },
    },
    grid: {
        show: true,
        borderColor: isDark.value ? 'rgba(148,163,184,0.16)' : '#e4eaf1',
        strokeDashArray: 4,
        xaxis: { lines: { show: false } },
        yaxis: { lines: { show: true } },
        padding: { left: 6, right: 14, top: 8, bottom: 0 },
    },
    xaxis: {
        categories: activeCategories.value,
        tickAmount: xaxisTickAmount.value,
        labels: {
            rotate: 0,
            hideOverlappingLabels: true,
            style: {
                fontSize: '11px',
                fontFamily: 'Plus Jakarta Sans, sans-serif',
                colors: isDark.value ? '#94A3B8' : '#9AAAB8',
                fontWeight: 500,
            },
        },
        axisBorder: { show: false },
        axisTicks: { show: false },
        crosshairs: {
            show: true,
            stroke: {
                color: isDark.value ? '#7AA2F7' : '#3D5A99',
                width: 1,
                dashArray: 3,
            },
        },
        tooltip: { enabled: false },
    },
    yaxis: {
        min: 0,
        tickAmount: Math.min(maxValue.value, 5),
        forceNiceScale: true,
        labels: {
            offsetX: -4,
            style: {
                fontSize: '11px',
                fontFamily: 'Plus Jakarta Sans, sans-serif',
                colors: isDark.value ? '#94A3B8' : '#9AAAB8',
                fontWeight: 500,
            },
            formatter: (val: number) => Math.round(val).toString(),
        },
    },
    dataLabels: { enabled: false },
    markers: {
        size: 0,
        strokeWidth: 3,
        strokeColors: isDark.value ? '#111c2e' : '#ffffff',
        hover: {
            size: 6,
            sizeOffset: 2,
        },
    },
    states: {
        hover: {
            filter: { type: 'none' },
        },
        active: {
            filter: { type: 'none' },
        },
    },
    tooltip: {
        enabled: true,
        shared: true,
        intersect: false,
        custom: ({ series, seriesIndex, dataPointIndex, w }: any) => {
            const val: number = series[seriesIndex][dataPointIndex];
            const label: string =
                w.globals.categoryLabels?.[dataPointIndex] ??
                w.globals.labels?.[dataPointIndex] ??
                '';
            // Tooltip palette mengikuti theme — di dark pakai surface gelap dengan border navy pale.
            const palette = isDark.value
                ? {
                      bg: '#111c2e',
                      text: '#E8EEF8',
                      border: '#48668f',
                      shadow: '0 4px 12px rgba(0,0,0,0.5)',
                      labelText: '#94A3B8',
                      accent: val === 0 ? '#94A3B8' : '#7AA2F7',
                      suffixText: '#94A3B8',
                  }
                : {
                      bg: '#ffffff',
                      text: '#111111',
                      border: '#111111',
                      shadow: '3px 3px 0px #111111',
                      labelText: '#6b7280',
                      accent: val === 0 ? '#9AAAB8' : '#1B3A6B',
                      suffixText: '#6b7280',
                  };

            return `
                <div style="
                    background:${palette.bg};
                    color:${palette.text};
                    border:2px solid ${palette.border};
                    border-radius:6px;
                    padding:6px 12px;
                    font-family:'Plus Jakarta Sans',sans-serif;
                    font-size:12px;
                    font-weight:600;
                    box-shadow:${palette.shadow};
                    white-space:nowrap;
                    line-height:1.6;
                ">
                    <span style="color:${palette.labelText};font-weight:500;font-size:11px;">${label}</span><br/>
                    <span style="font-size:17px;font-weight:800;color:${palette.accent};">${val}</span>
                    <span style="color:${palette.suffixText};font-weight:400;font-size:11px;"> task</span>
                </div>`;
        },
    },
}));

const chartSeries = computed(() => [{ name: 'Task Dibuat', data: activeData.value }]);

const chartKey = ref(0);
watch([activeMode, isDark, activeData, activeCategories], () => {
    chartKey.value++;
});
</script>

<template>
    <!-- LOADING SKELETON -->
    <article
        v-if="loading"
        class="task-trend-card relative flex h-full flex-col overflow-hidden rounded-[18px] border-[2.5px] border-black bg-white p-5 animate-pulse dark:border-slate-700/80 dark:bg-[#111c2e]"
    >
        <div class="flex items-center justify-between mb-4">
            <div class="h-7 w-48 rounded bg-muted/40 dark:bg-slate-700/40"></div>
            <div class="flex gap-1">
                <div class="h-7 w-14 rounded bg-muted/30 dark:bg-slate-700/30"></div>
                <div class="h-7 w-14 rounded bg-muted/30 dark:bg-slate-700/30"></div>
            </div>
        </div>
        <div class="flex items-center gap-3 mb-4">
            <div class="h-10 w-16 rounded bg-muted/40 dark:bg-slate-700/40"></div>
            <div class="h-6 w-14 rounded-md bg-muted/30 dark:bg-slate-700/30"></div>
        </div>
        <div class="flex-1 rounded bg-muted/20 dark:bg-slate-700/20"></div>
    </article>

    <!-- CONTENT -->
    <article
        v-else
        class="task-trend-card relative flex h-full flex-col overflow-hidden rounded-[18px] border-[2.5px] border-black bg-white px-5 pt-4 pb-2 transition-all duration-300 ease-out hover:-translate-y-1 cursor-default dark:border-slate-700/80 dark:bg-[#111c2e]"
        :aria-label="periodLabel ? `${title} ${periodLabel}` : title"
    >
        <!-- Header -->
        <div class="mb-2 flex items-start justify-between gap-4">
            <div class="min-w-0">
                <h2 class="truncate font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[20px] sm:text-[22px] lg:text-[24px] leading-tight text-black dark:text-slate-100">
                    {{ title }}
                </h2>
                <p v-if="periodLabel" class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">
                    {{ periodLabel }}
                </p>
            </div>

            <div
                v-if="!showModeToggle"
                class="hidden rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-right sm:block dark:border-slate-700 dark:bg-slate-900/60"
                aria-label="Puncak task pada periode ini"
            >
                <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">
                    Puncak
                </p>
                <p class="text-sm font-extrabold leading-tight text-[#1B3A6B] dark:text-[#7AA2F7]">
                    {{ peakLabel.value }}
                    <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">task</span>
                </p>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">
                    {{ peakLabel.label }}
                </p>
            </div>

            <!-- Week / Month toggle -->
            <div v-if="showModeToggle" class="flex items-center gap-0.5">
                <button
                    :class="[
                        'rounded-[4px] border border-black px-3 py-1 text-[12px] font-medium transition-all duration-150 dark:border-slate-600',
                        activeMode === 'week'
                            ? 'bg-white text-black dark:bg-slate-200 dark:text-black'
                            : 'bg-[#d9d9d9] text-black hover:bg-[#c4c4c4] dark:bg-slate-600 dark:text-slate-200 dark:hover:bg-slate-500',
                    ]"
                    @click="activeMode = 'week'"
                >
                    Week
                </button>
                <button
                    :class="[
                        'rounded-[4px] border border-black px-3 py-1 text-[12px] font-medium transition-all duration-150 dark:border-slate-600',
                        activeMode === 'month'
                            ? 'bg-white text-black dark:bg-slate-200 dark:text-black'
                            : 'bg-[#d9d9d9] text-black hover:bg-[#c4c4c4] dark:bg-slate-600 dark:text-slate-200 dark:hover:bg-slate-500',
                    ]"
                    @click="activeMode = 'month'"
                >
                    Month
                </button>
            </div>
        </div>

        <!-- Total + Trend badge -->
        <div class="mb-1 flex items-center gap-3">
            <span class="font-['Plus_Jakarta_Sans',sans-serif] font-extrabold text-[36px] leading-none text-black dark:text-slate-100">
                {{ totalTasks }}
            </span>
            <span
                :class="[
                    'inline-flex items-center gap-1 rounded-full border px-2 py-1 text-[12px] font-extrabold shadow-sm',
                    isTrendUp
                        ? 'border-[#80bd51] bg-[#80bd51]/20 text-[#1f5f2a] dark:text-[#9de47e]'
                        : 'border-red-300 bg-red-100 text-red-700 dark:border-red-500/50 dark:bg-red-500/15 dark:text-red-300',
                ]"
            >
                <svg class="h-3 w-3" :class="{ 'rotate-180': !isTrendUp }" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                    <path d="M6 2L9.5 5.5M6 2L2.5 5.5M6 2V10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                {{ isTrendUp ? '+' : '' }}{{ trendValue }}
            </span>
        </div>

        <!-- Chart — fills remaining height -->
        <div class="min-h-0 flex-1 -mx-2">
            <ClientOnly>
                <VueApexCharts
                    :key="chartKey"
                    type="area"
                    height="100%"
                    :options="chartOptions"
                    :series="chartSeries"
                />
                <template #fallback>
                    <div class="h-full w-full animate-pulse rounded-lg bg-slate-100 dark:bg-slate-800/40" />
                </template>
            </ClientOnly>
        </div>
    </article>
</template>

<style scoped>
/* Light: shadow hitam tegas khas neo-brutalism. */
.task-trend-card {
    box-shadow: 2px 4px 4px 4px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.task-trend-card:hover {
    box-shadow: 4px 8px 16px 2px rgba(0, 0, 0, 0.1);
}

/* Dark: shadow hitam pekat + halo putih tipis sebagai pengganti border yang
   jadi tidak kelihatan. Memberi dimensi tanpa terlihat berlebihan. */
:global(.dark) .task-trend-card {
    box-shadow:
        0 0 0 1px rgba(148, 163, 184, 0.08),
        0 14px 32px rgba(0, 0, 0, 0.42);
}

:global(.dark) .task-trend-card:hover {
    box-shadow:
        0 0 0 1px rgba(122, 162, 247, 0.28),
        0 18px 40px rgba(0, 0, 0, 0.5);
}
</style>
