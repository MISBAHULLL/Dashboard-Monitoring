<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ListTodo, Plus, Edit, Trash2, Filter, RotateCcw, Link as LinkIcon, Lock } from 'lucide-vue-next';
import { dashboard } from '@/routes';

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
    clients: Array<any>;
    product_teams: Array<any>;
    engineer_teams: Array<any>;
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

// State Filter (Mengambil nilai default dari URL params jika ada)
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

// Watcher untuk Filter Otomatis (Real-time tanpa tombol Cari)
watch(filterForm, (newVal) => {
    router.get('/tasks', newVal, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, { deep: true });

// Reset Filter
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

// Fungsi Update Status CEK (Radio Button logic)
const toggleCekStatus = (task: any, newStatus: string) => {
    if (!task.task_url && (newStatus === 'completed' || newStatus === 'revision')) {
        return; // Terkunci jika URL kosong
    }

    // Double click to cancel (kembali ke Open/In Progress, default 'open')
    let finalStatus = newStatus;
    if (task.status === newStatus) {
        finalStatus = 'open'; // Batal, kembali ke open/belum di cek
    }

    router.patch(`/tasks/${task.id}/status`, { status: finalStatus }, {
        preserveScroll: true,
        preserveState: true
    });
};

const deleteTask = (id: number, title: string) => {
    if (confirm(`Apakah Anda yakin ingin menghapus Task: "${title}"?`)) {
        useForm({}).delete(`/tasks/${id}`);
    }
};
</script>

<template>
    <Head title="Monitoring Task" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-8">
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-primary flex items-center gap-2">
                    <ListTodo class="h-8 w-8 text-sky-600" />
                    Monitoring Task
                </h1>
                <p class="text-muted-foreground mt-1">Pantau dan kelola tiket permintaan Faskes secara real-time.</p>
            </div>
            <Link href="/tasks/create">
                <Button class="flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white shadow-md">
                    <Plus class="h-4 w-4" /> Tambah Task
                </Button>
            </Link>
        </div>

        <!-- Filter Box (Meniru Desain Legacy) -->
        <div class="bg-card rounded-xl border border-border shadow-sm overflow-hidden">
            <div class="bg-sky-700 px-6 py-3 flex justify-between items-center text-white">
                <div class="flex items-center gap-2">
                    <Filter class="h-5 w-5" />
                    <span class="font-semibold tracking-wide">Filter Data Task</span>
                    <span class="text-xs font-normal opacity-80 ml-2 hidden sm:inline">Gunakan filter untuk mempersempit pencarian</span>
                </div>
                <Button @click="resetFilter" variant="ghost" size="sm" class="h-8 text-white hover:bg-sky-600 hover:text-white border border-white/20">
                    <RotateCcw class="h-4 w-4 mr-2" /> Reset
                </Button>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Baris 1 -->
                <div class="space-y-1">
                    <Label class="text-xs text-muted-foreground flex items-center gap-1">Product</Label>
                    <select v-model="filterForm.product_id" class="w-full text-sm border-input rounded-md h-9 focus:ring-sky-500 focus:border-sky-500 bg-background">
                        <option value="">Semua Product</option>
                        <option v-for="team in product_teams" :key="team.id" :value="team.id">{{ team.name }}</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <Label class="text-xs text-muted-foreground flex items-center gap-1">Client / Faskes</Label>
                    <select v-model="filterForm.client_id" class="w-full text-sm border-input rounded-md h-9 focus:ring-sky-500 focus:border-sky-500 bg-background">
                        <option value="">Semua Faskes</option>
                        <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <Label class="text-xs text-muted-foreground flex items-center gap-1">Enginer</Label>
                    <select v-model="filterForm.engineer_id" class="w-full text-sm border-input rounded-md h-9 focus:ring-sky-500 focus:border-sky-500 bg-background">
                        <option value="">Semua Enginer</option>
                        <option v-for="team in engineer_teams" :key="team.id" :value="team.id">{{ team.name }}</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <Label class="text-xs text-muted-foreground flex items-center gap-1">Jenis Task</Label>
                    <select v-model="filterForm.category" class="w-full text-sm border-input rounded-md h-9 focus:ring-sky-500 focus:border-sky-500 bg-background">
                        <option value="">Semua Jenis</option>
                        <option value="Fitur Berbayar">Fitur Berbayar</option>
                        <option value="Regulasi">Regulasi</option>
                        <option value="Saran Fitur">Saran Fitur</option>
                    </select>
                </div>

                <!-- Baris 2 -->
                <div class="space-y-1">
                    <Label class="text-xs text-muted-foreground flex items-center gap-1">Status Link</Label>
                    <select v-model="filterForm.has_link" class="w-full text-sm border-input rounded-md h-9 focus:ring-sky-500 focus:border-sky-500 bg-background">
                        <option value="">Semua Status</option>
                        <option value="yes">Sudah Ada Link</option>
                        <option value="no">Belum Ada Link</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <Label class="text-xs text-muted-foreground flex items-center gap-1">Status Cek</Label>
                    <select v-model="filterForm.status" class="w-full text-sm border-input rounded-md h-9 focus:ring-sky-500 focus:border-sky-500 bg-background">
                        <option value="">Semua Status Cek</option>
                        <option value="open">Belum Di Cek</option>
                        <option value="revision">Revisi</option>
                        <option value="completed">Selesai</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <Label class="text-xs text-muted-foreground flex items-center gap-1">Tanggal Dari</Label>
                    <Input type="date" v-model="filterForm.date_from" class="h-9 text-sm focus:ring-sky-500 focus:border-sky-500" />
                </div>
                <div class="space-y-1">
                    <Label class="text-xs text-muted-foreground flex items-center gap-1">Tanggal Sampai</Label>
                    <Input type="date" v-model="filterForm.date_to" class="h-9 text-sm focus:ring-sky-500 focus:border-sky-500" />
                </div>
            </div>
            
            <div class="px-6 pb-4 pt-0">
                 <Input type="text" v-model="filterForm.search" placeholder="Cari berdasarkan Judul Task atau Modul..." class="h-10 bg-muted/30 border-sky-200 focus:border-sky-500 focus:ring-sky-500 shadow-inner" />
            </div>
        </div>

        <!-- Tabel Task (Legacy Column Parity) -->
        <div class="relative flex-1 rounded-xl border border-border bg-card shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border-b border-border uppercase text-[10px] font-bold tracking-wider">
                        <tr>
                            <th class="py-4 px-4">PRODUCT</th>
                            <th class="py-4 px-4">FASKES</th>
                            <th class="py-4 px-4">FITUR</th>
                            <th class="py-4 px-4 text-center">TASK (URL)</th>
                            <th class="py-4 px-4">JENIS / KETERANGAN</th>
                            <th class="py-4 px-4">ENGINER</th>
                            <th class="py-4 px-4 text-center">DOKUMEN</th>
                            <th class="py-4 px-4">TANGGAL RELEASE</th>
                            <th class="py-4 px-4 text-center">CEK</th>
                            <th class="py-4 px-4 text-center">STATUS</th>
                            <th class="py-4 px-4 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="task in tasks.data" :key="task.id" 
                            class="border-b border-border last:border-0 transition-colors"
                            :class="{
                                'bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/20': task.status === 'completed',
                                'bg-orange-50 hover:bg-orange-100 dark:bg-orange-900/20': task.status === 'revision',
                                'hover:bg-muted/50': task.status !== 'completed' && task.status !== 'revision'
                            }">
                            
                            <td class="py-3 px-4 font-medium">{{ task.product?.name || '-' }}</td>
                            <td class="py-3 px-4 font-bold text-slate-700 dark:text-slate-300">{{ task.client?.name || '-' }}</td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-800 dark:text-slate-200">{{ task.title }}</div>
                                <div class="text-xs text-muted-foreground mt-0.5" v-if="task.modul">Modul: {{ task.modul }}</div>
                            </td>
                            
                            <td class="py-3 px-4 text-center">
                                <a v-if="task.task_url" :href="task.task_url" target="_blank" class="inline-flex items-center gap-1 text-sky-600 hover:text-sky-800 hover:underline text-xs font-medium bg-sky-50 px-2 py-1 rounded">
                                    <LinkIcon class="h-3 w-3" /> Link
                                </a>
                                <span v-else class="text-muted-foreground">-</span>
                            </td>
                            
                            <td class="py-3 px-4">
                                <div class="font-semibold text-purple-600 dark:text-purple-400 text-[10px] uppercase tracking-wide">{{ task.category }}</div>
                                <div class="text-xs text-muted-foreground mt-0.5 italic max-w-[200px] truncate">{{ task.description || '-' }}</div>
                            </td>
                            
                            <td class="py-3 px-4">{{ task.engineer?.name || task.assignee?.name || '-' }}</td>
                            
                            <td class="py-3 px-4 text-center text-slate-400 italic text-xs">Belum ada</td>
                            
                            <td class="py-3 px-4">{{ task.release_date ? new Date(task.release_date).toLocaleDateString('id-ID') : '-' }}</td>
                            
                            <td class="py-3 px-4">
                                <!-- CEK Logic -->
                                <div v-if="!task.task_url" class="flex flex-col gap-1 items-center justify-center opacity-40 cursor-not-allowed" title="Task URL belum diisi, tidak bisa merubah status cek">
                                    <Lock class="h-4 w-4 text-slate-500 mb-1" />
                                </div>
                                <div v-else class="flex flex-col gap-1.5 items-start text-[11px] font-medium">
                                    <label class="flex items-center gap-1.5 cursor-pointer hover:text-orange-600 transition-colors">
                                        <input type="radio" :name="'cek_' + task.id" :checked="task.status === 'revision'" @click.prevent="toggleCekStatus(task, 'revision')" class="text-orange-500 focus:ring-orange-500 h-3.5 w-3.5 cursor-pointer" />
                                        Revisi
                                    </label>
                                    <label class="flex items-center gap-1.5 cursor-pointer hover:text-emerald-600 transition-colors">
                                        <input type="radio" :name="'cek_' + task.id" :checked="task.status === 'completed'" @click.prevent="toggleCekStatus(task, 'completed')" class="text-emerald-500 focus:ring-emerald-500 h-3.5 w-3.5 cursor-pointer" />
                                        Selesai
                                    </label>
                                </div>
                            </td>
                            
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider shadow-sm"
                                    :class="{
                                        'border-slate-300 text-slate-600 bg-slate-100': task.status === 'open' || task.status === 'in_progress',
                                        'border-orange-200 text-orange-700 bg-orange-100': task.status === 'revision',
                                        'border-emerald-200 text-emerald-700 bg-emerald-100': task.status === 'completed'
                                    }">
                                    <span v-if="task.status === 'open' || task.status === 'in_progress'" class="flex items-center gap-1.5">
                                        <div class="h-1.5 w-1.5 rounded-full bg-slate-400"></div> BELUM DI CEK
                                    </span>
                                    <span v-else-if="task.status === 'revision'" class="flex items-center gap-1.5">
                                        <div class="h-1.5 w-1.5 rounded-full bg-orange-500 animate-pulse"></div> REVISI
                                    </span>
                                    <span v-else class="flex items-center gap-1.5">
                                        <div class="h-1.5 w-1.5 rounded-full bg-emerald-500"></div> SELESAI
                                    </span>
                                </span>
                            </td>
                            
                            <td class="py-3 px-4 text-right space-x-1 flex justify-end">
                                <Link :href="`/tasks/${task.id}/edit`">
                                    <Button variant="ghost" size="icon" class="h-8 w-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50">
                                        <Edit class="h-4 w-4" />
                                    </Button>
                                </Link>
                                <Button variant="ghost" size="icon" @click="deleteTask(task.id, task.title)" class="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </td>
                        </tr>
                        
                        <tr v-if="tasks.data.length === 0">
                            <td colspan="11" class="py-16 text-center text-muted-foreground bg-slate-50/50">
                                <ListTodo class="h-12 w-12 mx-auto text-slate-300 mb-3" />
                                <p class="text-lg font-medium text-slate-600">Pencarian Tidak Ditemukan</p>
                                <p class="text-sm mt-1">Coba sesuaikan filter pencarian atau klik Reset.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Pagination Meniru Sistem Lama -->
            <div class="bg-slate-50 border-t border-border p-4 flex flex-col sm:flex-row justify-between items-center text-sm text-slate-600">
                <div>
                    Menampilkan {{ tasks.data.length > 0 ? (tasks.current_page - 1) * tasks.per_page + 1 : 0 }} - 
                    {{ Math.min(tasks.current_page * tasks.per_page, tasks.total) }} dari {{ tasks.total }} task
                </div>
                
                <div v-if="tasks.links && tasks.links.length > 3" class="flex items-center space-x-1 mt-4 sm:mt-0">
                    <template v-for="(link, key) in tasks.links" :key="key">
                        <Link v-if="link.url"
                            :href="link.url" 
                            class="px-3 py-1 border rounded-sm transition-colors text-xs font-medium"
                            :class="link.active ? 'bg-sky-700 text-white border-sky-700' : 'bg-white hover:bg-slate-100 border-slate-300'"
                            v-html="link.label" />
                        <span v-else class="px-3 py-1 border rounded-sm bg-slate-100 text-slate-400 border-slate-200 text-xs font-medium cursor-not-allowed" v-html="link.label"></span>
                    </template>
                </div>
            </div>
        </div>

    </div>
</template>
