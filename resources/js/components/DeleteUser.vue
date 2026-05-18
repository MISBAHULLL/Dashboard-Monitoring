<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';
import { destroy as profileDestroy } from '@/routes/profile';
import { AlertTriangle, Trash2 } from 'lucide-vue-next';

const ProfileController = {
    destroy: profileDestroy,
};
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

const passwordInput = useTemplateRef('passwordInput');
</script>

<template>
    <div class="overflow-hidden rounded-[18px] border-2 border-red-400 bg-white shadow-[3px_5px_0_0_rgba(220,38,38,0.18)] dark:border-red-400/50 dark:bg-[#111c2e] dark:shadow-[0_18px_44px_rgba(0,0,0,0.42)]">
        <div class="border-b-2 border-red-400 bg-red-50 px-5 py-5 dark:border-red-400/50 dark:bg-red-950/20">
            <Heading
                variant="small"
                title="Hapus Akun"
                description="Hapus akun Anda dari sistem."
            />
        </div>
        <div
            class="space-y-4 p-5"
        >
            <div class="flex gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700 dark:border-red-400/30 dark:bg-red-950/25 dark:text-red-200">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-red-300 bg-white dark:border-red-400/40 dark:bg-red-950/40">
                    <AlertTriangle class="h-5 w-5" />
                </div>
                <div class="space-y-1">
                    <p class="font-extrabold">Tindakan berisiko</p>
                    <p class="text-sm leading-6">
                        Menghapus akun bersifat permanen. Pastikan Anda sudah yakin sebelum melanjutkan.
                    </p>
                </div>
            </div>
            <Dialog>
                <DialogTrigger as-child>
                    <Button
                        variant="destructive"
                        data-test="delete-user-button"
                        class="inline-flex h-10 items-center gap-2 rounded-xl border-[1.5px] border-black bg-red-600 px-4 text-sm font-bold text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] transition-all hover:-translate-y-0.5 hover:bg-red-700 hover:shadow-[3px_3px_0_0_rgba(0,0,0,0.2)] dark:border-red-300/40"
                    >
                        <Trash2 class="h-4 w-4" />
                        Hapus Akun
                    </Button
                    >
                </DialogTrigger>
                <DialogContent class="overflow-hidden rounded-[18px] border-2 border-black bg-white p-0 shadow-[4px_6px_0_0_rgba(0,0,0,0.22)] sm:max-w-[460px] dark:border-slate-700 dark:bg-[#111c2e]">
                    <Form
                        v-bind="ProfileController.destroy.form()"
                        reset-on-success
                        @error="() => passwordInput?.focus()"
                        :options="{
                            preserveScroll: true,
                        }"
                        class="space-y-0"
                        v-slot="{ errors, processing, reset, clearErrors }"
                    >
                        <DialogHeader class="border-b border-slate-100 px-5 py-4 text-left dark:border-slate-700/80">
                            <div class="flex items-start gap-3.5">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border-[1.5px] border-red-300 bg-red-50 text-red-600 shadow-[2px_2px_0_0_rgba(0,0,0,0.12)] dark:border-red-400/40 dark:bg-red-400/10 dark:text-red-200">
                                    <Trash2 class="h-5 w-5" />
                                </div>
                                <div>
                                    <DialogTitle class="text-base font-extrabold text-tm-navy dark:text-slate-100">
                                        Hapus akun permanen?
                                    </DialogTitle>
                                    <DialogDescription class="mt-1.5 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                        Masukkan password Anda untuk mengonfirmasi penghapusan akun.
                                    </DialogDescription>
                                </div>
                            </div>
                        </DialogHeader>

                        <div class="grid gap-2 px-5 py-4">
                            <Label for="password" class="sr-only"
                                >Password</Label
                            >
                            <PasswordInput
                                id="password"
                                name="password"
                                ref="passwordInput"
                                placeholder="Password"
                                class="h-11 rounded-xl border-[1.5px] border-black bg-white text-sm font-semibold shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] dark:border-slate-600 dark:bg-slate-950/30"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <DialogFooter class="gap-2 bg-slate-50/80 px-5 py-4 dark:bg-slate-950/25">
                            <DialogClose as-child>
                                <Button
                                    variant="secondary"
                                    class="h-9 rounded-lg border-[1.5px] border-black bg-white px-4 text-xs font-bold text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.1)] transition-all hover:-translate-y-0.5 dark:border-slate-600 dark:bg-slate-950/30 dark:text-slate-100"
                                    @click="
                                        () => {
                                            clearErrors();
                                            reset();
                                        }
                                    "
                                >
                                    Batal
                                </Button>
                            </DialogClose>

                            <Button
                                type="submit"
                                variant="destructive"
                                :disabled="processing"
                                data-test="confirm-delete-user-button"
                                class="h-9 rounded-lg border-[1.5px] border-black bg-red-600 px-4 text-xs font-bold text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] transition-all hover:-translate-y-0.5 hover:bg-red-700 dark:border-red-300/40"
                            >
                                {{ processing ? 'Menghapus...' : 'Hapus Akun' }}
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
