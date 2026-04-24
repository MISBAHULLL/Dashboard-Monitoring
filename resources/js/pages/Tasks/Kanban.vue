<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Columns3, ListTodo } from 'lucide-vue-next';
import { dashboard} from '@/routes';
import {index as tasksIndex} from '@/actions/App/Http/Controllers/TaskController';

// UI Components
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    tasks: Array<any>;
}>();

// Breadcrumbs
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Monitoring Task', href: tasksIndex.url() },
            { title: 'Kanban Board', href: '#' },
        ],
    },
});

// Setup 4 Kolom Status
const columns = [
    { id: 'open', title: 'Open (Baru)', color: 'border-amber-200 bg-amber-50', headerColor: 'bg-amber-100 text-amber-800' },
    { id: 'in_progress', title: 'In Progress', color: 'border-blue-200 bg-blue-50', headerColor: 'bg-blue-100 text-blue-800' },
    { id: 'revision', title: 'Revisi', color: 'border-red-200 bg-red-50', headerColor: 'bg-red-100 text-red-800' },
    { id: 'completed', title: 'Completed', color: 'border-emerald-200 bg-emerald-50', headerColor: 'bg-emerald-100 text-emerald-800' },
];

// Memecah data dari Database ke dalam masing-masing kotak status
const groupedTasks = computed(() => {
    const groups: Record<string, any[]> = { open: [], in_progress: [], revision: [], completed: [] };
    props.tasks.forEach(task => {
        if (groups[task.status]) groups[task.status].push(task);
    });
    return groups;
});

// --- LOGIKA DRAG & DROP (Tarik & Letakkan) ---
const draggedTaskId = ref<number | null>(null);

// 1. Saat Tiket mulai ditarik
const onDragStart = (e: DragEvent, taskId: number) => {
    draggedTaskId.value = taskId;
    if (e.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', taskId.toString()); // Bawa ID tiketnya
    }
};

// 2. Izin melintas di atas kolom lain
const onDragOver = (e: DragEvent) => {
    e.preventDefault(); 
};

// 3. Saat Tiket dilepaskan (Jatuh) di kolom baru
const onDrop = (e: DragEvent, newStatus: string) => {
    e.preventDefault();
    if (!draggedTaskId.value) return;
    
    const taskId = draggedTaskId.value;
    const task = props.tasks.find(t => t.id === taskId);
    
    // Jika statusnya berubah, update!
    if (task && task.status !== newStatus) {
        // A. Optimistic UI Update (Ubah di layar detik itu juga tanpa loading)
        task.status = newStatus;
        
        // B. Update ke Database (di balik layar)
        router.patch(`/tasks/${taskId}/status`, { status: newStatus }, {
            preserveScroll: true, // Jangan buat halamannya terlempar ke atas
        });
    }
    draggedTaskId.value = null; // Selesai drag
};
</script>

<template>
    <Head title="Kanban Board" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
        
        <!-- Header & Navigasi -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shrink-0">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-primary flex items-center gap-2">
                    <Columns3 class="h-8 w-8 text-sky-600" />
                    Papan Kanban
                </h1>
                <p class="text-muted-foreground mt-1">Tarik dan letakkan (Drag & Drop) tiket untuk memperbarui statusnya.</p>
            </div>
            
            <div class="flex items-center gap-2">
                <Link :href="tasksIndex.url()">
                    <Button variant="outline" class="flex items-center gap-2 border-slate-300">
                        <ListTodo class="h-4 w-4" /> Mode Tabel Biasa
                    </Button>
                </Link>
            </div>
        </div>

        <!-- Papan Grid Kanban -->
        <div class="flex-1 overflow-x-auto overflow-y-hidden pb-4">
            <div class="flex h-full min-w-max gap-6 items-start">
                
                <!-- Meloop 4 Kolom -->
                <div v-for="column in columns" :key="column.id" 
                     class="flex flex-col w-80 max-h-full rounded-xl border border-border bg-slate-100/50 dark:bg-slate-900/50 shadow-inner"
                     @dragover="onDragOver"
                     @drop="(e) => onDrop(e, column.id)">
                    
                    <!-- Judul Kolom -->
                    <div class="p-3 border-b flex items-center justify-between shrink-0" :class="column.headerColor + ' rounded-t-xl'">
                        <h3 class="font-bold text-sm uppercase tracking-wider">{{ column.title }}</h3>
                        <span class="bg-white/50 dark:bg-black/20 text-xs px-2 py-0.5 rounded-full font-bold">
                            {{ groupedTasks[column.id].length }}
                        </span>
                    </div>

                    <!-- Area Kartu Tiket (Bisa di-scroll kalau panjang) -->
                    <div class="flex-1 overflow-y-auto p-3 space-y-3 min-h-[150px]">
                        
                        <!-- Meloop Tiket di dalam Kolom Ini -->
                        <div v-for="task in groupedTasks[column.id]" :key="task.id"
                             draggable="true"
                             @dragstart="(e) => onDragStart(e, task.id)"
                             class="bg-card p-4 rounded-lg shadow-sm border border-border cursor-grab active:cursor-grabbing hover:border-sky-300 transition-all duration-200"
                             :class="{ 'opacity-50 scale-95': draggedTaskId === task.id }">
                            
                            <div class="flex justify-between items-start mb-2">
                                <Badge variant="outline" class="text-[10px] bg-muted">{{ task.category }}</Badge>
                                <!-- Indikator Lampu Prioritas -->
                                <span class="w-2 h-2 rounded-full mt-1 shadow-sm" 
                                      :class="{
                                        'bg-red-500': task.priority === 'urgent',
                                        'bg-amber-500': task.priority === 'high',
                                        'bg-blue-500': task.priority === 'medium',
                                        'bg-slate-300': task.priority === 'low'
                                      }" :title="`Priority: ${task.priority}`"></span>
                            </div>
                            
                            <h4 class="font-bold text-sm text-foreground mb-1 leading-snug">{{ task.title }}</h4>
                            <p class="text-xs text-muted-foreground mb-3 font-medium">{{ task.client?.name }}</p>
                            
                            <div class="flex justify-between items-center border-t border-border pt-2 mt-2">
                                <span class="text-[10px] text-muted-foreground truncate w-24" :title="task.product?.name">
                                    {{ task.product ? task.product.name : 'Tim Produk' }}
                                </span>
                                <Link :href="`/tasks/${task.id}/edit`" class="text-sky-600 hover:text-sky-800 text-[10px] font-bold" @click.stop>
                                    Edit
                                </Link>
                            </div>
                        </div>

                        <!-- Jika Kosong -->
                        <div v-if="groupedTasks[column.id].length === 0" class="text-center py-6 text-sm text-muted-foreground border-2 border-dashed border-border rounded-lg">
                            Kosong
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
