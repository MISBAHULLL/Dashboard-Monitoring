<script setup lang="ts">
import { computed } from 'vue';
import { Trophy, Medal, Award } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { index as tasksIndex } from '@/routes/tasks';
import type { TeamPerformance } from '@/types/dashboard';

/**
 * TeamPerformanceCard Component
 *
 * Neo-brutalist leaderboard untuk performa tim product.
 * Menggantikan tabel polos dengan tampilan yang ringkas & visual:
 * - Rank badge (gold/silver/bronze untuk top 3, navy pale untuk sisanya)
 * - Avatar bulat dengan inisial + gradient navy→teal
 * - Progress bar completion rate dengan warna dinamis
 *   (merah <40%, amber 40–69%, hijau ≥70%)
 * - Border 2.5px hitam + colored shadow (sejalan dengan HeroCard / TaskOverdueCard)
 * - Hover row lift + highlight border kiri
 *
 * Props tetap sama dengan versi sebelumnya supaya AdminDashboard tidak perlu diubah.
 *
 * @see component redesign/Ringkasan Performa Tim Product
 */

interface Props {
    teams: TeamPerformance[];
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
});

/**
 * Urutkan tim berdasarkan completion_rate DESC → total_tasks DESC (tiebreaker)
 * supaya leaderboard benar-benar mencerminkan performa, bukan volume.
 * Batas 10 tim sesuai requirement lama.
 */
const displayedTeams = computed(() => {
    return [...props.teams]
        .sort((a, b) => {
            if (b.completion_rate !== a.completion_rate) {
                return b.completion_rate - a.completion_rate;
            }
            return b.total_tasks - a.total_tasks;
        })
        .slice(0, 10);
});

/**
 * Ambil inisial dari nama tim (maks 2 huruf) untuk avatar.
 */
const getInitials = (name: string): string => {
    const parts = name.trim().split(/\s+/);
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
};

/**
 * Palet 8 gradient pair — dipilih manual agar semua kontras & teks putih readable.
 * Warna ditentukan dari team.id (bukan index) supaya konsisten meski posisi berubah.
 */
const AVATAR_GRADIENTS = [
    'from-[#1B3A6B] to-[#2BAE6E]',   // navy → teal (brand utama)
    'from-[#7C3AED] to-[#A78BFA]',   // violet → lavender
    'from-[#0369A1] to-[#38BDF8]',   // ocean blue → sky
    'from-[#B45309] to-[#F59E0B]',   // amber dark → amber
    'from-[#BE123C] to-[#FB7185]',   // rose dark → rose light
    'from-[#065F46] to-[#34D399]',   // emerald dark → emerald
    'from-[#9D174D] to-[#F472B6]',   // pink dark → pink light
    'from-[#1E40AF] to-[#60A5FA]',   // blue dark → blue light
] as const;

/**
 * Pilih gradient deterministik berdasarkan team.id agar warna tidak berubah
 * saat leaderboard di-resort. Fallback aman kalau id tidak tersedia.
 */
const getAvatarGradient = (teamId: number): string => {
    const safeId = Number.isFinite(teamId) ? Math.abs(Math.trunc(teamId)) : 0;
    return AVATAR_GRADIENTS[safeId % AVATAR_GRADIENTS.length];
};

/**
 * Generate URL ke halaman tasks dengan filter product + status.
 * - status undefined  → semua task tim
 * - status 'completed' → task selesai
 * - status 'overdue'   → task overdue (handler khusus di TaskController)
 */
const taskUrl = (teamId: number, status?: string): string => {
    const query: Record<string, string> = { product_id: String(teamId) };
    if (status) query.status = status;
    return tasksIndex({ query }).url;
};

/**
 * Warna progress bar sesuai completion rate:
 * merah <40%, amber 40-69%, hijau ≥70%.
 */
const getProgressColor = (rate: number): string => {
    if (rate >= 70) return 'bg-[#22C55E]';
    if (rate >= 40) return 'bg-[#F59E0B]';
    return 'bg-[#EF4444]';
};

