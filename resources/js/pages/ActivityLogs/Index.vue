<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Activity, Filter, RotateCcw, Clock, ArrowDownLeft, Plus, Pencil, Trash2, AlertCircle, LogIn, Search } from 'lucide-vue-next';
import { dashboard } from '@/routes';
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
        case 'created': return 'bg-tm-green-pale text-[#1E8A54] border-[#2BAE6E]/30';
        case 'updated': return 'bg-tm-navy-pale text-tm-navy border-[#1B3A6B]/20';
        case 'deleted': return 'bg-tm-danger-pale text-[#A32D2D] border-[#E84545]/30';
        case 'status_changed': return 'bg-tm-warning-pale text-[#92610A] border-[#F59E0B]/30';
        case 'logged_in': return 'bg-slate-100 text-slate-600 border-slate-200';
        default: return 'bg-slate-100 text-slate-600 border-slate-200';
    }
}

function getDotColor(action: string): string {
    switch (action) {
        case 'created': return 'bg-tm-green border-[#2BAE6E] text-white';
        case 'updated': return 'bg-tm-navy border-[#1B3A6B] text-white';
        case 'deleted': return 'bg-tm-danger border-[#E84545] text-white';
        case 'status_changed': return 'bg-[#F59E0B] border-[#D97706] text-white';
        case 'logged_in': return 'bg-slate-400 border-slate-500 text-white';
        default: return 'bg-slate-400 border-slate-500 text-white';
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

function getUserInitials(name: string): string {
    const parts = name.trim().split(/\s+/);
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}
</script>

<template>
    <Head title="Audit Trail" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-tm-navy flex items-center gap-3 dark:text-foreground">
                    <div class="flex h-10 w-10 items-center justify-center rounded-[12px] border-2 border-black bg-tm-navy-pale shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] dark:bg-tm-navy dark:border-border">
                        <Activity class="h-5 w-5 text-tm-navy dark:text-white" />
                    </div>
                    Audit Trail
                </h1>
                <p class="text-sm text-tm-text-secondary mt-1.5 ml-[52px] dark:text-muted-foreground">
                    Riwayat perubahan dan aktivitas di dalam sistem.
                </p>
            </div>
            <div class="text-xs font-medium text-tm-text-muted dark:text-muted-foreground">
                Total: <span class="font-bold text-tm-navy dark:text-foreground">{{ logs.total }}</span> aktivitas
            </div>
        </div>

        <!-- Filters -->
        <div class="rounded-[14px] border-2 border-black bg-white p-4 shadow-[2px_3px_0px_0px_rgba(0,0,0,0.8)] dark:bg-card dark:border-border dark:shadow-none">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="flex items-center gap-2.5 flex-1 flex-wrap">
                    <Filter class="h-4 w-4 text-tm-navy dark:text-muted-foreground" />
                    <select
                        v-model="filterForm.action"
                        class="h-9 rounded-[10px] border-2 border-tm-border bg-white px-3 text-xs font-semibold text-tm-navy transition-all focus:border-tm-green focus:outline-none focus:ring-2 focus:ring-tm-green/20 dark:border-border dark:bg-background dark:text-foreground"
                    >
                        <option value="">Semua Aksi</option>
                        <option v-for="a in actions" :key="a" :value="a">{{ a.replace('_', ' ') }}</option>
                    </select>
                    <select
                        v-model="filterForm.module"
                        class="h-9 rounded-[10px] border-2 border-tm-border bg-white px-3 text-xs font-semibold text-tm-navy transition-all focus:border-tm-green focus:outline-none focus:ring-2 focus:ring-tm-green/20 dark:border-border dark:bg-background dark:text-foreground"
                    >
                        <option value="">Semua Modul</option>
                        <option v-for="m in modules" :key="m" :value="m">{{ getModuleLabel(m) }}</option>
                    </select>
                    <div class="relative flex-1 min-w-[180px]">
                        <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-tm-text-muted" />
                        <Input
                            v-model="filterForm.search"
                            type="text"
                            placeholder="Cari deskripsi..."
                            class="h-9 pl-8 text-xs rounded-[10px] border-2 border-tm-border focus:border-tm-green focus:ring-2 focus:ring-tm-green/20"
                        />
                    </div>
                </div>
                <button
                    @click="resetFilter"
                    class="text-xs font-bold text-tm-text-muted hover:text-tm-danger flex items-center gap-1.5 transition-colors shrink-0"
                >
                    <RotateCcw class="h-3.5 w-3.5" /> Reset
                </button>
            </div>
        </div>

        <!-- Timeline -->
        <div class="relative flex-1">
            <!-- Timeline line -->
            <div class="absolute left-[19px] top-0 bottom-0 w-[2px] rounded-full bg-gradient-to-b from-tm-navy/30 via-tm-green/20 to-tm-navy/10 dark:from-border dark:via-border dark:to-transparent"></div>

            <div class="space-y-4">
                <div
                    v-for="log in logs.data"
                    :key="log.id"
                    class="relative pl-14 group"
                >
                    <!-- Timeline Dot -->
                    <div
                        class="absolute left-2 top-4 flex h-[18px] w-[18px] items-center justify-center rounded-full border-2 shadow-sm transition-transform group-hover:scale-110"
                        :class="getDotColor(log.action)"
                    >
                        <component :is="getActionIcon(log.action)" class="h-2.5 w-2.5" />
                    </div>

                    <!-- Card -->
                    <div class="rounded-[14px] border-2 border-black bg-white p-4 shadow-[2px_3px_0px_0px_rgba(0,0,0,0.8)] transition-all duration-200 group-hover:-translate-y-0.5 group-hover:shadow-[3px_5px_0px_0px_rgba(0,0,0,0.8)] dark:bg-card dark:border-border dark:shadow-none dark:group-hover:shadow-none dark:group-hover:border-tm-green/40">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <!-- User + badges row -->
                                <div class="flex items-center gap-2 flex-wrap">
                                    <!-- User avatar -->
                                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-tm-navy to-tm-green text-[10px] font-bold text-white shrink-0">
                                        {{ getUserInitials(log.user?.name ?? 'SY') }}
                                    </div>
                                    <span class="text-sm font-bold text-tm-navy dark:text-foreground">
                                        {{ log.user?.name ?? 'System' }}
                                    </span>

                                    <!-- Action badge -->
                                    <span
                                        class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                                        :class="getActionColor(log.action)"
                                    >
                                        {{ log.action.replace('_', ' ') }}
                                    </span>

                                    <!-- Module badge -->
                                    <span class="inline-flex items-center rounded-full bg-tm-navy-pale px-2 py-0.5 text-[10px] font-semibold text-tm-navy dark:bg-secondary dark:text-secondary-foreground">
                                        {{ getModuleLabel(log.module) }}
                                    </span>
                                </div>

                                <!-- Description -->
                                <p v-if="log.description" class="mt-2 text-sm text-tm-text-secondary leading-relaxed dark:text-muted-foreground">
                                    {{ log.description }}
                                </p>
                                <p v-else-if="log.target_title" class="mt-2 text-sm text-tm-text-secondary dark:text-muted-foreground">
                                    Target: <span class="font-medium text-tm-navy dark:text-foreground">{{ log.target_title }}</span>
                                </p>

                                <!-- Diff preview -->
                                <div v-if="log.old_values || log.new_values" class="mt-3 space-y-1.5">
                                    <div v-if="log.old_values" class="flex items-start gap-2 rounded-[8px] border-l-[3px] border-tm-danger bg-tm-danger-pale/60 px-3 py-2 dark:bg-red-950/20 dark:border-red-500">
                                        <ArrowDownLeft class="h-3 w-3 mt-0.5 text-tm-danger shrink-0" />
                                        <code class="text-[11px] font-mono text-[#A32D2D] break-all dark:text-red-300">
                                            {{ JSON.stringify(log.old_values, null, 0).slice(0, 200) }}
                                        </code>
                                    </div>
                                    <div v-if="log.new_values" class="flex items-start gap-2 rounded-[8px] border-l-[3px] border-tm-green bg-tm-green-pale/60 px-3 py-2 dark:bg-emerald-950/20 dark:border-emerald-500">
                                        <Plus class="h-3 w-3 mt-0.5 text-tm-green shrink-0" />
                                        <code class="text-[11px] font-mono text-[#1E8A54] break-all dark:text-emerald-300">
                                            {{ JSON.stringify(log.new_values, null, 0).slice(0, 200) }}
                                        </code>
                                    </div>
                                </div>
                            </div>

                            <!-- Timestamp -->
                            <div class="flex items-center gap-1.5 text-[11px] font-medium text-tm-text-muted whitespace-nowrap shrink-0 dark:text-muted-foreground">
                                <Clock class="h-3 w-3" />
                                {{ formatDate(log.created_at) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="logs.data.length === 0" class="py-20 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-[14px] border-2 border-black bg-tm-navy-pale shadow-[2px_3px_0px_0px_rgba(0,0,0,0.8)] dark:bg-card dark:border-border">
                    <Activity class="h-7 w-7 text-tm-navy dark:text-muted-foreground" />
                </div>
                <p class="text-sm font-semibold text-tm-text-secondary dark:text-muted-foreground">Belum ada aktivitas yang tercatat.</p>
                <p class="text-xs text-tm-text-muted mt-1 dark:text-muted-foreground">Aktivitas akan muncul saat ada perubahan di sistem.</p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="logs.links && logs.links.length > 3" class="flex items-center justify-end gap-1.5">
            <template v-for="(link, key) in logs.links" :key="key">
                <a
                    v-if="link.url"
                    :href="link.url"
                    class="min-w-[2rem] h-8 flex items-center justify-center rounded-[8px] text-xs font-bold transition-all border-2"
                    :class="link.active
                        ? 'bg-tm-navy border-black text-white shadow-[1px_2px_0px_0px_rgba(0,0,0,0.8)] dark:bg-tm-green dark:border-border'
                        : 'bg-white border-tm-border text-tm-navy hover:bg-tm-navy-pale hover:border-tm-navy/30 dark:bg-card dark:border-border dark:text-foreground dark:hover:bg-secondary'"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="min-w-[2rem] h-8 flex items-center justify-center rounded-[8px] text-xs font-bold text-tm-text-muted cursor-not-allowed border-2 border-transparent"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>
