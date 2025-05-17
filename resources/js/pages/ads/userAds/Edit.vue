<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { ref, watch } from 'vue';
import { Textarea } from '@/components/ui/textarea';
import type { SharedData, Ad, BreadcrumbItem, User } from '@/types';

const page = usePage<SharedData & { ad: Ad }>();
const user = page.props.auth.user as User;
const ad = page.props.ad;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Главная',
        href: '/dashboard',
    },
    {
        title: 'Мои объявления',
        href: route('user.ads.index', user.id),
    },
    {
        title: 'Редактировать объявление',
        href: route('user.ads.edit', [user.id, ad.id]),
    },
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
});

const previews = ref<string[]>(
    ad.media?.map((m: any) => m.original_url) ?? []
);

const handleImagesChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        form.images = Array.from(target.files);
        previews.value = form.images.map(file => URL.createObjectURL(file));
    }
};

watch(() => form.images, (newImages, oldImages) => {
    oldImages?.forEach(file => URL.revokeObjectURL(file as any));
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
                            <Label for="title" class="block text-sm font-medium mb-1">Заголовок</Label>
                            <Input id="title" v-model="form.title" type="text" required />
                        </div>

                        <div>
                            <Label for="description" class="block text-sm font-medium mb-1">Описание</Label>
                            <Textarea id="description" v-model="form.description" type="text" />
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-1">
                                <Label for="price" class="block text-sm font-medium mb-1">Цена</Label>
                                <Input id="price" v-model="form.price" type="number" step="0.01" />
                            </div>

                            <div class="flex-1">
                                <Label for="status" class="block text-sm font-medium mb-1">Статус</Label>
                                <Select v-model="form.status" class="w-full">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Выберите статус" />
                                    </SelectTrigger>
                                    <SelectContent class="w-full">
                                        <SelectItem
                                            v-for="option in statusOptions"
                                            :key="option.value"
                                            :value="option.value"
                                            class="w-full border rounded px-3 py-2 text-sm"
                                        >
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div>
                            <Label for="images" class="block text-sm font-medium mb-1">Изображения</Label>
                            <Input id="images" type="file" multiple accept="image/*" @change="handleImagesChange" />
                            <div v-if="previews.length" class="flex gap-2 mt-2 flex-wrap">
                                <Avatar v-for="(src, i) in previews" :key="i" class="size-16">
                                    <AvatarImage :src="src" />
                                    <AvatarFallback>IMG</AvatarFallback>
                                </Avatar>
                            </div>
                        </div>

                        <Button type="submit" :disabled="form.processing">Сохранить</Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
