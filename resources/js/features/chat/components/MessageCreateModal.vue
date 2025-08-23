<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import InputError from '@/shared/components/forms/InputError.vue';
import { Message, type SharedData } from '@/types';
import { watch, ref } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
    adId: number;
    recipientId: number;
    open: boolean;
    onClose: () => void;
}

const props = defineProps<Props>();
const page = usePage<SharedData>();
const messages = ref<Message>([]);

const form = useForm({
    content: '',
    general: '',
});

const open = ref(props.open);

watch(() => props.open, (val) => {
    open.value = val;
});

watch(open, (val) => {
    if (!val) {
        form.reset();
        props.onClose();
    }
});


watch(
    () => page.props.success,
    (newSuccess) => {
        if (newSuccess) {
            toast.success(newSuccess, {
                duration: 3000,
            });
        }
    }
);

watch(
    () => page.props.errors,
    (newErrors) => {
        if (newErrors && Object.keys(newErrors).length > 0 && !form.processing) {
            toast.error(Object.values(newErrors).join(', '), {
                duration: 5000,
            });
        }
    }
);
const handleSubmit = () => {
    form.post(route('chats.messages.store', { ad: props.adId, recipient: props.recipientId }), {
        onSuccess: () => {
            toast.success('Сообщение отправлено!', {
                duration: 3000,
            });
            form.reset();
            props.onClose();
        },
        onError: (errors) => {
            form.errors.general = errors.general || 'Не удалось отправить сообщение';
        },
    });
};


</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="space-y-6 max-w-xl">
            <DialogHeader>
                <DialogTitle class="text-xl font-semibold">Написать сообщение</DialogTitle>
            </DialogHeader>

            <div class="space-y-2 max-h-64 overflow-y-auto">
                <div v-for="message in messages" :key="message.id" class="p-2 border rounded">
                    <p>{{ message.content }}</p>
                    <small>{{ message.created_at }}</small>
                </div>
                <p v-if="form.errors.general" class="text-red-500 text-sm">{{ form.errors.general }}</p>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-5">
                <div class="space-y-2">
                    <Label class="text-sm font-medium text-muted-foreground">Сообщение</Label>
                    <Textarea
                        v-model="form.content"
                        rows="5"
                        class="w-full"
                        placeholder="Введите ваше сообщение..."
                    />
                    <InputError :message="form.errors.content" />
                </div>

                <div class="flex justify-end gap-3">
                    <Button
                        variant="outline"
                        @click="onClose"
                        :disabled="form.processing"
                    >
                        Отмена
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-primary text-primary-foreground hover:bg-primary/90"
                    >
                        Отправить
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
