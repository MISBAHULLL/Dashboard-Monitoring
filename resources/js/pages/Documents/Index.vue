<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { FileText, Plus, Download, Trash2, Eye, Upload } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

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
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Dokumen', href: '#' },
];

const showModal = ref(false);
const editingDocument = ref<Document | null>(null);

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
        form.post(`/documents/${editingDocument.value.id}`, {
            forceFormData: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/documents', {
            forceFormData: true,
            onSuccess: () => closeModal(),
        });
    }
}

function deleteDocument(id: number, title: string) {
    if (confirm(`Hapus dokumen "${title}"?`)) {
        router.delete(`/documents/${id}`);
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
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Dokumen" />

        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Dokumen</h1>
                    <p class="text-sm text-slate-500">Kelola dokumen dan riwayat versinya</p>
                </div>
                <Button @click="openCreate">
                    <Plus class="mr-2 h-4 w-4" /> Tambah Dokumen
                </Button>
            </div>

            <!-- Table -->
            <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
                <div v-if="documents.data.length === 0" class="py-16 text-center text-slate-400">
                    <FileText class="mx-auto mb-3 h-10 w-10 opacity-40" />
                    <p class="text-sm">Belum ada dokumen. Tambahkan dokumen pertama!</p>
                </div>

                <table v-else class="w-full text-sm">
                    <thead class="border-b bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Judul</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Tipe</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Faskes</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Versi</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Ukuran</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Tanggal</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="doc in documents.data" :key="doc.id"
                            class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <FileText class="h-4 w-4 shrink-0 text-sky-500" />
                                    <span class="font-medium">{{ doc.title }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-700 dark:bg-sky-900/30 dark:text-sky-400">
                                    {{ doc.type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ doc.client?.name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                    v{{ doc.current_version }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ formatBytes(doc.file_size) }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ formatDate(doc.created_at) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <Button variant="ghost" size="sm" @click="router.visit(`/documents/${doc.id}`)">
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="sm" @click="openEdit(doc)">
                                        <Upload class="h-4 w-4" />
                                    </Button>
                                    <a v-if="doc.file_path" :href="`/storage/${doc.file_path}`" target="_blank" download>
                                        <Button variant="ghost" size="sm">
                                            <Download class="h-4 w-4" />
                                        </Button>
                                    </a>
                                    <Button variant="ghost" size="sm" @click="deleteDocument(doc.id, doc.title)">
                                        <Trash2 class="h-4 w-4 text-red-500" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="documents.last_page > 1" class="flex items-center justify-between text-sm text-slate-500">
                <span>Total {{ documents.total }} dokumen</span>
                <div class="flex gap-1">
                    <template v-for="link in documents.links" :key="link.label">
                        <button
                            v-if="link.url"
                            @click="router.visit(link.url)"
                            :class="['rounded px-3 py-1 border transition-colors', link.active ? 'bg-primary text-primary-foreground border-primary' : 'hover:bg-slate-100 dark:hover:bg-slate-800']"
                            v-html="link.label"
                        />
                        <span v-else class="rounded px-3 py-1 border opacity-40 cursor-not-allowed" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>

        <!-- Modal Create/Edit -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white shadow-xl dark:bg-slate-900">
                <div class="border-b p-6 dark:border-slate-700">
                    <h2 class="text-lg font-bold">
                        {{ editingDocument ? 'Edit Dokumen' : 'Tambah Dokumen' }}
                    </h2>
                </div>
                <form @submit.prevent="submit" class="space-y-4 p-6">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Judul <span class="text-red-500">*</span></label>
                        <input v-model="form.title" type="text" required
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary dark:border-slate-600 dark:bg-slate-800" />
                        <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Tipe <span class="text-red-500">*</span></label>
                        <input v-model="form.type" type="text" required placeholder="Contoh: Kontrak, SOP, MOU"
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary dark:border-slate-600 dark:bg-slate-800" />
                        <p v-if="form.errors.type" class="mt-1 text-xs text-red-500">{{ form.errors.type }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Faskes <span class="text-red-500">*</span></label>
                        <select v-model="form.client_id" required
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary dark:border-slate-600 dark:bg-slate-800">
                            <option value="">-- Pilih Faskes --</option>
                            <option v-for="client in clients" :key="client.id" :value="String(client.id)">
                                {{ client.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.client_id" class="mt-1 text-xs text-red-500">{{ form.errors.client_id }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            File {{ editingDocument ? '(kosongkan jika tidak ingin update file)' : '' }}
                        </label>
                        <input type="file" @change="handleFileChange"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200" />
                        <p v-if="form.errors.file" class="mt-1 text-xs text-red-500">{{ form.errors.file }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Catatan Versi</label>
                        <input v-model="form.notes" type="text" placeholder="Contoh: Revisi klausul 3"
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary dark:border-slate-600 dark:bg-slate-800" />
                    </div>
                    <div class="flex gap-3 pt-2">
                        <Button type="button" variant="outline" class="flex-1" @click="closeModal">Batal</Button>
                        <Button type="submit" class="flex-1" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : (editingDocument ? 'Update' : 'Simpan') }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
