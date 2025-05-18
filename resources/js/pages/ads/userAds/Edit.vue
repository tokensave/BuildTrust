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
import { ref } from 'vue';
import ImagePreviewUploader from '@/components/ImagePreviewUploader.vue';

const page = usePage<SharedData & { ad: Ad }>();
const user = page.props.auth.user as User;
const ad = page.props.ad;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Главная', href: '/dashboard' },
    { title: 'Мои объявления', href: route('user.ads.index', user.id) },
    { title: 'Редактировать объявление', href: route('user.ads.edit', [user.id, ad.id]) },
];

const statusOptions = [
    { value: 'draft', label: 'Черновик' },
    { value: 'published', label: 'Опубликовано' },
    { value: 'archived', label: 'Архив' },
];

const form = useForm({
    title: ad.title,
    description: ad.description,
    price: ad.price,
    status: ad.status,
    images: [] as File[],
    deleted_media_ids: [] as number[],
});

const handleSubmit = () => {
    form.post(route('user.ads.update', [ad.user_id, ad.id]), {
        forceFormData: true,
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
                        </div>

                        <div>
                            <Label for="description" class="mb-1 block text-sm font-medium">Описание</Label>
                            <Textarea id="description" v-model="form.description" type="text" />
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-1">
                                <Label for="price" class="mb-1 block text-sm font-medium">Цена</Label>
                                <Input id="price" v-model="form.price" type="number" step="0.01" />
                            </div>

                            <div class="flex-1">
                                <Label for="status" class="mb-1 block text-sm font-medium">Статус</Label>
                                <Select v-model="form.status" class="w-full">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Выберите статус" />
                                    </SelectTrigger>
                                    <SelectContent class="w-full">
                                        <SelectItem
                                            v-for="option in statusOptions"
                                            :key="option.value"
                                            :value="option.value"
                                            class="w-full rounded border px-3 py-2 text-sm"
                                        >
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div>
                            <Label for="images" class="mb-1 block text-sm font-medium">Изображения</Label>

                            <ImagePreviewUploader class="mb-1 block text-sm font-medium"
                                v-model="form.images"
                                :existing-images="ad.media.map(m => ({ id: m.id, url: m.original_url }))"
                                @deletedMediaIds="form.deleted_media_ids = $event"
                            />
                        </div>

                        <Button type="submit" :disabled="form.processing">Сохранить</Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
