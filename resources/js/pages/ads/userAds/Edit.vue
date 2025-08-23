<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Ad, BreadcrumbItem, SharedData, User } from '@/types';
import { useForm, usePage } from '@inertiajs/vue3';
import ImagePreviewUploader from '@/shared/components/forms/ImagePreviewUploader.vue'
import InputError from '@/shared/components/forms/InputError.vue'
import { toast } from 'vue-sonner';
import { computed, watch, onMounted } from 'vue';
import { useEnums } from '@/shared/composables/data/useEnums';
import FeaturesSelector from '@/features/ads/components/forms/FeaturesSelector.vue'

const page = usePage<SharedData & { ad: Ad }>();
const user = page.props.auth.user as User;
const ad = page.props.ad;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Главная', href: '/dashboard' },
    { title: 'Мои объявления', href: route('user.ads.index', user.id) },
    { title: 'Редактировать объявление', href: route('user.ads.edit', [user.id, ad.id]) },
];

const form = useForm({
    title: ad.title,
    type: ad.type,
    category: ad.category || '',
    subcategory: ad.subcategory || '',
    description: ad.description,
    price: ad.price,
    status: ad.status,
    location: ad.location || '',
    is_urgent: ad.is_urgent,
    features: ad.features || [],
    images: [] as File[],
    deleted_media_ids: [] as number[],
});

// Композаблы для работы с enum'ами
const { 
    enums, 
    loadEnums, 
    getAdTypes, 
    getCategoriesByType, 
    getSubcategoriesByCategory 
} = useEnums();

// Загружаем enum'ы при монтировании
onMounted(async () => {
    await loadEnums();
});

// Вычисляем доступные категории на основе типа
const availableCategories = computed(() => {
    return getCategoriesByType(form.type);
});

// Вычисляем доступные подкатегории на основе выбранной категории
const availableSubcategories = computed(() => {
    return getSubcategoriesByCategory(form.category);
});

// Сбрасываем категорию и подкатегорию при смене типа
watch(() => form.type, () => {
    form.category = '';
    form.subcategory = '';
});

// Сбрасываем подкатегорию при смене категории
watch(() => form.category, () => {
    form.subcategory = '';
});

const handleSubmit = () => {
    form.post(route('user.ads.update', [user.id, ad.id]), {
        forceFormData: true,
        onSuccess: () => {
            toast.success('Объявление успешно обновлено', {
                duration: 3000,
            });
        },
        onError: () => {
            toast.error('Ошибка при сохранении.', {
                duration: 5000,
            });
        }
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <Card>
                <CardContent class="space-y-4">
                    <form @submit.prevent="handleSubmit" class="space-y-4">
                        <div>
                            <Label for="title" class="mb-1 block text-sm font-medium">Заголовок</Label>
                            <Input id="title" v-model="form.title" type="text" required />
                            <InputError :message="form.errors.title" />
                        </div>

                        <div>
                            <Label for="description" class="mb-1 block text-sm font-medium">Описание</Label>
                            <Textarea id="description" v-model="form.description" type="text" />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-1">
                                <Label for="price" class="mb-1 block text-sm font-medium">Цена</Label>
                                <Input id="price" v-model="form.price" type="number" step="0.01" min="0" />
                                <InputError :message="form.errors.price" />
                            </div>

                            <div class="flex-1">
                                <Label for="status" class="mb-1 block text-sm font-medium">Статус</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Выберите статус" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="option in enums.adStatuses"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.status" />
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-1">
                                <Label for="type" class="mb-1 block text-sm font-medium">Тип объявления</Label>
                                <Select v-model="form.type">
                                    <SelectTrigger class="w-full">
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
                                <InputError :message="form.errors.type" />
                                <div
                                    v-if="form.type"
                                    class="text-xs text-muted-foreground mt-1 min-h-[20px]"
                                >
                                    {{ getAdTypes.find(t => t.value === form.type)?.description }}
                                </div>
                            </div>

                            <div class="flex-1">
                                <Label for="category" class="mb-1 block text-sm font-medium">Категория</Label>
                                <Select v-model="form.category" :disabled="!form.type">
                                    <SelectTrigger class="w-full">
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
                                <InputError :message="form.errors.category" />
                            </div>

                            <div class="flex-1">
                                <Label for="subcategory" class="mb-1 block text-sm font-medium">Подкатегория</Label>
                                <Select v-model="form.subcategory" :disabled="!form.category">
                                    <SelectTrigger class="w-full">
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
                                <InputError :message="form.errors.subcategory" />
                            </div>
                        </div>

                        <div>
                            <Label for="location" class="mb-1 block text-sm font-medium">Местоположение</Label>
                            <Input id="location" v-model="form.location" type="text"/>
                            <InputError :message="form.errors.location" />
                        </div>

                        <!-- Характеристики -->
                        <div>
                            <FeaturesSelector
                                v-model="form.features"
                                :category="form.category"
                                :max-features="5"
                            />
                            <InputError :message="form.errors.features" />
                        </div>

                        <div class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                id="is_urgent"
                                v-model="form.is_urgent"
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                            />
                            <Label for="is_urgent" class="text-sm font-medium">Срочное объявление</Label>
                        </div>

                        <div>
                            <Label for="images" class="mb-1 block text-sm font-medium">Изображения</Label>
                            <ImagePreviewUploader
                                v-model="form.images"
                                :existing-images="ad.media?.map(m => ({ id: m.id, url: m.original_url })) || []"
                                @update:deletedMediaIds="form.deleted_media_ids = $event"
                            />
                        </div>

                        <Button type="submit" :disabled="form.processing">Сохранить</Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
