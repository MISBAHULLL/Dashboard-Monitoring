<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Database, Download, Upload, Trash2, AlertTriangle, HardDrive, Clock } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { dashboard } from '@/routes';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

interface Backup {
    name: string;
    path: string;
    size: number;
    created_at: number;
}

const props = defineProps<{
    backups: Backup[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Settings', href: '#' },
    { title: 'Backup & Restore', href: '#' },
];

const isCreatingBackup = ref(false);
const restoreForm = useForm({
    backup_file: null as File | null,
});

function formatBytes(bytes: number): string {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${(bytes / Math.pow(k, i)).toFixed(2)} ${sizes[i]}`;
}

function formatDate(timestamp: number): string {
    return new Date(timestamp * 1000).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function createBackup() {
    if (confirm('Apakah Anda yakin ingin membuat backup database?')) {
        isCreatingBackup.value = true;
        router.post(
            route('backup.create'),
            {},
            {
                onFinish: () => {
                    isCreatingBackup.value = false;
                },
            }
        );
    }
}

function downloadBackup(filename: string) {
    window.location.href = route('backup.download') + '?filename=' + encodeURIComponent(filename);
}

function deleteBackup(filename: string) {
    if (confirm(`Apakah Anda yakin ingin menghapus backup: ${filename}?`)) {
        router.delete(route('backup.destroy'), {
            data: { filename },
            preserveState: true,
            preserveScroll: true,
        });
    }
}

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        restoreForm.backup_file = target.files[0];
    }
}

function restoreBackup() {
    if (!restoreForm.backup_file) {
        alert('Pilih file backup terlebih dahulu!');
        return;
    }

    if (
        confirm(
            '⚠️ PERINGATAN!\n\nRestore database akan MENGHAPUS semua data saat ini dan menggantinya dengan data dari backup.\n\nApakah Anda yakin ingin melanjutkan?'
        )
    ) {
        restoreForm.post(route('backup.restore'), {
            onSuccess: () => {
                restoreForm.reset();
            },
        });
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Backup & Restore" />

        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Backup & Restore</h1>
                    <p class="text-sm text-slate-500">Kelola backup database sistem</p>
                </div>
            </div>

            <!-- Warning Alert -->
            <Alert variant="destructive">
                <AlertTriangle class="h-4 w-4" />
                <AlertDescription>
                    <strong>Peringatan:</strong> Fitur restore akan menghapus semua data saat ini. Pastikan Anda
                    membuat backup terlebih dahulu sebelum melakukan restore.
                </AlertDescription>
            </Alert>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Create Backup Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Database class="h-5 w-5" />
                            Buat Backup Baru
                        </CardTitle>
                        <CardDescription>
                            Download seluruh database dalam format SQL
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Button
                            @click="createBackup"
                            :disabled="isCreatingBackup"
                            class="w-full"
                            size="lg"
                        >
                            <Download class="mr-2 h-4 w-4" />
                            {{ isCreatingBackup ? 'Membuat Backup...' : 'Buat Backup Sekarang' }}
                        </Button>
                        <p class="mt-3 text-xs text-slate-500">
                            Backup akan disimpan di server dan dapat diunduh kapan saja.
                        </p>
                    </CardContent>
                </Card>

                <!-- Restore Backup Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Upload class="h-5 w-5" />
                            Restore dari Backup
                        </CardTitle>
                        <CardDescription>
                            Upload file backup untuk mengembalikan data
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <input
                                type="file"
                                accept=".sql"
                                @change="handleFileChange"
                                class="block w-full text-sm text-slate-500 file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200"
                            />
                            <Button
                                @click="restoreBackup"
                                :disabled="!restoreForm.backup_file || restoreForm.processing"
                                variant="destructive"
                                class="w-full"
                                size="lg"
                            >
                                <Upload class="mr-2 h-4 w-4" />
                                {{ restoreForm.processing ? 'Restoring...' : 'Restore Database' }}
                            </Button>
                        </div>
                        <p class="mt-3 text-xs text-red-500">
                            ⚠️ Proses ini akan menghapus semua data saat ini!
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Backup History -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <HardDrive class="h-5 w-5" />
                        Riwayat Backup
                    </CardTitle>
                    <CardDescription>
                        Daftar file backup yang tersedia
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="backups.length === 0" class="py-8 text-center text-slate-400">
                        <Database class="mx-auto mb-2 h-8 w-8 opacity-40" />
                        <p class="text-sm">Belum ada backup. Buat backup pertama Anda!</p>
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="backup in backups"
                            :key="backup.name"
                            class="flex items-center gap-4 rounded-lg border border-slate-100 p-4 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800/50"
                        >
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-600 dark:bg-sky-950/30">
                                <Database class="h-5 w-5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium truncate">{{ backup.name }}</p>
                                <p class="mt-0.5 text-xs text-slate-500 flex items-center gap-2">
                                    <Clock class="h-3 w-3" />
                                    {{ formatDate(backup.created_at) }}
                                    <span class="text-slate-300">·</span>
                                    <HardDrive class="h-3 w-3" />
                                    {{ formatBytes(backup.size) }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <Button
                                    @click="downloadBackup(backup.name)"
                                    variant="outline"
                                    size="sm"
                                >
                                    <Download class="h-4 w-4" />
                                </Button>
                                <Button
                                    @click="deleteBackup(backup.name)"
                                    variant="outline"
                                    size="sm"
                                >
                                    <Trash2 class="h-4 w-4 text-red-500" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
