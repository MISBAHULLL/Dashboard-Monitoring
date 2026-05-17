<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <main
    data-slot="sidebar-inset"
    :class="cn(
      'bg-background relative flex min-h-0 w-full flex-1 flex-col overflow-y-auto overflow-x-hidden overscroll-contain',
      'md:peer-data-[variant=inset]:m-2 md:peer-data-[variant=inset]:rounded-xl md:peer-data-[variant=inset]:shadow-sm',
      'md:peer-data-[variant=inset]:peer-data-[state=expanded]:ml-5',
      'md:peer-data-[variant=inset]:peer-data-[state=collapsed]:ml-9',
      'md:peer-data-[variant=inset]:transition-[margin] md:peer-data-[variant=inset]:duration-300 md:peer-data-[variant=inset]:ease-out',
      props.class,
    )"
  >
    <slot />
  </main>
</template>
