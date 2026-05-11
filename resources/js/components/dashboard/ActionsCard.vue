<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Plus, Users, Building2, Download } from 'lucide-vue-next';
import type { Component } from 'vue';

/**
 * ActionsCard Component
 *
 * Quick action buttons card for dashboard navigation.
 * Contains 4 action items: New Task, Add Team, New Faskes, Export Data
 *
 * Features:
 * - Neo-brutalism border styling with hover effects
 * - Icon + label layout with nested shadows
 * - Inertia.js Link integration
 * - Loading skeleton state
 * - Dark mode support
 */

interface ActionItem {
  /**
   * Action label text
   */
  label: string;

  /**
   * Secondary description text
   */
  description: string;

  /**
   * Icon component from lucide-vue-next
   */
  icon: Component;

  /**
   * Route URL for navigation
   */
  href: string;

  /**
   * Color theme for the action
   */
  colorTheme: 'navy' | 'green' | 'amber' | 'neutral';
}

interface Props {
  /**
   * Show loading skeleton instead of content
   * @default false
   */
  loading?: boolean;

  /**
   * Custom action items (optional, uses defaults if not provided)
   */
  actions?: ActionItem[];
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
});

/**
 * Default action items
 */
const defaultActions: ActionItem[] = [
  {
    label: 'New Task',
    description: 'Buat task baru',
    icon: Plus,
    href: '/tasks/create',
    colorTheme: 'green',
  },
  {
    label: 'Add Team',
    description: 'Tambah tim baru',
    icon: Users,
    href: '/teams/create',
    colorTheme: 'navy',
  },
  {
    label: 'New Faskes',
    description: 'Tambah faskes',
    icon: Building2,
    href: '/clients/create',
    colorTheme: 'amber',
  },
  {
    label: 'Export data',
    description: 'Export semua',
    icon: Download,
    href: '/export',
    colorTheme: 'neutral',
  },
];

/**
 * Get action items (use defaults if not provided)
 */
const actionItems = computed(() => props.actions || defaultActions);

/**
 * Container classes with neo-brutalism styling
 */
const containerClasses = computed(() => [
  'relative overflow-hidden rounded-2xl border-2 border-border bg-card p-5',
  'shadow-[3px_3px_0_0_rgba(27,58,107,0.12)]',
  'transition-all duration-200 ease-out',
  'hover:shadow-[5px_5px_0_0_rgba(27,58,107,0.15)]',
  'hover:-translate-y-0.5',
]);

/**
 * Color theme mappings for action items
 */
const colorThemes = {
  navy: {
    bg: 'bg-tm-navy-pale dark:bg-tm-navy/20',
    border: 'border-tm-navy',
    icon: 'text-tm-navy',
  },
  green: {
    bg: 'bg-tm-green-pale dark:bg-tm-green/20',
    border: 'border-tm-green',
    icon: 'text-tm-green',
  },
  amber: {
    bg: 'bg-tm-warning-pale dark:bg-tm-warning/10',
    border: 'border-tm-warning',
    icon: 'text-tm-warning',
  },
  neutral: {
    bg: 'bg-muted dark:bg-muted/50',
    border: 'border-border',
    icon: 'text-muted-foreground',
  },
} as const;

/**
 * Get color classes for an action item
 */
const getColorClasses = (theme: ActionItem['colorTheme']) => colorThemes[theme];
</script>

<template>
  <article :class="containerClasses">
    <!-- Loading skeleton -->
    <div v-if="loading" class="animate-pulse space-y-3">
      <div class="h-5 w-24 rounded bg-muted/50 mb-4"></div>
      <div v-for="i in 4" :key="i" class="flex items-center gap-3 p-3 rounded-xl border-2 bg-muted/20">
        <div class="h-10 w-10 rounded-lg bg-muted/40"></div>
        <div class="space-y-2">
          <div class="h-4 w-24 rounded bg-muted/40"></div>
          <div class="h-3 w-20 rounded bg-muted/30"></div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <template v-else>
      <!-- Header -->
      <header class="mb-4">
        <h2 class="text-base font-semibold text-primary">Actions</h2>
      </header>

      <!-- Action items list -->
      <div class="space-y-2.5">
        <Link
          v-for="(action, index) in actionItems"
          :key="index"
          :href="action.href"
          class="group flex items-center gap-3 rounded-xl border-2 p-3 transition-all duration-200 hover:-translate-y-0.5"
          :class="[
            getColorClasses(action.colorTheme).bg,
            getColorClasses(action.colorTheme).border,
            'shadow-[2px_2px_0_0_rgba(27,58,107,0.08)]',
            'hover:shadow-[3px_3px_0_0_rgba(27,58,107,0.12)]',
          ]"
        >
          <!-- Icon container with nested shadow -->
          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border-2 bg-white dark:bg-card shadow-[2px_2px_0_0_rgba(27,58,107,0.06)]"
            :class="getColorClasses(action.colorTheme).border"
          >
            <component
              :is="action.icon"
              class="h-5 w-5"
              :class="getColorClasses(action.colorTheme).icon"
            />
          </div>

          <!-- Text content -->
          <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">
              {{ action.label }}
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              {{ action.description }}
            </p>
          </div>
        </Link>
      </div>
    </template>
  </article>
</template>
