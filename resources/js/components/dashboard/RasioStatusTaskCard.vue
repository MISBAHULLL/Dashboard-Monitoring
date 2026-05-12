<script setup lang="ts">
import { computed, ref } from 'vue';

/**
 * RasioStatusTaskCard Component
 *
 * Neo-brutalist donut chart card that visualises task distribution by status.
 *
 * Layout dibuat kompak agar muat di slot `row-span-2` (±252px) dan tetap
 * sejajar dengan TaskDueSoonCard di Bento Grid dashboard.
 *
 * Props menerima urutan count status: [open, in_progress, revision, completed]
 * — sama dengan struktur `chart_donut` di DashboardController.
 */

interface Props {
    /** Counts in the order: [open, in_progress, revision, completed] */
    series: number[];
    /** Optional override for title */
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Rasio Status Task',
});

// ─── Chart geometry
const RADIUS = 90;
const CX = 110;
const CY = 110;
const STROKE_WIDTH = 20;
const CIRCUMFERENCE = 2 * Math.PI * RADIUS;

// ─── Colour palette
const COLORS = {
    open: '#FF6B00',
    inprogress: '#3B7CF4',
    revisi: '#EF1C1C',
    completed: '#22C55E',
} as const;

// ─── Human-readable labels per status (dipakai di tooltip)
const LABELS: Record<string, string> = {
    open: 'Open',
    inprogress: 'In Progress',
    revisi: 'Revisi',
    completed: 'Completed',
};

/**
 * Urutan segmen (searah jarum jam, mulai dari jam 12):
 * completed → open → revisi → in progress.
 */
const segments = computed(() => {
    const [open = 0, inProgress = 0, revision = 0, completed = 0] = props.series;
    return [
        { key: 'completed', color: COLORS.completed, value: completed },
        { key: 'open', color: COLORS.open, value: open },
        { key: 'revisi', color: COLORS.revisi, value: revision },
        { key: 'inprogress', color: COLORS.inprogress, value: inProgress },
    ];
});

const total = computed(() => segments.value.reduce((sum, s) => sum + s.value, 0));

/**
 * Pre-compute dasharray + dashoffset untuk tiap segmen.
 */
const renderedSegments = computed(() => {
    const t = total.value;
    if (t === 0) return [];
    let cumulative = 0;
    return segments.value
        .filter((s) => s.value > 0)
        .map((seg) => {
            const arc = (seg.value / t) * CIRCUMFERENCE;
            const dashArray = `${arc} ${CIRCUMFERENCE - arc}`;
            const dashOffset = CIRCUMFERENCE * (1 - cumulative / t);
            cumulative += seg.value;
            return {
                ...seg,
                dashArray,
                dashOffset,
                label: LABELS[seg.key] ?? seg.key,
                percentage: Math.round((seg.value / t) * 100),
            };
        });
});

// ─── Legend (2x2) dengan jumlah + persentase
const legendItems = computed(() => {
    const [open = 0, inProgress = 0, revision = 0, completed = 0] = props.series;
    const t = total.value || 1;
    return [
        { key: 'open', label: 'Open', color: COLORS.open, value: open, percentage: Math.round((open / t) * 100) },
        { key: 'inprogress', label: 'In Progress', color: COLORS.inprogress, value: inProgress, percentage: Math.round((inProgress / t) * 100) },
        { key: 'revisi', label: 'Revisi', color: COLORS.revisi, value: revision, percentage: Math.round((revision / t) * 100) },
        { key: 'completed', label: 'Completed', color: COLORS.completed, value: completed, percentage: Math.round((completed / t) * 100) },
    ];
});

// ─── Tooltip state
interface TooltipState {
    visible: boolean;
    x: number;
    y: number;
    label: string;
    value: number;
    percentage: number;
    color: string;
}

const tooltip = ref<TooltipState>({
    visible: false,
    x: 0,
    y: 0,
    label: '',
    value: 0,
    percentage: 0,
    color: '#000',
});

const activeKey = ref<string | null>(null);
const cardRef = ref<HTMLElement | null>(null);

function onSegmentMove(event: MouseEvent, seg: any) {
    if (!cardRef.value) return;
    const rect = cardRef.value.getBoundingClientRect();
    tooltip.value = {
        visible: true,
        x: event.clientX - rect.left,
        y: event.clientY - rect.top,
        label: seg.label,
        value: seg.value,
        percentage: seg.percentage,
        color: seg.color,
    };
    activeKey.value = seg.key;
}

function onSegmentLeave() {
    tooltip.value.visible = false;
    activeKey.value = null;
}
</script>

