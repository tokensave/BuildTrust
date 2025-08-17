<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import type { AdFilter, AdType, CategoriesStructure } from '@/types'

// ShadCN компоненты
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible'

// Иконки (если у вас есть lucide-vue-next)
import { Filter, X, ChevronDown, Search } from 'lucide-vue-next'

interface Props {
    filters?: AdFilter
    totalCount?: number
}

const props = withDefaults(defineProps<Props>(), {
    filters: () => ({}),
    totalCount: 0
})

// Состояние
const isFiltersOpen = ref(false)
const currentFilters = ref<AdFilter>({ ...props.filters })
const isLoading = ref(false)

// Синхронизация currentFilters с props.filters после обновления Inertia
watch(() => props.filters, (newFilters) => {
    currentFilters.value = { ...newFilters || {} };
}, { deep: true, immediate: true });

// Данные для селектов
const adTypes = ref<Record<string, AdType>>({})
const categoriesStructure = ref<CategoriesStructure>({})
const locations = ref<Record<string, string>>({})

// Вычисляемые свойства
const availableCategories = computed(() => {
    return Object.keys(categoriesStructure.value).reduce((acc, key) => {
        acc[key] = categoriesStructure.value[key].label
        return acc
    }, {} as Record<string, string>)
})

const availableSubcategories = computed(() => {
    if (!currentFilters.value.category || !categoriesStructure.value[currentFilters.value.category]) {
        return {}
    }

    return Object.keys(categoriesStructure.value[currentFilters.value.category].subcategories).reduce((acc, key) => {
        acc[key] = categoriesStructure.value[currentFilters.value.category!].subcategories[key].label
        return acc
    }, {} as Record<string, string>)
})

// Количество активных фильтров
const activeFiltersCount = computed(() => {
    return Object.values(currentFilters.value).filter(value =>
        value !== null && value !== undefined && value !== '' && value !== false
    ).length
})

// Текст активных фильтров для отображения
const activeFiltersText = computed(() => {
    const filters: string[] = []

    if (currentFilters.value.search) filters.push(`"${currentFilters.value.search}"`)
    if (currentFilters.value.type && adTypes.value[currentFilters.value.type]) {
        filters.push(adTypes.value[currentFilters.value.type].label)
    }
    if (currentFilters.value.category && availableCategories.value[currentFilters.value.category]) {
        filters.push(availableCategories.value[currentFilters.value.category])
    }
    if (currentFilters.value.subcategory && currentFilters.value.category && categoriesStructure.value[currentFilters.value.category]?.subcategories[currentFilters.value.subcategory]) {
        filters.push(categoriesStructure.value[currentFilters.value.category].subcategories[currentFilters.value.subcategory].label)
    }
    if (currentFilters.value.location) filters.push(currentFilters.value.location)
    if (currentFilters.value.min_price || currentFilters.value.max_price) {
        filters.push(`Цена: ${currentFilters.value.min_price || 0} - ${currentFilters.value.max_price || '∞'}`)
    }
    if (currentFilters.value.urgent) filters.push('Срочные')

    return filters.join(', ')
})

// Загрузка данных для фильтров
onMounted(async () => {
    try {
        const response = await fetch('/api/filters/structure')
        const data = await response.json()

        adTypes.value = data.types
        categoriesStructure.value = data.categories_structure

        const locationsResponse = await fetch('/api/filters/locations')
        locations.value = await locationsResponse.json()
    } catch (error) {
        console.error('Ошибка загрузки фильтров:', error)
    }
})

// Сброс подкатегории при изменении категории
watch(() => currentFilters.value.category, () => {
    currentFilters.value.subcategory = undefined
})

// Глобальный debounce для применения фильтров
const debouncedApplyFilters = useDebounceFn(() => {
    applyFilters()
}, 300)

// Отслеживание изменений поиска (свой debounce, но теперь через глобальный)
watch(() => currentFilters.value.search, () => {
    if (currentFilters.value.search !== props.filters.search) {
        debouncedApplyFilters()
    }
})

// Применение фильтров
// Применение фильтров
const applyFilters = () => {
    isLoading.value = true

    // Создаем копию фильтров и удаляем "all" значения
    const cleanFilters = { ...currentFilters.value }

    // Удаляем специальные значения
    Object.keys(cleanFilters).forEach(key => {
        if (cleanFilters[key] === 'all' ||
            cleanFilters[key] === undefined ||
            cleanFilters[key] === null ||
            cleanFilters[key] === '' ||
            cleanFilters[key] === false) {
            delete cleanFilters[key]
        }
    })

    router.get('/dashboard', cleanFilters, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false
        }
    })
}

