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
    GripVertical,
    ListTodo,
    LoaderCircle,
    MoreHorizontal,
    Plus,
} from 'lucide-vue-next';
import { dashboard } from '@/routes';
import {
    create as tasksCreate,
    edit as tasksEdit,
    index as tasksIndex,
    show as tasksShow,
    updateStatus as tasksUpdateStatus,
} from '@/actions/App/Http/Controllers/TaskController';

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
};

const props = defineProps<{
    tasks: Array<TaskItem>;
    meta: {
        completed_window_days: number;
        active_count: number;
        recent_completed_count: number;
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

// Setup 4 Kolom Status
const columns: Array<{
    id: TaskStatus;
    title: string;
    subtitle: string;
    panelClass: string;
    headerClass: string;
    ringClass: string;
}> = [
    {
        id: 'open',
        title: 'Open',
        subtitle: 'Belum mulai',
        panelClass: 'border-slate-200/80 bg-white/90',
        headerClass: 'bg-slate-100 text-slate-700',
        ringClass: 'ring-slate-300/70',
    },
    {
        id: 'in_progress',
        title: 'In Progress',
        subtitle: 'Sedang dikerjakan',
        panelClass: 'border-sky-200/80 bg-sky-50/60',
        headerClass: 'bg-sky-100 text-sky-800',
        ringClass: 'ring-sky-300/70',
    },
    {
        id: 'revision',
        title: 'Revision',
        subtitle: 'Butuh tindak lanjut',
        panelClass: 'border-amber-200/80 bg-amber-50/70',
        headerClass: 'bg-amber-100 text-amber-800',
        ringClass: 'ring-amber-300/70',
    },
    {
        id: 'completed',
        title: 'Completed',
        subtitle: `Selesai ${props.meta.completed_window_days} hari terakhir`,
        panelClass: 'border-emerald-200/80 bg-emerald-50/70',
        headerClass: 'bg-emerald-100 text-emerald-800',
        ringClass: 'ring-emerald-300/70',
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
    if (!value) {
        return Number.POSITIVE_INFINITY;
    }

    return new Date(value).getTime();
};

const formatDate = (value: string | null | undefined) => {
    if (!value) {
        return 'Belum dijadwalkan';
    }

    return new Date(value).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const isOverdue = (task: TaskItem) => {
    if (!task.release_date || task.status === 'completed') {
        return false;
    }

    return parseDateValue(task.release_date) < new Date().setHours(0, 0, 0, 0);
};

const compareTasks = (left: TaskItem, right: TaskItem) => {
    if (left.status === 'completed' && right.status === 'completed') {
        return parseDateValue(right.completed_at) - parseDateValue(left.completed_at);
    }

    const dueDateSort = parseDateValue(left.release_date) - parseDateValue(right.release_date);

    if (dueDateSort !== 0) {
        return dueDateSort;
    }

    return left.id - right.id;
};

// Memecah data dari Database ke dalam masing-masing kotak status
const groupedTasks = computed(() => {
    const groups: Record<TaskStatus, TaskItem[]> = { open: [], in_progress: [], revision: [], completed: [] };

    boardTasks.value.forEach((task) => {
        if (groups[task.status]) {
            groups[task.status].push(task);
        }
    });

    Object.values(groups).forEach((tasks) => {
        tasks.sort(compareTasks);
    });

    return groups;
});

const updateTaskStatus = (taskId: number, newStatus: TaskStatus) => {
    const task = boardTasks.value.find((item) => item.id === taskId);

    if (!task || !task.can_update_status || task.status === newStatus || isTaskProcessing(taskId)) {
        return;
    }

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
            onFinish: () => {
                setTaskProcessing(taskId, false);
            },
        },
    );
};

// 1. Saat Tiket mulai ditarik
const onDragStart = (e: DragEvent, taskId: number) => {
    const task = boardTasks.value.find((item) => item.id === taskId);
    if (!task?.can_update_status || isTaskProcessing(taskId)) {
        return;
    }

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

// 2. Izin melintas di atas kolom lain
const onDragOver = (e: DragEvent, columnId: TaskStatus) => {
    e.preventDefault();

    if (draggedTaskId.value !== null) {
        hoveredColumnId.value = columnId;
    }
};

// 3. Saat Tiket dilepaskan (Jatuh) di kolom baru
const onDrop = (e: DragEvent, newStatus: TaskStatus) => {
    e.preventDefault();
    if (!draggedTaskId.value) {
        return;
    }

    const taskId = draggedTaskId.value;
    hoveredColumnId.value = null;
    updateTaskStatus(taskId, newStatus);
    draggedTaskId.value = null;
};
</script>

<template>
    <Head title="Kanban Board" />

    <div class="flex h-full flex-1 flex-col gap-6 bg-slate-50/40 p-4 md:p-8 dark:bg-slate-950/30">
        <div
            v-if="syncMessage"
            class="flex flex-col gap-3 rounded-2xl border px-4 py-3 text-sm shadow-sm sm:flex-row sm:items-center sm:justify-between"
            :class="
                syncMessage.type === 'error'
                    ? 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300'
                    : 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300'
            "
        >
            <span>{{ syncMessage.text }}</span>
            <button
                class="text-left text-xs font-semibold uppercase tracking-wide opacity-80 transition hover:opacity-100 sm:text-right"
                @click="syncMessage = null"
            >
                Tutup
            </button>
        </div>

        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <h1 class="flex items-center gap-3 text-3xl font-bold tracking-tight text-primary">
                    <Columns3 class="h-8 w-8 text-sky-600" />
                    Papan Kanban
                </h1>
                <p class="mt-2 max-w-2xl text-sm text-muted-foreground">
                    Geser task antar kolom untuk memperbarui status. Di mobile atau saat butuh kontrol lebih presisi,
                    gunakan menu aksi pada setiap kartu.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <Link v-if="permissions.can_create" :href="tasksCreate.url()">
                    <Button class="flex items-center gap-2 rounded-xl bg-sky-600 text-white shadow-sm transition hover:bg-sky-500">
                        <Plus class="h-4 w-4" />
                        Task Baru
                    </Button>
                </Link>
                <Link :href="tasksIndex.url()">
                    <Button variant="outline" class="flex items-center gap-2 rounded-xl border-slate-300 bg-white/90">
                        <ListTodo class="h-4 w-4" />
                        Mode Tabel
                    </Button>
                </Link>
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Task Aktif</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ meta.active_count }}</p>
                <p class="mt-1 text-sm text-slate-500">Open, in progress, dan revision.</p>
            </div>
            <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/80 p-4 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">Completed Window</p>
                <p class="mt-2 text-3xl font-semibold text-emerald-800 dark:text-emerald-200">{{ meta.recent_completed_count }}</p>
                <p class="mt-1 text-sm text-emerald-700/80 dark:text-emerald-300/80">
                    Task selesai {{ meta.completed_window_days }} hari terakhir.
                </p>
            </div>
            <div class="rounded-2xl border border-sky-200/80 bg-sky-50/80 p-4 shadow-sm dark:border-sky-900/40 dark:bg-sky-950/20">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-700 dark:text-sky-300">Total Ditampilkan</p>
                <p class="mt-2 text-3xl font-semibold text-sky-800 dark:text-sky-200">{{ meta.total_count }}</p>
                <p class="mt-1 text-sm text-sky-700/80 dark:text-sky-300/80">Board ini memang fokus ke pekerjaan yang relevan sekarang.</p>
            </div>
            <div class="rounded-2xl border border-amber-200/80 bg-amber-50/80 p-4 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-700 dark:text-amber-300">Mode Interaksi</p>
                <p class="mt-2 text-lg font-semibold text-amber-800 dark:text-amber-200">Drag + Quick Actions</p>
                <p class="mt-1 text-sm text-amber-700/80 dark:text-amber-300/80">Lebih stabil di desktop, tetap usable di mobile.</p>
            </div>
        </div>

        <div class="flex-1 overflow-x-auto overflow-y-hidden pb-4">
            <div class="flex min-h-full min-w-max items-start gap-5">
                <div
                    v-for="column in columns"
                    :key="column.id"
                    class="flex max-h-full w-[22rem] flex-col rounded-[20px] border shadow-sm transition-all duration-200"
                    :class="[
                        column.panelClass,
                        hoveredColumnId === column.id ? `${column.ringClass} ring-2 ring-offset-2 ring-offset-slate-50 dark:ring-offset-slate-950` : '',
                    ]"
                    @dragover="(e) => onDragOver(e, column.id)"
                    @drop="(e) => onDrop(e, column.id)"
                >
                    <div class="shrink-0 border-b border-black/5 p-4" :class="`${column.headerClass} rounded-t-[20px]`">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-bold uppercase tracking-[0.14em]">{{ column.title }}</h3>
                                <p class="mt-1 text-xs font-medium opacity-80">{{ column.subtitle }}</p>
                            </div>
                            <span class="rounded-full bg-white/70 px-2.5 py-1 text-xs font-bold text-slate-700 shadow-sm dark:bg-slate-950/30 dark:text-slate-100">
                            {{ groupedTasks[column.id].length }}
                            </span>
                        </div>
                    </div>

                    <div class="flex min-h-[18rem] flex-1 flex-col gap-3 overflow-y-auto p-3">
                        <div
                            v-for="task in groupedTasks[column.id]"
                            :key="task.id"
                            :draggable="task.can_update_status && !isTaskProcessing(task.id)"
                            class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition-all duration-200 dark:border-slate-800 dark:bg-slate-900"
                            :class="[
                                task.can_update_status && !isTaskProcessing(task.id)
                                    ? 'cursor-grab hover:-translate-y-0.5 hover:border-sky-300 hover:shadow-md active:cursor-grabbing'
                                    : 'cursor-default',
                                draggedTaskId === task.id ? 'scale-[0.98] opacity-60 shadow-none' : '',
                                isTaskProcessing(task.id) ? 'pointer-events-none opacity-70' : '',
                            ]"
                            @dragstart="(e) => onDragStart(e, task.id)"
                            @dragend="onDragEnd"
                        >
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-2">
                                    <GripVertical
                                        class="h-4 w-4 shrink-0 text-slate-300"
                                        :class="task.can_update_status ? 'opacity-100' : 'opacity-40'"
                                    />
                                    <Badge variant="outline" class="truncate rounded-full border-slate-200 bg-slate-50 text-[10px] font-semibold text-slate-600">
                                        {{ task.category }}
                                    </Badge>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="h-2.5 w-2.5 shrink-0 rounded-full shadow-sm"
                                        :class="{
                                            'bg-red-500': task.priority === 'urgent',
                                            'bg-amber-500': task.priority === 'high',
                                            'bg-blue-500': task.priority === 'medium',
                                            'bg-slate-300': task.priority === 'low',
                                        }"
                                        :title="`Priority: ${task.priority}`"
                                    />
                                    <LoaderCircle
                                        v-if="isTaskProcessing(task.id)"
                                        class="h-4 w-4 animate-spin text-sky-500"
                                    />
                                </div>
                            </div>

                            <h4 class="line-clamp-2 text-sm font-semibold leading-6 text-slate-900 dark:text-slate-100">
                                <Link :href="tasksShow.url(task.id)" class="transition hover:text-sky-700">
                                    {{ task.title }}
                                </Link>
                            </h4>

                            <div class="mt-3 grid gap-2 text-xs text-slate-500 dark:text-slate-400">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-slate-700 dark:text-slate-200">Client</span>
                                    <span class="truncate">{{ task.client?.name || '-' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-slate-700 dark:text-slate-200">Product</span>
                                    <span class="truncate">{{ task.product?.name || '-' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <CalendarDays class="h-3.5 w-3.5 shrink-0" />
                                    <span :class="isOverdue(task) ? 'font-semibold text-red-600 dark:text-red-400' : ''">
                                        {{ formatDate(task.release_date) }}
                                    </span>
                                    <span
                                        v-if="isOverdue(task)"
                                        class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-600 dark:bg-red-950/30 dark:text-red-400"
                                    >
                                        Overdue
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-200/80 pt-3 dark:border-slate-800">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                                        {{ task.comments_count ?? 0 }} komentar
                                    </span>
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-semibold"
                                        :class="{
                                            'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300': task.status === 'open',
                                            'bg-sky-100 text-sky-700 dark:bg-sky-950/30 dark:text-sky-300': task.status === 'in_progress',
                                            'bg-amber-100 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300': task.status === 'revision',
                                            'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300': task.status === 'completed',
                                        }"
                                    >
                                        <Circle v-if="task.status === 'open'" class="h-3 w-3 fill-current" />
                                        <ArrowRightLeft v-else-if="task.status === 'in_progress'" class="h-3 w-3" />
                                        <CircleAlert v-else-if="task.status === 'revision'" class="h-3 w-3" />
                                        <CheckCircle2 v-else class="h-3 w-3" />
                                        {{ statusLabelMap[task.status] }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-1">
                                    <Link v-if="task.can_edit" :href="tasksEdit.url(task.id)" class="hidden sm:block">
                                        <Button variant="ghost" size="sm" class="h-8 rounded-lg px-3 text-xs font-semibold text-sky-700 hover:bg-sky-50 hover:text-sky-800 dark:hover:bg-sky-950/20">
                                            Edit
                                        </Button>
                                    </Link>

                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800">
                                                <MoreHorizontal class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" class="w-48 rounded-xl">
                                            <DropdownMenuItem
                                                v-for="statusOption in statusActionOrder"
                                                :key="statusOption"
                                                :disabled="!task.can_update_status || statusOption === task.status || isTaskProcessing(task.id)"
                                                class="cursor-pointer"
                                                @select="updateTaskStatus(task.id, statusOption)"
                                            >
                                                Pindahkan ke {{ statusLabelMap[statusOption] }}
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                v-if="task.can_edit"
                                                class="cursor-pointer sm:hidden"
                                                @select="router.get(tasksEdit.url(task.id))"
                                            >
                                                Edit task
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="groupedTasks[column.id].length === 0"
                            class="flex min-h-[10rem] flex-1 items-center justify-center rounded-2xl border border-dashed border-slate-300/80 bg-white/70 px-4 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-400"
                        >
                            Belum ada task di kolom ini.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
