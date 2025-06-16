<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input'; // Вернем Input для простоты
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import type { SharedData, User, Thread } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { ref, computed, nextTick, onMounted } from 'vue';
import { Send } from 'lucide-vue-next';

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user as User | null);
const thread = page.props.thread as Thread;
const messageContent = ref('');
const scrollAreaInner = ref<HTMLElement | null>(null);

// Получатель
const recipient = computed(() => {
    return thread.participants.find(p => p.id !== user.value?.id);
});

// Хлебные крошки
const breadcrumbs = computed(() => [
    { title: 'Главная', href: '/dashboard' },
    { title: 'Мои чаты', href: route('chats.index') },
    { title: thread.ad?.title ?? 'Чат', href: '' },
]);

// Упорядоченные сообщения
const orderedMessages = computed(() => {
    return [...thread.messages].sort((a, b) =>
        new Date(a.created_at).getTime() - new Date(b.created_at).getTime()
    );
});

// Отправка сообщения
const sendMessage = async () => {
    if (!user.value || !messageContent.value.trim() || !recipient.value) return;

    const url = route('chats.messages.store', {
        ad: thread.ad.id,
        recipient: recipient.value.id
    });

    router.post(url, {
        content: messageContent.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            messageContent.value = '';
            scrollToBottom();
        },
    });
};

// Прокрутка вниз
const scrollToBottom = () => {
    nextTick(() => {
        if (scrollAreaInner.value) {
            scrollAreaInner.value.scrollTop = scrollAreaInner.value.scrollHeight;
        }
    });
};

onMounted(() => {
    scrollToBottom();
});

function confirmDeleteMessage(messageId: number) {
    if (confirm('Удалить это сообщение?')) {
        deleteMessage(messageId);
    }
}

function deleteMessage(messageId: number) {
    const url = route('messages.delete', { message: messageId });

    router.delete(url, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            // Inertia автоматически обновит thread, если backend правильно вернёт его
        },
        onError: () => {
            alert('Ошибка при удалении сообщения');
        },
    });
}


</script>

<template>
    <AppLayout v-if="user" :breadcrumbs="breadcrumbs">
        <Head :title="thread.ad?.title ?? 'Чат'" />

        <div class="space-y-6 p-4">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold">
                        Чат по объявлению: {{ thread.ad?.title ?? 'Без объявления' }}
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        Собеседник: {{ recipient?.username ?? 'Неизвестный пользователь' }}
                    </p>
                </div>
            </div>

            <Separator />

            <!-- Chat -->
            <ScrollArea class="h-[500px] border rounded-xl bg-muted/50">
                <div ref="scrollAreaInner" class="h-full p-4">
                    <div class="flex flex-col space-y-3">
                        <div
                            v-for="message in orderedMessages"
                            :key="message.id"
                            :class="[
                'flex',
                message.author_id === user.id ? 'justify-end' : 'justify-start',
              ]"
                        >
                            <div
                                :class="[
                  'rounded-2xl p-3 shadow-sm max-w-[70%]',
                  message.author_id === user.id
                    ? 'bg-gray-100 text-black'
                    : 'bg-white text-black border',
                ]"
                            >
                                <p class="text-sm whitespace-pre-line">{{ message.content }}</p>
                                <p class="text-xs mt-1 text-muted-foreground text-right">
                                    {{ message.author.username }},
                                    {{ format(new Date(message.created_at), 'dd.MM.yyyy HH:mm') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </ScrollArea>

            <!-- Input Area -->
            <div class="flex gap-2">
                <Input
                    v-model="messageContent"
                    placeholder="Введите сообщение..."
                    @keyup.enter="sendMessage"
                    class="flex-1"
                />
                <Button @click="sendMessage" :disabled="!messageContent.trim()">
                    <Send class="w-4 h-4 mr-2" />
                    Отправить
                </Button>
            </div>
        </div>
    </AppLayout>

    <div v-else class="text-center text-muted-foreground py-12">
        Пожалуйста, войдите в систему.
    </div>
</template>
