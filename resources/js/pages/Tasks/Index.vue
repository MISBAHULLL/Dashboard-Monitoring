<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ListTodo, Plus, Edit, Trash2 } from 'lucide-vue-next';
import { dashboard } from '@/routes';

// UI Components
import { Button } from '@/components/ui/button';

// 1. Props dari TaskController@index
const props = defineProps<{
    tasks: {
        data: Array<any>;
        links: Array<any>;
    };
    clients: Array<any>;
    teams: Array<any>;
}>();

// 2. Setup Breadcrumbs
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Daftar Task', href: '#' },
        ],
    },
});

// Fungsi Menghapus Task
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
            
            <!-- Menggunakan komponen <Link> untuk berpindah HALAMAN PENUH ke /tasks/create -->
            <Link href="/tasks/create">
                <Button class="flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white">
                    <Plus class="h-4 w-4" /> Tambah Task Baru
                </Button>
            </Link>
        </div>

        <!-- Tabel Task -->
        <div class="relative flex-1 rounded-xl border border-sidebar-border bg-card shadow-sm">
            <div class="overflow-x-auto p-4">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-muted/50 text-muted-foreground border-b border-border">
                        <tr>
                            <th class="py-3 px-4 font-medium">No</th>
                            <th class="py-3 px-4 font-medium">Judul Task</th>
                            <th class="py-3 px-4 font-medium">Faskes (Client)</th>
                            <th class="py-3 px-4 font-medium">Modul</th>
                            <th class="py-3 px-4 font-medium text-center">Prioritas</th>
                            <th class="py-3 px-4 font-medium text-center">Status</th>
                            <th class="py-3 px-4 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loop data menggunakan tasks.data karena formatnya Pagination (dibagi per 10 baris) -->
                        <tr v-for="(task, index) in tasks.data" :key="task.id" class="border-b border-border last:border-0 hover:bg-muted/30">
                            <td class="py-3 px-4">{{ index + 1 }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-primary max-w-xs truncate" :title="task.title">{{ task.title }}</div>
                                <div class="text-xs text-muted-foreground">Kategori: {{ task.category }}</div>
                            </td>
                            <td class="py-3 px-4 font-medium">{{ task.client?.name }}</td>
                            <td class="py-3 px-4">{{ task.modul || '-' }}</td>
                            
                            <td class="py-3 px-4 text-center">
                                <!-- Badge Prioritas Manual Tailwind -->
                                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-semibold capitalize"
                                    :class="{
                                        'border-red-200 text-red-600 bg-red-50': task.priority === 'urgent',
                                        'border-amber-200 text-amber-600 bg-amber-50': task.priority === 'high',
                                        'border-blue-200 text-blue-600 bg-blue-50': task.priority === 'medium',
                                        'border-slate-200 text-slate-600 bg-slate-50': task.priority === 'low'
                                    }">
                                    {{ task.priority }}
                                </span>
                            </td>
                            
                            <td class="py-3 px-4 text-center">
                                <!-- Badge Status Manual Tailwind -->
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                    :class="{
                                        'bg-amber-100 text-amber-800': task.status === 'open',
                                        'bg-blue-100 text-blue-800': task.status === 'in_progress',
                                        'bg-red-100 text-red-800': task.status === 'revision',
                                        'bg-emerald-100 text-emerald-800': task.status === 'completed'
                                    }">
                                    {{ task.status.replace('_', ' ') }}
                                </span>
                            </td>
                            
                            <td class="py-3 px-4 text-right space-x-2 flex justify-end">
                                <!-- Tombol Edit megarah ke halaman /tasks/{id}/edit -->
                                <Link :href="`/tasks/${task.id}/edit`">
                                    <Button variant="outline" size="sm" class="h-8 px-2 text-blue-600 border-blue-200 hover:bg-blue-50">
                                        <Edit class="h-4 w-4" />
                                    </Button>
                                </Link>
                                <Button variant="outline" size="sm" @click="deleteTask(task.id, task.title)" class="h-8 px-2 text-red-600 border-red-200 hover:bg-red-50">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </td>
                        </tr>
                        
                        <tr v-if="tasks.data.length === 0">
                            <td colspan="7" class="py-12 text-center text-muted-foreground">
                                <ListTodo class="h-12 w-12 mx-auto text-slate-300 mb-3" />
                                <p>Belum ada Task yang dicatat.</p>
                                <p class="text-sm">Klik "Tambah Task Baru" untuk membuat tiket pertama Anda.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Sistem Pagination Bawaan Laravel -> Vue -->
            <div v-if="tasks.links && tasks.links.length > 3" class="p-4 border-t border-border flex justify-center">
                <div class="flex space-x-1">
                    <Link v-for="(link, key) in tasks.links" :key="key" 
                        :href="link.url || '#'" 
                        class="px-3 py-1 border rounded-md text-sm transition-colors"
                        :class="link.active ? 'bg-sky-600 text-white border-sky-600' : 'bg-card hover:bg-muted text-foreground border-border'"
                        v-html="link.label" />
                </div>
            </div>
        </div>

    </div>
</template>
