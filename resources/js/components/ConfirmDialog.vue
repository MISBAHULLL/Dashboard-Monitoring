<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogClose } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';
import { AlertTriangle, RotateCcw, Trash2 } from 'lucide-vue-next';

interface Props {
    open: boolean;
    title?: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: 'danger' | 'warning' | 'success' | 'default';
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Konfirmasi',
    description: 'Apakah Anda yakin ingin melanjutkan?',
    confirmLabel: 'Hapus',
    cancelLabel: 'Batal',
    variant: 'danger',
    loading: false,
});

const emit = defineEmits<{
    (e: 'confirm'): void;
    (e: 'cancel'): void;
    (e: 'update:open', value: boolean): void;
}>();

function handleCancel() {
    emit('cancel');
    emit('update:open', false);
}

function handleConfirm() {
    emit('confirm');
}

const icon = computed(() => {
    if (props.variant === 'success') return RotateCcw;
    if (props.variant === 'danger') return Trash2;
    return AlertTriangle;
});
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="overflow-hidden rounded-[18px] border-2 border-black bg-white p-0 shadow-[4px_6px_0_0_rgba(0,0,0,0.22)] sm:max-w-[460px] dark:border-slate-700 dark:bg-[#111c2e] dark:shadow-[0_24px_60px_rgba(0,0,0,0.55)]">
            <DialogHeader class="border-b border-slate-100 px-5 py-4 text-left dark:border-slate-700/80">
                <div class="flex items-start gap-3.5">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border-[1.5px] shadow-[2px_2px_0_0_rgba(0,0,0,0.12)]"
                        :class="{
                            'border-red-300 bg-red-50 text-red-600 dark:border-red-400/40 dark:bg-red-400/10 dark:text-red-200': variant === 'danger',
                            'border-amber-300 bg-amber-50 text-amber-700 dark:border-amber-300/40 dark:bg-amber-400/10 dark:text-amber-200': variant === 'warning',
                            'border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-300/40 dark:bg-emerald-400/10 dark:text-emerald-200': variant === 'success',
                            'border-slate-300 bg-slate-50 text-slate-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200': variant === 'default',
                        }"
                    >
                        <component :is="icon" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <DialogTitle class="text-base font-extrabold text-tm-navy dark:text-slate-100">{{ title }}</DialogTitle>
                        <DialogDescription class="mt-1.5 text-sm leading-6 text-slate-600 dark:text-slate-400">
                            {{ description }}
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>
            <DialogFooter class="flex gap-2.5 bg-slate-50/80 px-5 py-4 dark:bg-slate-950/25 sm:justify-end">
                <DialogClose as-child>
                    <Button
                        variant="outline"
                        class="h-9 rounded-lg border-[1.5px] border-black bg-white px-4 text-xs font-bold text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.1)] transition-all hover:-translate-y-0.5 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-950/30 dark:text-slate-100 dark:hover:bg-slate-800"
                        @click="handleCancel"
                        :disabled="loading"
                    >
                        {{ cancelLabel }}
                    </Button>
                </DialogClose>
                <Button
                    :disabled="loading"
                    class="h-9 rounded-lg border-[1.5px] border-black px-4 text-xs font-bold text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] transition-all hover:-translate-y-0.5 dark:border-slate-600"
                    :class="{
                        'bg-red-600 hover:bg-red-700': variant === 'danger',
                        'bg-amber-600 hover:bg-amber-700': variant === 'warning',
                        'bg-tm-green hover:bg-tm-green-dark': variant === 'success',
                        'bg-tm-navy hover:bg-tm-navy-medium': variant === 'default',
                    }"
                    @click="handleConfirm"
                >
                    {{ loading ? 'Memproses...' : confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
