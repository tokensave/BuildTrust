<script setup lang="ts">
import { ref, watch, computed, defineProps, defineEmits, onMounted, onBeforeUnmount } from 'vue';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';

// --- Параметры компонента ---
const props = defineProps<{
    modelValue: File[];                              // Входные файлы (новые изображения)
    existingImages?: { id: number; url: string }[]; // Уже загруженные изображения (с сервера)
}>();

const emit = defineEmits(['update:modelValue', 'deletedMediaIds']); // События: обновление файлов и удалённых id

// --- Состояния компонента ---
// Хранение уже загруженных изображений (с сервера)
const existingPreviews = ref<{ id: number; url: string }[]>([]);

// Хранение локальных preview новых загруженных файлов (URL.createObjectURL)
const previews = ref<string[]>([]);

// Копия файлов из modelValue
const files = ref<File[]>([...props.modelValue]);

// Индекс выбранного в модалке изображения (null — модалка закрыта)
const modalIndex = ref<number | null>(null);

// Список id удалённых уже существующих медиа (для отправки наверх)
const deletedMediaIds = ref<number[]>([]);

// --- Вычисляемое свойство для текущего отображаемого изображения в модалке ---
const currentImageSrc = computed(() => {
    if (modalIndex.value === null) return '';

    // Если индекс в диапазоне существующих изображений — взять url оттуда
    if (modalIndex.value < existingPreviews.value.length) {
        return existingPreviews.value[modalIndex.value].url;
    }

    // Иначе — это новая картинка из previews, учитываем смещение
    return previews.value[modalIndex.value - existingPreviews.value.length];
});

// Общее количество изображений (существующих + новых)
const totalImages = computed(() => existingPreviews.value.length + previews.value.length);

// --- Работа с изменением входящих файлов ---
// При изменении modelValue обновляем локальные файлы и создаём preview-urls для новых файлов
watch(
    () => props.modelValue,
    (newFiles) => {
        files.value = [...newFiles];

        // Освобождаем старые preview url
        previews.value.forEach(url => URL.revokeObjectURL(url));

        // Создаём новые preview url для новых файлов
        previews.value = newFiles.map(file => URL.createObjectURL(file));
    },
    { immediate: true }
);

// --- Обработка выбора файлов через input ---
const handleChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (!target.files) return;

    const newFiles = Array.from(target.files);

    // Добавляем новые файлы в список
    files.value.push(...newFiles);

    // Создаём preview url для новых файлов
    previews.value.push(...newFiles.map(file => URL.createObjectURL(file)));

    // Отправляем обновлённый список файлов наружу
    emit('update:modelValue', [...files.value]);

    // Очищаем input (для повторного выбора тех же файлов)
    target.value = '';
};

// --- Удаление изображений ---
// isExisting - флаг, показывающий, удаляется ли существующее (серверное) изображение или новое локальное
const removeImage = (index: number, isExisting = false) => {
    if (isExisting) {
        // Добавляем id удалённого медиа в список и удаляем из preview существующих
        const image = existingPreviews.value[index];
        deletedMediaIds.value.push(image.id);
        existingPreviews.value.splice(index, 1);

        // Уведомляем родителя об удалённых id
        emit('deletedMediaIds', deletedMediaIds.value);
        return;
    }

    // Удаляем локальное изображение и освобождаем url
    URL.revokeObjectURL(previews.value[index]);
    previews.value.splice(index, 1);
    files.value.splice(index, 1);

    // Обновляем modelValue в родителе
    emit('update:modelValue', [...files.value]);
};

// --- Открытие и закрытие модалки ---
const openModal = (index: number) => {
    modalIndex.value = index;
};
const closeModal = () => {
    modalIndex.value = null;
};

// --- Переключение изображений в модалке ---
const nextImage = () => {
    if (modalIndex.value !== null && modalIndex.value < totalImages.value - 1) {
        modalIndex.value++;
    }
};
const prevImage = () => {
    if (modalIndex.value !== null && modalIndex.value > 0) {
        modalIndex.value--;
    }
};

