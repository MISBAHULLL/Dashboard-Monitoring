<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { CirclePlus, Users, Building2, FileUp } from 'lucide-vue-next';
import { create as tasksCreate, exportMethod as tasksExport } from '@/routes/tasks';
import { index as teamsIndex } from '@/routes/teams';
import { index as clientsIndex } from '@/routes/clients';
import type { Component } from 'vue';

/**
 * ActionsCard Component
 *
 * Quick action buttons card for dashboard — matches Figma "Actions" panel.
 * Neo-brutalism styling: thick black border, rounded-[21px], heavy shadow.
 *
 * Contains 4 action items: New Task, Add Team, New Faskes, Export Data.
 * Each item navigates to the corresponding page.
 *
 * @see component redesign/Actions
 */

interface ActionItem {
    label: string;
    description: string;
    icon: Component;
    href: string;
    /** If true, use a plain <a> tag (for file downloads) instead of Inertia Link */
    external?: boolean;
}

interface Props {
    loading?: boolean;
    actions?: ActionItem[];
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
});

const defaultActions: ActionItem[] = [
    {
        label: 'New Task',
        description: 'Create New Task',
        icon: CirclePlus,
        href: tasksCreate.url(),
    },
    {
        label: 'Add Team',
        description: 'Create New Team',
        icon: Users,
        href: teamsIndex.url({ query: { action: 'create' } }),
    },
    {
        label: 'New Faskes',
        description: 'Create New Faskes',
        icon: Building2,
        href: clientsIndex.url({ query: { action: 'create' } }),
    },
    {
        label: 'Export data',
        description: 'Export CSV',
        icon: FileUp,
        href: tasksExport.url(),
        external: true,
    },
];

const actionItems = computed(() => props.actions || defaultActions);
</script>

<template>
    <article
        class="actions-card relative flex h-full flex-col rounded-[21px] border-3 border-black bg-white p-5 dark:border-slate-700/80 dark:bg-[#111c2e]"
    >
        <!-- Loading skeleton -->
        <div v-if="loading" class="animate-pulse space-y-3 flex-1">
            <div class="h-6 w-20 rounded bg-muted/50 dark:bg-slate-700/40 mb-3"></div>
            <div v-for="i in 4" :key="i" class="h-[56px] rounded-lg border-2 border-emerald-200 bg-emerald-50/30 dark:border-emerald-500/30 dark:bg-emerald-500/5"></div>
        </div>

        <!-- Content -->
        <template v-else>
            <!-- Header -->
            <header class="mb-4">
                <h2 class="text-[22px] font-normal text-black dark:text-foreground">
                    Actions
                </h2>
            </header>

            <!-- Action items list -->
            <div class="flex flex-1 flex-col justify-between gap-3">
                <!-- External link (file download) -->
                <a
                    v-for="(action, index) in actionItems.filter(a => a.external)"
                    :key="'ext-' + index"
                    :href="action.href"
                    class="action-item"
                >
                    <div class="action-icon">
                        <component :is="action.icon" class="h-5 w-5" :stroke-width="1.5" />
                    </div>
                    <div class="min-w-0">
                        <p class="action-label">{{ action.label }}</p>
                        <p class="action-desc">{{ action.description }}</p>
                    </div>
                </a>

                <!-- Inertia Link (SPA navigation) -->
                <Link
                    v-for="(action, index) in actionItems.filter(a => !a.external)"
                    :key="'link-' + index"
                    :href="action.href"
                    class="action-item"
                >
                    <div class="action-icon">
                        <component :is="action.icon" class="h-5 w-5" :stroke-width="1.5" />
                    </div>
                    <div class="min-w-0">
                        <p class="action-label">{{ action.label }}</p>
                        <p class="action-desc">{{ action.description }}</p>
                    </div>
                </Link>
            </div>
        </template>
    </article>
</template>

<style scoped>
.actions-card {
    box-shadow: 2px 4px 4px 4px rgba(0, 0, 0, 0.25);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.actions-card:hover {
    box-shadow: 4px 8px 16px 4px rgba(0, 0, 0, 0.35);
    transform: translateY(-2px);
}

/* Dark: ganti shadow hitam pekat (yang nggak kelihatan di bg gelap) dengan
   inner halo + drop shadow lembut supaya card tetap punya dimensi. */
:global(.dark) .actions-card {
    box-shadow:
        0 0 0 1px rgba(148, 163, 184, 0.08),
        0 14px 32px rgba(0, 0, 0, 0.42);
}

:global(.dark) .actions-card:hover {
    box-shadow:
        0 0 0 1px rgba(52, 211, 153, 0.28),
        0 18px 40px rgba(0, 0, 0, 0.5);
}

.action-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 7px;
    border: 1px solid #10b981;
    background: white;
    box-shadow: 0px 4px 4px 0px rgba(16, 185, 129, 0.25);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}

.action-item:hover {
    transform: translateY(-4px) scale(1.03);
    box-shadow: 0px 12px 24px 0px rgba(16, 185, 129, 0.4);
    border-color: #059669;
    background: #ecfdf5;
}

.action-item:active {
    transform: translateY(0) scale(0.98);
    box-shadow: 0px 2px 4px 0px rgba(16, 185, 129, 0.2);
}

.action-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    flex-shrink: 0;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), color 0.3s;
    color: #111;
}

.action-item:hover .action-icon {
    transform: scale(1.2) rotate(5deg);
    color: #059669;
}

.action-label {
    font-size: 15px;
    font-weight: 400;
    line-height: 1.2;
    color: #111;
    transition: color 0.3s;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.action-item:hover .action-label {
    color: #047857;
}

.action-desc {
    font-size: 11px;
    line-height: 1.2;
    color: rgba(0, 0, 0, 0.5);
    margin-top: 2px;
}

/* ──────────── Dark mode ────────────
   Item dibuat sedikit lebih terang dari card agar terlihat berlapis,
   border emerald dipertahankan tapi lebih translucent supaya nggak
   nyala berlebihan, dan glow halus dipakai sebagai pengganti shadow. */
:global(.dark) .action-item {
    background: rgba(15, 23, 42, 0.36);
    border-color: rgba(52, 211, 153, 0.38);
    box-shadow:
        0 0 0 1px rgba(52, 211, 153, 0.08) inset,
        0 8px 18px rgba(0, 0, 0, 0.22);
    color: var(--foreground);
}

:global(.dark) .action-item:hover {
    background: rgba(16, 185, 129, 0.14);
    border-color: #34d399;
    box-shadow:
        0 0 0 1px rgba(52, 211, 153, 0.35) inset,
        0 12px 24px rgba(16, 185, 129, 0.25);
}

:global(.dark) .action-icon {
    color: #E2E8F0;
}

:global(.dark) .action-item:hover .action-icon {
    color: #34d399;
}

:global(.dark) .action-label {
    color: #E8EEF8;
}

:global(.dark) .action-item:hover .action-label {
    color: #6ee7b7;
}

:global(.dark) .action-desc {
    color: #94A3B8;
}
</style>
