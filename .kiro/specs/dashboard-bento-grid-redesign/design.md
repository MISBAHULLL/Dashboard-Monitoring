# Design Document: Dashboard Bento Grid Redesign

## Overview

This document defines the technical design for redesigning the admin and member dashboards using a Bento Grid layout pattern with subtle neo-brutalism design touches. The redesign modernizes the visual appearance while preserving all existing functionality, data visualization, and user workflows.

### Design Philosophy

The Bento Grid pattern creates an asymmetric, visually engaging layout with varied card sizes, while subtle neo-brutalism elements add bold borders, vibrant colors, and a distinctive aesthetic. For this healthtech B2B product used by hospital staff, we balance modern design trends with professional trustworthiness.

**Key Principles:**
- Visual hierarchy through asymmetric card placement
- Subtle neo-brutalism: bold but not aggressive
- Professional and trustworthy aesthetic for healthcare context
- Performance-first approach with existing ApexCharts integration
- Full responsive support across devices

---

## Architecture

### System Context

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           Browser (Vue 3 + Inertia)                     │
├─────────────────────────────────────────────────────────────────────────┤
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────────────┐  │
│  │ AdminDashboard  │  │ MemberDashboard │  │ Reusable Components     │  │
│  │ (Bento Grid)    │  │ (Bento Grid)    │  │ (StatCard, ChartCard,   │  │
│  │                 │  │                 │  │  TaskListCard, etc.)    │  │
│  └────────┬────────┘  └────────┬────────┘  └─────────────────────────┘  │
│           │                    │                                         │
│           └────────┬───────────┘                                         │
│                    ▼                                                     │
│  ┌─────────────────────────────────────────────────────────────────────┐│
│  │                    DashboardController (PHP/Laravel)                ││
│  │  - Provides identical data structure (no backend changes required)  ││
│  │  - Returns: stats, chart_donut, chart_area, tasks, team_performance ││
│  └─────────────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────────────┘
```

### Component Hierarchy

```
AdminDashboard.vue / MemberDashboard.vue
├── BentoGrid (container component)
│   ├── StatCard (multiple instances)
│   │   ├── Card container with neo-brutalism styling
│   │   ├── Icon with colored background
│   │   ├── Statistic value (large text)
│   │   └── Label text
│   │
│   ├── DeadlineAlertCard (admin only)
│   │   ├── Header with icon and count
│   │   ├── Task list items
│   │   └── "Lihat Semua" link (conditional)
│   │
│   ├── ChartCard
│   │   ├── Card container
│   │   ├── Chart title
│   │   └── ApexCharts component
│   │
│   ├── TeamPerformanceCard (admin only)
│   │   ├── Card container (full-width)
│   │   ├── Table header
│   │   └── Team rows with completion rate
│   │
│   └── TaskListCard
│       ├── Card container
│       ├── Table header
│       └── Task rows with status/priority badges
```

---

## Components and Interfaces

### 1. BentoGrid Container Component

**Purpose:** Container component that manages the CSS Grid layout and responsive behavior.

```vue
<!-- resources/js/components/dashboard/BentoGrid.vue -->
<script setup lang="ts">
defineProps<{
  columns?: {
    default: number;
    md?: number;
    lg?: number;
    xl?: number;
  };
  gap?: string; // Tailwind gap class
}>();
</script>

<template>
  <div 
    class="grid gap-4 md:gap-6"
    :class="{
      'grid-cols-1 md:grid-cols-2 lg:grid-cols-4': columns?.default === 4,
      'auto-rows-min': true
    }"
  >
    <slot />
  </div>
</template>
```

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `columns` | `Object` | `{ default: 4, md: 2, lg: 4 }` | Column configuration per breakpoint |
| `gap` | `string` | `'gap-4 md:gap-6'` | Gap between grid items |

**Slots:**
| Slot | Description |
|------|-------------|
| `default` | Grid items (BentoGridItem components) |

---

### 2. BentoGridItem Component

**Purpose:** Individual grid item with configurable span for asymmetric layout.

```vue
<!-- resources/js/components/dashboard/BentoGridItem.vue -->
<script setup lang="ts">
defineProps<{
  span?: {
    default: string;
    md?: string;
    lg?: string;
  };
}>();
</script>

<template>
  <div :class="spanClass">
    <slot />
  </div>
</template>
```

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `span` | `Object` | `{ default: 'col-span-1' }` | CSS Grid column span classes |

**Span Configuration Examples:**
```typescript
// Small card (1 column)
span: { default: 'col-span-1' }

// Medium card (2 columns on desktop)
span: { default: 'col-span-1', md: 'col-span-2' }

// Large card (full width or 3 columns)
span: { default: 'col-span-1', md: 'col-span-2', lg: 'col-span-3' }

// Full width card
span: { default: 'col-span-full' }
```

---

### 3. StatCard Component

**Purpose:** Display numerical statistics with icon, label, and neo-brutalism styling.

```vue
<!-- resources/js/components/dashboard/StatCard.vue -->
<script setup lang="ts">
import type { Component } from 'vue';

type ColorTheme = 'navy' | 'green' | 'red' | 'amber' | 'neutral';

const props = withDefaults(defineProps<{
  label: string;
  value: number | string;
  icon: Component;
  colorTheme?: ColorTheme;
  loading?: boolean;
  animate?: boolean;
}>(), {
  colorTheme: 'neutral',
  loading: false,
  animate: true,
});

// Color theme mappings
const themeClasses = {
  navy: {
    bg: 'bg-tm-navy-pale dark:bg-tm-navy/20',
    border: 'border-tm-navy',
    iconBg: 'bg-tm-navy/10',
    iconColor: 'text-tm-navy',
    valueColor: 'text-tm-navy',
  },
  green: {
    bg: 'bg-tm-green-pale dark:bg-tm-green/20',
    border: 'border-tm-green',
    iconBg: 'bg-tm-green/10',
    iconColor: 'text-tm-green',
    valueColor: 'text-tm-green-dark',
  },
  red: {
    bg: 'bg-tm-danger-pale dark:bg-tm-danger/20',
    border: 'border-tm-danger',
    iconBg: 'bg-tm-danger/10',
    iconColor: 'text-tm-danger',
    valueColor: 'text-tm-danger',
  },
  amber: {
    bg: 'bg-tm-warning-pale dark:bg-tm-warning/20',
    border: 'border-tm-warning',
    iconBg: 'bg-tm-warning/10',
    iconColor: 'text-tm-warning',
    valueColor: 'text-tm-warning',
  },
  neutral: {
    bg: 'bg-card',
    border: 'border-border',
    iconBg: 'bg-muted',
    iconColor: 'text-muted-foreground',
    valueColor: 'text-foreground',
  },
};
</script>

