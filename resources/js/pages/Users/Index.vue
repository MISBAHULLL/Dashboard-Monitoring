<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { UserCog, Plus, Edit, Trash2, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { dashboard } from '@/routes';

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

// 1. Menerima data dari UserController@index
const props = defineProps<{
    users: Array<any>;
    teams: Array<{ id: number; name: string }>; // Data untuk dropdown pilihan Tim
}>();

// 2. Setup Breadcrumbs
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Master User', href: '#' },
        ],
    },
});

// 3. State Management
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref<number | null>(null);
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

const totalPages = computed(() => Math.ceil(props.users.length / perPage));
const visibleStart = computed(() => props.users.length === 0 ? 0 : (currentPage.value - 1) * perPage + 1);
const visibleEnd = computed(() => Math.min(currentPage.value * perPage, props.users.length));

const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return props.users.slice(start, start + perPage);
});

watch(() => props.users, () => {
    currentPage.value = 1;
});

watch(totalPages, (pages) => {
    if (pages > 0 && currentPage.value > pages) {
        currentPage.value = pages;
    }
});

// 4. Inertia Form
const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'member',
    team_id: '' as string | number, // Kosong saat awal
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

// Buka Modal Edit
const openEditModal = (user: any) => {
    isEditing.value = true;
    editingId.value = user.id;
    form.name = user.name;
    form.email = user.email;
    form.password = ''; // Kosongkan password saat edit
    form.role = user.role;
    form.team_id = user.team_id || '';
    form.is_active = Boolean(user.is_active);
    form.clearErrors();
    isModalOpen.value = true;
};

// Submit Data
const submitForm = () => {
    if (isEditing.value) {
        form.put(`/users/${editingId.value}`, {
            onSuccess: () => { isModalOpen.value = false; },
        });
    } else {
        form.post('/users', {
            onSuccess: () => { isModalOpen.value = false; },
        });
    }
};

