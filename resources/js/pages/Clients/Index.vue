<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Building2, Plus, Edit, Trash2, CheckCircle, XCircle, Search, ChevronLeft, ChevronRight } from 'lucide-vue-next';
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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';

// 1. Menerima data daftar client dari ClientController@index
const props = defineProps<{
    clients: Array<{
        id: number;
        name: string;
        address: string | null;
        city: string | null;
        type: 'A' | 'B' | 'C' | 'PRATAMA' | null;
        pic_name: string | null;
        pic_phone: string | null;
        is_active: boolean;
        tasks_count: number;
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

// Filter & Pagination
const filterCity = ref('all');
const filterType = ref('all');
const filterPic = ref('all');
const filterStatus = ref('all');
const currentPage = ref(1);
const perPage = 10;

const uniqueCities = computed(() =>
    [...new Set(props.clients.map(c => c.city).filter(Boolean))] as string[]
);

const uniquePics = computed(() =>
    [...new Set(props.clients.map(c => c.pic_name).filter(Boolean))] as string[]
);

const filteredClients = computed(() => {
    return props.clients.filter(c => {
        if (filterCity.value !== 'all' && c.city !== filterCity.value) return false;
        if (filterType.value !== 'all' && c.type !== filterType.value) return false;
        if (filterPic.value !== 'all' && c.pic_name !== filterPic.value) return false;
        if (filterStatus.value === 'aktif' && !c.is_active) return false;
        if (filterStatus.value === 'nonaktif' && c.is_active) return false;
        return true;
    });
});

const totalPages = computed(() => Math.ceil(filteredClients.value.length / perPage));

const paginatedClients = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return filteredClients.value.slice(start, start + perPage);
});

const resetFilters = () => {
    filterCity.value = 'all';
    filterType.value = 'all';
    filterPic.value = 'all';
    filterStatus.value = 'all';
    currentPage.value = 1;
};

// Reset ke halaman 1 saat filter berubah
const applyFilter = () => { currentPage.value = 1; };

