<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Activity, Filter, RotateCcw, Clock, ArrowDownLeft, Plus, Pencil, Trash2, AlertCircle, LogIn } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    logs: {
        data: Array<{
            id: number;
            user_id: number;
            user: { name: string } | null;
            action: string;
            module: string;
            target_title: string | null;
            description: string | null;
            old_values: Record<string, any> | null;
            new_values: Record<string, any> | null;
            created_at: string;
        }>;
        links: Array<any>;
        total: number;
        current_page: number;
        per_page: number;
    };
    filters: {
        action?: string;
        module?: string;
        search?: string;
    };
    actions: string[];
    modules: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Audit Trail', href: '#' },
        ],
    },
});

const filterForm = ref({
    action: props.filters.action || '',
    module: props.filters.module || '',
    search: props.filters.search || '',
});

watch(filterForm, (newVal) => {
    router.get('/activity-logs', newVal, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, { deep: true });

const resetFilter = () => {
    filterForm.value = { action: '', module: '', search: '' };
};

function getActionIcon(action: string) {
    switch (action) {
        case 'created': return Plus;
        case 'updated': return Pencil;
        case 'deleted': return Trash2;
        case 'status_changed': return AlertCircle;
        case 'logged_in': return LogIn;
        default: return Activity;
    }
}

function getActionColor(action: string): string {
    switch (action) {
        case 'created': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'updated': return 'bg-blue-100 text-blue-700 border-blue-200';
        case 'deleted': return 'bg-red-100 text-red-700 border-red-200';
        case 'status_changed': return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'logged_in': return 'bg-slate-100 text-slate-700 border-slate-200';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
}

function getModuleLabel(module: string): string {
    const labels: Record<string, string> = {
        task: 'Task',
        team: 'Tim',
        client: 'Faskes',
        document: 'Dokumen',
        user: 'Pengguna',
        system: 'Sistem',
    };
    return labels[module] || module;
}

function formatDate(iso: string): string {
    return new Date(iso).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head title="Audit Trail" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-primary flex items-center gap-2">
                    <Activity class="h-8 w-8 text-indigo-600" />
                    Audit Trail
                </h1>
                <p class="text-muted-foreground mt-1">Riwayat perubahan dan aktivitas di dalam sistem.</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex items-center gap-2 flex-1">
                <Filter class="h-4 w-4 text-slate-400" />
                <select v-model="filterForm.action" class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <option value="">Semua Aksi</option>
                    <option v-for="a in actions" :key="a" :value="a">{{ a.replace('_', ' ') }}</option>
                </select>
                <select v-model="filterForm.module" class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <option value="">Semua Modul</option>
                    <option v-for="m in modules" :key="m" :value="m">{{ getModuleLabel(m) }}</option>
                </select>
                <Input v-model="filterForm.search" type="text" placeholder="Cari deskripsi..." class="h-9 text-xs" />
            </div>
            <button @click="resetFilter" class="text-xs font-semibold text-slate-500 hover:text-sky-600 flex items-center gap-1.5 transition-colors">
                <RotateCcw class="h-3.5 w-3.5" /> Reset
            </button>
        </div>

        <!-- Timeline -->
        <div class="relative flex-1">
            <div class="absolute left-[19px] top-0 bottom-0 w-px bg-slate-200 dark:bg-slate-700"></div>

            <div class="space-y-6">
                <div v-for="log in logs.data" :key="log.id" class="relative pl-12">
                    <!-- Dot -->
                    <div class="absolute left-3 top-1 flex h-8 w-8 items-center justify-center rounded-full border"
                         :class="getActionColor(log.action)">
                        <component :is="getActionIcon(log.action)" class="h-3.5 w-3.5" />
                    </div>

                    <!-- Card -->
                    <div class="rounded-xl border border-slate-200 bg-card p-4 shadow-sm dark:border-slate-700">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-slate-100">
                                    <span>{{ log.user?.name ?? 'System' }}</span>
                                    <span class="text-slate-300">·</span>
                                    <span class="inline-flex items-center rounded border px-1.5 py-0 text-[10px] font-bold uppercase tracking-wide"
                                          :class="getActionColor(log.action)">
                                        {{ log.action.replace('_', ' ') }}
                                    </span>
                                    <span class="inline-flex items-center rounded bg-slate-100 px-1.5 py-0 text-[10px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        {{ getModuleLabel(log.module) }}
                                    </span>
                                </div>

                                <p v-if="log.description" class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                                    {{ log.description }}
                                </p>
                                <p v-else-if="log.target_title" class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                                    Target: {{ log.target_title }}
                                </p>

                                <!-- Diff preview -->
                                <div v-if="log.old_values || log.new_values" class="mt-3 space-y-1">
                                    <div v-if="log.old_values" class="rounded-md bg-red-50 px-2 py-1 text-[11px] text-red-700 dark:bg-red-950/30 dark:text-red-300">
                                        <ArrowDownLeft class="inline h-3 w-3 mr-1" />
                                        {{ JSON.stringify(log.old_values, null, 0).slice(0, 200) }}
                                    </div>
                                    <div v-if="log.new_values" class="rounded-md bg-emerald-50 px-2 py-1 text-[11px] text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300">
                                        <Plus class="inline h-3 w-3 mr-1" />
                                        {{ JSON.stringify(log.new_values, null, 0).slice(0, 200) }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 text-[11px] text-slate-400 dark:text-slate-500">
                                <Clock class="h-3 w-3" />
                                {{ formatDate(log.created_at) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="logs.data.length === 0" class="py-20 text-center text-slate-400">
                <Activity class="mx-auto mb-3 h-10 w-10 opacity-40" />
                <p class="text-sm">Belum ada aktivitas yang tercatat.</p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="logs.links && logs.links.length > 3" class="flex items-center justify-end gap-1">
            <template v-for="(link, key) in logs.links" :key="key">
                <a v-if="link.url"
                   :href="link.url"
                   class="min-w-[2rem] h-7 flex items-center justify-center rounded-md text-xs font-bold transition-all"
                   :class="link.active ? 'bg-sky-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300'"
                   v-html="link.label" />
                <span v-else class="min-w-[2rem] h-7 flex items-center justify-center rounded-md text-xs font-bold text-slate-300 cursor-not-allowed" v-html="link.label"></span>
            </template>
        </div>
    </div>
</template>
