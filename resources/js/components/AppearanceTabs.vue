<script setup lang="ts">
import { Check, Monitor, Moon, Sun } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    {
        value: 'light',
        Icon: Sun,
        label: 'Light',
        description: 'Latar terang dengan teks kontras.',
        preview: 'bg-white',
        previewHeader: 'bg-slate-100',
        previewBar: 'bg-tm-navy',
        previewLine: 'bg-slate-300',
    },
    {
        value: 'dark',
        Icon: Moon,
        label: 'Dark',
        description: 'Latar gelap untuk ruang rendah cahaya.',
        preview: 'bg-slate-950',
        previewHeader: 'bg-slate-800',
        previewBar: 'bg-emerald-400',
        previewLine: 'bg-slate-600',
    },
    {
        value: 'system',
        Icon: Monitor,
        label: 'System',
        description: 'Mengikuti pengaturan perangkat.',
        preview: 'bg-gradient-to-r from-white to-slate-950',
        previewHeader: 'bg-slate-200/80',
        previewBar: 'bg-sky-500',
        previewLine: 'bg-slate-400',
    },
] as const;
</script>

<template>
    <div class="grid gap-4 md:grid-cols-3">
        <button
            v-for="{
                value,
                Icon,
                label,
                description,
                preview,
                previewHeader,
                previewBar,
                previewLine,
            } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            :class="[
                'group flex min-h-44 flex-col justify-between rounded-[16px] border-[1.5px] p-4 text-left shadow-[2px_3px_0_0_rgba(0,0,0,0.10)] transition-all hover:-translate-y-0.5 hover:shadow-[3px_4px_0_0_rgba(0,0,0,0.14)] focus-visible:ring-2 focus-visible:ring-tm-green focus-visible:ring-offset-2 focus-visible:outline-none dark:focus-visible:ring-offset-[#111c2e]',
                appearance === value
                    ? 'border-black bg-tm-green-pale text-tm-navy dark:border-emerald-300/50 dark:bg-emerald-400/10 dark:text-slate-100'
                    : 'border-tm-border bg-white text-tm-navy hover:border-black hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950/25 dark:text-slate-100 dark:hover:border-slate-500 dark:hover:bg-slate-900/60',
            ]"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span
                        :class="[
                            'flex h-10 w-10 items-center justify-center rounded-xl border-[1.5px] shadow-[1px_2px_0_0_rgba(0,0,0,0.10)]',
                            appearance === value
                                ? 'border-black bg-white text-tm-green-dark dark:border-emerald-300/40 dark:bg-slate-950/50 dark:text-emerald-200'
                                : 'border-tm-border bg-tm-navy-pale text-tm-navy dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200',
                        ]"
                    >
                        <component :is="Icon" class="h-5 w-5" />
                    </span>
                    <span>
                        <span class="block text-sm font-extrabold">{{
                            label
                        }}</span>
                        <span
                            class="mt-0.5 block text-xs font-medium text-tm-text-secondary dark:text-slate-400"
                        >
                            {{ description }}
                        </span>
                    </span>
                </div>
                <span
                    v-if="appearance === value"
                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-tm-green text-white shadow-sm dark:bg-emerald-400 dark:text-slate-950"
                >
                    <Check class="h-4 w-4" />
                </span>
            </div>

            <div
                :class="[
                    'mt-5 overflow-hidden rounded-xl border border-black/10 shadow-inner dark:border-white/10',
                    preview,
                ]"
            >
                <div
                    :class="[
                        'flex h-8 items-center gap-1.5 px-3',
                        previewHeader,
                    ]"
                >
                    <span class="h-2 w-2 rounded-full bg-red-400"></span>
                    <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                </div>
                <div class="space-y-2 p-3">
                    <div :class="['h-3 w-2/3 rounded-full', previewBar]"></div>
                    <div
                        :class="['h-2 w-full rounded-full', previewLine]"
                    ></div>
                    <div :class="['h-2 w-4/5 rounded-full', previewLine]"></div>
                </div>
            </div>
        </button>
    </div>
</template>
