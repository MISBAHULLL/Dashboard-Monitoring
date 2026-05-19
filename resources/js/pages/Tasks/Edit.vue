<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index as tasksIndex, update as updateTask } from '@/routes/tasks';

// Menerima data dari TaskController@edit
const props = defineProps<{
    task: any; // Data task lama yang akan diedit
    clients: Array<{ id: number; name: string }>;
    product_teams: Array<{ id: number; name: string }>;
    engineer_teams: Array<{ id: number; name: string }>;
    users: Array<{ id: number; name: string }>;
    existing_modules: Array<string>;
}>();

// Inertia Form State (Otomatis terisi data lama)
// Simpan tanggal awal untuk deteksi perubahan
const originalReleaseDate = props.task.release_date ? props.task.release_date.split('T')[0] : '';

const form = useForm({
    title: props.task.title || '',
    client_id: props.task.client_id || '',
    product_id: props.task.product_id || '',
    engineer_id: props.task.engineer_id || '',
    assigned_to: props.task.assigned_to || '',
    description: props.task.description || '',
    modul: props.task.modul || '',
    task_url: props.task.task_url || '',
    category: props.task.category || 'Saran Fitur',
    priority: props.task.priority || 'medium',
    status: props.task.status || 'open',
    release_date: originalReleaseDate,
    release_reason: '',
});

// Tampilkan textarea alasan hanya jika tanggal berubah
const releaseDateChanged = computed(() => {
    return originalReleaseDate !== '' && form.release_date !== originalReleaseDate;
});

const submitForm = () => {
    // Karena ini halaman Edit, kita gunakan metode PUT ke URL spesifik task tersebut
    form.put(updateTask.url(props.task.id));
};
</script>

