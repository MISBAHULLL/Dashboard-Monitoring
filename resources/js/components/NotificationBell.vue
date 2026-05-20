<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Bell,
    BellRing,
    CheckCheck,
    Clock3,
    MessageSquare,
    RefreshCw,
    Trash2,
    Ticket,
} from 'lucide-vue-next';
import {
    dismissAll as dismissAllNotifications,
    dismissRead as dismissReadNotifications,
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
    type:
        | 'task_assigned'
        | 'deadline_soon'
        | 'deadline_overdue'
        | 'status_changed'
        | 'new_comment'
        | string;
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
const authUserId = computed(() => {
    const auth = page.props.auth as { user?: { id?: number | null } | null } | undefined;

    return auth?.user?.id ?? null;
});

// Local optimistic overrides: track which IDs have been marked read client-side
const localReadIds = ref<Set<number>>(new Set());
// Track dismissed IDs so they disappear immediately from the list
const localDismissedIds = ref<Set<number>>(new Set());
// Track if all were marked read locally
const localAllRead = ref(false);
// Track if all were dismissed locally
const localAllDismissed = ref(false);

const notificationState = computed<NotificationState>(() => {
    return (
        (page.props.notifications as NotificationState | undefined) ?? {
            unread_count: 0,
            items: [],
        }
    );
});

// Visible items after applying local dismissals
const items = computed(() =>
    localAllDismissed.value
        ? []
        : notificationState.value.items.filter(
              (n) => !localDismissedIds.value.has(n.id),
          ),
);

// Effective is_read for each item (server value OR local optimistic)
const isRead = (notification: NotificationItem): boolean =>
    notification.is_read || localReadIds.value.has(notification.id) || localAllRead.value;

// Unread count: start from server value, subtract local optimistic reads
const unreadCount = computed(() => {
    if (localAllRead.value || localAllDismissed.value) return 0;

    const serverUnread = notificationState.value.unread_count;
    // Count how many of the locally-read IDs were actually unread on the server
    const locallyReadCount = notificationState.value.items.filter(
        (n) => !n.is_read && localReadIds.value.has(n.id),
    ).length;

    return Math.max(0, serverUnread - locallyReadCount);
});

const readItemsCount = computed(
    () => items.value.filter((n) => isRead(n)).length,
);

// Reset local optimistic state when server data or active user changes.
// Without this, "dismiss all" / "mark read" can hide new notifications after
// an Inertia refresh or after switching users in the same browser session.
const serverNotificationSignature = computed(() =>
    JSON.stringify({
        user: authUserId.value,
        unread: notificationState.value.unread_count,
        items: notificationState.value.items.map((notification) => ({
            id: notification.id,
            is_read: notification.is_read,
        })),
    }),
);

let lastServerNotificationSignature = '';
const syncLocalState = () => {
    if (serverNotificationSignature.value !== lastServerNotificationSignature) {
        lastServerNotificationSignature = serverNotificationSignature.value;
        localReadIds.value = new Set();
        localDismissedIds.value = new Set();
        localAllRead.value = false;
        localAllDismissed.value = false;
    }
};

watch(serverNotificationSignature, () => syncLocalState(), { immediate: true });

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
    syncLocalState();

    // Optimistically mark as read immediately so badge decrements right away
    if (!isRead(notification)) {
        localReadIds.value = new Set([...localReadIds.value, notification.id]);
    }

    if (notification.link) {
        // Fire-and-forget the mark-as-read patch, then navigate
        if (!notification.is_read) {
            router.patch(markNotificationAsRead.url(notification.id), {}, {
                preserveState: true,
                preserveScroll: true,
                only: ['notifications'],
            });
        }
        router.get(notification.link, {}, { preserveScroll: false });
    } else if (!notification.is_read) {
        router.patch(
            markNotificationAsRead.url(notification.id),
            {},
            {
                preserveState: true,
                preserveScroll: true,
                only: ['notifications'],
                onSuccess: () => syncLocalState(),
            },
        );
    }
};

const markAllAsRead = () => {
    if (unreadCount.value === 0) {
        return;
    }

    // Optimistic: mark all as read immediately
    localAllRead.value = true;

    router.patch(
        markAllNotificationsAsRead.url(),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            only: ['notifications'],
            onSuccess: () => syncLocalState(),
        },
    );
};

const dismissRead = () => {
    if (readItemsCount.value === 0) {
        return;
    }

    // Optimistic: hide all currently-read items immediately
    const readIds = items.value.filter((n) => isRead(n)).map((n) => n.id);
    localDismissedIds.value = new Set([...localDismissedIds.value, ...readIds]);

    router.patch(
        dismissReadNotifications.url(),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            only: ['notifications'],
            onSuccess: () => syncLocalState(),
        },
    );
};

