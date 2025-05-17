<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { type User, type SharedData, type BreadcrumbItem } from '@/types';
import { ref, watch } from 'vue';
import { Label } from '@/components/ui/label';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';


const page = usePage<SharedData>();
const user = page.props.auth.user as User;

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
        title: 'Новое объявление',
        href: route('user.ads.create', user.id), // текущая страница
    },
];

// Возможные статусы
const statusOptions = [
    { value: 'draft', label: 'Черновик' },
    { value: 'published', label: 'Опубликовано' },
    { value: 'archived', label: 'Архив' },
];

const form = useForm({
    title: '',
    description: '',
    price: null as number | null,
    status: '',
    images: [] as File[],
});

const previews = ref<string[]>([]);

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
</script>

<template>
    <AppLayout  :breadcrumbs="breadcrumbs">
    <Head title="Новое объявление" />
        <div class="p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Новое объявление</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <form @submit.prevent="form.post(route('user.ads.store', user.id), { forceFormData: true })" class="space-y-4">
                        <div>
                            <Label for="title" class="block text-sm font-medium mb-1">Заголовок</Label>
                            <Input id="title" v-model="form.title" type="text" required />
                        </div>

                        <div>
                            <Label for="description" class="block text-sm font-medium mb-1">Описание</Label>
                            <Input id="description" v-model="form.description" type="text" />
                        </div>

                        <div>
                            <Label for="price" class="block text-sm font-medium mb-1">Цена</Label>
                            <Input id="price" v-model="form.price" type="number" step="0.01" min="0" />
                        </div>

                        <div>
                            <Label for="status" class="block text-sm font-medium mb-1">Статус</Label>
                            <Select v-model="form.status" class="w-full">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Выберите статус" />
                                </SelectTrigger>
                                <SelectContent class="w-full">
                                    <SelectItem
                                        class="w-full border rounded px-3 py-2 text-sm"
                                        v-for="option in statusOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label for="images" class="block text-sm font-medium mb-1">Изображения</Label>
                            <Input
                                id="images"
                                type="file"
                                multiple
                                accept="image/*"
                                @change="handleImagesChange"
                            />
                            <div v-if="previews.length" class="flex gap-2 mt-2 flex-wrap">
                                <Avatar
                                    v-for="(src, index) in previews"
                                    :key="index"
                                    class="size-16"
                                >
                                    <AvatarImage :src="src" alt="Превью" />
                                    <AvatarFallback>IMG</AvatarFallback>
                                </Avatar>
                            </div>
                        </div>

                        <Button type="submit" :disabled="form.processing">Создать</Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>