// Hapus Data
const deleteUser = (id: number, name: string) => {
    confirmAction.value = {
        open: true,
        title: 'Hapus User',
        description: `Akun "${name}" akan dihapus dari sistem. Pastikan akun ini tidak sedang dibutuhkan untuk akses aplikasi.`,
        confirmLabel: 'Hapus User',
        variant: 'danger',
        onConfirm: () => {
            useForm({}).delete(`/users/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    confirmAction.value.open = false;
                },
            });
        },
    };
};
</script>

<template>
    <Head title="Master User" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4 md:px-6 md:py-5">
        
        <!-- Header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-tm-navy flex items-center gap-3 dark:text-foreground">
                    <div class="flex h-10 w-10 items-center justify-center rounded-[12px] border-2 border-black bg-tm-navy-pale shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] dark:bg-tm-navy dark:border-border">
                        <UserCog class="h-5 w-5 text-tm-navy dark:text-white" />
                    </div>
                    Master User
                </h1>
                <p class="text-sm text-tm-text-secondary mt-1.5 ml-[52px] dark:text-muted-foreground">Kelola akun dan hak akses seluruh staf di sistem ini.</p>
            </div>
            <button
                @click="openAddModal"
                class="inline-flex items-center gap-2 rounded-[10px] border-2 border-black bg-tm-green px-4 py-2.5 text-sm font-bold text-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_4px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:shadow-none"
            >
                <Plus class="h-4 w-4" /> Tambah User
            </button>
        </div>

        <div class="rounded-[14px] border-2 border-black bg-white px-4 py-3 shadow-[2px_3px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:bg-card dark:shadow-none">
            <p class="text-xs text-tm-text-secondary dark:text-muted-foreground">
                Menampilkan <span class="font-bold text-tm-navy dark:text-foreground">{{ visibleStart }}</span> - <span class="font-bold text-tm-navy dark:text-foreground">{{ visibleEnd }}</span> dari <span class="font-bold text-tm-navy dark:text-foreground">{{ users.length }}</span> data user
            </p>
        </div>

        <!-- Tabel Data -->
        <div class="rounded-[14px] border-2 border-black bg-white shadow-[2px_3px_0px_0px_rgba(0,0,0,0.8)] overflow-hidden dark:bg-card dark:border-border dark:shadow-none">
            <div class="overflow-x-auto">
                <table class="users-index-table w-full text-sm">
                    <thead>
                        <tr class="border-b-2 border-black bg-tm-navy-pale dark:bg-secondary dark:border-border">
                            <th class="px-4 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">No</th>
                            <th class="px-4 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Nama & Email</th>
                            <th class="px-4 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Role</th>
                            <th class="px-4 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Divisi Tim</th>
                            <th class="px-4 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Status</th>
                            <th class="px-4 py-2.5 text-right text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(user, index) in paginatedUsers" :key="user.id" class="border-b border-tm-border transition-colors hover:bg-tm-navy-pale/40 dark:border-border dark:hover:bg-secondary/50">
                            <td class="px-4 py-3.5 text-tm-text-secondary dark:text-muted-foreground">{{ (currentPage - 1) * perPage + index + 1 }}</td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-tm-navy dark:text-foreground">{{ user.name }}</div>
                                <div class="text-xs text-tm-text-muted dark:text-muted-foreground">{{ user.email }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span v-if="user.role === 'admin'" class="inline-flex items-center rounded-[6px] border-2 border-tm-danger bg-tm-danger-pale px-2.5 py-0.5 text-[10px] font-bold uppercase text-tm-danger dark:bg-tm-danger/20 dark:border-tm-danger/50">
                                    {{ user.role }}
                                </span>
                                <span v-else class="inline-flex items-center rounded-[6px] border-2 border-tm-navy bg-tm-navy-pale px-2.5 py-0.5 text-[10px] font-bold uppercase text-tm-navy dark:bg-tm-navy/20 dark:border-tm-navy-medium dark:text-tm-navy-medium">
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-tm-text dark:text-foreground">
                                {{ user.team ? user.team.name : 'Belum Masuk Tim' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span v-if="user.is_active" class="inline-flex items-center gap-1.5 rounded-[6px] border border-tm-green bg-tm-green-pale px-2 py-0.5 text-xs font-bold text-tm-green-dark dark:bg-tm-green/20 dark:border-tm-green/50 dark:text-tm-green">
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
                                    <button @click="openEditModal(user)" title="Edit" class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] border border-tm-border text-tm-navy-medium transition-all hover:border-tm-navy hover:bg-tm-navy-pale dark:border-border dark:text-foreground dark:hover:bg-secondary">
                                        <Edit class="h-4 w-4" />
                                    </button>
                                    <button @click="deleteUser(user.id, user.name)" title="Hapus" class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] border border-tm-border text-tm-danger transition-all hover:border-tm-danger hover:bg-tm-danger-pale dark:border-border dark:hover:bg-tm-danger/10">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.length === 0">
                            <td colspan="6" class="py-12 text-center">
                                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-[12px] border-2 border-black bg-tm-navy-pale shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] dark:bg-secondary dark:border-border">
                                    <UserCog class="h-5 w-5 text-tm-navy dark:text-muted-foreground" />
                                </div>
                                <p class="text-sm font-semibold text-tm-text-secondary dark:text-muted-foreground">Belum ada data User.</p>
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

        <!-- Modal Tambah/Edit User -->
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle class="text-tm-navy dark:text-foreground">{{ isEditing ? 'Edit Akun User' : 'Tambah User Baru' }}</DialogTitle>
                    <DialogDescription>Pengaturan akses dan penempatan divisi staf.</DialogDescription>
                </DialogHeader>
                
                <form @submit.prevent="submitForm" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="name">Nama Lengkap <span class="text-tm-danger">*</span></Label>
                        <Input id="name" v-model="form.name" placeholder="John Doe" :class="{ 'border-tm-danger': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-sm text-tm-danger">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="email">Alamat Email <span class="text-tm-danger">*</span></Label>
                        <Input id="email" type="email" v-model="form.email" placeholder="john@example.com" :class="{ 'border-tm-danger': form.errors.email }" />
                        <p v-if="form.errors.email" class="text-sm text-tm-danger">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Password <span v-if="!isEditing" class="text-tm-danger">*</span></Label>
                        <Input id="password" type="password" v-model="form.password" :placeholder="isEditing ? 'Kosongkan jika tidak ingin ganti' : 'Minimal 8 karakter'" :class="{ 'border-tm-danger': form.errors.password }" />
                        <p v-if="form.errors.password" class="text-sm text-tm-danger">{{ form.errors.password }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="role">Hak Akses (Role)</Label>
                            <select id="role" v-model="form.role" class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-tm-navy focus:border-transparent dark:bg-card dark:border-border">
                                <option value="member">Member</option>
                                <option value="admin">Administrator</option>
                            </select>
                            <p v-if="form.errors.role" class="text-sm text-tm-danger">{{ form.errors.role }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="team_id">Divisi Tim</Label>
                            <select id="team_id" v-model="form.team_id" class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-tm-navy focus:border-transparent dark:bg-card dark:border-border">
                                <option value="">(Tidak Ada Tim)</option>
                                <option v-for="team in teams" :key="team.id" :value="team.id">
                                    {{ team.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.team_id" class="text-sm text-tm-danger">{{ form.errors.team_id }}</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 pt-2">
                        <input type="checkbox" id="is_active" v-model="form.is_active" class="h-4 w-4 rounded border-gray-300 text-tm-green focus:ring-tm-green" />
                        <Label for="is_active" class="cursor-pointer">Izinkan Login (Aktif)</Label>
                    </div>

                    <DialogFooter class="pt-4">
                        <Button type="button" variant="outline" @click="isModalOpen = false">Batal</Button>
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-[10px] border-2 border-black bg-tm-green px-4 py-2 text-sm font-bold text-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 dark:border-border dark:shadow-none">
                            {{ form.processing ? 'Memproses...' : (isEditing ? 'Update User' : 'Simpan User') }}
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
.users-index-table :deep(th),
.users-index-table :deep(td) {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    line-height: 1.2;
}

.users-index-table :deep(tbody tr) {
    height: 3.25rem;
}

.users-index-table :deep(.h-8) {
    height: 1.75rem;
}

.users-index-table :deep(.w-8) {
    width: 1.75rem;
}
</style>