const dismissAll = () => {
    if (items.value.length === 0) {
        return;
    }

    // Optimistic: hide everything immediately
    localAllDismissed.value = true;

    router.patch(
        dismissAllNotifications.url(),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            only: ['notifications'],
            onSuccess: () => syncLocalState(),
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
                    class="absolute top-[3px] left-[3px] h-[42px] w-[42px] rounded-xl border border-slate-900 bg-slate-900"
                />
                <!-- Main yellow button -->
                <div
                    class="relative flex h-[42px] w-[42px] items-center justify-center rounded-xl border-[1.5px] border-slate-900 bg-amber-400 transition-transform group-hover:-translate-x-[1px] group-hover:-translate-y-[1px] group-active:translate-x-[3px] group-active:translate-y-[3px]"
                >
                    <BellRing
                        v-if="unreadCount > 0"
                        class="h-5 w-5 text-slate-900"
                    />
                    <Bell v-else class="h-5 w-5 text-slate-900" />
                </div>

                <!-- Red notification badge -->
                <span
                    v-if="unreadCount > 0"
                    class="absolute -top-1 -right-1 z-10 inline-flex min-w-[20px] items-center justify-center rounded-full border-2 border-white bg-red-500 px-1 py-0.5 text-[10px] leading-none font-bold text-white"
                >
                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                </span>
                <span class="sr-only">Notifications</span>
            </button>
        </DropdownMenuTrigger>

        <DropdownMenuContent
            align="end"
            class="w-[22rem] rounded-xl border-2 border-slate-900 p-0 shadow-[4px_4px_0px_0px_#0f172a]"
        >
            <div class="border-b-2 border-slate-900 px-4 py-3">
                <div class="flex items-center justify-between gap-3">
                    <span
                        class="text-sm font-bold text-slate-800 dark:text-slate-100"
                    >
                        Notifikasi
                    </span>
                    <button
                        class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 transition hover:text-amber-700 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="unreadCount === 0"
                        @click="markAllAsRead"
                    >
                        <CheckCheck class="h-3.5 w-3.5" />
                        Tandai dibaca
                    </button>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    <button
                        class="inline-flex h-8 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                        :disabled="readItemsCount === 0"
                        @click="dismissRead"
                    >
                        <Trash2 class="h-3.5 w-3.5" />
                        Bersihkan dibaca
                    </button>
                    <button
                        class="inline-flex h-8 items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2 text-xs font-semibold text-red-600 transition hover:border-red-300 hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-red-400/30 dark:bg-red-950/30 dark:text-red-300"
                        :disabled="items.length === 0"
                        @click="dismissAll"
                    >
                        <Trash2 class="h-3.5 w-3.5" />
                        Bersihkan semua
                    </button>
                </div>
            </div>

            <div
                v-if="items.length === 0"
                class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400"
            >
                Belum ada notifikasi baru.
            </div>

            <div v-else class="max-h-[28rem] overflow-y-auto py-2">
                <DropdownMenuItem
                    v-for="notification in items"
                    :key="notification.id"
                    class="mx-2 mb-1 flex cursor-pointer items-start gap-3 rounded-lg px-3 py-3 transition-colors"
                    :class="
                        isRead(notification)
                            ? 'opacity-70'
                            : toneForType(notification.type).item
                    "
                    @select="openNotification(notification)"
                >
                    <div
                        class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-slate-200"
                        :class="toneForType(notification.type).icon"
                    >
                        <component
                            :is="iconForType(notification.type)"
                            class="h-4 w-4"
                        />
                    </div>

                    <div class="min-w-0 space-y-1">
                        <div class="flex items-start gap-2">
                            <p
                                class="line-clamp-1 flex-1 text-sm font-semibold text-slate-800 dark:text-slate-100"
                            >
                                {{ notification.title }}
                            </p>
                            <span
                                v-if="!isRead(notification)"
                                class="mt-1 h-2 w-2 shrink-0 rounded-full"
                                :class="toneForType(notification.type).dot"
                            />
                        </div>
                        <p
                            class="line-clamp-2 text-xs leading-5 text-slate-500 dark:text-slate-400"
                        >
                            {{ notification.body }}
                        </p>
                        <p
                            class="text-[11px] font-medium text-slate-400 dark:text-slate-500"
                        >
                            {{ formatTimestamp(notification.created_at) }}
                        </p>
                    </div>
                </DropdownMenuItem>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
