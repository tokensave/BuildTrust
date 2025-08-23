<script setup lang="ts">
/**
 * 📝 Create Ad Page - Страница создания объявления
 *
 * Рефакторинг с использованием новых композаблов:
 * - useAdForm для логики формы
 * - useEnums для получения справочных данных
 * - FormField для унифицированных полей
 */

import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Checkbox } from '@/components/ui/checkbox'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem, type SharedData, type User } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { onMounted } from 'vue'

// Новые импорты
import { useAdForm } from '@/features/ads/composables/useAdForm'
import { useEnums } from '@/shared/composables/data/useEnums'
import FormField from '@/shared/components/forms/FormField.vue'
import ImagePreviewUploader from '@/shared/components/forms/ImagePreviewUploader.vue'
import FeaturesSelector from '@/features/ads/components/forms/FeaturesSelector.vue'

const page = usePage<SharedData>()
const user = page.props.auth.user as User

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Главная', href: '/dashboard' },
    { title: 'Мои объявления', href: route('user.ads.index', user.id) },
    { title: 'Новое объявление', href: route('user.ads.create', user.id) }
]

// Композаблы
const {
    form,
    availableCategories,
    availableSubcategories,
    submitCreate,
    saveAsDraft,
    publish,
    addImages,
    removeImage,
    addFeature,
    removeFeature,
    isFormValid
} = useAdForm()

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const { enums, loadEnums, getAdTypes } = useEnums()

// Загружаем enum'ы при монтировании
onMounted(async () => {
    await loadEnums()
})

// Обработчики
const handleSubmit = () => submitCreate(user.id)
const handleSaveAsDraft = () => saveAsDraft(user.id)
const handlePublish = () => publish(user.id)
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <Card>
                <CardContent class="p-6">
                    <form @submit.prevent="handleSubmit" class="space-y-6">
                        <!-- Основная информация -->
                        <div class="space-y-4">
                            <h2 class="text-lg font-semibold text-foreground">Основная информация</h2>

                            <FormField
                                label="Заголовок"
                                v-model="form.title"
                                :error="form.errors.title"
                                placeholder="Введите заголовок объявления"
                                required
                            />

                            <FormField
                                label="Описание"
                                v-model="form.description"
                                type="textarea"
                                :error="form.errors.description"
                                placeholder="Опишите товар или услугу подробно"
                                hint="Чем подробнее описание, тем больше заинтересованных покупателей"
                                required
                            />
                        </div>

                        <!-- Категории -->
                        <div class="space-y-4">
                            <h2 class="text-lg font-semibold text-foreground">Категория</h2>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Тип объявления -->
                                <div>
                                    <label class="text-sm font-medium text-foreground mb-2 block">
                                        Тип объявления <span class="text-destructive">*</span>
                                    </label>
                                    <Select v-model="form.type" required>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Выберите тип объявления" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="option in getAdTypes"
                                                :key="option.value"
                                                :value="option.value"
                                            >
                                                {{ option.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.type" class="text-sm text-destructive mt-1">{{ form.errors.type }}</p>
                                    <!-- Описание выбранного типа снизу -->
                                    <div
                                        v-if="form.type"
                                        class="text-xs text-muted-foreground mt-1 min-h-[20px]"
                                    >
                                        {{ getAdTypes.find(t => t.value === form.type)?.description }}
                                    </div>
                                </div>

                                <!-- Категория -->
                                <div>
                                    <label class="text-sm font-medium text-foreground mb-2 block">Категория</label>
                                    <Select v-model="form.category" :disabled="!form.type">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Выберите категорию" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="[key, category] in Object.entries(availableCategories)"
                                                :key="key"
                                                :value="key"
                                            >
                                                {{ category.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.category" class="text-sm text-destructive mt-1">{{ form.errors.category }}</p>
                                </div>

                                <!-- Подкатегория -->
                                <div>
                                    <label class="text-sm font-medium text-foreground mb-2 block">Подкатегория</label>
                                    <Select v-model="form.subcategory" :disabled="!form.category">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Выберите подкатегорию" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="[key, subcategory] in Object.entries(availableSubcategories)"
                                                :key="key"
                                                :value="key"
                                            >
                                                {{ subcategory.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.subcategory" class="text-sm text-destructive mt-1">{{ form.errors.subcategory }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Цена и местоположение -->
                        <div class="space-y-4">
                            <h2 class="text-lg font-semibold text-foreground">Цена и местоположение</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <FormField
                                    label="Цена"
                                    v-model="form.price"
                                    type="number"
                                    :error="form.errors.price"
                                    placeholder="0"
                                    hint="Оставьте пустым, если цена договорная"
                                    step="0.01"
                                    min="0"
                                />

                                <FormField
                                    label="Местоположение"
                                    v-model="form.location"
                                    :error="form.errors.location"
                                    placeholder="Город, район"
                                    hint="Укажите где находится товар или оказываются услуги"
                                />
                            </div>
                        </div>

                        <!-- Характеристики -->
                        <div class="space-y-4">
                            <h2 class="text-lg font-semibold text-foreground">Характеристики</h2>
                            <FeaturesSelector
                                v-model="form.features"
                                :category="form.category"
                                :max-features="5"
                            />
                            <p v-if="form.errors.features" class="text-sm text-destructive">{{ form.errors.features }}</p>
                        </div>

                        <!-- Изображения -->
                        <div class="space-y-4">
                            <h2 class="text-lg font-semibold text-foreground">Изображения</h2>
                            <ImagePreviewUploader v-model="form.images" />
                            <p v-if="form.errors.images" class="text-sm text-destructive">{{ form.errors.images }}</p>
                        </div>

                        <!-- Дополнительные параметры -->
                        <div class="space-y-4">
                            <h2 class="text-lg font-semibold text-foreground">Дополнительные параметры</h2>

                            <div class="flex items-center space-x-2">
                                <Checkbox
                                    id="is_urgent"
                                    v-model="form.is_urgent"
                                />
                                <label for="is_urgent" class="text-sm font-medium cursor-pointer">
                                    Срочное объявление
                                </label>
                            </div>
                        </div>

                        <!-- Кнопки действий -->
                        <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t">
                            <Button
                                type="button"
                                variant="outline"
                                @click="handleSaveAsDraft"
                                :disabled="form.processing || !isFormValid"
                                class="flex-1 sm:flex-none"
                            >
                                Сохранить как черновик
                            </Button>

                            <Button
                                type="button"
                                @click="handlePublish"
                                :disabled="form.processing || !isFormValid"
                                class="flex-1 sm:flex-none"
                            >
                                Опубликовать
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