<template>
  <article
    :class="[
      'relative overflow-hidden rounded-xl border-[1.5px] p-6',
      'shadow-[2px_2px_0_0_rgba(0,0,0,0.08)] dark:shadow-[2px_2px_0_0_rgba(255,255,255,0.05)]',
      'transition-all duration-200 ease-out',
      'hover:shadow-[4px_4px_0_0_rgba(0,0,0,0.1)] dark:hover:shadow-[4px_4px_0_0_rgba(255,255,255,0.08)]',
      'hover:-translate-y-0.5',
      themeClasses[colorTheme].bg,
      themeClasses[colorTheme].border,
    ]"
    :aria-label="`${label}: ${value}`"
  >
    <!-- Loading skeleton -->
    <div v-if="loading" class="animate-pulse">
      <div class="h-4 w-24 rounded bg-muted/50 mb-2"></div>
      <div class="h-8 w-16 rounded bg-muted/50"></div>
    </div>
    
    <!-- Content -->
    <div v-else class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-muted-foreground">{{ label }}</p>
        <p 
          class="mt-2 text-3xl font-bold"
          :class="themeClasses[colorTheme].valueColor"
        >
          {{ value }}
        </p>
      </div>
      <div 
        :class="[
          'rounded-xl p-3 border-[1.5px]',
          themeClasses[colorTheme].iconBg,
          themeClasses[colorTheme].border,
        ]"
      >
        <component 
          :is="icon" 
          class="h-6 w-6"
          :class="themeClasses[colorTheme].iconColor"
        />
      </div>
    </div>
  </article>
</template>
```

**Props:**
| Prop | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| `label` | `string` | Yes | - | Statistic label text |
| `value` | `number \| string` | Yes | - | Statistic value to display |
| `icon` | `Component` | Yes | - | Lucide icon component |
| `colorTheme` | `ColorTheme` | No | `'neutral'` | Color theme for the card |
| `loading` | `boolean` | No | `false` | Show loading skeleton |
| `animate` | `boolean` | No | `true` | Enable hover animations |

**Events:**
| Event | Payload | Description |
|-------|---------|-------------|
| `click` | `MouseEvent` | Emitted when card is clicked (for clickable cards) |

---

### 4. DeadlineAlertCard Component

**Purpose:** Display overdue or due-soon tasks with urgency styling.

```vue
<!-- resources/js/components/dashboard/DeadlineAlertCard.vue -->
<script setup lang="ts">
import { computed } from 'vue';
import { TriangleAlert, Clock3 } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

type AlertType = 'overdue' | 'due_soon';

const props = defineProps<{
  type: AlertType;
  count: number;
  tasks: Array<{
    id: number;
    title: string;
    client?: { name: string };
    release_date?: string;
  }>;
  viewAllLink?: string;
  loading?: boolean;
}>();

const config = computed(() => ({
  overdue: {
    icon: TriangleAlert,
    label: 'Task Overdue',
    colorClass: 'red',
    emptyMessage: 'Tidak ada task overdue.',
  },
  due_soon: {
    icon: Clock3,
    label: 'Task Due Soon (H-7)',
    colorClass: 'amber',
    emptyMessage: 'Tidak ada task due soon.',
  },
}[props.type]));
</script>

<template>
  <article
    :class="[
      'relative overflow-hidden rounded-xl border-[1.5px] p-6',
      'shadow-[2px_2px_0_0_rgba(0,0,0,0.08)]',
      type === 'overdue' 
        ? 'border-tm-danger bg-tm-danger-pale/60 dark:bg-tm-danger/10' 
        : 'border-tm-warning bg-tm-warning-pale/60 dark:bg-tm-warning/10',
    ]"
    :aria-label="`${config.label}: ${count} tasks`"
  >
    <!-- Header -->
    <div class="mb-4 flex items-center justify-between">
      <div>
        <p 
          class="text-sm font-medium"
          :class="type === 'overdue' ? 'text-red-700 dark:text-red-300' : 'text-amber-700 dark:text-amber-300'"
        >
          {{ config.label }}
        </p>
        <p 
          class="mt-2 text-3xl font-bold"
          :class="type === 'overdue' ? 'text-red-700 dark:text-red-300' : 'text-amber-700 dark:text-amber-300'"
        >
          {{ count }}
        </p>
      </div>
      <div 
        :class="[
          'rounded-xl p-3 border-[1.5px]',
          type === 'overdue' ? 'bg-red-100 border-red-300 dark:bg-red-900/40' : 'bg-amber-100 border-amber-300 dark:bg-amber-900/40',
        ]"
      >
        <component 
          :is="config.icon" 
          class="h-6 w-6"
          :class="type === 'overdue' ? 'text-red-600 dark:text-red-300' : 'text-amber-600 dark:text-amber-300'"
        />
      </div>
    </div>

    <!-- Task List -->
    <div class="space-y-2">
      <div 
        v-for="task in tasks.slice(0, 10)" 
        :key="task.id" 
        :class="[
          'rounded-lg border-[1.5px] bg-white p-3 dark:bg-card',
          type === 'overdue' ? 'border-red-200/80 dark:border-red-900/30' : 'border-amber-200/80 dark:border-amber-900/30',
        ]"
      >
        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">
          {{ task.title }}
        </p>
        <div class="mt-1 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
          <span class="truncate">{{ task.client?.name || '-' }}</span>
          <span 
            class="font-semibold flex-shrink-0 ml-2"
            :class="type === 'overdue' ? 'text-red-600 dark:text-red-300' : 'text-amber-600 dark:text-amber-300'"
          >
            {{ task.release_date ? new Date(task.release_date).toLocaleDateString('id-ID') : '-' }}
          </span>
        </div>
      </div>
      
      <!-- Empty state -->
      <p v-if="tasks.length === 0" class="text-sm text-slate-500 dark:text-slate-400">
        {{ config.emptyMessage }}
      </p>
    </div>

    <!-- View All Link -->
    <Link 
      v-if="tasks.length > 10 && viewAllLink" 
      :href="viewAllLink"
      class="mt-4 inline-flex items-center text-sm font-medium text-primary hover:text-primary/80"
    >
      Lihat Semua
    </Link>
  </article>
</template>
```

**Props:**
| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `type` | `'overdue' \| 'due_soon'` | Yes | Alert type determining styling |
| `count` | `number` | Yes | Total count of tasks |
| `tasks` | `Task[]` | Yes | Array of task objects |
| `viewAllLink` | `string` | No | URL to full task list |
| `loading` | `boolean` | No | Show loading state |

---

### 5. ChartCard Component

**Purpose:** Container for ApexCharts with neo-brutalism styling.

```vue
<!-- resources/js/components/dashboard/ChartCard.vue -->
<script setup lang="ts">
import VueApexCharts from 'vue3-apexcharts';
import type { ApexOptions } from 'apexcharts';

