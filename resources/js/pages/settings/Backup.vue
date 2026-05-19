<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    CheckCircle,
    Database,
    Download,
    FileText,
    HardDrive,
    Trash2,
    Upload,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { dashboard } from '@/routes';
import {
    create as createBackupRoute,
    destroy as destroyBackupRoute,
    download as downloadBackupRoute,
    index,
    restore as restoreBackupRoute,
} from '@/routes/backup';

interface Backup {
    name: string;
    path: string;
    size: number;
    created_at: number;
}

interface FlashMessages {
    success?: string;
    error?: string;
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

const page = usePage<{ flash: FlashMessages }>();
const flash = computed(() => page.props.flash ?? {});

const isCreatingBackup = ref(false);
const confirmAction = ref({
    open: false,
    title: '',
    description: '',
    confirmLabel: 'Lanjutkan',
    variant: 'warning' as 'danger' | 'warning' | 'success' | 'default',
    loading: false,
    onConfirm: () => {},
});
const restoreForm = useForm({
    backup_file: null as File | null,
});
const selectedFilename = ref<string | null>(null);

const lastBackup = computed(() =>
    props.backups.length ? props.backups[0] : null,
);
const totalBackupSize = computed(() =>
    props.backups.reduce((total, backup) => total + backup.size, 0),
);

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

function createBackup() {
    confirmAction.value = {
        open: true,
        title: 'Buat Backup Database',
        description: 'Sistem akan membuat snapshot database saat ini dan menyimpannya sebagai file SQL di server.',
        confirmLabel: 'Buat Backup',
        variant: 'default',
        loading: false,
        onConfirm: () => {
            confirmAction.value.open = false;
            isCreatingBackup.value = true;
            router.post(
                createBackupRoute.url(),
                {},
                {
                    onFinish: () => {
                        isCreatingBackup.value = false;
                    },
                },
            );
        },
    };
}

function downloadBackup(filename: string) {
    window.location.href = downloadBackupRoute.url({ query: { filename } });
}

function deleteBackup(filename: string) {
    confirmAction.value = {
        open: true,
        title: 'Hapus Backup',
        description: `File backup "${filename}" akan dihapus dari server dan tidak bisa digunakan untuk restore.`,
        confirmLabel: 'Hapus Backup',
        variant: 'danger',
        loading: false,
        onConfirm: () => {
            confirmAction.value.open = false;
            router.delete(destroyBackupRoute.url(), {
                data: { filename },
                preserveState: true,
                preserveScroll: true,
            });
        },
    };
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
        return;
    }

    const filename = selectedFilename.value ?? restoreForm.backup_file.name;

    confirmAction.value = {
        open: true,
        title: 'Restore Database',
        description: `Restore akan menghapus data saat ini dan menggantinya dengan isi file "${filename}". Setelah restore, sistem akan menjalankan migration terbaru agar schema tetap cocok dengan kode aplikasi.`,
        confirmLabel: 'Restore Sekarang',
        variant: 'danger',
        loading: false,
        onConfirm: () => {
            confirmAction.value.loading = true;
            restoreForm.post(restoreBackupRoute.url(), {
                onSuccess: () => {
                    restoreForm.reset();
                    selectedFilename.value = null;
                },
                onFinish: () => {
                    confirmAction.value.loading = false;
                    confirmAction.value.open = false;
                },
            });
        },
    };
}
</script>

