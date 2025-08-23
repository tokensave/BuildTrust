<script setup lang="ts">
import AppContent from '@/shared/components/layout/AppContent.vue'
import AppHeader from '@/shared/components/layout/AppHeader.vue'
import AppShell from '@/shared/components/layout/AppShell.vue'
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell class="flex-col">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent>
            <slot />
        </AppContent>
    </AppShell>
</template>
