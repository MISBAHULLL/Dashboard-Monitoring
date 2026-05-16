<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Building2, MapPin, FileText, Upload, Edit, Trash2,
    Download, ExternalLink, Link2, CheckCircle2, Circle, ChevronLeft,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import {
    Dialog, DialogContent, DialogDescription,
    DialogFooter, DialogHeader, DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';

// ── Interfaces ───────────────────────────────────────────────
interface Task {
    id: number;
    title: string;
    category: string;
    status: 'open' | 'in_progress' | 'revision' | 'completed';
    pivot?: { status: 'revision' | 'completed' | null };
}

interface DocumentItem {
    id: number;
    title: string;
    type: string;
    doc_url: string | null;
    file_path: string | null;
    file_name: string | null;
    file_size: number | null;
    current_version: number;
    created_at: string;
    tasks: Task[];
    client: { id: number; name: string; city: string | null } | null;
    creator: { id: number; name: string } | null;
}

const props = defineProps<{
    document: DocumentItem;
    clientDocuments: DocumentItem[];
    clientTasks: Task[];
    documentTypes: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Dokumen', href: '/documents' },
            { title: 'Detail', href: '#' },
        ],
    },
});

// ── Helpers ──────────────────────────────────────────────────
function formatDate(iso: string): string {
    return new Date(iso).toLocaleString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

const taskStatusLabel: Record<string, string> = {
    open: 'Open', in_progress: 'In Progress', revision: 'Revisi', completed: 'Selesai',
};
const taskStatusClass: Record<string, string> = {
    open: 'bg-tm-navy-pale text-tm-navy border border-tm-navy/20',
    in_progress: 'bg-tm-navy-pale text-tm-navy-medium border border-tm-navy/20',
    revision: 'bg-tm-warning-pale text-[#92610A] border border-[#F59E0B]/20',
    completed: 'bg-tm-green-pale text-[#1E8A54] border border-tm-green/20',
};
const typeClass: Record<string, string> = {
    UAT: 'bg-tm-warning-pale text-[#92610A] border border-[#F59E0B]/20',
    MOM: 'bg-[#F3E8FF] text-[#6B21A8] border border-[#A855F7]/20',
    BAST: 'bg-tm-green-pale text-[#1E8A54] border border-tm-green/20',
};

// ── Modal Upload Dokumen Baru ────────────────────────────────
const isNewDocOpen = ref(false);
const newDocForm = useForm({
    client_id: String(props.document.client?.id ?? ''),
    title: '', type: '', doc_url: '', file: null as File | null, notes: '',
});
function handleNewFileChange(e: Event) {
    const t = e.target as HTMLInputElement;
    if (t.files?.[0]) newDocForm.file = t.files[0];
}
function submitNewDoc() {
    newDocForm.post('/documents', {
        forceFormData: true,
        onSuccess: () => { isNewDocOpen.value = false; router.reload(); },
    });
}

// ── Modal Edit Dokumen ───────────────────────────────────────
const isUploadOpen = ref(false);
const editingDoc   = ref<DocumentItem | null>(null);
const uploadForm   = useForm({
    client_id: '', title: '', type: '', doc_url: '',
    file: null as File | null, notes: '',
});
function openUploadModal(doc: DocumentItem) {
    editingDoc.value       = doc;
    uploadForm.client_id   = String(doc.client?.id ?? '');
    uploadForm.title       = doc.title;
    uploadForm.type        = doc.type;
    uploadForm.doc_url     = doc.doc_url ?? '';
    uploadForm.file        = null;
    uploadForm.notes       = '';
    uploadForm.clearErrors();
    isUploadOpen.value     = true;
}
function handleFileChange(e: Event) {
    const t = e.target as HTMLInputElement;
    if (t.files?.[0]) uploadForm.file = t.files[0];
}
function submitUpload() {
    if (uploadForm.file) {
        uploadForm
            .transform((data) => ({ ...data, _method: 'put' }))
            .post(`/documents/${editingDoc.value!.id}`, {
                forceFormData: true,
                onSuccess: () => { isUploadOpen.value = false; },
            });
    } else {
        uploadForm.put(`/documents/${editingDoc.value!.id}`, {
            onSuccess: () => { isUploadOpen.value = false; },
        });
    }
}

// ── Modal Hubungkan Task ─────────────────────────────────────
const isTaskModalOpen  = ref(false);
const activeDoc        = ref<DocumentItem | null>(null);

// State per task: { taskId: 'revision' | 'completed' | null }
// null = tidak dicentang
const taskSelections = ref<Record<number, 'revision' | 'completed' | null>>({});

const syncForm = useForm({ tasks: [] as { id: number; status: string | null }[] });

function openTaskModal(doc: DocumentItem) {
    activeDoc.value = doc;

    // Inisialisasi dari pivot yang sudah ada
    const init: Record<number, 'revision' | 'completed' | null> = {};
    doc.tasks.forEach(t => {
        init[t.id] = t.pivot?.status ?? null;
    });
    taskSelections.value = init;
    isTaskModalOpen.value = true;
}

function isChecked(taskId: number): boolean {
    return taskId in taskSelections.value;
}

function toggleTask(taskId: number) {
    if (isChecked(taskId)) {
        // Uncheck — hapus dari selections
        const s = { ...taskSelections.value };
        delete s[taskId];
        taskSelections.value = s;
    } else {
        // Check — default status null (belum dipilih)
        taskSelections.value = { ...taskSelections.value, [taskId]: null };
    }
}

function setTaskStatus(taskId: number, status: 'revision' | 'completed') {
    taskSelections.value = { ...taskSelections.value, [taskId]: status };
}

function submitSync() {
    syncForm.tasks = Object.entries(taskSelections.value).map(([id, status]) => ({
        id: Number(id),
        status,
    }));
    syncForm.post(`/documents/${activeDoc.value!.id}/sync-tasks`, {
        onSuccess: () => { isTaskModalOpen.value = false; },
    });
}

// ── Hapus Dokumen ────────────────────────────────────────────
function deleteDocument(doc: DocumentItem) {
    if (!confirm(`Hapus dokumen "${doc.title}"?`)) return;
    router.delete(`/documents/${doc.id}`, {
        onSuccess: () => {
            if (doc.id === props.document.id) router.visit('/documents');
            else router.reload();
        },
    });
}
</script>

<template>
    <Head :title="`Dokumen — ${document.client?.name}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8">

        <!-- ── HEADER ── -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-tm-navy flex items-center gap-3 dark:text-foreground">
                    <div class="flex h-10 w-10 items-center justify-center rounded-[12px] border-2 border-black bg-tm-navy-pale shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] dark:bg-tm-navy dark:border-border">
                        <FileText class="h-5 w-5 text-tm-navy dark:text-white" />
                    </div>
                    Dokumen Client
                </h1>
                <div class="mt-1.5 ml-[52px] flex items-center gap-3 text-sm text-tm-text-secondary dark:text-muted-foreground">
                    <span class="flex items-center gap-1.5 font-medium">
                        <Building2 class="h-3.5 w-3.5 text-tm-navy dark:text-muted-foreground" /> {{ document.client?.name ?? '-' }}
                    </span>
                    <span v-if="document.client?.city" class="flex items-center gap-1.5">
                        <MapPin class="h-3.5 w-3.5 text-tm-text-muted" /> {{ document.client.city }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <button
                    @click="router.visit('/documents')"
                    class="inline-flex items-center gap-1.5 rounded-[10px] border-2 border-black bg-white px-3.5 py-2 text-sm font-bold text-tm-navy shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] dark:bg-card dark:border-border dark:text-foreground dark:shadow-none"
                >
                    <ChevronLeft class="h-4 w-4" /> Kembali
                </button>
                <button
                    @click="isNewDocOpen = true"
                    class="inline-flex items-center gap-2 rounded-[10px] border-2 border-black bg-tm-green px-4 py-2 text-sm font-bold text-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:shadow-none"
                >
                    <Upload class="h-4 w-4" /> Upload Dokumen
                </button>
            </div>
        </div>

        <!-- ── TABEL ── -->
        <div class="rounded-[14px] border-2 border-black bg-white shadow-[2px_3px_0px_0px_rgba(0,0,0,0.8)] overflow-hidden dark:bg-card dark:border-border dark:shadow-none">
            <div v-if="clientDocuments.length === 0" class="py-20 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-[14px] border-2 border-black bg-tm-navy-pale shadow-[2px_3px_0px_0px_rgba(0,0,0,0.8)] dark:bg-card dark:border-border">
                    <FileText class="h-7 w-7 text-tm-navy dark:text-muted-foreground" />
                </div>
                <p class="text-sm font-semibold text-tm-text-secondary dark:text-muted-foreground">Belum ada dokumen untuk client ini.</p>
                <p class="text-xs text-tm-text-muted mt-1 dark:text-muted-foreground">Klik "Upload Dokumen" untuk memulai.</p>
            </div>

            <table v-else class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-black bg-tm-navy-pale dark:bg-secondary dark:border-border">
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Nama Dokumen</th>
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Link Dokumen</th>
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Download</th>
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Tanggal Upload</th>
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground min-w-[240px]">Task Terkait</th>
                        <th class="px-4 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="doc in clientDocuments" :key="doc.id"
                        class="group border-b border-tm-border last:border-0 transition-colors hover:bg-tm-navy-pale/40 dark:border-border dark:hover:bg-secondary/50">

                        <!-- Nama Dokumen -->
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[10px] border-2"
                                    :class="doc.type === 'UAT' ? 'bg-tm-warning-pale border-[#F59E0B]/30' : doc.type === 'MOM' ? 'bg-[#F3E8FF] border-[#A855F7]/30' : 'bg-tm-green-pale border-tm-green/30'">
                                    <FileText class="h-4.5 w-4.5"
                                        :class="doc.type === 'UAT' ? 'text-[#92610A]' : doc.type === 'MOM' ? 'text-[#6B21A8]' : 'text-[#1E8A54]'" />
                                </div>
                                <div>
                                    <p class="font-semibold text-tm-navy dark:text-foreground">{{ doc.title }}</p>
                                    <span class="inline-flex items-center mt-0.5 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                                        :class="typeClass[doc.type] ?? 'bg-tm-navy-pale text-tm-navy border border-tm-navy/20'">
                                        {{ doc.type }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        <!-- Link Dokumen -->
                        <td class="px-4 py-4">
                            <a v-if="doc.doc_url" :href="doc.doc_url" target="_blank"
                                class="inline-flex items-center gap-1.5 rounded-[8px] border border-tm-navy/20 bg-tm-navy-pale px-2.5 py-1 text-xs font-semibold text-tm-navy transition-all hover:bg-tm-navy hover:text-white dark:bg-secondary dark:text-foreground dark:border-border dark:hover:bg-tm-navy dark:hover:text-white">
                                <ExternalLink class="h-3 w-3" /> Buka Link
                            </a>
                            <span v-else class="text-tm-text-muted text-sm dark:text-muted-foreground">-</span>
                        </td>

                        <!-- Download -->
                        <td class="px-4 py-4">
                            <a v-if="doc.file_path" :href="`/storage/${doc.file_path}`" target="_blank" download
                                class="inline-flex items-center gap-1.5 rounded-[8px] border border-tm-green/20 bg-tm-green-pale px-2.5 py-1 text-xs font-semibold text-[#1E8A54] transition-all hover:bg-tm-green hover:text-white dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-500/20 dark:hover:bg-tm-green dark:hover:text-white">
                                <Download class="h-3 w-3" /> Download
                            </a>
                            <span v-else class="text-tm-text-muted text-sm dark:text-muted-foreground">-</span>
                        </td>

                        <!-- Tanggal Upload -->
                        <td class="px-4 py-4 text-xs font-medium text-tm-text-muted whitespace-nowrap dark:text-muted-foreground">
                            {{ formatDate(doc.created_at) }}
                        </td>

                        <!-- Task Terkait -->
                        <td class="px-4 py-4">
                            <div v-if="doc.tasks.length > 0" class="rounded-[10px] border-2 border-tm-border bg-tm-navy-pale/30 p-2.5 space-y-1.5 dark:bg-secondary/30 dark:border-border">
                                <button @click="openTaskModal(doc)"
                                    class="flex items-center gap-1.5 text-xs font-bold text-tm-navy hover:text-tm-green transition-colors mb-1 dark:text-foreground dark:hover:text-tm-green">
                                    <Link2 class="h-3.5 w-3.5" />
                                    {{ doc.tasks.length }} Task Terhubung
                                </button>
                                <div v-for="task in doc.tasks" :key="task.id"
                                    class="flex items-center justify-between rounded-[8px] bg-white border border-tm-border px-2.5 py-1.5 gap-2 dark:bg-card dark:border-border">
                                    <span class="text-xs font-medium text-tm-navy truncate dark:text-foreground">{{ task.title }}</span>
                                    <span v-if="task.pivot?.status"
                                        :class="['shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold', taskStatusClass[task.pivot.status]]">
                                        {{ taskStatusLabel[task.pivot.status] }}
                                    </span>
                                </div>
                            </div>
                            <button v-else @click="openTaskModal(doc)"
                                class="flex w-full items-center justify-center gap-1.5 rounded-[10px] border-2 border-dashed border-tm-border px-3 py-2.5 text-xs font-semibold text-tm-text-muted hover:border-tm-green hover:text-tm-green hover:bg-tm-green-pale/30 transition-all dark:border-border dark:text-muted-foreground dark:hover:border-tm-green dark:hover:text-tm-green">
                                <Circle class="h-3.5 w-3.5" /> Hubungkan Task
                            </button>
                        </td>

                        <!-- Aksi -->
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-1">
                                <button
                                    @click="openUploadModal(doc)"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-transparent text-tm-text-muted transition-all hover:border-tm-navy/20 hover:bg-tm-navy-pale hover:text-tm-navy dark:hover:bg-secondary dark:hover:text-foreground"
                                    title="Edit Dokumen"
                                >
                                    <Edit class="h-4 w-4" />
                                </button>
                                <button
                                    @click="deleteDocument(doc)"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-transparent text-tm-text-muted transition-all hover:border-tm-danger/20 hover:bg-tm-danger-pale hover:text-tm-danger dark:hover:bg-red-950/30 dark:hover:text-red-400"
                                    title="Hapus Dokumen"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ── MODAL UPLOAD DOKUMEN BARU ── -->
        <Dialog :open="isNewDocOpen" @update:open="isNewDocOpen = $event">
            <DialogContent class="sm:max-w-[500px] rounded-[18px] border-2 border-black shadow-[4px_6px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:shadow-none">
                <DialogHeader class="border-b-2 border-black bg-tm-navy-pale -mx-6 -mt-6 px-6 py-4 rounded-t-[16px] dark:bg-secondary dark:border-border">
                    <DialogTitle class="flex items-center gap-2 text-tm-navy font-extrabold dark:text-foreground">
                        <Upload class="h-5 w-5" /> Upload Dokumen Baru
                    </DialogTitle>
                    <DialogDescription class="text-tm-text-secondary dark:text-muted-foreground">
                        Untuk <span class="font-semibold text-tm-navy dark:text-foreground">{{ document.client?.name }}</span>
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitNewDoc" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label class="text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-foreground">Judul <span class="text-tm-danger">*</span></Label>
                        <Input v-model="newDocForm.title" placeholder="Contoh: UAT Modul Antrian" class="rounded-[10px] border-2 border-tm-border focus:border-tm-green focus:ring-2 focus:ring-tm-green/20 dark:border-border" :class="{ 'border-tm-danger': newDocForm.errors.title }" />
                        <p v-if="newDocForm.errors.title" class="text-xs font-medium text-tm-danger">{{ newDocForm.errors.title }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label class="text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-foreground">Tipe <span class="text-tm-danger">*</span></Label>
                        <input v-model="newDocForm.type" type="text" required list="new-type-list"
                            placeholder="Pilih atau ketik tipe baru..."
                            class="w-full rounded-[10px] border-2 border-tm-border bg-white px-3 py-2.5 text-sm font-medium text-tm-navy transition-all focus:border-tm-green focus:outline-none focus:ring-2 focus:ring-tm-green/20 dark:border-border dark:bg-background dark:text-foreground"
                            :class="{ 'border-tm-danger': newDocForm.errors.type }" />
                        <datalist id="new-type-list">
                            <option v-for="t in documentTypes" :key="t" :value="t" />
                        </datalist>
                        <p v-if="newDocForm.errors.type" class="text-xs font-medium text-tm-danger">{{ newDocForm.errors.type }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label class="text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-foreground">Link URL <span class="text-xs font-normal text-tm-text-muted">(opsional)</span></Label>
                        <Input v-model="newDocForm.doc_url" placeholder="https://drive.google.com/..." class="rounded-[10px] border-2 border-tm-border focus:border-tm-green focus:ring-2 focus:ring-tm-green/20 dark:border-border" :class="{ 'border-tm-danger': newDocForm.errors.doc_url }" />
                        <p v-if="newDocForm.errors.doc_url" class="text-xs font-medium text-tm-danger">{{ newDocForm.errors.doc_url }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label class="text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-foreground">Upload File <span class="text-xs font-normal text-tm-text-muted">(opsional)</span></Label>
                        <input type="file" @change="handleNewFileChange"
                            class="block w-full rounded-[10px] border-2 border-dashed border-tm-border bg-tm-navy-pale/30 px-3 py-3 text-sm text-tm-text-secondary transition-all file:mr-3 file:rounded-[8px] file:border-2 file:border-black file:bg-white file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-tm-navy file:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] hover:border-tm-green/50 dark:border-border dark:bg-secondary/30 dark:file:bg-card dark:file:text-foreground dark:file:border-border" />
                    </div>
                    <div class="space-y-2">
                        <Label class="text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-foreground">Catatan</Label>
                        <Input v-model="newDocForm.notes" placeholder="Contoh: Versi awal" class="rounded-[10px] border-2 border-tm-border focus:border-tm-green focus:ring-2 focus:ring-tm-green/20 dark:border-border" />
                    </div>
                    <DialogFooter class="pt-3 gap-3">
                        <button type="button" @click="isNewDocOpen = false"
                            class="flex-1 rounded-[10px] border-2 border-black bg-white px-4 py-2.5 text-sm font-bold text-tm-navy shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] dark:bg-card dark:border-border dark:text-foreground dark:shadow-none">
                            Batal
                        </button>
                        <button type="submit" :disabled="newDocForm.processing"
                            class="flex-1 rounded-[10px] border-2 border-black bg-tm-green px-4 py-2.5 text-sm font-bold text-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] disabled:opacity-50 disabled:cursor-not-allowed dark:border-border dark:shadow-none">
                            {{ newDocForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- ── MODAL EDIT DOKUMEN ── -->
        <Dialog :open="isUploadOpen" @update:open="isUploadOpen = $event">
            <DialogContent class="sm:max-w-[500px] rounded-[18px] border-2 border-black shadow-[4px_6px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:shadow-none">
                <DialogHeader class="border-b-2 border-black bg-tm-navy-pale -mx-6 -mt-6 px-6 py-4 rounded-t-[16px] dark:bg-secondary dark:border-border">
                    <DialogTitle class="flex items-center gap-2 text-tm-navy font-extrabold dark:text-foreground">
                        <Edit class="h-5 w-5" /> Edit Dokumen
                    </DialogTitle>
                    <DialogDescription class="text-tm-text-secondary dark:text-muted-foreground">Upload file baru akan menaikkan versi dokumen.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitUpload" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label class="text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-foreground">Judul <span class="text-tm-danger">*</span></Label>
                        <Input v-model="uploadForm.title" class="rounded-[10px] border-2 border-tm-border focus:border-tm-green focus:ring-2 focus:ring-tm-green/20 dark:border-border" :class="{ 'border-tm-danger': uploadForm.errors.title }" />
                        <p v-if="uploadForm.errors.title" class="text-xs font-medium text-tm-danger">{{ uploadForm.errors.title }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label class="text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-foreground">Tipe <span class="text-tm-danger">*</span></Label>
                        <input v-model="uploadForm.type" type="text" required list="up-type-list"
                            placeholder="Pilih atau ketik tipe baru..."
                            class="w-full rounded-[10px] border-2 border-tm-border bg-white px-3 py-2.5 text-sm font-medium text-tm-navy transition-all focus:border-tm-green focus:outline-none focus:ring-2 focus:ring-tm-green/20 dark:border-border dark:bg-background dark:text-foreground"
                            :class="{ 'border-tm-danger': uploadForm.errors.type }" />
                        <datalist id="up-type-list">
                            <option v-for="t in documentTypes" :key="t" :value="t" />
                        </datalist>
                        <p v-if="uploadForm.errors.type" class="text-xs font-medium text-tm-danger">{{ uploadForm.errors.type }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label class="text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-foreground">Link URL <span class="text-xs font-normal text-tm-text-muted">(opsional)</span></Label>
                        <Input v-model="uploadForm.doc_url" placeholder="https://drive.google.com/..." class="rounded-[10px] border-2 border-tm-border focus:border-tm-green focus:ring-2 focus:ring-tm-green/20 dark:border-border" :class="{ 'border-tm-danger': uploadForm.errors.doc_url }" />
                        <p v-if="uploadForm.errors.doc_url" class="text-xs font-medium text-tm-danger">{{ uploadForm.errors.doc_url }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label class="text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-foreground">File <span class="text-xs font-normal text-tm-text-muted">(kosongkan jika tidak ingin ganti)</span></Label>
                        <input type="file" @change="handleFileChange"
                            class="block w-full rounded-[10px] border-2 border-dashed border-tm-border bg-tm-navy-pale/30 px-3 py-3 text-sm text-tm-text-secondary transition-all file:mr-3 file:rounded-[8px] file:border-2 file:border-black file:bg-white file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-tm-navy file:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] hover:border-tm-green/50 dark:border-border dark:bg-secondary/30 dark:file:bg-card dark:file:text-foreground dark:file:border-border" />
                        <p v-if="uploadForm.errors.file" class="text-xs font-medium text-tm-danger">{{ uploadForm.errors.file }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label class="text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-foreground">Catatan Versi</Label>
                        <Input v-model="uploadForm.notes" placeholder="Contoh: Revisi klausul 3" class="rounded-[10px] border-2 border-tm-border focus:border-tm-green focus:ring-2 focus:ring-tm-green/20 dark:border-border" />
                    </div>
                    <DialogFooter class="pt-3 gap-3">
                        <button type="button" @click="isUploadOpen = false"
                            class="flex-1 rounded-[10px] border-2 border-black bg-white px-4 py-2.5 text-sm font-bold text-tm-navy shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] dark:bg-card dark:border-border dark:text-foreground dark:shadow-none">
                            Batal
                        </button>
                        <button type="submit" :disabled="uploadForm.processing"
                            class="flex-1 rounded-[10px] border-2 border-black bg-tm-green px-4 py-2.5 text-sm font-bold text-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] disabled:opacity-50 disabled:cursor-not-allowed dark:border-border dark:shadow-none">
                            {{ uploadForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- ── MODAL HUBUNGKAN TASK ── -->
        <Dialog :open="isTaskModalOpen" @update:open="isTaskModalOpen = $event">
            <DialogContent class="sm:max-w-[480px] rounded-[18px] border-2 border-black shadow-[4px_6px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:shadow-none">
                <DialogHeader class="border-b-2 border-black bg-tm-navy-pale -mx-6 -mt-6 px-6 py-4 rounded-t-[16px] dark:bg-secondary dark:border-border">
                    <DialogTitle class="text-tm-navy font-extrabold dark:text-foreground">Pilih Task Terkait</DialogTitle>
                    <DialogDescription class="text-tm-text-secondary dark:text-muted-foreground">Centang task yang relevan dengan dokumen ini</DialogDescription>
                </DialogHeader>

                <div class="max-h-80 overflow-y-auto space-y-2 py-4">
                    <p v-if="clientTasks.length === 0" class="text-center text-sm text-tm-text-muted py-6 dark:text-muted-foreground">
                        Tidak ada task aktif yang tersedia untuk dihubungkan.
                    </p>

                    <div v-for="task in clientTasks" :key="task.id"
                        :class="[
                            'rounded-[10px] border-2 p-3 transition-all',
                            isChecked(task.id) ? 'border-tm-green bg-tm-green-pale/40 shadow-sm dark:bg-emerald-950/20 dark:border-tm-green/50' : 'border-tm-border hover:bg-tm-navy-pale/30 dark:border-border dark:hover:bg-secondary/50'
                        ]">
                        <!-- Baris atas: checkbox + judul -->
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" :checked="isChecked(task.id)" @change="toggleTask(task.id)"
                                class="mt-0.5 h-4 w-4 rounded border-2 border-tm-border text-tm-green focus:ring-tm-green/30 dark:border-border" />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-tm-navy dark:text-foreground">{{ task.title }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold bg-tm-navy-pale text-tm-navy dark:bg-secondary dark:text-foreground">
                                        {{ task.category }}
                                    </span>
                                    <span v-if="isChecked(task.id)" class="text-[10px] font-bold text-tm-green flex items-center gap-1">
                                        <CheckCircle2 class="h-3 w-3" /> Terpilih
                                    </span>
                                </div>
                            </div>
                        </label>

                        <!-- Baris bawah: pilih status (muncul hanya jika dicentang) -->
                        <div v-if="isChecked(task.id)" class="mt-2.5 ml-7 flex items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-wide text-tm-text-muted dark:text-muted-foreground">Status:</span>
                            <button type="button"
                                :class="[
                                    'rounded-full px-2.5 py-1 text-[10px] font-bold border-2 transition-all',
                                    taskSelections[task.id] === 'revision'
                                        ? 'bg-[#F59E0B] text-white border-[#D97706] shadow-[1px_1px_0px_0px_rgba(0,0,0,0.5)]'
                                        : 'border-tm-border text-tm-text-muted hover:border-[#F59E0B] hover:text-[#92610A] hover:bg-tm-warning-pale dark:border-border dark:text-muted-foreground'
                                ]"
                                @click="setTaskStatus(task.id, 'revision')">
                                ↺ Revisi
                            </button>
                            <button type="button"
                                :class="[
                                    'rounded-full px-2.5 py-1 text-[10px] font-bold border-2 transition-all',
                                    taskSelections[task.id] === 'completed'
                                        ? 'bg-tm-green text-white border-[#1E8A54] shadow-[1px_1px_0px_0px_rgba(0,0,0,0.5)]'
                                        : 'border-tm-border text-tm-text-muted hover:border-tm-green hover:text-[#1E8A54] hover:bg-tm-green-pale dark:border-border dark:text-muted-foreground'
                                ]"
                                @click="setTaskStatus(task.id, 'completed')">
                                ✓ Selesai
                            </button>
                        </div>
                    </div>
                </div>

                <DialogFooter class="pt-3 gap-3">
                    <button type="button" @click="isTaskModalOpen = false"
                        class="flex-1 rounded-[10px] border-2 border-black bg-white px-4 py-2.5 text-sm font-bold text-tm-navy shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] dark:bg-card dark:border-border dark:text-foreground dark:shadow-none">
                        Batal
                    </button>
                    <button @click="submitSync" :disabled="syncForm.processing"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-[10px] border-2 border-black bg-tm-green px-4 py-2.5 text-sm font-bold text-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] disabled:opacity-50 disabled:cursor-not-allowed dark:border-border dark:shadow-none">
                        <CheckCircle2 class="h-4 w-4" />
                        {{ syncForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

    </div>
</template>
