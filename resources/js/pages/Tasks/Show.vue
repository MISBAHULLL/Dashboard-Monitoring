<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarDays,
    ClipboardList,
    Info,
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
    pin as pinTaskComment,
} from '@/routes/tasks/comments';
import { edit as editTask, index as tasksIndex } from '@/routes/tasks';

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
    sla_due_date: string | null;
    sla_warning_date: string | null;
    sla_status: string | null;
    created_at: string | null;
    updated_at: string | null;
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

const statusLabels: Record<TaskDetail['status'], string> = {
    open: 'Open',
    in_progress: 'In Progress',
    revision: 'Revision',
    completed: 'Completed',
};

const priorityLabels: Record<TaskDetail['priority'], string> = {
    urgent: 'Urgent',
    high: 'High',
    medium: 'Medium',
    low: 'Low',
};

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

const deadlineSource = computed(() =>
    props.task.release_date ? 'Manual release' : 'SLA kategori',
);

const deleteComment = (comment: TaskCommentItem) => {
    if (!confirm('Hapus komentar ini?')) {
        return;
    }

    useForm({}).delete(
        destroyTaskComment.url({ task: props.task.id, comment: comment.id }),
        {
            preserveScroll: true,
        },
    );
};

const togglePin = (comment: TaskCommentItem) => {
    useForm({}).patch(
        pinTaskComment.url({ task: props.task.id, comment: comment.id }),
        {
            preserveScroll: true,
        },
    );
};

const orderedComments = computed(() =>
    [...props.task.comments].sort((left, right) => {
        if (left.is_pinned !== right.is_pinned) {
            return Number(right.is_pinned) - Number(left.is_pinned);
        }

        return (
            new Date(right.created_at ?? '').getTime() -
            new Date(left.created_at ?? '').getTime()
        );
    }),
);
</script>

