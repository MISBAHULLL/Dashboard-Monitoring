<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { AlertCircle, Clock, ListTodo } from 'lucide-vue-next';

// Import Bento Grid components
import BentoGrid from '@/components/dashboard/BentoGrid.vue';
import BentoGridItem from '@/components/dashboard/BentoGridItem.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import TaskListCard from '@/components/dashboard/TaskListCard.vue';
import { dashboard } from '@/routes';
import type { MemberDashboardProps } from '@/types/dashboard';

// 1. Menerima data dari Controller (Sama dengan admin, tapi isinya berbeda sedikit)
defineProps<MemberDashboardProps>();

// 2. Mengatur Breadcrumbs (Navigasi Header)
defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Member Dashboard" />

    <div 
        id="main-content"
        class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-8"
        role="main"
        aria-label="Dashboard Member"
    >
        
        <!-- Header -->
        <header>
            <h1 class="text-3xl font-bold tracking-tight text-primary">Selamat Datang!</h1>
            <p class="text-muted-foreground mt-1">Berikut adalah ringkasan task yang ditugaskan kepada Anda.</p>
        </header>

        <!-- Bento Grid Layout -->
        <BentoGrid :columns="{ default: 1, md: 3, lg: 3 }">
            
            <!-- Row 1: Stats (3 cards) -->
            <BentoGridItem :span="{ default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' }">
                <StatCard 
                    label="Perlu Dikerjakan" 
                    :value="stats.open_tasks" 
                    :icon="AlertCircle" 
                    colorTheme="amber" 
                />
            </BentoGridItem>

            <BentoGridItem :span="{ default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' }">
                <StatCard 
                    label="Sedang Dikerjakan" 
                    :value="stats.in_progress_tasks" 
                    :icon="Clock" 
                    colorTheme="navy" 
                />
            </BentoGridItem>

            <BentoGridItem :span="{ default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' }">
                <StatCard 
                    label="Tugas Selesai" 
                    :value="stats.completed_tasks" 
                    :icon="ListTodo" 
                    colorTheme="green" 
                />
            </BentoGridItem>

            <!-- Row 2: Task List (full width) -->
            <BentoGridItem :span="{ default: 'col-span-full' }">
                <TaskListCard variant="assigned" :tasks="my_tasks" />
            </BentoGridItem>

        </BentoGrid>

    </div>
</template>