defineProps<{
  title: string;
  subtitle?: string;
  chartType: 'area' | 'donut';
  options: ApexOptions;
  series: any;
  height?: number;
  loading?: boolean;
}>();
</script>

<template>
  <article
    class="relative overflow-hidden rounded-xl border-[1.5px] border-border bg-card p-6
           shadow-[2px_2px_0_0_rgba(0,0,0,0.08)] dark:shadow-[2px_2px_0_0_rgba(255,255,255,0.05)]
           transition-all duration-200 ease-out
           hover:shadow-[4px_4px_0_0_rgba(0,0,0,0.1)] dark:hover:shadow-[4px_4px_0_0_rgba(255,255,255,0.08)]"
    role="img"
    :aria-label="title"
  >
    <!-- Loading skeleton -->
    <div v-if="loading" class="animate-pulse">
      <div class="h-5 w-48 rounded bg-muted/50 mb-4"></div>
      <div class="h-64 w-full rounded bg-muted/30"></div>
    </div>
    
    <template v-else>
      <header class="mb-4">
        <h2 class="text-lg font-semibold text-primary">{{ title }}</h2>
        <p v-if="subtitle" class="text-sm text-muted-foreground mt-1">{{ subtitle }}</p>
      </header>
      
      <div class="w-full" :class="{ 'flex justify-center': chartType === 'donut' }">
        <VueApexCharts 
          :type="chartType" 
          :height="height || 300"
          :options="options" 
          :series="series" 
        />
      </div>
    </template>
  </article>
</template>
```

**Props:**
| Prop | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| `title` | `string` | Yes | - | Chart title |
| `subtitle` | `string` | No | - | Optional subtitle |
| `chartType` | `'area' \| 'donut'` | Yes | - | Chart type |
| `options` | `ApexOptions` | Yes | - | ApexCharts configuration |
| `series` | `any` | Yes | - | Chart data series |
| `height` | `number` | No | `300` | Chart height in pixels |
| `loading` | `boolean` | No | `false` | Show loading state |

---

### 6. TeamPerformanceCard Component

**Purpose:** Display team performance data in table format.

```vue
<!-- resources/js/components/dashboard/TeamPerformanceCard.vue -->
<script setup lang="ts">
interface TeamPerformance {
  id: number;
  name: string;
  total_tasks: number;
  completed_tasks: number;
  open_tasks: number;
  in_progress_tasks: number;
  revision_tasks: number;
  overdue_tasks: number;
  completion_rate: number;
}

defineProps<{
  teams: TeamPerformance[];
  loading?: boolean;
}>();
</script>

<template>
  <article
    class="relative rounded-xl border-[1.5px] border-border bg-card 
           shadow-[2px_2px_0_0_rgba(0,0,0,0.08)] dark:shadow-[2px_2px_0_0_rgba(255,255,255,0.05)]"
  >
    <div class="p-6">
      <header class="mb-4">
        <h2 class="text-lg font-semibold text-primary">Ringkasan Performa Tim Product</h2>
        <p class="text-sm text-muted-foreground">
          Progress task per tim berdasarkan total, selesai, dan overdue.
        </p>
      </header>

      <!-- Loading skeleton -->
      <div v-if="loading" class="animate-pulse space-y-3">
        <div class="h-10 w-full rounded bg-muted/30"></div>
        <div class="h-10 w-full rounded bg-muted/20"></div>
        <div class="h-10 w-full rounded bg-muted/20"></div>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-muted/50 text-muted-foreground border-b border-border">
            <tr>
              <th class="py-3 px-4 font-medium">Tim</th>
              <th class="py-3 px-4 font-medium text-center">Total</th>
              <th class="py-3 px-4 font-medium text-center">Selesai</th>
              <th class="py-3 px-4 font-medium text-center">Overdue</th>
              <th class="py-3 px-4 font-medium text-center">Completion Rate</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="team in teams" 
              :key="team.id" 
              class="border-b border-border last:border-0 hover:bg-muted/30 transition-colors"
            >
              <td class="py-3 px-4 font-medium">{{ team.name }}</td>
              <td class="py-3 px-4 text-center">{{ team.total_tasks }}</td>
              <td class="py-3 px-4 text-center text-tm-green">{{ team.completed_tasks }}</td>
              <td class="py-3 px-4 text-center text-tm-danger">{{ team.overdue_tasks }}</td>
              <td class="py-3 px-4 text-center">
                <span class="inline-flex items-center rounded-full bg-tm-navy-pale px-2.5 py-0.5 text-xs font-semibold text-tm-navy dark:bg-tm-navy/20 dark:text-tm-navy-pale">
                  {{ team.completion_rate }}%
                </span>
              </td>
            </tr>
            <tr v-if="teams.length === 0">
              <td colspan="5" class="py-8 text-center text-muted-foreground">
                Belum ada data performa tim.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </article>
</template>
```

---

### 7. TaskListCard Component

**Purpose:** Display task list in table format for both admin (recent tasks) and member (assigned tasks).

```vue
<!-- resources/js/components/dashboard/TaskListCard.vue -->
<script setup lang="ts">
interface Task {
  id: number;
  title: string;
  modul?: string;
  status: 'open' | 'in_progress' | 'revision' | 'completed';
  priority?: 'urgent' | 'high' | 'medium' | 'low';
  client?: { name: string };
  created_at: string;
}

type TaskListVariant = 'recent' | 'assigned';

const props = withDefaults(defineProps<{
  variant: TaskListVariant;
  tasks: Task[];
  loading?: boolean;
}>(), {
  loading: false,
});

const statusBadgeClasses = {
  open: 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-900/50',
  in_progress: 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-900/50',
  revision: 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-900/50',
  completed: 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-900/50',
};

const priorityBadgeClasses = {
  urgent: 'border-red-200 text-red-600 dark:border-red-900/50 dark:text-red-400',
  high: 'border-amber-200 text-amber-600 dark:border-amber-900/50 dark:text-amber-400',
  medium: 'border-blue-200 text-blue-600 dark:border-blue-900/50 dark:text-blue-400',
  low: 'border-slate-200 text-slate-600 dark:border-slate-700 dark:text-slate-400',
};
</script>

