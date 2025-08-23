<script setup lang="ts">
/**
 * 🏷️ SpecialBadge - Специальные бейджи для объявлений
 * 
 * Компонент для отображения специальных меток объявлений:
 * - "Срочно" для is_urgent
 * - "Услуга" для is_service
 */

import { computed } from 'vue'
import { Badge } from '@/components/ui/badge'

interface Props {
  type: 'urgent' | 'service'
  size?: 'sm' | 'md' | 'lg'
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md'
})

// Настройки для каждого типа бейджа
const badgeConfig = computed(() => {
  switch (props.type) {
    case 'urgent':
      return {
        label: 'Срочно',
        variant: 'destructive' as const,
        classes: 'bg-red-500 text-white border-red-600'
      }
    case 'service':
      return {
        label: 'Услуга', 
        variant: 'secondary' as const,
        classes: 'bg-blue-500 text-white border-blue-600'
      }
  }
})

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
      badgeConfig.classes,
      sizeClasses,
      'font-medium rounded-full'
    ]"
    :variant="badgeConfig.variant"
  >
    {{ badgeConfig.label }}
  </Badge>
</template>
