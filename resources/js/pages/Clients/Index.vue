<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Building2, Plus, Edit, Trash2, Search, ChevronLeft, ChevronRight, Eye, FilePlus, FileText } from 'lucide-vue-next';
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
        documents_count: number;
        created_at: string;
    }>;
    documentTypes: string[];
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

// Auto-open modal jika ada query param ?action=create (dari dashboard Actions card)
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'create') {
        openAddModal();
    }
});

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

// === DOKUMEN ===
const isDocModalOpen = ref(false);
const selectedClientName = ref('');

const docForm = useForm({
    client_id: '' as string,
    title: '',
    type: '',
    file: null as File | null,
    notes: '',
});

const openDocModal = (client: { id: number; name: string }) => {
    docForm.reset();
    docForm.clearErrors();
    docForm.client_id = String(client.id);
    selectedClientName.value = client.name;
    isDocModalOpen.value = true;
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files?.[0]) docForm.file = target.files[0];
};

const submitDoc = () => {
    docForm.post('/documents', {
        forceFormData: true,
        onSuccess: () => { isDocModalOpen.value = false; },
    });
};
</script>

<template>
    <Head title="Master Faskes" />

    <div class="flex h-full flex-1 flex-col gap-0 overflow-x-auto">
        
        <!-- Header dengan background gradient -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-6 py-5 sm:px-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white flex items-center gap-2.5">
                        <Building2 class="h-7 w-7 text-emerald-400" />
                        Master Faskes (Client)
                    </h1>
                    <p class="text-slate-300 text-sm mt-1">Kelola data Fasilitas Kesehatan yang menjadi pelanggan Anda.</p>
                </div>
                <Button @click="openAddModal" class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white shadow-md">
                    <Plus class="h-4 w-4" /> Tambah Faskes
                </Button>
            </div>
        </div>

        <!-- Content area -->
        <div class="flex flex-col gap-4 p-4 sm:p-6">
            <!-- Filter Bar -->
            <div class="rounded-lg border border-border bg-card p-4">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Filter Kota -->
                    <Select v-model="filterCity" @update:modelValue="applyFilter">
                        <SelectTrigger class="h-9 w-[140px] text-sm border-dashed">
                            <SelectValue placeholder="Semua Kota" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Kota</SelectItem>
                            <SelectItem v-for="city in uniqueCities" :key="city" :value="city">{{ city }}</SelectItem>
                        </SelectContent>
                    </Select>

                    <!-- Filter Tipe -->
                    <Select v-model="filterType" @update:modelValue="applyFilter">
                        <SelectTrigger class="h-9 w-[140px] text-sm border-dashed">
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
                        <SelectTrigger class="h-9 w-[140px] text-sm border-dashed">
                            <SelectValue placeholder="Semua PIC" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua PIC</SelectItem>
                            <SelectItem v-for="pic in uniquePics" :key="pic" :value="pic">{{ pic }}</SelectItem>
                        </SelectContent>
                    </Select>

                    <!-- Filter Status -->
                    <Select v-model="filterStatus" @update:modelValue="applyFilter">
                        <SelectTrigger class="h-9 w-[140px] text-sm border-dashed">
                            <SelectValue placeholder="Semua Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Status</SelectItem>
                            <SelectItem value="aktif">Aktif</SelectItem>
                            <SelectItem value="nonaktif">Nonaktif</SelectItem>
                        </SelectContent>
                    </Select>

                    <!-- Reset Filter -->
                    <Button v-if="filterCity !== 'all' || filterType !== 'all' || filterPic !== 'all' || filterStatus !== 'all'" variant="ghost" size="sm" @click="resetFilters" class="h-9 text-xs text-muted-foreground hover:text-foreground">
                        Reset Filter
                    </Button>
                </div>

                <!-- Info hasil filter -->
                <p class="text-xs text-muted-foreground mt-2.5">
                    Menampilkan <span class="font-semibold text-foreground">{{ filteredClients.length }}</span> dari <span class="font-semibold text-foreground">{{ clients.length }}</span> data
                </p>
            </div>

            <!-- Tabel Data -->
            <div class="rounded-lg border border-border bg-card shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/40">
                                <th class="py-3 px-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">No</th>
                                <th class="py-3 px-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Nama Faskes</th>
                                <th class="py-3 px-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Kota</th>
                                <th class="py-3 px-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Tipe</th>
                                <th class="py-3 px-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">PIC</th>
                                <th class="py-3 px-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Telp PIC</th>
                                <th class="py-3 px-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider text-center">Tasks</th>
                                <th class="py-3 px-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Dokumen</th>
                                <th class="py-3 px-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Status</th>
                                <th class="py-3 px-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="(client, index) in paginatedClients" :key="client.id" class="hover:bg-muted/20 transition-colors">
                                <td class="py-3.5 px-4 text-muted-foreground">{{ (currentPage - 1) * perPage + index + 1 }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-foreground">{{ client.name }}</div>
                                    <div class="text-xs text-muted-foreground mt-0.5">{{ client.address || '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-foreground">{{ client.city || '-' }}</td>
                                <td class="py-3.5 px-4">
                                    <span v-if="client.type === 'PRATAMA'" class="inline-flex items-center rounded px-2 py-0.5 text-xs font-bold bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300">PRATAMA</span>
                                    <span v-else-if="client.type" class="inline-flex items-center justify-center h-6 w-6 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">{{ client.type }}</span>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>
                                <td class="py-3.5 px-4 text-foreground">{{ client.pic_name || '-' }}</td>
                                <td class="py-3.5 px-4 text-foreground">{{ client.pic_phone || '-' }}</td>
                                <td class="py-3.5 px-4 text-center font-medium text-foreground">{{ client.tasks_count }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-sm font-medium text-foreground">{{ client.documents_count }}</span>
                                        <Button variant="ghost" size="sm" class="h-6 w-6 p-0 text-sky-600 hover:text-sky-700 hover:bg-sky-50 dark:hover:bg-sky-950" @click="router.visit(`/documents?client_id=${client.id}`)" title="Lihat Dokumen">
                                            <Eye class="h-3.5 w-3.5" />
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-6 w-6 p-0 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-950" @click="openDocModal(client)" title="Tambah Dokumen">
                                            <FilePlus class="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span v-if="client.is_active" class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1.5 text-xs font-medium text-red-500 dark:text-red-400">
                                        <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                        Nonaktif
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button variant="ghost" size="sm" @click="openEditModal(client)" class="h-8 w-8 p-0 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-950">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="deleteClient(client.id, client.name)" class="h-8 w-8 p-0 text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredClients.length === 0">
                                <td colspan="10" class="py-12 text-center text-muted-foreground">
                                    <Search class="h-10 w-10 mx-auto mb-3 opacity-30" />
                                    <p class="text-sm">{{ clients.length === 0 ? 'Belum ada data Faskes.' : 'Tidak ada data yang sesuai filter.' }}</p>
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
                                size="sm"
                                class="h-8 w-8 p-0"
                                :class="page === currentPage
                                    ? 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700 hover:text-white'
                                    : 'bg-transparent border border-border text-foreground hover:bg-muted'"
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

        <!-- MODAL TAMBAH DOKUMEN -->
        <Dialog :open="isDocModalOpen" @update:open="isDocModalOpen = $event">
            <DialogContent class="sm:max-w-[480px]">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <FileText class="h-5 w-5 text-sky-600" />
                        Tambah Dokumen
                    </DialogTitle>
                    <DialogDescription>
                        Faskes: <span class="font-semibold text-foreground">{{ selectedClientName }}</span>
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitDoc" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="doc_title">Judul Dokumen <span class="text-red-500">*</span></Label>
                        <Input id="doc_title" v-model="docForm.title" placeholder="Contoh: Kontrak Kerjasama 2025" :class="{ 'border-red-500': docForm.errors.title }" />
                        <p v-if="docForm.errors.title" class="text-sm text-red-500">{{ docForm.errors.title }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="doc_type">Tipe Dokumen <span class="text-red-500">*</span></Label>
                        <input id="doc_type" v-model="docForm.type" type="text" required list="client-doc-type-list"
                            placeholder="Pilih atau ketik tipe baru..."
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs focus:outline-none focus:ring-2 focus:ring-ring"
                            :class="{ 'border-red-500': docForm.errors.type }" />
                        <datalist id="client-doc-type-list">
                            <option v-for="t in documentTypes" :key="t" :value="t" />
                        </datalist>
                        <p v-if="docForm.errors.type" class="text-sm text-red-500">{{ docForm.errors.type }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label>File Dokumen</Label>
                        <input type="file" @change="handleFileChange"
                            class="block w-full text-sm text-muted-foreground file:mr-4 file:rounded-md file:border-0 file:bg-muted file:px-3 file:py-1.5 file:text-sm file:font-medium hover:file:bg-muted/80" />
                        <p v-if="docForm.errors.file" class="text-sm text-red-500">{{ docForm.errors.file }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="doc_notes">Catatan Versi</Label>
                        <Input id="doc_notes" v-model="docForm.notes" placeholder="Contoh: Versi awal" />
                    </div>

                    <DialogFooter class="pt-2">
                        <Button type="button" variant="outline" @click="isDocModalOpen = false">Batal</Button>
                        <Button type="submit" :disabled="docForm.processing" class="bg-sky-600 hover:bg-sky-700">
                            {{ docForm.processing ? 'Menyimpan...' : 'Simpan Dokumen' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
