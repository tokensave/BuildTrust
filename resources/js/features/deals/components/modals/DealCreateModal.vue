<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import InputError from '@/shared/components/forms/InputError.vue';
import ImagePreviewUploader from '@/shared/components/forms/ImagePreviewUploader.vue';
import { watch, ref  } from 'vue';


interface Props {
    adId: number;
    sellerId: number;
    price: number;
    open: boolean;
    onClose: () => void;
}

const props = defineProps<Props>();

const form = useForm({
    notes: '',
    documents: [] as File[],
});

const open = ref(props.open);

watch(() => props.open, (val) => {
    open.value = val;
});

watch(open, (val) => {
    if (!val) {
        props.onClose();
    }
});

const handleSubmit = () => {
    form.post(route('deals.store', { ad: props.adId }), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            props.onClose();
        },
    });
};
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="space-y-6 max-w-xl">
            <DialogHeader>
                <DialogTitle class="text-xl font-semibold">Создание сделки</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-5">
                <div class="space-y-2">
                    <Label class="text-sm font-medium text-muted-foreground">Комментарий к сделке</Label>
                    <Textarea v-model="form.notes" rows="4" class="w-full" />
                    <InputError :message="form.errors.notes" />
                </div>

                <div class="space-y-2">
                    <Label class="text-sm font-medium text-muted-foreground">Документы (изображения)</Label>
                    <ImagePreviewUploader v-model="form.documents" />
                    <InputError :message="form.errors.documents" />
                </div>

                <div class="flex justify-end">
                    <Button type="submit" :disabled="form.processing">Создать сделку</Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
