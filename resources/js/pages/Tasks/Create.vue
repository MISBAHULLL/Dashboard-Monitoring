<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ArrowLeft, Save, BookmarkPlus, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { store as taskTemplatesStore } from '@/routes/task-templates';

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
    category: 'Saran Fitur',
    priority: 'medium',
    status: 'open',
    release_date: '',
    force_duplicate: false,
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
    showTemplateModal.value = true;
    templateName.value = '';
};

const submitTemplate = () => {
    if (!templateName.value.trim()) return;

    router.post(taskTemplatesStore.url(), {
        name: templateName.value.trim(),
        client_id: form.client_id,
        product_id: form.product_id,
        engineer_id: form.engineer_id,
        description: form.description,
        category: form.category,
        priority: form.priority,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            showTemplateModal.value = false;
            toast.success('Template berhasil disimpan dan bisa digunakan!');
        },
        onError: () => {
            toast.error('Gagal menyimpan template. Pastikan Faskes dan Divisi Produk sudah diisi.');
        }
    });
};

const deleteTemplate = (id: number) => {
    confirmAction.value = {
        open: true,
        title: 'Hapus Template',
        description: 'Apakah Anda yakin ingin menghapus template ini? Tindakan ini tidak dapat dibatalkan.',
        onConfirm: () => {
            router.delete(`/task-templates/${id}`, {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    confirmAction.value.open = false;
                    toast.success('Template berhasil dihapus.');
                },
            });
        },
    };
};

// State untuk modal template
const showTemplateModal = ref(false);
const templateName = ref('');

// State untuk confirm dialog
const confirmAction = ref({
    open: false,
    title: '',
    description: '',
    onConfirm: () => {},
});
</script>

