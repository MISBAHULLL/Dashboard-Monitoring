<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogClose } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { AlertTriangle } from 'lucide-vue-next';

interface Props {
    open: boolean;
    title?: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: 'danger' | 'warning' | 'default';
    loading?: boolean;
}

withDefaults(defineProps<Props>(), {
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
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                        :class="{
                            'bg-red-100 text-red-600': variant === 'danger',
                            'bg-amber-100 text-amber-600': variant === 'warning',
                            'bg-slate-100 text-slate-600': variant === 'default',
                        }">
                        <AlertTriangle class="h-5 w-5" />
                    </div>
                    <div>
                        <DialogTitle class="text-lg font-semibold">{{ title }}</DialogTitle>
                        <DialogDescription class="text-sm text-slate-500 mt-1">
                            {{ description }}
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>
            <DialogFooter class="mt-4 flex gap-3 sm:justify-end">
                <DialogClose as-child>
                    <Button variant="outline" @click="handleCancel" :disabled="loading">
                        {{ cancelLabel }}
                    </Button>
                </DialogClose>
                <Button
                    :disabled="loading"
                    :class="{
                        'bg-red-600 hover:bg-red-700 text-white': variant === 'danger',
                        'bg-amber-600 hover:bg-amber-700 text-white': variant === 'warning',
                    }"
                    @click="handleConfirm"
                >
                    {{ loading ? 'Memproses...' : confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
