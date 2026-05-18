<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { update as profileUpdate } from '@/routes/profile';
import { Mail, Save, UserRound } from 'lucide-vue-next';

const ProfileController = {
    update: profileUpdate,
};
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const userInitials = computed(() => {
    const name = String(user.value?.name ?? '').trim();
    if (!name) return 'U';

    return name
        .split(' ')
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
});
</script>

<template>
    <Head title="Profil" />

    <h1 class="sr-only">Profil</h1>

    <div class="space-y-6">
        <section class="overflow-hidden rounded-[18px] border-2 border-black bg-white shadow-[3px_5px_0_0_rgba(0,0,0,0.18)] dark:border-slate-700 dark:bg-[#111c2e] dark:shadow-[0_18px_44px_rgba(0,0,0,0.42)]">
            <div class="flex flex-col gap-4 border-b-2 border-black bg-tm-navy-pale px-5 py-5 dark:border-slate-700 dark:bg-slate-800/70 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border-2 border-black bg-white text-lg font-extrabold text-tm-navy shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] dark:border-slate-600 dark:bg-slate-950/40 dark:text-slate-100">
                        {{ userInitials }}
                    </div>
                    <div>
                        <h2 class="text-xl font-extrabold text-tm-navy dark:text-slate-100">Profil Pengguna</h2>
                        <p class="mt-1 text-sm text-tm-text-secondary dark:text-slate-400">
                            Kelola identitas akun yang digunakan untuk masuk ke sistem.
                        </p>
                    </div>
                </div>
                <div class="rounded-xl border border-tm-navy/15 bg-white px-3 py-2 text-xs font-bold text-tm-navy shadow-sm dark:border-slate-600 dark:bg-slate-950/40 dark:text-slate-200">
                    {{ user.email }}
                </div>
            </div>

            <div class="p-5">
                <Heading
                    variant="small"
                    title="Informasi Profil"
                    description="Perbarui nama dan alamat email akun Anda."
                />

                <Form
                    v-bind="ProfileController.update.form()"
                    class="mt-5 space-y-5"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="name" class="text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-slate-200">Nama</Label>
                            <div class="relative">
                                <UserRound class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-tm-text-muted dark:text-slate-500" />
                                <Input
                                    id="name"
                                    class="h-11 rounded-xl border-[1.5px] border-black bg-white pl-9 text-sm font-semibold text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] focus-visible:ring-tm-green dark:border-slate-600 dark:bg-slate-950/30 dark:text-slate-100"
                                    name="name"
                                    :default-value="user.name"
                                    required
                                    autocomplete="name"
                                    placeholder="Nama lengkap"
                                />
                            </div>
                            <InputError :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email" class="text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-slate-200">Email</Label>
                            <div class="relative">
                                <Mail class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-tm-text-muted dark:text-slate-500" />
                                <Input
                                    id="email"
                                    type="email"
                                    class="h-11 rounded-xl border-[1.5px] border-black bg-white pl-9 text-sm font-semibold text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] focus-visible:ring-tm-green dark:border-slate-600 dark:bg-slate-950/30 dark:text-slate-100"
                                    name="email"
                                    :default-value="user.email"
                                    required
                                    autocomplete="username"
                                    placeholder="Alamat email"
                                />
                            </div>
                            <InputError :message="errors.email" />
                        </div>
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-400/30 dark:bg-amber-950/25 dark:text-amber-200">
                        Email Anda belum diverifikasi.
                        <Link
                            :href="send()"
                            as="button"
                            class="font-bold underline underline-offset-4 transition-colors hover:text-amber-900 dark:hover:text-amber-100"
                        >
                            Kirim ulang email verifikasi.
                        </Link>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 font-bold text-tm-green"
                        >
                            Link verifikasi baru sudah dikirim ke email Anda.
                        </div>
                    </div>

                    <div class="flex items-center justify-end border-t border-slate-100 pt-4 dark:border-slate-700/80">
                        <Button
                            :disabled="processing"
                            data-test="update-profile-button"
                            class="inline-flex h-10 items-center gap-2 rounded-xl border-[1.5px] border-black bg-tm-green px-4 text-sm font-bold text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] transition-all hover:-translate-y-0.5 hover:bg-tm-green-dark hover:shadow-[3px_3px_0_0_rgba(0,0,0,0.2)] dark:border-emerald-300/40"
                        >
                            <Save class="h-4 w-4" />
                            {{ processing ? 'Menyimpan...' : 'Simpan Profil' }}
                        </Button>
                    </div>
                </Form>
            </div>
        </section>

        <DeleteUser />
    </div>
</template>
