<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRightLeft,
    CalendarDays,
    CheckCircle2,
    Circle,
    CircleAlert,
    Columns3,
    ListTodo,
    LoaderCircle,
    MoreHorizontal,
    Plus,
    Zap,
    RotateCcw,
    Trophy,
} from 'lucide-vue-next';
import { dashboard } from '@/routes';
import {
    create as tasksCreate,
    edit as tasksEdit,
    index as tasksIndex,
    show as tasksShow,
    updateStatus as tasksUpdateStatus,
} from '@/routes/tasks';

// UI Components
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

type TaskStatus = 'open' | 'in_progress' | 'revision' | 'completed';

type TaskItem = {
    id: number;
    title: string;
    category: string;
    priority: 'urgent' | 'high' | 'medium' | 'low';
    status: TaskStatus;
    release_date: string | null;
    completed_at?: string | null;
    client?: { name?: string | null } | null;
    product?: { name?: string | null } | null;
    assignee?: { name?: string | null } | null;
    comments_count?: number;
    can_edit: boolean;
    can_update_status: boolean;
    sla_status?: string;
};

const props = defineProps<{
    tasks: Array<TaskItem>;
    meta: {
        completed_display_limit: number;
        active_count: number;
        completed_count: number;
        completed_shown: number;
        total_count: number;
    };
    permissions: {
        can_create: boolean;
    };
}>();

// Breadcrumbs
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Monitoring Task', href: tasksIndex.url() },
            { title: 'Kanban Board', href: '#' },
        ],
    },
});

// Setup 4 Kolom Status — Neo-brutalist color scheme
const columns: Array<{
    id: TaskStatus;
    title: string;
    subtitle: string;
    icon: typeof Circle;
    panelClass: string;
    headerClass: string;
    ringClass: string;
    shadowColor: string;
    accentColor: string;
}> = [
    {
        id: 'open',
        title: 'Open',
        subtitle: 'Belum mulai',
        icon: Circle,
        panelClass: 'border-[2px] border-[#1B3A6B]/20 bg-white/95 dark:border-sky-300/28 dark:bg-[#111c2e]',
        headerClass: 'bg-[#E8EEF8] text-[#1B3A6B] dark:bg-sky-400/10 dark:text-sky-100',
        ringClass: 'ring-[#1B3A6B]/40',
        shadowColor: 'shadow-[3px_4px_0px_0px_rgba(27,58,107,0.15)] dark:shadow-[0_14px_32px_rgba(0,0,0,0.42),0_0_0_1px_rgba(148,163,184,0.08)]',
        accentColor: '#1B3A6B',
    },
    {
        id: 'in_progress',
        title: 'In Progress',
        subtitle: 'Sedang dikerjakan',
        icon: Zap,
        panelClass: 'border-[2px] border-[#0369A1]/30 bg-sky-50/80 dark:border-sky-300/35 dark:bg-sky-400/10',
        headerClass: 'bg-[#E0F2FE] text-[#0369A1] dark:bg-sky-400/12 dark:text-sky-100',
        ringClass: 'ring-[#0369A1]/40',
        shadowColor: 'shadow-[3px_4px_0px_0px_rgba(3,105,161,0.15)] dark:shadow-[0_14px_32px_rgba(0,0,0,0.42),0_0_0_1px_rgba(56,189,248,0.14)]',
        accentColor: '#0369A1',
    },
    {
        id: 'revision',
        title: 'Revision',
        subtitle: 'Butuh tindak lanjut',
        icon: RotateCcw,
        panelClass: 'border-[2px] border-[#D97706]/30 bg-amber-50/80 dark:border-amber-300/35 dark:bg-amber-400/10',
        headerClass: 'bg-[#FEF3C7] text-[#92400E] dark:bg-amber-400/12 dark:text-amber-100',
        ringClass: 'ring-[#D97706]/40',
        shadowColor: 'shadow-[3px_4px_0px_0px_rgba(217,119,6,0.15)] dark:shadow-[0_14px_32px_rgba(0,0,0,0.42),0_0_0_1px_rgba(251,191,36,0.14)]',
        accentColor: '#D97706',
    },
    {
        id: 'completed',
        title: 'Completed',
        subtitle: `${props.meta.completed_shown} dari ${props.meta.completed_count} ditampilkan`,
        icon: Trophy,
        panelClass: 'border-[2px] border-[#2BAE6E]/30 bg-emerald-50/80 dark:border-emerald-300/35 dark:bg-emerald-400/10',
        headerClass: 'bg-[#E4F7ED] text-[#166534] dark:bg-emerald-400/12 dark:text-emerald-100',
        ringClass: 'ring-[#2BAE6E]/40',
        shadowColor: 'shadow-[3px_4px_0px_0px_rgba(43,174,110,0.15)] dark:shadow-[0_14px_32px_rgba(0,0,0,0.42),0_0_0_1px_rgba(52,211,153,0.14)]',
        accentColor: '#2BAE6E',
    },
];

