<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ArrowLeft, Save, BookmarkPlus } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { store as taskTemplatesStore } from '@/actions/App/Http/Controllers/TaskTemplateController';

const props = defineProps<{
    clients: Array<{ id: number; name: string }>;
    product_teams: Array<{ id: number; name: string }>;
    engineer_teams: Array<{ id: number; name: string }>;
    users: Array<{ id: number; name: string }>;
    existing_modules: Array<string>;
    task_templates: Array<any>;
}>();

// Inertia Form State
const form = useForm({
    title: '',
    client_id: '',
    product_id: '',
    engineer_id: '',
    assigned_to: '',
    description: '',
    modul: '',
    task_url: '',
    category: 'Saran Fitur', // Default value
    priority: 'medium', // Default value
    status: 'open', // Default value
    release_date: '',
});

const submitForm = () => {
    form.post('/tasks');
};

const applyTemplate = (templateId: number) => {
    const template = props.task_templates.find(t => t.id === templateId);
    if (template) {
        if (template.client_id) form.client_id = template.client_id;
        if (template.product_id) form.product_id = template.product_id;
        if (template.engineer_id) form.engineer_id = template.engineer_id;
        if (template.category) form.category = template.category;
        if (template.priority) form.priority = template.priority;
        if (template.description) form.description = template.description;
    }
};

const saveAsTemplate = () => {
    const name = prompt('Masukkan nama template untuk pola form ini:');
    if (!name) return;

    router.post(taskTemplatesStore.url(), {
        name,
        client_id: form.client_id,
        product_id: form.product_id,
        engineer_id: form.engineer_id,
        description: form.description,
        category: form.category,
        priority: form.priority,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => alert('Template berhasil disimpan dan bisa digunakan!'),
        onError: (err) => {
            alert('Gagal menyimpan template. Pastikan Faskes dan Divisi Produk sudah diisi.');
        }
    });
};

const deleteTemplate = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus template ini?')) {
        router.delete(`/task-templates/${id}`, {
            preserveScroll: true,
            preserveState: true,
        });
    }
};
</script>

