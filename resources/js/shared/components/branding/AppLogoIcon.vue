<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img
        :src="'/storage/logoSVG.svg'"
        :class="className"
        v-bind="$attrs"
        alt="Логотип"
    />
</template>