<template>
    <Head :title="`Task: ${task.title}`" />

    <div
        class="flex h-full flex-1 flex-col gap-5 overflow-y-auto bg-tm-page p-3 md:p-4 dark:bg-[#081422]"
    >
        <div
            class="flex flex-col gap-4 border-b border-slate-200 px-1 pb-5 lg:flex-row lg:items-start lg:justify-between dark:border-slate-700"
        >
            <div class="flex items-start gap-4">
                <Link :href="tasksIndex.url()">
                    <Button
                        variant="outline"
                        size="icon"
                        class="h-11 w-11 shrink-0 rounded-2xl border-2 border-black bg-white text-tm-navy shadow-[2px_2px_0_0_rgba(0,0,0,0.14)] transition-all hover:-translate-y-0.5 hover:bg-tm-navy-pale dark:border-slate-600 dark:bg-slate-950/40 dark:text-slate-100 dark:hover:bg-slate-800"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </Button>
                </Link>
                <div class="min-w-0 space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge
                            variant="outline"
                            class="rounded-full border-black bg-white px-3 py-1 text-xs font-bold text-tm-navy shadow-[1px_1px_0_0_rgba(0,0,0,0.08)] dark:border-slate-600 dark:bg-slate-950/40 dark:text-slate-200"
                        >
                            {{ task.category }}
                        </Badge>
                        <Badge
                            class="rounded-full px-3 py-1 text-xs font-bold shadow-sm"
                            :class="{
                                'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300':
                                    task.status === 'open',
                                'bg-sky-100 text-sky-700 dark:bg-sky-950/30 dark:text-sky-300':
                                    task.status === 'in_progress',
                                'bg-amber-100 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300':
                                    task.status === 'revision',
                                'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300':
                                    task.status === 'completed',
                            }"
                        >
                            {{ statusLabels[task.status] }}
                        </Badge>
                        <Badge
                            variant="outline"
                            class="rounded-full border-tm-border bg-white px-3 py-1 text-xs font-bold text-tm-text-secondary dark:border-slate-600 dark:bg-slate-950/40 dark:text-slate-300"
                        >
                            {{ priorityLabels[task.priority] }}
                        </Badge>
                    </div>
                    <h1
                        class="text-2xl font-extrabold tracking-tight break-words text-tm-navy dark:text-slate-100"
                    >
                        {{ task.title }}
                    </h1>
                    <p
                        class="max-w-3xl text-sm leading-6 text-tm-text-secondary dark:text-slate-400"
                    >
                        {{
                            task.description ||
                            'Belum ada deskripsi rinci untuk tiket ini.'
                        }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <Link v-if="permissions.can_edit" :href="editTask.url(task.id)">
                    <Button
                        class="h-10 rounded-xl border-[1.5px] border-black bg-tm-green px-4 text-sm font-bold text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] transition-all hover:-translate-y-0.5 hover:bg-tm-green-dark hover:shadow-[3px_3px_0_0_rgba(0,0,0,0.2)] dark:border-emerald-300/40"
                    >
                        Edit Task
                    </Button>
                </Link>
            </div>
        </div>

        <div
            class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(22rem,0.9fr)]"
        >
            <section
                class="overflow-hidden rounded-[18px] border-2 border-black bg-white shadow-[3px_5px_0_0_rgba(0,0,0,0.18)] dark:border-slate-700 dark:bg-[#111c2e] dark:shadow-[0_18px_44px_rgba(0,0,0,0.42)]"
            >
                <div
                    class="flex items-center justify-between gap-3 border-b-2 border-black bg-tm-navy-pale px-5 py-4 dark:border-slate-700 dark:bg-slate-800/70"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl border-[1.5px] border-black bg-white text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.10)] dark:border-slate-600 dark:bg-slate-950/40 dark:text-slate-100"
                        >
                            <MessageSquareMore class="h-5 w-5" />
                        </div>
                        <div>
                            <h2
                                class="text-lg font-extrabold text-tm-navy dark:text-slate-100"
                            >
                                Diskusi Task
                            </h2>
                            <p
                                class="text-xs font-medium text-tm-text-secondary dark:text-slate-400"
                            >
                                {{ orderedComments.length }} komentar tercatat
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-5 p-5">
                    <form
                        v-if="permissions.can_comment"
                        @submit.prevent="submitComment"
                        class="space-y-3 rounded-2xl border-[1.5px] border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/30"
                    >
                        <Textarea
                            v-model="commentForm.body"
                            rows="4"
                            placeholder="Tulis pembaruan, pertanyaan, atau keputusan penting untuk task ini..."
                            class="min-h-28 rounded-xl border-[1.5px] border-black bg-white text-sm text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] placeholder:text-tm-text-muted focus-visible:ring-tm-green dark:border-slate-600 dark:bg-slate-950/40 dark:text-slate-100"
                            :class="{
                                'border-red-500': commentForm.errors.body,
                            }"
                        />
                        <p
                            v-if="commentForm.errors.body"
                            class="text-sm text-red-500"
                        >
                            {{ commentForm.errors.body }}
                        </p>
                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <p
                                class="text-xs font-medium text-tm-text-secondary dark:text-slate-400"
                            >
                                Gunakan komentar untuk update progres,
                                keputusan, atau kendala.
                            </p>
                            <Button
                                type="submit"
                                :disabled="
                                    commentForm.processing ||
                                    !commentForm.body.trim()
                                "
                                class="h-10 rounded-xl border-[1.5px] border-black bg-tm-navy px-4 text-sm font-bold text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] transition-all hover:-translate-y-0.5 hover:bg-tm-navy-medium disabled:translate-y-0 disabled:opacity-60"
                            >
                                <SendHorizonal class="mr-2 h-4 w-4" />
                                {{
                                    commentForm.processing
                                        ? 'Mengirim...'
                                        : 'Kirim Komentar'
                                }}
                            </Button>
                        </div>
                    </form>

                    <div
                        v-if="orderedComments.length === 0"
                        class="rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center dark:border-slate-700 dark:bg-slate-950/30"
                    >
                        <div
                            class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl border-[1.5px] border-black bg-white text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.10)] dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                        >
                            <MessageSquareMore class="h-7 w-7" />
                        </div>
                        <p
                            class="text-sm font-bold text-tm-navy dark:text-slate-100"
                        >
                            Belum ada diskusi
                        </p>
                        <p
                            class="mt-1 text-sm text-tm-text-secondary dark:text-slate-400"
                        >
                            Komentar pertama biasanya membantu semua orang tetap
                            sinkron.
                        </p>
                    </div>

                    <div v-else class="space-y-4">
                        <article
                            v-for="comment in orderedComments"
                            :key="comment.id"
                            class="rounded-2xl border-[1.5px] p-4 shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] transition"
                            :class="
                                comment.is_pinned
                                    ? 'border-amber-300 bg-amber-50 dark:border-amber-300/35 dark:bg-amber-400/10'
                                    : 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-950/25'
                            "
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border-[1.5px] border-black bg-tm-navy-pale text-sm font-extrabold text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                                    >
                                        {{
                                            comment.user?.name
                                                ?.slice(0, 2)
                                                .toUpperCase() ?? '??'
                                        }}
                                    </div>
                                    <div class="min-w-0">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <p
                                                class="font-bold text-tm-navy dark:text-slate-100"
                                            >
                                                {{
                                                    comment.user?.name ??
                                                    'User tidak diketahui'
                                                }}
                                            </p>
                                            <Badge
                                                v-if="comment.is_pinned"
                                                class="rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300"
                                            >
                                                Disematkan
                                            </Badge>
                                        </div>
                                        <p
                                            class="text-xs text-tm-text-secondary dark:text-slate-400"
                                        >
                                            {{ formatDate(comment.created_at) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex shrink-0 items-center gap-1">
                                    <Button
                                        v-if="comment.can_pin"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 rounded-lg text-amber-600 hover:bg-amber-100 dark:hover:bg-amber-950/30"
                                        @click="togglePin(comment)"
                                    >
                                        <Pin
                                            v-if="!comment.is_pinned"
                                            class="h-4 w-4"
                                        />
                                        <PinOff v-else class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        v-if="comment.can_delete"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 rounded-lg text-tm-danger hover:bg-tm-danger-pale dark:hover:bg-red-400/10"
                                        @click="deleteComment(comment)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <p
                                class="mt-4 rounded-xl bg-slate-50 p-3 text-sm leading-6 whitespace-pre-line text-slate-700 dark:bg-slate-900/70 dark:text-slate-300"
                            >
                                {{ comment.body }}
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <aside
                class="overflow-hidden rounded-[18px] border-2 border-black bg-white shadow-[3px_5px_0_0_rgba(0,0,0,0.18)] dark:border-slate-700 dark:bg-[#111c2e] dark:shadow-[0_18px_44px_rgba(0,0,0,0.42)]"
            >
                <div
                    class="flex items-center gap-3 border-b-2 border-black bg-tm-navy-pale px-5 py-4 dark:border-slate-700 dark:bg-slate-800/70"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl border-[1.5px] border-black bg-white text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.10)] dark:border-slate-600 dark:bg-slate-950/40 dark:text-slate-100"
                    >
                        <ClipboardList class="h-5 w-5" />
                    </div>
                    <h2
                        class="text-lg font-extrabold text-tm-navy dark:text-slate-100"
                    >
                        Ringkasan Task
                    </h2>
                </div>

                <div class="grid gap-3 p-5 sm:grid-cols-2 xl:grid-cols-1">
                    <div
                        class="rounded-2xl border-[1.5px] border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/30"
                    >
                        <p
                            class="text-xs font-bold tracking-[0.16em] text-tm-text-secondary uppercase dark:text-slate-400"
                        >
                            Client
                        </p>
                        <p
                            class="mt-2 text-sm font-bold break-words text-tm-navy dark:text-slate-100"
                        >
                            {{ task.client?.name || '-' }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border-[1.5px] border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/30"
                    >
                        <p
                            class="text-xs font-bold tracking-[0.16em] text-tm-text-secondary uppercase dark:text-slate-400"
                        >
                            Product
                        </p>
                        <p
                            class="mt-2 text-sm font-bold break-words text-tm-navy dark:text-slate-100"
                        >
                            {{ task.product?.name || '-' }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border-[1.5px] border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/30"
                    >
                        <p
                            class="text-xs font-bold tracking-[0.16em] text-tm-text-secondary uppercase dark:text-slate-400"
                        >
                            Engineer / PIC
                        </p>
                        <p
                            class="mt-2 text-sm font-bold break-words text-tm-navy dark:text-slate-100"
                        >
                            {{
                                task.assignee?.name ||
                                task.engineer?.name ||
                                '-'
                            }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border-[1.5px] border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/30"
                    >
                        <p
                            class="text-xs font-bold tracking-[0.16em] text-tm-text-secondary uppercase dark:text-slate-400"
                        >
                            Release Date
                        </p>
                        <p
                            class="mt-2 flex items-center gap-2 text-sm font-bold text-tm-navy dark:text-slate-100"
                        >
                            <CalendarDays
                                class="h-4 w-4 text-tm-text-muted dark:text-slate-500"
                            />
                            {{ formatDate(task.release_date) }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border-[1.5px] border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/30"
                    >
                        <p
                            class="text-xs font-bold tracking-[0.16em] text-tm-text-secondary uppercase dark:text-slate-400"
                        >
                            Deadline Efektif
                        </p>
                        <p
                            class="mt-2 flex items-center gap-2 text-sm font-bold text-tm-navy dark:text-slate-100"
                        >
                            <CalendarDays
                                class="h-4 w-4 text-tm-text-muted dark:text-slate-500"
                            />
                            {{ formatDate(task.sla_due_date) }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border-[1.5px] border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/30"
                    >
                        <p
                            class="text-xs font-bold tracking-[0.16em] text-tm-text-secondary uppercase dark:text-slate-400"
                        >
                            Sumber Deadline
                        </p>
                        <p
                            class="mt-2 text-sm font-bold break-words text-tm-navy dark:text-slate-100"
                        >
                            {{ deadlineSource }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border-[1.5px] border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/30"
                    >
                        <p
                            class="text-xs font-bold tracking-[0.16em] text-tm-text-secondary uppercase dark:text-slate-400"
                        >
                            Dibuat Oleh
                        </p>
                        <p
                            class="mt-2 flex items-center gap-2 text-sm font-bold text-tm-navy dark:text-slate-100"
                        >
                            <UserRound
                                class="h-4 w-4 text-tm-text-muted dark:text-slate-500"
                            />
                            {{ task.creator?.name || '-' }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border-[1.5px] border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/30"
                    >
                        <p
                            class="text-xs font-bold tracking-[0.16em] text-tm-text-secondary uppercase dark:text-slate-400"
                        >
                            Dibuat Pada
                        </p>
                        <p
                            class="mt-2 flex items-center gap-2 text-sm font-bold text-tm-navy dark:text-slate-100"
                        >
                            <CalendarDays
                                class="h-4 w-4 text-tm-text-muted dark:text-slate-500"
                            />
                            {{ formatDate(task.created_at) }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border-[1.5px] border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/30"
                    >
                        <p
                            class="text-xs font-bold tracking-[0.16em] text-tm-text-secondary uppercase dark:text-slate-400"
                        >
                            Terakhir Diperbarui
                        </p>
                        <p
                            class="mt-2 flex items-center gap-2 text-sm font-bold text-tm-navy dark:text-slate-100"
                        >
                            <CalendarDays
                                class="h-4 w-4 text-tm-text-muted dark:text-slate-500"
                            />
                            {{ formatDate(task.updated_at) }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border-[1.5px] border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/30"
                    >
                        <p
                            class="flex items-center gap-2 text-xs font-bold tracking-[0.16em] text-tm-text-secondary uppercase dark:text-slate-400"
                        >
                            <Info class="h-3.5 w-3.5" />
                            Modul
                        </p>
                        <p
                            class="mt-2 text-sm font-bold break-words text-tm-navy dark:text-slate-100"
                        >
                            {{ task.modul || '-' }}
                        </p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</template>
