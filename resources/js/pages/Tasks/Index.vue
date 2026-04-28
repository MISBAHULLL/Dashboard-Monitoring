<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ListTodo, Plus, Edit, Trash2, Filter, RotateCcw, ExternalLink, Lock, CheckCircle2, AlertCircle, Download } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { show as showTask, bulkDestroy, bulkUpdateStatus, bulkAssign } from '@/actions/App/Http/Controllers/TaskController';

// UI Components
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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
    product_id: props.filters.product_id || '',
    client_id: props.filters.client_id || '',
    engineer_id: props.filters.engineer_id || '',
    category: props.filters.category || '',
    status: props.filters.status || '',
    has_link: props.filters.has_link || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

watch(filterForm, (newVal) => {
    router.get('/tasks', newVal, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, { deep: true });

const resetFilter = () => {
    filterForm.value = {
        search: '',
        product_id: '',
        client_id: '',
        engineer_id: '',
        category: '',
        status: '',
        has_link: '',
        date_from: '',
        date_to: '',
    };
};

const selectedTasks = ref<number[]>([]);

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
    if (confirm(`Hapus ${selectedTasks.value.length} task secara massal?`)) {
        bulkActionForm.ids = selectedTasks.value;
        bulkActionForm.post(bulkDestroy.url(), {
            preserveScroll: true,
            onSuccess: () => selectedTasks.value = [],
        });
    }
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
        if (value) {
            params.append(key, String(value));
        }
    }
    return `/tasks/export?${params.toString()}`;
});

const deleteTask = (id: number, title: string) => {
    if (confirm(`Apakah Anda yakin ingin menghapus Task: "${title}"?`)) {
        useForm({}).delete(`/tasks/${id}`);
    }
};

// Helper untuk Avatar Initials
const getInitials = (name: string) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

// Helper warna avatar berdasar string
const getAvatarColor = (name: string) => {
    const colors = ['bg-blue-100 text-blue-700', 'bg-emerald-100 text-emerald-700', 'bg-violet-100 text-violet-700', 'bg-amber-100 text-amber-700', 'bg-rose-100 text-rose-700'];
    if (!name) return colors[0];
    let hash = 0;
    for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
    return colors[Math.abs(hash) % colors.length];
};
</script>

