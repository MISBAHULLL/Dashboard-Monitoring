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
    open: 'bg-slate-100 text-slate-600',
    in_progress: 'bg-blue-100 text-blue-700',
    revision: 'bg-amber-100 text-amber-700',
    completed: 'bg-emerald-100 text-emerald-700',
};
const typeClass: Record<string, string> = {
    UAT: 'bg-orange-100 text-orange-700',
    MOM: 'bg-purple-100 text-purple-700',
    BAST: 'bg-teal-100 text-teal-700',
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
    uploadForm.post(`/documents/${editingDoc.value!.id}`, {
        forceFormData: true,
        onSuccess: () => { isUploadOpen.value = false; },
    });
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
                <h1 class="text-2xl font-bold tracking-tight text-primary flex items-center gap-2">
                    <FileText class="h-6 w-6 text-sky-600" /> Dokumen Client
                </h1>
                <div class="mt-1 flex items-center gap-3 text-sm text-muted-foreground">
                    <span class="flex items-center gap-1">
                        <Building2 class="h-3.5 w-3.5" /> {{ document.client?.name ?? '-' }}
                    </span>
                    <span v-if="document.client?.city" class="flex items-center gap-1">
                        <MapPin class="h-3.5 w-3.5" /> {{ document.client.city }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="router.visit('/documents')" class="gap-1">
                    <ChevronLeft class="h-4 w-4" /> Kembali
                </Button>
                <Button @click="isNewDocOpen = true" class="gap-2 bg-slate-800 hover:bg-slate-700">
                    <Upload class="h-4 w-4" /> Upload Dokumen
                </Button>
            </div>
        </div>

        <!-- ── TABEL ── -->
        <div class="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
            <div v-if="clientDocuments.length === 0" class="py-16 text-center text-muted-foreground">
                <FileText class="mx-auto mb-3 h-10 w-10 opacity-30" />
                <p class="text-sm">Belum ada dokumen untuk client ini.</p>
            </div>

            <table v-else class="w-full text-sm">
                <thead class="bg-muted/50 border-b border-border">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wide">Nama Dokumen</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wide">Link Dokumen</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wide">Download</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wide">Tanggal Upload</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wide min-w-[240px]">Task Terkait</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-muted-foreground uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="doc in clientDocuments" :key="doc.id"
                        class="border-b border-border last:border-0 hover:bg-muted/20 transition-colors">

                        <!-- Nama Dokumen -->
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg"
                                    :class="doc.type === 'UAT' ? 'bg-orange-50' : doc.type === 'MOM' ? 'bg-purple-50' : 'bg-teal-50'">
                                    <FileText class="h-5 w-5"
                                        :class="doc.type === 'UAT' ? 'text-orange-500' : doc.type === 'MOM' ? 'text-purple-500' : 'text-teal-500'" />
                                </div>
                                <div>
                                    <p class="font-semibold text-foreground">{{ doc.title }}</p>
                                    <span class="inline-block mt-0.5 rounded px-1.5 py-0.5 text-xs font-bold"
                                        :class="typeClass[doc.type] ?? 'bg-slate-100 text-slate-600'">
                                        {{ doc.type }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        <!-- Link Dokumen -->
                        <td class="px-4 py-4">
                            <a v-if="doc.doc_url" :href="doc.doc_url" target="_blank"
                                class="inline-flex items-center gap-1 text-sky-600 hover:underline text-sm font-medium">
                                <ExternalLink class="h-3.5 w-3.5" /> Buka Link
                            </a>
                            <span v-else class="text-muted-foreground text-sm">-</span>
                        </td>

                        <!-- Download -->
                        <td class="px-4 py-4">
                            <a v-if="doc.file_path" :href="`/storage/${doc.file_path}`" target="_blank" download>
                                <span class="inline-flex items-center gap-1 text-emerald-600 hover:underline text-sm font-medium">
                                    <Download class="h-3.5 w-3.5" /> Download
                                </span>
                            </a>
                            <span v-else class="text-muted-foreground text-sm">-</span>
                        </td>

                        <!-- Tanggal Upload -->
                        <td class="px-4 py-4 text-sm text-muted-foreground whitespace-nowrap">
                            {{ formatDate(doc.created_at) }}
                        </td>

                        <!-- Task Terkait -->
                        <td class="px-4 py-4">
                            <div v-if="doc.tasks.length > 0" class="rounded-lg border border-border bg-muted/30 p-2 space-y-1.5">
                                <button @click="openTaskModal(doc)"
                                    class="flex items-center gap-1.5 text-xs font-semibold text-sky-600 hover:underline mb-1">
                                    <Link2 class="h-3.5 w-3.5" />
                                    {{ doc.tasks.length }} Task Terhubung
                                </button>
                                <div v-for="task in doc.tasks" :key="task.id"
                                    class="flex items-center justify-between rounded bg-background border border-border px-2 py-1 gap-2">
                                    <span class="text-xs text-foreground truncate">{{ task.title }}</span>
                                    <!-- Status dari pivot -->
                                    <span v-if="task.pivot?.status"
                                        :class="['shrink-0 rounded px-1.5 py-0.5 text-xs font-medium', taskStatusClass[task.pivot.status]]">
                                        {{ taskStatusLabel[task.pivot.status] }}
                                    </span>
                                </div>
                            </div>
                            <button v-else @click="openTaskModal(doc)"
                                class="flex w-full items-center justify-center gap-1.5 rounded-lg border border-dashed border-border px-3 py-2 text-xs text-muted-foreground hover:border-sky-400 hover:text-sky-600 transition-colors">
                                <Circle class="h-3.5 w-3.5" /> Hubungkan Task
                            </button>
                        </td>

                        <!-- Aksi -->
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-1">
                                <Button variant="ghost" size="sm" class="h-8 w-8 p-0 text-blue-600 hover:bg-blue-50"
                                    @click="openUploadModal(doc)" title="Edit Dokumen">
                                    <Edit class="h-4 w-4" />
                                </Button>
                                <Button variant="ghost" size="sm" class="h-8 w-8 p-0 text-red-500 hover:bg-red-50"
                                    @click="deleteDocument(doc)" title="Hapus Dokumen">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ── MODAL UPLOAD DOKUMEN BARU ── -->
        <Dialog :open="isNewDocOpen" @update:open="isNewDocOpen = $event">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Upload class="h-5 w-5" /> Upload Dokumen Baru
                    </DialogTitle>
                    <DialogDescription>
                        Untuk <span class="font-semibold text-foreground">{{ document.client?.name }}</span>
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitNewDoc" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label>Judul <span class="text-red-500">*</span></Label>
                        <Input v-model="newDocForm.title" placeholder="Contoh: UAT Modul Antrian" :class="{ 'border-red-500': newDocForm.errors.title }" />
                        <p v-if="newDocForm.errors.title" class="text-xs text-red-500">{{ newDocForm.errors.title }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Tipe <span class="text-red-500">*</span></Label>
                        <input v-model="newDocForm.type" type="text" required list="new-type-list"
                            placeholder="Pilih atau ketik tipe baru..."
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                            :class="{ 'border-red-500': newDocForm.errors.type }" />
                        <datalist id="new-type-list">
                            <option v-for="t in documentTypes" :key="t" :value="t" />
                        </datalist>
                        <p v-if="newDocForm.errors.type" class="text-xs text-red-500">{{ newDocForm.errors.type }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Link URL <span class="text-xs text-muted-foreground">(opsional)</span></Label>
                        <Input v-model="newDocForm.doc_url" placeholder="https://drive.google.com/..." :class="{ 'border-red-500': newDocForm.errors.doc_url }" />
                        <p v-if="newDocForm.errors.doc_url" class="text-xs text-red-500">{{ newDocForm.errors.doc_url }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Upload File <span class="text-xs text-muted-foreground">(opsional)</span></Label>
                        <input type="file" @change="handleNewFileChange"
                            class="block w-full text-sm text-muted-foreground file:mr-4 file:rounded-md file:border-0 file:bg-muted file:px-3 file:py-1.5 file:text-sm file:font-medium hover:file:bg-muted/80" />
                    </div>
                    <div class="space-y-2">
                        <Label>Catatan</Label>
                        <Input v-model="newDocForm.notes" placeholder="Contoh: Versi awal" />
                    </div>
                    <DialogFooter class="pt-2">
                        <Button type="button" variant="outline" @click="isNewDocOpen = false">Batal</Button>
                        <Button type="submit" :disabled="newDocForm.processing" class="bg-slate-800 hover:bg-slate-700">
                            {{ newDocForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- ── MODAL EDIT DOKUMEN ── -->
        <Dialog :open="isUploadOpen" @update:open="isUploadOpen = $event">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Edit class="h-5 w-5" /> Edit Dokumen
                    </DialogTitle>
                    <DialogDescription>Upload file baru akan menaikkan versi dokumen.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitUpload" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label>Judul <span class="text-red-500">*</span></Label>
                        <Input v-model="uploadForm.title" :class="{ 'border-red-500': uploadForm.errors.title }" />
                        <p v-if="uploadForm.errors.title" class="text-xs text-red-500">{{ uploadForm.errors.title }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Tipe <span class="text-red-500">*</span></Label>
                        <input v-model="uploadForm.type" type="text" required list="up-type-list"
                            placeholder="Pilih atau ketik tipe baru..."
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                            :class="{ 'border-red-500': uploadForm.errors.type }" />
                        <datalist id="up-type-list">
                            <option v-for="t in documentTypes" :key="t" :value="t" />
                        </datalist>
                        <p v-if="uploadForm.errors.type" class="text-xs text-red-500">{{ uploadForm.errors.type }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Link URL <span class="text-xs text-muted-foreground">(opsional)</span></Label>
                        <Input v-model="uploadForm.doc_url" placeholder="https://drive.google.com/..." :class="{ 'border-red-500': uploadForm.errors.doc_url }" />
                        <p v-if="uploadForm.errors.doc_url" class="text-xs text-red-500">{{ uploadForm.errors.doc_url }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>File <span class="text-xs text-muted-foreground">(kosongkan jika tidak ingin ganti)</span></Label>
                        <input type="file" @change="handleFileChange"
                            class="block w-full text-sm text-muted-foreground file:mr-4 file:rounded-md file:border-0 file:bg-muted file:px-3 file:py-1.5 file:text-sm file:font-medium hover:file:bg-muted/80" />
                        <p v-if="uploadForm.errors.file" class="text-xs text-red-500">{{ uploadForm.errors.file }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Catatan Versi</Label>
                        <Input v-model="uploadForm.notes" placeholder="Contoh: Revisi klausul 3" />
                    </div>
                    <DialogFooter class="pt-2">
                        <Button type="button" variant="outline" @click="isUploadOpen = false">Batal</Button>
                        <Button type="submit" :disabled="uploadForm.processing" class="bg-slate-800 hover:bg-slate-700">
                            {{ uploadForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- ── MODAL HUBUNGKAN TASK ── -->
        <Dialog :open="isTaskModalOpen" @update:open="isTaskModalOpen = $event">
            <DialogContent class="sm:max-w-[480px]">
                <DialogHeader>
                    <DialogTitle>Pilih Task Terkait</DialogTitle>
                    <DialogDescription>Centang task yang relevan dengan dokumen ini</DialogDescription>
                </DialogHeader>

                <div class="max-h-80 overflow-y-auto space-y-2 py-4">
                    <p v-if="clientTasks.length === 0" class="text-center text-sm text-muted-foreground py-6">
                        Tidak ada task aktif yang tersedia untuk dihubungkan.
                    </p>

                    <div v-for="task in clientTasks" :key="task.id"
                        :class="[
                            'rounded-lg border p-3 transition-colors',
                            isChecked(task.id) ? 'border-sky-400 bg-sky-50 dark:bg-sky-950/30' : 'border-border hover:bg-muted/40'
                        ]">
                        <!-- Baris atas: checkbox + judul -->
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" :checked="isChecked(task.id)" @change="toggleTask(task.id)"
                                class="mt-0.5 h-4 w-4 rounded border-gray-300 text-sky-600" />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-foreground">{{ task.title }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="rounded px-1.5 py-0.5 text-xs font-medium bg-muted text-muted-foreground">
                                        {{ task.category }}
                                    </span>
                                    <span v-if="isChecked(task.id)" class="text-xs text-sky-600 flex items-center gap-1">
                                        <CheckCircle2 class="h-3 w-3" /> Terpilih
                                    </span>
                                </div>
                            </div>
                        </label>

                        <!-- Baris bawah: pilih status (muncul hanya jika dicentang) -->
                        <div v-if="isChecked(task.id)" class="mt-2 ml-7 flex items-center gap-2">
                            <span class="text-xs text-muted-foreground font-medium">STATUS:</span>
                            <button type="button"
                                :class="[
                                    'rounded px-2.5 py-1 text-xs font-medium border transition-colors',
                                    taskSelections[task.id] === 'revision'
                                        ? 'bg-amber-500 text-white border-amber-500'
                                        : 'border-border text-muted-foreground hover:border-amber-400 hover:text-amber-600'
                                ]"
                                @click="setTaskStatus(task.id, 'revision')">
                                ↺ Revisi
                            </button>
                            <button type="button"
                                :class="[
                                    'rounded px-2.5 py-1 text-xs font-medium border transition-colors',
                                    taskSelections[task.id] === 'completed'
                                        ? 'bg-emerald-500 text-white border-emerald-500'
                                        : 'border-border text-muted-foreground hover:border-emerald-400 hover:text-emerald-600'
                                ]"
                                @click="setTaskStatus(task.id, 'completed')">
                                ✓ Selesai
                            </button>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="isTaskModalOpen = false">Batal</Button>
                    <Button @click="submitSync" :disabled="syncForm.processing" class="bg-slate-800 hover:bg-slate-700 gap-2">
                        <CheckCircle2 class="h-4 w-4" />
                        {{ syncForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

    </div>
</template>
