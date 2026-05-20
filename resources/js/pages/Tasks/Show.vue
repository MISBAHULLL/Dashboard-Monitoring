<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarDays,
    CheckCircle2,
    ClipboardList,
    Info,
    MessageSquareMore,
    Pin,
    PinOff,
    Reply,
    RotateCcw,
    SendHorizonal,
    Trash2,
    UserRound,
    X,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    destroy as destroyTaskComment,
    store as storeTaskComment,
    pin as pinTaskComment,
} from '@/routes/tasks/comments';
import { edit as editTask, index as tasksIndex, submitReview as submitTaskReview, updateStatus } from '@/routes/tasks';

type TaskCommentItem = {
    id: number;
    body: string;
    reply_to_id: number | null;
    reply_to: {
        id: number;
        body: string;
        user: { id: number; name: string } | null;
    } | null;
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
    review_requested_at?: string | null;
    review_requested_by?: number | null;
    client?: { name?: string | null } | null;
    product?: { name?: string | null } | null;
    engineer?: { name?: string | null } | null;
    assignee?: { name?: string | null } | null;
    creator?: { name?: string | null } | null;
    comments: TaskCommentItem[];
    documents_count?: number;
};

const props = defineProps<{
    task: TaskDetail;
    permissions: {
        can_edit: boolean;
        can_comment: boolean;
        can_submit_review: boolean;
        can_review: boolean;
    };
}>();

const page = usePage();
const currentUserId = computed(() => {
    const auth = page.props.auth as { user?: { id?: number | null } | null } | undefined;

    return auth?.user?.id ?? null;
});

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
    reply_to_id: null as number | null,
});
const replyTarget = ref<TaskCommentItem | null>(null);

const reviewForm = useForm({
    task_url: props.task.task_url && props.task.task_url !== '-' ? props.task.task_url : '',
    note: '',
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
        onSuccess: () => {
            commentForm.reset();
            replyTarget.value = null;
        },
    });
};

