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
        href: teamsIndex.url(),
    },
    {
        label: 'New Faskes',
        description: 'Create New Faskes',
        icon: Building2,
        href: clientsIndex.url(),
    },
    {
        label: 'Export data',
        description: 'Export Excel',
        icon: FileUp,
        href: tasksExport.url(),
        external: true,
    },
];

const actionItems = computed(() => props.actions || defaultActions);
</script>

<template>
    <article
        class="relative flex h-full flex-col overflow-hidden rounded-[21px] border-3 border-black bg-white p-5 dark:bg-card dark:border-border"
        style="box-shadow: 2px 4px 4px 4px rgba(0, 0, 0, 0.25)"
    >
        <!-- Loading skeleton -->
        <div v-if="loading" class="animate-pulse space-y-3 flex-1">
            <div class="h-6 w-20 rounded bg-muted/50 mb-3"></div>
            <div v-for="i in 4" :key="i" class="h-[56px] rounded-lg border-2 border-emerald-200 bg-emerald-50/30"></div>
        </div>

        <!-- Content -->
        <template v-else>
            <!-- Header -->
            <header class="mb-4">
                <h2 class="text-[22px] font-normal text-black dark:text-foreground">
                    Actions
                </h2>
            </header>

            <!-- Action items list — justify-between agar tersebar merata -->
            <div class="flex flex-1 flex-col justify-between gap-3">
                <component
                    v-for="(action, index) in actionItems"
                    :key="index"
                    :is="action.external ? 'a' : Link"
                    :href="action.href"
                    class="group flex items-center gap-3 rounded-[7px] border border-emerald-500 bg-white px-4 py-3 transition-all duration-200 hover:-translate-y-0.5 hover:bg-emerald-50/50 dark:bg-card dark:border-emerald-500/60 dark:hover:bg-emerald-900/10"
                    style="box-shadow: 0px 4px 4px 0px rgba(16, 185, 129, 0.25)"
                >
                    <!-- Icon -->
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center">
                        <component
                            :is="action.icon"
                            class="h-5 w-5 text-black dark:text-foreground"
                            :stroke-width="1.5"
                        />
                    </div>

                    <!-- Text content -->
                    <div class="min-w-0">
                        <p class="text-[15px] font-normal leading-tight text-black dark:text-foreground truncate">
                            {{ action.label }}
                        </p>
                        <p class="text-[11px] text-black/60 dark:text-muted-foreground leading-tight mt-0.5">
                            {{ action.description }}
                        </p>
                    </div>
                </component>
            </div>
        </template>
    </article>
</template>
