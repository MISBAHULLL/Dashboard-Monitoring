<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Timer, Save, CheckCircle, XCircle, Info } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { dashboard } from '@/routes';

interface SlaItem {
    id: number | null;
    category: string;
    max_days: number | null;
    warning_days: number | null;
}

const props = defineProps<{
    slaList: SlaItem[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Settings', href: '#' },
            { title: 'Konfigurasi SLA', href: '/settings/sla-config' },
        ],
    },
});

const page = usePage();
const flash = page.props.flash as any || {};

// Warna badge per kategori
const categoryClass: Record<string, string> = {
    'Fitur Berbayar': 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200',
    'Regulasi':       'bg-indigo-50 text-indigo-700 border-indigo-200',
    'Saran Fitur':    'bg-sky-50 text-sky-700 border-sky-200',
    'Prioritas':      'bg-rose-50 text-rose-700 border-rose-200',
};

const form = useForm({
    configs: props.slaList.map(s => ({
        category:     s.category,
        max_days:     s.max_days ?? null,
        warning_days: s.warning_days ?? null,
    })),
});

function submit() {
    form.post('/settings/sla-config', { preserveScroll: true });
}
</script>

<template>
    <Head title="Konfigurasi SLA" />

    <div class="flex flex-col space-y-6">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold flex items-center gap-3">
                    <Timer class="h-8 w-8 text-primary" />
                    Konfigurasi SLA
                </h1>
                <p class="text-base text-muted-foreground mt-2">
                    Atur batas waktu penyelesaian task berdasarkan kategori.
                </p>
            </div>
        </div>

        <!-- Flash messages -->
        <Alert v-if="flash?.success" class="border-green-200 bg-green-50 text-green-800">
            <CheckCircle class="h-4 w-4 text-green-600" />
            <AlertTitle class="text-green-800">Berhasil</AlertTitle>
            <AlertDescription class="text-green-700">{{ flash.success }}</AlertDescription>
        </Alert>

        <Alert v-if="form.errors.configs" variant="destructive">
            <XCircle class="h-4 w-4" />
            <AlertTitle>Validasi Gagal</AlertTitle>
            <AlertDescription>{{ form.errors.configs }}</AlertDescription>
        </Alert>

        <!-- Info Card -->
        <Alert class="border-blue-200 bg-blue-50">
            <Info class="h-4 w-4 text-blue-600" />
            <AlertDescription class="text-blue-700 text-sm">
                <strong>Max Days</strong> — batas maksimal hari penyelesaian task sejak dibuat. &nbsp;|&nbsp;
                <strong>Warning Days</strong> — berapa hari sebelum deadline sistem mulai memberi peringatan. Harus lebih kecil dari Max Days.
            </AlertDescription>
        </Alert>

        <!-- SLA Config Form -->
        <Card class="border-2">
            <CardHeader class="pb-4">
                <CardTitle class="text-xl">Batas Waktu per Kategori Task</CardTitle>
                <CardDescription>Konfigurasi akan langsung mempengaruhi perhitungan SLA Status di tabel task.</CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-4">

                    <!-- Header kolom -->
                    <div class="grid grid-cols-12 gap-4 px-4 pb-2 border-b border-border">
                        <div class="col-span-4 text-xs font-bold text-muted-foreground uppercase tracking-wide">Kategori</div>
                        <div class="col-span-3 text-xs font-bold text-muted-foreground uppercase tracking-wide">Max Days <span class="normal-case font-normal">(hari)</span></div>
                        <div class="col-span-3 text-xs font-bold text-muted-foreground uppercase tracking-wide">Warning Days <span class="normal-case font-normal">(hari)</span></div>
                        <div class="col-span-2 text-xs font-bold text-muted-foreground uppercase tracking-wide">Status</div>
                    </div>

                    <!-- Baris per kategori -->
                    <div v-for="(config, index) in form.configs" :key="config.category"
                        class="grid grid-cols-12 gap-4 items-center px-4 py-3 rounded-lg hover:bg-muted/30 transition-colors">

                        <!-- Kategori badge -->
                        <div class="col-span-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold border"
                                :class="categoryClass[config.category] ?? 'bg-slate-50 text-slate-700 border-slate-200'">
                                {{ config.category }}
                            </span>
                        </div>

                        <!-- Max Days -->
                        <div class="col-span-3">
                            <Input
                                type="number"
                                v-model.number="config.max_days"
                                min="1" max="365"
                                placeholder="cth: 30"
                                class="h-9"
                                :class="{ 'border-red-500': form.errors[`configs.${index}.max_days`] }"
                            />
                            <p v-if="form.errors[`configs.${index}.max_days`]" class="text-xs text-red-500 mt-1">
                                {{ form.errors[`configs.${index}.max_days`] }}
                            </p>
                        </div>

                        <!-- Warning Days -->
                        <div class="col-span-3">
                            <Input
                                type="number"
                                v-model.number="config.warning_days"
                                min="1"
                                placeholder="cth: 7"
                                class="h-9"
                                :class="{ 'border-red-500': form.errors[`configs.${index}.warning_days`] }"
                            />
                            <p v-if="form.errors[`configs.${index}.warning_days`]" class="text-xs text-red-500 mt-1">
                                {{ form.errors[`configs.${index}.warning_days`] }}
                            </p>
                        </div>

                        <!-- Status konfigurasi -->
                        <div class="col-span-2">
                            <span v-if="config.max_days && config.warning_days"
                                class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded-full">
                                <CheckCircle class="h-3 w-3" /> Aktif
                            </span>
                            <span v-else
                                class="inline-flex items-center gap-1 text-xs font-medium text-slate-500 bg-slate-50 border border-slate-200 px-2 py-1 rounded-full">
                                Belum diset
                            </span>
                        </div>
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="flex justify-end pt-4 border-t border-border">
                        <Button type="submit" :disabled="form.processing" class="gap-2 h-10 px-6">
                            <Save class="h-4 w-4" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Konfigurasi' }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>

    </div>
</template>
