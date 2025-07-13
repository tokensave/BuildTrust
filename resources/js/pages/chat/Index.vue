<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from '@/components/ui/card';
import { Avatar, AvatarImage } from '@/components/ui/avatar';
// import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { MessageSquare, User, Clock } from 'lucide-vue-next';
import { format } from 'date-fns';
import type { SharedData, User as UserType } from '@/types';
import { toast } from 'vue-sonner';

const page = usePage<SharedData>();
const user = page.props.auth.user as UserType;
const threads = page.props.threads || [];

const breadcrumbs = [
    { title: 'Главная', href: '/dashboard' },
    { title: 'Мои сообщения', href: route('chats.index', { user: user.id }) },
];

function openChat(threadId: number) {
    router.visit(route('chats.show', { user: user.id, thread: threadId }));
}

function confirmDelete(threadId: number) {
    if (confirm('Вы уверены, что хотите удалить этот чат?')) {
        deleteThread(threadId);
    }
}

function deleteThread(threadId: number) {
    router.delete(route('chats.delete-thread', { thread: threadId }), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            toast.success('Чат удален!', {
                duration: 3000,
            });
        }
    });
}

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Мои чаты" />
        <div class="p-4 space-y-6">
            <div v-if="threads.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <Card v-for="thread in threads" :key="thread.id" class="overflow-hidden shadow">
                    <CardHeader class="flex gap-4 items-center">
                        <Avatar class="h-32 w-32 rounded-2xl">
                            <AvatarImage :src="thread.ad.image_url ?? '/default.jpg'" alt="Объявление" />
                        </Avatar>
                        <div class="flex-1">
                            <CardTitle class="text-base truncate">
                                {{ thread.ad.title }}
                            </CardTitle>
                            <CardDescription class="text-xs text-muted-foreground">
                                <User class="inline w-4 h-4 mr-1" />
                                {{
                                    thread.participants
                                        .filter(p => p.id !== user.id)
                                        .map(p => p.username)
                                        .join(', ') || '—'
                                }}
                            </CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div class="text-sm flex items-center text-muted-foreground">
                            <MessageSquare class="w-4 h-4 mr-1" />
                            {{ thread.latest_message?.content || 'Нет сообщений' }}
                        </div>
                        <div class="text-xs text-gray-500 flex items-center">
                            <Clock class="w-4 h-4 mr-1" />
                            {{ thread.latest_message ? format(new Date(thread.latest_message.created_at), 'dd.MM.yyyy HH:mm') : '—' }}
                        </div>
                        <div class="flex gap-2">
                            <Button variant="outline" size="sm" @click="openChat(thread.id)">Открыть</Button>
                            <Button variant="destructive" size="sm" @click="confirmDelete(thread.id)">Удалить</Button>
                        </div>
                    </CardContent>

                </Card>
            </div>

            <div v-else class="text-center text-gray-500">У вас пока нет чатов.</div>
        </div>
    </AppLayout>
</template>
