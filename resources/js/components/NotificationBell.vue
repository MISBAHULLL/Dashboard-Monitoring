<script setup lang="ts">
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Bell, BellRing, CheckCheck, Clock3, Ticket } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    markAllAsRead as markAllNotificationsAsRead,
    markAsRead as markNotificationAsRead,
} from '@/actions/App/Http/Controllers/NotificationController';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

type NotificationItem = {
    id: number;
    type: 'task_assigned' | 'deadline_soon' | string;
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
    if (type === 'deadline_soon') {
        return Clock3;
    }

    return Ticket;
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
            <Button
                variant="ghost"
                size="icon"
                class="relative h-10 w-10 rounded-full border border-slate-200/80 bg-white text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
            >
                <BellRing v-if="unreadCount > 0" class="h-4.5 w-4.5" />
                <Bell v-else class="h-4.5 w-4.5" />
                <span
                    v-if="unreadCount > 0"
                    class="absolute -right-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-bold text-white shadow-sm"
                >
                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                </span>
                <span class="sr-only">Notifications</span>
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-[22rem] rounded-2xl p-0">
            <div class="flex items-center justify-between px-4 py-3">
                <DropdownMenuLabel class="p-0 text-sm font-semibold text-slate-800 dark:text-slate-100">
                    Notifikasi
                </DropdownMenuLabel>
                <button
                    class="text-xs font-semibold text-sky-600 transition hover:text-sky-700 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="unreadCount === 0"
                    @click="markAllAsRead"
                >
                    <span class="inline-flex items-center gap-1">
                        <CheckCheck class="h-3.5 w-3.5" />
                        Tandai semua dibaca
                    </span>
                </button>
            </div>

            <DropdownMenuSeparator />

            <div v-if="items.length === 0" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                Belum ada notifikasi baru.
            </div>

            <div v-else class="max-h-[28rem] overflow-y-auto py-2">
                <DropdownMenuItem
                    v-for="notification in items"
                    :key="notification.id"
                    class="mx-2 mb-1 flex cursor-pointer items-start gap-3 rounded-xl px-3 py-3"
                    :class="notification.is_read ? 'opacity-75' : 'bg-sky-50/70 dark:bg-sky-950/20'"
                    @select="openNotification(notification)"
                >
                    <div
                        class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                        :class="notification.type === 'deadline_soon'
                            ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300'
                            : 'bg-sky-100 text-sky-700 dark:bg-sky-950/30 dark:text-sky-300'"
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
                                class="mt-1 h-2 w-2 shrink-0 rounded-full bg-sky-500"
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
