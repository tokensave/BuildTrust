<script setup lang="ts">
import InputError from '@/shared/components/forms/InputError.vue';
import TextLink from '@/shared/components/navigation/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    username: '',
    company_name: '',
    inn: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Создайте аккаунт" description="Введите данные">
        <Head title="Регистрация" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="username">Логин</Label>
                    <Input id="username" type="text" required autofocus autocomplete="username" v-model="form.username" placeholder="Придумайте логин" />
                    <InputError :message="form.errors.username" />
                </div>

                <div class="grid gap-2">
                    <Label for="company_name">Название компании</Label>
                    <Input id="company_name" type="text" required v-model="form.company_name" placeholder="ООО СтройИнвест" />
                    <InputError :message="form.errors.company_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="inn">ИНН компании</Label>
                    <Input id="inn" type="text" required v-model="form.inn" placeholder="1234567890" />
                    <InputError :message="form.errors.inn" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Электронная почта</Label>
                    <Input id="email" type="email" required autocomplete="email" v-model="form.email" placeholder="email@example.com" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Пароль</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="********"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Подтвердите пароль</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        placeholder="********"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    <span v-else>Создать аккаунт</span>
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Уже зарегистрированы?
                <TextLink :href="route('login')" class="underline underline-offset-4">Войдите</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
