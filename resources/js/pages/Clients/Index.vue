<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Building2, Plus, Edit, Trash2 } from 'lucide-vue-next';
import { dashboard } from '@/routes';

// Import komponen UI dari shadcn-vue
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';

// 1. Menerima data daftar client dari ClientController@index
const props = defineProps<{
    clients: Array<{
        id: number;
        name: string;
        address: string;
        phone: string;
        created_at: string;
    }>;
}>();

// 2. Setup Breadcrumbs Navigasi
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Master Faskes', href: '#' },
        ],
    },
});

// 3. State Management untuk Modal (Pop-up)
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref<number | null>(null);

// 4. Inertia Form (Untuk binding input & submit data)
const form = useForm({
    name: '',
    address: '',
    phone: '',
});

// Fungsi membuka modal untuk Tambah Data
const openAddModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset(); // Kosongkan form
    form.clearErrors();
    isModalOpen.value = true;
};

// Fungsi membuka modal untuk Edit Data
const openEditModal = (client: any) => {
    isEditing.value = true;
    editingId.value = client.id;
    form.name = client.name;
    form.address = client.address || '';
    form.phone = client.phone || '';
    form.clearErrors();
    isModalOpen.value = true;
};

// Fungsi Submit (Simpan / Update)
const submitForm = () => {
    if (isEditing.value) {
        // Jika sedang edit, gunakan method PUT
        form.put(`/clients/${editingId.value}`, {
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    } else {
        // Jika tambah baru, gunakan method POST
        form.post('/clients', {
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    }
};

// Fungsi Hapus Data
const deleteClient = (id: number, name: string) => {
    if (confirm(`Apakah Anda yakin ingin menghapus Faskes "${name}"?`)) {
        useForm({}).delete(`/clients/${id}`);
    }
};
</script>

<template>
    <Head title="Master Faskes" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-8">
        
        <!-- Header & Tombol Tambah -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-primary flex items-center gap-2">
                    <Building2 class="h-8 w-8 text-emerald-600" />
                    Master Faskes (Client)
                </h1>
                <p class="text-muted-foreground mt-1">Kelola data Fasilitas Kesehatan yang menjadi pelanggan Anda.</p>
            </div>
            <Button @click="openAddModal" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700">
                <Plus class="h-4 w-4" /> Tambah Faskes
            </Button>
        </div>

        <!-- Tabel Data -->
        <div class="relative flex-1 rounded-xl border border-sidebar-border bg-card shadow-sm">
            <div class="overflow-x-auto p-4">
                <table class="w-full text-left text-sm">
                    <thead class="bg-muted/50 text-muted-foreground border-b border-border">
                        <tr>
                            <th class="py-3 px-4 font-medium">No</th>
                            <th class="py-3 px-4 font-medium">Nama Faskes</th>
                            <th class="py-3 px-4 font-medium">Alamat</th>
                            <th class="py-3 px-4 font-medium">Telepon</th>
                            <th class="py-3 px-4 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(client, index) in clients" :key="client.id" class="border-b border-border last:border-0 hover:bg-muted/30">
                            <td class="py-3 px-4">{{ index + 1 }}</td>
                            <td class="py-3 px-4 font-bold text-primary">{{ client.name }}</td>
                            <td class="py-3 px-4">{{ client.address || '-' }}</td>
                            <td class="py-3 px-4">{{ client.phone || '-' }}</td>
                            <td class="py-3 px-4 text-right space-x-2">
                                <Button variant="outline" size="sm" @click="openEditModal(client)" class="h-8 px-2 text-blue-600 border-blue-200 hover:bg-blue-50">
                                    <Edit class="h-4 w-4" />
                                </Button>
                                <Button variant="outline" size="sm" @click="deleteClient(client.id, client.name)" class="h-8 px-2 text-red-600 border-red-200 hover:bg-red-50">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="clients.length === 0">
                            <td colspan="5" class="py-8 text-center text-muted-foreground">
                                Belum ada data Faskes. Klik tombol "Tambah Faskes" untuk memulai.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL / DIALOG (Akan muncul jika isModalOpen = true) -->
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ isEditing ? 'Edit Faskes' : 'Tambah Faskes Baru' }}</DialogTitle>
                    <DialogDescription>
                        Pastikan nama dan kontak Faskes sudah diisi dengan benar.
                    </DialogDescription>
                </DialogHeader>
                
                <form @submit.prevent="submitForm" class="space-y-4 py-4">
                    <!-- Input Nama -->
                    <div class="space-y-2">
                        <Label for="name">Nama Faskes <span class="text-red-500">*</span></Label>
                        <Input id="name" v-model="form.name" placeholder="Contoh: RSUD Dr. Soetomo" :class="{ 'border-red-500': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Input Nomor Telepon -->
                    <div class="space-y-2">
                        <Label for="phone">Nomor Telepon</Label>
                        <Input id="phone" v-model="form.phone" placeholder="Contoh: 031-123456" :class="{ 'border-red-500': form.errors.phone }" />
                        <p v-if="form.errors.phone" class="text-sm text-red-500">{{ form.errors.phone }}</p>
                    </div>

                    <!-- Input Alamat -->
                    <div class="space-y-2">
                        <Label for="address">Alamat Lengkap</Label>
                        <Textarea id="address" v-model="form.address" placeholder="Masukkan alamat lengkap" class="resize-none" :class="{ 'border-red-500': form.errors.address }" />
                        <p v-if="form.errors.address" class="text-sm text-red-500">{{ form.errors.address }}</p>
                    </div>

                    <DialogFooter class="pt-4">
                        <Button type="button" variant="outline" @click="isModalOpen = false">Batal</Button>
                        <Button type="submit" :disabled="form.processing" class="bg-emerald-600 hover:bg-emerald-700">
                            {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Simpan Faskes') }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