<template>
    <article
        ref="cardRef"
        class="raslo-card relative flex h-full w-full min-h-0 flex-col items-center overflow-hidden rounded-[22px] border-[2.5px] border-black bg-[#FCFCFC] px-3 pt-2.5 pb-2.5 dark:bg-card"
        :aria-label="`${title}: ${total} total tasks`"
    >
        <!-- Title (kompak) -->
        <h2
            class="mb-1 text-center font-['Plus_Jakarta_Sans',sans-serif] text-[15px] font-extrabold leading-tight tracking-[-0.2px] text-[#0b2a6b] sm:text-[16px] dark:text-tm-navy-pale"
        >
            {{ title }}
        </h2>

        <!-- Donut (kecil, flex-shrink agar tidak mendorong legend keluar) -->
        <div class="flex min-h-0 flex-1 items-center justify-center">
            <svg
                viewBox="0 0 220 220"
                class="block h-full w-auto max-h-[110px]"
                preserveAspectRatio="xMidYMid meet"
                :aria-label="`${title} donut chart`"
                role="img"
            >
                <!-- Empty-state ring -->
                <circle
                    v-if="total === 0"
                    :cx="CX"
                    :cy="CY"
                    :r="RADIUS"
                    fill="none"
                    stroke="#E5E7EB"
                    :stroke-width="STROKE_WIDTH"
                />

                <!-- Segments -->
                <circle
                    v-for="seg in renderedSegments"
                    :key="seg.key"
                    :cx="CX"
                    :cy="CY"
                    :r="RADIUS"
                    fill="none"
                    :stroke="seg.color"
                    :stroke-width="activeKey === seg.key ? STROKE_WIDTH + 4 : STROKE_WIDTH"
                    :stroke-dasharray="seg.dashArray"
                    :stroke-dashoffset="seg.dashOffset"
                    :transform="`rotate(-90 ${CX} ${CY})`"
                    stroke-linecap="butt"
                    class="cursor-pointer transition-[stroke-width] duration-150"
                    @mousemove="(e) => onSegmentMove(e, seg)"
                    @mouseleave="onSegmentLeave"
                >
                    <title>{{ seg.label }}: {{ seg.value }} ({{ seg.percentage }}%)</title>
                </circle>

                <!-- Centre: total -->
                <text
                    :x="CX"
                    :y="CY - 4"
                    text-anchor="middle"
                    dominant-baseline="auto"
                    font-family="'Plus Jakarta Sans', sans-serif"
                    font-weight="800"
                    font-size="34"
                    class="pointer-events-none fill-[#111111] dark:fill-white"
                >
                    {{ total }}
                </text>
                <text
                    :x="CX"
                    :y="CY + 24"
                    text-anchor="middle"
                    dominant-baseline="auto"
                    font-family="'Plus Jakarta Sans', sans-serif"
                    font-weight="700"
                    font-size="18"
                    class="pointer-events-none fill-[#111111] dark:fill-white"
                >
                    Total
                </text>
            </svg>
        </div>

        <!-- Separator -->
        <div class="mb-1.5 h-px w-full bg-black/15 dark:bg-white/15" />

        <!-- Legend: 2x2 grid, single-line per item -->
        <div class="grid w-full flex-shrink-0 grid-cols-2 gap-x-2 gap-y-1">
            <div
                v-for="item in legendItems"
                :key="item.key"
                class="flex items-center gap-1.5"
            >
                <span
                    class="h-2 w-2 flex-shrink-0 rounded-full"
                    :style="{ backgroundColor: item.color }"
                    aria-hidden="true"
                />
                <span
                    class="truncate font-['Plus_Jakarta_Sans',sans-serif] text-[11px] font-semibold text-[#555] dark:text-slate-300"
                >
                    {{ item.label }}
                </span>
                <span
                    class="ml-auto font-['Plus_Jakarta_Sans',sans-serif] text-[11px] font-extrabold tabular-nums text-[#111] dark:text-white"
                >
                    {{ item.value }}
                    <span class="text-[10px] font-semibold text-[#888] dark:text-slate-400">
                        · {{ item.percentage }}%
                    </span>
                </span>
            </div>
        </div>

        <!-- Tooltip mengikuti cursor -->
        <Transition name="tooltip-fade">
            <div
                v-if="tooltip.visible"
                class="pointer-events-none absolute z-20 -translate-x-1/2 -translate-y-full rounded-lg border-2 border-black bg-white px-2 py-1 text-[11px] shadow-[2px_2px_0_0_rgba(0,0,0,0.8)] dark:bg-card dark:text-white"
                :style="{ left: `${tooltip.x}px`, top: `${tooltip.y - 8}px` }"
                role="tooltip"
            >
                <div class="flex items-center gap-1.5">
                    <span
                        class="h-2 w-2 flex-shrink-0 rounded-full"
                        :style="{ backgroundColor: tooltip.color }"
                    />
                    <span class="font-['Plus_Jakarta_Sans',sans-serif] font-bold text-[#111] dark:text-white">
                        {{ tooltip.label }}
                    </span>
                </div>
                <div class="mt-0.5 font-['Plus_Jakarta_Sans',sans-serif] text-[#333] dark:text-slate-200">
                    <span class="font-bold">{{ tooltip.value }}</span>
                    <span class="text-[#777] dark:text-slate-400"> · {{ tooltip.percentage }}%</span>
                </div>
            </div>
        </Transition>
    </article>
</template>

<style scoped>
.raslo-card {
    box-shadow: 1px 3px 5px 0 rgba(0, 0, 0, 0.50);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.raslo-card:hover {
    transform: translateY(-3px) scale(1.01);
    box-shadow: 2px 6px 12px 2px rgba(24, 24, 24, 0.35);
}

:global(.dark) .raslo-card {
    box-shadow: 4px 6px 4px 1px #181818ff;
}

:global(.dark) .raslo-card:hover {
    transform: translateY(-3px) scale(1.01);
    box-shadow: 4px 8px 14px 2px rgba(255, 255, 255, 0.12);
}

.tooltip-fade-enter-active,
.tooltip-fade-leave-active {
    transition: opacity 120ms ease-out, transform 120ms ease-out;
}

.tooltip-fade-enter-from,
.tooltip-fade-leave-to {
    opacity: 0;
    transform: translate(-50%, -100%) translateY(-4px);
}
</style>
