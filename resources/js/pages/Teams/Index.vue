<script setup lang="ts">
import { computed, ref, onMounted, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { UsersRound, Plus, Edit, Trash2, RotateCcw, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import {
    bulkRestore as bulkRestoreTeamsRoute,
    destroy as destroyTeam,
    index as teamsIndex,
    restore as restoreTeamRoute,
    store as storeTeam,
    update as updateTeam,
} from '@/routes/teams';

// Import komponen UI
import { Button } from '@/components/ui/button';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';

// 1. Menerima data dari TeamController@index
const props = defineProps<{
    teams: Array<{
        id: number;
        name: string;
        type: string;
        phone: string;
        is_active: boolean;
        users_count?: number; // Menampilkan jumlah anggota tim
        deleted_at?: string | null;
    }>;
    activeTrashed?: boolean;
}>();

// 2. Setup Breadcrumbs
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Master Team', href: '#' },
        ],
    },
});

// 3. State Management
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref<number | null>(null);
const selectedTeams = ref<number[]>([]);
const currentPage = ref(1);
const perPage = 10;
const confirmAction = ref({
    open: false,
    title: '',
    description: '',
    confirmLabel: 'Hapus',
    variant: 'danger' as 'danger' | 'warning' | 'success' | 'default',
    onConfirm: () => {},
});

const totalPages = computed(() => Math.ceil(props.teams.length / perPage));
const visibleStart = computed(() => props.teams.length === 0 ? 0 : (currentPage.value - 1) * perPage + 1);
const visibleEnd = computed(() => Math.min(currentPage.value * perPage, props.teams.length));

const paginatedTeams = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return props.teams.slice(start, start + perPage);
});

const selectAllTeams = computed({
    get: () => paginatedTeams.value.length > 0 && selectedTeams.value.length === paginatedTeams.value.length,
    set: (value) => {
        selectedTeams.value = value ? paginatedTeams.value.map((team) => team.id) : [];
    },
});

watch(() => props.teams, () => {
    selectedTeams.value = [];
    currentPage.value = 1;
});

watch(paginatedTeams, () => {
    selectedTeams.value = [];
});

watch(totalPages, (pages) => {
    if (pages > 0 && currentPage.value > pages) {
        currentPage.value = pages;
    }
});

// 4. Inertia Form
const form = useForm({
    name: '',
    type: '',
    phone: '',
    is_active: true,
});

// Buka Modal Tambah
const openAddModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

// Auto-open modal jika ada query param ?action=create (dari dashboard Actions card)
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'create') {
        openAddModal();
    }
});

// Buka Modal Edit
const openEditModal = (team: any) => {
    isEditing.value = true;
    editingId.value = team.id;
    form.name = team.name;
    form.type = team.type || '';
    form.phone = team.phone || '';
    // Konversi nilai 1/0 dari database MySQL menjadi tipe boolean (true/false) untuk frontend
    form.is_active = Boolean(team.is_active); 
    form.clearErrors();
    isModalOpen.value = true;
};

// Submit Data
const submitForm = () => {
    if (isEditing.value) {
        if (editingId.value === null) {
            return;
        }

        form.put(updateTeam.url(editingId.value), {
            onSuccess: () => { isModalOpen.value = false; },
        });
    } else {
        form.post(storeTeam.url(), {
            onSuccess: () => { isModalOpen.value = false; },
        });
    }
};

// Hapus Data
const deleteTeam = (id: number, name: string) => {
    confirmAction.value = {
        open: true,
        title: 'Hapus Tim',
        description: `Tim "${name}" akan dipindahkan ke daftar terhapus dan masih bisa dipulihkan kembali.`,
        confirmLabel: 'Hapus Tim',
        variant: 'danger',
        onConfirm: () => {
            useForm({}).delete(destroyTeam.url(id), {
                preserveScroll: true,
                onSuccess: () => {
                    confirmAction.value.open = false;
                },
            });
        },
    };
};

const restoreTeam = (id: number, name: string) => {
    confirmAction.value = {
        open: true,
        title: 'Pulihkan Tim',
        description: `Tim "${name}" akan dikembalikan ke daftar aktif dan bisa digunakan lagi.`,
        confirmLabel: 'Pulihkan',
        variant: 'success',
        onConfirm: () => {
            router.patch(restoreTeamRoute.url(id), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    confirmAction.value.open = false;
                },
            });
        },
    };
};

