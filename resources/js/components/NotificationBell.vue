<script setup lang="ts">
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { AlertTriangle, Bell, BellRing, CheckCheck, Clock3, MessageSquare, RefreshCw, Ticket } from 'lucide-vue-next';
import {
    readAll as markAllNotificationsAsRead,
    read as markNotificationAsRead,
} from '@/routes/notifications';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

type NotificationItem = {
    id: number;
    type: 'task_assigned' | 'deadline_soon' | 'deadline_overdue' | 'status_changed' | 'new_comment' | string;
    title: string;
    body: string | null;
    link: string | null;
    is_read: boolean;
    created_at: string | null;
};

type NotificationState = {
    unread_count: number;
    items: NotificationItem[];
};

const page = usePage();

const notificationState = computed<NotificationState>(() => {
    return (page.props.notifications as NotificationState | undefined) ?? {
        unread_count: 0,
        items: [],
    };
});

const unreadCount = computed(() => notificationState.value.unread_count);
const items = computed(() => notificationState.value.items);

const formatTimestamp = (value: string | null) => {
    if (!value) {
        return '';
    }

    return new Date(value).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const iconForType = (type: NotificationItem['type']) => {
    if (type === 'deadline_overdue') {
        return AlertTriangle;
    }

    if (type === 'deadline_soon') {
        return Clock3;
    }

    if (type === 'status_changed') {
        return RefreshCw;
    }

    if (type === 'new_comment') {
        return MessageSquare;
    }

    return Ticket;
};

const toneForType = (type: NotificationItem['type']) => {
    if (type === 'deadline_overdue') {
        return {
            item: 'bg-red-50/90 dark:bg-red-950/25',
            icon: 'border-red-200 bg-red-100 text-red-700 dark:border-red-400/30 dark:bg-red-950/40 dark:text-red-300',
            dot: 'bg-red-500',
        };
    }

    if (type === 'deadline_soon') {
        return {
            item: 'bg-amber-50/90 dark:bg-amber-950/25',
            icon: 'border-amber-200 bg-amber-100 text-amber-700 dark:border-amber-400/30 dark:bg-amber-950/40 dark:text-amber-300',
            dot: 'bg-amber-500',
        };
    }

    if (type === 'task_assigned') {
        return {
            item: 'bg-sky-50/90 dark:bg-sky-950/25',
            icon: 'border-sky-200 bg-sky-100 text-sky-700 dark:border-sky-400/30 dark:bg-sky-950/40 dark:text-sky-300',
            dot: 'bg-sky-500',
        };
    }

    if (type === 'status_changed') {
        return {
            item: 'bg-violet-50/90 dark:bg-violet-950/25',
            icon: 'border-violet-200 bg-violet-100 text-violet-700 dark:border-violet-400/30 dark:bg-violet-950/40 dark:text-violet-300',
            dot: 'bg-violet-500',
        };
    }

    if (type === 'new_comment') {
        return {
            item: 'bg-emerald-50/90 dark:bg-emerald-950/25',
            icon: 'border-emerald-200 bg-emerald-100 text-emerald-700 dark:border-emerald-400/30 dark:bg-emerald-950/40 dark:text-emerald-300',
            dot: 'bg-emerald-500',
        };
    }

    return {
        item: 'bg-slate-50/90 dark:bg-slate-900/45',
        icon: 'border-slate-200 bg-slate-100 text-slate-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300',
        dot: 'bg-slate-500',
    };
};

const openNotification = (notification: NotificationItem) => {
    const visitLink = () => {
        if (notification.link) {
            router.get(notification.link, {}, { preserveScroll: true });
        }
    };

    if (notification.is_read) {
        visitLink();
        return;
    }

    router.patch(
        markNotificationAsRead.url(notification.id),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            only: ['notifications'],
            onSuccess: visitLink,
        },
    );
};

const markAllAsRead = () => {
    if (unreadCount.value === 0) {
        return;
    }

    router.patch(
        markAllNotificationsAsRead.url(),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            only: ['notifications'],
        },
    );
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <!-- Neo-brutalist notification button -->
            <button class="group relative cursor-pointer focus:outline-none">
                <!-- Shadow layer -->
                <div
                    class="absolute left-[3px] top-[3px] h-[42px] w-[42px] rounded-xl border border-slate-900 bg-slate-900"
                />
                <!-- Main yellow button -->
                <div
                    class="relative flex h-[42px] w-[42px] items-center justify-center rounded-xl border-[1.5px] border-slate-900 bg-amber-400 transition-transform group-hover:-translate-x-[1px] group-hover:-translate-y-[1px] group-active:translate-x-[3px] group-active:translate-y-[3px]"
                >
                    <BellRing v-if="unreadCount > 0" class="h-5 w-5 text-slate-900" />
                    <Bell v-else class="h-5 w-5 text-slate-900" />
                </div>

                <!-- Red notification badge -->
                <span
                    v-if="unreadCount > 0"
                    class="absolute -right-1 -top-1 z-10 inline-flex min-w-[20px] items-center justify-center rounded-full border-2 border-white bg-red-500 px-1 py-0.5 text-[10px] font-bold leading-none text-white"
                >
                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                </span>
                <span class="sr-only">Notifications</span>
            </button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-[22rem] rounded-xl border-2 border-slate-900 p-0 shadow-[4px_4px_0px_0px_#0f172a]">
            <div class="flex items-center justify-between border-b-2 border-slate-900 px-4 py-3">
                <span class="text-sm font-bold text-slate-800 dark:text-slate-100">
                    Notifikasi
                </span>
                <button
                    class="text-xs font-semibold text-amber-600 transition hover:text-amber-700 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="unreadCount === 0"
                    @click="markAllAsRead"
                >
                    <span class="inline-flex items-center gap-1">
                        <CheckCheck class="h-3.5 w-3.5" />
                        Tandai semua dibaca
                    </span>
                </button>
            </div>

            <div v-if="items.length === 0" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                Belum ada notifikasi baru.
            </div>

            <div v-else class="max-h-[28rem] overflow-y-auto py-2">
                <DropdownMenuItem
                    v-for="notification in items"
                    :key="notification.id"
                    class="mx-2 mb-1 flex cursor-pointer items-start gap-3 rounded-lg px-3 py-3 transition-colors"
                    :class="notification.is_read ? 'opacity-70' : toneForType(notification.type).item"
                    @select="openNotification(notification)"
                >
                    <div
                        class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-slate-200"
                        :class="toneForType(notification.type).icon"
                    >
                        <component :is="iconForType(notification.type)" class="h-4 w-4" />
                    </div>

                    <div class="min-w-0 space-y-1">
                        <div class="flex items-start gap-2">
                            <p class="line-clamp-1 flex-1 text-sm font-semibold text-slate-800 dark:text-slate-100">
                                {{ notification.title }}
                            </p>
                            <span
                                v-if="!notification.is_read"
                                class="mt-1 h-2 w-2 shrink-0 rounded-full"
                                :class="toneForType(notification.type).dot"
                            />
                        </div>
                        <p class="line-clamp-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                            {{ notification.body }}
                        </p>
                        <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500">
                            {{ formatTimestamp(notification.created_at) }}
                        </p>
                    </div>
                </DropdownMenuItem>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
