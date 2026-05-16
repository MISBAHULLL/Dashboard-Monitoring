<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ListTodo, Users, Clock, Building2, ArrowUpRight } from 'lucide-vue-next';
import { index as tasksIndex } from '@/routes/tasks';
import { index as teamsIndex } from '@/routes/teams';
import { index as clientsIndex } from '@/routes/clients';

/**
 * GridTaskCard Component
 *
 * A 2×2 stats grid card with neo-brutalism styling inside a green rounded container.
 * Matches the Figma "Grid Task" design — placed beside the ActionsCard in the dashboard.
 *
 * Features:
 * - 4 stat cards: Total Tasks, Total Tim, Menunggu Dikerjakan, Total Faskes
 * - Green container with thick border and shadow
 * - Each card: label top-left, arrow top-right, big value bottom-left, icon bottom-right
 * - Divider line separating value area from bottom
 * - Dark mode support
 *
 * @see component redesign/Grid Task
 */

interface Props {
    totalTasks: number;
    totalTeams: number;
    pendingTasks: number;
    totalClients: number;
    trends?: {
        tasks: number;
        teams: number;
        pending: number;
        clients: number;
    };
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
});

/**
 * Helper: convert a trend number into a trend object or undefined (hidden if 0)
 */
function makeTrend(value: number): { direction: 'up' | 'down'; value: number } | undefined {
    if (!value || value === 0) return undefined;
    return {
        direction: value > 0 ? 'up' : 'down',
        value: Math.abs(value),
    };
}

interface StatItem {
    label: string;
    value: number;
    icon: typeof ListTodo;
    href: string;
    trend?: { direction: 'up' | 'down'; value: number };
}

const statItems = computed<StatItem[]>(() => [
    {
        label: 'Total Taks',
        value: props.totalTasks,
        icon: ListTodo,
        href: tasksIndex.url(),
        trend: props.trends ? makeTrend(props.trends.tasks) : undefined,
    },
    {
        label: 'Total Tim',
        value: props.totalTeams,
        icon: Users,
        href: teamsIndex.url(),
        trend: props.trends ? makeTrend(props.trends.teams) : undefined,
    },
    {
        label: 'Menunggu Dikerjakan',
        value: props.pendingTasks,
        icon: Clock,
        href: tasksIndex({ query: { status: 'open' } }).url,
        trend: props.trends ? makeTrend(props.trends.pending) : undefined,
    },
    {
        label: 'Total Faskes',
        value: props.totalClients,
        icon: Building2,
        href: clientsIndex.url(),
        trend: props.trends ? makeTrend(props.trends.clients) : undefined,
    },
]);
</script>

<template>
    <div class="grid-task-container">
        <!-- Loading skeleton -->
        <div v-if="loading" class="grid grid-cols-2 gap-3 h-full animate-pulse">
            <div v-for="i in 4" :key="i" class="rounded-[28px] bg-white/40 border border-black/10"></div>
        </div>

        <!-- Content -->
        <div v-else class="grid grid-cols-2 gap-3 h-full">
            <Link
                v-for="(stat, index) in statItems"
                :key="index"
                :href="stat.href"
                class="grid-task-item"
                :aria-label="`${stat.label}: ${stat.value}`"
            >
                <!-- Arrow top-right (absolute, bold/tajam) -->
                <ArrowUpRight class="absolute top-3 right-3 h-5 w-5 text-black dark:text-foreground" :stroke-width="3" />

                <!-- Label at top -->
                <p class="grid-task-label">
                    <template v-if="stat.label.includes('\n')">
                        <span v-for="(line, i) in stat.label.split('\n')" :key="i">
                            {{ line }}<br v-if="i === 0" />
                        </span>
                    </template>
                    <template v-else>{{ stat.label }}</template>
                </p>

                <!-- Middle: Value+Badge (left) and Icon (right) -->
                <div class="flex items-start justify-between w-full flex-1 pt-1">
                    <div class="flex items-center gap-2">
                        <span class="grid-task-value">{{ stat.value }}</span>
                        <span
                            v-if="stat.trend"
                            :class="[
                                'grid-task-trend',
                                stat.trend.direction === 'up' ? 'grid-task-trend--up' : 'grid-task-trend--down',
                            ]"
                        >
                            <svg
                                v-if="stat.trend.direction === 'up'"
                                class="h-3 w-3"
                                viewBox="0 0 14 14"
                                fill="none"
                            >
                                <path
                                    d="M3 11L11 3M11 3H5M11 3V9"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <svg
                                v-else
                                class="h-3 w-3 rotate-180"
                                viewBox="0 0 14 14"
                                fill="none"
                            >
                                <path
                                    d="M3 11L11 3M11 3H5M11 3V9"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            {{ stat.trend.direction === 'up' ? '+' : '-' }}{{ stat.trend.value }}
                        </span>
                    </div>

                    <!-- Icon (self-center vertikal, independen dari value) -->
                    <component
                        :is="stat.icon"
                        class="grid-task-icon self-center"
                        :stroke-width="1.8"
                    />
                </div>

                <!-- Divider line -->
                <!-- Tebal: h-[Xpx], Geser: translate-y-[Ypx], Margin kiri-kanan: mx-[Xpx] -->
                <div class="grid-task-line"></div>
            </Link>
        </div>
    </div>
