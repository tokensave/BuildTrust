<script setup lang="ts">
/**
 * 📝 FormField - Универсальное поле формы
 * 
 * Стандартизирует отображение полей формы с лейблами,
 * ошибками валидации и подсказками.
 * 
 * @example
 * ```vue
 * <FormField 
 *     label="Заголовок" 
 *     v-model="form.title" 
 *     :error="form.errors.title"
 *     required 
 * />
 * 
 * <FormField 
 *     label="Описание" 
 *     v-model="form.description" 
 *     type="textarea"
 *     :error="form.errors.description"
 *     hint="Опишите товар или услугу подробно"
 * />
 * ```
 */

import { computed } from 'vue'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import InputError from '@/shared/components/forms/InputError.vue'

interface Props {
    label: string
    modelValue: any
    error?: string | string[]
    type?: 'text' | 'email' | 'password' | 'number' | 'textarea' | 'tel'
    placeholder?: string
    hint?: string
    required?: boolean
    disabled?: boolean
    readonly?: boolean
    id?: string
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text'
})

const emit = defineEmits<{
    'update:modelValue': [value: any]
}>()

// Генерируем уникальный ID если не предоставлен
const fieldId = computed(() => props.id || `field-${Math.random().toString(36).substr(2, 9)}`)

// Обрабатываем ошибки (может быть строка или массив)
const errorMessage = computed(() => {
    if (!props.error) return ''
    if (Array.isArray(props.error)) {
        return props.error.length > 0 ? props.error[0] : ''
    }
    return props.error
})

const hasError = computed(() => !!errorMessage.value)

// Обновляем значение
const updateValue = (value: any) => {
    emit('update:modelValue', value)
}
</script>

<template>
    <div class="space-y-2">
        <!-- Лейбл -->
        <Label 
            :for="fieldId" 
            class="text-sm font-medium text-foreground"
        >
            {{ label }}
            <span v-if="required" class="text-destructive ml-1">*</span>
        </Label>

        <!-- Поле ввода -->
        <div class="relative">
            <!-- Textarea -->
            <Textarea
                v-if="type === 'textarea'"
                :id="fieldId"
                :model-value="modelValue"
                @update:model-value="updateValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :readonly="readonly"
                :class="[
                    'min-h-[80px]',
                    hasError ? 'border-destructive focus:border-destructive' : ''
                ]"
                v-bind="$attrs"
            />

            <!-- Обычный input -->
            <Input
                v-else
                :id="fieldId"
                :type="type"
                :model-value="modelValue"
                @update:model-value="updateValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :readonly="readonly"
                :class="[
                    hasError ? 'border-destructive focus:border-destructive' : ''
                ]"
                v-bind="$attrs"
            />
        </div>

        <!-- Подсказка -->
        <p 
            v-if="hint && !hasError" 
            class="text-sm text-muted-foreground"
        >
            {{ hint }}
        </p>

        <!-- Ошибка валидации -->
        <InputError v-if="hasError" :message="errorMessage" />
    </div>
</template>
