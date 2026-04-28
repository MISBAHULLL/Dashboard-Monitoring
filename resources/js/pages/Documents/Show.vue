<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { FileText, ArrowLeft, Download, Clock, User, HardDrive } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    document: {
        id: number;
        title: string;
        type: string;
        file_path: string | null;
        file_name: string | null;
        mime_type: string | null;
        file_size: number | null;
        current_version: number;
        client: { name: string } | null;
        creator: { name: string } | null;
        versions: Array<{
            id: number;
            version_number: number;
            file_path: string;
            doc_url: string | null;
            file_size: number | null;
            notes: string | null;
            uploaded_by: number;
            uploader: { name: string } | null;
            created_at: string;
        }>;
    };
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Dokumen', href: '/documents' },
    { title: props.document.title, href: '#' },
];

function formatBytes(bytes: number | null): string {
    if (!bytes) return '-';
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return `${(bytes / Math.pow(1024, i)).toFixed(2)} ${sizes[i]}`;
}

function formatDate(iso: string): string {
    return new Date(iso).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="document.title" />

        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-8">
            <Button variant="outline" size="sm" @click="router.visit('/documents')">
                <ArrowLeft class="mr-1 h-4 w-4" /> Kembali
            </Button>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Document Info Card -->
                <div class="lg:col-span-1 space-y-4">
                    <div class="rounded-xl border bg-card p-6 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-100 text-sky-600 dark:bg-sky-950/30">
                                <FileText class="h-6 w-6" />
                            </div>
                            <div>
                                <h1 class="text-xl font-bold">{{ document.title }}</h1>
                                <span class="rounded bg-sky-100 px-2 py-0.5 text-xs font-bold text-sky-700 dark:bg-sky-900/30">{{ document.type }}</span>
                            </div>
                        </div>

                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2 dark:border-slate-700">
                                <span class="text-slate-500">Faskes</span>
                                <span class="font-medium">{{ document.client?.name ?? '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2 dark:border-slate-700">
                                <span class="text-slate-500">Dibuat Oleh</span>
                                <span class="font-medium">{{ document.creator?.name ?? '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2 dark:border-slate-700">
                                <span class="text-slate-500">Versi Saat Ini</span>
                                <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700">v{{ document.current_version }}</span>
                            </div>
                            <div v-if="document.file_name" class="flex items-center justify-between border-b border-slate-100 pb-2 dark:border-slate-700">
                                <span class="text-slate-500">Nama File</span>
                                <span class="font-medium truncate max-w-[180px]">{{ document.file_name }}</span>
                            </div>
                            <div v-if="document.mime_type" class="flex items-center justify-between border-b border-slate-100 pb-2 dark:border-slate-700">
                                <span class="text-slate-500">Tipe File</span>
                                <span class="font-medium">{{ document.mime_type }}</span>
                            </div>
                            <div v-if="document.file_size" class="flex items-center justify-between">
                                <span class="text-slate-500">Ukuran</span>
                                <span class="font-medium">{{ formatBytes(document.file_size) }}</span>
                            </div>
                        </div>

                        <div v-if="document.file_path" class="mt-6">
                            <a :href="`/storage/${document.file_path}`" target="_blank" download>
                                <Button variant="outline" class="w-full">
                                    <Download class="mr-2 h-4 w-4" /> Download File Terbaru
                                </Button>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Version History -->
                <div class="lg:col-span-2">
                    <div class="rounded-xl border bg-card p-6 shadow-sm">
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <Clock class="h-5 w-5 text-slate-500" />
                            Riwayat Versi
                        </h2>

                        <div v-if="document.versions.length === 0" class="py-8 text-center text-slate-400">
                            <FileText class="mx-auto mb-2 h-8 w-8 opacity-40" />
                            <p class="text-sm">Belum ada riwayat versi.</p>
                        </div>

                        <div v-else class="space-y-3">
                            <div v-for="version in [...document.versions].reverse()" :key="version.id"
                                 class="flex items-center gap-4 rounded-lg border border-slate-100 p-4 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800/50">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800">
                                    <HardDrive class="h-5 w-5" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded bg-amber-100 px-1.5 py-0 text-[10px] font-bold text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">v{{ version.version_number }}</span>
                                        <span v-if="version.version_number === document.current_version" class="rounded bg-emerald-100 px-1.5 py-0 text-[10px] font-bold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">SAAT INI</span>
                                    </div>
                                    
                                    <!-- Notes -->
                                    <p v-if="version.notes" class="mt-1 text-xs text-slate-600 dark:text-slate-400 italic">
                                        "{{ version.notes }}"
                                    </p>
                                    
                                    <!-- Metadata -->
                                    <p class="mt-0.5 text-xs text-slate-500 flex items-center gap-1 flex-wrap">
                                        <User class="h-3 w-3" /> {{ version.uploader?.name ?? 'System' }}
                                        <span class="text-slate-300">·</span>
                                        <Clock class="h-3 w-3" /> {{ formatDate(version.created_at) }}
                                        <template v-if="version.file_size">
                                            <span class="text-slate-300">·</span>
                                            <HardDrive class="h-3 w-3" />
                                            <span>{{ formatBytes(version.file_size) }}</span>
                                        </template>
                                    </p>
                                </div>
                                <a v-if="version.file_path" :href="`/storage/${version.file_path}`" target="_blank" download
                                   class="shrink-0 rounded-md border border-slate-200 p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:border-slate-700 dark:hover:bg-slate-800">
                                    <Download class="h-4 w-4" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
