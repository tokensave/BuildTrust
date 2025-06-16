<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'vue-sonner'; // Правильный импорт toast
import { type SharedData, type BreadcrumbItem, type Deal, type DealStatus, type StatusConfig } from '@/types';

const page = usePage<SharedData>();
const deals = ref(page.props.deals as Deal[]);
const statuses = page.props.statuses as Record<DealStatus, StatusConfig>;

const showDealDialog = ref(false);
const selectedDeal = ref<Deal | null>(null);
const selectedStatus = ref<DealStatus>('');

function viewDeal(deal: Deal) {
    selectedDeal.value = deal;
    selectedStatus.value = deal.status;
    showDealDialog.value = true;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Главная', href: '/dashboard' },
    { title: 'Мои сделки', href: route('user.deals.index') },
];

// Следим за изменениями в page.props.success для отображения уведомления
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

// Следим за ошибками (если они передаются через Inertia)
watch(
    () => page.props.errors,
    (newErrors) => {
        if (newErrors && Object.keys(newErrors).length > 0) {
            toast.error(Object.values(newErrors).join(', '), {
                duration: 5000, // Ошибки показываем 5 секунд
            });
        }
    }
);

function updateStatus() {
    if (selectedDeal.value && selectedStatus.value) {
        router.post(route('user.deals.updateStatus', selectedDeal.value.id), {
            status: selectedStatus.value
        }, {
            onSuccess: (page) => {
                if (selectedDeal.value) {
                    selectedDeal.value.status = selectedStatus.value;
                    const dealIndex = deals.value.findIndex(d => d.id === selectedDeal.value!.id);
                    if (dealIndex !== -1) {
                        deals.value[dealIndex].status = selectedStatus.value;
                    }
                    showDealDialog.value = false; // Закрываем диалог
                }
            },
            onError: (errors) => {
                toast.error(Object.values(errors).join(', '), {
                    duration: 5000,
                });
            },
            preserveScroll: true,
            preserveState: true
        });
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Мои сделки" />

        <div class="p-4 space-y-6">
            <Table v-if="deals.length">
                <TableHeader>
                    <TableRow>
                        <TableHead>№</TableHead>
                        <TableHead>Статус</TableHead>
                        <TableHead>Объявление</TableHead>
                        <TableHead>Покупатель</TableHead>
                        <TableHead>Продавец</TableHead>
                        <TableHead>Цена</TableHead>
                        <TableHead></TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="deal in deals" :key="deal.id">
                        <TableCell>{{ deal.id }}</TableCell>
                        <TableCell>
                            <span :class="['text-xs px-2 py-1 rounded-full', statuses[deal.status]?.color]">
                                {{ statuses[deal.status]?.label }}
                            </span>
                        </TableCell>
                        <TableCell>{{ deal.ad_title }}</TableCell>
                        <TableCell>
                            {{ deal.buyer.name }}
                            <small class="text-gray-500">{{ deal.buyer.company?.name }}</small>
                        </TableCell>
                        <TableCell>
                            {{ deal.seller.name }}
                            <small class="text-gray-500">{{ deal.seller.company?.name }}</small>
                        </TableCell>
                        <TableCell>{{ deal.price }} ₽</TableCell>
                        <TableCell>
                            <Button variant="outline" size="sm" @click="viewDeal(deal)">Посмотреть</Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <div v-else class="text-center text-gray-500">У вас пока нет сделок.</div>

            <Dialog v-model:open="showDealDialog">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ selectedDeal?.ad_title }}</DialogTitle>
                    </DialogHeader>
                    <div v-if="selectedDeal" class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm mt-4">
                        <div class="text-gray-500">Статус:</div>
                        <div>
                            <Select v-model="selectedStatus" @update:modelValue="updateStatus">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue :placeholder="statuses[selectedDeal.status]?.label" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="(status, key) in statuses" :key="key" :value="status.value">
                                        {{ status.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="text-gray-500">Комментарий:</div>
                        <div>{{ selectedDeal.notes || '—' }}</div>
                        <div class="col-span-2" v-if="selectedDeal.documents?.length">
                            <div class="text-gray-500 font-medium mb-2">Документы:</div>
                            <ul class="list-disc list-inside text-blue-600 text-sm">
                                <li v-for="doc in selectedDeal.documents" :key="doc.id">
                                    <a :href="doc.url" target="_blank">{{ doc.name }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