const cloneTask = (task: TaskItem): TaskItem => ({ ...task });
const boardTasks = ref(props.tasks.map(cloneTask));
const draggedTaskId = ref<number | null>(null);
const hoveredColumnId = ref<TaskStatus | null>(null);
const processingTaskIds = ref<number[]>([]);
const syncMessage = ref<{ type: 'error' | 'success'; text: string } | null>(null);

watch(
    () => props.tasks,
    (tasks) => {
        boardTasks.value = tasks.map(cloneTask);
    },
    { deep: true },
);

const statusLabelMap: Record<TaskStatus, string> = {
    open: 'Open',
    in_progress: 'In Progress',
    revision: 'Revision',
    completed: 'Completed',
};

const statusActionOrder: TaskStatus[] = ['open', 'in_progress', 'revision', 'completed'];

const isTaskProcessing = (taskId: number) => processingTaskIds.value.includes(taskId);

const setTaskProcessing = (taskId: number, processing: boolean) => {
    if (processing) {
        if (!processingTaskIds.value.includes(taskId)) {
            processingTaskIds.value = [...processingTaskIds.value, taskId];
        }
        return;
    }
    processingTaskIds.value = processingTaskIds.value.filter((id) => id !== taskId);
};

const parseDateValue = (value: string | null | undefined) => {
    if (!value) return Number.POSITIVE_INFINITY;
    return new Date(value).getTime();
};

