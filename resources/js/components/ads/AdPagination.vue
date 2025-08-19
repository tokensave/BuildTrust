<!-- components/ads/AdPagination.vue -->
<script setup lang="ts">
import { computed } from 'vue';
import {
    Pagination,
    PaginationContent,
    PaginationItem,
    PaginationNext,
    PaginationPrevious
} from '@/components/ui/pagination';
import { Button } from '@/components/ui/button';

interface Props {
    currentPage: number;
    lastPage: number;
    total: number;
    from: number | null;
    to: number | null;
    filters?: Record<string, any>;
}

interface Emits {
    (e: 'change-page', page: number): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const visiblePages = computed(() => {
    const pages: number[] = [];
    const delta = 2;
    const left = Math.max(1, props.currentPage - delta);
    const right = Math.min(props.lastPage, props.currentPage + delta);

    for (let i = left; i <= right; i++) {
        pages.push(i);
    }

    return pages;
});

const showFirstPage = computed(() => props.currentPage > 3);
const showLastPage = computed(() => props.currentPage < props.lastPage - 2);
const showLeftDots = computed(() => props.currentPage > 4);
const showRightDots = computed(() => props.currentPage < props.lastPage - 3);

const goToPage = (pageNumber: number) => {
    if (pageNumber >= 1 && pageNumber <= props.lastPage && pageNumber !== props.currentPage) {
        emit('change-page', pageNumber);
    }
};

const goToPrevious = () => {
    if (props.currentPage > 1) {
        goToPage(props.currentPage - 1);
    }
};

const goToNext = () => {
    if (props.currentPage < props.lastPage) {
        goToPage(props.currentPage + 1);
    }
};
</script>

<template>
    <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <!-- Информация о результатах -->
        <div class="text-sm text-muted-foreground">
            Показано {{ from }} - {{ to }} из {{ total }} результатов
        </div>

        <!-- Компонент пагинации -->
        <Pagination>
            <PaginationContent class="flex flex-wrap justify-center gap-1">
                <!-- Предыдущая страница -->
                    <PaginationPrevious
                        @click="goToPrevious"
                        :disabled="currentPage === 1"
                        class="h-9 px-3"
                    />

                <!-- Первая страница -->
                <PaginationItem v-if="showFirstPage">
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="goToPage(1)"
                        class="h-9 w-9 p-0"
                    >
                        1
                    </Button>
                </PaginationItem>

                <!-- Точки слева -->
                <PaginationEllipsis :index="4" />

                <!-- Номера страниц -->
                <PaginationItem v-for="pageNum in visiblePages" :key="pageNum" :value="pageNum">
                    <Button
                        :variant="pageNum === currentPage ? 'default' : 'ghost'"
                        size="sm"
                        @click="goToPage(pageNum)"
                        class="h-9 w-9 p-0"
                    >
                        {{ pageNum }}
                    </Button>
                </PaginationItem>

                <!-- Точки справа -->
                <PaginationItem v-if="showRightDots" :value="0">
                    <span class="px-2 text-muted-foreground">...</span>
                </PaginationItem>

                <!-- Последняя страница -->
                <PaginationItem v-if="showLastPage" :value="lastPage">
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="goToPage(lastPage)"
                        class="h-9 w-9 p-0"
                    >
                        {{ lastPage }}
                    </Button>
                </PaginationItem>

                <!-- Следующая страница -->
                    <PaginationNext
                        @click="goToNext"
                        :disabled="currentPage === lastPage"
                        class="h-9 px-3"
                    />
            </PaginationContent>
        </Pagination>
    </div>
</template>
