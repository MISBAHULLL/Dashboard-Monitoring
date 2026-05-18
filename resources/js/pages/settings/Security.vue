<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import {
    CheckCircle2,
    KeyRound,
    LockKeyhole,
    Save,
    ShieldCheck,
    ShieldOff,
} from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';
import { update as securityUpdate } from '@/routes/user-password';

const SecurityController = {
    update: securityUpdate,
};
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import { edit } from '@/routes/security';
import { disable, enable } from '@/routes/two-factor';

type Props = {
    canManageTwoFactor?: boolean;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    canManageTwoFactor: false,
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Security settings',
                href: edit(),
            },
        ],
    },
});

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => clearTwoFactorAuthData());
</script>

<template>
    <Head title="Security settings" />

    <h1 class="sr-only">Security settings</h1>

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
                        <LockKeyhole class="h-6 w-6" />
                    </div>
                    <div>
                        <h2
                            class="text-xl font-extrabold text-tm-navy dark:text-slate-100"
                        >
                            Keamanan Akun
                        </h2>
                        <p
                            class="mt-1 text-sm text-tm-text-secondary dark:text-slate-400"
                        >
                            Kelola password dan proteksi tambahan untuk akun
                            Anda.
                        </p>
                    </div>
                </div>
                <div
                    class="inline-flex w-fit items-center gap-2 rounded-xl border border-tm-navy/15 bg-white px-3 py-2 text-xs font-bold text-tm-navy shadow-sm dark:border-slate-600 dark:bg-slate-950/40 dark:text-slate-200"
                >
                    <ShieldCheck class="h-4 w-4 text-tm-green" />
                    Pengaturan sensitif
                </div>
            </div>

            <div class="p-5">
                <div>
                    <h3
                        class="text-base font-extrabold text-tm-navy dark:text-slate-100"
                    >
                        Update Password
                    </h3>
                    <p
                        class="mt-1 text-sm text-tm-text-secondary dark:text-slate-400"
                    >
                        Gunakan password yang panjang dan unik untuk menjaga
                        akun tetap aman.
                    </p>
                </div>

                <Form
                    v-bind="SecurityController.update.form()"
                    :options="{
                        preserveScroll: true,
                    }"
                    reset-on-success
                    :reset-on-error="[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]"
                    class="mt-5 space-y-5"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-5">
                        <div class="grid gap-2">
                            <Label
                                for="current_password"
                                class="text-xs font-bold tracking-wide text-tm-navy uppercase dark:text-slate-200"
                            >
                                Password Saat Ini
                            </Label>
                            <div class="relative">
                                <KeyRound
                                    class="pointer-events-none absolute top-1/2 left-3 z-10 h-4 w-4 -translate-y-1/2 text-tm-text-muted dark:text-slate-500"
                                />
                                <PasswordInput
                                    id="current_password"
                                    name="current_password"
                                    class="h-11 rounded-xl border-[1.5px] border-black bg-white pr-11 pl-9 text-sm font-semibold text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] focus-visible:ring-tm-green dark:border-slate-600 dark:bg-slate-950/30 dark:text-slate-100"
                                    autocomplete="current-password"
                                    placeholder="Masukkan password saat ini"
                                />
                            </div>
                            <InputError :message="errors.current_password" />
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label
                                    for="password"
                                    class="text-xs font-bold tracking-wide text-tm-navy uppercase dark:text-slate-200"
                                >
                                    Password Baru
                                </Label>
                                <div class="relative">
                                    <KeyRound
                                        class="pointer-events-none absolute top-1/2 left-3 z-10 h-4 w-4 -translate-y-1/2 text-tm-text-muted dark:text-slate-500"
                                    />
                                    <PasswordInput
                                        id="password"
                                        name="password"
                                        class="h-11 rounded-xl border-[1.5px] border-black bg-white pr-11 pl-9 text-sm font-semibold text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] focus-visible:ring-tm-green dark:border-slate-600 dark:bg-slate-950/30 dark:text-slate-100"
                                        autocomplete="new-password"
                                        placeholder="Masukkan password baru"
                                    />
                                </div>
                                <InputError :message="errors.password" />
                            </div>

                            <div class="grid gap-2">
                                <Label
                                    for="password_confirmation"
                                    class="text-xs font-bold tracking-wide text-tm-navy uppercase dark:text-slate-200"
                                >
                                    Konfirmasi Password
                                </Label>
                                <div class="relative">
                                    <KeyRound
                                        class="pointer-events-none absolute top-1/2 left-3 z-10 h-4 w-4 -translate-y-1/2 text-tm-text-muted dark:text-slate-500"
                                    />
                                    <PasswordInput
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        class="h-11 rounded-xl border-[1.5px] border-black bg-white pr-11 pl-9 text-sm font-semibold text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] focus-visible:ring-tm-green dark:border-slate-600 dark:bg-slate-950/30 dark:text-slate-100"
                                        autocomplete="new-password"
                                        placeholder="Ulangi password baru"
                                    />
                                </div>
                                <InputError
                                    :message="errors.password_confirmation"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-end border-t border-slate-100 pt-4 dark:border-slate-700/80"
                    >
                        <Button
                            :disabled="processing"
                            data-test="update-password-button"
                            class="inline-flex h-10 items-center gap-2 rounded-xl border-[1.5px] border-black bg-tm-green px-4 text-sm font-bold text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] transition-all hover:-translate-y-0.5 hover:bg-tm-green-dark hover:shadow-[3px_3px_0_0_rgba(0,0,0,0.2)] dark:border-emerald-300/40"
                        >
                            <Save class="h-4 w-4" />
                            {{
                                processing ? 'Menyimpan...' : 'Simpan Password'
                            }}
                        </Button>
                    </div>
                </Form>
            </div>
        </section>

        <section
            v-if="canManageTwoFactor"
            class="overflow-hidden rounded-[18px] border-2 border-black bg-white shadow-[3px_5px_0_0_rgba(0,0,0,0.18)] dark:border-slate-700 dark:bg-[#111c2e] dark:shadow-[0_18px_44px_rgba(0,0,0,0.42)]"
        >
            <div
                class="flex flex-col gap-4 border-b-2 border-black bg-tm-navy-pale px-5 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700 dark:bg-slate-800/70"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border-2 border-black bg-white text-tm-navy shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] dark:border-slate-600 dark:bg-slate-950/40 dark:text-slate-100"
                    >
                        <ShieldCheck class="h-6 w-6" />
                    </div>
                    <div>
                        <h2
                            class="text-xl font-extrabold text-tm-navy dark:text-slate-100"
                        >
                            Two-Factor Authentication
                        </h2>
                        <p
                            class="mt-1 text-sm text-tm-text-secondary dark:text-slate-400"
                        >
                            Tambahkan verifikasi TOTP saat login.
                        </p>
                    </div>
                </div>
                <div
                    :class="[
                        'inline-flex w-fit items-center gap-2 rounded-xl border px-3 py-2 text-xs font-bold shadow-sm',
                        twoFactorEnabled
                            ? 'border-tm-green/30 bg-tm-green-pale text-tm-green-dark dark:border-emerald-300/40 dark:bg-emerald-400/10 dark:text-emerald-200'
                            : 'border-amber-300/50 bg-amber-50 text-amber-700 dark:border-amber-300/30 dark:bg-amber-400/10 dark:text-amber-200',
                    ]"
                >
                    <CheckCircle2 v-if="twoFactorEnabled" class="h-4 w-4" />
                    <ShieldOff v-else class="h-4 w-4" />
                    {{ twoFactorEnabled ? 'Aktif' : 'Belum aktif' }}
                </div>
            </div>

            <div class="space-y-5 p-5">
                <div
                    v-if="!twoFactorEnabled"
                    class="rounded-2xl border-[1.5px] border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/30"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div class="max-w-2xl">
                            <h3
                                class="text-base font-extrabold text-tm-navy dark:text-slate-100"
                            >
                                Proteksi login belum aktif
                            </h3>
                            <p
                                class="mt-1 text-sm leading-relaxed text-tm-text-secondary dark:text-slate-400"
                            >
                                Setelah diaktifkan, Anda akan diminta kode aman
                                dari aplikasi authenticator setiap kali login.
                            </p>
                        </div>

                        <Button
                            v-if="hasSetupData"
                            @click="showSetupModal = true"
                            class="inline-flex h-10 items-center gap-2 rounded-xl border-[1.5px] border-black bg-tm-navy px-4 text-sm font-bold text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] transition-all hover:-translate-y-0.5 hover:bg-tm-navy-medium"
                        >
                            <ShieldCheck class="h-4 w-4" />
                            Lanjutkan Setup
                        </Button>
                        <Form
                            v-else
                            v-bind="enable.form()"
                            @success="showSetupModal = true"
                            #default="{ processing }"
                        >
                            <Button
                                type="submit"
                                :disabled="processing"
                                class="inline-flex h-10 items-center gap-2 rounded-xl border-[1.5px] border-black bg-tm-navy px-4 text-sm font-bold text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] transition-all hover:-translate-y-0.5 hover:bg-tm-navy-medium"
                            >
                                <ShieldCheck class="h-4 w-4" />
                                {{
                                    processing ? 'Memproses...' : 'Aktifkan 2FA'
                                }}
                            </Button>
                        </Form>
                    </div>
                </div>

                <div
                    v-else
                    class="rounded-2xl border-[1.5px] border-tm-green/30 bg-tm-green-pale p-4 dark:border-emerald-300/35 dark:bg-emerald-400/10"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div class="max-w-2xl">
                            <h3
                                class="text-base font-extrabold text-tm-navy dark:text-slate-100"
                            >
                                Two-factor authentication aktif
                            </h3>
                            <p
                                class="mt-1 text-sm leading-relaxed text-tm-text-secondary dark:text-slate-400"
                            >
                                Akun Anda membutuhkan kode verifikasi tambahan
                                dari aplikasi authenticator saat login.
                            </p>
                        </div>

                        <Form v-bind="disable.form()" #default="{ processing }">
                            <Button
                                variant="destructive"
                                type="submit"
                                :disabled="processing"
                                class="inline-flex h-10 items-center gap-2 rounded-xl border-[1.5px] border-black bg-tm-danger px-4 text-sm font-bold text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] transition-all hover:-translate-y-0.5 hover:bg-red-600"
                            >
                                <ShieldOff class="h-4 w-4" />
                                {{
                                    processing
                                        ? 'Memproses...'
                                        : 'Nonaktifkan 2FA'
                                }}
                            </Button>
                        </Form>
                    </div>

                    <div
                        class="mt-5 border-t border-tm-green/20 pt-5 dark:border-emerald-300/20"
                    >
                        <TwoFactorRecoveryCodes />
                    </div>
                </div>
            </div>

            <TwoFactorSetupModal
                v-model:isOpen="showSetupModal"
                :requiresConfirmation="requiresConfirmation"
                :twoFactorEnabled="twoFactorEnabled"
            />
        </section>
    </div>
</template>
