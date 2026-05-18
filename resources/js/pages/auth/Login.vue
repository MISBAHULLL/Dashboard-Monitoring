<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Masuk ke akun Anda',
        description: 'Gunakan akun internal untuk mengakses dashboard.',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const labelClass = 'text-xs font-bold text-tm-navy dark:text-slate-100';
const fieldClass =
    'h-11 rounded-xl border-[1.5px] border-black/60 bg-white px-4 text-sm text-tm-navy shadow-[2px_2px_0_0_rgba(27,58,107,0.10)] transition-all placeholder:text-tm-text-muted focus-visible:border-tm-green focus-visible:ring-tm-green/25 dark:border-slate-600 dark:bg-[#0f172a] dark:text-white dark:shadow-none dark:placeholder:text-slate-500';
const buttonClass =
    'h-11 rounded-xl border-2 border-black bg-tm-green font-bold text-white shadow-[3px_3px_0_0_rgba(0,0,0,0.75)] transition-all hover:-translate-y-0.5 hover:bg-tm-green-dark hover:shadow-[4px_5px_0_0_rgba(0,0,0,0.75)] active:translate-y-0 active:shadow-[1px_1px_0_0_rgba(0,0,0,0.75)] dark:border-slate-700 dark:shadow-[0_12px_24px_rgba(0,0,0,0.35)] dark:hover:bg-tm-green';
</script>

<template>
    <Head title="Log in" />

    <div
        v-if="status"
        class="mb-4 rounded-xl border border-tm-green/30 bg-tm-green-pale px-4 py-3 text-center text-sm font-semibold text-tm-green-dark dark:bg-tm-green/15 dark:text-emerald-200"
    >
        {{ status }}
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email" :class="labelClass">Email</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="email@example.com"
                    :class="fieldClass"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password" :class="labelClass">Password</Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-sm font-semibold text-tm-navy-medium decoration-tm-navy-medium/40 hover:text-tm-green dark:text-emerald-200"
                        :tabindex="5"
                    >
                        Lupa password?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Password"
                    :class="fieldClass"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label
                    for="remember"
                    class="flex items-center gap-3 text-sm font-semibold text-tm-text-secondary dark:text-slate-300"
                >
                    <Checkbox
                        id="remember"
                        name="remember"
                        :tabindex="3"
                        class="border-black/50 data-[state=checked]:border-tm-green data-[state=checked]:bg-tm-green dark:border-slate-600"
                    />
                    <span>Ingat saya</span>
                </Label>
            </div>

            <Button
                type="submit"
                :class="['mt-2 w-full', buttonClass]"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Masuk
            </Button>
        </div>

        <div
            class="text-center text-sm text-tm-text-secondary dark:text-slate-300"
            v-if="canRegister"
        >
            Belum punya akun?
            <TextLink
                :href="register()"
                class="font-semibold text-tm-navy-medium decoration-tm-navy-medium/40 hover:text-tm-green dark:text-emerald-200"
                :tabindex="5"
            >Daftar</TextLink>
        </div>
    </Form>
</template>