const formatDate = (value: string | null | undefined) => {
    if (!value) return 'Belum dijadwalkan';
    return new Date(value).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const isOverdue = (task: TaskItem) => {
    if (!task.release_date || task.status === 'completed') return false;
    return parseDateValue(task.release_date) < new Date().setHours(0, 0, 0, 0);
};

const compareTasks = (left: TaskItem, right: TaskItem) => {
    if (left.status === 'completed' && right.status === 'completed') {
        return parseDateValue(right.completed_at) - parseDateValue(left.completed_at);
    }
    const dueDateSort = parseDateValue(left.release_date) - parseDateValue(right.release_date);
    if (dueDateSort !== 0) return dueDateSort;
    return left.id - right.id;
};

const groupedTasks = computed(() => {
    const groups: Record<TaskStatus, TaskItem[]> = { open: [], in_progress: [], revision: [], completed: [] };
    boardTasks.value.forEach((task) => {
        if (groups[task.status]) groups[task.status].push(task);
    });
    Object.values(groups).forEach((tasks) => tasks.sort(compareTasks));
    return groups;
});

const updateTaskStatus = (taskId: number, newStatus: TaskStatus) => {
    const task = boardTasks.value.find((item) => item.id === taskId);
    if (!task || !task.can_update_status || task.status === newStatus || isTaskProcessing(taskId)) return;

    const previousStatus = task.status;
    task.status = newStatus;
    syncMessage.value = null;
    setTaskProcessing(taskId, true);

    router.patch(
        tasksUpdateStatus.url(taskId),
        { status: newStatus },
        {
            preserveScroll: true,
            preserveState: true,
            onError: () => {
                task.status = previousStatus;
                syncMessage.value = {
                    type: 'error',
                    text: `Status "${task.title}" gagal diperbarui. Silakan coba lagi.`,
                };
            },
            onSuccess: () => {
                syncMessage.value = {
                    type: 'success',
                    text: `Status "${task.title}" dipindahkan ke ${statusLabelMap[newStatus]}.`,
                };
            },
            onFinish: () => setTaskProcessing(taskId, false),
        },
    );
};

const onDragStart = (e: DragEvent, taskId: number) => {
    const task = boardTasks.value.find((item) => item.id === taskId);
    if (!task?.can_update_status || isTaskProcessing(taskId)) return;
    draggedTaskId.value = taskId;
    if (e.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', taskId.toString());
    }
};

const onDragEnd = () => {
    draggedTaskId.value = null;
    hoveredColumnId.value = null;
};

const scrollColumnWhileDragging = (e: DragEvent) => {
    const column = e.currentTarget as HTMLElement | null;
    const scroller = column?.querySelector<HTMLElement>('.kanban-column-scroll');
    if (!scroller) return;

    const rect = scroller.getBoundingClientRect();
    const distanceFromTop = e.clientY - rect.top;
    const distanceFromBottom = rect.bottom - e.clientY;
    const edgeSize = 96;

    if (distanceFromTop < edgeSize) {
        scroller.scrollBy({ top: -14, behavior: 'auto' });
    } else if (distanceFromBottom < edgeSize) {
        scroller.scrollBy({ top: 14, behavior: 'auto' });
    }
};

const onDragOver = (e: DragEvent, columnId: TaskStatus) => {
    e.preventDefault();
    if (draggedTaskId.value !== null) {
        hoveredColumnId.value = columnId;
        scrollColumnWhileDragging(e);
    }
};

const onDrop = (e: DragEvent, newStatus: TaskStatus) => {
    e.preventDefault();
    if (!draggedTaskId.value) return;
    const taskId = draggedTaskId.value;
    hoveredColumnId.value = null;
    updateTaskStatus(taskId, newStatus);
    draggedTaskId.value = null;
};
</script>

<template>
    <Head title="Kanban Board" />

    <div class="flex h-full min-h-0 flex-1 flex-col gap-6 overflow-hidden bg-tm-page px-4 py-5 dark:bg-[#081422] md:px-6 md:py-7 xl:px-7">
        <!-- Sync Message Toast -->
        <div
            v-if="syncMessage"
            class="flex flex-col gap-3 rounded-[14px] border-[2px] px-5 py-3.5 text-sm font-medium sm:flex-row sm:items-center sm:justify-between"
            :class="
                syncMessage.type === 'error'
                    ? 'border-[#E84545]/40 bg-[#FDEAEA] text-[#A32D2D] shadow-[2px_3px_0px_0px_rgba(232,69,69,0.2)] dark:border-red-300/35 dark:bg-red-400/10 dark:text-red-100'
                    : 'border-[#2BAE6E]/40 bg-[#E4F7ED] text-[#166534] shadow-[2px_3px_0px_0px_rgba(43,174,110,0.2)] dark:border-emerald-300/35 dark:bg-emerald-400/10 dark:text-emerald-100'
            "
        >
            <span>{{ syncMessage.text }}</span>
            <button
                class="text-left text-xs font-bold uppercase tracking-wider opacity-80 transition hover:opacity-100 sm:text-right"
                @click="syncMessage = null"
            >
                Tutup
            </button>
        </div>

        <!-- Page Header — Neo-brutalist -->
        <div class="flex shrink-0 flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <h1 class="flex items-center gap-3 text-2xl font-extrabold tracking-tight text-[#1B3A6B] dark:text-white md:text-3xl">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl border-[2px] border-black bg-[#E8EEF8] shadow-[2px_2px_0px_0px_rgba(27,58,107,0.2)] dark:border-slate-600 dark:bg-[#111c2e] dark:shadow-[0_10px_24px_rgba(0,0,0,0.35)]">
                        <Columns3 class="h-5 w-5 text-[#1B3A6B] dark:text-white" />
                    </div>
                    Papan Kanban
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-[#5C6B7A] dark:text-slate-400">
                    Geser task antar kolom untuk memperbarui status. Di mobile atau saat butuh kontrol lebih presisi,
                    gunakan menu aksi pada setiap kartu.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <Link v-if="permissions.can_create" :href="tasksCreate.url()">
                    <Button class="flex items-center gap-2 rounded-xl border-[2px] border-black bg-[#2BAE6E] px-4 py-2.5 text-sm font-bold text-white shadow-[3px_3px_0px_0px_rgba(0,0,0,0.2)] transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#1E8A54] hover:shadow-[4px_4px_0px_0px_rgba(0,0,0,0.25)] dark:border-emerald-300/40 dark:shadow-[0_12px_28px_rgba(16,185,129,0.2)]">
                        <Plus class="h-4 w-4" :stroke-width="2.5" />
                        Task Baru
                    </Button>
                </Link>
                <Link :href="tasksIndex.url()">
                    <Button variant="outline" class="flex items-center gap-2 rounded-xl border-[2px] border-[#1B3A6B]/30 bg-white px-4 py-2.5 text-sm font-bold text-[#1B3A6B] shadow-[2px_2px_0px_0px_rgba(27,58,107,0.1)] transition-all duration-200 hover:-translate-y-0.5 hover:border-[#1B3A6B]/50 hover:shadow-[3px_3px_0px_0px_rgba(27,58,107,0.15)] dark:border-slate-600 dark:bg-[#111c2e] dark:text-slate-100 dark:shadow-[0_10px_24px_rgba(0,0,0,0.35)] dark:hover:bg-slate-800/70">
                        <ListTodo class="h-4 w-4" />
                        Mode Tabel
                    </Button>
                </Link>
            </div>
        </div>

        <!-- Stat Cards — Neo-brutalist style -->
        <div class="grid w-full shrink-0 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <!-- Task Aktif -->
            <div class="relative overflow-hidden rounded-[16px] border-[2px] border-black bg-white p-5 shadow-[3px_4px_0px_0px_rgba(27,58,107,0.12)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[4px_5px_0px_0px_rgba(27,58,107,0.16)] dark:border-slate-700/80 dark:bg-[#111c2e] dark:shadow-[0_14px_32px_rgba(0,0,0,0.42),0_0_0_1px_rgba(148,163,184,0.08)]">
                <div class="mb-3 h-[3px] w-10 rounded-full bg-[#1B3A6B]"></div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#5C6B7A] dark:text-slate-400">Task Aktif</p>
                <p class="mt-1.5 text-3xl font-extrabold text-[#1B3A6B] dark:text-white">{{ meta.active_count }}</p>
                <p class="mt-1 text-xs text-[#5C6B7A] dark:text-slate-500">Open, in progress, dan revision.</p>
            </div>
            <!-- Completed Window -->
            <div class="relative overflow-hidden rounded-[16px] border-[2px] border-black bg-white p-5 shadow-[3px_4px_0px_0px_rgba(43,174,110,0.15)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[4px_5px_0px_0px_rgba(43,174,110,0.2)] dark:border-slate-700/80 dark:bg-[#111c2e] dark:shadow-[0_14px_32px_rgba(0,0,0,0.42),0_0_0_1px_rgba(52,211,153,0.12)]">
                <div class="mb-3 h-[3px] w-10 rounded-full bg-[#2BAE6E]"></div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#2BAE6E]">Completed</p>
                <p class="mt-1.5 text-3xl font-extrabold text-[#1B3A6B] dark:text-white">{{ meta.completed_count }}</p>
                <p class="mt-1 text-xs text-[#5C6B7A] dark:text-slate-500">{{ meta.completed_shown }} terbaru ditampilkan di board.</p>
            </div>
            <!-- Total Ditampilkan -->
            <div class="relative overflow-hidden rounded-[16px] border-[2px] border-black bg-white p-5 shadow-[3px_4px_0px_0px_rgba(3,105,161,0.12)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[4px_5px_0px_0px_rgba(3,105,161,0.16)] dark:border-slate-700/80 dark:bg-[#111c2e] dark:shadow-[0_14px_32px_rgba(0,0,0,0.42),0_0_0_1px_rgba(56,189,248,0.12)]">
                <div class="mb-3 h-[3px] w-10 rounded-full bg-[#0369A1]"></div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#0369A1]">Total Task</p>
                <p class="mt-1.5 text-3xl font-extrabold text-[#1B3A6B] dark:text-white">{{ meta.total_count }}</p>
                <p class="mt-1 text-xs text-[#5C6B7A] dark:text-slate-500">Semua task aktif + completed.</p>
            </div>
            <!-- Mode Interaksi -->
            <div class="relative overflow-hidden rounded-[16px] border-[2px] border-black bg-white p-5 shadow-[3px_4px_0px_0px_rgba(217,119,6,0.12)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[4px_5px_0px_0px_rgba(217,119,6,0.16)] dark:border-slate-700/80 dark:bg-[#111c2e] dark:shadow-[0_14px_32px_rgba(0,0,0,0.42),0_0_0_1px_rgba(251,191,36,0.12)]">
                <div class="mb-3 h-[3px] w-10 rounded-full bg-[#D97706]"></div>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#D97706]">Mode Interaksi</p>
                <p class="mt-1.5 text-lg font-extrabold text-[#1B3A6B] dark:text-white">Drag + Quick Actions</p>
                <p class="mt-1 text-xs text-[#5C6B7A] dark:text-slate-500">Lebih stabil di desktop, tetap usable di mobile.</p>
            </div>
        </div>

        <!-- Kanban Columns -->
        <div class="-mx-1 min-h-0 flex-1 overflow-x-auto overflow-y-hidden px-1 pb-4">
            <div class="grid h-full min-h-0 min-w-[76rem] grid-cols-4 items-stretch gap-4 xl:min-w-0 xl:gap-5">
                <div
                    v-for="column in columns"
                    :key="column.id"
                    class="relative flex max-h-full min-h-0 min-w-0 flex-col rounded-[18px] transition-all duration-200"
                    :class="[
                        column.panelClass,
                        column.shadowColor,
                        hoveredColumnId === column.id ? `${column.ringClass} scale-[1.01] ring-2 ring-offset-2 ring-offset-transparent` : '',
                    ]"
                    @dragover="(e) => onDragOver(e, column.id)"
                    @drop="(e) => onDrop(e, column.id)"
                >
                    <div
                        v-if="hoveredColumnId === column.id && draggedTaskId"
                        class="pointer-events-none absolute inset-1 z-20 rounded-[15px] border-[2px] border-dashed border-[#1B3A6B]/35 bg-white/20 dark:border-sky-200/35 dark:bg-sky-300/5"
                    />

                    <!-- Column Header -->
                    <div
                        class="relative z-10 shrink-0 overflow-hidden rounded-t-[16px] border-b-[2.5px] p-4 shadow-[0_1px_0_0_rgba(15,23,42,0.04)]"
                        :class="column.headerClass"
                        :style="{ borderBottomColor: column.accentColor }"
                    >
                        <!-- Left accent bar -->
                        <div
                            class="absolute left-0 top-3 bottom-3 w-[3.5px] rounded-r-full"
                            :style="{ backgroundColor: column.accentColor }"
                        />
                        <div class="flex items-center justify-between gap-3 pl-2">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-[10px] border-[2px] border-black/80 bg-white shadow-[1.5px_1.5px_0px_0px_rgba(0,0,0,0.15)] dark:border-slate-600 dark:bg-slate-950/35"
                                >
                                    <component :is="column.icon" class="h-4 w-4" :style="{ color: column.accentColor }" :stroke-width="2.5" />
                                </div>
                                <div>
                                    <h3 class="text-[13px] font-extrabold uppercase tracking-[0.14em]">{{ column.title }}</h3>
                                    <p class="text-[10px] font-semibold opacity-60">{{ column.subtitle }}</p>
                                </div>
                            </div>
                            <span
                                class="flex h-8 min-w-[32px] items-center justify-center rounded-full border-[2px] border-black/70 bg-white px-2.5 text-xs font-extrabold shadow-[1.5px_1.5px_0px_0px_rgba(0,0,0,0.1)] dark:border-slate-600 dark:bg-slate-950/45"
                                :style="{ color: column.accentColor }"
                            >
                                {{ groupedTasks[column.id].length }}
                            </span>
                        </div>
                    </div>

                    <!-- Task Cards Container -->
                    <div class="kanban-column-scroll flex min-h-0 flex-1 scroll-pb-6 flex-col gap-3 overflow-y-auto overscroll-contain p-3 pr-2 pb-6">
                        <div
                            v-for="task in groupedTasks[column.id]"
                            :key="task.id"
                            :draggable="task.can_update_status && !isTaskProcessing(task.id)"
                            class="group rounded-[14px] border-[1.5px] border-[#DDE3EC] bg-white p-4 transition-all duration-200 dark:border-slate-700/80 dark:bg-slate-950/45 dark:shadow-[0_10px_22px_rgba(0,0,0,0.25)]"
                            :class="[
                                task.can_update_status && !isTaskProcessing(task.id)
                                    ? 'cursor-grab hover:-translate-y-1 hover:border-[#1B3A6B]/40 hover:shadow-[2px_4px_8px_0px_rgba(27,58,107,0.12)] active:cursor-grabbing active:scale-[0.98] dark:hover:border-sky-300/35 dark:hover:bg-slate-900/80 dark:hover:shadow-[0_14px_28px_rgba(0,0,0,0.34)]'
                                    : 'cursor-default',
                                draggedTaskId === task.id ? 'scale-[0.96] opacity-50 shadow-none border-dashed' : '',
                                isTaskProcessing(task.id) ? 'pointer-events-none opacity-60' : '',
                            ]"
                            @dragstart="(e) => onDragStart(e, task.id)"
                            @dragend="onDragEnd"
                        >
                            <!-- Card Top: Category + Priority -->
                            <div class="mb-2.5 flex items-start justify-between gap-2">
                                <div class="flex min-w-0 flex-wrap items-center gap-1.5">
                                    <Badge variant="outline" class="truncate rounded-full border-[1.5px] border-[#DDE3EC] bg-[#F0F3F7] px-2 py-0.5 text-[10px] font-bold text-[#1B3A6B] dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600">
                                        {{ task.category }}
                                    </Badge>
                                    <span
                                        v-if="task.sla_status"
                                        class="inline-flex items-center rounded-full border-[1.5px] px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide"
                                        :class="{
                                            'border-emerald-300 bg-[#E4F7ED] text-[#166534] dark:border-emerald-300/35 dark:bg-emerald-400/10 dark:text-emerald-200': task.sla_status === 'completed_on_time',
                                            'border-amber-300 bg-[#FEF3DC] text-[#92400E] dark:border-amber-300/35 dark:bg-amber-400/10 dark:text-amber-200': task.sla_status === 'completed_late',
                                            'border-blue-300 bg-[#E8EEF8] text-[#1B3A6B] dark:border-blue-300/35 dark:bg-blue-400/10 dark:text-blue-200': task.sla_status === 'on_track',
                                            'border-orange-300 bg-[#FEF3DC] text-[#9A3412] dark:border-orange-300/35 dark:bg-orange-400/10 dark:text-orange-200': task.sla_status === 'warning',
                                            'border-red-300 bg-[#FDEAEA] text-[#A32D2D] dark:border-red-300/35 dark:bg-red-400/10 dark:text-red-200': task.sla_status === 'overdue',
                                            'border-slate-300 bg-[#F0F3F7] text-[#5C6B7A] dark:border-slate-600 dark:bg-slate-800/65 dark:text-slate-400': task.sla_status === 'unknown',
                                        }"
                                    >
                                        {{ task.sla_status === 'on_track' ? 'ON TRACK' : task.sla_status === 'overdue' ? 'OVERDUE' : task.sla_status === 'warning' ? 'WARNING' : task.sla_status === 'completed_on_time' ? 'ON TIME' : task.sla_status === 'completed_late' ? 'LATE' : 'UNKNOWN' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="h-2.5 w-2.5 shrink-0 rounded-full ring-2 ring-white shadow-sm dark:ring-slate-950"
                                        :class="{
                                            'bg-[#E84545]': task.priority === 'urgent',
                                            'bg-[#F59E0B]': task.priority === 'high',
                                            'bg-[#0369A1]': task.priority === 'medium',
                                            'bg-[#9AAAB8]': task.priority === 'low',
                                        }"
                                        :title="`Priority: ${task.priority}`"
                                    />
                                    <LoaderCircle
                                        v-if="isTaskProcessing(task.id)"
                                        class="h-4 w-4 animate-spin text-[#2BAE6E]"
                                    />
                                </div>
                            </div>

                            <!-- Task Title -->
                            <h4 class="line-clamp-2 text-[13px] font-bold leading-5 text-[#1B3A6B] dark:text-white">
                                <Link :href="tasksShow.url(task.id)" class="transition-colors duration-150 hover:text-[#2BAE6E]">
                                    {{ task.title }}
                                </Link>
                            </h4>

                            <!-- Task Meta -->
                            <div class="mt-2.5 space-y-1.5 text-[11px] text-[#5C6B7A] dark:text-slate-400">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-[#1B3A6B]/70 dark:text-slate-300">Client</span>
                                    <span class="truncate">{{ task.client?.name || '-' }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-[#1B3A6B]/70 dark:text-slate-300">Product</span>
                                    <span class="truncate">{{ task.product?.name || '-' }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <CalendarDays class="h-3 w-3 shrink-0" />
                                    <span :class="isOverdue(task) ? 'font-bold text-[#E84545] dark:text-red-300' : ''">
                                        {{ formatDate(task.release_date) }}
                                    </span>
                                    <span
                                        v-if="isOverdue(task)"
                                        class="rounded-full border border-[#E84545]/30 bg-[#FDEAEA] px-1.5 py-0.5 text-[9px] font-bold text-[#A32D2D] dark:border-red-300/35 dark:bg-red-400/10 dark:text-red-200"
                                    >
                                        Overdue
                                    </span>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="mt-3 flex items-center justify-between gap-2 border-t border-[#DDE3EC] pt-3 dark:border-slate-700">
                                <div class="flex min-w-0 items-center gap-1.5">
                                    <span class="rounded-full border border-[#DDE3EC] bg-[#F0F3F7] px-2 py-0.5 text-[10px] font-semibold text-[#5C6B7A] dark:bg-slate-800 dark:text-slate-400 dark:border-slate-600">
                                        {{ task.comments_count ?? 0 }} komentar
                                    </span>
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold"
                                        :class="{
                                            'border-[#1B3A6B]/20 bg-[#E8EEF8] text-[#1B3A6B] dark:border-sky-300/35 dark:bg-sky-400/10 dark:text-sky-200': task.status === 'open',
                                            'border-[#0369A1]/20 bg-[#E0F2FE] text-[#0369A1] dark:border-blue-300/35 dark:bg-blue-400/10 dark:text-blue-200': task.status === 'in_progress',
                                            'border-[#D97706]/20 bg-[#FEF3DC] text-[#92400E] dark:border-amber-300/35 dark:bg-amber-400/10 dark:text-amber-200': task.status === 'revision',
                                            'border-[#2BAE6E]/20 bg-[#E4F7ED] text-[#166534] dark:border-emerald-300/35 dark:bg-emerald-400/10 dark:text-emerald-200': task.status === 'completed',
                                        }"
                                    >
                                        <Circle v-if="task.status === 'open'" class="h-2.5 w-2.5 fill-current" />
                                        <ArrowRightLeft v-else-if="task.status === 'in_progress'" class="h-2.5 w-2.5" />
                                        <CircleAlert v-else-if="task.status === 'revision'" class="h-2.5 w-2.5" />
                                        <CheckCircle2 v-else class="h-2.5 w-2.5" />
                                        {{ statusLabelMap[task.status] }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-1">
                                    <Link v-if="task.can_edit" :href="tasksEdit.url(task.id)" class="hidden sm:block">
                                        <Button variant="ghost" size="sm" class="h-7 rounded-lg px-2.5 text-[11px] font-bold text-[#1B3A6B] opacity-0 transition-all group-hover:opacity-100 hover:bg-[#E8EEF8] dark:text-white dark:hover:bg-slate-800">
                                            Edit
                                        </Button>
                                    </Link>

                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-7 w-7 rounded-lg text-[#5C6B7A] opacity-60 transition-all group-hover:opacity-100 hover:bg-[#F0F3F7] hover:text-[#1B3A6B] dark:hover:bg-slate-800">
                                                <MoreHorizontal class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" class="w-48 rounded-xl border-[1.5px] border-[#DDE3EC] shadow-[3px_4px_12px_0px_rgba(27,58,107,0.12)] dark:border-slate-700 dark:bg-[#111c2e] dark:text-slate-100">
                                            <DropdownMenuItem
                                                v-for="statusOption in statusActionOrder"
                                                :key="statusOption"
                                                :disabled="!task.can_update_status || statusOption === task.status || isTaskProcessing(task.id)"
                                                class="cursor-pointer rounded-lg text-[13px] font-medium"
                                                @select="updateTaskStatus(task.id, statusOption)"
                                            >
                                                Pindahkan ke {{ statusLabelMap[statusOption] }}
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                v-if="task.can_edit"
                                                class="cursor-pointer rounded-lg text-[13px] font-medium sm:hidden"
                                                @select="router.get(tasksEdit.url(task.id))"
                                            >
                                                Edit task
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div
                            v-if="groupedTasks[column.id].length === 0"
                            class="flex min-h-[10rem] flex-1 items-center justify-center rounded-[14px] border-[2px] border-dashed border-[#DDE3EC] bg-[#F0F3F7]/50 px-4 text-center text-sm font-medium text-[#9AAAB8] dark:border-slate-700 dark:bg-slate-900/30 dark:text-slate-500"
                        >
                            Belum ada task di kolom ini.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.kanban-column-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgb(27 58 107 / 0.22) transparent;
}

.kanban-column-scroll::-webkit-scrollbar {
    width: 8px;
}

.kanban-column-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.kanban-column-scroll::-webkit-scrollbar-thumb {
    border: 2px solid transparent;
    border-radius: 999px;
    background-clip: content-box;
    background-color: rgb(27 58 107 / 0.22);
}

.kanban-column-scroll:hover::-webkit-scrollbar-thumb {
    background-color: rgb(27 58 107 / 0.38);
}

:global(.dark) .kanban-column-scroll {
    scrollbar-color: rgb(148 163 184 / 0.26) transparent;
}

:global(.dark) .kanban-column-scroll::-webkit-scrollbar-thumb {
    background-color: rgb(148 163 184 / 0.26);
}

:global(.dark) .kanban-column-scroll:hover::-webkit-scrollbar-thumb {
    background-color: rgb(148 163 184 / 0.42);
}
</style>
