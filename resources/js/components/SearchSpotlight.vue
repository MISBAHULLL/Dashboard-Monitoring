<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import {
    ListTodo,
    Building2,
    UsersRound,
    FileText,
    Search,
    Loader2,
    Command,
    ArrowUp,
    ArrowDown,
    CornerDownLeft,
    X,
} from 'lucide-vue-next';

interface SearchItem {
    id: number;
    title: string;
    subtitle: string;
    meta: string | null;
    type: string;
    url: string;
    status?: string;
}

interface SearchGroup {
    label: string;
    icon: string;
    items: SearchItem[];
}

const isOpen = ref(false);
const query = ref('');
const isLoading = ref(false);
const results = ref<SearchGroup[]>([]);
const selectedIndex = ref(-1);
const inputRef = ref<HTMLInputElement | null>(null);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const totalItems = computed(() => {
    return results.value.reduce((sum, group) => sum + group.items.length, 0);
});

function open() {
    isOpen.value = true;
    query.value = '';
    results.value = [];
    selectedIndex.value = -1;
    nextTick(() => inputRef.value?.focus());
}

function close() {
    isOpen.value = false;
}

function highlight(text: string, term: string): string {
    if (!term) return text;
    const regex = new RegExp(`(${escapeRegex(term)})`, 'gi');
    return text.replace(regex, '<mark class="bg-emerald-200 text-emerald-900 rounded px-0.5">$1</mark>');
}

function escapeRegex(str: string): string {
    return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

async function fetchResults(term: string) {
    if (term.length < 2) {
        results.value = [];
        selectedIndex.value = -1;
        return;
    }

    isLoading.value = true;
    try {
        const res = await fetch(`/api/search?q=${encodeURIComponent(term)}`);
        const data = await res.json();
        results.value = data;
        selectedIndex.value = data.length > 0 ? 0 : -1;
    } catch (e) {
        results.value = [];
        selectedIndex.value = -1;
    } finally {
        isLoading.value = false;
    }
}

watch(query, (newVal) => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchResults(newVal), 300);
});

function onKeydown(e: KeyboardEvent) {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        open();
    }

    if (!isOpen.value) return;

    switch (e.key) {
        case 'Escape':
            close();
            break;
        case 'ArrowDown':
            e.preventDefault();
            if (totalItems.value > 0) {
                selectedIndex.value = (selectedIndex.value + 1) % totalItems.value;
                scrollToSelected();
            }
            break;
        case 'ArrowUp':
            e.preventDefault();
            if (totalItems.value > 0) {
                selectedIndex.value = (selectedIndex.value - 1 + totalItems.value) % totalItems.value;
                scrollToSelected();
            }
            break;
        case 'Enter':
            e.preventDefault();
            navigateToSelected();
            break;
    }
}

function getItemByIndex(index: number): { groupIndex: number; itemIndex: number; item: SearchItem } | null {
    let current = 0;
    for (let g = 0; g < results.value.length; g++) {
        for (let i = 0; i < results.value[g].items.length; i++) {
            if (current === index) {
                return { groupIndex: g, itemIndex: i, item: results.value[g].items[i] };
            }
            current++;
        }
    }
    return null;
}

function isSelected(groupIndex: number, itemIndex: number): boolean {
    let current = 0;
    for (let g = 0; g < results.value.length; g++) {
        for (let i = 0; i < results.value[g].items.length; i++) {
            if (g === groupIndex && i === itemIndex) {
                return current === selectedIndex.value;
            }
            current++;
        }
    }
    return false;
}

function navigateToSelected() {
    const found = getItemByIndex(selectedIndex.value);
    if (found) {
        navigate(found.item.url);
    }
}

function navigate(url: string) {
    close();
    router.visit(url);
}

function scrollToSelected() {
    nextTick(() => {
        const el = document.querySelector('[data-selected="true"]');
        el?.scrollIntoView({ block: 'nearest' });
    });
}

function getGroupIcon(iconName: string) {
    switch (iconName) {
        case 'ListTodo': return ListTodo;
        case 'Building2': return Building2;
        case 'UsersRound': return UsersRound;
        case 'FileText': return FileText;
        default: return Search;
    }
}

