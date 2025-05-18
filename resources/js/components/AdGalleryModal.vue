<script setup lang="ts">
import { computed, defineProps, defineEmits } from 'vue';

const props = defineProps<{
    images: string[];
    index: number | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'next'): void;
    (e: 'prev'): void;
}>();

const isModalOpen = computed(() => props.index !== null);
const currentImage = computed(() =>
    props.index !== null ? props.images[props.index] : ''
);
</script>

<template>
    <div
        v-if="isModalOpen"
        class="fixed inset-0 bg-black/80 flex items-center justify-center z-50"
    >
        <div
            class="relative bg-black rounded-lg max-w-5xl w-[90vw] max-h-[80vh] p-4 flex items-center justify-center"
        >
            <img
                :src="currentImage"
                alt="Изображение"
                class="max-w-full max-h-[70vh] object-contain"
            />

            <!-- Кнопка закрытия -->
            <button
                @click="emit('close')"
                class="absolute top-2 right-2 text-white text-3xl font-bold bg-red-600 rounded-full w-8 h-8 flex items-center justify-center"
                aria-label="Закрыть"
            >
                ×
            </button>

            <!-- Кнопка назад -->
            <button
                v-if="props.index !== null && props.index > 0"
                @click="emit('prev')"
                class="absolute left-2 text-white text-4xl font-bold bg-black/50 rounded px-2 py-1"
                aria-label="Предыдущее"
            >
                ‹
            </button>

            <!-- Кнопка вперёд -->
            <button
                v-if="props.index !== null && props.index < props.images.length - 1"
                @click="emit('next')"
                class="absolute right-2 text-white text-4xl font-bold bg-black/50 rounded px-2 py-1"
                aria-label="Следующее"
            >
                ›
            </button>
        </div>
    </div>
</template>
