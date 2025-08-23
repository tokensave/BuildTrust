<script setup lang="ts">
/**
 * 🏷️ StatusBadge - Универсальный компонент для отображения статусов
 *
 * Заменяет дублирование логики статусов в разных компонентах.
 * Получает данные о цветах и лейблах из централизованного источника.
 *
 * @example
 * ```vue
 * <StatusBadge :status="ad.status" type="ad" />
 * <StatusBadge :status="deal.status" type="deal" />
 * <StatusBadge status="published" type="ad" size="sm" />
 * ```
 */

import { computed } from 'vue'
import { Badge } from '@/components/ui/badge'
import { useEnums } from '@/shared/composables/data/useEnums'

interface Props {
    status: string
    type?: 'ad' | 'deal'
    size?: 'sm' | 'md' | 'lg'
    variant?: 'default' | 'outline'
}

const props = withDefaults(defineProps<Props>(), {
    type: 'ad',
    size: 'md',
    variant: 'default'
})

const { getStatusLabel, getStatusColor } = useEnums()

// Получаем лейбл и цвет статуса
const statusLabel = computed(() => getStatusLabel(props.status, props.type))
const statusColor = computed(() => getStatusColor(props.status, props.type))

// Размеры бейджа
const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm': return 'px-2 py-0.5 text-xs'
        case 'lg': return 'px-4 py-2 text-base'
        default: return 'px-3 py-1 text-sm'
    }
})
</script>

<template>
    <Badge
        :class="[
            statusColor,
            sizeClasses,
            'font-medium rounded-full'
        ]"
        :variant="variant"
    >
        {{ statusLabel }}
    </Badge>
</template>
