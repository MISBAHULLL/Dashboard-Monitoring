<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineOptions({
    layout: {
        title: 'Buat akun baru',
        description: 'Lengkapi data akun untuk mulai menggunakan sistem.',
    },
});

const labelClass = 'text-xs font-bold text-tm-navy dark:text-slate-100';
const fieldClass =
    'h-11 rounded-xl border-[1.5px] border-black/60 bg-white px-4 text-sm text-tm-navy shadow-[2px_2px_0_0_rgba(27,58,107,0.10)] transition-all placeholder:text-tm-text-muted focus-visible:border-tm-green focus-visible:ring-tm-green/25 dark:border-slate-600 dark:bg-[#0f172a] dark:text-white dark:shadow-none dark:placeholder:text-slate-500';
const buttonClass =
    'h-11 rounded-xl border-2 border-black bg-tm-green font-bold text-white shadow-[3px_3px_0_0_rgba(0,0,0,0.75)] transition-all hover:-translate-y-0.5 hover:bg-tm-green-dark hover:shadow-[4px_5px_0_0_rgba(0,0,0,0.75)] active:translate-y-0 active:shadow-[1px_1px_0_0_rgba(0,0,0,0.75)] dark:border-slate-700 dark:shadow-[0_12px_24px_rgba(0,0,0,0.35)] dark:hover:bg-tm-green';
</script>

<template>
    <Head title="Register" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="name" :class="labelClass">Nama</Label>
                <Input
                    id="name"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="name"
                    name="name"
                    placeholder="Nama lengkap"
                    :class="fieldClass"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email" :class="labelClass">Email</Label>
                <Input
                    id="email"
                    type="email"
                    required
                    :tabindex="2"
                    autocomplete="email"
                    name="email"
                    placeholder="email@example.com"
                    :class="fieldClass"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password" :class="labelClass">Password</Label>
                <PasswordInput
                    id="password"
                    required
                    :tabindex="3"
                    autocomplete="new-password"
                    name="password"
                    placeholder="Password"
                    :class="fieldClass"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation" :class="labelClass">
                    Konfirmasi password
                </Label>
                <PasswordInput
                    id="password_confirmation"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="Konfirmasi password"
                    :class="fieldClass"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                :class="['mt-2 w-full', buttonClass]"
                tabindex="5"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                Buat akun
            </Button>
        </div>

        <div class="text-center text-sm text-tm-text-secondary dark:text-slate-300">
            Sudah punya akun?
            <TextLink
                :href="login()"
                class="font-semibold text-tm-navy-medium decoration-tm-navy-medium/40 hover:text-tm-green dark:text-emerald-200"
                :tabindex="6"
                >Masuk</TextLink
            >
        </div>
    </Form>
</template>
