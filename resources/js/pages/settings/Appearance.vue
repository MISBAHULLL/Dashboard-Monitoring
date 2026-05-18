<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Palette } from 'lucide-vue-next';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import { edit } from '@/routes/appearance';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Appearance settings',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Appearance settings" />

    <h1 class="sr-only">Appearance settings</h1>

    <div class="space-y-6">
        <section
            class="overflow-hidden rounded-[18px] border-2 border-black bg-white shadow-[3px_5px_0_0_rgba(0,0,0,0.18)] dark:border-slate-700 dark:bg-[#111c2e] dark:shadow-[0_18px_44px_rgba(0,0,0,0.42)]"
        >
            <div
                class="flex flex-col gap-4 border-b-2 border-black bg-tm-navy-pale px-5 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700 dark:bg-slate-800/70"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border-2 border-black bg-white text-tm-navy shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] dark:border-slate-600 dark:bg-slate-950/40 dark:text-slate-100"
                    >
                        <Palette class="h-6 w-6" />
                    </div>
                    <div>
                        <h2
                            class="text-xl font-extrabold text-tm-navy dark:text-slate-100"
                        >
                            Tampilan Aplikasi
                        </h2>
                        <p
                            class="mt-1 text-sm text-tm-text-secondary dark:text-slate-400"
                        >
                            Atur mode warna yang paling nyaman untuk digunakan.
                        </p>
                    </div>
                </div>
                <div
                    class="rounded-xl border border-tm-navy/15 bg-white px-3 py-2 text-xs font-bold text-tm-navy shadow-sm dark:border-slate-600 dark:bg-slate-950/40 dark:text-slate-200"
                >
                    Preferensi tersimpan otomatis
                </div>
            </div>

            <div class="space-y-5 p-5">
                <div>
                    <h3
                        class="text-base font-extrabold text-tm-navy dark:text-slate-100"
                    >
                        Mode Warna
                    </h3>
                    <p
                        class="mt-1 text-sm text-tm-text-secondary dark:text-slate-400"
                    >
                        Pilih tema terang, gelap, atau ikuti pengaturan
                        perangkat.
                    </p>
                </div>

                <AppearanceTabs />
            </div>
        </section>
    </div>
</template>