const bulkRestoreTeams = (restoreAll = false) => {
    const total = restoreAll ? props.teams.length : selectedTeams.value.length;
    if (total === 0) return;

    confirmAction.value = {
        open: true,
        title: restoreAll ? 'Pulihkan Semua Tim' : 'Pulihkan Tim Terpilih',
        description: `${restoreAll ? 'Semua' : total} tim terhapus akan dikembalikan ke daftar aktif.`,
        confirmLabel: 'Pulihkan',
        variant: 'success',
        onConfirm: () => {
            router.patch(bulkRestoreTeamsRoute.url(), {
                ids: restoreAll ? [] : selectedTeams.value,
                restore_all: restoreAll,
            }, {
                preserveScroll: true,
                onSuccess: () => {
                    selectedTeams.value = [];
                    confirmAction.value.open = false;
                },
            });
        },
    };
};
</script>

<template>
    <Head title="Master Team" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4 md:px-6 md:py-5">
        
        <!-- Header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-tm-navy flex items-center gap-3 dark:text-foreground">
                    <div class="flex h-10 w-10 items-center justify-center rounded-[12px] border-2 border-black bg-tm-navy-pale shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] dark:bg-tm-navy dark:border-border">
                        <UsersRound class="h-5 w-5 text-tm-navy dark:text-white" />
                    </div>
                    Master Team
                </h1>
                <p class="text-sm text-tm-text-secondary mt-1.5 ml-[52px] dark:text-muted-foreground">Kelola departemen atau divisi tim internal Anda.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button
                    @click="router.visit(activeTrashed ? teamsIndex.url() : teamsIndex.url({ query: { trashed: 'only' } }))"
                    class="inline-flex items-center gap-2 rounded-[10px] border-2 border-black bg-white px-4 py-2.5 text-sm font-bold text-tm-navy shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:bg-tm-navy-pale hover:shadow-[3px_4px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:bg-card dark:text-foreground dark:shadow-none"
                >
                    <RotateCcw class="h-4 w-4" /> {{ activeTrashed ? 'Lihat Aktif' : 'Lihat Terhapus' }}
                </button>
                <button
                    v-if="activeTrashed && teams.length > 0"
                    @click="bulkRestoreTeams(true)"
                    class="inline-flex items-center gap-2 rounded-[10px] border-2 border-black bg-tm-green px-4 py-2.5 text-sm font-bold text-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_4px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:shadow-none"
                >
                    <RotateCcw class="h-4 w-4" /> Restore Semua
                </button>
                <button
                    v-if="!activeTrashed"
                    @click="openAddModal"
                    class="inline-flex items-center gap-2 rounded-[10px] border-2 border-black bg-tm-green px-4 py-2.5 text-sm font-bold text-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_4px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:shadow-none"
                >
                    <Plus class="h-4 w-4" /> Tambah Tim
                </button>
            </div>
        </div>

        <div v-if="activeTrashed && selectedTeams.length > 0" class="flex flex-wrap items-center justify-between gap-3 rounded-[14px] border-2 border-black bg-tm-navy-pale p-3 shadow-[2px_3px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:bg-secondary dark:shadow-none">
            <span class="text-sm font-bold text-tm-navy dark:text-foreground">{{ selectedTeams.length }} tim dipilih</span>
            <Button @click="bulkRestoreTeams(false)" size="sm" class="bg-tm-green hover:bg-tm-green-dark">
                <RotateCcw class="mr-2 h-4 w-4" />
                Pulihkan Terpilih
            </Button>
        </div>

        <div class="rounded-[14px] border-2 border-black bg-white px-4 py-3 shadow-[2px_3px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:bg-card dark:shadow-none">
            <p class="text-xs text-tm-text-secondary dark:text-muted-foreground">
                Menampilkan <span class="font-bold text-tm-navy dark:text-foreground">{{ visibleStart }}</span> - <span class="font-bold text-tm-navy dark:text-foreground">{{ visibleEnd }}</span> dari <span class="font-bold text-tm-navy dark:text-foreground">{{ teams.length }}</span> data tim
            </p>
        </div>

        <!-- Tabel Data -->
        <div class="rounded-[14px] border-2 border-black bg-white shadow-[2px_3px_0px_0px_rgba(0,0,0,0.8)] overflow-hidden dark:bg-card dark:border-border dark:shadow-none">
            <div class="overflow-x-auto">
                <table class="teams-index-table w-full text-sm">
                    <thead>
                        <tr class="border-b-2 border-black bg-tm-navy-pale dark:bg-secondary dark:border-border">
                            <th class="px-4 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">
                                <span class="flex items-center gap-2">
                                    <input v-if="activeTrashed" type="checkbox" v-model="selectAllTeams" class="h-4 w-4 cursor-pointer rounded border-slate-300 text-tm-navy focus:ring-tm-navy" />
                                    No
                                </span>
                            </th>
                            <th class="px-4 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Nama Tim</th>
                            <th class="px-4 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Tipe / Divisi</th>
                            <th class="px-4 py-2.5 text-center text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Jumlah Anggota</th>
                            <th class="px-4 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Status</th>
                            <th class="px-4 py-2.5 text-right text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(team, index) in paginatedTeams" :key="team.id" class="border-b border-tm-border transition-colors hover:bg-tm-navy-pale/40 dark:border-border dark:hover:bg-secondary/50">
                            <td class="px-4 py-3.5 text-tm-text-secondary dark:text-muted-foreground">
                                <span class="flex items-center gap-2">
                                    <input v-if="activeTrashed" type="checkbox" v-model="selectedTeams" :value="team.id" class="h-4 w-4 cursor-pointer rounded border-slate-300 text-tm-navy focus:ring-tm-navy" />
                                    {{ (currentPage - 1) * perPage + index + 1 }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-bold text-tm-navy dark:text-foreground">{{ team.name }}</td>
                            <td class="px-4 py-3.5">
                                <span v-if="team.type" class="inline-flex items-center rounded-[6px] border-2 border-tm-navy bg-tm-navy-pale px-2.5 py-0.5 text-xs font-bold uppercase text-tm-navy dark:bg-tm-navy/20 dark:border-tm-navy-medium dark:text-tm-navy-medium">{{ team.type }}</span>
                                <span v-else class="text-tm-text-muted dark:text-muted-foreground">-</span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="font-semibold text-tm-navy dark:text-foreground">{{ team.users_count || 0 }}</span>
                                <span class="text-tm-text-secondary dark:text-muted-foreground"> orang</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span v-if="team.is_active" class="inline-flex items-center gap-1.5 rounded-[6px] border border-tm-green bg-tm-green-pale px-2 py-0.5 text-xs font-bold text-tm-green-dark dark:bg-tm-green/20 dark:border-tm-green/50 dark:text-tm-green">
                                    <span class="h-1.5 w-1.5 rounded-full bg-tm-green"></span>
                                    Aktif
                                </span>
                                <span v-else class="inline-flex items-center gap-1.5 rounded-[6px] border border-tm-danger bg-tm-danger-pale px-2 py-0.5 text-xs font-bold text-tm-danger dark:bg-tm-danger/20 dark:border-tm-danger/50">
                                    <span class="h-1.5 w-1.5 rounded-full bg-tm-danger"></span>
                                    Non-Aktif
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button v-if="team.deleted_at" @click="restoreTeam(team.id, team.name)" title="Pulihkan" class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] border border-tm-border text-tm-green transition-all hover:border-tm-green hover:bg-tm-green-pale dark:border-border dark:hover:bg-tm-green/10">
                                        <RotateCcw class="h-4 w-4" />
                                    </button>
                                    <button v-if="!team.deleted_at" @click="openEditModal(team)" title="Edit" class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] border border-tm-border text-tm-navy-medium transition-all hover:border-tm-navy hover:bg-tm-navy-pale dark:border-border dark:text-foreground dark:hover:bg-secondary">
                                        <Edit class="h-4 w-4" />
                                    </button>
                                    <button v-if="!team.deleted_at" @click="deleteTeam(team.id, team.name)" title="Hapus" class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] border border-tm-border text-tm-danger transition-all hover:border-tm-danger hover:bg-tm-danger-pale dark:border-border dark:hover:bg-tm-danger/10">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="teams.length === 0">
                            <td colspan="6" class="py-12 text-center">
                                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-[12px] border-2 border-black bg-tm-navy-pale shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] dark:bg-secondary dark:border-border">
                                    <UsersRound class="h-5 w-5 text-tm-navy dark:text-muted-foreground" />
                                </div>
                                <p class="text-sm font-semibold text-tm-text-secondary dark:text-muted-foreground">Belum ada data Tim.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="totalPages > 1" class="flex items-center justify-between border-t-2 border-black px-4 py-2 dark:border-border">
                <p class="text-xs font-medium text-tm-text-secondary dark:text-muted-foreground">
                    Halaman {{ currentPage }} dari {{ totalPages }}
                </p>
                <div class="flex items-center gap-1">
                    <button :disabled="currentPage === 1" @click="currentPage--" class="inline-flex h-7 w-7 items-center justify-center rounded-[8px] border-2 border-tm-border text-tm-navy transition-colors hover:bg-tm-navy-pale disabled:cursor-not-allowed disabled:opacity-40 dark:border-border dark:text-foreground dark:hover:bg-secondary">
                        <ChevronLeft class="h-4 w-4" />
                    </button>
                    <template v-for="page in totalPages" :key="page">
                        <button
                            v-if="page === 1 || page === totalPages || Math.abs(page - currentPage) <= 1"
                            @click="currentPage = page"
                            class="inline-flex h-7 w-7 items-center justify-center rounded-[8px] border-2 text-xs font-bold transition-colors"
                            :class="page === currentPage
                                ? 'border-black bg-tm-green text-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] dark:border-tm-green dark:shadow-none'
                                : 'border-tm-border text-tm-navy hover:bg-tm-navy-pale dark:border-border dark:text-foreground dark:hover:bg-secondary'"
                        >
                            {{ page }}
                        </button>
                        <span v-else-if="page === currentPage - 2 || page === currentPage + 2" class="px-1 text-sm text-tm-text-muted">...</span>
                    </template>
                    <button :disabled="currentPage === totalPages" @click="currentPage++" class="inline-flex h-7 w-7 items-center justify-center rounded-[8px] border-2 border-tm-border text-tm-navy transition-colors hover:bg-tm-navy-pale disabled:cursor-not-allowed disabled:opacity-40 dark:border-border dark:text-foreground dark:hover:bg-secondary">
                        <ChevronRight class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Tambah/Edit Tim -->
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle class="text-tm-navy dark:text-foreground">{{ isEditing ? 'Edit Tim' : 'Tambah Tim Baru' }}</DialogTitle>
                    <DialogDescription>Isi detail departemen atau tim internal.</DialogDescription>
                </DialogHeader>
                
                <form @submit.prevent="submitForm" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="name">Nama Tim <span class="text-tm-danger">*</span></Label>
                        <Input id="name" v-model="form.name" placeholder="Contoh: Tim IT Support" :class="{ 'border-tm-danger': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-sm text-tm-danger">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="type">Tipe / Divisi <span class="text-tm-danger">*</span></Label>
                        <select id="type" v-model="form.type" class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-tm-navy focus:border-transparent dark:bg-card dark:border-border">
                            <option value="" disabled>-- Pilih Tipe Divisi --</option>
                            <option value="PRODUCT">PRODUCT</option>
                            <option value="ENGINEER">ENGINEER</option>
                        </select>
                        <p v-if="form.errors.type" class="text-sm text-tm-danger">{{ form.errors.type }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="phone">Telepon Tim</Label>
                        <Input id="phone" v-model="form.phone" placeholder="Kontak darurat tim" :class="{ 'border-tm-danger': form.errors.phone }" />
                        <p v-if="form.errors.phone" class="text-sm text-tm-danger">{{ form.errors.phone }}</p>
                    </div>

                    <!-- Toggle Status -->
                    <div class="flex items-center space-x-2 pt-2">
                        <input type="checkbox" id="is_active" v-model="form.is_active" class="h-4 w-4 rounded border-gray-300 text-tm-green focus:ring-tm-green" />
                        <Label for="is_active" class="cursor-pointer">Set sebagai Tim Aktif</Label>
                    </div>

                    <DialogFooter class="pt-4">
                        <Button type="button" variant="outline" @click="isModalOpen = false">Batal</Button>
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-[10px] border-2 border-black bg-tm-green px-4 py-2 text-sm font-bold text-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 dark:border-border dark:shadow-none">
                            {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Simpan Tim') }}
                        </button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>

    <ConfirmDialog
        :open="confirmAction.open"
        :title="confirmAction.title"
        :description="confirmAction.description"
        :confirm-label="confirmAction.confirmLabel"
        cancel-label="Batal"
        :variant="confirmAction.variant"
        @update:open="(val) => confirmAction.open = val"
        @confirm="confirmAction.onConfirm"
        @cancel="confirmAction.open = false"
    />
</template>

<style scoped>
.teams-index-table :deep(th),
.teams-index-table :deep(td) {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    line-height: 1.2;
}

.teams-index-table :deep(tbody tr) {
    height: 3.1rem;
}

.teams-index-table :deep(.h-8) {
    height: 1.75rem;
}

.teams-index-table :deep(.w-8) {
    width: 1.75rem;
}
</style>
