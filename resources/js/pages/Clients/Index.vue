<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Building2, Plus, Edit, Trash2, Search, ChevronLeft, ChevronRight, Eye, FilePlus, FileText, RotateCcw } from 'lucide-vue-next';
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
        deleted_at?: string | null;
    }>;
    documentTypes: string[];
    activeTrashed?: boolean;
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

const restoreClient = (id: number, name: string) => {
    if (confirm(`Pulihkan Faskes "${name}"?`)) {
        router.patch(`/clients/${id}/restore`);
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

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-8">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-tm-navy flex items-center gap-3 dark:text-foreground">
                    <div class="flex h-10 w-10 items-center justify-center rounded-[12px] border-2 border-black bg-tm-navy-pale shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] dark:bg-tm-navy dark:border-border">
                        <Building2 class="h-5 w-5 text-tm-navy dark:text-white" />
                    </div>
                    Master Faskes (Client)
                </h1>
                <p class="text-sm text-tm-text-secondary mt-1.5 ml-[52px] dark:text-muted-foreground">Kelola data Fasilitas Kesehatan yang menjadi pelanggan Anda.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button
                    @click="router.visit(activeTrashed ? '/clients' : '/clients?trashed=only')"
                    class="inline-flex items-center gap-2 rounded-[10px] border-2 border-black bg-white px-4 py-2.5 text-sm font-bold text-tm-navy shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:bg-tm-navy-pale hover:shadow-[3px_4px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:bg-card dark:text-foreground dark:shadow-none"
                >
                    <RotateCcw class="h-4 w-4" /> {{ activeTrashed ? 'Lihat Aktif' : 'Lihat Terhapus' }}
                </button>
                <button
                    v-if="!activeTrashed"
                    @click="openAddModal"
                    class="inline-flex items-center gap-2 rounded-[10px] border-2 border-black bg-tm-green px-4 py-2.5 text-sm font-bold text-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_4px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] dark:border-border dark:shadow-none"
                >
                    <Plus class="h-4 w-4" /> Tambah Faskes
                </button>
            </div>
        </div>
        <!-- Filter Bar -->
        <div class="rounded-[14px] border-2 border-black bg-white p-4 shadow-[2px_3px_0px_0px_rgba(0,0,0,0.8)] dark:bg-card dark:border-border dark:shadow-none">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Filter Kota -->
                <Select v-model="filterCity" @update:modelValue="applyFilter">
                    <SelectTrigger class="h-9 w-[140px] text-sm rounded-[8px] border-2 border-tm-border font-medium dark:border-border">
                        <SelectValue placeholder="Semua Kota" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua Kota</SelectItem>
                        <SelectItem v-for="city in uniqueCities" :key="city" :value="city">{{ city }}</SelectItem>
                    </SelectContent>
                </Select>

                <!-- Filter Tipe -->
                <Select v-model="filterType" @update:modelValue="applyFilter">
                    <SelectTrigger class="h-9 w-[140px] text-sm rounded-[8px] border-2 border-tm-border font-medium dark:border-border">
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
                    <SelectTrigger class="h-9 w-[140px] text-sm rounded-[8px] border-2 border-tm-border font-medium dark:border-border">
                        <SelectValue placeholder="Semua PIC" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua PIC</SelectItem>
                        <SelectItem v-for="pic in uniquePics" :key="pic" :value="pic">{{ pic }}</SelectItem>
                    </SelectContent>
                </Select>

                <!-- Filter Status -->
                <Select v-model="filterStatus" @update:modelValue="applyFilter">
                    <SelectTrigger class="h-9 w-[140px] text-sm rounded-[8px] border-2 border-tm-border font-medium dark:border-border">
                        <SelectValue placeholder="Semua Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua Status</SelectItem>
                        <SelectItem value="aktif">Aktif</SelectItem>
                        <SelectItem value="nonaktif">Nonaktif</SelectItem>
                    </SelectContent>
                </Select>

                <!-- Reset Filter -->
                <Button v-if="filterCity !== 'all' || filterType !== 'all' || filterPic !== 'all' || filterStatus !== 'all'" variant="ghost" size="sm" @click="resetFilters" class="h-9 text-xs font-semibold text-tm-text-secondary hover:text-tm-navy dark:text-muted-foreground dark:hover:text-foreground">
                    Reset Filter
                </Button>
            </div>

            <!-- Info hasil filter -->
            <p class="text-xs text-tm-text-secondary mt-2.5 dark:text-muted-foreground">
                Menampilkan <span class="font-bold text-tm-navy dark:text-foreground">{{ filteredClients.length }}</span> dari <span class="font-bold text-tm-navy dark:text-foreground">{{ clients.length }}</span> data
            </p>
        </div>

        <!-- Tabel Data -->
        <div class="rounded-[14px] border-2 border-black bg-white shadow-[2px_3px_0px_0px_rgba(0,0,0,0.8)] overflow-hidden dark:bg-card dark:border-border dark:shadow-none">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b-2 border-black bg-tm-navy-pale dark:bg-secondary dark:border-border">
                            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">No</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Nama Faskes</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Kota</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Tipe</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">PIC</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Telp PIC</th>
                            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Tasks</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Dokumen</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Status</th>
                            <th class="px-4 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-tm-navy dark:text-foreground">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(client, index) in paginatedClients" :key="client.id" class="border-b border-tm-border transition-colors hover:bg-tm-navy-pale/40 dark:border-border dark:hover:bg-secondary/50">
                            <td class="px-4 py-3.5 text-tm-text-secondary dark:text-muted-foreground">{{ (currentPage - 1) * perPage + index + 1 }}</td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-tm-navy dark:text-foreground">{{ client.name }}</div>
                                <div class="text-xs text-tm-text-muted mt-0.5 dark:text-muted-foreground">{{ client.address || '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5 text-tm-text dark:text-foreground">{{ client.city || '-' }}</td>
                            <td class="px-4 py-3.5">
                                <span v-if="client.type === 'PRATAMA'" class="inline-flex items-center rounded-[6px] border-2 border-tm-green bg-tm-green-pale px-2 py-0.5 text-xs font-bold text-tm-green-dark dark:bg-tm-green/20 dark:border-tm-green/50 dark:text-tm-green">PRATAMA</span>
                                <span v-else-if="client.type" class="inline-flex items-center justify-center h-7 w-7 rounded-full border-2 border-tm-navy bg-tm-navy-pale text-xs font-bold text-tm-navy dark:bg-tm-navy/20 dark:border-tm-navy-medium dark:text-tm-navy-medium">{{ client.type }}</span>
                                <span v-else class="text-tm-text-muted dark:text-muted-foreground">-</span>
                            </td>
                            <td class="px-4 py-3.5 text-tm-text dark:text-foreground">{{ client.pic_name || '-' }}</td>
                            <td class="px-4 py-3.5 text-tm-text dark:text-foreground">{{ client.pic_phone || '-' }}</td>
                            <td class="px-4 py-3.5 text-center font-semibold text-tm-navy dark:text-foreground">{{ client.tasks_count }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm font-semibold text-tm-navy dark:text-foreground">{{ client.documents_count }}</span>
                                    <button v-if="!client.deleted_at" @click="router.visit(`/documents?client_id=${client.id}`)" title="Lihat Dokumen" class="inline-flex h-6 w-6 items-center justify-center rounded-[6px] text-tm-navy-medium transition-colors hover:bg-tm-navy-pale dark:text-foreground dark:hover:bg-secondary">
                                        <Eye class="h-3.5 w-3.5" />
                                    </button>
                                    <button v-if="!client.deleted_at" @click="openDocModal(client)" title="Tambah Dokumen" class="inline-flex h-6 w-6 items-center justify-center rounded-[6px] text-tm-green transition-colors hover:bg-tm-green-pale dark:hover:bg-tm-green/10">
                                        <FilePlus class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span v-if="client.is_active" class="inline-flex items-center gap-1.5 rounded-[6px] border border-tm-green bg-tm-green-pale px-2 py-0.5 text-xs font-bold text-tm-green-dark dark:bg-tm-green/20 dark:border-tm-green/50 dark:text-tm-green">
                                    <span class="h-1.5 w-1.5 rounded-full bg-tm-green"></span>
                                    Aktif
                                </span>
                                <span v-else class="inline-flex items-center gap-1.5 rounded-[6px] border border-tm-danger bg-tm-danger-pale px-2 py-0.5 text-xs font-bold text-tm-danger dark:bg-tm-danger/20 dark:border-tm-danger/50">
                                    <span class="h-1.5 w-1.5 rounded-full bg-tm-danger"></span>
                                    Nonaktif
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button v-if="client.deleted_at" @click="restoreClient(client.id, client.name)" title="Pulihkan" class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] border border-tm-border text-tm-green transition-all hover:border-tm-green hover:bg-tm-green-pale dark:border-border dark:hover:bg-tm-green/10">
                                        <RotateCcw class="h-4 w-4" />
                                    </button>
                                    <button v-if="!client.deleted_at" @click="openEditModal(client)" title="Edit" class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] border border-tm-border text-tm-navy-medium transition-all hover:border-tm-navy hover:bg-tm-navy-pale dark:border-border dark:text-foreground dark:hover:bg-secondary">
                                        <Edit class="h-4 w-4" />
                                    </button>
                                    <button v-if="!client.deleted_at" @click="deleteClient(client.id, client.name)" title="Hapus" class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] border border-tm-border text-tm-danger transition-all hover:border-tm-danger hover:bg-tm-danger-pale dark:border-border dark:hover:bg-tm-danger/10">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredClients.length === 0">
                            <td colspan="10" class="py-12 text-center">
                                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-[12px] border-2 border-black bg-tm-navy-pale shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] dark:bg-secondary dark:border-border">
                                    <Search class="h-5 w-5 text-tm-navy dark:text-muted-foreground" />
                                </div>
                                <p class="text-sm font-semibold text-tm-text-secondary dark:text-muted-foreground">{{ clients.length === 0 ? 'Belum ada data Faskes.' : 'Tidak ada data yang sesuai filter.' }}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="flex items-center justify-between border-t-2 border-black px-4 py-3 dark:border-border">
                <p class="text-xs font-medium text-tm-text-secondary dark:text-muted-foreground">
                    Halaman {{ currentPage }} dari {{ totalPages }}
                </p>
                <div class="flex items-center gap-1">
                    <button :disabled="currentPage === 1" @click="currentPage--" class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] border-2 border-tm-border text-tm-navy transition-colors hover:bg-tm-navy-pale disabled:opacity-40 disabled:cursor-not-allowed dark:border-border dark:text-foreground dark:hover:bg-secondary">
                        <ChevronLeft class="h-4 w-4" />
                    </button>
                    <template v-for="page in totalPages" :key="page">
                        <button
                            v-if="page === 1 || page === totalPages || Math.abs(page - currentPage) <= 1"
                            @click="currentPage = page"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] border-2 text-xs font-bold transition-colors"
                            :class="page === currentPage
                                ? 'border-black bg-tm-green text-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] dark:border-tm-green dark:shadow-none'
                                : 'border-tm-border text-tm-navy hover:bg-tm-navy-pale dark:border-border dark:text-foreground dark:hover:bg-secondary'"
                        >
                            {{ page }}
                        </button>
                        <span v-else-if="page === currentPage - 2 || page === currentPage + 2" class="px-1 text-tm-text-muted text-sm">…</span>
                    </template>
                    <button :disabled="currentPage === totalPages" @click="currentPage++" class="inline-flex h-8 w-8 items-center justify-center rounded-[8px] border-2 border-tm-border text-tm-navy transition-colors hover:bg-tm-navy-pale disabled:opacity-40 disabled:cursor-not-allowed dark:border-border dark:text-foreground dark:hover:bg-secondary">
                        <ChevronRight class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL / DIALOG (Akan muncul jika isModalOpen = true) -->
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-[560px]">
                <DialogHeader>
                    <DialogTitle class="text-tm-navy dark:text-foreground">{{ isEditing ? 'Edit Faskes' : 'Tambah Faskes Baru' }}</DialogTitle>
                    <DialogDescription>
                        Pastikan nama dan kontak Faskes sudah diisi dengan benar.
                    </DialogDescription>
                </DialogHeader>
                
                <form @submit.prevent="submitForm" class="space-y-4 py-4">
                    <!-- Input Nama -->
                    <div class="space-y-2">
                        <Label for="name">Nama Faskes <span class="text-tm-danger">*</span></Label>
                        <Input id="name" v-model="form.name" placeholder="Contoh: RSUD Dr. Soetomo" :class="{ 'border-tm-danger': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-sm text-tm-danger">{{ form.errors.name }}</p>
                    </div>

                    <!-- Input Kota & Tipe (2 kolom) -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="city">Kota</Label>
                            <Input id="city" v-model="form.city" placeholder="Contoh: Surabaya" :class="{ 'border-tm-danger': form.errors.city }" />
                            <p v-if="form.errors.city" class="text-sm text-tm-danger">{{ form.errors.city }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label>Tipe Faskes</Label>
                            <Select v-model="form.type">
                                <SelectTrigger :class="{ 'border-tm-danger': form.errors.type }">
                                    <SelectValue placeholder="Pilih tipe" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="PRATAMA">PRATAMA</SelectItem>
                                    <SelectItem value="A">Tipe A</SelectItem>
                                    <SelectItem value="B">Tipe B</SelectItem>
                                    <SelectItem value="C">Tipe C</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.type" class="text-sm text-tm-danger">{{ form.errors.type }}</p>
                        </div>
                    </div>

                    <!-- Input PIC -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="pic_name">Nama PIC</Label>
                            <Input id="pic_name" v-model="form.pic_name" placeholder="Nama penanggung jawab" :class="{ 'border-tm-danger': form.errors.pic_name }" />
                            <p v-if="form.errors.pic_name" class="text-sm text-tm-danger">{{ form.errors.pic_name }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="pic_phone">Telepon PIC</Label>
                            <Input id="pic_phone" v-model="form.pic_phone" placeholder="Contoh: 08123456789" :class="{ 'border-tm-danger': form.errors.pic_phone }" />
                            <p v-if="form.errors.pic_phone" class="text-sm text-tm-danger">{{ form.errors.pic_phone }}</p>
                        </div>
                    </div>

                    <!-- Input Alamat -->
                    <div class="space-y-2">
                        <Label for="address">Alamat Lengkap</Label>
                        <Textarea id="address" v-model="form.address" placeholder="Masukkan alamat lengkap" class="resize-none" :class="{ 'border-tm-danger': form.errors.address }" />
                        <p v-if="form.errors.address" class="text-sm text-tm-danger">{{ form.errors.address }}</p>
                    </div>

                    <!-- Toggle Status Aktif -->
                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="is_active" v-model="form.is_active" class="h-4 w-4 rounded border-gray-300 text-tm-green focus:ring-tm-green" />
                        <Label for="is_active" class="cursor-pointer">Faskes Aktif</Label>
                    </div>

                    <DialogFooter class="pt-4">
                        <Button type="button" variant="outline" @click="isModalOpen = false">Batal</Button>
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-[10px] border-2 border-black bg-tm-green px-4 py-2 text-sm font-bold text-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 dark:border-border dark:shadow-none">
                            {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Simpan Faskes') }}
                        </button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- MODAL TAMBAH DOKUMEN -->
        <Dialog :open="isDocModalOpen" @update:open="isDocModalOpen = $event">
            <DialogContent class="sm:max-w-[480px]">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-tm-navy dark:text-foreground">
                        <FileText class="h-5 w-5 text-tm-navy-medium" />
                        Tambah Dokumen
                    </DialogTitle>
                    <DialogDescription>
                        Faskes: <span class="font-bold text-tm-navy dark:text-foreground">{{ selectedClientName }}</span>
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitDoc" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="doc_title">Judul Dokumen <span class="text-tm-danger">*</span></Label>
                        <Input id="doc_title" v-model="docForm.title" placeholder="Contoh: Kontrak Kerjasama 2025" :class="{ 'border-tm-danger': docForm.errors.title }" />
                        <p v-if="docForm.errors.title" class="text-sm text-tm-danger">{{ docForm.errors.title }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="doc_type">Tipe Dokumen <span class="text-tm-danger">*</span></Label>
                        <input id="doc_type" v-model="docForm.type" type="text" required list="client-doc-type-list"
                            placeholder="Pilih atau ketik tipe baru..."
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs focus:outline-none focus:ring-2 focus:ring-ring"
                            :class="{ 'border-tm-danger': docForm.errors.type }" />
                        <datalist id="client-doc-type-list">
                            <option v-for="t in documentTypes" :key="t" :value="t" />
                        </datalist>
                        <p v-if="docForm.errors.type" class="text-sm text-tm-danger">{{ docForm.errors.type }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label>File Dokumen</Label>
                        <input type="file" @change="handleFileChange"
                            class="block w-full text-sm text-muted-foreground file:mr-4 file:rounded-md file:border-0 file:bg-muted file:px-3 file:py-1.5 file:text-sm file:font-medium hover:file:bg-muted/80" />
                        <p v-if="docForm.errors.file" class="text-sm text-tm-danger">{{ docForm.errors.file }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="doc_notes">Catatan Versi</Label>
                        <Input id="doc_notes" v-model="docForm.notes" placeholder="Contoh: Versi awal" />
                    </div>

                    <DialogFooter class="pt-2">
                        <Button type="button" variant="outline" @click="isDocModalOpen = false">Batal</Button>
                        <button type="submit" :disabled="docForm.processing" class="inline-flex items-center gap-2 rounded-[10px] border-2 border-black bg-tm-navy px-4 py-2 text-sm font-bold text-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,0.8)] active:translate-y-0 active:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.8)] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 dark:border-border dark:shadow-none">
                            {{ docForm.processing ? 'Menyimpan...' : 'Simpan Dokumen' }}
                        </button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