function getStatusColor(status?: string): string {
    switch (status) {
        case 'open': return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'in_progress': return 'bg-blue-100 text-blue-700 border-blue-200';
        case 'revision': return 'bg-red-100 text-red-700 border-red-200';
        case 'completed': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
}

onMounted(() => {
    window.addEventListener('keydown', onKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown);
});

defineExpose({ open });
</script>

<template>
    <Dialog :open="isOpen" @update:open="isOpen = $event">
        <DialogContent
            class="max-w-2xl! gap-0 overflow-hidden border border-slate-200 p-0 shadow-2xl dark:border-slate-700"
            :show-close-button="false"
        >
            <DialogTitle class="sr-only">Pencarian Global</DialogTitle>

            <!-- Search Input Header -->
            <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3 dark:border-slate-700">
                <Search class="h-5 w-5 text-slate-400" />
                <input
                    ref="inputRef"
                    v-model="query"
                    type="text"
                    placeholder="Cari task, faskes, tim, atau dokumen..."
                    class="flex-1 bg-transparent text-sm text-slate-900 outline-none placeholder:text-slate-400 dark:text-slate-100"
                />
                <div class="flex items-center gap-1">
                    <kbd class="hidden rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500 dark:border-slate-600 dark:bg-slate-800 sm:inline-block">
                        ESC
                    </kbd>
                    <button
                        class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700"
                        @click="close"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <!-- Results Area -->
            <div class="max-h-[60vh] overflow-y-auto">
                <!-- Loading -->
                <div v-if="isLoading" class="flex items-center justify-center py-12">
                    <Loader2 class="h-6 w-6 animate-spin text-slate-400" />
                    <span class="ml-2 text-sm text-slate-500">Mencari...</span>
                </div>

                <!-- Empty / Too Short -->
                <div v-else-if="query.length === 0" class="flex flex-col items-center justify-center py-12 text-slate-400">
                    <Command class="mb-2 h-8 w-8 opacity-50" />
                    <p class="text-sm">Ketik untuk mencari data</p>
                    <p class="mt-1 text-xs text-slate-400">Minimal 2 karakter</p>
                </div>

                <div v-else-if="query.length >= 2 && results.length === 0 && !isLoading" class="flex flex-col items-center justify-center py-12 text-slate-400">
                    <Search class="mb-2 h-8 w-8 opacity-50" />
                    <p class="text-sm">Tidak ada hasil untuk "{{ query }}"</p>
                </div>

                <!-- Grouped Results -->
                <template v-else>
                    <div v-for="(group, gIndex) in results" :key="group.label">
                        <!-- Group Header -->
                        <div class="sticky top-0 z-10 flex items-center gap-2 bg-slate-50/95 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-slate-500 backdrop-blur-sm dark:bg-slate-800/95 dark:text-slate-400">
                            <component :is="getGroupIcon(group.icon)" class="h-3.5 w-3.5" />
                            {{ group.label }}
                        </div>

                        <!-- Items -->
                        <div class="px-2 pb-2">
                            <button
                                v-for="(item, iIndex) in group.items"
                                :key="item.id + item.type"
                                :data-selected="isSelected(gIndex, iIndex)"
                                class="group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left transition-colors"
                                :class="isSelected(gIndex, iIndex)
                                    ? 'bg-emerald-50 dark:bg-emerald-900/20'
                                    : 'hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                                @click="navigate(item.url)"
                                @mouseenter="selectedIndex = (() => {
                                    let current = 0;
                                    for (let g = 0; g < gIndex; g++) current += results[g].items.length;
                                    return current + iIndex;
                                })()"
                            >
                                <component :is="getGroupIcon(group.icon)" class="h-4 w-4 shrink-0 text-slate-400 group-hover:text-emerald-600" />

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-200" v-html="highlight(item.title, query)"></p>
                                        <span v-if="item.status" class="inline-flex items-center rounded border px-1.5 py-0 text-[10px] font-semibold capitalize" :class="getStatusColor(item.status)">
                                            {{ item.status.replace('_', ' ') }}
                                        </span>
                                    </div>
                                    <p class="truncate text-xs text-slate-500" v-html="highlight(item.subtitle + (item.meta ? ` · ${item.meta}` : ''), query)"></p>
                                </div>

                                <CornerDownLeft v-if="isSelected(gIndex, iIndex)" class="h-3.5 w-3.5 shrink-0 text-emerald-600" />
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer Keyboard Hints -->
            <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/80 px-4 py-2 text-[10px] text-slate-400 dark:border-slate-700 dark:bg-slate-800/50">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1">
                        <ArrowUp class="h-3 w-3" />
                        <ArrowDown class="h-3 w-3" />
                        Navigasi
                    </span>
                    <span class="flex items-center gap-1">
                        <CornerDownLeft class="h-3 w-3" />
                        Buka
                    </span>
                </div>
                <span class="flex items-center gap-1">
                    <kbd class="rounded border border-slate-200 bg-white px-1 py-0 text-[9px] font-semibold dark:border-slate-600 dark:bg-slate-700">Ctrl</kbd>
                    <kbd class="rounded border border-slate-200 bg-white px-1 py-0 text-[9px] font-semibold dark:border-slate-600 dark:bg-slate-700">K</kbd>
                    Buka Pencarian
                </span>
            </div>
        </DialogContent>
    </Dialog>
</template>
