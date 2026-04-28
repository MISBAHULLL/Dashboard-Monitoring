<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Database, Download, Upload, Trash2, HardDrive, FileText, CheckCircle, XCircle } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { dashboard } from '@/routes';
import { index } from '@/routes/backup';

interface Backup {
    name: string;
    path: string;
    size: number;
    created_at: number;
}

const props = defineProps<{
    backups: Backup[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Settings', href: '#' },
            { title: 'Backup & Restore', href: index() },
        ],
    },
});

const page = usePage();
const flash = page.props.flash || {};

const isCreatingBackup = ref(false);
const restoreForm = useForm({
    backup_file: null as File | null,
});
const selectedFilename = ref<string | null>(null);

function formatBytes(bytes: number): string {
    if (bytes === 0) {
        return '0 B';
    }

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

const lastBackup = computed(() => (props.backups.length ? props.backups[0] : null));

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
        selectedFilename.value = target.files[0].name;
    }
}

function restoreBackup() {
    if (!restoreForm.backup_file) {
        alert('Pilih file backup terlebih dahulu!');

        return;
    }

    if (
        confirm(
            'PERINGATAN!\n\nRestore database akan MENGHAPUS semua data saat ini dan menggantinya dengan data dari backup.\n\nApakah Anda yakin ingin melanjutkan?'
        )
    ) {
        restoreForm.post(route('backup.restore'), {
            onSuccess: () => {
                restoreForm.reset();
                selectedFilename.value = null;
            },
        });
    }
}
</script>