// --- Обработка клавиатуры для навигации и закрытия модалки ---
const handleKeydown = (e: KeyboardEvent) => {
    if (modalIndex.value === null) return;

    if (e.key === 'Escape') {
        closeModal();
    } else if (e.key === 'ArrowRight') {
        nextImage();
    } else if (e.key === 'ArrowLeft') {
        prevImage();
    }
};

// --- Обработка свайпов (touch) для переключения в модалке ---
let startX = 0;
let endX = 0;

const handleTouchStart = (e: TouchEvent) => {
    startX = e.touches[0].clientX;
};
const handleTouchMove = (e: TouchEvent) => {
    endX = e.touches[0].clientX;
};
const handleTouchEnd = () => {
    const deltaX = endX - startX;
    if (Math.abs(deltaX) > 50) {
        if (deltaX > 0) prevImage();
        else nextImage();
    }
};

// --- Жизненный цикл компонента ---
// При монтировании инициализируем существующие изображения и вешаем слушатель клавиатуры
onMounted(() => {
    if (props.existingImages) {
        existingPreviews.value = [...props.existingImages];
    }
    window.addEventListener('keydown', handleKeydown);
});

// Убираем слушатель при размонтировании
onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeydown);
});

</script>

<template>
    <div>
        <!-- Выбор файлов -->
        <Input
            type="file"
            accept="image/*"
            multiple
            @change="handleChange"
        />

        <!-- Существующие изображения -->
        <div v-if="existingPreviews.length" class="flex gap-2 mt-2 flex-wrap">
            <div
                v-for="(img, index) in existingPreviews"
                :key="`existing-${img.id}`"
                class="relative size-16 cursor-pointer"
                @click="openModal(index)"
            >
                <Avatar class="size-16">
                    <AvatarImage :src="img.url" />
                    <AvatarFallback>IMG</AvatarFallback>
                </Avatar>
                <button
                    type="button"
                    class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center"
                    @click.stop.prevent="removeImage(index, true)"
                >
                    ×
                </button>
            </div>
        </div>

        <!-- Новые изображения -->
        <div v-if="previews.length" class="flex gap-2 mt-2 flex-wrap">
            <div
                v-for="(src, index) in previews"
                :key="`new-${index}`"
                class="relative size-16 cursor-pointer"
                @click="openModal(existingPreviews.length + index)"
            >
                <Avatar class="size-16">
                    <AvatarImage :src="src" />
                    <AvatarFallback>IMG</AvatarFallback>
                </Avatar>
                <button
                    type="button"
                    class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center"
                    @click.stop.prevent="removeImage(index, false)"
                >
                    ×
                </button>
            </div>
        </div>

        <!-- Модалка для просмотра выбранного изображения -->
        <div
            v-if="modalIndex !== null"
            class="fixed inset-0 bg-black/80 flex items-center justify-center z-50"
            @click="closeModal"
            @touchstart="handleTouchStart"
            @touchmove="handleTouchMove"
            @touchend="handleTouchEnd"
        >
            <img
                :src="currentImageSrc"
                class="max-w-[90vw] max-h-[90vh] rounded shadow-lg"
                @click.stop
             alt="Изображение обьявления"/>
            <button
                v-if="modalIndex > 0"
                class="absolute left-4 text-white text-3xl font-bold bg-black/50 px-2 py-1 rounded"
                @click.stop.prevent="prevImage"
            >
                ‹
            </button>
            <button
                v-if="modalIndex < totalImages - 1"
                class="absolute right-4 text-white text-3xl font-bold bg-black/50 px-2 py-1 rounded"
                @click.stop.prevent="nextImage"
            >
                ›
            </button>
            <button
                class="absolute top-4 right-4 text-white text-2xl font-bold bg-red-600 px-2 py-1 rounded-full"
                @click.stop.prevent="closeModal"
            >
                ×
            </button>
        </div>
    </div>
</template>
