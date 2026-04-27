<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarDays,
    MessageSquareMore,
    Pin,
    PinOff,
    SendHorizonal,
    Trash2,
    UserRound,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import {
    destroy as destroyTaskComment,
    store as storeTaskComment,
    togglePin as pinTaskComment,
} from '@/actions/App/Http/Controllers/TaskCommentController';
import {
    edit as editTask,
    index as tasksIndex,
} from '@/actions/App/Http/Controllers/TaskController';

type TaskCommentItem = {
    id: number;
    body: string;
    is_pinned: boolean;
    created_at: string | null;
    user: { id: number; name: string } | null;
    can_delete: boolean;
    can_pin: boolean;
};

type TaskDetail = {
    id: number;
    title: string;
    description: string | null;
    category: string;
    priority: 'urgent' | 'high' | 'medium' | 'low';
    status: 'open' | 'in_progress' | 'revision' | 'completed';
    task_url: string | null;
    modul: string | null;
    release_date: string | null;
    client?: { name?: string | null } | null;
    product?: { name?: string | null } | null;
    engineer?: { name?: string | null } | null;
    assignee?: { name?: string | null } | null;
    creator?: { name?: string | null } | null;
    comments: TaskCommentItem[];
};

const props = defineProps<{
    task: TaskDetail;
    permissions: {
        can_edit: boolean;
        can_comment: boolean;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Daftar Task', href: tasksIndex.url() },
            { title: 'Detail Task', href: '#' },
        ],
    },
});

const commentForm = useForm({
    body: '',
});

const submitComment = () => {
    commentForm.post(storeTaskComment.url(props.task.id), {
        preserveScroll: true,
        onSuccess: () => commentForm.reset(),
    });
};

const formatDate = (value: string | null) => {
    if (!value) {
        return 'Belum dijadwalkan';
    }

    return new Date(value).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const deleteComment = (comment: TaskCommentItem) => {
    if (!confirm('Hapus komentar ini?')) {
        return;
    }

    useForm({}).delete(destroyTaskComment.url({ task: props.task.id, comment: comment.id }), {
        preserveScroll: true,
    });
};

const togglePin = (comment: TaskCommentItem) => {
    useForm({}).patch(pinTaskComment.url({ task: props.task.id, comment: comment.id }), {
        preserveScroll: true,
    });
};

const orderedComments = computed(() =>
    [...props.task.comments].sort((left, right) => {
        if (left.is_pinned !== right.is_pinned) {
            return Number(right.is_pinned) - Number(left.is_pinned);
        }

        return new Date(right.created_at ?? '').getTime() - new Date(left.created_at ?? '').getTime();
    }),
);
</script>

<template>
    <Head :title="`Task: ${task.title}`" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-y-auto bg-slate-50/40 p-4 md:p-8 dark:bg-slate-950/30">
        <div class="flex flex-col gap-4 border-b border-slate-200/80 pb-5 dark:border-slate-800 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex items-start gap-4">
                <Link :href="tasksIndex.url()">
                    <Button variant="outline" size="icon" class="h-10 w-10 shrink-0 rounded-full border-slate-300 bg-white dark:bg-slate-900">
                        <ArrowLeft class="h-5 w-5" />
                    </Button>
                </Link>
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge variant="outline" class="rounded-full border-slate-200 bg-white text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                            {{ task.category }}
                        </Badge>
                        <Badge
                            class="rounded-full"
                            :class="{
                                'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300': task.status === 'open',
                                'bg-sky-100 text-sky-700 dark:bg-sky-950/30 dark:text-sky-300': task.status === 'in_progress',
                                'bg-amber-100 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300': task.status === 'revision',
                                'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300': task.status === 'completed',
                            }"
                        >
                            {{ task.status.replace('_', ' ') }}
                        </Badge>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ task.title }}</h1>
                    <p class="max-w-3xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                        {{ task.description || 'Belum ada deskripsi rinci untuk tiket ini.' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <Link v-if="permissions.can_edit" :href="editTask.url(task.id)">
                    <Button class="rounded-xl bg-sky-600 text-white hover:bg-sky-500">Edit Task</Button>
                </Link>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(22rem,0.9fr)]">
            <section class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-2">
                    <MessageSquareMore class="h-5 w-5 text-sky-600" />
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Diskusi Task</h2>
                </div>

                <form v-if="permissions.can_comment" @submit.prevent="submitComment" class="space-y-3 rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                    <Textarea
                        v-model="commentForm.body"
                        rows="4"
                        placeholder="Tulis pembaruan, pertanyaan, atau keputusan penting untuk task ini..."
                        :class="{ 'border-red-500': commentForm.errors.body }"
                    />
                    <p v-if="commentForm.errors.body" class="text-sm text-red-500">{{ commentForm.errors.body }}</p>
                    <div class="flex justify-end">
                        <Button type="submit" :disabled="commentForm.processing" class="rounded-xl bg-sky-600 text-white hover:bg-sky-500">
                            <SendHorizonal class="mr-2 h-4 w-4" />
                            {{ commentForm.processing ? 'Mengirim...' : 'Kirim Komentar' }}
                        </Button>
                    </div>
                </form>

                <div v-if="orderedComments.length === 0" class="rounded-2xl border border-dashed border-slate-300/80 px-6 py-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    Belum ada diskusi. Komentar pertama biasanya membantu semua orang tetap sinkron.
                </div>

                <div v-else class="space-y-4">
                    <article
                        v-for="comment in orderedComments"
                        :key="comment.id"
                        class="rounded-2xl border p-4 shadow-sm transition"
                        :class="comment.is_pinned
                            ? 'border-amber-200 bg-amber-50/70 dark:border-amber-900/40 dark:bg-amber-950/20'
                            : 'border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-900'"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sm font-bold text-sky-700 dark:bg-sky-950/30 dark:text-sky-300">
                                    {{ comment.user?.name?.slice(0, 2).toUpperCase() ?? '??' }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ comment.user?.name ?? 'User tidak diketahui' }}</p>
                                        <Badge v-if="comment.is_pinned" class="rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300">
                                            Disematkan
                                        </Badge>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ formatDate(comment.created_at) }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-1">
                                <Button
                                    v-if="comment.can_pin"
                                    variant="ghost"
                                    size="icon"
                                    class="h-8 w-8 rounded-lg text-amber-600 hover:bg-amber-100 dark:hover:bg-amber-950/30"
                                    @click="togglePin(comment)"
                                >
                                    <Pin v-if="!comment.is_pinned" class="h-4 w-4" />
                                    <PinOff v-else class="h-4 w-4" />
                                </Button>
                                <Button
                                    v-if="comment.can_delete"
                                    variant="ghost"
                                    size="icon"
                                    class="h-8 w-8 rounded-lg text-rose-500 hover:bg-rose-100 dark:hover:bg-rose-950/30"
                                    @click="deleteComment(comment)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>

                        <p class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-700 dark:text-slate-300">
                            {{ comment.body }}
                        </p>
                    </article>
                </div>
            </section>

            <aside class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Ringkasan Task</h2>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Client</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ task.client?.name || '-' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Product</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ task.product?.name || '-' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Engineer / PIC</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ task.assignee?.name || task.engineer?.name || '-' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Release Date</p>
                        <p class="mt-2 flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-slate-100">
                            <CalendarDays class="h-4 w-4 text-slate-400" />
                            {{ formatDate(task.release_date) }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Dibuat Oleh</p>
                        <p class="mt-2 flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-slate-100">
                            <UserRound class="h-4 w-4 text-slate-400" />
                            {{ task.creator?.name || '-' }}
                        </p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</template>
