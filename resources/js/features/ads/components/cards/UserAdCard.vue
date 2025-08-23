<script setup lang="ts">
/**
 * 📢 UserAdCard - Карточка объявления в личном кабинете
 * 
 * Рефакторинг с использованием новых композаблов:
 * - useNotifications для уведомлений
 * - StatusBadge для статусов
 * - Типизация из ad domain
 */

import { Card, CardDescription, CardTitle } from '@/components/ui/card'
import { Link, router, usePage } from '@inertiajs/vue3'
import { Trash2 } from 'lucide-vue-next'
import type { Ad } from '@/features/ads/types/ad'
import type { SharedData, User } from '@/types'
import { useNotifications } from '@/shared/composables/useNotifications'
import { useConfirmDialog } from '@/shared/composables/ui/useConfirmDialog'
import StatusBadge from '@/shared/components/feedback/StatusBadge.vue'
import SpecialBadge from '../display/SpecialBadge.vue'
import { formatPrice } from '@/shared/utils/formatting'

// Props с новой типизацией
interface Props {
    ad: Ad
}

const props = defineProps<Props>()

// Композаблы
const page = usePage<SharedData>()
const user = page.props.auth.user as User
const { showAdDeleted, showError } = useNotifications()
const { confirm } = useConfirmDialog()

// Удаление объявления с подтверждением
const handleDelete = async () => {
    const isConfirmed = await confirm({
        title: 'Удаление объявления',
        message: 'Вы уверены, что хотите удалить объявление?',
        confirmText: 'Удалить',
        cancelText: 'Отмена'
    })

    if (!isConfirmed) return

    router.delete(route('user.ads.destroy', { user: user.id, ad: props.ad.id }), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => showAdDeleted(),
        onError: () => showError('Невозможно удалить объявление при наличии незакрытых сделок.')
    })
}
</script>

<template>
    <Card class="group relative overflow-hidden p-0">
        <button
            @click.stop="handleDelete"
            class="absolute top-2 right-2 z-10 opacity-0 transition-opacity group-hover:opacity-100 p-1 rounded-full bg-white/90 hover:bg-white shadow-sm"
            title="Удалить объявление"
        >
            <Trash2 class="h-4 w-4 text-red-600 hover:text-red-700" />
        </button>

        <Link
            :href="route('user.ads.edit', { user: user.id, ad: props.ad.id })"
            class="relative block"
        >
            <img
                :src="props.ad.image_url"
                alt="Preview"
                class="h-48 w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />

            <!-- Статус -->
            <div class="absolute top-2 left-2 z-10">
                <StatusBadge :status="props.ad.status" type="ad" size="sm" />
            </div>

            <!-- Бейджи сверху справа -->
            <div class="absolute top-2 right-12 z-10 flex gap-1">
                <SpecialBadge 
                    v-if="props.ad.is_urgent" 
                    type="urgent" 
                    size="sm"
                />
                <SpecialBadge 
                    v-if="props.ad.is_service" 
                    type="service" 
                    size="sm"
                />
            </div>

            <!-- Информация при наведении -->
            <div class="absolute inset-0 flex flex-col justify-end bg-black/50 p-4 text-white opacity-0 transition-opacity group-hover:opacity-100">
                <CardTitle class="mb-2">{{ props.ad.title }}</CardTitle>

                <!-- Категории -->
                <div v-if="props.ad.formatted_category" class="text-xs text-gray-300 mb-1">
                    {{ props.ad.formatted_category }}
                </div>

                <!-- Местоположение и цена -->
                <div class="flex justify-between items-center mb-2 text-sm">
                    <span v-if="props.ad.location" class="text-gray-300">
                        📍 {{ props.ad.location }}
                    </span>
                    <span class="font-semibold text-green-300">
                        {{ formatPrice(props.ad.price) }}
                    </span>
                </div>

                <CardDescription class="text-gray-200 text-sm">
                    {{ props.ad.description }}
                </CardDescription>

                <!-- Характеристики -->
                <div class="mt-2">
                    <div v-if="props.ad.features && props.ad.features.length > 0" class="flex flex-wrap gap-1">
                        <span
                            v-for="feature in props.ad.features.slice(0, 3)"
                            :key="feature"
                            class="bg-white/20 text-white px-2 py-0.5 rounded text-xs"
                        >
                            {{ feature }}
                        </span>
                        <span v-if="props.ad.features.length > 3" class="text-xs text-gray-300">
                            +{{ props.ad.features.length - 3 }}
                        </span>
                    </div>
                </div>
            </div>
        </Link>
    </Card>
</template>