<template>
    <Head title="Tambah Task Baru" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-y-auto rounded-xl p-4 md:p-8 bg-tm-page">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <Link href="/tasks">
                    <Button variant="outline" size="icon" class="h-10 w-10 shrink-0 rounded-full border-[1.5px] border-black shadow-[2px_2px_0_0_rgba(0,0,0,0.1)] hover:-translate-y-0.5 hover:shadow-[3px_3px_0_0_rgba(0,0,0,0.15)] transition-all bg-white">
                        <ArrowLeft class="h-5 w-5 text-tm-navy" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-tm-navy">Buat Tiket Task Baru</h1>
                    <p class="text-sm text-tm-text-secondary mt-1">Isi rincian informasi di bawah untuk mendaftarkan task ke sistem.</p>
                </div>
            </div>
            <div>
                <Button type="button" @click="saveAsTemplate" class="flex items-center gap-2 h-10 px-5 bg-white hover:bg-emerald-50 hover:text-emerald-700 active:bg-emerald-100 text-tm-navy border-[1.5px] border-black shadow-[2px_2px_0_0_rgba(0,0,0,0.1)] rounded-xl hover:-translate-y-0.5 hover:shadow-[3px_3px_0_0_rgba(0,0,0,0.15)] active:translate-y-0 active:shadow-[1px_1px_0_0_rgba(0,0,0,0.1)] transition-all font-medium tracking-wide text-sm">
                    <BookmarkPlus class="h-4 w-4" />
                    Simpan Sebagai Template
                </Button>
            </div>
        </div>

        <!-- Pilihan Template -->
        <div v-if="task_templates.length > 0" class="bg-white border-[1.5px] border-black/70 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center gap-4 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)]">
            <div class="text-sm font-bold text-tm-navy whitespace-nowrap">Pilih Template Cepat:</div>
            <div class="flex gap-2 flex-wrap">
                <div v-for="template in task_templates" :key="template.id" class="flex items-center bg-tm-navy-pale border-[1.5px] border-black/50 rounded-full pl-3 pr-1 shadow-sm hover:shadow-[2px_2px_0_0_rgba(0,0,0,0.08)] hover:-translate-y-px transition-all">
                    <button type="button" @click="applyTemplate(template.id)" class="text-xs font-semibold text-tm-navy hover:text-tm-navy-medium outline-none">
                        {{ template.name }}
                    </button>
                    <div class="w-px h-3 bg-slate-300 mx-2"></div>
                    <button type="button" @click="deleteTemplate(template.id)" class="text-slate-400 hover:text-red-500 rounded-full p-1 hover:bg-red-50 transition-colors" title="Hapus template">
                        <X class="h-3.5 w-3.5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <form @submit.prevent="submitForm">
            <div class="bg-white border-[2.5px] border-black rounded-[18px] shadow-[2px_4px_4px_0_rgba(11,42,107,0.15)] p-6 md:p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-[3px_6px_12px_0_rgba(11,42,107,0.2)]">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <!-- KOLOM KIRI (Informasi Utama) -->
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <Label for="title" class="text-[11px] font-bold text-tm-text-secondary uppercase tracking-wider ml-1">Judul Task <span class="text-red-500">*</span></Label>
                            <Input id="title" v-model="form.title" placeholder="Contoh: Penambahan tombol Export Excel..." class="h-10 border-[1.5px] border-black/70 rounded-lg bg-white hover:bg-slate-50 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] hover:-translate-y-px transition-all focus:border-tm-navy focus:ring-1 focus:ring-tm-navy" :class="{ '!border-red-500': form.errors.title }" />
                            <p v-if="form.errors.title" class="text-xs text-red-500 ml-1">{{ form.errors.title }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="description" class="text-[11px] font-bold text-tm-text-secondary uppercase tracking-wider ml-1">Deskripsi Detail</Label>
                            <Textarea id="description" v-model="form.description" rows="5" placeholder="Jelaskan kebutuhan faskes secara rinci..." class="border-[1.5px] border-black/70 rounded-lg bg-white hover:bg-slate-50 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] transition-all focus:border-tm-navy focus:ring-1 focus:ring-tm-navy resize-none" :class="{ '!border-red-500': form.errors.description }" />
                            <p v-if="form.errors.description" class="text-xs text-red-500 ml-1">{{ form.errors.description }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="modul" class="text-[11px] font-bold text-tm-text-secondary uppercase tracking-wider ml-1">Modul / Menu Terkait</Label>
                                <Input id="modul" list="modul-options" v-model="form.modul" placeholder="Ketik atau pilih modul..." class="h-10 border-[1.5px] border-black/70 rounded-lg bg-white hover:bg-slate-50 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] hover:-translate-y-px transition-all focus:border-tm-navy focus:ring-1 focus:ring-tm-navy" :class="{ '!border-red-500': form.errors.modul }" />
                                <datalist id="modul-options">
                                    <option v-for="m in existing_modules" :key="m" :value="m"></option>
                                </datalist>
                                <p v-if="form.errors.modul" class="text-xs text-red-500 ml-1">{{ form.errors.modul }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="task_url" class="text-[11px] font-bold text-tm-text-secondary uppercase tracking-wider ml-1">Link Referensi / Docs</Label>
                                <Input id="task_url" v-model="form.task_url" placeholder="https://..." class="h-10 border-[1.5px] border-black/70 rounded-lg bg-white hover:bg-slate-50 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] hover:-translate-y-px transition-all focus:border-tm-navy focus:ring-1 focus:ring-tm-navy" :class="{ '!border-red-500': form.errors.task_url }" />
                                <p v-if="form.errors.task_url" class="text-xs text-red-500 ml-1">{{ form.errors.task_url }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- KOLOM KANAN (Pengaturan Divisi & Atribut) -->
                    <div class="space-y-6 rounded-xl bg-tm-navy-pale/30 p-5 border-[1.5px] border-black/30">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="client_id" class="text-[11px] font-bold text-tm-text-secondary uppercase tracking-wider ml-1">Faskes (Client) <span class="text-red-500">*</span></Label>
                                <select id="client_id" v-model="form.client_id" class="flex h-10 w-full rounded-lg border-[1.5px] border-black/70 bg-white hover:bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] hover:-translate-y-px transition-all focus:border-tm-navy focus:ring-1 focus:ring-tm-navy focus:outline-none cursor-pointer">
                                    <option value="" disabled>-- Pilih Faskes --</option>
                                    <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                                </select>
                                <p v-if="form.errors.client_id" class="text-xs text-red-500 ml-1">{{ form.errors.client_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="category" class="text-[11px] font-bold text-tm-text-secondary uppercase tracking-wider ml-1">Kategori Task</Label>
                                <select id="category" v-model="form.category" class="flex h-10 w-full rounded-lg border-[1.5px] border-black/70 bg-white hover:bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] hover:-translate-y-px transition-all focus:border-tm-navy focus:ring-1 focus:ring-tm-navy focus:outline-none cursor-pointer">
                                    <option value="Fitur Berbayar">Fitur Berbayar</option>
                                    <option value="Regulasi">Regulasi</option>
                                    <option value="Saran Fitur">Saran Fitur</option>
                                    <option value="Prioritas">Prioritas</option>
                                </select>
                                <p v-if="form.errors.category" class="text-xs text-red-500 ml-1">{{ form.errors.category }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="product_id" class="text-[11px] font-bold text-tm-text-secondary uppercase tracking-wider ml-1">Divisi Produk <span class="text-red-500">*</span></Label>
                                <select id="product_id" v-model="form.product_id" class="flex h-10 w-full rounded-lg border-[1.5px] border-black/70 bg-white hover:bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] hover:-translate-y-px transition-all focus:border-tm-navy focus:ring-1 focus:ring-tm-navy focus:outline-none cursor-pointer">
                                    <option value="" disabled>-- Pilih Tim Produk --</option>
                                    <option v-for="team in product_teams" :key="team.id" :value="team.id">{{ team.name }}</option>
                                </select>
                                <p v-if="form.errors.product_id" class="text-xs text-red-500 ml-1">{{ form.errors.product_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="engineer_id" class="text-[11px] font-bold text-tm-text-secondary uppercase tracking-wider ml-1">Divisi Engineer</Label>
                                <select id="engineer_id" v-model="form.engineer_id" class="flex h-10 w-full rounded-lg border-[1.5px] border-black/70 bg-white hover:bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] hover:-translate-y-px transition-all focus:border-tm-navy focus:ring-1 focus:ring-tm-navy focus:outline-none cursor-pointer">
                                    <option value="">(Bisa menyusul)</option>
                                    <option v-for="team in engineer_teams" :key="team.id" :value="team.id">{{ team.name }}</option>
                                </select>
                                <p v-if="form.errors.engineer_id" class="text-xs text-red-500 ml-1">{{ form.errors.engineer_id }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="priority" class="text-[11px] font-bold text-tm-text-secondary uppercase tracking-wider ml-1">Tingkat Prioritas</Label>
                                <select id="priority" v-model="form.priority" class="flex h-10 w-full rounded-lg border-[1.5px] border-black/70 bg-white hover:bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] hover:-translate-y-px transition-all focus:border-tm-navy focus:ring-1 focus:ring-tm-navy focus:outline-none cursor-pointer">
                                    <option value="urgent">Urgent</option>
                                    <option value="high">High</option>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                </select>
                                <p v-if="form.errors.priority" class="text-xs text-red-500 ml-1">{{ form.errors.priority }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="status" class="text-[11px] font-bold text-tm-text-secondary uppercase tracking-wider ml-1">Status Pengerjaan</Label>
                                <select id="status" v-model="form.status" class="flex h-10 w-full rounded-lg border-[1.5px] border-black/70 bg-white hover:bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] hover:-translate-y-px transition-all focus:border-tm-navy focus:ring-1 focus:ring-tm-navy focus:outline-none cursor-pointer">
                                    <option value="open">Open (Baru)</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="revision">Revisi</option>
                                    <option value="completed">Selesai (Completed)</option>
                                </select>
                                <p v-if="form.errors.status" class="text-xs text-red-500 ml-1">{{ form.errors.status }}</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="release_date" class="text-[11px] font-bold text-tm-text-secondary uppercase tracking-wider ml-1">Tanggal Release (Target)</Label>
                                <Input id="release_date" type="date" v-model="form.release_date" class="h-10 border-[1.5px] border-black/70 rounded-lg bg-white hover:bg-slate-50 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] hover:-translate-y-px transition-all focus:border-tm-navy focus:ring-1 focus:ring-tm-navy cursor-pointer" />
                                <p v-if="form.errors.release_date" class="text-xs text-red-500 ml-1">{{ form.errors.release_date }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="assigned_to" class="text-[11px] font-bold text-tm-text-secondary uppercase tracking-wider ml-1">Assign Ke PIC (Person in Charge)</Label>
                                <select id="assigned_to" v-model="form.assigned_to" class="flex h-10 w-full rounded-lg border-[1.5px] border-black/70 bg-white hover:bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] hover:-translate-y-px transition-all focus:border-tm-navy focus:ring-1 focus:ring-tm-navy focus:outline-none cursor-pointer">
                                    <option value="">(Belum ditentukan)</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                </select>
                                <p v-if="form.errors.assigned_to" class="text-xs text-red-500 ml-1">{{ form.errors.assigned_to }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian Footer Form -->
            <div class="mt-6 flex flex-col gap-4">
                <!-- Duplicate Warning -->
                <div v-if="form.errors.duplicate" class="flex items-start gap-3 rounded-xl border-[1.5px] border-amber-400 bg-amber-50 p-4 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-amber-600 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-amber-800">{{ form.errors.duplicate }}</p>
                        <label class="mt-3 flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.force_duplicate" class="rounded border-amber-300 text-amber-600 focus:ring-amber-500" />
                            <span class="text-sm font-medium text-amber-700">Abaikan pengecekan duplikat (paksa simpan)</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-center gap-4 pt-2">
                    <Link href="/tasks">
                        <Button type="button" variant="outline" class="h-11 px-8 border-[1.5px] border-black/70 rounded-xl shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.1)] hover:-translate-y-0.5 transition-all font-medium text-slate-600">Batal</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing" class="h-11 px-8 bg-tm-navy hover:bg-tm-navy-medium text-white border-[1.5px] border-black shadow-[2px_2px_0_0_rgba(0,0,0,0.2)] rounded-xl hover:-translate-y-0.5 hover:shadow-[3px_3px_0_0_rgba(0,0,0,0.25)] transition-all flex items-center gap-2 font-medium tracking-wide">
                        <Save class="h-4 w-4" />
                        {{ form.processing ? 'Menyimpan Data...' : 'Simpan Tiket Task' }}
                    </Button>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal Simpan Template -->
    <Dialog :open="showTemplateModal" @update:open="(val) => showTemplateModal = val">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle class="text-lg font-semibold text-tm-navy">Simpan Sebagai Template</DialogTitle>
                <DialogDescription class="text-sm text-tm-text-secondary mt-1">
                    Beri nama untuk template ini agar mudah dikenali saat digunakan nanti.
                </DialogDescription>
            </DialogHeader>
            <div class="py-4">
                <Label for="template-name" class="text-[11px] font-bold text-tm-text-secondary uppercase tracking-wider ml-1">Nama Template</Label>
                <Input
                    id="template-name"
                    v-model="templateName"
                    placeholder="Contoh: Faskes A - Fitur Berbayar"
                    class="mt-2 h-10 border-[1.5px] border-black/70 rounded-lg bg-white text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] focus:border-tm-navy focus:ring-1 focus:ring-tm-navy"
                    @keyup.enter="submitTemplate"
                />
            </div>
            <DialogFooter class="flex gap-3 sm:justify-end">
                <Button variant="outline" @click="showTemplateModal = false" class="border-[1.5px] border-black/70 rounded-lg">Batal</Button>
                <Button @click="submitTemplate" :disabled="!templateName.trim()" class="bg-tm-navy hover:bg-tm-navy-medium text-white border-[1.5px] border-black shadow-[2px_2px_0_0_rgba(0,0,0,0.15)] rounded-lg">
                    Simpan Template
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

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