<template>
    <Head title="Backup & Restore" />

    <div class="space-y-6">
        <section
            class="overflow-hidden rounded-[18px] border-2 border-black bg-white shadow-[3px_5px_0_0_rgba(0,0,0,0.18)] dark:border-slate-700 dark:bg-[#111c2e] dark:shadow-[0_18px_44px_rgba(0,0,0,0.42)]"
        >
            <div
                class="flex flex-col gap-4 border-b-2 border-black bg-tm-navy-pale px-5 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700 dark:bg-slate-800/70"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border-2 border-black bg-white text-tm-navy shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] dark:border-slate-600 dark:bg-slate-950/40 dark:text-slate-100"
                    >
                        <Database class="h-6 w-6" />
                    </div>
                    <div>
                        <h1
                            class="text-xl font-extrabold text-tm-navy dark:text-slate-100"
                        >
                            Backup & Restore
                        </h1>
                        <p
                            class="mt-1 text-sm text-tm-text-secondary dark:text-slate-400"
                        >
                            Kelola snapshot database dan pemulihan data sistem.
                        </p>
                    </div>
                </div>

                <div
                    class="grid w-full grid-cols-2 gap-2 sm:w-auto sm:min-w-64"
                >
                    <div
                        class="rounded-xl border border-tm-navy/15 bg-white px-3 py-2 shadow-sm dark:border-slate-600 dark:bg-slate-950/40"
                    >
                        <p
                            class="text-[10px] font-bold tracking-wide text-tm-text-secondary uppercase dark:text-slate-400"
                        >
                            Total File
                        </p>
                        <p
                            class="mt-0.5 text-sm font-extrabold text-tm-navy dark:text-slate-100"
                        >
                            {{ backups.length }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-tm-navy/15 bg-white px-3 py-2 shadow-sm dark:border-slate-600 dark:bg-slate-950/40"
                    >
                        <p
                            class="text-[10px] font-bold tracking-wide text-tm-text-secondary uppercase dark:text-slate-400"
                        >
                            Total Ukuran
                        </p>
                        <p
                            class="mt-0.5 text-sm font-extrabold text-tm-navy dark:text-slate-100"
                        >
                            {{ formatBytes(totalBackupSize) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-5 p-5">
                <Alert
                    v-if="flash?.success"
                    class="rounded-xl border-tm-green/30 bg-tm-green-pale text-tm-green-dark dark:border-emerald-300/35 dark:bg-emerald-400/10 dark:text-emerald-200"
                >
                    <CheckCircle class="h-4 w-4 text-tm-green" />
                    <AlertTitle>Berhasil</AlertTitle>
                    <AlertDescription>{{ flash.success }}</AlertDescription>
                </Alert>

                <Alert
                    v-if="flash?.error"
                    variant="destructive"
                    class="rounded-xl border-red-300 bg-red-50 dark:border-red-300/35 dark:bg-red-400/10"
                >
                    <XCircle class="h-4 w-4" />
                    <AlertTitle>Gagal</AlertTitle>
                    <AlertDescription>{{ flash.error }}</AlertDescription>
                </Alert>

                <div class="grid gap-5 xl:grid-cols-2">
                    <div
                        class="flex min-h-[21rem] flex-col rounded-[16px] border-[1.5px] border-black bg-white p-5 shadow-[2px_3px_0_0_rgba(0,0,0,0.12)] dark:border-slate-700 dark:bg-slate-950/25"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border-[1.5px] border-black bg-tm-navy-pale text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.10)] dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                            >
                                <Database class="h-5 w-5" />
                            </div>
                            <div>
                                <h2
                                    class="text-lg font-extrabold text-tm-navy dark:text-slate-100"
                                >
                                    Buat Backup
                                </h2>
                                <p
                                    class="mt-1 text-sm leading-relaxed text-tm-text-secondary dark:text-slate-400"
                                >
                                    Simpan snapshot database ke server sebagai
                                    file SQL.
                                </p>
                            </div>
                        </div>

                        <Button
                            @click="createBackup"
                            :disabled="isCreatingBackup"
                            class="mt-6 inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border-[1.5px] border-black bg-tm-navy text-sm font-bold text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] transition-all hover:-translate-y-0.5 hover:bg-tm-navy-medium hover:shadow-[3px_3px_0_0_rgba(0,0,0,0.2)] disabled:translate-y-0 disabled:opacity-70"
                        >
                            <Download class="h-4 w-4" />
                            {{
                                isCreatingBackup
                                    ? 'Membuat Backup...'
                                    : 'Buat Backup Sekarang'
                            }}
                        </Button>

                        <div
                            class="mt-6 flex-1 rounded-2xl border-[1.5px] border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/30"
                        >
                            <div v-if="lastBackup" class="space-y-2">
                                <p
                                    class="text-xs font-bold tracking-wide text-tm-text-secondary uppercase dark:text-slate-400"
                                >
                                    Backup Terakhir
                                </p>
                                <p
                                    class="text-base font-extrabold break-all text-tm-navy dark:text-slate-100"
                                >
                                    {{ lastBackup.name }}
                                </p>
                                <div
                                    class="flex flex-wrap gap-2 text-xs font-semibold text-tm-text-secondary dark:text-slate-400"
                                >
                                    <span>{{
                                        formatDate(lastBackup.created_at)
                                    }}</span>
                                    <span>|</span>
                                    <span>{{
                                        formatBytes(lastBackup.size)
                                    }}</span>
                                </div>
                            </div>
                            <div
                                v-else
                                class="flex h-full min-h-24 flex-col items-center justify-center text-center"
                            >
                                <HardDrive
                                    class="mb-2 h-8 w-8 text-tm-text-muted dark:text-slate-500"
                                />
                                <p
                                    class="text-sm font-semibold text-tm-text-secondary dark:text-slate-400"
                                >
                                    Belum ada backup tersedia.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex min-h-[21rem] flex-col rounded-[16px] border-[1.5px] border-black bg-white p-5 shadow-[2px_3px_0_0_rgba(0,0,0,0.12)] dark:border-slate-700 dark:bg-slate-950/25"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border-[1.5px] border-black bg-red-50 text-tm-danger shadow-[1px_2px_0_0_rgba(0,0,0,0.10)] dark:border-slate-600 dark:bg-red-400/10 dark:text-red-200"
                            >
                                <Upload class="h-5 w-5" />
                            </div>
                            <div>
                                <h2
                                    class="text-lg font-extrabold text-tm-navy dark:text-slate-100"
                                >
                                    Restore Database
                                </h2>
                                <p
                                    class="mt-1 text-sm leading-relaxed text-tm-text-secondary dark:text-slate-400"
                                >
                                    Upload file SQL untuk mengembalikan data.
                                    Proses akan menimpa data saat ini.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-4">
                            <label class="block cursor-pointer">
                                <div
                                    :class="[
                                        'flex min-h-36 w-full flex-col items-center justify-center rounded-2xl border-2 border-dashed p-4 text-center transition-all',
                                        selectedFilename
                                            ? 'border-tm-green bg-tm-green-pale dark:border-emerald-300/50 dark:bg-emerald-400/10'
                                            : 'border-slate-300 bg-slate-50 hover:border-tm-navy hover:bg-tm-navy-pale dark:border-slate-600 dark:bg-slate-950/30 dark:hover:border-sky-300/50 dark:hover:bg-sky-400/10',
                                    ]"
                                >
                                    <FileText
                                        class="mb-3 h-10 w-10 text-tm-text-muted dark:text-slate-500"
                                    />
                                    <p
                                        class="max-w-full text-sm font-bold break-all text-tm-navy dark:text-slate-100"
                                    >
                                        <span v-if="selectedFilename">{{
                                            selectedFilename
                                        }}</span>
                                        <span v-else
                                            >Klik untuk memilih file .sql</span
                                        >
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-tm-text-secondary dark:text-slate-400"
                                    >
                                        File backup SQL dari sistem.
                                    </p>
                                </div>
                                <input
                                    type="file"
                                    accept=".sql"
                                    @change="handleFileChange"
                                    class="hidden"
                                />
                            </label>

                            <Button
                                @click="restoreBackup"
                                :disabled="
                                    !restoreForm.backup_file ||
                                    restoreForm.processing
                                "
                                variant="destructive"
                                class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border-[1.5px] border-black bg-tm-danger text-sm font-bold text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] transition-all hover:-translate-y-0.5 hover:bg-red-600 hover:shadow-[3px_3px_0_0_rgba(0,0,0,0.2)] disabled:translate-y-0 disabled:opacity-55"
                            >
                                <Upload class="h-4 w-4" />
                                {{
                                    restoreForm.processing
                                        ? 'Memulihkan...'
                                        : 'Restore Database'
                                }}
                            </Button>
                        </div>

                        <div
                            class="mt-5 rounded-2xl border border-red-300 bg-red-50 px-4 py-3 text-sm leading-relaxed text-red-700 dark:border-red-300/35 dark:bg-red-400/10 dark:text-red-200"
                        >
                            Proses ini akan menghapus semua data saat ini.
                            Pastikan Anda memiliki backup sebelum melanjutkan.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section
            class="overflow-hidden rounded-[18px] border-2 border-black bg-white shadow-[3px_5px_0_0_rgba(0,0,0,0.18)] dark:border-slate-700 dark:bg-[#111c2e] dark:shadow-[0_18px_44px_rgba(0,0,0,0.42)]"
        >
            <div
                class="flex flex-col gap-4 border-b-2 border-black bg-tm-navy-pale px-5 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700 dark:bg-slate-800/70"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border-2 border-black bg-white text-tm-navy shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] dark:border-slate-600 dark:bg-slate-950/40 dark:text-slate-100"
                    >
                        <HardDrive class="h-5 w-5" />
                    </div>
                    <div>
                        <h2
                            class="text-xl font-extrabold text-tm-navy dark:text-slate-100"
                        >
                            Riwayat Backup
                        </h2>
                        <p
                            class="mt-1 text-sm text-tm-text-secondary dark:text-slate-400"
                        >
                            Daftar file backup yang tersedia di server.
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-5">
                <div v-if="backups.length === 0" class="py-14 text-center">
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border-2 border-black bg-tm-navy-pale text-tm-navy shadow-[2px_2px_0_0_rgba(0,0,0,0.12)] dark:border-slate-600 dark:bg-sky-400/10 dark:text-sky-200"
                    >
                        <Database class="h-8 w-8" />
                    </div>
                    <p
                        class="text-base font-extrabold text-tm-navy dark:text-slate-100"
                    >
                        Belum ada backup tersedia.
                    </p>
                    <p
                        class="mt-1 text-sm text-tm-text-secondary dark:text-slate-400"
                    >
                        Buat backup pertama untuk mulai menyimpan snapshot
                        database.
                    </p>
                </div>

                <div
                    v-else
                    class="overflow-hidden rounded-2xl border-[1.5px] border-black shadow-[2px_3px_0_0_rgba(0,0,0,0.10)] dark:border-slate-700"
                >
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead
                                class="border-b-[1.5px] border-black bg-tm-navy-pale text-xs font-bold tracking-wide text-tm-navy uppercase dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-200"
                            >
                                <tr>
                                    <th class="px-4 py-3">Nama File</th>
                                    <th class="px-4 py-3">Dibuat</th>
                                    <th class="px-4 py-3">Ukuran</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-950/20"
                            >
                                <tr
                                    v-for="backup in backups"
                                    :key="backup.name"
                                    class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-900/60"
                                >
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-tm-border bg-tm-navy-pale text-tm-navy dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200"
                                            >
                                                <FileText class="h-4 w-4" />
                                            </div>
                                            <span
                                                class="font-bold break-all text-tm-navy dark:text-slate-100"
                                            >
                                                {{ backup.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td
                                        class="px-4 py-3 font-medium text-tm-text-secondary dark:text-slate-400"
                                    >
                                        {{ formatDate(backup.created_at) }}
                                    </td>
                                    <td
                                        class="px-4 py-3 font-medium text-tm-text-secondary dark:text-slate-400"
                                    >
                                        {{ formatBytes(backup.size) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <Button
                                                @click="
                                                    downloadBackup(backup.name)
                                                "
                                                variant="outline"
                                                size="icon"
                                                class="h-9 w-9 rounded-xl border-[1.5px] border-black bg-white text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] transition-all hover:-translate-y-0.5 hover:bg-tm-navy-pale dark:border-slate-600 dark:bg-slate-950/30 dark:text-slate-100 dark:hover:bg-slate-800"
                                                title="Download backup"
                                            >
                                                <Download class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                @click="
                                                    deleteBackup(backup.name)
                                                "
                                                variant="outline"
                                                size="icon"
                                                class="h-9 w-9 rounded-xl border-[1.5px] border-black bg-white text-tm-danger shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] transition-all hover:-translate-y-0.5 hover:bg-tm-danger-pale hover:text-red-700 dark:border-slate-600 dark:bg-slate-950/30 dark:text-red-300 dark:hover:bg-red-400/10"
                                                title="Hapus backup"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <ConfirmDialog
            :open="confirmAction.open"
            :title="confirmAction.title"
            :description="confirmAction.description"
            :confirm-label="confirmAction.confirmLabel"
            cancel-label="Batal"
            :variant="confirmAction.variant"
            :loading="confirmAction.loading"
            @update:open="(value) => (confirmAction.open = value)"
            @confirm="confirmAction.onConfirm"
            @cancel="confirmAction.open = false"
        />
    </div>
</template>
