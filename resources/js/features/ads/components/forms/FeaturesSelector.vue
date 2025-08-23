<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { X, Plus } from 'lucide-vue-next'

interface Props {
  modelValue: string[]
  category?: string
  maxFeatures?: number
}

const props = withDefaults(defineProps<Props>(), {
  maxFeatures: 5
})

const emit = defineEmits<{
  'update:modelValue': [value: string[]]
}>()

const newFeature = ref('')
const featuresFromApi = ref<Record<string, { value: string; label: string }>>({})
const isLoading = ref(false)

// Загрузка характеристик при изменении категории
watch(() => props.category, async (newCategory) => {
  if (!newCategory) {
    featuresFromApi.value = {}
    return
  }
  
  try {
    isLoading.value = true
    const response = await fetch(`/api/filters/features?category=${newCategory}`)
    const data = await response.json()
    featuresFromApi.value = data
  } catch (error) {
    console.error('Ошибка загрузки характеристик:', error)
    featuresFromApi.value = {}
  } finally {
    isLoading.value = false
  }
}, { immediate: true })

// Предустановленные характеристики (теперь из API)
const predefinedFeatures = computed(() => {
  return Object.values(featuresFromApi.value).map(feature => feature.label)
})

// Доступные для добавления характеристики (исключаем уже выбранные)
const availableToAdd = computed(() => {
  return predefinedFeatures.value.filter(feature => !props.modelValue.includes(feature))
})

const addFeature = (feature: string) => {
  if (props.modelValue.length < props.maxFeatures && !props.modelValue.includes(feature)) {
    emit('update:modelValue', [...props.modelValue, feature])
  }
}

const removeFeature = (feature: string) => {
  emit('update:modelValue', props.modelValue.filter(f => f !== feature))
}

const addCustomFeature = () => {
  if (newFeature.value.trim() && 
      !props.modelValue.includes(newFeature.value.trim()) && 
      props.modelValue.length < props.maxFeatures) {
    emit('update:modelValue', [...props.modelValue, newFeature.value.trim()])
    newFeature.value = ''
  }
}
</script>

<template>
  <div class="space-y-3">
    <Label class="text-sm font-medium">Характеристики</Label>
    
    <!-- Выбранные характеристики -->
    <div v-if="modelValue.length > 0" class="flex flex-wrap gap-2">
      <Badge
        v-for="feature in modelValue"
        :key="feature"
        variant="secondary"
        class="cursor-pointer hover:bg-destructive hover:text-destructive-foreground"
        @click="removeFeature(feature)"
      >
        {{ feature }}
        <X class="ml-1 h-3 w-3" />
      </Badge>
    </div>
    
    <!-- Предустановленные варианты для выбора -->
    <div v-if="availableToAdd.length > 0 && modelValue.length < maxFeatures" class="space-y-2">
      <Label class="text-xs text-muted-foreground">Выберите из популярных:</Label>
      <div v-if="isLoading" class="text-sm text-muted-foreground">Загрузка...</div>
      <div v-else class="flex flex-wrap gap-2">
        <Badge
          v-for="feature in availableToAdd"
          :key="feature"
          variant="outline"
          class="cursor-pointer hover:bg-primary hover:text-primary-foreground"
          @click="addFeature(feature)"
        >
          <Plus class="mr-1 h-3 w-3" />
          {{ feature }}
        </Badge>
      </div>
    </div>
    
    <!-- Добавление своей характеристики -->
    <div v-if="modelValue.length < maxFeatures" class="flex gap-2">
      <Input
        v-model="newFeature"
        placeholder="Добавить свою характеристику..."
        class="flex-1"
        maxlength="50"
        @keyup.enter="addCustomFeature"
      />
      <Button
        type="button"
        variant="outline"
        size="sm"
        :disabled="!newFeature.trim()"
        @click="addCustomFeature"
      >
        <Plus class="h-4 w-4" />
      </Button>
    </div>
    
    <!-- Счетчик -->
    <div class="text-xs text-muted-foreground">
      {{ modelValue.length }}/{{ maxFeatures }} характеристик
    </div>
  </div>
</template>