// Сброс фильтров
const clearFilters = () => {
    currentFilters.value = {}
    applyFilters()
}

// Удаление конкретного фильтра
const removeFilter = (filterKey: keyof AdFilter) => {
    currentFilters.value[filterKey] = undefined
    if (filterKey === 'category') {
        currentFilters.value.subcategory = undefined
    }
    debouncedApplyFilters()
}

// Удаление фильтров цены
const removePriceFilter = () => {
    currentFilters.value.min_price = undefined
    currentFilters.value.max_price = undefined
    debouncedApplyFilters()
}

</script>

<template>
    <!-- Мобильная версия: кнопка с количеством фильтров -->
    <div class="mb-6">
        <!-- Строка с результатами и кнопкой фильтров -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-semibold">
                    Объявления
                    <span v-if="totalCount" class="text-muted-foreground font-normal">
            ({{ totalCount }})
          </span>
                </h2>
                <div v-if="isLoading" class="animate-spin h-4 w-4 border-2 border-primary border-t-transparent rounded-full" />
            </div>

            <div class="flex items-center gap-2">
                <!-- Активные фильтры в виде бейджей на десктопе -->
                <div class="hidden md:flex items-center gap-1 max-w-md flex-wrap">
                    <Badge
                        v-if="currentFilters.search"
                        variant="secondary"
                        class="cursor-pointer hover:bg-destructive hover:text-destructive-foreground"
                        @click="removeFilter('search')"
                    >
                        "{{ currentFilters.search }}"
                        <X class="ml-1 h-3 w-3" />
                    </Badge>
                    <Badge
                        v-if="currentFilters.type && adTypes[currentFilters.type]"
                        variant="secondary"
                        class="cursor-pointer hover:bg-destructive hover:text-destructive-foreground"
                        @click="removeFilter('type')"
                    >
                        {{ adTypes[currentFilters.type].label }}
                        <X class="ml-1 h-3 w-3" />
                    </Badge>
                    <Badge
                        v-if="currentFilters.category && availableCategories[currentFilters.category]"
                        variant="secondary"
                        class="cursor-pointer hover:bg-destructive hover:text-destructive-foreground"
                        @click="removeFilter('category')"
                    >
                        {{ availableCategories[currentFilters.category] }}
                        <X class="ml-1 h-3 w-3" />
                    </Badge>
                    <Badge
                        v-if="currentFilters.subcategory && currentFilters.category && categoriesStructure[currentFilters.category]?.subcategories[currentFilters.subcategory]"
                        variant="secondary"
                        class="cursor-pointer hover:bg-destructive hover:text-destructive-foreground"
                        @click="removeFilter('subcategory')"
                    >
                        {{ categoriesStructure[currentFilters.category].subcategories[currentFilters.subcategory].label }}
                        <X class="ml-1 h-3 w-3" />
                    </Badge>
                    <Badge
                        v-if="currentFilters.location"
                        variant="secondary"
                        class="cursor-pointer hover:bg-destructive hover:text-destructive-foreground"
                        @click="removeFilter('location')"
                    >
                        {{ currentFilters.location }}
                        <X class="ml-1 h-3 w-3" />
                    </Badge>
                    <Badge
                        v-if="currentFilters.min_price || currentFilters.max_price"
                        variant="secondary"
                        class="cursor-pointer hover:bg-destructive hover:text-destructive-foreground"
                        @click="removePriceFilter"
                    >
                        Цена: {{ currentFilters.min_price || 0 }} - {{ currentFilters.max_price || '∞' }}
                        <X class="ml-1 h-3 w-3" />
                    </Badge>
                    <Badge
                        v-if="currentFilters.urgent"
                        variant="secondary"
                        class="cursor-pointer hover:bg-destructive hover:text-destructive-foreground"
                        @click="removeFilter('urgent')"
                    >
                        Срочные
                        <X class="ml-1 h-3 w-3" />
                    </Badge>
                </div>

                <!-- Кнопка фильтров -->
                <Button
                    variant="outline"
                    @click="isFiltersOpen = !isFiltersOpen"
                    :class="{ 'bg-primary text-primary-foreground': activeFiltersCount > 0 }"
                >
                    <Filter class="h-4 w-4 mr-2" />
                    Фильтры
                    <Badge
                        v-if="activeFiltersCount > 0"
                        :variant="activeFiltersCount > 0 ? 'secondary' : 'outline'"
                        class="ml-2 h-5 px-1.5 text-xs"
                    >
                        {{ activeFiltersCount }}
                    </Badge>
                    <ChevronDown
                        class="h-4 w-4 ml-2 transition-transform"
                        :class="{ 'rotate-180': isFiltersOpen }"
                    />
                </Button>
            </div>
        </div>

        <!-- Показ активных фильтров на мобильных -->
        <div v-if="activeFiltersText && !isFiltersOpen" class="md:hidden mb-4">
            <p class="text-sm text-muted-foreground">
                Активные фильтры: {{ activeFiltersText }}
            </p>
        </div>

        <!-- Панель фильтров -->
        <Collapsible v-model:open="isFiltersOpen">
            <CollapsibleContent>
                <Card class="mb-4">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-base flex items-center justify-between">
                            <span>Фильтры поиска</span>
                            <Button
                                v-if="activeFiltersCount > 0"
                                variant="ghost"
                                size="sm"
                                @click="clearFilters"
                            >
                                Очистить все
                            </Button>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Поиск -->
                        <div class="space-y-2">
                            <Label for="search">Поиск</Label>
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    id="search"
                                    v-model="currentFilters.search"
                                    placeholder="Поиск по названию и описанию..."
                                    class="pl-10"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Тип -->
                            <div class="space-y-2">
                                <Label>Тип</Label>
                                <Select :model-value="currentFilters.type ?? 'all'" @update:model-value="(value) => { currentFilters.type = value === 'all' ? undefined : value; debouncedApplyFilters(); }">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Все типы" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Все типы</SelectItem>
                                        <SelectItem
                                            v-for="(type, key) in adTypes"
                                            :key="key"
                                            :value="key"
                                        >
                                            {{ type.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Категория -->
                            <div class="space-y-2">
                                <Label>Категория</Label>
                                <Select :model-value="currentFilters.category ?? 'all'" @update:model-value="(value) => { currentFilters.category = value === 'all' ? undefined : value; debouncedApplyFilters(); }">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Все категории" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Все категории</SelectItem>
                                        <SelectItem
                                            v-for="(label, key) in availableCategories"
                                            :key="key"
                                            :value="key"
                                        >
                                            {{ label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Подкатегория -->
                            <div class="space-y-2">
                                <Label>Подкатегория</Label>
                                <Select
                                    :model-value="currentFilters.subcategory ?? 'all'"
                                    :disabled="!currentFilters.category || Object.keys(availableSubcategories).length === 0"
                                    @update:model-value="(value) => { currentFilters.subcategory = value === 'all' ? undefined : value; debouncedApplyFilters(); }"
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Все подкатегории" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Все подкатегории</SelectItem>
                                        <SelectItem
                                            v-for="(label, key) in availableSubcategories"
                                            :key="key"
                                            :value="key"
                                        >
                                            {{ label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Местоположение -->
                            <div class="space-y-2">
                                <Label>Город</Label>
                                <Select :model-value="currentFilters.location ?? 'all'" @update:model-value="(value) => { currentFilters.location = value === 'all' ? undefined : value; debouncedApplyFilters(); }">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Все города" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Все города</SelectItem>
                                        <SelectItem
                                            v-for="(city, key) in locations"
                                            :key="key"
                                            :value="city"
                                        >
                                            {{ city }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <Separator />

                        <!-- Дополнительные фильтры -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Цена от -->
                            <div class="space-y-2">
                                <Label for="min-price">Цена от (₽)</Label>
                                <Input
                                    id="min-price"
                                    v-model.number="currentFilters.min_price"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    @blur="debouncedApplyFilters"
                                    @keyup.enter="debouncedApplyFilters"
                                />
                            </div>

                            <!-- Цена до -->
                            <div class="space-y-2">
                                <Label for="max-price">Цена до (₽)</Label>
                                <Input
                                    id="max-price"
                                    v-model.number="currentFilters.max_price"
                                    type="number"
                                    min="0"
                                    placeholder="∞"
                                    @blur="debouncedApplyFilters"
                                    @keyup.enter="debouncedApplyFilters"
                                />
                            </div>

                            <!-- Срочные -->
                            <div class="flex items-center space-x-2 pt-6">
                                <Checkbox
                                    id="urgent"
                                    v-model:checked="currentFilters.urgent"
                                    @update:checked="debouncedApplyFilters"
                                />
                                <Label for="urgent" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    Только срочные
                                </Label>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </CollapsibleContent>
        </Collapsible>
    </div>
</template>