// 4. Inertia Form (Untuk binding input & submit data)
const form = useForm({
    name: '',
    address: '',
    city: '',
    type: '' as 'A' | 'B' | 'C' | 'PRATAMA' | '',
    pic_name: '',
    pic_phone: '',
    is_active: true,
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
    form.city = client.city || '';
    form.type = client.type || '';
    form.pic_name = client.pic_name || '';
    form.pic_phone = client.pic_phone || '';
    form.is_active = client.is_active;
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

        <!-- Filter Bar -->
        <div class="rounded-xl border border-sidebar-border bg-card shadow-sm p-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <!-- Filter Kota -->
                <Select v-model="filterCity" @update:modelValue="applyFilter">
                    <SelectTrigger class="h-9 text-sm">
                        <SelectValue placeholder="Semua Kota" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua Kota</SelectItem>
                        <SelectItem v-for="city in uniqueCities" :key="city" :value="city">{{ city }}</SelectItem>
                    </SelectContent>
                </Select>

                <!-- Filter Tipe -->
                <Select v-model="filterType" @update:modelValue="applyFilter">
                    <SelectTrigger class="h-9 text-sm">
                        <SelectValue placeholder="Semua Tipe" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua Tipe</SelectItem>
                        <SelectItem value="PRATAMA">PRATAMA</SelectItem>
                        <SelectItem value="A">Tipe A</SelectItem>
                        <SelectItem value="B">Tipe B</SelectItem>
                        <SelectItem value="C">Tipe C</SelectItem>
                    </SelectContent>
                </Select>

                <!-- Filter PIC -->
                <Select v-model="filterPic" @update:modelValue="applyFilter">
                    <SelectTrigger class="h-9 text-sm">
                        <SelectValue placeholder="Semua PIC" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua PIC</SelectItem>
                        <SelectItem v-for="pic in uniquePics" :key="pic" :value="pic">{{ pic }}</SelectItem>
                    </SelectContent>
                </Select>

                <!-- Filter Status -->
                <Select v-model="filterStatus" @update:modelValue="applyFilter">
                    <SelectTrigger class="h-9 text-sm">
                        <SelectValue placeholder="Semua Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua Status</SelectItem>
                        <SelectItem value="aktif">Aktif</SelectItem>
                        <SelectItem value="nonaktif">Nonaktif</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Info hasil filter + tombol reset -->
            <div class="flex items-center justify-between mt-3">
                <p class="text-xs text-muted-foreground">
                    Menampilkan <span class="font-semibold text-foreground">{{ filteredClients.length }}</span> dari <span class="font-semibold text-foreground">{{ clients.length }}</span> data
                </p>
                <Button v-if="filterCity !== 'all' || filterType !== 'all' || filterPic !== 'all' || filterStatus !== 'all'" variant="ghost" size="sm" @click="resetFilters" class="h-7 text-xs text-muted-foreground hover:text-foreground">
                    Reset Filter
                </Button>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="relative flex-1 rounded-xl border border-sidebar-border bg-card shadow-sm">
            <div class="overflow-x-auto p-4">
                <table class="w-full text-left text-sm">
                    <thead class="bg-muted/50 text-muted-foreground border-b border-border">
                        <tr>
                            <th class="py-3 px-4 font-medium">No</th>
                            <th class="py-3 px-4 font-medium">Nama Faskes</th>
                            <th class="py-3 px-4 font-medium">Kota</th>
                            <th class="py-3 px-4 font-medium">Tipe</th>
                            <th class="py-3 px-4 font-medium">PIC</th>
                            <th class="py-3 px-4 font-medium">Telp PIC</th>
                            <th class="py-3 px-4 font-medium">Tasks</th>
                            <th class="py-3 px-4 font-medium">Status</th>
                            <th class="py-3 px-4 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(client, index) in paginatedClients" :key="client.id" class="border-b border-border last:border-0 hover:bg-muted/30">
                            <td class="py-3 px-4">{{ (currentPage - 1) * 10 + index + 1 }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-primary">{{ client.name }}</div>
                                <div class="text-xs text-muted-foreground">{{ client.address || '-' }}</div>
                            </td>
                            <td class="py-3 px-4">{{ client.city || '-' }}</td>
                            <td class="py-3 px-4">
                                <span v-if="client.type" class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700">{{ client.type }}</span>
                                <span v-else class="text-muted-foreground">-</span>
                            </td>
                            <td class="py-3 px-4">{{ client.pic_name || '-' }}</td>
                            <td class="py-3 px-4">{{ client.pic_phone || '-' }}</td>
                            <td class="py-3 px-4 text-center">{{ client.tasks_count }}</td>
                            <td class="py-3 px-4">
                                <span v-if="client.is_active" class="inline-flex items-center gap-1 text-emerald-600 text-xs font-medium">
                                    <CheckCircle class="h-3.5 w-3.5" /> Aktif
                                </span>
                                <span v-else class="inline-flex items-center gap-1 text-red-500 text-xs font-medium">
                                    <XCircle class="h-3.5 w-3.5" /> Nonaktif
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right space-x-2">
                                <Button variant="outline" size="sm" @click="openEditModal(client)" class="h-8 px-2 text-blue-600 border-blue-200 hover:bg-blue-50">
                                    <Edit class="h-4 w-4" />
                                </Button>
                                <Button variant="outline" size="sm" @click="deleteClient(client.id, client.name)" class="h-8 px-2 text-red-600 border-red-200 hover:bg-red-50">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="filteredClients.length === 0">
                            <td colspan="9" class="py-8 text-center text-muted-foreground">
                                <Search class="h-8 w-8 mx-auto mb-2 opacity-30" />
                                {{ clients.length === 0 ? 'Belum ada data Faskes.' : 'Tidak ada data yang sesuai filter.' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="flex items-center justify-between border-t border-border px-4 py-3">
                <p class="text-xs text-muted-foreground">
                    Halaman {{ currentPage }} dari {{ totalPages }}
                </p>
                <div class="flex items-center gap-1">
                    <Button variant="outline" size="sm" class="h-8 w-8 p-0" :disabled="currentPage === 1" @click="currentPage--">
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                    <template v-for="page in totalPages" :key="page">
                        <Button
                            v-if="page === 1 || page === totalPages || Math.abs(page - currentPage) <= 1"
                            variant="outline"
                            size="sm"
                            class="h-8 w-8 p-0"
                            :class="{ 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700': page === currentPage }"
                            @click="currentPage = page"
                        >
                            {{ page }}
                        </Button>
                        <span v-else-if="page === currentPage - 2 || page === currentPage + 2" class="px-1 text-muted-foreground text-sm">…</span>
                    </template>
                    <Button variant="outline" size="sm" class="h-8 w-8 p-0" :disabled="currentPage === totalPages" @click="currentPage++">
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- MODAL / DIALOG (Akan muncul jika isModalOpen = true) -->
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-[560px]">
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

                    <!-- Input Kota & Tipe (2 kolom) -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="city">Kota</Label>
                            <Input id="city" v-model="form.city" placeholder="Contoh: Surabaya" :class="{ 'border-red-500': form.errors.city }" />
                            <p v-if="form.errors.city" class="text-sm text-red-500">{{ form.errors.city }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label>Tipe Faskes</Label>
                            <Select v-model="form.type">
                                <SelectTrigger :class="{ 'border-red-500': form.errors.type }">
                                    <SelectValue placeholder="Pilih tipe" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="PRATAMA">PRATAMA</SelectItem>
                                    <SelectItem value="A">Tipe A</SelectItem>
                                    <SelectItem value="B">Tipe B</SelectItem>
                                    <SelectItem value="C">Tipe C</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.type" class="text-sm text-red-500">{{ form.errors.type }}</p>
                        </div>
                    </div>

                    <!-- Input PIC -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="pic_name">Nama PIC</Label>
                            <Input id="pic_name" v-model="form.pic_name" placeholder="Nama penanggung jawab" :class="{ 'border-red-500': form.errors.pic_name }" />
                            <p v-if="form.errors.pic_name" class="text-sm text-red-500">{{ form.errors.pic_name }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="pic_phone">Telepon PIC</Label>
                            <Input id="pic_phone" v-model="form.pic_phone" placeholder="Contoh: 08123456789" :class="{ 'border-red-500': form.errors.pic_phone }" />
                            <p v-if="form.errors.pic_phone" class="text-sm text-red-500">{{ form.errors.pic_phone }}</p>
                        </div>
                    </div>

                    <!-- Input Alamat -->
                    <div class="space-y-2">
                        <Label for="address">Alamat Lengkap</Label>
                        <Textarea id="address" v-model="form.address" placeholder="Masukkan alamat lengkap" class="resize-none" :class="{ 'border-red-500': form.errors.address }" />
                        <p v-if="form.errors.address" class="text-sm text-red-500">{{ form.errors.address }}</p>
                    </div>

                    <!-- Toggle Status Aktif -->
                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="is_active" v-model="form.is_active" class="h-4 w-4 rounded border-gray-300 text-emerald-600" />
                        <Label for="is_active" class="cursor-pointer">Faskes Aktif</Label>
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
