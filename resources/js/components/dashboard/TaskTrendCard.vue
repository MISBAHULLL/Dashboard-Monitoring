<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import ClientOnly from '@/components/ClientOnly.vue';
import VueApexCharts from 'vue3-apexcharts';
import type { ApexOptions } from 'apexcharts';

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
}

const props = withDefaults(defineProps<Props>(), {
    monthlyCategories: undefined,
    monthlyData: undefined,
    loading: false,
});

const activeMode = ref<'week' | 'month'>('week');

const activeData = computed(() =>
    activeMode.value === 'week' ? props.data : (props.monthlyData ?? props.data),
);

const activeCategories = computed(() =>
    activeMode.value === 'week' ? props.categories : (props.monthlyCategories ?? props.categories),
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

const xaxisTickAmount = computed(() => (activeMode.value === 'month' ? 6 : undefined));

const chartOptions = computed<ApexOptions>(() => ({
    chart: {
        type: 'line',
        fontFamily: 'Plus Jakarta Sans, sans-serif',
        toolbar: { show: false },
        zoom: { enabled: false },
        animations: {
            enabled: true,
            easing: 'linear',
            speed: 500,
            dynamicAnimation: { enabled: true, speed: 300 },
        },
    },
    colors: ['#3D5A99'],
    stroke: {
        curve: 'straight',
        width: 2,
    },
    grid: {
        show: true,
        borderColor: '#e5e7eb',
        strokeDashArray: 0,
        xaxis: { lines: { show: false } },
        yaxis: { lines: { show: true } },
        padding: { left: 4, right: 10, top: 4, bottom: 0 },
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
                colors: '#9AAAB8',
                fontWeight: 500,
            },
        },
        axisBorder: { show: false },
        axisTicks: { show: false },
        crosshairs: {
            show: true,
            stroke: { color: '#3D5A99', width: 1, dashArray: 3 },
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
                colors: '#9AAAB8',
                fontWeight: 500,
            },
            formatter: (val: number) => Math.round(val).toString(),
        },
    },
    dataLabels: { enabled: false },
    markers: { size: 0 },
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
            const accent = val === 0 ? '#9AAAB8' : '#1B3A6B';
            return `
                <div style="
                    background:#ffffff;
                    color:#111111;
                    border:2px solid #111111;
                    border-radius:6px;
                    padding:6px 12px;
                    font-family:'Plus Jakarta Sans',sans-serif;
                    font-size:12px;
                    font-weight:600;
                    box-shadow:3px 3px 0px #111111;
                    white-space:nowrap;
                    line-height:1.6;
                ">
                    <span style="color:#6b7280;font-weight:500;font-size:11px;">${label}</span><br/>
                    <span style="font-size:17px;font-weight:800;color:${accent};">${val}</span>
                    <span style="color:#6b7280;font-weight:400;font-size:11px;"> task</span>
                </div>`;
        },
    },
}));

const chartSeries = computed(() => [{ name: 'Tasks', data: activeData.value }]);

const chartKey = ref(0);
watch(activeMode, () => { chartKey.value++; });
</script>

<template>
    <!-- LOADING SKELETON -->
    <article
        v-if="loading"
        class="relative flex h-full flex-col overflow-hidden rounded-[18px] border-[2.5px] border-black bg-white p-5 dark:bg-card animate-pulse"
        style="box-shadow: 2px 4px 4px 4px rgba(0,0,0,0.08)"
    >
        <div class="flex items-center justify-between mb-4">
            <div class="h-7 w-48 rounded bg-muted/40"></div>
            <div class="flex gap-1">
                <div class="h-7 w-14 rounded bg-muted/30"></div>
                <div class="h-7 w-14 rounded bg-muted/30"></div>
            </div>
        </div>
        <div class="flex items-center gap-3 mb-4">
            <div class="h-10 w-16 rounded bg-muted/40"></div>
            <div class="h-6 w-14 rounded-md bg-muted/30"></div>
        </div>
        <div class="flex-1 rounded bg-muted/20"></div>
    </article>

    <!-- CONTENT -->
    <article
        v-else
        class="relative flex h-full flex-col overflow-hidden rounded-[18px] border-[2.5px] border-black bg-white px-5 pt-4 pb-2 dark:bg-card transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-[4px_8px_16px_2px_rgba(0,0,0,0.10)] cursor-default"
        style="box-shadow: 2px 4px 4px 4px rgba(0,0,0,0.08)"
        aria-label="Tren Task 7 Days"
    >
        <!-- Header -->
        <div class="flex items-center justify-between mb-1">
            <h2 class="whitespace-nowrap font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[20px] sm:text-[22px] lg:text-[24px] leading-tight text-black dark:text-slate-100">
                Tren Task 7 Days
            </h2>

            <!-- Week / Month toggle -->
            <div class="flex items-center gap-0.5">
                <button
                    :class="[
                        'rounded-[4px] border border-black px-3 py-1 text-[12px] font-medium transition-all duration-150',
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
                        'rounded-[4px] border border-black px-3 py-1 text-[12px] font-medium transition-all duration-150',
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
        <div class="flex items-center gap-3 mb-0">
            <span class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[36px] leading-none text-black dark:text-slate-100">
                {{ totalTasks }}
            </span>
            <span
                :class="[
                    'inline-flex items-center gap-0.5 rounded-md px-2 py-0.5 text-[13px] font-semibold',
                    isTrendUp ? 'bg-[#80bd51]/80 text-black font-bold' : 'bg-red-400/80 text-black font-bold',
                ]"
            >
                <svg class="h-3 w-3" :class="{ 'rotate-180': !isTrendUp }" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                    <path d="M6 2L9.5 5.5M6 2L2.5 5.5M6 2V10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                {{ isTrendUp ? '+' : '' }}{{ trendValue }}
            </span>
        </div>

        <!-- Chart — fills remaining height -->
        <div class="flex-1 min-h-0 -mx-2">
            <ClientOnly>
                <VueApexCharts
                    :key="chartKey"
                    type="line"
                    height="100%"
                    :options="chartOptions"
                    :series="chartSeries"
                />
                <template #fallback>
                    <div class="h-full w-full animate-pulse rounded-lg bg-slate-100" />
                </template>
            </ClientOnly>
        </div>
    </article>
</template>
