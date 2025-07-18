<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { useForm, usePage } from '@inertiajs/vue3';
import ImagePreviewUploader from '@/components/ImagePreviewUploader.vue';
import InputError from '@/components/InputError.vue';
import { toast } from 'vue-sonner';

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Главная', href: '/dashboard', },
    { title: 'Мои объявления', href: route('user.ads.index', user.id), },
    { title: 'Новое объявление', href: route('user.ads.create', user.id), }// текущая страница},
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

const handleSubmit = () => {
    form.post(route('user.ads.store', user.id), {
        forceFormData: true,
        onSuccess: () => {
            toast.success('Объявление успешно создано', {
                duration: 3000,
            });
        },
        onError: () => {
            toast.error('Ошибка при создании объявления', {
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
                            <Input id="title" v-model="form.title" type="text"/>
                            <InputError :message="form.errors.title" />
                        </div>

                        <div>
                            <Label for="description" class="mb-1 block text-sm font-medium">Описание</Label>
                            <Textarea id="description" v-model="form.description" type="text"/>
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
                                <Select v-model="form.status" class="w-full">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Выберите статус" />
                                    </SelectTrigger>
                                    <SelectContent class="w-full">
                                        <SelectItem
                                            class="w-full rounded border px-3 py-2 text-sm"
                                            v-for="option in statusOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div>
                            <Label for="images" class="mb-1 block text-sm font-medium">Изображения</Label>
                            <ImagePreviewUploader v-model="form.images" />
                        </div>

                        <Button type="submit" :disabled="form.processing">Создать</Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