<template>
    <Head title="Backup & Restore" />

    <div class="flex flex-col space-y-6">
        <div class="flex flex-col gap-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold">Backup & Restore</h1>
                    <p class="text-base text-muted-foreground mt-2">Kelola backup database sistem</p>
                </div>
            </div>

            <!-- Flash messages -->
            <Alert v-if="flash?.success" class="border-green-200 bg-green-50 text-green-800">
                <CheckCircle class="h-4 w-4 text-green-600" />
                <AlertTitle class="text-green-800">Berhasil</AlertTitle>
                <AlertDescription class="text-green-700">{{ flash.success }}</AlertDescription>
            </Alert>

            <Alert v-if="flash?.error" variant="destructive">
                <XCircle class="h-4 w-4" />
                <AlertTitle>Gagal</AlertTitle>
                <AlertDescription>{{ flash.error }}</AlertDescription>
            </Alert>

            <!-- Action Cards -->
            <div class="flex flex-col xl:flex-row gap-8">
                <!-- Create Backup Card -->
                <Card class="border-2 flex-1">
                    <CardHeader class="pb-6">
                        <CardTitle class="flex items-center gap-3 text-2xl">
                            <Database class="h-6 w-6 text-primary shrink-0" />
                            Buat Backup
                        </CardTitle>
                        <CardDescription class="text-base mt-2">
                            Simpan snapshot database ke server sebagai file SQL.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <Button
                            @click="createBackup"
                            :disabled="isCreatingBackup"
                            class="w-full h-auto min-h-[3rem] whitespace-normal text-center py-2"
                            size="lg"
                        >
                            <Download class="mr-2 h-5 w-5 shrink-0" />
                            <span>{{ isCreatingBackup ? 'Membuat Backup...' : 'Buat Backup Sekarang' }}</span>
                        </Button>

                        <div class="rounded-lg bg-muted/50 p-4 text-base overflow-hidden">
                            <div v-if="lastBackup" class="space-y-2">
                                <p class="text-muted-foreground">Backup terakhir:</p>
                                <p class="font-semibold text-lg break-all">{{ lastBackup.name }}</p>
                                <p class="text-sm text-muted-foreground flex flex-wrap gap-1">
                                    <span>{{ formatDate(lastBackup.created_at) }}</span>
                                    <span>·</span>
                                    <span>{{ formatBytes(lastBackup.size) }}</span>
                                </p>
                            </div>
                            <p v-else class="text-muted-foreground">Belum ada backup tersedia.</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Restore Backup Card -->
                <Card class="border-2 flex-1">
                    <CardHeader class="pb-6">
                        <CardTitle class="flex items-center gap-3 text-2xl">
                            <Upload class="h-6 w-6 text-primary shrink-0" />
                            Restore Database
                        </CardTitle>
                        <CardDescription class="text-base mt-2">
                            Upload file SQL untuk mengembalikan data. Proses akan menimpa data saat ini.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <label class="flex-1 cursor-pointer">
                                    <div class="flex flex-col items-center justify-center w-full min-h-[8rem] border-2 border-dashed border-muted-foreground/25 rounded-lg hover:border-primary/50 transition-colors p-4 text-center" :class="{ 'border-primary bg-primary/5': selectedFilename }">
                                        <FileText class="mx-auto h-10 w-10 text-muted-foreground mb-3 shrink-0" />
                                        <p class="text-base text-muted-foreground break-all">
                                            <span v-if="selectedFilename" class="font-semibold text-foreground">{{ selectedFilename }}</span>
                                            <span v-else>Klik untuk memilih file .sql</span>
                                        </p>
                                    </div>
                                    <input
                                        type="file"
                                        accept=".sql"
                                        @change="handleFileChange"
                                        class="hidden"
                                    />
                                </label>
                            </div>

                            <Button
                                @click="restoreBackup"
                                :disabled="!restoreForm.backup_file || restoreForm.processing"
                                variant="destructive"
                                class="w-full h-auto min-h-[3rem] whitespace-normal text-center py-2"
                                size="lg"
                            >
                                <Upload class="mr-2 h-5 w-5 shrink-0" />
                                <span>{{ restoreForm.processing ? 'Restoring...' : 'Restore Database' }}</span>
                            </Button>
                        </div>

                        <Alert variant="destructive" class="bg-red-50 border-red-200">
                            <AlertDescription class="text-sm text-red-700 leading-relaxed">
                                Proses ini akan menghapus semua data saat ini. Pastikan Anda memiliki backup sebelum melanjutkan.
                            </AlertDescription>
                        </Alert>
                    </CardContent>
                </Card>
            </div>

            <!-- Backup History -->
            <Card class="border-2">
                <CardHeader class="pb-6">
                    <CardTitle class="flex items-center gap-3 text-2xl">
                        <HardDrive class="h-6 w-6 text-primary" />
                        Riwayat Backup
                    </CardTitle>
                    <CardDescription class="text-base mt-2">
                        Daftar file backup yang tersedia di server
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="backups.length === 0" class="py-16 text-center">
                        <Database class="mx-auto mb-4 h-16 w-16 text-muted-foreground/40" />
                        <p class="text-lg text-muted-foreground">Belum ada backup tersedia.</p>
                        <p class="text-base text-muted-foreground/70 mt-2">Buat backup pertama Anda untuk memulai.</p>
                    </div>

                    <div v-else class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="text-base">Nama File</TableHead>
                                    <TableHead class="text-base">Dibuat</TableHead>
                                    <TableHead class="text-base">Ukuran</TableHead>
                                    <TableHead class="text-right text-base">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="backup in backups" :key="backup.name">
                                    <TableCell class="font-medium text-base">{{ backup.name }}</TableCell>
                                    <TableCell class="text-muted-foreground text-base">{{ formatDate(backup.created_at) }}</TableCell>
                                    <TableCell class="text-muted-foreground text-base">{{ formatBytes(backup.size) }}</TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-3">
                                            <Button @click="downloadBackup(backup.name)" variant="outline" size="sm" class="h-10 px-4">
                                                <Download class="h-5 w-5" />
                                            </Button>
                                            <Button @click="deleteBackup(backup.name)" variant="outline" size="sm" class="text-destructive hover:text-destructive h-10 px-4">
                                                <Trash2 class="h-5 w-5" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