/**
 * Warna teks persentase — senada dengan bar.
 */
const getProgressTextColor = (rate: number): string => {
    if (rate >= 70) return 'text-[#16a34a] dark:text-emerald-400';
    if (rate >= 40) return 'text-[#d97706] dark:text-amber-400';
    return 'text-[#dc2626] dark:text-red-400';
};

/**
 * Meta rank untuk 3 besar: medal emoji + warna shadow.
 * Top 1 = gold, top 2 = silver, top 3 = bronze, selain itu navy pale.
 */
const getRankMeta = (index: number) => {
    if (index === 0) {
        return {
            bg: 'bg-gradient-to-br from-[#fde68a] to-[#f59e0b]',
            text: 'text-[#78350f]',
            border: 'border-[#b45309]',
            icon: Trophy,
        };
    }
    if (index === 1) {
        return {
            bg: 'bg-gradient-to-br from-[#e5e7eb] to-[#9ca3af]',
            text: 'text-[#1f2937]',
            border: 'border-[#4b5563]',
            icon: Medal,
        };
    }
    if (index === 2) {
        return {
            bg: 'bg-gradient-to-br from-[#fed7aa] to-[#c2410c]',
            text: 'text-[#7c2d12]',
            border: 'border-[#9a3412]',
            icon: Award,
        };
    }
    return {
        bg: 'bg-tm-navy-pale dark:bg-slate-800',
        text: 'text-tm-navy dark:text-tm-navy-pale',
        border: 'border-tm-navy/40 dark:border-slate-600',
        icon: null,
    };
};
</script>

