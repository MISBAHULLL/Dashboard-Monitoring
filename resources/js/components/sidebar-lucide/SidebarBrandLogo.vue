<script setup lang="ts">
/**
 * Brand logo di header sidebar (variant Lucide).
 *
 * - **Expanded state**: pakai "logo teks" (mark + wordmark "trustmedis").
 * - **Collapsed state** (`collapsible=icon`): otomatis ditukar ke
 *   "logo mark" saja supaya tetap visible di bar sempit.
 * - Fallback ke `AppLogoIcon` bila gambar gagal dimuat.
 */
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { dashboard } from '@/routes';

import logoTextUrl from '@/assets/trustmedis-logo-text.png';
import logoMarkUrl from '@/assets/trustmedis-logo-mark.png';

interface Props {
    /** Override URL logo expanded (image dengan teks). */
    textSrc?: string;
    /** Override URL logo collapsed (image icon saja). */
    markSrc?: string;
    alt?: string;
}

const props = withDefaults(defineProps<Props>(), {
    textSrc: logoTextUrl,
    markSrc: logoMarkUrl,
    alt: 'trustmedis – healthtech solution',
});

const hasTextError = ref(false);
const hasMarkError = ref(false);
</script>

<template>
    <div class="relative">
        <Link
            :href="dashboard()"
            :aria-label="alt"
            class="block rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60"
        >
            <!-- ── Expanded: logo teks (PNG sudah di-trim) ─── -->
            <!-- px-6 + pt-6 menjaga logo tidak ter-clip oleh rounded
                 corner 28px container sidebar. -->
            <div class="block px-6 pt-6 pb-4 group-data-[collapsible=icon]:hidden">
                <img
                    v-if="!hasTextError"
                    :src="props.textSrc"
                    :alt="props.alt"
                    class="mx-auto block h-auto w-full object-contain"
                    loading="eager"
                    @error="hasTextError = true"
                />
                <AppLogoIcon
                    v-else
                    class="mx-auto block h-16 w-16 text-white"
                    :aria-label="props.alt"
                    role="img"
                />
            </div>

            <!-- ── Collapsed: logo mark ──────────────────────── -->
            <div
                class="hidden items-center justify-center py-4 group-data-[collapsible=icon]:flex"
            >
                <img
                    v-if="!hasMarkError"
                    :src="props.markSrc"
                    :alt="props.alt"
                    class="h-9 w-9 object-contain"
                    loading="eager"
                    @error="hasMarkError = true"
                />
                <AppLogoIcon
                    v-else
                    class="size-9 text-white"
                    :aria-label="props.alt"
                    role="img"
                />
            </div>
        </Link>
    </div>
</template>