const submitReview = () => {
    reviewForm.post(submitTaskReview.url(props.task.id), {
        preserveScroll: true,
        onSuccess: () => reviewForm.reset(),
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

const hasReviewEvidence = computed(() =>
    Boolean(props.task.task_url && props.task.task_url !== '-') ||
    Number(props.task.documents_count ?? 0) > 0,
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

const isOwnComment = (comment: TaskCommentItem) =>
    Boolean(comment.user?.id && currentUserId.value && comment.user.id === currentUserId.value);

const commentExcerpt = (body: string, maxLength = 110) => {
    const normalized = body.replace(/\s+/g, ' ').trim();

    return normalized.length > maxLength ? `${normalized.slice(0, maxLength)}...` : normalized;
};

const startReply = (comment: TaskCommentItem) => {
    replyTarget.value = comment;
    commentForm.reply_to_id = comment.id;
    commentForm.clearErrors('reply_to_id');
};

const cancelReply = () => {
    replyTarget.value = null;
    commentForm.reply_to_id = null;
    commentForm.clearErrors('reply_to_id');
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

// --- Review actions (admin: completed / revision) ---
const revisionDialogOpen = ref(false);
const revisionForm = useForm({ review_note: '' });

const isReviewPending = computed(() => Boolean(props.task.review_requested_at));

const openRevisionDialog = () => {
    revisionForm.reset();
    revisionForm.clearErrors();
    revisionDialogOpen.value = true;
};

const closeRevisionDialog = () => {
    revisionDialogOpen.value = false;
    revisionForm.reset();
    revisionForm.clearErrors();
};

const markCompleted = () => {
    router.patch(
        updateStatus.url(props.task.id),
        { status: 'completed' },
        { preserveScroll: true },
    );
};

const submitRevision = () => {
    revisionForm
        .transform((data) => ({
            status: 'revision',
            review_note: data.review_note,
        }))
        .patch(updateStatus.url(props.task.id), {
            preserveScroll: true,
            onSuccess: closeRevisionDialog,
            onFinish: () => revisionForm.transform((data) => data),
        });
};
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

        <section
            v-if="permissions.can_submit_review"
            class="rounded-[18px] border-2 border-black bg-white p-5 shadow-[3px_5px_0_0_rgba(0,0,0,0.14)] dark:border-slate-700 dark:bg-[#111c2e] dark:shadow-[0_18px_44px_rgba(0,0,0,0.42)]"
        >
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-extrabold text-tm-navy dark:text-slate-100">
                            Ajukan Review ke Admin
                        </h2>
                        <Badge
                            v-if="task.review_requested_at"
                            class="rounded-full bg-sky-100 text-sky-700 dark:bg-sky-400/10 dark:text-sky-200"
                        >
                            Menunggu review
                        </Badge>
                    </div>
                    <p class="mt-1 text-sm leading-6 text-tm-text-secondary dark:text-slate-400">
                        Kirim task ke admin setelah pekerjaan selesai. Sertakan URL hasil kerja atau pastikan dokumen pendukung sudah terhubung.
                    </p>
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-950/30 dark:text-slate-300"
                >
                    Bukti saat ini:
                    <span class="font-extrabold" :class="hasReviewEvidence ? 'text-tm-green dark:text-emerald-300' : 'text-tm-danger dark:text-red-300'">
                        {{ hasReviewEvidence ? 'tersedia' : 'belum ada' }}
                    </span>
                </div>
            </div>

            <form class="mt-4 grid gap-3 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)_auto]" @submit.prevent="submitReview">
                <div class="space-y-1">
                    <label class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary dark:text-slate-400">
                        URL Hasil Pengerjaan
                    </label>
                    <Input
                        v-model="reviewForm.task_url"
                        type="url"
                        placeholder="https://..."
                        class="h-10 rounded-xl border-[1.5px] border-black bg-white text-sm text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] dark:border-slate-600 dark:bg-slate-950/40 dark:text-slate-100"
                        :class="{ 'border-red-500': reviewForm.errors.task_url }"
                    />
                    <p v-if="reviewForm.errors.task_url" class="text-xs font-semibold text-red-500">
                        {{ reviewForm.errors.task_url }}
                    </p>
                </div>

                <div class="space-y-1">
                    <label class="ml-1 text-[11px] font-bold uppercase tracking-wider text-tm-text-secondary dark:text-slate-400">
                        Catatan Pengerjaan
                    </label>
                    <Textarea
                        v-model="reviewForm.note"
                        rows="2"
                        placeholder="Jelaskan singkat apa yang sudah dikerjakan atau bagian yang perlu dicek admin..."
                        class="min-h-10 rounded-xl border-[1.5px] border-black bg-white text-sm text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] dark:border-slate-600 dark:bg-slate-950/40 dark:text-slate-100"
                        :class="{ 'border-red-500': reviewForm.errors.note }"
                    />
                    <p v-if="reviewForm.errors.note" class="text-xs font-semibold text-red-500">
                        {{ reviewForm.errors.note }}
                    </p>
                </div>

                <div class="flex items-end">
                    <Button
                        type="submit"
                        :disabled="reviewForm.processing || !reviewForm.note.trim()"
                        class="h-10 w-full rounded-xl border-[1.5px] border-black bg-tm-green px-4 text-sm font-bold text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] transition-all hover:-translate-y-0.5 hover:bg-tm-green-dark disabled:translate-y-0 disabled:opacity-60 lg:w-auto"
                    >
                        {{ reviewForm.processing ? 'Mengirim...' : 'Ajukan Review' }}
                    </Button>
                </div>
            </form>
        </section>

        <!-- Admin: Review Decision Section -->
        <section
            v-if="permissions.can_review && isReviewPending"
            class="rounded-[18px] border-2 border-emerald-500 bg-white p-5 shadow-[3px_5px_0_0_rgba(0,0,0,0.14)] dark:border-emerald-500/60 dark:bg-[#111c2e] dark:shadow-[0_18px_44px_rgba(0,0,0,0.42)]"
        >
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-extrabold text-tm-navy dark:text-slate-100">
                            Review Pengajuan
                        </h2>
                        <Badge class="rounded-full bg-sky-100 text-sky-700 dark:bg-sky-400/10 dark:text-sky-200">
                            Menunggu keputusan admin
                        </Badge>
                    </div>
                    <p class="mt-1 text-sm leading-6 text-tm-text-secondary dark:text-slate-400">
                        Member telah mengajukan task ini untuk direview. Tandai sebagai selesai atau minta revisi jika ada yang perlu diperbaiki.
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-3">
                    <Button
                        type="button"
                        class="h-10 rounded-xl border-[1.5px] border-black bg-amber-400 px-5 text-sm font-bold text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] transition-all hover:-translate-y-0.5 hover:bg-amber-500 dark:border-amber-300/40"
                        @click="openRevisionDialog"
                    >
                        <RotateCcw class="mr-2 h-4 w-4" />
                        Minta Revisi
                    </Button>
                    <Button
                        type="button"
                        class="h-10 rounded-xl border-[1.5px] border-black bg-tm-green px-5 text-sm font-bold text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] transition-all hover:-translate-y-0.5 hover:bg-tm-green-dark dark:border-emerald-300/40"
                        @click="markCompleted"
                    >
                        <CheckCircle2 class="mr-2 h-4 w-4" />
                        Tandai Selesai
                    </Button>
                </div>
            </div>
        </section>

        <!-- Dialog Revisi -->
        <Dialog :open="revisionDialogOpen" @update:open="(v) => { if (!v) closeRevisionDialog(); }">
            <DialogContent class="overflow-hidden rounded-[18px] border-2 border-black bg-white p-0 shadow-[4px_6px_0_0_rgba(0,0,0,0.22)] sm:max-w-[520px] dark:border-slate-700 dark:bg-[#111c2e] dark:shadow-[0_24px_60px_rgba(0,0,0,0.55)]">
                <form @submit.prevent="submitRevision">
                    <DialogHeader class="border-b border-slate-100 px-5 py-4 text-left dark:border-slate-700/80">
                        <DialogTitle class="text-base font-extrabold text-tm-navy dark:text-slate-100">
                            Minta Revisi Task
                        </DialogTitle>
                        <DialogDescription class="mt-1.5 text-sm leading-6 text-slate-600 dark:text-slate-400">
                            Tulis alasan revisi agar member tahu bagian mana yang perlu diperbaiki.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="space-y-3 px-5 py-4">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-tm-navy dark:border-slate-700 dark:bg-slate-950/35 dark:text-slate-100">
                            {{ task.title }}
                        </div>

                        <div class="space-y-1.5">
                            <Label for="revision-note-show" class="text-xs font-bold uppercase tracking-wide text-tm-navy dark:text-slate-200">
                                Alasan revisi
                            </Label>
                            <Textarea
                                id="revision-note-show"
                                v-model="revisionForm.review_note"
                                rows="5"
                                placeholder="Contoh: URL sudah bisa dibuka, tetapi data faskes belum sesuai dengan requirement..."
                                class="resize-none rounded-xl border-[1.5px] border-black/70 text-sm shadow-[1px_2px_0_0_rgba(0,0,0,0.06)] focus:border-tm-navy focus:ring-1 focus:ring-tm-navy dark:border-slate-600 dark:bg-slate-950/35 dark:text-slate-100"
                                :class="{ '!border-tm-danger': revisionForm.errors.review_note }"
                            />
                            <p v-if="revisionForm.errors.review_note" class="text-xs font-semibold text-tm-danger">
                                {{ revisionForm.errors.review_note }}
                            </p>
                        </div>
                    </div>

                    <DialogFooter class="flex gap-2.5 bg-slate-50/80 px-5 py-4 dark:bg-slate-950/25 sm:justify-end">
                        <Button
                            type="button"
                            variant="outline"
                            class="h-10 rounded-xl border-[1.5px] border-black px-5 shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] dark:border-slate-600 dark:bg-slate-950/25 dark:text-slate-100"
                            @click="closeRevisionDialog"
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            :disabled="revisionForm.processing"
                            class="h-10 rounded-xl border-[1.5px] border-black bg-tm-warning px-5 font-bold text-white shadow-[2px_2px_0_0_rgba(0,0,0,0.18)] hover:bg-amber-500 disabled:opacity-60"
                        >
                            {{ revisionForm.processing ? 'Mengirim...' : 'Kirim Revisi' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

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
                        <div
                            v-if="replyTarget"
                            class="flex items-start justify-between gap-3 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 dark:border-sky-300/30 dark:bg-sky-400/10"
                        >
                            <div class="min-w-0">
                                <p class="text-xs font-extrabold uppercase tracking-wide text-sky-700 dark:text-sky-200">
                                    Membalas {{ replyTarget.user?.name ?? 'komentar' }}
                                </p>
                                <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-600 dark:text-slate-300">
                                    {{ commentExcerpt(replyTarget.body, 140) }}
                                </p>
                            </div>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 shrink-0 rounded-lg text-slate-500 hover:bg-sky-100 dark:text-slate-300 dark:hover:bg-sky-400/10"
                                @click="cancelReply"
                            >
                                <X class="h-4 w-4" />
                            </Button>
                        </div>

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
                        <p
                            v-if="commentForm.errors.reply_to_id"
                            class="text-sm text-red-500"
                        >
                            {{ commentForm.errors.reply_to_id }}
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
                                        : replyTarget
                                          ? 'Kirim Balasan'
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
                            class="flex w-full"
                            :class="isOwnComment(comment) ? 'justify-end' : 'justify-start'"
                        >
                            <div
                                class="flex max-w-[92%] items-start gap-3 sm:max-w-[82%]"
                                :class="isOwnComment(comment) ? 'flex-row-reverse' : 'flex-row'"
                            >
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border-[1.5px] border-black bg-tm-navy-pale text-sm font-extrabold text-tm-navy shadow-[1px_2px_0_0_rgba(0,0,0,0.08)] dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                                >
                                    {{
                                        comment.user?.name
                                            ?.slice(0, 2)
                                            .toUpperCase() ?? '??'
                                    }}
                                </div>

                                <div
                                    class="flex min-w-0 flex-col gap-1.5"
                                    :class="isOwnComment(comment) ? 'items-end text-right' : 'items-start text-left'"
                                >
                                    <div class="min-w-0">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                            :class="isOwnComment(comment) ? 'justify-end' : 'justify-start'"
                                        >
                                            <Badge
                                                v-if="comment.is_pinned"
                                                class="rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300"
                                            >
                                                Disematkan
                                            </Badge>
                                            <p class="font-bold text-tm-navy dark:text-slate-100">
                                                {{ comment.user?.name ?? 'User tidak diketahui' }}
                                            </p>
                                        </div>
                                        <p class="text-xs text-tm-text-secondary dark:text-slate-400">
                                            {{ formatDate(comment.created_at) }}
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-2xl border-[1.5px] px-4 py-3 shadow-[1px_2px_0_0_rgba(0,0,0,0.08)]"
                                        :class="[
                                            isOwnComment(comment)
                                                ? 'rounded-tr-md border-tm-navy bg-tm-navy text-white dark:border-sky-300/35 dark:bg-sky-400/18 dark:text-sky-50'
                                                : 'rounded-tl-md border-slate-200 bg-white text-slate-700 dark:border-slate-700 dark:bg-slate-950/35 dark:text-slate-200',
                                            comment.is_pinned
                                                ? 'ring-2 ring-amber-300/70 dark:ring-amber-300/35'
                                                : '',
                                        ]"
                                    >
                                        <div
                                            v-if="comment.reply_to"
                                            class="mb-2 rounded-xl border-l-4 px-3 py-2 text-left"
                                            :class="
                                                isOwnComment(comment)
                                                    ? 'border-sky-200 bg-white/10 text-sky-50'
                                                    : 'border-sky-400 bg-sky-50 text-slate-600 dark:bg-sky-400/10 dark:text-slate-300'
                                            "
                                        >
                                            <p class="text-[11px] font-extrabold uppercase tracking-wide">
                                                {{ comment.reply_to.user?.name ?? 'Komentar sebelumnya' }}
                                            </p>
                                            <p class="mt-1 line-clamp-2 text-xs leading-5 opacity-90">
                                                {{ commentExcerpt(comment.reply_to.body, 120) }}
                                            </p>
                                        </div>

                                        <p class="text-sm leading-6 whitespace-pre-line">
                                            {{ comment.body }}
                                        </p>
                                    </div>

                                    <div
                                        class="flex items-center gap-1"
                                        :class="isOwnComment(comment) ? 'justify-end' : 'justify-start'"
                                    >
                                        <Button
                                            v-if="permissions.can_comment"
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="h-7 rounded-lg px-2 text-[11px] font-bold text-slate-500 hover:bg-slate-100 hover:text-tm-navy dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100"
                                            @click="startReply(comment)"
                                        >
                                            <Reply class="mr-1 h-3.5 w-3.5" />
                                            Balas
                                        </Button>
                                        <Button
                                            v-if="comment.can_pin"
                                            variant="ghost"
                                            size="icon"
                                            class="h-7 w-7 rounded-lg text-amber-600 hover:bg-amber-100 dark:hover:bg-amber-950/30"
                                            @click="togglePin(comment)"
                                        >
                                            <Pin
                                                v-if="!comment.is_pinned"
                                                class="h-3.5 w-3.5"
                                            />
                                            <PinOff v-else class="h-3.5 w-3.5" />
                                        </Button>
                                        <Button
                                            v-if="comment.can_delete"
                                            variant="ghost"
                                            size="icon"
                                            class="h-7 w-7 rounded-lg text-tm-danger hover:bg-tm-danger-pale dark:hover:bg-red-400/10"
                                            @click="deleteComment(comment)"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
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
