<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { FileText, Plus, Download, Trash2, Eye, Upload, X, File, FolderOpen, RotateCcw } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { computed, ref, watch } from 'vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Dokumen', href: '#' },
        ],
    },
});

interface Document {
    id: number;
    title: string;
    type: string;
    file_path: string | null;
    file_name: string | null;
    file_size: number | null;
    current_version: number;
    client: { id: number; name: string } | null;
    creator: { id: number; name: string } | null;
    created_at: string;
    deleted_at?: string | null;
}

interface PaginatedDocuments {
    data: Document[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

const props = defineProps<{
    documents: PaginatedDocuments;
    clients?: Array<{ id: number; name: string }>;
    documentTypes: string[];
    activeTrashed?: boolean;
}>();

const showModal = ref(false);
const editingDocument = ref<Document | null>(null);
const selectedDocuments = ref<number[]>([]);

const selectAllDocuments = computed({
    get: () => props.documents.data.length > 0 && selectedDocuments.value.length === props.documents.data.length,
    set: (value) => {
        selectedDocuments.value = value ? props.documents.data.map((doc) => doc.id) : [];
    },
});

watch(() => props.documents.data, () => {
    selectedDocuments.value = [];
});

const form = useForm({
    client_id: '',
    title: '',
    type: '',
    file: null as File | null,
    notes: '',
});

function openCreate() {
    editingDocument.value = null;
    form.reset();
    showModal.value = true;
}

function openEdit(doc: Document) {
    editingDocument.value = doc;
    form.client_id = String(doc.client?.id ?? '');
    form.title = doc.title;
    form.type = doc.type;
    form.file = null;
    form.notes = '';
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    form.reset();
    editingDocument.value = null;
}

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.file = target.files[0];
    }
}

function submit() {
    if (editingDocument.value) {
        if (form.file) {
            form
                .transform((data) => ({ ...data, _method: 'put' }))
                .post(`/documents/${editingDocument.value.id}`, {
                    forceFormData: true,
                    onSuccess: () => closeModal(),
                });
        } else {
            form.put(`/documents/${editingDocument.value.id}`, {
                onSuccess: () => closeModal(),
            });
        }
    } else {
        form.post('/documents', {
            forceFormData: !!form.file,
            onSuccess: () => closeModal(),
        });
    }
}

function deleteDocument(id: number, title: string) {
    if (confirm(`Hapus dokumen "${title}"?`)) {
        router.delete(`/documents/${id}`);
    }
}

function restoreDocument(id: number, title: string) {
    if (confirm(`Pulihkan dokumen "${title}"?`)) {
        router.patch(`/documents/${id}/restore`);
    }
}

function bulkRestoreDocuments(restoreAll = false) {
    const total = restoreAll ? props.documents.total : selectedDocuments.value.length;
    if (total === 0) return;

    if (confirm(`Pulihkan ${restoreAll ? 'semua' : total} dokumen terhapus?`)) {
        router.patch('/documents/bulk-restore', {
            ids: restoreAll ? [] : selectedDocuments.value,
            restore_all: restoreAll,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedDocuments.value = [];
            },
        });
    }
}

function formatBytes(bytes: number | null): string {
    if (!bytes) return '-';
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return `${(bytes / Math.pow(1024, i)).toFixed(2)} ${sizes[i]}`;
}

function formatDate(iso: string): string {
    return new Date(iso).toLocaleString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
    });
}

function getTypeColor(type: string): string {
    const colors: Record<string, string> = {
        UAT: 'bg-tm-navy-pale text-tm-navy border-tm-navy/20',
        SOP: 'bg-tm-green-pale text-[#1E8A54] border-tm-green/20',
        Regulasi: 'bg-tm-warning-pale text-[#92610A] border-[#F59E0B]/20',
        Kontrak: 'bg-[#F3E8FF] text-[#6B21A8] border-[#A855F7]/20',
    };
    return colors[type] || 'bg-tm-navy-pale text-tm-navy border-tm-navy/20';
}
</script>