<template>
    <article
        class="team-perf-card relative flex h-full flex-col overflow-hidden rounded-[18px] border-[2.5px] border-black bg-white px-5 pt-4 pb-3.5 dark:bg-card"
        aria-label="Ringkasan Performa Tim Product"
    >
        <!-- Header -->
        <header class="mb-3 flex items-start justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div
                    class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl border-[1.5px] border-black bg-gradient-to-br from-tm-navy to-[#2D5090] text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.15)] dark:border-slate-500"
                >
                    <Trophy :size="18" :stroke-width="2.2" />
                </div>
                <div>
                    <h2
                        class="text-[18px] font-extrabold leading-tight tracking-[-0.3px] text-[#093b70] dark:text-tm-navy-pale sm:text-[20px]"
                    >
                        Ringkasan Performa Tim Product
                    </h2>
                    <p class="text-[12px] font-normal text-tm-text-secondary dark:text-slate-400">
                        Progress task per tim berdasarkan total, selesai, dan overdue.
                    </p>
                </div>
            </div>

            <!-- Badge jumlah tim -->
            <span
                v-if="!loading && teams.length > 0"
                class="hidden flex-shrink-0 items-center gap-1 rounded-full border-[1.5px] border-black bg-tm-navy-pale px-2.5 py-0.5 text-[11px] font-bold text-tm-navy transition-all duration-200 hover:bg-tm-navy hover:text-white hover:scale-105 hover:shadow-[2px_2px_0_0_rgba(0,0,0,0.2)] cursor-default sm:inline-flex dark:bg-slate-800 dark:text-tm-navy-pale dark:border-slate-600 dark:hover:bg-tm-navy-pale dark:hover:text-[#111]"
            >
                {{ displayedTeams.length }} Tim
            </span>
        </header>

        <!-- Loading skeleton -->
        <div v-if="loading" class="flex flex-col gap-2">
            <div
                v-for="i in 5"
                :key="i"
                class="flex items-center gap-3 rounded-xl border-[1.5px] border-dashed border-muted/50 px-3 py-2.5"
            >
                <div class="h-8 w-8 animate-pulse rounded-lg bg-muted/40"></div>
                <div class="h-8 w-8 animate-pulse rounded-full bg-muted/40"></div>
                <div class="flex-1 space-y-1.5">
                    <div class="h-3 w-32 animate-pulse rounded bg-muted/40"></div>
                    <div class="h-2 w-full animate-pulse rounded bg-muted/30"></div>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div
            v-else-if="teams.length === 0"
            class="flex flex-1 flex-col items-center justify-center py-10 text-center"
        >
            <div
                class="mb-3 flex h-14 w-14 items-center justify-center rounded-full border-[2px] border-dashed border-tm-navy/30 bg-tm-navy-pale dark:bg-slate-800 dark:border-slate-600"
            >
                <Trophy class="text-tm-navy/50 dark:text-slate-500" :size="24" :stroke-width="2" />
            </div>
            <p class="text-[14px] font-semibold text-tm-navy dark:text-slate-200">
                Belum ada data performa tim.
            </p>
            <p class="mt-1 text-[12px] text-tm-text-secondary dark:text-slate-400">
                Data akan muncul setelah tim memiliki task yang di-assign.
            </p>
        </div>

        <!-- Leaderboard rows -->
        <ol
            v-else
            class="leaderboard-scroll flex flex-col gap-2 overflow-y-auto pr-0.5"
            style="max-height: 560px"
            aria-label="Daftar peringkat performa tim"
        >
            <li
                v-for="(team, index) in displayedTeams"
                :key="team.id"
                class="team-row group relative flex items-center gap-3 rounded-xl border-[1.5px] border-border/60 bg-white px-3 py-2.5 transition-all duration-200 ease-out hover:-translate-y-0.5 hover:border-tm-navy hover:bg-tm-navy-pale/40 hover:shadow-[3px_3px_0_0_rgba(27,58,107,0.25)] dark:bg-card/60 dark:border-border dark:hover:bg-tm-navy/10 dark:hover:border-tm-navy-pale/40"
            >
                <!-- Rank badge -->
                <div
                    class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg border-[1.5px] text-[13px] font-black tabular-nums"
                    :class="[getRankMeta(index).bg, getRankMeta(index).text, getRankMeta(index).border]"
                    :aria-label="`Peringkat ${index + 1}`"
                >
                    <component
                        v-if="getRankMeta(index).icon"
                        :is="getRankMeta(index).icon"
                        :size="16"
                        :stroke-width="2.5"
                    />
                    <span v-else>{{ index + 1 }}</span>
                </div>

                <!-- Avatar inisial -->
                <div
                    class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br text-[12px] font-extrabold text-white shadow-[1px_1px_0_0_rgba(0,0,0,0.2)]"
                    :class="getAvatarGradient(team.id)"
                    aria-hidden="true"
                >
                    {{ getInitials(team.name) }}
                </div>

                <!-- Nama + progress bar -->
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <!-- Nama tim → link ke semua task tim -->
                        <Link
                            :href="taskUrl(team.id)"
                            class="truncate text-[14px] font-semibold text-[#093b70] transition-colors hover:text-tm-navy-medium hover:underline dark:text-slate-100 dark:hover:text-sky-400"
                            :title="`Lihat semua task ${team.name}`"
                        >
                            {{ team.name }}
                        </Link>
                        <span
                            class="flex-shrink-0 text-[12px] font-extrabold tabular-nums"
                            :class="getProgressTextColor(team.completion_rate)"
                        >
                            {{ team.completion_rate }}%
                        </span>
                    </div>

                    <!-- Progress bar -->
                    <div
                        class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700"
                        role="progressbar"
                        :aria-valuenow="team.completion_rate"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        :aria-label="`Completion rate ${team.name}`"
                    >
                        <div
                            class="h-full rounded-full transition-all duration-500 ease-out"
                            :class="getProgressColor(team.completion_rate)"
                            :style="{ width: `${Math.max(team.completion_rate, 2)}%` }"
                        />
                    </div>
                </div>

                <!-- Stats kompak di kanan — tiap angka bisa diklik untuk filter -->
                <div class="flex flex-shrink-0 items-center gap-3 sm:gap-4">
                    <!-- Total → semua task tim -->
                    <div class="flex flex-col items-end">
                        <span
                            class="text-[10px] font-semibold uppercase tracking-wide text-tm-text-muted dark:text-slate-500"
                        >
                            Total
                        </span>
                        <Link
                            :href="taskUrl(team.id)"
                            class="text-[14px] font-bold tabular-nums text-[#0b2a6b] transition-colors hover:text-tm-navy-medium hover:underline dark:text-slate-100 dark:hover:text-sky-400"
                            :title="`Semua task ${team.name}`"
                        >
                            {{ team.total_tasks }}
                        </Link>
                    </div>
                    <!-- Selesai → filter completed -->
                    <div class="flex flex-col items-end">
                        <span
                            class="text-[10px] font-semibold uppercase tracking-wide text-tm-text-muted dark:text-slate-500"
                        >
                            Selesai
                        </span>
                        <Link
                            :href="taskUrl(team.id, 'completed')"
                            class="text-[14px] font-bold tabular-nums text-tm-green transition-colors hover:text-tm-green-dark hover:underline dark:hover:text-emerald-400"
                            :title="`Task selesai ${team.name}`"
                        >
                            {{ team.completed_tasks }}
                        </Link>
                    </div>
                    <!-- Overdue → filter overdue -->
                    <div class="flex flex-col items-end">
                        <span
                            class="text-[10px] font-semibold uppercase tracking-wide text-tm-text-muted dark:text-slate-500"
                        >
                            Overdue
                        </span>
                        <!-- Hanya jadi link jika ada overdue, kalau 0 tetap teks biasa -->
                        <Link
                            v-if="team.overdue_tasks > 0"
                            :href="taskUrl(team.id, 'overdue')"
                            class="text-[14px] font-bold tabular-nums text-tm-danger transition-colors hover:text-red-700 hover:underline dark:hover:text-red-400"
                            :title="`Task overdue ${team.name}`"
                        >
                            {{ team.overdue_tasks }}
                        </Link>
                        <span
                            v-else
                            class="text-[14px] font-bold tabular-nums text-tm-text-muted dark:text-slate-500"
                        >
                            0
                        </span>
                    </div>
                </div>
            </li>
        </ol>
    </article>
</template>

<style scoped>
.team-perf-card {
    box-shadow: 2px 4px 4px 0 rgba(11, 42, 107, 0.35);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.team-perf-card:hover {
    transform: translateY(-2px);
    box-shadow: 3px 8px 12px 2px rgba(11, 42, 107, 0.4);
}

:global(.dark) .team-perf-card {
    box-shadow: 3px 5px 5px 0 rgba(0, 0, 0, 0.55);
}

:global(.dark) .team-perf-card:hover {
    box-shadow: 4px 8px 14px 2px rgba(255, 255, 255, 0.1);
}

/* Row accent line di kiri pada hover — memperkuat kesan "active row" */
.team-row::before {
    content: '';
    position: absolute;
    left: 0;
    top: 20%;
    bottom: 20%;
    width: 3px;
    border-radius: 2px;
    background: linear-gradient(180deg, #1b3a6b 0%, #2bae6e 100%);
    opacity: 0;
    transform: translateX(-4px);
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.team-row:hover::before {
    opacity: 1;
    transform: translateX(0);
}

/* Scrollbar tipis yang bisa disembunyikan */
.leaderboard-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(27, 58, 107, 0.25) transparent;
}

.leaderboard-scroll::-webkit-scrollbar {
    width: 4px;
}

.leaderboard-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.leaderboard-scroll::-webkit-scrollbar-thumb {
    background-color: rgba(27, 58, 107, 0.25);
    border-radius: 4px;
}

:global(.dark) .leaderboard-scroll {
    scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
}

:global(.dark) .leaderboard-scroll::-webkit-scrollbar-thumb {
    background-color: rgba(255, 255, 255, 0.15);
}
</style>
