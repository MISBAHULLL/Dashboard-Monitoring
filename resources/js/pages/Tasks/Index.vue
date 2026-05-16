<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ListTodo, Plus, Edit, Trash2, Filter, RotateCcw, ExternalLink, Lock, CheckCircle2, AlertCircle, Download, Upload } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import { dashboard } from '@/routes';
import { show as showTask, bulkDestroy, bulkUpdateStatus, bulkAssign } from '@/routes/tasks';

// UI Components
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ConfirmDialog from '@/components/ConfirmDialog.vue';

const props = defineProps<{
    tasks: {
        data: Array<any>;
        links: Array<any>;
        total: number;
        current_page: number;
        per_page: number;
    };
    filters: any;
    permissions: {
        can_create: boolean;
    };
    clients: Array<any>;
    product_teams: Array<any>;
    engineer_teams: Array<any>;
    users: Array<any>;
}>();

// Setup Breadcrumbs
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Daftar Task', href: '#' },
        ],
    },
});

const filterForm = ref({
    search: props.filters.search || '',
    product_id: props.filters.product_id || 'all',
    client_id: props.filters.client_id || 'all',
    engineer_id: props.filters.engineer_id || 'all',
    category: props.filters.category || 'all',
    status: props.filters.status || 'all',
    has_link: props.filters.has_link || 'all',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

watch(filterForm, (newVal) => {
    const params: Record<string, string> = {};
    for (const [key, value] of Object.entries(newVal)) {
        params[key] = value === 'all' ? '' : value;
    }
    router.get('/tasks', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 
{ deep: true });
const resetFilter = () => {
    filterForm.value = {
        search: '',
        product_id: 'all',
        client_id: 'all',
        engineer_id: 'all',
        category: 'all',
        status: 'all',
        has_link: 'all',
        date_from: '',
        date_to: '',
    };
};

const selectedTasks = ref<number[]>([]);

const confirmAction = ref({
    open: false,
    title: '',
    description: '',
    onConfirm: () => {},
});

const selectAll = computed({
    get: () => props.tasks.data.length > 0 && selectedTasks.value.length === props.tasks.data.length,
    set: (val) => {
        if (val) {
            selectedTasks.value = props.tasks.data.map(t => t.id);
        } else {
            selectedTasks.value = [];
        }
    }
});

const bulkActionForm = useForm({
    ids: [] as number[],
    status: '',
    assigned_to: '',
});

const handleBulkDelete = () => {
    confirmAction.value = {
        open: true,
        title: 'Hapus Task Massal',
        description: `Apakah Anda yakin ingin menghapus ${selectedTasks.value.length} task yang dipilih? Tindakan ini tidak dapat dibatalkan.`,
        onConfirm: () => {
            bulkActionForm.ids = selectedTasks.value;
            bulkActionForm.post(bulkDestroy.url(), {
                preserveScroll: true,
                onSuccess: () => {
                    selectedTasks.value = [];
                    confirmAction.value.open = false;
                },
            });
        },
    };
};

const handleBulkStatus = (status: string) => {
    bulkActionForm.ids = selectedTasks.value;
    bulkActionForm.status = status;
    bulkActionForm.post(bulkUpdateStatus.url(), {
        preserveScroll: true,
        onSuccess: () => selectedTasks.value = [],
    });
};

const handleBulkAssign = (userId: string) => {
    if (!userId) return;
    bulkActionForm.ids = selectedTasks.value;
    bulkActionForm.assigned_to = userId;
    bulkActionForm.post(bulkAssign.url(), {
        preserveScroll: true,
        onSuccess: () => selectedTasks.value = [],
    });
};

watch(() => props.tasks.data, () => {
    selectedTasks.value = [];
});

const toggleCekStatus = (task: any, newStatus: string) => {
    if ((!task.task_url || task.task_url === '-') && (newStatus === 'completed' || newStatus === 'revision')) {
        return;
    }

    let finalStatus = newStatus;
    if (task.status === newStatus) {
        finalStatus = 'open';
    }

    router.patch(`/tasks/${task.id}/status`, { status: finalStatus }, {
        preserveScroll: true,
        preserveState: true
    });
};

const exportUrl = computed(() => {
    const params = new URLSearchParams();
    for (const [key, value] of Object.entries(filterForm.value)) {
        const cleanValue = value === 'all' ? '' : value;
        if (cleanValue) {
            params.append(key, String(cleanValue));
        }
    }
    // Jika ada task yang dicentang, export hanya yang dipilih
    if (selectedTasks.value.length > 0) {
        params.append('ids', selectedTasks.value.join(','));
    }
    return `/tasks/export?${params.toString()}`;
});

const deleteTask = (id: number, title: string) => {
    confirmAction.value = {
        open: true,
        title: 'Hapus Task',
        description: `Apakah Anda yakin ingin menghapus task "${title}"? Tindakan ini tidak dapat dibatalkan.`,
        onConfirm: () => {
            useForm({}).delete(`/tasks/${id}`, {
                onSuccess: () => {
                    confirmAction.value.open = false;
                },
            });
        },
    };
};

// Helper untuk Avatar Initials
const getInitials = (name: string) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

// Helper warna avatar berdasar string
const getAvatarColor = (name: string) => {
    const colors = [
        'bg-blue-100 text-blue-700 dark:bg-blue-400/15 dark:text-blue-200',
        'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-200',
        'bg-violet-100 text-violet-700 dark:bg-violet-400/15 dark:text-violet-200',
        'bg-amber-100 text-amber-700 dark:bg-amber-400/15 dark:text-amber-200',
        'bg-rose-100 text-rose-700 dark:bg-rose-400/15 dark:text-rose-200',
    ];
    if (!name) return colors[0];
    let hash = 0;
    for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
    return colors[Math.abs(hash) % colors.length];
};

// --- Import CSV ---
const showImportModal = ref(false);
const importForm = useForm({ file: null as File | null, force_duplicate: false });
const importFileInput = ref<HTMLInputElement | null>(null);
const onImportFileSelected = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        importForm.file = target.files[0];
    }
};
const submitImport = () => {
    if (!importForm.file) return;
    importForm.post('/tasks/import', {
        forceFormData: true,
        onSuccess: (page) => {
            showImportModal.value = false;
            importForm.reset();
            const flash = (page as any).props?.flash;
            if (flash?.success) {
                toast.success(flash.success);
            }
        },
    });
};
</script>

<template>
    <Head title="Monitoring Task" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl bg-tm-page p-4 md:p-8 dark:bg-[#081422]">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-3 text-3xl font-extrabold tracking-tight text-tm-navy dark:text-slate-100">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl border-[1.5px] border-black bg-gradient-to-br from-tm-navy to-tm-navy-medium text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.15)] dark:border-slate-600 dark:shadow-[0_10px_24px_rgba(0,0,0,0.35)]">
                        <ListTodo class="h-5 w-5" />
                    </div>
                    Monitoring Task
                </h1>
                <p class="mt-2 ml-1 text-sm text-tm-text-secondary dark:text-slate-400">Kelola dan pantau tiket permintaan faskes dengan sistem filter cerdas.</p>
            </div>
            <div class="flex items-center gap-3">
                <a :href="exportUrl" target="_blank" class="flex h-10 items-center gap-2 rounded-xl border-[1.5px] border-black bg-white px-4 text-slate-700 shadow-[2px_2px_0_0_rgba(0,0,0,0.1)] transition-all hover:-translate-y-1 hover:bg-slate-50 hover:shadow-[3px_4px_0_0_rgba(0,0,0,0.15)] dark:border-slate-600 dark:bg-[#111c2e] dark:text-slate-100 dark:shadow-[0_10px_24px_rgba(0,0,0,0.35)] dark:hover:bg-slate-800/70">
                    <Download class="h-4 w-4 text-tm-green" /> <span class="font-medium tracking-wide text-sm">Export</span>
                </a>
                <button v-if="permissions.can_create" @click="showImportModal = true" class="flex h-10 items-center gap-2 rounded-xl border-[1.5px] border-black bg-white px-4 text-slate-700 shadow-[2px_2px_0_0_rgba(0,0,0,0.1)] transition-all hover:-translate-y-1 hover:bg-slate-50 hover:shadow-[3px_4px_0_0_rgba(0,0,0,0.15)] dark:border-slate-600 dark:bg-[#111c2e] dark:text-slate-100 dark:shadow-[0_10px_24px_rgba(0,0,0,0.35)] dark:hover:bg-slate-800/70">
                    <Upload class="h-4 w-4 text-tm-navy-medium" /> <span class="font-medium tracking-wide text-sm">Import</span>
                </button>
                <Link v-if="permissions.can_create" href="/tasks/create">
                    <Button class="flex h-10 items-center gap-2 rounded-xl border-[1.5px] border-black bg-tm-green px-5 text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.2)] transition-all hover:-translate-y-1 hover:bg-tm-green-dark hover:shadow-[3px_4px_0_0_rgba(0,0,0,0.25)] dark:border-emerald-300/40 dark:shadow-[0_12px_28px_rgba(16,185,129,0.2)]">
                        <Plus class="h-4 w-4" /> <span class="font-medium tracking-wide">Task Baru</span>
                    </Button>
                </Link>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="rounded-[18px] border-[2.5px] border-black bg-white p-1 shadow-[2px_4px_4px_0_rgba(11,42,107,0.15)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[3px_6px_12px_0_rgba(11,42,107,0.2)] dark:border-slate-700/80 dark:bg-[#111c2e] dark:shadow-[0_14px_32px_rgba(0,0,0,0.42),0_0_0_1px_rgba(148,163,184,0.08)] dark:hover:shadow-[0_18px_40px_rgba(0,0,0,0.5),0_0_0_1px_rgba(52,211,153,0.16)]">
            <div class="p-4">
                <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-700/70">
                    <div class="flex items-center gap-2.5">
                        <Filter class="h-4 w-4 text-tm-text-secondary dark:text-slate-400" />
                        <h3 class="text-sm font-bold tracking-wide text-tm-navy dark:text-slate-100">Filter & Pencarian</h3>
                    </div>
                    <button @click="resetFilter" class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-tm-text-secondary transition-colors hover:bg-tm-navy-pale hover:text-tm-navy-medium dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100">
                        <RotateCcw class="h-3.5 w-3.5" /> Reset Filter
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-4">
                    <div class="space-y-1.5">
                        <Label class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary dark:text-slate-400">Product</Label>
                        <Select v-model="filterForm.product_id">
                            <SelectTrigger class="h-9 w-full cursor-pointer rounded-lg border-[1.5px] border-black/70 bg-white text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] dark:border-slate-600 dark:bg-slate-950/25 dark:text-slate-100 dark:hover:bg-slate-800/60">
                                <SelectValue placeholder="Semua Product" />
                            </SelectTrigger>
                            <SelectContent class="rounded-lg border-[1.5px] border-black shadow-[3px_4px_0_0_rgba(0,0,0,0.1)] dark:border-slate-600 dark:bg-[#111c2e] dark:text-slate-100">
                                <SelectItem value="all">Semua Product</SelectItem>
                                <SelectItem v-for="team in product_teams" :key="team.id" :value="String(team.id)">{{ team.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary dark:text-slate-400">Client / Faskes</Label>
                        <Select v-model="filterForm.client_id">
                            <SelectTrigger class="h-9 w-full cursor-pointer rounded-lg border-[1.5px] border-black/70 bg-white text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] dark:border-slate-600 dark:bg-slate-950/25 dark:text-slate-100 dark:hover:bg-slate-800/60">
                                <SelectValue placeholder="Semua Faskes" />
                            </SelectTrigger>
                            <SelectContent class="rounded-lg border-[1.5px] border-black shadow-[3px_4px_0_0_rgba(0,0,0,0.1)] dark:border-slate-600 dark:bg-[#111c2e] dark:text-slate-100">
                                <SelectItem value="all">Semua Faskes</SelectItem>
                                <SelectItem v-for="client in clients" :key="client.id" :value="String(client.id)">{{ client.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary dark:text-slate-400">Engineer</Label>
                        <Select v-model="filterForm.engineer_id">
                            <SelectTrigger class="h-9 w-full cursor-pointer rounded-lg border-[1.5px] border-black/70 bg-white text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] dark:border-slate-600 dark:bg-slate-950/25 dark:text-slate-100 dark:hover:bg-slate-800/60">
                                <SelectValue placeholder="Semua Engineer" />
                            </SelectTrigger>
                            <SelectContent class="rounded-lg border-[1.5px] border-black shadow-[3px_4px_0_0_rgba(0,0,0,0.1)] dark:border-slate-600 dark:bg-[#111c2e] dark:text-slate-100">
                                <SelectItem value="all">Semua Engineer</SelectItem>
                                <SelectItem v-for="team in engineer_teams" :key="team.id" :value="String(team.id)">{{ team.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary dark:text-slate-400">Jenis Task</Label>
                        <Select v-model="filterForm.category">
                            <SelectTrigger class="h-9 w-full cursor-pointer rounded-lg border-[1.5px] border-black/70 bg-white text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] dark:border-slate-600 dark:bg-slate-950/25 dark:text-slate-100 dark:hover:bg-slate-800/60">
                                <SelectValue placeholder="Semua Jenis" />
                            </SelectTrigger>
                            <SelectContent class="rounded-lg border-[1.5px] border-black shadow-[3px_4px_0_0_rgba(0,0,0,0.1)] dark:border-slate-600 dark:bg-[#111c2e] dark:text-slate-100">
                                <SelectItem value="all">Semua Jenis</SelectItem>
                                <SelectItem value="Fitur Berbayar">Fitur Berbayar</SelectItem>
                                <SelectItem value="Regulasi">Regulasi</SelectItem>
                                <SelectItem value="Saran Fitur">Saran Fitur</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary dark:text-slate-400">Status Link</Label>
                        <Select v-model="filterForm.has_link">
                            <SelectTrigger class="h-9 w-full cursor-pointer rounded-lg border-[1.5px] border-black/70 bg-white text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] dark:border-slate-600 dark:bg-slate-950/25 dark:text-slate-100 dark:hover:bg-slate-800/60">
                                <SelectValue placeholder="Semua" />
                            </SelectTrigger>
                            <SelectContent class="rounded-lg border-[1.5px] border-black shadow-[3px_4px_0_0_rgba(0,0,0,0.1)] dark:border-slate-600 dark:bg-[#111c2e] dark:text-slate-100">
                                <SelectItem value="all">Semua</SelectItem>
                                <SelectItem value="yes">Sudah Ada Link</SelectItem>
                                <SelectItem value="no">Belum Ada Link</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary dark:text-slate-400">Status Cek</Label>
                        <Select v-model="filterForm.status">
                            <SelectTrigger class="h-9 w-full cursor-pointer rounded-lg border-[1.5px] border-black/70 bg-white text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] dark:border-slate-600 dark:bg-slate-950/25 dark:text-slate-100 dark:hover:bg-slate-800/60">
                                <SelectValue placeholder="Semua Status" />
                            </SelectTrigger>
                            <SelectContent class="rounded-lg border-[1.5px] border-black shadow-[3px_4px_0_0_rgba(0,0,0,0.1)] dark:border-slate-600 dark:bg-[#111c2e] dark:text-slate-100">
                                <SelectItem value="all">Semua Status</SelectItem>
                                <SelectItem value="open">Belum Di Cek</SelectItem>
                                <SelectItem value="in_progress">In Progress</SelectItem>
                                <SelectItem value="revision">Revisi</SelectItem>
                                <SelectItem value="completed">Selesai</SelectItem>
                                <SelectItem value="overdue">Overdue</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5 lg:col-span-2">
                        <Label class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary dark:text-slate-400">Rentang Tanggal Release</Label>
                        <div class="flex items-center gap-3">
                            <Input type="date" v-model="filterForm.date_from" class="h-9 w-full cursor-pointer rounded-lg border-[1.5px] border-black/70 bg-white text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] focus:border-tm-navy focus:ring-1 focus:ring-tm-navy dark:border-slate-600 dark:bg-slate-950/25 dark:text-slate-100 dark:[color-scheme:dark] dark:hover:bg-slate-800/60" />
                            <span class="text-sm font-semibold text-tm-text-secondary dark:text-slate-400">s/d</span>
                            <Input type="date" v-model="filterForm.date_to" class="h-9 w-full cursor-pointer rounded-lg border-[1.5px] border-black/70 bg-white text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] focus:border-tm-navy focus:ring-1 focus:ring-tm-navy dark:border-slate-600 dark:bg-slate-950/25 dark:text-slate-100 dark:[color-scheme:dark] dark:hover:bg-slate-800/60" />
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="rounded-b-[16px] border-t border-slate-100 bg-slate-50/50 p-2 dark:border-slate-700/70 dark:bg-slate-950/20">
                 <Input type="text" v-model="filterForm.search" placeholder="Pencarian cepat judul task atau deskripsi..." class="h-10 w-full rounded-lg border-[1.5px] border-black/70 bg-white px-4 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] focus:border-tm-navy focus:ring-1 focus:ring-tm-navy dark:border-slate-600 dark:bg-slate-950/25 dark:text-slate-100 dark:placeholder:text-slate-500" />
            </div>
        </div>

        <!-- Bulk Actions Toolbar -->
        <div v-if="selectedTasks.length > 0" class="flex flex-wrap items-center justify-between gap-4 rounded-[14px] border-[1.5px] border-black bg-tm-navy-pale p-3 shadow-[2px_3px_0_0_rgba(0,0,0,0.1)] dark:border-sky-300/35 dark:bg-sky-400/10 dark:shadow-[0_12px_28px_rgba(0,0,0,0.38)]">
            <div class="flex items-center gap-3">
                <span class="bg-tm-navy text-white text-xs font-bold px-2 py-1 rounded-md">{{ selectedTasks.length }} terpilih</span>
                <span class="text-sm font-semibold text-tm-navy dark:text-slate-100">Aksi Massal:</span>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Assign -->
                <select @change="handleBulkAssign(($event.target as HTMLSelectElement).value); ($event.target as HTMLSelectElement).value = ''" class="h-8 w-32 cursor-pointer rounded-lg border-0 bg-white px-2 text-xs text-slate-700 ring-1 ring-inset ring-tm-border focus:ring-2 focus:ring-inset focus:ring-tm-navy dark:bg-slate-950/40 dark:text-slate-100 dark:ring-slate-600">
                    <option value="">Assign ke...</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                </select>

                <!-- Status -->
                <select @change="handleBulkStatus(($event.target as HTMLSelectElement).value); ($event.target as HTMLSelectElement).value = ''" class="h-8 w-32 cursor-pointer rounded-lg border-0 bg-white px-2 text-xs text-slate-700 ring-1 ring-inset ring-tm-border focus:ring-2 focus:ring-inset focus:ring-tm-navy dark:bg-slate-950/40 dark:text-slate-100 dark:ring-slate-600">
                    <option value="">Ubah Status...</option>
                    <option value="open">Belum Di Cek</option>
                    <option value="revision">Revisi</option>
                    <option value="completed">Selesai</option>
                </select>

                <!-- Delete -->
                <Button @click="handleBulkDelete" variant="destructive" size="sm" class="h-8 text-xs bg-tm-danger hover:bg-red-600">
                    <Trash2 class="h-3.5 w-3.5 mr-1.5" /> Hapus
                </Button>
            </div>
        </div>

        <!-- Modern Data Grid with 11 Columns Parity -->
        <div class="flex flex-1 flex-col overflow-hidden rounded-[18px] border-[2.5px] border-black bg-white shadow-[2px_4px_4px_0_rgba(11,42,107,0.25)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[3px_8px_12px_2px_rgba(11,42,107,0.3)] dark:border-slate-700/80 dark:bg-[#111c2e] dark:shadow-[0_14px_32px_rgba(0,0,0,0.44),0_0_0_1px_rgba(148,163,184,0.08)] dark:hover:shadow-[0_18px_40px_rgba(0,0,0,0.52),0_0_0_1px_rgba(122,162,247,0.22)]">
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="border-b-[2px] border-black/80 bg-tm-navy-pale dark:border-slate-600 dark:bg-slate-800/80">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-tm-navy dark:text-slate-200">
                            <th class="py-3 px-4 w-10 text-center">
                                <input type="checkbox" v-model="selectAll" class="h-4 w-4 cursor-pointer rounded border-slate-300 text-tm-navy focus:ring-tm-navy dark:border-slate-600 dark:bg-slate-950/40" />
                            </th>
                            <th class="py-3 px-4">Product</th>
                            <th class="py-3 px-4">Faskes</th>
                            <th class="py-3 px-4 min-w-[200px]">Fitur</th>
                            <th class="py-3 px-4 text-center">Task (URL)</th>
                            <th class="py-3 px-4 min-w-[180px]">Jenis / Keterangan</th>
                            <th class="py-3 px-4">Engineer</th>
                            <th class="py-3 px-4 text-center">Dokumen</th>
                            <th class="py-3 px-4 text-center">Tanggal Release</th>
                            <th class="py-3 px-4 text-center">SLA Status</th>
                            <th class="py-3 px-4 text-center">Cek</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/10 dark:divide-slate-700/70">
                        <tr v-for="task in tasks.data" :key="task.id" 
                            class="group relative transition-all duration-200 hover:bg-tm-navy-pale/40 dark:bg-[#111c2e] dark:hover:bg-slate-800/50"
                            :class="{
                                'bg-tm-green-pale/60 hover:bg-tm-green-pale dark:bg-emerald-400/10 dark:hover:bg-emerald-400/16': task.status === 'completed',
                                'bg-tm-warning-pale/60 hover:bg-tm-warning-pale dark:bg-amber-400/10 dark:hover:bg-amber-400/16': task.status === 'revision'
                            }">
                            
                            <!-- 0. CHECKBOX -->
                            <td class="py-3 px-4 relative">
                                <!-- Aksen garis warna di sebelah kiri -->
                                <div class="absolute left-0 top-0 bottom-0 w-1 transition-all duration-200" 
                                    :class="{
                                        'bg-tm-green': task.status === 'completed',
                                        'bg-tm-warning': task.status === 'revision',
                                        'bg-transparent group-hover:bg-tm-navy-medium': task.status !== 'completed' && task.status !== 'revision'
                                    }">
                                </div>
                                
                                <div class="flex justify-center items-center h-full">
                                    <input type="checkbox" v-model="selectedTasks" :value="task.id" class="h-4 w-4 cursor-pointer rounded border-slate-300 text-tm-navy focus:ring-tm-navy dark:border-slate-600 dark:bg-slate-950/40" />
                                </div>
                            </td>

                            <!-- 1. PRODUCT -->
                            <td class="py-3 px-4 relative">
                                <span class="block max-w-[120px] truncate text-xs font-bold text-slate-700 dark:text-slate-200" :title="task.product?.name">
                                    {{ task.product?.name || '-' }}
                                </span>
                            </td>

                            <!-- 2. FASKES -->
                            <td class="py-3 px-4">
                                <span class="flex max-w-[150px] items-center gap-1.5 truncate text-xs font-semibold text-slate-600 dark:text-slate-300" :title="task.client?.name">
                                    🏥 {{ task.client?.name || '-' }}
                                </span>
                            </td>
                            
                            <!-- 3. FITUR (Modul) -->
                            <td class="py-3 px-4">
                                <div class="flex flex-col gap-1">
                                    <span class="max-w-[220px] cursor-pointer truncate text-[13px] font-bold text-slate-800 transition-colors hover:text-tm-navy-medium dark:text-slate-100 dark:hover:text-sky-300" :title="task.title">
                                        <Link :href="showTask.url(task.id)">{{ task.title }}</Link>
                                    </span>
                                    <span v-if="task.modul" class="flex items-center gap-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                        <span class="h-1 w-1 rounded-full bg-slate-300 dark:bg-slate-500"></span> {{ task.modul }}
                                    </span>
                                    <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">
                                        {{ task.comments_count ?? 0 }} komentar
                                    </span>
                                </div>
                            </td>
                            
                            <!-- 4. TASK (URL) -->
                            <td class="py-3 px-4 text-center">
                                <a v-if="task.task_url && task.task_url !== '-'" :href="task.task_url" target="_blank" 
                                   class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-tm-navy/20 bg-tm-navy-pale text-tm-navy shadow-sm transition-all duration-300 hover:scale-110 hover:bg-tm-navy hover:text-white dark:border-sky-300/35 dark:bg-sky-400/10 dark:text-sky-200 dark:hover:bg-sky-300 dark:hover:text-slate-950" title="Buka Dokumen/URL">
                                    <ExternalLink class="h-3.5 w-3.5" />
                                </a>
                                <span v-else class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-100 bg-slate-50 text-slate-300 dark:border-slate-700 dark:bg-slate-950/35 dark:text-slate-500" title="Tidak ada URL">
                                    <span class="text-lg leading-none -mt-1">-</span>
                                </span>
                            </td>
                            
                            <!-- 5. JENIS / KETERANGAN -->
                            <td class="py-3 px-4">
                                <div class="flex flex-col gap-1 items-start">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wider w-fit border shadow-sm"
                                        :class="{
                                            'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200 dark:bg-fuchsia-400/10 dark:text-fuchsia-200 dark:border-fuchsia-300/35': task.category === 'Fitur Berbayar',
                                            'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-400/10 dark:text-indigo-200 dark:border-indigo-300/35': task.category === 'Regulasi',
                                            'bg-tm-navy-pale text-tm-navy border-tm-navy/20 dark:bg-sky-400/10 dark:text-sky-200 dark:border-sky-300/35': task.category === 'Saran Fitur',
                                            'bg-tm-danger-pale text-tm-danger border-tm-danger/30 dark:bg-red-400/10 dark:text-red-200 dark:border-red-300/35': task.category === 'Prioritas',
                                        }">
                                        {{ task.category }}
                                    </span>
                                    <span class="mt-0.5 max-w-[180px] truncate text-[11px] italic text-slate-500 dark:text-slate-400" :title="task.description">
                                        {{ task.description || 'Tidak ada keterangan.' }}
                                    </span>
                                </div>
                            </td>

                            <!-- 6. ENGINEER -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full border border-white text-[9px] font-extrabold shadow-sm ring-1 ring-slate-100 dark:border-slate-900 dark:ring-slate-700"
                                        :class="getAvatarColor(task.engineer?.name || task.assignee?.name)">
                                        {{ getInitials(task.engineer?.name || task.assignee?.name) }}
                                    </div>
                                    <span class="max-w-[100px] truncate text-xs font-bold text-slate-700 dark:text-slate-200" :title="task.engineer?.name || task.assignee?.name">{{ task.engineer?.name || task.assignee?.name || '-' }}</span>
                                </div>
                            </td>
                            
                            <!-- 7. DOKUMEN -->
                            <td class="py-3 px-4 text-center">
                                <div v-if="task.documents && task.documents.length > 0" class="flex flex-col items-center gap-1">
                                    <span class="rounded-full border border-tm-navy/20 bg-tm-navy-pale px-2 py-0.5 text-[10px] font-bold text-tm-navy dark:border-sky-300/35 dark:bg-sky-400/10 dark:text-sky-200">
                                        {{ task.documents.length }} dok
                                    </span>
                                    <div class="flex flex-wrap justify-center gap-1 max-w-[100px]">
                                        <span v-for="doc in task.documents.slice(0, 2)" :key="doc.id"
                                            class="text-[9px] font-bold px-1.5 py-0.5 rounded"
                                            :class="doc.type === 'UAT' ? 'bg-orange-100 text-orange-700 dark:bg-orange-400/10 dark:text-orange-200' : doc.type === 'MOM' ? 'bg-purple-100 text-purple-700 dark:bg-purple-400/10 dark:text-purple-200' : 'bg-teal-100 text-teal-700 dark:bg-teal-400/10 dark:text-teal-200'">
                                            {{ doc.type }}
                                        </span>
                                        <span v-if="task.documents.length > 2" class="text-[9px] text-slate-400 dark:text-slate-500">+{{ task.documents.length - 2 }}</span>
                                    </div>
                                </div>
                                <span v-else class="rounded-md border border-slate-100 bg-slate-50 px-2 py-1 text-[10px] font-medium text-slate-400 dark:border-slate-700 dark:bg-slate-950/35 dark:text-slate-500">Belum ada</span>
                            </td>

                            <!-- 8. TANGGAL RELEASE -->
                            <td class="py-3 px-4 text-center">
                                <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">
                                    {{ task.release_date ? new Date(task.release_date).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) : '-' }}
                                </span>
                            </td>

                            <!-- 8.5. SLA STATUS -->
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold tracking-wide shadow-sm"
                                    :class="{
                                        'bg-tm-green-pale text-tm-green-dark border border-tm-green/30 dark:bg-emerald-400/10 dark:text-emerald-200 dark:border-emerald-300/35': task.sla_status === 'completed_on_time',
                                        'bg-tm-warning-pale text-amber-700 border border-tm-warning/30 dark:bg-amber-400/10 dark:text-amber-200 dark:border-amber-300/35': task.sla_status === 'completed_late',
                                        'bg-blue-100 text-blue-700 border border-blue-200 dark:bg-blue-400/10 dark:text-blue-200 dark:border-blue-300/35': task.sla_status === 'on_track',
                                        'bg-tm-warning-pale text-orange-700 border border-tm-warning/30 dark:bg-orange-400/10 dark:text-orange-200 dark:border-orange-300/35': task.sla_status === 'warning',
                                        'bg-tm-danger-pale text-tm-danger border border-tm-danger/30 dark:bg-red-400/10 dark:text-red-200 dark:border-red-300/35': task.sla_status === 'overdue',
                                        'bg-slate-100 text-slate-500 border border-slate-200 dark:bg-slate-800/60 dark:text-slate-400 dark:border-slate-700': task.sla_status === 'unknown',
                                    }" :title="'Batas waktu SLA: ' + (task.sla ? task.sla.max_days + ' hari' : 'tidak ada config')">
                                    {{ (task.sla_status || 'unknown').replace(/_/g, ' ').toUpperCase() }}
                                </span>
                            </td>

                            <!-- 9. CEK (TOGGLE BUTTONS MODERN) -->
                            <td class="py-3 px-4">
                                <div v-if="!task.can_update_status"
                                     class="mx-auto flex h-7 w-full max-w-[120px] cursor-not-allowed items-center justify-center rounded-md border border-slate-200 bg-slate-100/80 text-[9px] font-bold text-slate-400 opacity-70 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-500">
                                    NO AKSES
                                </div>
                                <div v-else-if="!task.task_url || task.task_url === '-'" 
                                     class="mx-auto flex h-7 w-full max-w-[120px] cursor-not-allowed items-center justify-center rounded-md border border-slate-200 bg-slate-100/80 text-[9px] font-bold text-slate-400 opacity-70 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-500">
                                    <Lock class="h-3 w-3 mr-1" /> URL KOSONG
                                </div>
                                <div v-else class="mx-auto flex w-fit items-center justify-center rounded-lg border border-tm-border bg-slate-100/80 p-0.5 shadow-inner dark:border-slate-700 dark:bg-slate-950/35">
                                    <button @click="toggleCekStatus(task, 'revision')"
                                            class="flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-bold transition-all duration-200"
                                            :class="task.status === 'revision' ? 'bg-white text-tm-warning shadow-sm border border-tm-warning/30 ring-1 ring-tm-warning/20 dark:bg-amber-400/10 dark:text-amber-200 dark:border-amber-300/35' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-200/80 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800'">
                                        <AlertCircle class="h-3 w-3" :class="task.status === 'revision' ? 'text-tm-warning' : 'text-slate-400'" /> Rev
                                    </button>
                                    <button @click="toggleCekStatus(task, 'completed')"
                                            class="flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-bold transition-all duration-200"
                                            :class="task.status === 'completed' ? 'bg-white text-tm-green shadow-sm border border-tm-green/30 ring-1 ring-tm-green/20 dark:bg-emerald-400/10 dark:text-emerald-200 dark:border-emerald-300/35' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-200/80 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800'">
                                        <CheckCircle2 class="h-3 w-3" :class="task.status === 'completed' ? 'text-tm-green' : 'text-slate-400'" /> OK
                                    </button>
                                </div>
                            </td>
                            
                            <!-- 10. STATUS -->
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md text-[10px] font-bold tracking-wider shadow-sm"
                                    :class="{
                                        'bg-white text-slate-500 border border-slate-200 dark:bg-slate-950/35 dark:text-slate-400 dark:border-slate-700': task.status === 'open' || task.status === 'in_progress',
                                        'bg-white text-tm-warning border border-tm-warning/30 dark:bg-amber-400/10 dark:text-amber-200 dark:border-amber-300/35': task.status === 'revision',
                                        'bg-white text-tm-green-dark border border-tm-green/30 dark:bg-emerald-400/10 dark:text-emerald-200 dark:border-emerald-300/35': task.status === 'completed'
                                    }">
                                    <span v-if="task.status === 'open' || task.status === 'in_progress'" class="flex items-center gap-1.5">
                                        <span class="relative flex h-1.5 w-1.5"><span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-slate-400"></span></span>
                                        BELUM
                                    </span>
                                    <span v-else-if="task.status === 'revision'" class="flex items-center gap-1.5">
                                        <span class="relative flex h-1.5 w-1.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-tm-warning opacity-75"></span><span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-tm-warning"></span></span>
                                        REVISI
                                    </span>
                                    <span v-else class="flex items-center gap-1.5">
                                        <span class="relative flex h-1.5 w-1.5"><span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-tm-green"></span></span>
                                        SELESAI
                                    </span>
                                </span>
                            </td>
                            
                            <!-- 11. AKSI -->
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1 opacity-50 group-hover:opacity-100 transition-opacity duration-200">
                                    <Link v-if="task.can_edit" :href="`/tasks/${task.id}/edit`">
                                        <Button variant="ghost" size="icon" class="h-7 w-7 rounded-lg text-tm-navy-medium transition-colors hover:bg-tm-navy-pale hover:text-tm-navy dark:text-sky-300 dark:hover:bg-sky-400/10 dark:hover:text-sky-100">
                                            <Edit class="h-3.5 w-3.5" />
                                        </Button>
                                    </Link>
                                    <Button v-if="task.can_delete" variant="ghost" size="icon" @click="deleteTask(task.id, task.title)" class="h-7 w-7 rounded-lg text-tm-danger transition-colors hover:bg-tm-danger-pale hover:text-red-700 dark:text-red-300 dark:hover:bg-red-400/10 dark:hover:text-red-200">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        
                        <tr v-if="tasks.data.length === 0">
                            <td colspan="12" class="py-20 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                                    <div class="mb-4 flex h-16 w-16 rotate-3 items-center justify-center rounded-[14px] border-[1.5px] border-black bg-tm-navy-pale shadow-[2px_2px_0_0_rgba(0,0,0,0.1)] dark:border-slate-600 dark:bg-sky-400/10">
                                        <ListTodo class="h-8 w-8 -rotate-3 text-tm-navy/50 dark:text-sky-200/60" />
                                    </div>
                                    <p class="text-base font-bold text-tm-navy dark:text-slate-100">Pencarian Tidak Ditemukan</p>
                                    <p class="mb-4 mt-1 max-w-sm text-xs text-tm-text-secondary dark:text-slate-400">Tidak ada data tiket task yang sesuai dengan kombinasi filter yang Anda pilih saat ini.</p>
                                    <Button @click="resetFilter" variant="outline" class="h-9 rounded-xl border-[1.5px] border-black text-xs font-semibold text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] transition-all hover:-translate-y-0.5 hover:bg-tm-navy-pale dark:border-slate-600 dark:bg-slate-950/25 dark:text-slate-100 dark:hover:bg-slate-800">
                                        <RotateCcw class="mr-2 h-3.5 w-3.5 text-tm-text-secondary dark:text-slate-400" /> Reset Filter
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modern Pagination Footer -->
            <div class="flex flex-col items-center justify-between border-t-[2px] border-black/80 bg-slate-50/80 p-3 px-5 text-xs font-semibold text-tm-text-secondary dark:border-slate-600 dark:bg-slate-950/25 dark:text-slate-400 sm:flex-row">
                <div>
                    Menampilkan <span class="font-extrabold text-tm-navy dark:text-slate-100">{{ tasks.data.length > 0 ? (tasks.current_page - 1) * tasks.per_page + 1 : 0 }}</span> - 
                    <span class="font-extrabold text-tm-navy dark:text-slate-100">{{ Math.min(tasks.current_page * tasks.per_page, tasks.total) }}</span> dari <span class="font-extrabold text-tm-navy dark:text-slate-100">{{ tasks.total }}</span> tiket
                </div>
                
                <div v-if="tasks.links && tasks.links.length > 3" class="mt-3 flex w-fit items-center gap-1 rounded-xl border-[1.5px] border-black bg-white p-1 shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] dark:border-slate-600 dark:bg-slate-950/35 sm:mt-0">
                    <template v-for="(link, key) in tasks.links" :key="key">
                        <Link v-if="link.url && !link.active"
                            :href="link.url" 
                            class="flex h-7 min-w-[2rem] items-center justify-center rounded-md bg-transparent px-2 text-[13px] font-bold text-tm-navy transition-all duration-200 hover:bg-tm-navy-pale dark:text-slate-200 dark:hover:bg-slate-800">
                            <span v-html="link.label"></span>
                        </Link>
                        <span v-else-if="link.active" class="flex h-7 min-w-[2rem] items-center justify-center rounded-md bg-tm-navy px-2 text-[13px] font-bold text-white shadow-[1px_1px_0_0_rgba(0,0,0,0.2)] dark:bg-sky-300 dark:text-slate-950">
                            <span v-html="link.label"></span>
                        </span>
                        <span v-else class="flex h-7 min-w-[2rem] cursor-not-allowed items-center justify-center rounded-md bg-transparent px-2 text-[13px] font-bold text-tm-text-muted dark:text-slate-600">
                            <span v-html="link.label"></span>
                        </span>
                    </template>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Import Modal -->
    <Teleport to="body">
        <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 dark:bg-black/70" @click.self="showImportModal = false">
            <div class="w-full max-w-md space-y-5 rounded-[18px] border-[2.5px] border-black bg-white p-6 shadow-[4px_6px_0_0_rgba(0,0,0,0.2)] dark:border-slate-700/80 dark:bg-[#111c2e] dark:shadow-[0_18px_44px_rgba(0,0,0,0.55),0_0_0_1px_rgba(148,163,184,0.08)]">
                <div>
                    <h2 class="text-lg font-bold text-tm-navy dark:text-slate-100">Import Task dari File</h2>
                    <p class="mt-1 text-sm text-tm-text-secondary dark:text-slate-400">Upload file CSV atau Excel (.xlsx) dengan kolom: Judul Task, Product, Client, Jenis, Prioritas, dll.</p>
                </div>
                <div class="space-y-3">
                    <label class="block">
                        <span class="text-sm font-semibold text-tm-navy dark:text-slate-200">Pilih File</span>
                        <input ref="importFileInput" type="file" accept=".csv,.xlsx,.xls" @change="onImportFileSelected"
                            class="mt-1 block w-full cursor-pointer text-sm text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-tm-navy-pale file:px-4 file:py-2 file:text-sm file:font-semibold file:text-tm-navy hover:file:bg-tm-navy/10 dark:text-slate-400 dark:file:bg-sky-400/10 dark:file:text-sky-200 dark:hover:file:bg-sky-400/20" />
                    </label>
                    <p v-if="importForm.errors.file" class="text-sm text-tm-danger">{{ importForm.errors.file }}</p>

                    <label class="flex items-center gap-2 cursor-pointer mt-2">
                        <input type="checkbox" v-model="importForm.force_duplicate" class="rounded border-slate-300 text-tm-navy focus:ring-tm-navy dark:border-slate-600 dark:bg-slate-950/40" />
                        <span class="text-sm text-tm-text-secondary dark:text-slate-400">Abaikan pengecekan duplikat (paksa import semua baris)</span>
                    </label>
                </div>
                <div class="flex justify-end gap-3 border-t pt-2 dark:border-slate-700">
                    <Button variant="outline" @click="showImportModal = false" class="h-10 rounded-xl border-[1.5px] border-black px-5 shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] transition-all hover:-translate-y-0.5 dark:border-slate-600 dark:bg-slate-950/25 dark:text-slate-100 dark:hover:bg-slate-800">Batal</Button>
                    <Button @click="submitImport" :disabled="!importForm.file || importForm.processing"
                        class="flex h-10 items-center gap-2 rounded-xl border-[1.5px] border-black bg-tm-navy px-5 text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.2)] transition-all hover:-translate-y-0.5 hover:bg-tm-navy-medium dark:border-sky-300/35">
                        <Upload class="h-4 w-4" />
                        {{ importForm.processing ? 'Memproses...' : 'Import Sekarang' }}
                    </Button>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- Confirm Dialog -->
    <ConfirmDialog
        :open="confirmAction.open"
        :title="confirmAction.title"
        :description="confirmAction.description"
        confirm-label="Hapus"
        cancel-label="Batal"
        variant="danger"
        @update:open="(val) => confirmAction.open = val"
        @confirm="confirmAction.onConfirm"
        @cancel="confirmAction.open = false"
    />
</template>
