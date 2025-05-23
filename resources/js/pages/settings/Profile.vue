<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Настройки профиля',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
const company = page.props.company;
const { getInitials } = useInitials();

const form = useForm({
    username: user.username ?? '',
    email: user.email,
    company_name: company?.name ?? '',
    inn: company?.inn ?? '',
    phone: company?.phone ?? '',
    city: company?.city ?? '',
    address: company?.address ?? '',
    website: company?.website ?? '',
    verified: company?.verified ?? '',
    avatar:   null as File | null,
});

const preview = ref<string | null>((user.media?.[0]?.original_url as string) || null);

const handleAvatarChange = (event: Event) => {
    const file = (event.target as HTMLInputElement)?.files?.[0] || null;
    form.avatar = file;

    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.post(route('profile.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Настройки" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Информация профиля" />

                <div class="grid gap-2 md:col-span-2">
                    <Label for="verified">Статус компании</Label>
                    <div class="flex items-center gap-2">
                        <span>{{ form.verified ? 'Подтверждена' : 'Не подтверждена' }}</span>
                        <svg
                            v-if="form.verified"
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-green-500"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414L8.414 15 4.707 11.293a1 1 0 111.414-1.414L8.414 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <svg
                            v-else
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-gray-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-4.586l-3.293-3.293a1 1 0 111.414-1.414L9 11.586l4.293-4.293a1 1 0 011.414 1.414l-5 5a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                </div>

                <form @submit.prevent="submit" class="grid gap-6 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="username">Логин</Label>
                        <Input
                            id="username"
                            class="mt-1 block w-full"
                            v-model="form.username"
                            required
                            autocomplete="username"
                            placeholder="Логин или название компании"
                        />
                        <InputError class="mt-2" :message="form.errors.username" />
                    </div>

                    <div v-if="user.role === 'director'" class="grid gap-2">
                        <Label for="company_name">Название компании</Label>
                        <Input
                            id="company_name"
                            class="mt-1 block w-full"
                            v-model="form.company_name"
                            placeholder="Название вашей компании"
                        />
                        <InputError class="mt-2" :message="form.errors.company_name" />
                    </div>

                    <div v-if="user.role === 'director'" class="grid gap-2">
                        <Label for="inn">ИНН компании</Label>
                        <Input
                            id="inn"
                            class="mt-1 block w-full"
                            v-model="form.inn"
                            placeholder="ИНН вашей компании"
                        />
                        <InputError class="mt-2" :message="form.errors.inn" />
                    </div>

                    <div v-if="user.role === 'director'" class="grid gap-2">
                        <Label for="phone">Номер телефона</Label>
                        <Input
                            id="phone"
                            class="mt-1 block w-full"
                            v-model="form.phone"
                            placeholder="Номер телефона вашей компании"
                        />
                        <InputError class="mt-2" :message="form.errors.phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Электронная почта</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="email"
                            placeholder="Электронная почта"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="city">Город</Label>
                        <Input
                            id="city"
                            class="mt-1 block w-full"
                            v-model="form.city"
                            placeholder="Город вашей компании"
                        />
                        <InputError class="mt-2" :message="form.errors.city" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="address">Адрес</Label>
                        <Input
                            id="address"
                            class="mt-1 block w-full"
                            v-model="form.address"
                            placeholder="Адрес вашей компании"
                        />
                        <InputError class="mt-2" :message="form.errors.address" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="website">Сайт</Label>
                        <Input
                            id="website"
                            class="mt-1 block w-full"
                            v-model="form.website"
                            placeholder="Веб-сайт вашей компании"
                        />
                        <InputError class="mt-2" :message="form.errors.website" />
                    </div>

                    <div class="grid gap-2 md:col-span-2">
                        <Label for="avatar">Аватар</Label>
                        <div class="flex items-center gap-4">
                            <Avatar class="size-16">
                                <AvatarImage v-if="preview" :src="preview" alt="Аватар" />
                                <AvatarFallback>
                                    {{ getInitials(user.username) }}
                                </AvatarFallback>
                            </Avatar>
                            <Input id="avatar" type="file" accept="image/*" @change="handleAvatarChange" />
                        </div>
                        <InputError class="mt-2" :message="form.errors.avatar" />
                    </div>


                    <div v-if="mustVerifyEmail && !user.email_verified_at" class="md:col-span-2">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Ваша почта не подтверждена.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Нажмите сюда, чтобы отправить письмо с подтверждением.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            Ссылка для подтверждения отправлена на вашу почту.
                        </div>
                    </div>

                    <div class="flex items-center gap-4 md:col-span-2">
                        <Button :disabled="form.processing">Сохранить</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Фиксируем изменения...</p>
                        </Transition>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>