<template>
    <Head title="Tambah Task Baru" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-y-auto rounded-xl p-4 md:p-8">
        
        <!-- Header -->
        <div class="flex items-center gap-4 border-b pb-4">
            <Link href="/tasks">
                <Button variant="outline" size="icon" class="h-10 w-10 shrink-0 rounded-full">
                    <ArrowLeft class="h-5 w-5" />
                </Button>
            </Link>
            <div class="flex-1">
                <h1 class="text-2xl font-bold tracking-tight text-primary">Buat Tiket Task Baru</h1>
                <p class="text-sm text-muted-foreground mt-1">Isi rincian informasi di bawah untuk mendaftarkan task ke sistem.</p>
            </div>
            <div>
                <Button type="button" @click="saveAsTemplate" variant="outline" class="flex items-center gap-2 h-10 border-sky-200 text-sky-700 bg-sky-50 hover:bg-sky-100">
                    <BookmarkPlus class="h-4 w-4" />
                    Simpan Sebagai Template
                </Button>
            </div>
        </div>

        <!-- Pilihan Template -->
        <div v-if="task_templates.length > 0" class="max-w-5xl bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center gap-4 shadow-sm">
            <div class="text-sm font-semibold text-slate-700 whitespace-nowrap">Pilih Template Cepat:</div>
            <div class="flex gap-2 flex-wrap">
                <div v-for="template in task_templates" :key="template.id" class="flex items-center bg-white border border-slate-200 rounded-full pl-3 pr-1 shadow-sm hover:border-slate-300 transition-all">
                    <button type="button" @click="applyTemplate(template.id)" class="text-xs font-medium text-slate-700 hover:text-sky-600 outline-none">
                        {{ template.name }}
                    </button>
                    <div class="w-px h-3 bg-slate-200 mx-2"></div>
                    <button type="button" @click="deleteTemplate(template.id)" class="text-slate-400 hover:text-red-500 rounded-full p-1 hover:bg-red-50 transition-colors" title="Hapus template">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Form Layout menggunakan Grid CSS untuk 2 Kolom -->
        <form @submit.prevent="submitForm" class="max-w-5xl">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- KOLOM KIRI (Informasi Utama) -->
                <div class="space-y-6">
                    <div class="space-y-2">
                        <Label for="title">Judul Task <span class="text-red-500">*</span></Label>
                        <Input id="title" v-model="form.title" placeholder="Contoh: Penambahan tombol Export Excel..." :class="{ 'border-red-500': form.errors.title }" />
                        <p v-if="form.errors.title" class="text-sm text-red-500">{{ form.errors.title }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Deskripsi Detail</Label>
                        <Textarea id="description" v-model="form.description" rows="5" placeholder="Jelaskan kebutuhan faskes secara rinci..." :class="{ 'border-red-500': form.errors.description }" />
                        <p v-if="form.errors.description" class="text-sm text-red-500">{{ form.errors.description }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="modul">Modul / Menu Terkait</Label>
                            <Input id="modul" list="modul-options" v-model="form.modul" placeholder="Ketik atau pilih modul..." :class="{ 'border-red-500': form.errors.modul }" />
                            <datalist id="modul-options">
                                <option v-for="m in existing_modules" :key="m" :value="m"></option>
                            </datalist>
                            <p v-if="form.errors.modul" class="text-sm text-red-500">{{ form.errors.modul }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="task_url">Link Referensi / Docs</Label>
                            <Input id="task_url" v-model="form.task_url" placeholder="https://..." :class="{ 'border-red-500': form.errors.task_url }" />
                            <p v-if="form.errors.task_url" class="text-sm text-red-500">{{ form.errors.task_url }}</p>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN (Pengaturan Divisi & Atribut) -->
                <div class="space-y-6 rounded-lg bg-muted/30 p-6 border border-border">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="client_id">Faskes (Client) <span class="text-red-500">*</span></Label>
                            <select id="client_id" v-model="form.client_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-sky-600">
                                <option value="" disabled>-- Pilih Faskes --</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                            </select>
                            <p v-if="form.errors.client_id" class="text-sm text-red-500">{{ form.errors.client_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="category">Kategori Task</Label>
                            <select id="category" v-model="form.category" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-sky-600">
                                <option value="Fitur Berbayar">Fitur Berbayar</option>
                                <option value="Regulasi">Regulasi</option>
                                <option value="Saran Fitur">Saran Fitur</option>
                                <option value="Prioritas">Prioritas</option>
                            </select>
                            <p v-if="form.errors.category" class="text-sm text-red-500">{{ form.errors.category }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="product_id">Divisi Produk <span class="text-red-500">*</span></Label>
                            <select id="product_id" v-model="form.product_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-sky-600">
                                <option value="" disabled>-- Pilih Tim Produk --</option>
                                <option v-for="team in product_teams" :key="team.id" :value="team.id">{{ team.name }}</option>
                            </select>
                            <p v-if="form.errors.product_id" class="text-sm text-red-500">{{ form.errors.product_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="engineer_id">Divisi Engineer</Label>
                            <select id="engineer_id" v-model="form.engineer_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-sky-600">
                                <option value="">(Bisa menyusul)</option>
                                <option v-for="team in engineer_teams" :key="team.id" :value="team.id">{{ team.name }}</option>
                            </select>
                            <p v-if="form.errors.engineer_id" class="text-sm text-red-500">{{ form.errors.engineer_id }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="priority">Tingkat Prioritas</Label>
                            <select id="priority" v-model="form.priority" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-sky-600">
                                <option value="urgent">Urgent</option>
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                            <p v-if="form.errors.priority" class="text-sm text-red-500">{{ form.errors.priority }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Status Pengerjaan</Label>
                            <select id="status" v-model="form.status" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-sky-600">
                                <option value="open">Open (Baru)</option>
                                <option value="in_progress">In Progress</option>
                                <option value="revision">Revisi</option>
                                <option value="completed">Selesai (Completed)</option>
                            </select>
                            <p v-if="form.errors.status" class="text-sm text-red-500">{{ form.errors.status }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="release_date">Tanggal Release (Target)</Label>
                            <Input id="release_date" type="date" v-model="form.release_date" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-sky-600" />
                            <p v-if="form.errors.release_date" class="text-sm text-red-500">{{ form.errors.release_date }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="assigned_to">Assign Ke PIC (Person in Charge)</Label>
                            <select id="assigned_to" v-model="form.assigned_to" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-sky-600">
                                <option value="">(Belum ditentukan)</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </select>
                            <p v-if="form.errors.assigned_to" class="text-sm text-red-500">{{ form.errors.assigned_to }}</p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Bagian Footer Form -->
            <div class="mt-8 flex justify-end gap-4 border-t pt-6">
                <Link href="/tasks">
                    <Button type="button" variant="outline" class="h-11 px-8">Batal</Button>
                </Link>
                <Button type="submit" :disabled="form.processing" class="h-11 px-8 bg-sky-600 hover:bg-sky-700 text-white flex items-center gap-2">
                    <Save class="h-4 w-4" />
                    {{ form.processing ? 'Menyimpan Data...' : 'Simpan Tiket Task' }}
                </Button>
            </div>
        </form>
    </div>
</template>