<template>
    <Head title="Monitoring Task" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-8 bg-slate-50/50">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 flex items-center gap-3">
                    <div class="p-2.5 bg-sky-100 rounded-xl text-sky-600 shadow-sm border border-sky-200/50">
                        <ListTodo class="h-6 w-6" />
                    </div>
                    Monitoring Task
                </h1>
                <p class="text-sm text-slate-500 mt-2 ml-1">Kelola dan pantau tiket permintaan faskes dengan sistem filter cerdas.</p>
            </div>
            <div class="flex items-center gap-3">
                <a :href="exportUrl" target="_blank" class="flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-sm rounded-xl transition-all hover:-translate-y-0.5 h-10 px-4">
                    <Download class="h-4 w-4 text-emerald-600" /> <span class="font-medium tracking-wide text-sm">Export Excel</span>
                </a>
                <Link v-if="permissions.can_create" href="/tasks/create">
                    <Button class="flex items-center gap-2 bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white shadow-lg shadow-sky-500/20 rounded-xl transition-all hover:-translate-y-0.5 border-0 h-10 px-5">
                        <Plus class="h-4 w-4" /> <span class="font-medium tracking-wide">Task Baru</span>
                    </Button>
                </Link>
            </div>
        </div>

        <!-- Filter Modern Glass Card -->
        <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-2xl shadow-sm p-1">
            <div class="p-4">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <Filter class="h-4 w-4 text-slate-500" />
                        <h3 class="font-bold text-slate-700 text-sm tracking-wide">Filter & Pencarian</h3>
                    </div>
                    <button @click="resetFilter" class="text-xs font-semibold text-slate-500 hover:text-sky-600 flex items-center gap-1.5 transition-colors px-3 py-1.5 rounded-lg hover:bg-slate-100">
                        <RotateCcw class="h-3.5 w-3.5" /> Reset Filter
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-4">
                    <div class="space-y-1.5">
                        <Label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider ml-1">Product</Label>
                        <select v-model="filterForm.product_id" class="w-full text-sm border-0 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-sky-500 rounded-xl bg-slate-50/50 hover:bg-slate-50 h-9 px-3 transition-all cursor-pointer text-slate-700">
                            <option value="">Semua Product</option>
                            <option v-for="team in product_teams" :key="team.id" :value="team.id">{{ team.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider ml-1">Client / Faskes</Label>
                        <select v-model="filterForm.client_id" class="w-full text-sm border-0 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-sky-500 rounded-xl bg-slate-50/50 hover:bg-slate-50 h-9 px-3 transition-all cursor-pointer text-slate-700">
                            <option value="">Semua Faskes</option>
                            <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider ml-1">Engineer</Label>
                        <select v-model="filterForm.engineer_id" class="w-full text-sm border-0 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-sky-500 rounded-xl bg-slate-50/50 hover:bg-slate-50 h-9 px-3 transition-all cursor-pointer text-slate-700">
                            <option value="">Semua Engineer</option>
                            <option v-for="team in engineer_teams" :key="team.id" :value="team.id">{{ team.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider ml-1">Jenis Task</Label>
                        <select v-model="filterForm.category" class="w-full text-sm border-0 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-sky-500 rounded-xl bg-slate-50/50 hover:bg-slate-50 h-9 px-3 transition-all cursor-pointer text-slate-700">
                            <option value="">Semua Jenis</option>
                            <option value="Fitur Berbayar">Fitur Berbayar</option>
                            <option value="Regulasi">Regulasi</option>
                            <option value="Saran Fitur">Saran Fitur</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider ml-1">Status Link</Label>
                        <select v-model="filterForm.has_link" class="w-full text-sm border-0 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-sky-500 rounded-xl bg-slate-50/50 hover:bg-slate-50 h-9 px-3 transition-all cursor-pointer text-slate-700">
                            <option value="">Semua</option>
                            <option value="yes">Sudah Ada Link</option>
                            <option value="no">Belum Ada Link</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider ml-1">Status Cek</Label>
                        <select v-model="filterForm.status" class="w-full text-sm border-0 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-sky-500 rounded-xl bg-slate-50/50 hover:bg-slate-50 h-9 px-3 transition-all cursor-pointer text-slate-700">
                            <option value="">Semua Status</option>
                            <option value="open">Belum Di Cek</option>
                            <option value="revision">Revisi</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>
                    <div class="space-y-1.5 lg:col-span-2">
                        <Label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider ml-1">Rentang Tanggal Release</Label>
                        <div class="flex items-center gap-3">
                            <Input type="date" v-model="filterForm.date_from" class="h-9 text-sm border-0 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-sky-500 rounded-xl bg-slate-50/50 hover:bg-slate-50 transition-all cursor-pointer w-full text-slate-700" />
                            <span class="text-slate-500 text-sm font-semibold">s/d</span>
                            <Input type="date" v-model="filterForm.date_to" class="h-9 text-sm border-0 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-sky-500 rounded-xl bg-slate-50/50 hover:bg-slate-50 transition-all cursor-pointer w-full text-slate-700" />
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-2 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl">
                 <Input type="text" v-model="filterForm.search" placeholder="Pencarian cepat judul task atau deskripsi..." class="h-10 w-full bg-white border-0 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-sky-500 shadow-sm rounded-xl px-4 text-sm transition-all text-slate-700" />
            </div>
        </div>

        <!-- Bulk Actions Toolbar -->
        <div v-if="selectedTasks.length > 0" class="bg-sky-50 border border-sky-200 rounded-xl p-3 flex flex-wrap items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="bg-sky-600 text-white text-xs font-bold px-2 py-1 rounded-md">{{ selectedTasks.length }} terpilih</span>
                <span class="text-sm font-semibold text-slate-700">Aksi Massal:</span>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Assign -->
                <select @change="handleBulkAssign(($event.target as HTMLSelectElement).value); ($event.target as HTMLSelectElement).value = ''" class="text-xs border-0 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-sky-500 rounded-lg bg-white h-8 px-2 cursor-pointer text-slate-700 w-32">
                    <option value="">Assign ke...</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                </select>

                <!-- Status -->
                <select @change="handleBulkStatus(($event.target as HTMLSelectElement).value); ($event.target as HTMLSelectElement).value = ''" class="text-xs border-0 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-sky-500 rounded-lg bg-white h-8 px-2 cursor-pointer text-slate-700 w-32">
                    <option value="">Ubah Status...</option>
                    <option value="open">Belum Di Cek</option>
                    <option value="revision">Revisi</option>
                    <option value="completed">Selesai</option>
                </select>

                <!-- Delete -->
                <Button @click="handleBulkDelete" variant="destructive" size="sm" class="h-8 text-xs bg-rose-500 hover:bg-rose-600">
                    <Trash2 class="h-3.5 w-3.5 mr-1.5" /> Hapus
                </Button>
            </div>
        </div>

        <!-- Modern Data Grid with 11 Columns Parity -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex-1 flex flex-col">
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-100 border-b border-slate-200">
                        <tr class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                            <th class="py-3 px-4 w-10 text-center">
                                <input type="checkbox" v-model="selectAll" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 w-4 h-4 cursor-pointer" />
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
                    <tbody class="divide-y divide-slate-100/80">
                        <tr v-for="task in tasks.data" :key="task.id" 
                            class="group transition-all duration-200 hover:bg-sky-50/30 relative"
                            :class="{
                                'bg-emerald-50/40 hover:bg-emerald-50/70': task.status === 'completed',
                                'bg-orange-50/40 hover:bg-orange-50/70': task.status === 'revision'
                            }">
                            
                            <!-- 0. CHECKBOX -->
                            <td class="py-3 px-4 relative">
                                <!-- Aksen garis warna di sebelah kiri -->
                                <div class="absolute left-0 top-0 bottom-0 w-1 transition-all duration-200" 
                                    :class="{
                                        'bg-emerald-400': task.status === 'completed',
                                        'bg-orange-400': task.status === 'revision',
                                        'bg-transparent group-hover:bg-sky-400': task.status !== 'completed' && task.status !== 'revision'
                                    }">
                                </div>
                                
                                <div class="flex justify-center items-center h-full">
                                    <input type="checkbox" v-model="selectedTasks" :value="task.id" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 w-4 h-4 cursor-pointer" />
                                </div>
                            </td>

                            <!-- 1. PRODUCT -->
                            <td class="py-3 px-4 relative">
                                <span class="font-bold text-slate-700 text-xs truncate max-w-[120px] block" :title="task.product?.name">
                                    {{ task.product?.name || '-' }}
                                </span>
                            </td>

                            <!-- 2. FASKES -->
                            <td class="py-3 px-4">
                                <span class="text-xs font-semibold text-slate-600 flex items-center gap-1.5 truncate max-w-[150px]" :title="task.client?.name">
                                    🏥 {{ task.client?.name || '-' }}
                                </span>
                            </td>
                            
                            <!-- 3. FITUR (Modul) -->
                            <td class="py-3 px-4">
                                <div class="flex flex-col gap-1">
                                    <span class="font-bold text-slate-800 text-[13px] hover:text-sky-600 transition-colors cursor-pointer truncate max-w-[220px]" :title="task.title">
                                        <Link :href="showTask.url(task.id)">{{ task.title }}</Link>
                                    </span>
                                    <span v-if="task.modul" class="text-[11px] font-medium text-slate-500 flex items-center gap-1">
                                        <span class="w-1 h-1 rounded-full bg-slate-300"></span> {{ task.modul }}
                                    </span>
                                    <span class="text-[11px] font-medium text-slate-400">
                                        {{ task.comments_count ?? 0 }} komentar
                                    </span>
                                </div>
                            </td>
                            
                            <!-- 4. TASK (URL) -->
                            <td class="py-3 px-4 text-center">
                                <a v-if="task.task_url && task.task_url !== '-'" :href="task.task_url" target="_blank" 
                                   class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-sky-50 text-sky-600 hover:bg-sky-500 hover:text-white transition-all duration-300 shadow-sm border border-sky-100 hover:scale-110" title="Buka Dokumen/URL">
                                    <ExternalLink class="h-3.5 w-3.5" />
                                </a>
                                <span v-else class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-slate-50 text-slate-300 border border-slate-100" title="Tidak ada URL">
                                    <span class="text-lg leading-none -mt-1">-</span>
                                </span>
                            </td>
                            
                            <!-- 5. JENIS / KETERANGAN -->
                            <td class="py-3 px-4">
                                <div class="flex flex-col gap-1 items-start">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wider w-fit border shadow-sm"
                                        :class="{
                                            'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200': task.category === 'Fitur Berbayar',
                                            'bg-indigo-50 text-indigo-700 border-indigo-200': task.category === 'Regulasi',
                                            'bg-sky-50 text-sky-700 border-sky-200': task.category === 'Saran Fitur',
                                            'bg-rose-50 text-rose-700 border-rose-200': task.category === 'Prioritas',
                                        }">
                                        {{ task.category }}
                                    </span>
                                    <span class="text-[11px] text-slate-500 truncate max-w-[180px] italic mt-0.5" :title="task.description">
                                        {{ task.description || 'Tidak ada keterangan.' }}
                                    </span>
                                </div>
                            </td>

                            <!-- 6. ENGINEER -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-full flex items-center justify-center text-[9px] font-extrabold border border-white shadow-sm ring-1 ring-slate-100"
                                        :class="getAvatarColor(task.engineer?.name || task.assignee?.name)">
                                        {{ getInitials(task.engineer?.name || task.assignee?.name) }}
                                    </div>
                                    <span class="text-xs font-bold text-slate-700 truncate max-w-[100px]" :title="task.engineer?.name || task.assignee?.name">{{ task.engineer?.name || task.assignee?.name || '-' }}</span>
                                </div>
                            </td>
                            
                            <!-- 7. DOKUMEN -->
                            <td class="py-3 px-4 text-center">
                                <span class="text-[10px] font-medium text-slate-400 bg-slate-50 px-2 py-1 rounded-md border border-slate-100">Belum ada</span>
                            </td>

                            <!-- 8. TANGGAL RELEASE -->
                            <td class="py-3 px-4 text-center">
                                <span class="text-xs font-semibold text-slate-600">
                                    {{ task.release_date ? new Date(task.release_date).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) : '-' }}
                                </span>
                            </td>

                            <!-- 8.5. SLA STATUS -->
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold tracking-wide shadow-sm"
                                    :class="{
                                        'bg-emerald-100 text-emerald-700 border border-emerald-200': task.sla_status === 'completed_on_time',
                                        'bg-amber-100 text-amber-700 border border-amber-200': task.sla_status === 'completed_late',
                                        'bg-blue-100 text-blue-700 border border-blue-200': task.sla_status === 'on_track',
                                        'bg-orange-100 text-orange-700 border border-orange-200': task.sla_status === 'warning',
                                        'bg-red-100 text-red-700 border border-red-200': task.sla_status === 'overdue',
                                        'bg-slate-100 text-slate-500 border border-slate-200': task.sla_status === 'unknown',
                                    }" :title="'Batas waktu SLA: ' + (task.sla ? task.sla.max_days + ' hari' : 'tidak ada config')">
                                    {{ task.sla_status.replace(/_/g, ' ').toUpperCase() }}
                                </span>
                            </td>

                            <!-- 9. CEK (TOGGLE BUTTONS MODERN) -->
                            <td class="py-3 px-4">
                                <div v-if="!task.can_update_status"
                                     class="flex items-center justify-center h-7 w-full max-w-[120px] rounded-md bg-slate-100/80 text-slate-400 text-[9px] font-bold border border-slate-200 cursor-not-allowed mx-auto opacity-70">
                                    NO AKSES
                                </div>
                                <div v-else-if="!task.task_url || task.task_url === '-'" 
                                     class="flex items-center justify-center h-7 w-full max-w-[120px] rounded-md bg-slate-100/80 text-slate-400 text-[9px] font-bold border border-slate-200 cursor-not-allowed mx-auto opacity-70">
                                    <Lock class="h-3 w-3 mr-1" /> URL KOSONG
                                </div>
                                <div v-else class="flex items-center justify-center p-0.5 rounded-lg bg-slate-100/80 border border-slate-200/60 mx-auto w-fit shadow-inner">
                                    <button @click="toggleCekStatus(task, 'revision')"
                                            class="flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-bold transition-all duration-200"
                                            :class="task.status === 'revision' ? 'bg-white text-orange-600 shadow-sm border border-orange-200/50 ring-1 ring-orange-500/20' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-200/80'">
                                        <AlertCircle class="h-3 w-3" :class="task.status === 'revision' ? 'text-orange-500' : 'text-slate-400'" /> Rev
                                    </button>
                                    <button @click="toggleCekStatus(task, 'completed')"
                                            class="flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-bold transition-all duration-200"
                                            :class="task.status === 'completed' ? 'bg-white text-emerald-600 shadow-sm border border-emerald-200/50 ring-1 ring-emerald-500/20' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-200/80'">
                                        <CheckCircle2 class="h-3 w-3" :class="task.status === 'completed' ? 'text-emerald-500' : 'text-slate-400'" /> OK
                                    </button>
                                </div>
                            </td>
                            
                            <!-- 10. STATUS -->
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md text-[10px] font-bold tracking-wider shadow-sm"
                                    :class="{
                                        'bg-white text-slate-500 border border-slate-200': task.status === 'open' || task.status === 'in_progress',
                                        'bg-white text-orange-700 border border-orange-200': task.status === 'revision',
                                        'bg-white text-emerald-700 border border-emerald-200': task.status === 'completed'
                                    }">
                                    <span v-if="task.status === 'open' || task.status === 'in_progress'" class="flex items-center gap-1.5">
                                        <span class="relative flex h-1.5 w-1.5"><span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-slate-400"></span></span>
                                        BELUM
                                    </span>
                                    <span v-else-if="task.status === 'revision'" class="flex items-center gap-1.5">
                                        <span class="relative flex h-1.5 w-1.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span><span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-orange-500"></span></span>
                                        REVISI
                                    </span>
                                    <span v-else class="flex items-center gap-1.5">
                                        <span class="relative flex h-1.5 w-1.5"><span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span></span>
                                        SELESAI
                                    </span>
                                </span>
                            </td>
                            
                            <!-- 11. AKSI -->
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1 opacity-50 group-hover:opacity-100 transition-opacity duration-200">
                                    <Link v-if="task.can_edit" :href="`/tasks/${task.id}/edit`">
                                        <Button variant="ghost" size="icon" class="h-7 w-7 rounded-lg text-blue-600 hover:text-blue-700 hover:bg-blue-100/80 transition-colors">
                                            <Edit class="h-3.5 w-3.5" />
                                        </Button>
                                    </Link>
                                    <Button v-if="task.can_delete" variant="ghost" size="icon" @click="deleteTask(task.id, task.title)" class="h-7 w-7 rounded-lg text-rose-500 hover:text-rose-600 hover:bg-rose-100/80 transition-colors">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        
                        <tr v-if="tasks.data.length === 0">
                            <td colspan="12" class="py-20 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <div class="h-16 w-16 bg-slate-50 rounded-2xl flex items-center justify-center mb-4 border border-slate-100 shadow-sm rotate-3">
                                        <ListTodo class="h-8 w-8 text-slate-300 -rotate-3" />
                                    </div>
                                    <p class="text-base font-bold text-slate-700">Pencarian Tidak Ditemukan</p>
                                    <p class="text-xs mt-1 mb-4 text-slate-500 max-w-sm">Tidak ada data tiket task yang sesuai dengan kombinasi filter yang Anda pilih saat ini.</p>
                                    <Button @click="resetFilter" variant="outline" class="h-9 border-slate-200 hover:bg-slate-50 rounded-xl font-semibold text-slate-600 text-xs">
                                        <RotateCcw class="h-3.5 w-3.5 mr-2 text-slate-400" /> Reset Filter
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modern Pagination Footer -->
            <div class="bg-slate-50/80 border-t border-slate-200 p-3 px-5 flex flex-col sm:flex-row justify-between items-center text-xs font-semibold text-slate-500">
                <div>
                    Menampilkan <span class="font-extrabold text-slate-800">{{ tasks.data.length > 0 ? (tasks.current_page - 1) * tasks.per_page + 1 : 0 }}</span> - 
                    <span class="font-extrabold text-slate-800">{{ Math.min(tasks.current_page * tasks.per_page, tasks.total) }}</span> dari <span class="font-extrabold text-slate-800">{{ tasks.total }}</span> tiket
                </div>
                
                <div v-if="tasks.links && tasks.links.length > 3" class="flex items-center gap-1 mt-3 sm:mt-0 bg-white p-1 rounded-xl border border-slate-200 shadow-sm">
                    <template v-for="(link, key) in tasks.links" :key="key">
                        <Link v-if="link.url"
                            :href="link.url" 
                            class="min-w-[2rem] h-7 flex items-center justify-center px-2 rounded-md transition-all duration-200 font-bold"
                            :class="link.active ? 'bg-sky-600 text-white shadow-sm shadow-sky-500/30' : 'bg-transparent hover:bg-slate-100 text-slate-600'"
                            v-html="link.label" />
                        <span v-else class="min-w-[2rem] h-7 flex items-center justify-center px-2 rounded-md bg-transparent text-slate-300 font-bold cursor-not-allowed" v-html="link.label"></span>
                    </template>
                </div>
            </div>
        </div>

    </div>
</template>
