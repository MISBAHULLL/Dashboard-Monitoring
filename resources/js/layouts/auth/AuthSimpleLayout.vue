<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ClipboardCheck, FileText, ShieldCheck } from 'lucide-vue-next';
import { home } from '@/routes';
import logoTextUrl from '@/assets/trustmedis-logo-text.png';

const trustPoints = [
    {
        title: 'Akses internal',
        description: 'Masuk aman untuk tim yang berwenang.',
        icon: ShieldCheck,
    },
    {
        title: 'Workflow terpantau',
        description: 'Prioritas task tetap mudah diikuti.',
        icon: ClipboardCheck,
    },
    {
        title: 'Dokumen tersentral',
        description: 'Referensi kerja tersimpan rapi.',
        icon: FileText,
    },
];

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh items-center justify-center bg-tm-page px-4 py-6 dark:bg-[#081422] sm:px-6 lg:px-8"
    >
        <div
            class="grid w-full max-w-5xl overflow-hidden rounded-[22px] border-2 border-black bg-white shadow-[6px_8px_0_0_rgba(17,37,72,0.16)] dark:border-slate-700/80 dark:bg-[#111c2e] dark:shadow-[0_24px_60px_rgba(0,0,0,0.48)] lg:grid-cols-[0.95fr_1.05fr]"
        >
            <section
                class="hidden min-h-[620px] flex-col justify-between bg-[#0E2A52] p-8 text-white lg:flex"
            >
                <div>
                    <Link
                        :href="home()"
                        class="inline-flex rounded-xl bg-white px-5 py-4 shadow-[3px_4px_0_0_rgba(0,0,0,0.35)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-tm-green"
                    >
                        <img
                            :src="logoTextUrl"
                            alt="Trustmedis"
                            class="h-14 w-auto object-contain"
                        />
                    </Link>

                    <div class="mt-12 space-y-4">
                        <p class="text-sm font-semibold text-emerald-200">
                            Monitoring Task PO
                        </p>
                        <h2 class="max-w-sm text-3xl font-extrabold leading-tight">
                            Workspace operasional Trustmedis
                        </h2>
                    </div>
                </div>

                <div class="grid gap-3">
                    <div
                        v-for="item in trustPoints"
                        :key="item.title"
                        class="flex items-start gap-3 rounded-2xl border-2 border-white/15 bg-white/10 p-4"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-tm-green/20 text-emerald-200"
                        >
                            <component
                                :is="item.icon"
                                class="h-5 w-5"
                                :stroke-width="2"
                                aria-hidden="true"
                            />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-white">
                                {{ item.title }}
                            </p>
                            <p class="mt-1 text-xs leading-5 text-slate-300">
                                {{ item.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <div
                class="flex min-h-[620px] items-center justify-center px-5 py-8 sm:px-8 lg:px-12"
            >
                <div class="w-full max-w-[420px]">
                    <div class="mb-8 flex flex-col items-center gap-4 text-center">
                        <Link
                            :href="home()"
                            class="rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-tm-green"
                        >
                            <img
                                :src="logoTextUrl"
                                alt="Trustmedis"
                                class="h-16 w-auto object-contain"
                            />
                            <span class="sr-only">{{ title }}</span>
                        </Link>

                        <div class="space-y-2">
                            <h1 class="text-2xl font-extrabold text-tm-navy dark:text-white">
                                {{ title }}
                            </h1>
                            <p class="text-sm leading-6 text-tm-text-secondary dark:text-slate-300">
                                {{ description }}
                            </p>
                        </div>
                    </div>

                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
