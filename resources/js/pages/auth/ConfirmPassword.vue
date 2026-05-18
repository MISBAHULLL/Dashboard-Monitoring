<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { LockKeyhole, ShieldCheck } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/password/confirm';

defineOptions({
    layout: {
        title: 'Konfirmasi Password',
        description:
            'Masukkan password akun Anda untuk membuka halaman Security.',
    },
});
</script>

<template>
    <Head title="Konfirmasi Password" />

    <Form
        v-bind="store.form()"
        reset-on-success
        v-slot="{ errors, processing }"
    >
        <div class="space-y-5">
            <div class="rounded-[18px] border-2 border-black bg-white p-5 shadow-[3px_5px_0_0_rgba(0,0,0,0.18)] dark:border-slate-700 dark:bg-[#111c2e] dark:shadow-[0_18px_44px_rgba(0,0,0,0.42)]">
                <div class="mb-5 flex items-start gap-3.5">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border-2 border-black bg-tm-navy-pale text-tm-navy shadow-[2px_2px_0_0_rgba(0,0,0,0.14)] dark:border-slate-600 dark:bg-sky-400/10 dark:text-sky-200">
                        <ShieldCheck class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-lg font-extrabold text-tm-navy dark:text-slate-100">
                            Verifikasi akses Security
                        </h1>
                        <p class="mt-1.5 text-sm leading-6 text-tm-text-secondary dark:text-slate-400">
                            Anda masih login. Kami hanya perlu memastikan ini benar-benar Anda sebelum membuka pengaturan password dan 2FA.
                        </p>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="password" class="text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-slate-200">
                        Password saat ini
                    </Label>
                    <div class="relative">
                        <LockKeyhole class="pointer-events-none absolute left-3 top-1/2 z-10 h-4 w-4 -translate-y-1/2 text-tm-text-muted dark:text-slate-500" />
                        <PasswordInput
                            id="password"
                            name="password"
                            class="h-11 rounded-xl border-[1.5px] border-black bg-white pl-9 text-sm font-semibold text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] focus-visible:ring-tm-green dark:border-slate-600 dark:bg-slate-950/30 dark:text-slate-100"
                            required
                            autocomplete="current-password"
                            autofocus
                        />
                    </div>

                    <InputError :message="errors.password" />
                </div>
            </div>

            <div class="space-y-3">
                <Button
                    class="flex h-11 w-full items-center justify-center gap-2 rounded-xl border-[1.5px] border-black bg-tm-green text-sm font-bold text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] transition-all hover:-translate-y-0.5 hover:bg-tm-green-dark hover:shadow-[3px_3px_0_0_rgba(0,0,0,0.2)] dark:border-emerald-300/40"
                    :disabled="processing"
                    data-test="confirm-password-button"
                >
                    <Spinner v-if="processing" />
                    {{ processing ? 'Memverifikasi...' : 'Lanjut ke Security' }}
                </Button>

                <p class="text-center text-xs leading-5 text-tm-text-muted dark:text-slate-500">
                    Setelah terkonfirmasi, akses security aktif sementara sesuai sesi keamanan aplikasi.
                </p>
            </div>
        </div>
    </Form>
</template>
