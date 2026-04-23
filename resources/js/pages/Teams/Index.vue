<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { UsersRound, Plus, Edit, Trash2 } from 'lucide-vue-next';
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

// 1. Menerima data dari TeamController@index
const props = defineProps<{
    teams: Array<{
        id: number;
        name: string;
        type: string;
        phone: string;
        is_active: boolean;
        users_count?: number; // Menampilkan jumlah anggota tim
    }>;
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
        form.put(`/teams/${editingId.value}`, {
            onSuccess: () => { isModalOpen.value = false; },
        });
    } else {
        form.post('/teams', {
            onSuccess: () => { isModalOpen.value = false; },
        });
    }
};

// Hapus Data
const deleteTeam = (id: number, name: string) => {
    if (confirm(`Peringatan: Menghapus tim "${name}" bisa berdampak pada user di dalamnya. Yakin?`)) {
        useForm({}).delete(`/teams/${id}`);
    }
};
</script>

<template>
    <Head title="Master Team" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-8">
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-primary flex items-center gap-2">
                    <UsersRound class="h-8 w-8 text-indigo-600" />
                    Master Team
                </h1>
                <p class="text-muted-foreground mt-1">Kelola departemen atau divisi tim internal Anda.</p>
            </div>
            <Button @click="openAddModal" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white">
                <Plus class="h-4 w-4" /> Tambah Tim
            </Button>
        </div>

        <div class="relative flex-1 rounded-xl border border-sidebar-border bg-card shadow-sm">
            <div class="overflow-x-auto p-4">
                <table class="w-full text-left text-sm">
                    <thead class="bg-muted/50 text-muted-foreground border-b border-border">
                        <tr>
                            <th class="py-3 px-4 font-medium">No</th>
                            <th class="py-3 px-4 font-medium">Nama Tim</th>
                            <th class="py-3 px-4 font-medium">Tipe / Divisi</th>
                            <th class="py-3 px-4 font-medium text-center">Jumlah Anggota</th>
                            <th class="py-3 px-4 font-medium">Status</th>
                            <th class="py-3 px-4 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(team, index) in teams" :key="team.id" class="border-b border-border last:border-0 hover:bg-muted/30">
                            <td class="py-3 px-4">{{ index + 1 }}</td>
                            <td class="py-3 px-4 font-bold text-primary">{{ team.name }}</td>
                            <td class="py-3 px-4 capitalize">{{ team.type || '-' }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="font-semibold text-indigo-600">{{ team.users_count || 0 }}</span> orang
                            </td>
                            <td class="py-3 px-4">
                                <!-- Menggunakan komponen Badge shadcn untuk Status -->
                                <Badge :variant="team.is_active ? 'default' : 'secondary'" 
                                       :class="team.is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-800'">
                                    {{ team.is_active ? 'Aktif' : 'Non-Aktif' }}
                                </Badge>
                            </td>
                            <td class="py-3 px-4 text-right space-x-2 flex justify-end">
                                <Button variant="outline" size="sm" @click="openEditModal(team)" class="h-8 px-2 text-blue-600 border-blue-200 hover:bg-blue-50">
                                    <Edit class="h-4 w-4" />
                                </Button>
                                <Button variant="outline" size="sm" @click="deleteTeam(team.id, team.name)" class="h-8 px-2 text-red-600 border-red-200 hover:bg-red-50">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="teams.length === 0">
                            <td colspan="6" class="py-8 text-center text-muted-foreground">
                                Belum ada data Tim.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ isEditing ? 'Edit Tim' : 'Tambah Tim Baru' }}</DialogTitle>
                    <DialogDescription>Isi detail departemen atau tim internal.</DialogDescription>
                </DialogHeader>
                
                <form @submit.prevent="submitForm" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="name">Nama Tim <span class="text-red-500">*</span></Label>
                        <Input id="name" v-model="form.name" placeholder="Contoh: Tim IT Support" :class="{ 'border-red-500': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="type">Tipe / Divisi <span class="text-red-500">*</span></Label>
                        <select id="type" v-model="form.type" class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
                            <option value="" disabled>-- Pilih Tipe Divisi --</option>
                            <option value="PRODUCT">PRODUCT</option>
                            <option value="ENGINEER">ENGINEER</option>
                        </select>
                        <p v-if="form.errors.type" class="text-sm text-red-500">{{ form.errors.type }}</p>
                    </div>


                    <div class="space-y-2">
                        <Label for="phone">Telepon Tim</Label>
                        <Input id="phone" v-model="form.phone" placeholder="Kontak darurat tim" :class="{ 'border-red-500': form.errors.phone }" />
                        <p v-if="form.errors.phone" class="text-sm text-red-500">{{ form.errors.phone }}</p>
                    </div>

                    <!-- Toggle Sederhana menggunakan Label dan Checkbox Native -->
                    <div class="flex items-center space-x-2 pt-2">
                        <input type="checkbox" id="is_active" v-model="form.is_active" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" />
                        <Label for="is_active" class="cursor-pointer">Set sebagai Tim Aktif</Label>
                    </div>

                    <DialogFooter class="pt-4">
                        <Button type="button" variant="outline" @click="isModalOpen = false">Batal</Button>
                        <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white">
                            {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Simpan Tim') }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
