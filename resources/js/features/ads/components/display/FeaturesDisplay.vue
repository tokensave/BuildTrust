<script setup lang="ts">
import { Badge } from '@/components/ui/badge'

interface Props {
  features: string[]
  maxVisible?: number
  size?: 'sm' | 'md' | 'lg'
}

const props = withDefaults(defineProps<Props>(), {
  maxVisible: 3,
  size: 'md'
})

const sizeClasses = {
  sm: 'text-xs px-2 py-0.5',
  md: 'text-sm px-2 py-1',
  lg: 'text-base px-3 py-1.5'
}
</script>

<template>
  <div v-if="features && features.length > 0" class="flex flex-wrap gap-2">
    <Badge
      v-for="feature in features.slice(0, maxVisible)"
      :key="feature"
      variant="outline"
      :class="sizeClasses[size]"
    >
      {{ feature }}
    </Badge>
    
    <Badge
      v-if="features.length > maxVisible"
      variant="secondary"
      :class="sizeClasses[size]"
    >
      +{{ features.length - maxVisible }}
    </Badge>
  </div>
</template>