<template>
    <Head title="Dokumen" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-tm-navy flex items-center gap-3 dark:text-foreground">
                    <div class="flex h-10 w-10 items-center justify-center rounded-[12px] border-2 border-black bg-tm-navy-pale shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] dark:bg-tm-navy dark:border-border">
                        <FileText class="h-5 w-5 text-tm-navy dark:text-white" />
                    </div>
                    Dokumen
                </h1>
                <p class="text-sm text-tm-text-secondary mt-1.5 ml-[52px] dark:text-muted-foreground">
                    Kelola dokumen dan riwayat versinya.
                    <span class="font-semibold text-tm-navy dark:text-foreground">{{ documents.total }}</span> dokumen tersimpan.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button
                    @click="router.visit(activeTrashed ? '/documents' : '/documents?trashed=only')"
                    class="inline-flex items-center gap-2 rounded-[10px] border-2 border-black bg-white px-4 py-2.5 text-sm font-bold text-tm-navy shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:bg-tm-navy-pale hover:shadow-[3px_4px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:bg-card dark:text-foreground dark:shadow-none"
                >
                    <RotateCcw class="h-4 w-4" />
                    {{ activeTrashed ? 'Lihat Aktif' : 'Lihat Terhapus' }}
                </button>
                <button
                    v-if="activeTrashed && documents.total > 0"
                    @click="bulkRestoreDocuments(true)"
                    class="inline-flex items-center gap-2 rounded-[10px] border-2 border-black bg-tm-green px-4 py-2.5 text-sm font-bold text-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_4px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:shadow-none"
                >
                    <RotateCcw class="h-4 w-4" />
                    Restore Semua
                </button>
                <button
                    v-if="!activeTrashed"
                    @click="openCreate"
                    class="inline-flex items-center gap-2 rounded-[10px] border-2 border-black bg-tm-green px-4 py-2.5 text-sm font-bold text-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_4px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:shadow-none"
                >
                    <Plus class="h-4 w-4" />
                    Tambah Dokumen
                </button>
            </div>
        </div>

        <div v-if="activeTrashed && selectedDocuments.length > 0" class="flex flex-wrap items-center justify-between gap-3 rounded-[14px] border-2 border-black bg-tm-navy-pale p-3 shadow-[2px_3px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:bg-secondary dark:shadow-none">
            <span class="text-sm font-bold text-tm-navy dark:text-foreground">{{ selectedDocuments.length }} dokumen dipilih</span>
            <Button @click="bulkRestoreDocuments(false)" size="sm" class="bg-tm-green hover:bg-tm-green-dark">
                <RotateCcw class="mr-2 h-4 w-4" />
                Pulihkan Terpilih
            </Button>
        </div>

        <!-- Table Card -->
        <div class="rounded-[14px] border-2 border-black bg-white shadow-[2px_3px_0px_0px_rgba(0,0,0,0.8)] overflow-hidden dark:bg-card dark:border-border dark:shadow-none">
            <!-- Empty State -->
            <div v-if="documents.data.length === 0" class="py-20 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-[14px] border-2 border-black bg-tm-navy-pale shadow-[2px_3px_0px_0px_rgba(0,0,0,0.8)] dark:bg-card dark:border-border">
                    <FolderOpen class="h-7 w-7 text-tm-navy dark:text-muted-foreground" />
                </div>
                <p class="text-sm font-semibold text-tm-text-secondary dark:text-muted-foreground">{{ activeTrashed ? 'Tidak ada dokumen terhapus.' : 'Belum ada dokumen.' }}</p>
                <p v-if="!activeTrashed" class="text-xs text-tm-text-muted mt-1 dark:text-muted-foreground">Klik "Tambah Dokumen" untuk memulai.</p>
            </div>

            <!-- Table -->
            <table v-else class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-black bg-tm-navy-pale dark:bg-secondary dark:border-border">
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">
                            <span class="flex items-center gap-2.5">
                                <input v-if="activeTrashed" type="checkbox" v-model="selectAllDocuments" class="h-4 w-4 cursor-pointer rounded border-slate-300 text-tm-navy focus:ring-tm-navy" />
                                Judul
                            </span>
                        </th>
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Tipe</th>
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Faskes</th>
                        <th class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Versi</th>
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Ukuran</th>
                        <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Tanggal</th>
                        <th class="px-4 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="doc in documents.data"
                        :key="doc.id"
                        class="group border-b border-tm-border transition-colors hover:bg-tm-navy-pale/40 dark:border-border dark:hover:bg-secondary/50"
                    >
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <input v-if="activeTrashed" type="checkbox" v-model="selectedDocuments" :value="doc.id" class="h-4 w-4 cursor-pointer rounded border-slate-300 text-tm-navy focus:ring-tm-navy" />
                                <div class="flex h-8 w-8 items-center justify-center rounded-[8px] bg-tm-navy-pale border border-tm-navy/10 dark:bg-secondary">
                                    <File class="h-4 w-4 text-tm-navy dark:text-foreground" />
                                </div>
                                <span class="font-semibold text-tm-navy dark:text-foreground">{{ doc.title }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <span
                                class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                                :class="getTypeColor(doc.type)"
                            >
                                {{ doc.type }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-tm-text-secondary dark:text-muted-foreground">
                            {{ doc.client?.name ?? '-' }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-flex items-center justify-center h-6 w-8 rounded-full bg-tm-green-pale text-[11px] font-bold text-[#1E8A54] border border-tm-green/20 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-500/20">
                                v{{ doc.current_version }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-xs text-tm-text-muted dark:text-muted-foreground">
                            {{ formatBytes(doc.file_size) }}
                        </td>
                        <td class="px-4 py-3.5 text-xs text-tm-text-muted dark:text-muted-foreground">
                            {{ formatDate(doc.created_at) }}
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                <!-- View -->
                                <button
                                    v-if="doc.deleted_at"
                                    @click="restoreDocument(doc.id, doc.title)"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-transparent text-tm-green transition-all hover:border-tm-green/20 hover:bg-tm-green-pale hover:text-[#1E8A54] dark:hover:bg-emerald-950/30 dark:hover:text-emerald-400"
                                    title="Pulihkan dokumen"
                                >
                                    <RotateCcw class="h-4 w-4" />
                                </button>
                                <button
                                    v-if="!doc.deleted_at"
                                    @click="router.visit(`/documents/${doc.id}`)"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-transparent text-tm-text-muted transition-all hover:border-tm-navy/20 hover:bg-tm-navy-pale hover:text-tm-navy dark:hover:bg-secondary dark:hover:text-foreground"
                                    title="Lihat detail"
                                >
                                    <Eye class="h-4 w-4" />
                                </button>
                                <!-- Upload new version -->
                                <button
                                    v-if="!doc.deleted_at"
                                    @click="openEdit(doc)"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-transparent text-tm-text-muted transition-all hover:border-tm-green/20 hover:bg-tm-green-pale hover:text-[#1E8A54] dark:hover:bg-emerald-950/30 dark:hover:text-emerald-400"
                                    title="Upload versi baru"
                                >
                                    <Upload class="h-4 w-4" />
                                </button>
                                <!-- Download -->
                                <a
                                    v-if="doc.file_path && !doc.deleted_at"
                                    :href="`/storage/${doc.file_path}`"
                                    target="_blank"
                                    download
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-transparent text-tm-text-muted transition-all hover:border-tm-navy/20 hover:bg-tm-navy-pale hover:text-tm-navy dark:hover:bg-secondary dark:hover:text-foreground"
                                    title="Download"
                                >
                                    <Download class="h-4 w-4" />
                                </a>
                                <!-- Delete -->
                                <button
                                    v-if="!doc.deleted_at"
                                    @click="deleteDocument(doc.id, doc.title)"
                                    class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-transparent text-tm-text-muted transition-all hover:border-tm-danger/20 hover:bg-tm-danger-pale hover:text-tm-danger dark:hover:bg-red-950/30 dark:hover:text-red-400"
                                    title="Hapus dokumen"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="documents.last_page > 1" class="flex items-center justify-between">
            <span class="text-xs font-medium text-tm-text-muted dark:text-muted-foreground">
                Halaman {{ documents.current_page }} dari {{ documents.last_page }}
            </span>
            <div class="flex items-center gap-1.5">
                <template v-for="link in documents.links" :key="link.label">
                    <button
                        v-if="link.url"
                        @click="router.visit(link.url)"
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
    </div>

    <!-- Modal Create/Edit -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-2"
                >
                    <div v-if="showModal" class="w-full max-w-md rounded-[18px] border-2 border-black bg-white shadow-[4px_6px_0px_0px_rgba(0,0,0,0.8)] dark:bg-card dark:border-border dark:shadow-none">
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between border-b-2 border-black bg-tm-navy-pale px-6 py-4 rounded-t-[16px] dark:bg-secondary dark:border-border">
                            <h2 class="text-lg font-extrabold text-tm-navy dark:text-foreground">
                                {{ editingDocument ? 'Edit Dokumen' : 'Tambah Dokumen' }}
                            </h2>
                            <button
                                @click="closeModal"
                                class="flex h-8 w-8 items-center justify-center rounded-[8px] border-2 border-black bg-white text-tm-navy transition-all hover:bg-tm-danger-pale hover:text-tm-danger hover:border-tm-danger dark:bg-card dark:border-border dark:text-foreground"
                            >
                                <X class="h-4 w-4" />
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <form @submit.prevent="submit" class="space-y-4 p-6">
                            <!-- Title -->
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-foreground">
                                    Judul <span class="text-tm-danger">*</span>
                                </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    required
                                    class="w-full rounded-[10px] border-2 border-tm-border bg-white px-3 py-2.5 text-sm font-medium text-tm-navy transition-all focus:border-tm-green focus:outline-none focus:ring-2 focus:ring-tm-green/20 dark:border-border dark:bg-background dark:text-foreground"
                                    placeholder="Nama dokumen..."
                                />
                                <p v-if="form.errors.title" class="mt-1 text-xs font-medium text-tm-danger">{{ form.errors.title }}</p>
                            </div>

                            <!-- Type -->
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-foreground">
                                    Tipe <span class="text-tm-danger">*</span>
                                </label>
                                <input
                                    v-model="form.type"
                                    type="text"
                                    required
                                    list="doc-type-list"
                                    placeholder="Pilih atau ketik tipe baru..."
                                    class="w-full rounded-[10px] border-2 border-tm-border bg-white px-3 py-2.5 text-sm font-medium text-tm-navy transition-all focus:border-tm-green focus:outline-none focus:ring-2 focus:ring-tm-green/20 dark:border-border dark:bg-background dark:text-foreground"
                                />
                                <datalist id="doc-type-list">
                                    <option v-for="t in documentTypes" :key="t" :value="t" />
                                </datalist>
                                <p v-if="form.errors.type" class="mt-1 text-xs font-medium text-tm-danger">{{ form.errors.type }}</p>
                            </div>

                            <!-- Client -->
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-foreground">
                                    Faskes <span class="text-tm-danger">*</span>
                                </label>
                                <select
                                    v-model="form.client_id"
                                    required
                                    class="w-full rounded-[10px] border-2 border-tm-border bg-white px-3 py-2.5 text-sm font-medium text-tm-navy transition-all focus:border-tm-green focus:outline-none focus:ring-2 focus:ring-tm-green/20 dark:border-border dark:bg-background dark:text-foreground"
                                >
                                    <option value="">-- Pilih Faskes --</option>
                                    <option v-for="client in clients" :key="client.id" :value="String(client.id)">
                                        {{ client.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.client_id" class="mt-1 text-xs font-medium text-tm-danger">{{ form.errors.client_id }}</p>
                            </div>

                            <!-- File -->
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-foreground">
                                    File {{ editingDocument ? '(opsional)' : '' }}
                                </label>
                                <div class="relative">
                                    <input
                                        type="file"
                                        @change="handleFileChange"
                                        class="block w-full rounded-[10px] border-2 border-dashed border-tm-border bg-tm-navy-pale/30 px-3 py-3 text-sm text-tm-text-secondary transition-all file:mr-3 file:rounded-[8px] file:border-2 file:border-black file:bg-white file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-tm-navy file:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] hover:border-tm-green/50 hover:bg-tm-green-pale/20 dark:border-border dark:bg-secondary/30 dark:text-muted-foreground dark:file:bg-card dark:file:text-foreground dark:file:border-border"
                                    />
                                </div>
                                <p v-if="form.errors.file" class="mt-1 text-xs font-medium text-tm-danger">{{ form.errors.file }}</p>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-foreground">
                                    Catatan Versi
                                </label>
                                <input
                                    v-model="form.notes"
                                    type="text"
                                    placeholder="Contoh: Revisi klausul 3"
                                    class="w-full rounded-[10px] border-2 border-tm-border bg-white px-3 py-2.5 text-sm font-medium text-tm-navy transition-all focus:border-tm-green focus:outline-none focus:ring-2 focus:ring-tm-green/20 dark:border-border dark:bg-background dark:text-foreground"
                                />
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3 pt-3">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="flex-1 rounded-[10px] border-2 border-black bg-white px-4 py-2.5 text-sm font-bold text-tm-navy shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] dark:bg-card dark:border-border dark:text-foreground dark:shadow-none"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex-1 rounded-[10px] border-2 border-black bg-tm-green px-4 py-2.5 text-sm font-bold text-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:shadow-none"
                                >
                                    {{ form.processing ? 'Menyimpan...' : (editingDocument ? 'Update' : 'Simpan') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