</template>

<style scoped>
.grid-task-container {
    height: 100%;
    padding: 0.75rem;
    border-radius: 35px;
    border: 3px solid #111;
    background: rgba(3, 164, 114, 0.87);
    box-shadow: 6px 6px 0px 0px rgba(3, 164, 114, 0.6);
    overflow: hidden;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.grid-task-container:hover {
    transform: translateY(-4px) scale(1.01);
    box-shadow: 8px 10px 0px 0px rgba(3, 164, 114, 0.7), 0 20px 40px rgba(3, 164, 114, 0.2);
}

.dark .grid-task-container {
    background: rgba(16, 185, 129, 0.16);
    border-color: rgba(52, 211, 153, 0.42);
    box-shadow:
        0 0 0 1px rgba(52, 211, 153, 0.16) inset,
        0 16px 34px rgba(0, 0, 0, 0.42);
}

.grid-task-item {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 1rem 1.25rem 0.75rem;
    border-radius: 28px;
    border: 1.5px solid #111;
    background: #fcfcfc;
    box-shadow: 2px 4px 4px 0px rgba(0, 0, 0, 0.25);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    overflow: hidden;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}

.grid-task-item:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 4px 8px 16px 0px rgba(0, 0, 0, 0.3);
    border-color: #03a472;
}

.grid-task-item:hover .grid-task-icon {
    transform: translate(-4px, -20px) scale(1.15);
    color: #03a472;
}

.grid-task-item:active {
    transform: translateY(0px) scale(0.98);
    box-shadow: 1px 2px 4px 0px rgba(0, 0, 0, 0.2);
}

.dark .grid-task-item {
    background: #111c2e;
    border-color: rgba(148, 163, 184, 0.38);
    box-shadow:
        0 0 0 1px rgba(148, 163, 184, 0.06) inset,
        0 10px 22px rgba(0, 0, 0, 0.32);
}

.grid-task-label {
    font-size: 20px;
    font-weight: 800;
    line-height: 3;
    color: #111;
    white-space: pre-line;
    -webkit-text-stroke: 0.3px #111;
}

.dark .grid-task-label {
    color: #f1f5f9;
    -webkit-text-stroke: 0;
}

.grid-task-icon {
    width: 54px;
    height: 54px;
    color: rgba(0, 0, 0, 0.85);
    margin-right: 0.5rem;
    /* Geser icon: translate(kiri-kanan, atas-bawah) */
    /* Negatif = kiri/atas, Positif = kanan/bawah */
    transform: translate(-4px, -20px);
    transition: transform 0.2s ease, color 0.2s ease;
}

.dark .grid-task-icon {
    color: #cbd5e1;
    opacity: 0.86;
}

.grid-task-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
    color: #111;
}

.dark .grid-task-value {
    color: #f8fafc;
}

.grid-task-trend {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 800;
}

.grid-task-trend--up {
    background: #80bd51;
    color: #111;
}

.grid-task-trend--down {
    background: #e01d1d;
    color: #111;
}

.grid-task-line {
    width: 100%;
    height: 2px;                        /* Tebal line */
    background: rgba(0, 0, 0, 0.9);
    /* Geser: translate(kiri-kanan, atas-bawah) */
    /* Negatif = kiri/atas, Positif = kanan/bawah */
    transform: translate(0px, -15px);
    /* Margin kiri-kanan untuk atur lebar */
    margin-left: 0px;
    margin-right: 0px;
}

.dark .grid-task-line {
    background: rgba(203, 213, 225, 0.22);
}
</style>
