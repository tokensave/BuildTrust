/**
 * 📝 useAdForm - Композабл для работы с формами объявлений
 * 
 * Централизует логику создания и редактирования объявлений,
 * включая валидацию, обработку файлов и уведомления.
 * 
 * @example
 * ```typescript
 * const { form, submitCreate, submitUpdate } = useAdForm()
 * 
 * // Создание объявления
 * await submitCreate(userId)
 * 
 * // Редактирование
 * const { form } = useAdForm(existingAd)
 * await submitUpdate(userId, adId)
 * ```
 */

import { computed, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import type { CreateAdForm, UpdateAdForm, Ad } from '@/features/ads/types/ad'
import { useNotifications } from '@/shared/composables/core/useNotifications'
import { useEnums } from '@/shared/composables/data/useEnums'

export function useAdForm(initialData?: Partial<Ad>) {
    const { showAdCreated, showAdUpdated, showError } = useNotifications()
    const { getCategoriesByType, getSubcategoriesByCategory } = useEnums()

    // Форма с начальными данными
    const form = useForm<CreateAdForm>({
        title: initialData?.title || '',
        description: initialData?.description || '',
        type: initialData?.type || 'goods',
        category: initialData?.category || undefined,
        subcategory: initialData?.subcategory || undefined,
        price: initialData?.price || undefined,
        location: initialData?.location || '',
        features: initialData?.features || [],
        images: [],
        is_urgent: initialData?.is_urgent || false,
        status: initialData?.status || 'draft'
    })

    // Вычисляемые свойства для категорий
    const availableCategories = computed(() => {
        return getCategoriesByType(form.type)
    })

    const availableSubcategories = computed(() => {
        return form.category ? getSubcategoriesByCategory(form.category) : {}
    })

    // Сброс зависимых полей при изменении родительских
    watch(() => form.type, () => {
        form.category = undefined
        form.subcategory = undefined
    })

    watch(() => form.category, () => {
        form.subcategory = undefined
    })

    // Валидация формы
    const isFormValid = computed(() => {
        return !!(
            form.title &&
            form.description &&
            form.type &&
            !form.processing
        )
    })

    // Проверка изменений
    const isDirty = computed(() => {
        return form.isDirty
    })

    // Создание объявления
    const submitCreate = async (userId: number) => {
        if (!isFormValid.value) {
            showError('Заполните обязательные поля')
            return
        }

        form.post(route('user.ads.store', userId), {
            forceFormData: true, // Для загрузки файлов
            onSuccess: () => {
                showAdCreated()
                // Можно добавить редирект на список объявлений
                router.visit(route('user.ads.index', userId))
            },
            onError: (errors) => {
                console.error('Ошибки валидации:', errors)
                showError('Проверьте правильность заполнения формы')
            }
        })
    }

    // Обновление объявления
    const submitUpdate = async (userId: number, adId: number, updateData?: Partial<UpdateAdForm>) => {
        if (!isFormValid.value) {
            showError('Заполните обязательные поля')
            return
        }

        // Подготавливаем данные для обновления
        const formData = updateData ? { ...form.data(), ...updateData } : form.data()

        form.put(route('user.ads.update', { user: userId, ad: adId }), {
            data: formData,
            forceFormData: true,
            onSuccess: () => {
                showAdUpdated()
                router.visit(route('user.ads.index', userId))
            },
            onError: (errors) => {
                console.error('Ошибки валидации:', errors)
                showError('Проверьте правильность заполнения формы')
            }
        })
    }

    // Сохранение как черновик
    const saveAsDraft = async (userId: number, adId?: number) => {
        form.status = 'draft'
        
        if (adId) {
            await submitUpdate(userId, adId)
        } else {
            await submitCreate(userId)
        }
    }

    // Опубликовать объявление
    const publish = async (userId: number, adId?: number) => {
        form.status = 'published'
        
        if (adId) {
            await submitUpdate(userId, adId)
        } else {
            await submitCreate(userId)
        }
    }

    // Добавление изображений
    const addImages = (files: File[]) => {
        form.images = [...form.images, ...files]
    }

    // Удаление изображения
    const removeImage = (index: number) => {
        form.images = form.images.filter((_, i) => i !== index)
    }

    // Добавление характеристики
    const addFeature = (feature: string) => {
        if (feature && !form.features.includes(feature)) {
            form.features = [...form.features, feature]
        }
    }

    // Удаление характеристики
    const removeFeature = (feature: string) => {
        form.features = form.features.filter(f => f !== feature)
    }

    // Очистка формы
    const resetForm = () => {
        form.reset()
    }

    return {
        // Форма и состояние
        form,
        
        // Валидация и состояние
        isFormValid,
        isDirty,
        
        // Вычисляемые свойства
        availableCategories,
        availableSubcategories,
        
        // Методы отправки
        submitCreate,
        submitUpdate,
        saveAsDraft,
        publish,
        
        // Работа с файлами и данными
        addImages,
        removeImage,
        addFeature,
        removeFeature,
        resetForm,
    }
}
