<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import UserAdCard from '@/components/ads/UserAdCard.vue';
import { type SharedData, type BreadcrumbItem, type User } from '@/types';
import { CirclePlus } from 'lucide-vue-next';
// import { Button } from '@/components/ui/button';

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
const ads = page.props.ads;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Главная',
        href: '/dashboard',
    },
    {
        title: 'Мои объявления',
        href: route('user.ads.index', user.id),
    },
];
</script>

<template>
    <Head title="Мои объявления" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <div class="flex justify-end items-center">
                <Link
                    :href="route('user.ads.create', user.id)"
                    class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-200 transition"
                >
                    <CirclePlus class="w-4 h-4" />
                    Новое объявление
                </Link>
            </div>


            <div v-if="ads && ads.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <UserAdCard v-for="ad in ads" :key="ad.id" :ad="ad" />
            </div>

            <div v-else class="text-center text-gray-500 py-10">
                У вас пока нет объявлений.
            </div>
        </div>
    </AppLayout>
</template>
