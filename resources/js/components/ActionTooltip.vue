<script setup lang="ts">
import type { TooltipContentProps } from 'reka-ui';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

withDefaults(defineProps<{
    label: string;
    side?: TooltipContentProps['side'];
}>(), {
    side: 'top',
});
</script>

<template>
    <TooltipProvider>
        <Tooltip>
            <TooltipTrigger as-child>
                <slot />
            </TooltipTrigger>
            <TooltipContent :side="side">
                {{ label }}
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>
</template>
