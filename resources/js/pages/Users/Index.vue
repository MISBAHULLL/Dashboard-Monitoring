<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { UserCog, Plus, Edit, Trash2 } from 'lucide-vue-next';
import { dashboard } from '@/routes';

// Import komponen UI
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
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
    if (confirm(`Apakah Anda yakin ingin menghapus akun "${name}" secara permanen?`)) {
        useForm({}).delete(`/users/${id}`);
    }
};
</script>

<template>
    <Head title="Master User" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-8">
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-primary flex items-center gap-2">
                    <UserCog class="h-8 w-8 text-rose-600" />
                    Master User
                </h1>
                <p class="text-muted-foreground mt-1">Kelola akun dan hak akses seluruh staf di sistem ini.</p>
            </div>
            <Button @click="openAddModal" class="flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white">
                <Plus class="h-4 w-4" /> Tambah User
            </Button>
        </div>

        <div class="relative flex-1 rounded-xl border border-sidebar-border bg-card shadow-sm">
            <div class="overflow-x-auto p-4">
                <table class="w-full text-left text-sm">
                    <thead class="bg-muted/50 text-muted-foreground border-b border-border">
                        <tr>
                            <th class="py-3 px-4 font-medium">No</th>
                            <th class="py-3 px-4 font-medium">Nama & Email</th>
                            <th class="py-3 px-4 font-medium">Role</th>
                            <th class="py-3 px-4 font-medium">Divisi Tim</th>
                            <th class="py-3 px-4 font-medium">Status</th>
                            <th class="py-3 px-4 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(user, index) in users" :key="user.id" class="border-b border-border last:border-0 hover:bg-muted/30">
                            <td class="py-3 px-4">{{ index + 1 }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-primary">{{ user.name }}</div>
                                <div class="text-xs text-muted-foreground">{{ user.email }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <Badge :variant="user.role === 'admin' ? 'destructive' : 'secondary'" class="uppercase text-[10px]">
                                    {{ user.role }}
                                </Badge>
                            </td>
                            <td class="py-3 px-4 font-medium">
                                {{ user.team ? user.team.name : 'Belum Masuk Tim' }}
                            </td>
                            <td class="py-3 px-4">
                                <Badge :variant="user.is_active ? 'default' : 'secondary'" 
                                       :class="user.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800'">
                                    {{ user.is_active ? 'Aktif' : 'Non-Aktif' }}
                                </Badge>
                            </td>
                            <td class="py-3 px-4 text-right space-x-2 flex justify-end">
                                <Button variant="outline" size="sm" @click="openEditModal(user)" class="h-8 px-2 text-blue-600 border-blue-200 hover:bg-blue-50">
                                    <Edit class="h-4 w-4" />
                                </Button>
                                <Button variant="outline" size="sm" @click="deleteUser(user.id, user.name)" class="h-8 px-2 text-red-600 border-red-200 hover:bg-red-50">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ isEditing ? 'Edit Akun User' : 'Tambah User Baru' }}</DialogTitle>
                    <DialogDescription>Pengaturan akses dan penempatan divisi staf.</DialogDescription>
                </DialogHeader>
                
                <form @submit.prevent="submitForm" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="name">Nama Lengkap <span class="text-red-500">*</span></Label>
                        <Input id="name" v-model="form.name" placeholder="John Doe" :class="{ 'border-red-500': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="email">Alamat Email <span class="text-red-500">*</span></Label>
                        <Input id="email" type="email" v-model="form.email" placeholder="john@example.com" :class="{ 'border-red-500': form.errors.email }" />
                        <p v-if="form.errors.email" class="text-sm text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Password <span v-if="!isEditing" class="text-red-500">*</span></Label>
                        <Input id="password" type="password" v-model="form.password" :placeholder="isEditing ? 'Kosongkan jika tidak ingin ganti' : 'Minimal 8 karakter'" :class="{ 'border-red-500': form.errors.password }" />
                        <p v-if="form.errors.password" class="text-sm text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="role">Hak Akses (Role)</Label>
                            <!-- Menggunakan elemen Select native HTML dengan gaya Tailwind -->
                            <select id="role" v-model="form.role" class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                                <option value="member">Member</option>
                                <option value="admin">Administrator</option>
                            </select>
                            <p v-if="form.errors.role" class="text-sm text-red-500">{{ form.errors.role }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="team_id">Divisi Tim</Label>
                            <select id="team_id" v-model="form.team_id" class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                                <option value="">(Tidak Ada Tim)</option>
                                <!-- Looping pilihan dari database -->
                                <option v-for="team in teams" :key="team.id" :value="team.id">
                                    {{ team.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.team_id" class="text-sm text-red-500">{{ form.errors.team_id }}</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 pt-2">
                        <input type="checkbox" id="is_active" v-model="form.is_active" class="h-4 w-4 rounded border-gray-300 text-rose-600 focus:ring-rose-600" />
                        <Label for="is_active" class="cursor-pointer">Izinkan Login (Aktif)</Label>
                    </div>

                    <DialogFooter class="pt-4">
                        <Button type="button" variant="outline" @click="isModalOpen = false">Batal</Button>
                        <Button type="submit" :disabled="form.processing" class="bg-rose-600 hover:bg-rose-700 text-white">
                            {{ form.processing ? 'Memproses...' : (isEditing ? 'Update User' : 'Simpan User') }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