<template>
    <Head title="Edit Tiket Task" />

    <div class="task-edit-page flex h-full flex-1 flex-col gap-6 overflow-y-auto rounded-xl bg-tm-page p-4 md:p-8 dark:bg-[#081422]">
        
        <!-- Header -->
        <div class="flex items-center gap-4">
            <Link :href="tasksIndex.url()">
                <Button variant="outline" size="icon" class="h-10 w-10 shrink-0 rounded-full border-[1.5px] border-black bg-white shadow-[2px_2px_0_0_rgba(0,0,0,0.1)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_3px_0_0_rgba(0,0,0,0.15)] dark:border-slate-600 dark:bg-[#111c2e] dark:shadow-[0_10px_24px_rgba(0,0,0,0.35)] dark:hover:bg-slate-800/70">
                    <ArrowLeft class="h-5 w-5 text-tm-navy dark:text-slate-100" />
                </Button>
            </Link>
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-tm-navy dark:text-slate-100">Edit Tiket: {{ task.title }}</h1>
                <p class="mt-1 text-sm text-tm-text-secondary dark:text-slate-400">Perbarui rincian, status, atau pindah tugaskan tiket ini.</p>
            </div>
        </div>

        <form @submit.prevent="submitForm">
            <div class="task-form-card rounded-[18px] border-[2.5px] border-black bg-white p-6 shadow-[2px_4px_4px_0_rgba(11,42,107,0.15)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[3px_6px_12px_0_rgba(11,42,107,0.2)] dark:border-slate-700/80 dark:bg-[#111c2e] dark:shadow-[0_14px_32px_rgba(0,0,0,0.44),0_0_0_1px_rgba(148,163,184,0.08)] dark:hover:shadow-[0_18px_40px_rgba(0,0,0,0.52),0_0_0_1px_rgba(122,162,247,0.2)] md:p-8">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                
                <!-- KOLOM KIRI (Informasi Utama) -->
                <div class="space-y-6">
                    <div class="space-y-2">
                        <Label for="title" class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary">Judul Task <span class="text-red-500">*</span></Label>
                        <Input id="title" v-model="form.title" placeholder="Contoh: Penambahan tombol Export Excel..." class="h-10 rounded-lg border-[1.5px] border-black/70 bg-white text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] focus:border-tm-navy focus:ring-1 focus:ring-tm-navy" :class="{ '!border-red-500': form.errors.title }" />
                        <p v-if="form.errors.title" class="ml-1 text-xs text-red-500">{{ form.errors.title }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="description" class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary">Deskripsi Detail</Label>
                        <Textarea id="description" v-model="form.description" rows="5" placeholder="Jelaskan kebutuhan faskes secara rinci..." class="resize-none rounded-lg border-[1.5px] border-black/70 bg-white text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] focus:border-tm-navy focus:ring-1 focus:ring-tm-navy" :class="{ '!border-red-500': form.errors.description }" />
                        <p v-if="form.errors.description" class="ml-1 text-xs text-red-500">{{ form.errors.description }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="modul" class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary">Modul / Menu Terkait</Label>
                            <Input id="modul" list="modul-options" v-model="form.modul" placeholder="Ketik atau pilih modul..." class="h-10 rounded-lg border-[1.5px] border-black/70 bg-white text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] focus:border-tm-navy focus:ring-1 focus:ring-tm-navy" :class="{ '!border-red-500': form.errors.modul }" />
                            <datalist id="modul-options">
                                <option v-for="m in existing_modules" :key="m" :value="m"></option>
                            </datalist>
                            <p v-if="form.errors.modul" class="ml-1 text-xs text-red-500">{{ form.errors.modul }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="task_url" class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary">Link Referensi / Docs</Label>
                            <Input id="task_url" v-model="form.task_url" placeholder="https://..." class="h-10 rounded-lg border-[1.5px] border-black/70 bg-white text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] focus:border-tm-navy focus:ring-1 focus:ring-tm-navy" :class="{ '!border-red-500': form.errors.task_url }" />
                            <p v-if="form.errors.task_url" class="ml-1 text-xs text-red-500">{{ form.errors.task_url }}</p>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN (Pengaturan Divisi & Atribut) -->
                <div class="settings-panel space-y-6 rounded-xl border-[1.5px] border-black/30 bg-tm-navy-pale/30 p-5 dark:border-slate-700 dark:bg-slate-950/20">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="client_id" class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary">Faskes (Client) <span class="text-red-500">*</span></Label>
                            <select id="client_id" v-model="form.client_id" class="flex h-10 w-full cursor-pointer rounded-lg border-[1.5px] border-black/70 bg-white px-3 py-2 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] focus:border-tm-navy focus:outline-none focus:ring-1 focus:ring-tm-navy">
                                <option value="" disabled>-- Pilih Faskes --</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                            </select>
                            <p v-if="form.errors.client_id" class="ml-1 text-xs text-red-500">{{ form.errors.client_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="category" class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary">Kategori Task</Label>
                            <select id="category" v-model="form.category" class="flex h-10 w-full cursor-pointer rounded-lg border-[1.5px] border-black/70 bg-white px-3 py-2 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] focus:border-tm-navy focus:outline-none focus:ring-1 focus:ring-tm-navy">
                                <option value="Fitur Berbayar">Fitur Berbayar</option>
                                <option value="Regulasi">Regulasi</option>
                                <option value="Saran Fitur">Saran Fitur</option>
                                <option value="Prioritas">Prioritas</option>
                            </select>
                            <p v-if="form.errors.category" class="ml-1 text-xs text-red-500">{{ form.errors.category }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="product_id" class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary">Divisi Produk <span class="text-red-500">*</span></Label>
                            <select id="product_id" v-model="form.product_id" class="flex h-10 w-full cursor-pointer rounded-lg border-[1.5px] border-black/70 bg-white px-3 py-2 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] focus:border-tm-navy focus:outline-none focus:ring-1 focus:ring-tm-navy">
                                <option value="" disabled>-- Pilih Tim Produk --</option>
                                <option v-for="team in product_teams" :key="team.id" :value="team.id">{{ team.name }}</option>
                            </select>
                            <p v-if="form.errors.product_id" class="ml-1 text-xs text-red-500">{{ form.errors.product_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="engineer_id" class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary">Divisi Engineer</Label>
                            <select id="engineer_id" v-model="form.engineer_id" class="flex h-10 w-full cursor-pointer rounded-lg border-[1.5px] border-black/70 bg-white px-3 py-2 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] focus:border-tm-navy focus:outline-none focus:ring-1 focus:ring-tm-navy">
                                <option value="">(Bisa menyusul)</option>
                                <option v-for="team in engineer_teams" :key="team.id" :value="team.id">{{ team.name }}</option>
                            </select>
                            <p v-if="form.errors.engineer_id" class="ml-1 text-xs text-red-500">{{ form.errors.engineer_id }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="priority" class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary">Tingkat Prioritas</Label>
                            <select id="priority" v-model="form.priority" class="flex h-10 w-full cursor-pointer rounded-lg border-[1.5px] border-black/70 bg-white px-3 py-2 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] focus:border-tm-navy focus:outline-none focus:ring-1 focus:ring-tm-navy">
                                <option value="urgent">Urgent</option>
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                            <p v-if="form.errors.priority" class="ml-1 text-xs text-red-500">{{ form.errors.priority }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="status" class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary">Status Pengerjaan</Label>
                            <select id="status" v-model="form.status" class="flex h-10 w-full cursor-pointer rounded-lg border-[1.5px] border-black/70 bg-white px-3 py-2 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] focus:border-tm-navy focus:outline-none focus:ring-1 focus:ring-tm-navy">
                                <option value="open">Open (Baru)</option>
                                <option value="in_progress">In Progress</option>
                                <option value="revision">Revisi</option>
                                <option value="completed">Selesai (Completed)</option>
                            </select>
                            <p v-if="form.errors.status" class="ml-1 text-xs text-red-500">{{ form.errors.status }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="release_date" class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary">Tanggal Release (Target)</Label>
                            <Input id="release_date" type="date" v-model="form.release_date" class="h-10 cursor-pointer rounded-lg border-[1.5px] border-black/70 bg-white text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] focus:border-tm-navy focus:ring-1 focus:ring-tm-navy" />
                            <p class="text-[10px] font-medium leading-4 text-slate-500 dark:text-slate-400">
                                Opsional. Jika dikosongkan, deadline efektif mengikuti SLA kategori.
                            </p>
                            <p v-if="form.errors.release_date" class="ml-1 text-xs text-red-500">{{ form.errors.release_date }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="assigned_to" class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary">Assign Ke PIC (Person in Charge)</Label>
                            <select id="assigned_to" v-model="form.assigned_to" class="flex h-10 w-full cursor-pointer rounded-lg border-[1.5px] border-black/70 bg-white px-3 py-2 text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-px hover:bg-slate-50 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.08)] focus:border-tm-navy focus:outline-none focus:ring-1 focus:ring-tm-navy">
                                <option value="">(Belum ditentukan)</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </select>
                            <p v-if="form.errors.assigned_to" class="ml-1 text-xs text-red-500">{{ form.errors.assigned_to }}</p>
                        </div>
                    </div>

                     <!-- Alasan perubahan tanggal (muncul hanya jika tanggal berubah) -->
                    <div v-if="releaseDateChanged" class="space-y-2 rounded-xl border-[1.5px] border-amber-400 bg-amber-50 p-4 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] dark:border-amber-300/35 dark:bg-amber-400/10">
                        <Label for="release_reason" class="font-bold text-amber-800 dark:text-amber-200">
                            Alasan Perubahan Tanggal Release
                        </Label>
                        <p class="text-xs text-amber-700 dark:text-amber-200">
                            Tanggal release berubah dari {{ originalReleaseDate }} ke {{ form.release_date }}. Mohon berikan alasan.
                        </p>
                        <Textarea
                            id="release_reason"
                            v-model="form.release_reason"
                            rows="3"
                            placeholder="Contoh: Permintaan faskes untuk menunda deployment..."
                            class="resize-none rounded-lg border-[1.5px] border-amber-400 bg-white text-sm text-slate-700 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                        />
                        <p v-if="form.errors.release_reason" class="ml-1 text-xs text-red-500">{{ form.errors.release_reason }}</p>
                    </div>

                </div>
            </div>

            <div class="mt-6 flex justify-center gap-4 pt-2">
                <Link :href="tasksIndex.url()">
                    <Button type="button" variant="outline" class="h-11 rounded-xl border-[1.5px] border-black/70 px-8 font-medium text-slate-600 shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] transition-all hover:-translate-y-0.5 hover:shadow-[2px_3px_0_0_rgba(0,0,0,0.1)] dark:border-slate-600 dark:bg-slate-950/25 dark:text-slate-200 dark:hover:bg-slate-800">Batal</Button>
                </Link>
                <Button type="submit" :disabled="form.processing" class="flex h-11 items-center gap-2 rounded-xl border-[1.5px] border-black bg-tm-navy px-8 font-medium tracking-wide text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.2)] transition-all hover:-translate-y-0.5 hover:bg-tm-navy-medium hover:shadow-[3px_3px_0_0_rgba(0,0,0,0.25)] dark:border-sky-300/35 dark:shadow-[0_12px_28px_rgba(59,130,246,0.18)]">
                    <Save class="h-4 w-4" />
                    {{ form.processing ? 'Memproses Update...' : 'Simpan Perubahan' }}
                </Button>
            </div>
            </div>
        </form>
    </div>
</template>

<style scoped>
:global(.dark) .task-edit-page :is(label, .text-tm-text-secondary) {
    color: rgb(148 163 184);
}

:global(.dark) .task-edit-page :is(input, select, textarea) {
    border-color: rgb(71 85 105);
    background-color: rgb(2 6 23 / 0.28);
    color: rgb(241 245 249);
    box-shadow:
        0 0 0 1px rgb(148 163 184 / 0.04) inset,
        0 8px 18px rgb(0 0 0 / 0.18);
}

:global(.dark) .task-edit-page :is(input, select, textarea):hover {
    background-color: rgb(30 41 59 / 0.62);
}

:global(.dark) .task-edit-page :is(input, textarea)::placeholder {
    color: rgb(100 116 139);
}

:global(.dark) .task-edit-page input[type='date'] {
    color-scheme: dark;
}

:global(.dark) .task-edit-page select option {
    background-color: #111c2e;
    color: rgb(241 245 249);
}
</style>