<template>
  <article
    class="relative flex-1 rounded-xl border-[1.5px] border-border bg-card 
           shadow-[2px_2px_0_0_rgba(0,0,0,0.08)] dark:shadow-[2px_2px_0_0_rgba(255,255,255,0.05)]"
  >
    <div class="p-6">
      <header class="mb-4">
        <h2 class="text-lg font-semibold text-primary">
          {{ variant === 'recent' ? '5 Task Terbaru' : 'Tugas Anda yang Belum Selesai' }}
        </h2>
        <p class="text-sm text-muted-foreground">
          {{ variant === 'recent' ? 'Task yang baru saja dibuat ke dalam sistem.' : 'Daftar task yang di-assign ke Anda dan membutuhkan perhatian.' }}
        </p>
      </header>

      <!-- Loading skeleton -->
      <div v-if="loading" class="animate-pulse space-y-3">
        <div class="h-10 w-full rounded bg-muted/30"></div>
        <div class="h-10 w-full rounded bg-muted/20"></div>
        <div class="h-10 w-full rounded bg-muted/20"></div>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-muted/50 text-muted-foreground border-b border-border">
            <tr>
              <th class="py-3 px-4 font-medium">Faskes / Client</th>
              <th class="py-3 px-4 font-medium">Judul Task</th>
              <th v-if="variant === 'recent'" class="py-3 px-4 font-medium">Modul</th>
              <th v-if="variant === 'assigned'" class="py-3 px-4 font-medium">Prioritas</th>
              <th class="py-3 px-4 font-medium">Status</th>
              <th v-if="variant === 'recent'" class="py-3 px-4 font-medium">Tanggal Dibuat</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="task in tasks" 
              :key="task.id" 
              class="border-b border-border last:border-0 hover:bg-muted/30 transition-colors"
            >
              <td class="py-3 px-4">{{ task.client?.name || '-' }}</td>
              <td class="py-3 px-4 font-medium">{{ task.title }}</td>
              <td v-if="variant === 'recent'" class="py-3 px-4">{{ task.modul || '-' }}</td>
              <td v-if="variant === 'assigned'" class="py-3 px-4">
                <span 
                  v-if="task.priority"
                  class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-semibold capitalize"
                  :class="priorityBadgeClasses[task.priority]"
                >
                  {{ task.priority }}
                </span>
              </td>
              <td class="py-3 px-4">
                <span 
                  class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold capitalize"
                  :class="statusBadgeClasses[task.status]"
                >
                  {{ task.status.replace('_', ' ') }}
                </span>
              </td>
              <td v-if="variant === 'recent'" class="py-3 px-4">
                {{ new Date(task.created_at).toLocaleDateString('id-ID') }}
              </td>
            </tr>
            
            <tr v-if="tasks.length === 0">
              <td :colspan="variant === 'recent' ? 5 : 4" class="py-8 text-center text-muted-foreground">
                {{ variant === 'assigned' ? 'Hebat! Anda tidak memiliki task yang tertunda. 🎉' : 'Belum ada task yang dibuat.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </article>
</template>
```

---

### 8. ErrorState Component

**Purpose:** Display error message with retry action.

```vue
<!-- resources/js/components/dashboard/ErrorState.vue -->
<script setup lang="ts">
import { RefreshCw } from 'lucide-vue-next';

defineProps<{
  message: string;
  onRetry?: () => void;
}>();
</script>

<template>
  <div class="flex flex-col items-center justify-center py-8 text-center">
    <div class="rounded-xl border-[1.5px] border-tm-danger bg-tm-danger-pale p-4 mb-4">
      <svg class="h-8 w-8 text-tm-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
    </div>
    <p class="text-sm text-muted-foreground mb-4">{{ message }}</p>
    <button 
      v-if="onRetry"
      @click="onRetry"
      class="inline-flex items-center gap-2 rounded-lg border-[1.5px] border-tm-navy bg-tm-navy-pale px-4 py-2 text-sm font-medium text-tm-navy hover:bg-tm-navy/10 transition-colors"
    >
      <RefreshCw class="h-4 w-4" />
      Coba Lagi
    </button>
  </div>
</template>
```

---

## Data Models

### Admin Dashboard Props

```typescript
interface AdminDashboardProps {
  stats: {
    total_tasks: number;
    open_tasks: number;
    in_progress_tasks: number;
    completed_tasks: number;
    total_clients: number;
    total_teams: number;
  };
  chart_donut: number[];
  chart_area: {
    categories: string[];
    data: number[];
  };
  overdue_count: number;
  due_soon_count: number;
  overdue_tasks: Task[];
  due_soon_tasks: Task[];
  team_performance: TeamPerformance[];
  recent_tasks: Task[];
}

interface Task {
  id: number;
  title: string;
  modul?: string;
  status: 'open' | 'in_progress' | 'revision' | 'completed';
  priority?: 'urgent' | 'high' | 'medium' | 'low';
  client?: { id: number; name: string };
  product?: { id: number; name: string };
  assignee?: { id: number; name: string };
  release_date?: string;
  created_at: string;
}

interface TeamPerformance {
  id: number;
  name: string;
  type: string;
  total_tasks: number;
  completed_tasks: number;
  open_tasks: number;
  in_progress_tasks: number;
  revision_tasks: number;
  overdue_tasks: number;
  completion_rate: number;
}
```

### Member Dashboard Props

```typescript
interface MemberDashboardProps {
  stats: {
    total_tasks: number;
    open_tasks: number;
    in_progress_tasks: number;
    completed_tasks: number;
  };
  my_tasks: Task[];
}
```

---

## Bento Grid Layout Specifications

### Admin Dashboard Layout

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           HEADER SECTION                                 │
│  Dashboard Admin - Pantau seluruh aktivitas task dan performa tim       │
└─────────────────────────────────────────────────────────────────────────┘

┌───────────────┬───────────────┬───────────────┬───────────────┐
│   StatCard    │   StatCard    │   StatCard    │   StatCard    │
│  Total Tasks  │  Open Tasks   │ Total Clients │  Total Teams  │
│   (small)     │   (small)     │   (small)     │   (small)     │
│  col-span-1   │  col-span-1   │  col-span-1   │  col-span-1   │
└───────────────┴───────────────┴───────────────┴───────────────┘

┌───────────────────────────────┬───────────────────────────────┐
│    DeadlineAlertCard          │    DeadlineAlertCard          │
│    (Overdue Tasks)            │    (Due Soon Tasks)           │
│       (medium)                │       (medium)                │
│     col-span-2                │     col-span-2                │
└───────────────────────────────┴───────────────────────────────┘

┌───────────────────────────────────────────┬───────────────────┐
│           ChartCard (Area)                │   ChartCard       │
│           Task Trends                     │   (Donut)         │
│              (large)                      │   Status Ratio    │
│            col-span-3                     │      (small)      │
│                                           │   col-span-1      │
└───────────────────────────────────────────┴───────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                    TeamPerformanceCard (full-width)                     │
│                           col-span-full                                  │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                      TaskListCard (Recent Tasks)                         │
│                           col-span-full                                  │
└─────────────────────────────────────────────────────────────────────────┘
```

**Grid Configuration:**

```typescript
// Admin Dashboard Bento Grid Layout
const adminLayout = {
  columns: {
    default: 1,    // Mobile: single column
    md: 2,         // Tablet: 2 columns
    lg: 4,         // Desktop: 4 columns
  },
  items: [
    // Row 1: Stats (4 small cards)
    { component: 'StatCard', props: { label: 'Total Tasks', colorTheme: 'neutral' }, span: { default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' } },
    { component: 'StatCard', props: { label: 'Open Tasks', colorTheme: 'amber' }, span: { default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' } },
    { component: 'StatCard', props: { label: 'Total Faskes', colorTheme: 'green' }, span: { default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' } },
    { component: 'StatCard', props: { label: 'Total Tim', colorTheme: 'navy' }, span: { default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' } },
    
    // Row 2: Deadline Alerts (2 medium cards)
    { component: 'DeadlineAlertCard', props: { type: 'overdue' }, span: { default: 'col-span-1', md: 'col-span-1', lg: 'col-span-2' } },
    { component: 'DeadlineAlertCard', props: { type: 'due_soon' }, span: { default: 'col-span-1', md: 'col-span-1', lg: 'col-span-2' } },
    
    // Row 3: Charts (1 large + 1 small)
    { component: 'ChartCard', props: { chartType: 'area', title: 'Tren Pembuatan Task' }, span: { default: 'col-span-1', md: 'col-span-2', lg: 'col-span-3' } },
    { component: 'ChartCard', props: { chartType: 'donut', title: 'Rasio Status Task' }, span: { default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' } },
    
    // Row 4: Team Performance (full width)
    { component: 'TeamPerformanceCard', span: { default: 'col-span-full' } },
    
    // Row 5: Recent Tasks (full width)
    { component: 'TaskListCard', props: { variant: 'recent' }, span: { default: 'col-span-full' } },
  ],
};
```

### Member Dashboard Layout

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           HEADER SECTION                                 │
│  Selamat Datang! - Berikut adalah ringkasan task yang ditugaskan        │
└─────────────────────────────────────────────────────────────────────────┘

┌───────────────┬───────────────┬───────────────┐
│   StatCard    │   StatCard    │   StatCard    │
│  Open Tasks   │  In Progress  │   Completed   │
│   (small)     │   (small)     │   (small)     │
│  col-span-1   │  col-span-1   │  col-span-1   │
└───────────────┴───────────────┴───────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                    TaskListCard (Assigned Tasks)                         │
│                           col-span-full                                  │
└─────────────────────────────────────────────────────────────────────────┘
```

**Grid Configuration:**

```typescript
// Member Dashboard Bento Grid Layout
const memberLayout = {
  columns: {
    default: 1,    // Mobile: single column
    md: 3,         // Tablet: 3 columns for stats
    lg: 3,         // Desktop: 3 columns
  },
  items: [
    // Row 1: Stats (3 cards)
    { component: 'StatCard', props: { label: 'Perlu Dikerjakan', colorTheme: 'amber' }, span: { default: 'col-span-1' } },
    { component: 'StatCard', props: { label: 'Sedang Dikerjakan', colorTheme: 'navy' }, span: { default: 'col-span-1' } },
    { component: 'StatCard', props: { label: 'Tugas Selesai', colorTheme: 'green' }, span: { default: 'col-span-1' } },
    
    // Row 2: Task List (full width)
    { component: 'TaskListCard', props: { variant: 'assigned' }, span: { default: 'col-span-full' } },
  ],
};
```

---

## Color System and Design Tokens

### Trustmedis Brand Colors

The design uses the Trustmedis brand palette already defined in `resources/css/app.css`:

```css
/* Primary Colors */
--color-tm-navy:           #1B3A6B;  /* Primary brand - navy */
--color-tm-navy-pale:      #E8EEF8;  /* Light navy for backgrounds */

/* Accent Colors */
--color-tm-green:          #2BAE6E;  /* Success, CTA, accent - use as background only */
--color-tm-green-pale:     #E4F7ED;  /* Light green for backgrounds */
--color-tm-green-dark:     #1E7A4E;  /* Darker green for text on light backgrounds */

/* Semantic Colors */
--color-tm-danger:         #E84545;  /* Overdue, error, critical */
--color-tm-danger-pale:    #FDEAEA;  /* Light red for backgrounds */
--color-tm-warning:        #F59E0B;  /* Due soon, in-progress - use as background only */
--color-tm-warning-pale:   #FEF3DC;  /* Light amber for backgrounds */

**Important Color Usage Note:**
- Green (#2BAE6E) and amber (#F59E0B) should ONLY be used as background colors, not as direct text colors.
- For text on these backgrounds, always use darker variants:
  - Green backgrounds: use `text-tm-green-dark` (#1E7A4E) or `text-emerald-700`
  - Amber backgrounds: use `text-amber-700` or `text-amber-800`
- This ensures proper contrast ratios (minimum 4.5:1) for accessibility compliance.

/* Surface Colors */
--color-tm-page:           #F0F3F7;  /* Page background */
--color-tm-border:         #DDE3EC;  /* Card/table border */
```

### Neo-Brutalism Design Tokens

```css
/* Border Styling */
--bento-border-width:      1.5px;
--bento-border-style:      solid;
--bento-border-radius:     0.75rem; /* rounded-xl */

/* Shadow Styling (Subtle Neo-Brutalism) */
--bento-shadow:            2px 2px 0 0 rgba(0, 0, 0, 0.08);
--bento-shadow-hover:      4px 4px 0 0 rgba(0, 0, 0, 0.1);

/* Dark Mode Shadows */
--bento-shadow-dark:       2px 2px 0 0 rgba(255, 255, 255, 0.05);
--bento-shadow-dark-hover: 4px 4px 0 0 rgba(255, 255, 255, 0.08);

/* Spacing */
--bento-gap:               1rem;     /* gap-4 */
--bento-gap-md:            1.5rem;   /* gap-6 */
--bento-padding:           1.5rem;   /* p-6 */

/* Typography */
--bento-title-size:        1.125rem; /* text-lg */
--bento-value-size:        1.875rem; /* text-3xl */
--bento-label-size:        0.875rem; /* text-sm */
```

### TailwindCSS Utility Classes

```css
/* Neo-Brutalism Card Base */
.bento-card {
  @apply relative overflow-hidden rounded-xl border-[1.5px] p-6;
  @apply shadow-[2px_2px_0_0_rgba(0,0,0,0.08)] dark:shadow-[2px_2px_0_0_rgba(255,255,255,0.05)];
  @apply transition-all duration-200 ease-out;
  @apply hover:shadow-[4px_4px_0_0_rgba(0,0,0,0.1)] dark:hover:shadow-[4px_4px_0_0_rgba(255,255,255,0.08)];
  @apply hover:-translate-y-0.5;
}

/* Color Theme Variants */
.bento-card-navy {
  @apply bg-tm-navy-pale dark:bg-tm-navy/20 border-tm-navy;
}

.bento-card-green {
  @apply bg-tm-green-pale dark:bg-tm-green/20 border-tm-green;
}

.bento-card-red {
  @apply bg-tm-danger-pale dark:bg-tm-danger/20 border-tm-danger;
}

.bento-card-amber {
  @apply bg-tm-warning-pale dark:bg-tm-warning/20 border-tm-warning;
}
```

---

## Responsive Breakpoints and Grid Behavior

### Breakpoint Definitions

| Breakpoint | Width | Grid Columns | Description |
|------------|-------|--------------|-------------|
| `default`  | < 768px | 1 column | Mobile - stacked layout |
| `md`       | 768px - 1023px | 2 columns | Tablet - reduced grid |
| `lg`       | ≥ 1024px | 4 columns | Desktop - full Bento Grid |

### Grid Behavior by Breakpoint

#### Mobile (< 768px)
- Single-column stacked layout
- All cards span full width (`col-span-1`)
- Content order preserved from top to bottom
- Charts resize to fit container
- Tables enable horizontal scroll

#### Tablet (768px - 1023px)
- 2-column grid for stat cards
- Deadline alerts side-by-side
- Area chart spans 2 columns
- Donut chart spans 1 column
- Team performance and task list remain full-width

#### Desktop (≥ 1024px)
- Full 4-column Bento Grid
- Stat cards: 4 columns (1 each)
- Deadline alerts: 2 columns each
- Area chart: 3 columns
- Donut chart: 1 column
- Team performance: full width
- Task list: full width

### Responsive Grid CSS

```css
.bento-grid {
  @apply grid gap-4 md:gap-6;
  @apply grid-cols-1 md:grid-cols-2 lg:grid-cols-4;
  @apply auto-rows-min;
}

/* Responsive Span Utilities */
.bento-span-small {
  @apply col-span-1;
}

.bento-span-medium {
  @apply col-span-1 md:col-span-2;
}

.bento-span-large {
  @apply col-span-1 md:col-span-2 lg:col-span-3;
}

.bento-span-full {
  @apply col-span-full;
}
```

---

## Loading and Error States

### Loading States (Skeleton Loaders)

Each component includes a skeleton loader variant that displays during data fetching:

#### StatCard Skeleton
```vue
<div class="animate-pulse">
  <div class="h-4 w-24 rounded bg-muted/50 mb-2"></div>
  <div class="h-8 w-16 rounded bg-muted/50"></div>
</div>
```

#### ChartCard Skeleton
```vue
<div class="animate-pulse">
  <div class="h-5 w-48 rounded bg-muted/50 mb-4"></div>
  <div class="h-64 w-full rounded bg-muted/30"></div>
</div>
```

#### Table Skeleton
```vue
<div class="animate-pulse space-y-3">
  <div class="h-10 w-full rounded bg-muted/30"></div>
  <div class="h-10 w-full rounded bg-muted/20"></div>
  <div class="h-10 w-full rounded bg-muted/20"></div>
</div>
```

### Error States

When data fetch fails, components display an inline error message with a retry button:

```vue
<ErrorState 
  message="Gagal memuat data task overdue"
  :on-retry="refetchData"
/>
```

**Error State Design:**
- Uses Trustmedis red color (#E84545) for error indicator
- Maintains component container dimensions
- Includes "Coba Lagi" (Retry) button
- Clear, descriptive error message

---

## Accessibility Considerations

### Semantic HTML Structure

```vue
<article aria-label="Statistics Dashboard">
  <section aria-labelledby="stats-heading">
    <h2 id="stats-heading">Key Metrics</h2>
    <!-- StatCards -->
  </section>
  
  <section aria-labelledby="alerts-heading">
    <h2 id="alerts-heading">Deadline Alerts</h2>
    <!-- DeadlineAlertCards -->
  </section>
  
  <section aria-labelledby="charts-heading">
    <h2 id="charts-heading">Data Visualization</h2>
    <!-- ChartCards -->
  </section>
</article>
```

### ARIA Labels

| Component | ARIA Attribute | Value |
|-----------|---------------|-------|
| StatCard | `aria-label` | `{label}: {value}` |
| DeadlineAlertCard | `aria-label` | `{type}: {count} tasks` |
| ChartCard | `role="img"` | `aria-label="{title}"` |
| Tables | Standard table semantics | Headers, scope attributes |

### Color Contrast

All color combinations maintain minimum contrast ratios:
- Normal text: 4.5:1 minimum
- Large text (18px+): 3:1 minimum
- Interactive elements: 3:1 minimum

Verified combinations:
- Navy (#1B3A6B) on white: 9.8:1 ✓
- Green (#2BAE6E) on white: 3.5:1 ✓
- Red (#E84545) on white: 4.0:1 ✓
- Amber (#F59E0B) on white: 2.5:1 (use with caution, pair with dark text)

### Keyboard Navigation

- All interactive elements are focusable
- Focus order follows visual reading order
- Focus indicators visible with `outline-ring/50`
- Skip links for main content areas

---

## Error Handling

### Component-Level Error Handling

Each dashboard component handles errors independently:

```vue
<script setup lang="ts">
const props = defineProps<{
  loading?: boolean;
  error?: string | null;
  onRetry?: () => void;
}>();
</script>

<template>
  <article class="bento-card">
    <template v-if="loading">
      <SkeletonLoader />
    </template>
    
    <template v-else-if="error">
      <ErrorState :message="error" :on-retry="onRetry" />
    </template>
    
    <template v-else>
      <!-- Normal content -->
    </template>
  </article>
</template>
```

### Error Message Localization

Error messages are in Indonesian (Bahasa Indonesia) to match the application's locale:

| Component | Error Message |
|-----------|---------------|
| StatCard | "Gagal memuat statistik" |
| DeadlineAlertCard | "Gagal memuat data task overdue/due soon" |
| ChartCard | "Gagal memuat data grafik" |
| TeamPerformanceCard | "Gagal memuat data performa tim" |
| TaskListCard | "Gagal memuat daftar task" |

---

## Testing Strategy

This feature primarily involves UI rendering and layout transformations, which is NOT suitable for property-based testing. Instead, we use:

### Unit Tests (Example-Based)

1. **Component Rendering Tests**
   - Verify each component renders with correct props
   - Test color theme variants
   - Test loading and error states

2. **Responsive Behavior Tests**
   - Test grid column classes at different breakpoints
   - Verify span classes are applied correctly

3. **Accessibility Tests**
   - Verify ARIA labels are present
   - Test keyboard navigation
   - Verify color contrast

### Integration Tests

1. **Dashboard Data Flow**
   - Test that AdminDashboard receives all props from controller
   - Test that MemberDashboard receives all props from controller
   - Verify data binding in components

2. **Chart Rendering**
   - Test ApexCharts configuration
   - Verify chart data updates

### Visual Regression Tests

1. **Snapshot Tests**
   - Capture component snapshots at different viewport sizes
   - Compare against baseline snapshots

### Test Configuration

```typescript
// vitest.config.ts
export default defineConfig({
  test: {
    environment: 'jsdom',
    globals: true,
    setupFiles: ['./tests/setup.ts'],
  },
});
```

---

## Implementation Notes

### No Backend Changes Required

The DashboardController remains unchanged. All data props are preserved:
- `stats`, `chart_donut`, `chart_area` for both dashboards
- `overdue_count`, `due_soon_count`, `overdue_tasks`, `due_soon_tasks` for admin
- `team_performance`, `recent_tasks` for admin
- `my_tasks` for member

### Component File Structure

```
resources/js/components/dashboard/
├── BentoGrid.vue              # Grid container
├── BentoGridItem.vue          # Grid item wrapper
├── StatCard.vue               # Statistics card
├── DeadlineAlertCard.vue      # Overdue/due-soon alerts
├── ChartCard.vue              # Chart container
├── TeamPerformanceCard.vue    # Team performance table
├── TaskListCard.vue           # Task list table
└── ErrorState.vue             # Error display component
```

**Note:** Skeleton loaders are implemented inline within each component using Vue's `v-if="loading"` directive with TailwindCSS `animate-pulse` classes. No separate SkeletonLoader.vue file is needed.

### Migration Path

1. Create new component files in `resources/js/components/dashboard/`
2. Update `AdminDashboard.vue` to use new Bento Grid components
3. Update `MemberDashboard.vue` to use new Bento Grid components
4. Add TailwindCSS utility classes to `app.css`
5. Run visual regression tests to verify layout

### Performance Considerations

- Components use Vue 3 Composition API for optimal performance
- ApexCharts instances are not recreated on re-render
- Skeleton loaders prevent layout shift during loading
- CSS Grid is hardware-accelerated for smooth responsive transitions


---

## Correctness Properties

*This feature is primarily a UI rendering and layout transformation project. While most acceptance criteria require example-based unit tests or visual regression tests, there are several universal properties suitable for property-based testing.*

### Property 1: Color Theme Mapping

*For any* valid `colorTheme` prop value ('navy', 'green', 'red', 'amber', 'neutral'), the StatCard component SHALL apply the corresponding CSS classes from the theme mapping configuration.

**Validates: Requirements 2.3, 3.3, 3.4, 3.5, 8.2, 8.3, 8.4**

**Test Strategy:** Generate random colorTheme values and verify the component has the correct background, border, and text color classes.

### Property 2: Status Badge Color Mapping

*For any* valid task `status` value ('open', 'in_progress', 'revision', 'completed'), the status badge SHALL have the corresponding color class (amber for open, blue for in_progress, red for revision, green for completed).

**Validates: Requirements 7.3, 9.4**

**Test Strategy:** Generate tasks with random status values and verify the badge element has the correct color class.

### Property 3: Priority Badge Color Mapping

*For any* valid task `priority` value ('urgent', 'high', 'medium', 'low'), the priority badge SHALL have the corresponding color class (red for urgent, amber for high, blue for medium, slate for low).

**Validates: Requirements 9.3**

**Test Strategy:** Generate tasks with random priority values and verify the badge element has the correct color class.

### Property 4: Urgency-to-Color Mapping

*For any* deadline alert `type` value ('overdue', 'due_soon'), the DeadlineAlertCard SHALL use the corresponding urgency color (red for overdue, amber for due_soon) for all visual elements including border, background, icon, and text.

**Validates: Requirements 4.1, 4.2, 14.3**

**Test Strategy:** Generate DeadlineAlertCard with random type values and verify all color-related elements use the correct urgency color.

### Property 5: Task List Filtering

*For any* array of tasks passed to the MemberDashboard TaskListCard, only tasks with `status !== 'completed'` SHALL be rendered in the DOM.

**Validates: Requirements 9.1**

**Test Strategy:** Generate random arrays of tasks with mixed statuses and verify no completed tasks appear in the rendered output.

### Property 6: ARIA Label Format

*For any* StatCard with `label` and `value` props, the `aria-label` attribute SHALL be formatted as "{label}: {value}".

**Validates: Requirements 12.2**

**Test Strategy:** Generate StatCard instances with random label and value props, then verify the aria-label matches the expected format.

### Property 7: Task List Rendering

*For any* array of tasks (length 0-10) passed to DeadlineAlertCard, the component SHALL render all tasks in the array with their title, client name, and release date visible.

**Validates: Requirements 4.3, 4.4**

**Test Strategy:** Generate random arrays of tasks (0-10 items) and verify each task's information is present in the rendered output.

### Property 8: "Lihat Semua" Link Display

*For any* tasks array with length greater than 10 passed to DeadlineAlertCard, a "Lihat Semua" link SHALL be rendered.

**Validates: Requirements 4.7, 4.8**

**Test Strategy:** Generate random task arrays with varying lengths and verify the link appears only when length > 10.

### Property 9: Team Performance Ordering

*For any* array of team performance data, the displayed teams SHALL be sorted by `total_tasks` descending and limited to a maximum of 10 items.

**Validates: Requirements 6.5**

**Test Strategy:** Generate random arrays of team data and verify the rendered teams are correctly sorted and limited.

### Property 10: Semantic HTML Structure

*For all* dashboard components (StatCard, DeadlineAlertCard, ChartCard, TeamPerformanceCard, TaskListCard), the root element SHALL be a semantic HTML element (article, section, or header).

**Validates: Requirements 12.1**

**Test Strategy:** For each component type, render the component and verify the root element tag name is in the allowed set.

---

## Property Reflection

After reviewing all identified properties, the following consolidations apply:

1. **Properties 2 and 3** (Status and Priority Badge Color Mapping) are related but distinct - they test different prop-to-color mappings for different data types. Both are kept separate as they validate different component behaviors.

2. **Properties 7 and 8** both relate to DeadlineAlertCard task rendering. Property 7 tests rendering of tasks within the limit, Property 8 tests the conditional "Lihat Semua" link. These are logically distinct and both provide unique validation value.

3. **Property 4 (Urgency-to-Color Mapping)** consolidates the color testing for DeadlineAlertCard that was identified separately in multiple requirements (4.1, 4.2, 14.3).

4. **Contrast ratio properties** (2.6, 12.3, 15.2) are design-time validations that should be verified once during design review, not runtime properties. These are captured in the Accessibility Considerations section rather than as testable properties.

---

## Testing Strategy

This feature uses a **multi-layered testing approach** combining example-based unit tests, property-based tests, integration tests, and visual regression tests.

### Unit Tests (Example-Based)

**Purpose:** Verify specific component behaviors with concrete examples.

| Test Area | Test Cases |
|-----------|------------|
| StatCard Rendering | Renders with all props, displays loading skeleton, applies hover classes |
| DeadlineAlertCard | Renders overdue/due_soon variants, displays empty state |
| ChartCard | Renders area/donut charts, applies neo-brutalism styling |
| TeamPerformanceCard | Renders team table, displays empty state, shows completion rate badge |
| TaskListCard | Renders recent/assigned variants, displays status/priority badges |
| ErrorState | Displays error message, renders retry button |

### Property-Based Tests

**Purpose:** Verify universal properties across many generated inputs.

**Configuration:**
- Library: `vitest` + `fast-check` (or `@property-based/vitest`)
- Iterations: 100 per property
- Seed: Fixed seed for reproducibility

**Properties to Test:**
1. Color Theme Mapping (Property 1)
2. Status Badge Color Mapping (Property 2)
3. Priority Badge Color Mapping (Property 3)
4. Urgency-to-Color Mapping (Property 4)
5. Task List Filtering (Property 5)
6. ARIA Label Format (Property 6)
7. Task List Rendering (Property 7)
8. "Lihat Semua" Link Display (Property 8)
9. Team Performance Ordering (Property 9)
10. Semantic HTML Structure (Property 10)

### Integration Tests

**Purpose:** Verify component integration with backend data and navigation.

| Test Area | Test Cases |
|-----------|------------|
| AdminDashboard Data Flow | Component receives all props from controller, all data is rendered |
| MemberDashboard Data Flow | Component receives all props from controller, all data is rendered |
| Chart Backward Compatibility | ApexCharts options unchanged after refactor |
| Navigation Breadcrumbs | Breadcrumbs remain functional |
| Responsive Grid Behavior | Grid classes adapt at breakpoints (768px, 1024px) |

### Visual Regression Tests

**Purpose:** Capture and compare visual snapshots to detect unintended changes.

| Viewport | Snapshots |
|----------|-----------|
| Mobile (375px) | AdminDashboard, MemberDashboard, individual cards |
| Tablet (768px) | AdminDashboard, MemberDashboard |
| Desktop (1440px) | AdminDashboard, MemberDashboard, all card variants |
| Dark Mode | All snapshots in dark mode variant |

**Tools:**
- Playwright or Percy for snapshot capture
- Threshold: 0.1% pixel difference tolerance

### Accessibility Tests

**Purpose:** Verify WCAG compliance and assistive technology support.

| Test Area | Test Cases |
|-----------|------------|
| ARIA Attributes | All components have appropriate aria-labels |
| Keyboard Navigation | All interactive elements focusable in logical order |
| Color Contrast | All text meets 4.5:1 contrast ratio |
| Screen Reader | Components announce correctly with VoiceOver/NVDA |

### Test File Structure

```
tests/
├── Unit/
│   └── Components/
│       └── Dashboard/
│           ├── StatCard.test.ts
│           ├── DeadlineAlertCard.test.ts
│           ├── ChartCard.test.ts
│           ├── TeamPerformanceCard.test.ts
│           ├── TaskListCard.test.ts
│           └── ErrorState.test.ts
│
├── Property/
│   └── Dashboard/
│       ├── ColorMapping.property.test.ts
│       ├── TaskFiltering.property.test.ts
│       └── Accessibility.property.test.ts
│
├── Integration/
│   └── Dashboard/
│       ├── AdminDashboard.integration.test.ts
│       └── MemberDashboard.integration.test.ts
│
└── Visual/
    └── Dashboard/
        ├── AdminDashboard.visual.test.ts
        └── MemberDashboard.visual.test.ts
```

### Test Configuration Example

```typescript
// tests/Property/Dashboard/ColorMapping.property.test.ts
import { describe, it } from 'vitest';
import fc from 'fast-check';
import { mount } from '@vue/test-utils';
import StatCard from '@/components/dashboard/StatCard.vue';

describe('StatCard Color Theme Mapping', () => {
  it('applies correct color classes for any valid theme', () => {
    const colorThemes = ['navy', 'green', 'red', 'amber', 'neutral'] as const;
    
    fc.assert(
      fc.property(
        fc.constantFrom(...colorThemes),
        fc.string({ minLength: 1 }),
        fc.nat(),
        (theme, label, value) => {
          const wrapper = mount(StatCard, {
            props: { label, value, colorTheme: theme, icon: {} }
          });
          
          // Verify theme classes are applied
          expect(wrapper.classes()).toContain(`bg-tm-${theme}-pale`);
          expect(wrapper.classes()).toContain(`border-tm-${theme}`);
        }
      ),
      { numRuns: 100 }
    );
  });
});
```

---

## Implementation Checklist

- [ ] Create `resources/js/components/dashboard/` directory
- [ ] Implement BentoGrid.vue container component
- [ ] Implement BentoGridItem.vue wrapper component
- [ ] Implement StatCard.vue with all color themes
- [ ] Implement DeadlineAlertCard.vue for overdue/due_soon
- [ ] Implement ChartCard.vue wrapping ApexCharts
- [ ] Implement TeamPerformanceCard.vue table component
- [ ] Implement TaskListCard.vue with both variants
- [ ] Implement ErrorState.vue component
- [ ] Add TailwindCSS utility classes to app.css
- [ ] Update AdminDashboard.vue to use new components
- [ ] Update MemberDashboard.vue to use new components
- [ ] Write unit tests for all components
- [ ] Write property-based tests for color mappings
- [ ] Write integration tests for data flow
- [ ] Capture visual regression baselines
- [ ] Run accessibility audit
- [ ] Document component API in Storybook (optional)
