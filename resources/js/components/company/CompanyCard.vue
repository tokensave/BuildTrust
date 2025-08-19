<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Sheet, SheetTrigger, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { Separator } from '@/components/ui/separator';
import type { Company, User } from '@/types';
import { ref } from 'vue';
import axios from 'axios';
import {
    MapPin,
    Building,
    Phone,
    Mail,
    Globe,
    Loader2,
    Search,
    BarChart3,
    ExternalLink,
    AlertCircle,
    CheckCircle,
    Clock,
    TrendingUp,
    Star
} from 'lucide-vue-next';

const { company, user } = defineProps<{
    company: Company;
    user: User;
}>();

// Состояния для проверки контрагента
const isChecking = ref(false);
const checkResult = ref<string | null>(null);
const checkError = ref<string | null>(null);
const analysisData = ref<{
    status?: string;
    risk_level?: string;
    description?: string;
    recommendations?: string;
} | null>(null);

// Функция для проверки контрагента (быстрая проверка)
const checkCounterparty = async () => {
    isChecking.value = true;
    checkResult.value = null;
    checkError.value = null;

    try {
        const response = await axios.post('/api/ai/check-counterparty', {
            inn: company.inn,
            company_name: company.name
        });

        if (response.data.success) {
            checkResult.value = response.data.data.description || 'Описание не получено';
        } else {
            checkError.value = response.data.message || 'Ошибка при получении данных';
        }
    } catch (error) {
        console.error('Ошибка при проверке контрагента:', error);
        if (error.response?.data?.message) {
            checkError.value = error.response.data.message;
        } else {
            checkError.value = 'Не удалось получить информацию о контрагенте';
        }
    } finally {
        isChecking.value = false;
    }
};

// Функция для полного анализа компании
const analyzeCompany = async (forceUpdate = false) => {
    isChecking.value = true;
    checkResult.value = null;
    checkError.value = null;
    analysisData.value = null;

    try {
        const response = await axios.post('/api/ai/search-company', {
            inn: company.inn,
            company_name: company.name,
            force_update: forceUpdate
        });

        if (response.data.success) {
            const analysis = response.data.data.analysis;
            const description = response.data.data.description || analysis?.summary;
            
            if (description || analysis) {
                // Сохраняем структурированные данные
                analysisData.value = {
                    status: analysis?.status || 'unknown',
                    risk_level: analysis?.risk_level || 'medium',
                    description: description || 'Описание не получено',
                    recommendations: analysis?.recommendations || null
                };
                
                // Устанавливаем флаг успешного получения данных
                checkResult.value = 'success';
            } else {
                checkError.value = 'Анализ не содержит данных';
            }
        } else {
            checkError.value = response.data.message || 'Ошибка при получении данных';
        }
    } catch (error) {
        console.error('Ошибка при анализе компании:', error);
        if (error.response?.data?.message) {
            checkError.value = error.response.data.message;
        } else {
            checkError.value = 'Не удалось получить информацию о компании';
        }
    } finally {
        isChecking.value = false;
    }
};

// Вспомогательные функции для отображения статуса и риска
const getStatusText = (status) => {
    const statusMap = {
        'active': '✅ Активная',
        'inactive': '⚠️ Неактивная',
        'liquidated': '❌ Ликвидирована',
        'bankruptcy': '🔴 Банкротство',
        'unknown': '❓ Неизвестно'
    };
    return statusMap[status] || status;
};

const getRiskText = (riskLevel) => {
    const riskMap = {
        'low': '🟢 Низкий',
        'medium': '🟡 Средний',
        'high': '🟠 Высокий',
        'critical': '🔴 Критический'
    };
    return riskMap[riskLevel] || riskLevel;
};

const displayValue = (value: string | null | undefined): string => {
    return value || 'Не указано';
};
</script>

<template>
    <Card class="w-full border-0 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden bg-gradient-to-br from-background to-muted/20">
        <!-- Header with gradient background -->
        <div class="bg-gradient-to-r from-blue-600/10 to-purple-600/10 dark:from-blue-600/20 dark:to-purple-600/20">
            <CardHeader class="flex items-center gap-6 px-8 py-6 relative">
                <div class="absolute top-4 right-4">
                    <Badge 
                        variant="outline" 
                        class="border-2 font-semibold"
                        :class="company.verified 
                            ? 'border-green-500 text-green-700 bg-green-50 dark:bg-green-950 dark:text-green-400' 
                            : 'border-amber-500 text-amber-700 bg-amber-50 dark:bg-amber-950 dark:text-amber-400'"
                    >
                        <component :is="company.verified ? CheckCircle : AlertCircle" class="w-3 h-3 mr-1" />
                        {{ company.verified ? 'Верифицирована' : 'Требует проверки' }}
                    </Badge>
                </div>
                
                <div class="relative">
                    <Avatar class="h-24 w-24 ring-4 ring-background shadow-lg">
                        <AvatarImage :src="user.avatar_url || '/default-avatar.png'" alt="Логотип компании" />
                        <AvatarFallback class="text-2xl font-bold bg-gradient-to-br from-blue-500 to-purple-600 text-white">
                            {{ company.name.charAt(0) }}
                        </AvatarFallback>
                    </Avatar>
                    <div class="absolute -bottom-2 -right-2 bg-background rounded-full p-2 shadow-lg">
                        <Star class="w-4 h-4 text-yellow-500 fill-current" />
                    </div>
                </div>
                
                <div class="flex-1">
                    <CardTitle class="text-3xl font-bold bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                        {{ company.name }}
                    </CardTitle>
                    <CardDescription class="text-lg mt-1 flex items-center gap-2">
                        <Building class="w-4 h-4" />
                        ИНН: {{ company.inn || 'Не указан' }}
                    </CardDescription>
                </div>
            </CardHeader>
        </div>

        <!-- Content with improved layout -->
        <CardContent class="px-8 py-6">
            <!-- Contact Information Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-muted-foreground uppercase tracking-wide flex items-center gap-2">
                        <MapPin class="w-4 h-4" />
                        Местоположение
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-muted/50 hover:bg-muted/80 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                <MapPin class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-sm">Город</div>
                                <div class="text-sm text-muted-foreground">{{ displayValue(company.city) }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-muted/50 hover:bg-muted/80 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                                <Building class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-sm">Адрес</div>
                                <div class="text-sm text-muted-foreground break-words">{{ displayValue(company.address) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-muted-foreground uppercase tracking-wide flex items-center gap-2">
                        <Mail class="w-4 h-4" />
                        Контакты
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-muted/50 hover:bg-muted/80 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                <Phone class="w-4 h-4 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-sm">Телефон</div>
                                <div class="text-sm text-muted-foreground">{{ displayValue(company.phone) }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-muted/50 hover:bg-muted/80 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-900 flex items-center justify-center">
                                <Mail class="w-4 h-4 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-sm">Email</div>
                                <div class="text-sm text-muted-foreground break-all">{{ displayValue(company.email) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Website Section -->
            <div v-if="company.website" class="mb-6">
                <Separator class="mb-4" />
                <div class="flex items-center gap-3 p-4 rounded-lg bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-950/50 dark:to-cyan-950/50 border border-blue-200 dark:border-blue-800">
                    <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center">
                        <Globe class="w-5 h-5 text-white" />
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-blue-900 dark:text-blue-100">Веб-сайт</div>
                        <a
                            :href="company.website"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 font-medium flex items-center gap-2 break-words hover:underline transition-colors"
                        >
                            {{ displayValue(company.website) }}
                            <ExternalLink class="w-3 h-3" />
                        </a>
                    </div>
                </div>
            </div>

            <Separator class="mb-6" />

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3">
                <Sheet>
                    <SheetTrigger as-child>
                        <Button 
                            @click="checkCounterparty" 
                            :disabled="isChecking" 
                            variant="outline" 
                            class="flex-1 h-12 text-sm font-medium hover:bg-blue-50 dark:hover:bg-blue-950 border-blue-200 dark:border-blue-800 hover:border-blue-300 dark:hover:border-blue-700 transition-all duration-200"
                        >
                            <Search v-if="!isChecking" class="w-4 h-4 mr-2" />
                            <Loader2 v-else class="w-4 h-4 mr-2 animate-spin" />
                            <Clock class="w-4 h-4 mr-2" />
                            Быстрая проверка
                        </Button>
                    </SheetTrigger>
                    <SheetContent class="sm:max-w-lg">
                        <SheetHeader class="space-y-3">
                            <SheetTitle class="flex items-center gap-2 text-xl">
                                <Search class="w-5 h-5 text-blue-600" />
                                Быстрая проверка
                            </SheetTitle>
                            <SheetDescription class="text-base">
                                Краткая оценка надежности <span class="font-semibold">{{ company.name }}</span>
                            </SheetDescription>
                        </SheetHeader>
                        <div class="mt-8">
                            <div v-if="isChecking" class="flex flex-col items-center justify-center py-12 space-y-4">
                                <Loader2 class="w-12 h-12 animate-spin text-blue-600" />
                                <div class="text-center">
                                    <p class="font-medium">Анализируем данные...</p>
                                    <p class="text-sm text-muted-foreground mt-1">Это займет несколько секунд</p>
                                </div>
                            </div>
                            <div v-else-if="checkResult" class="space-y-4">
                                <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 dark:from-green-950/50 dark:to-blue-950/50 rounded-lg border border-green-200 dark:border-green-800">
                                    <div class="flex items-center gap-2 mb-3">
                                        <CheckCircle class="w-5 h-5 text-green-600" />
                                        <span class="font-semibold text-green-900 dark:text-green-100">Результат проверки</span>
                                    </div>
                                    <p class="text-sm whitespace-pre-line leading-relaxed">{{ checkResult }}</p>
                                </div>
                            </div>
                            <div v-else-if="checkError" class="p-6 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-950/50 dark:to-orange-950/50 rounded-lg border border-red-200 dark:border-red-800">
                                <div class="flex items-center gap-2 mb-3">
                                    <AlertCircle class="w-5 h-5 text-red-600" />
                                    <span class="font-semibold text-red-900 dark:text-red-100">Ошибка</span>
                                </div>
                                <p class="text-sm text-red-700 dark:text-red-300">{{ checkError }}</p>
                            </div>
                            <div v-else class="text-center py-8">
                                <Search class="w-16 h-16 text-muted-foreground mx-auto mb-4" />
                                <p class="text-muted-foreground">Нажмите кнопку выше для быстрой проверки контрагента</p>
                            </div>
                        </div>
                    </SheetContent>
                </Sheet>
                
                <Sheet>
                    <SheetTrigger as-child>
                        <Button 
                            @click="() => analyzeCompany(false)" 
                            :disabled="isChecking" 
                            class="flex-1 h-12 text-sm font-medium bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 border-0 shadow-lg hover:shadow-xl transition-all duration-200"
                        >
                            <BarChart3 v-if="!isChecking" class="w-4 h-4 mr-2" />
                            <Loader2 v-else class="w-4 h-4 mr-2 animate-spin" />
                            <TrendingUp class="w-4 h-4 mr-2" />
                            Полный анализ
                        </Button>
                    </SheetTrigger>
                    <SheetContent class="sm:max-w-lg">
                        <SheetHeader class="space-y-3">
                            <SheetTitle class="flex items-center gap-2 text-xl">
                                <BarChart3 class="w-5 h-5 text-purple-600" />
                                Полный анализ
                            </SheetTitle>
                            <SheetDescription class="text-base">
                                Подробный анализ компании <span class="font-semibold">{{ company.name }}</span>
                            </SheetDescription>
                        </SheetHeader>
                        <div class="mt-8">
                            <div v-if="isChecking" class="flex flex-col items-center justify-center py-12 space-y-4">
                                <Loader2 class="w-12 h-12 animate-spin text-purple-600" />
                                <div class="text-center">
                                    <p class="font-medium">Проводим глубокий анализ...</p>
                                    <p class="text-sm text-muted-foreground mt-1">Собираем данные из различных источников</p>
                                </div>
                            </div>
                            <div v-else-if="checkResult && analysisData" class="space-y-6">
                                <!-- Статус компании -->
                                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center">
                                            <CheckCircle class="w-4 h-4 text-white" />
                                        </div>
                                        <h4 class="font-semibold text-blue-900 dark:text-blue-100">Статус компании</h4>
                                    </div>
                                    <p class="text-blue-800 dark:text-blue-200 font-medium ml-11">{{ getStatusText(analysisData.status) }}</p>
                                </div>

                                <!-- Уровень риска -->
                                <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/30 dark:to-orange-950/30 rounded-xl p-4 border border-amber-200 dark:border-amber-800">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center">
                                            <AlertCircle class="w-4 h-4 text-white" />
                                        </div>
                                        <h4 class="font-semibold text-amber-900 dark:text-amber-100">Уровень риска</h4>
                                    </div>
                                    <p class="text-amber-800 dark:text-amber-200 font-medium ml-11">{{ getRiskText(analysisData.risk_level) }}</p>
                                </div>

                                <!-- Описание -->
                                <div class="bg-gradient-to-r from-slate-50 to-gray-50 dark:from-slate-950/30 dark:to-gray-950/30 rounded-xl p-4 border border-slate-200 dark:border-slate-800">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-500 flex items-center justify-center">
                                            <Building class="w-4 h-4 text-white" />
                                        </div>
                                        <h4 class="font-semibold text-slate-900 dark:text-slate-100">Описание</h4>
                                    </div>
                                    <p class="text-slate-700 dark:text-slate-300 leading-relaxed ml-11">{{ analysisData.description }}</p>
                                </div>

                                <!-- Рекомендации -->
                                <div v-if="analysisData.recommendations" class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-950/30 dark:to-emerald-950/30 rounded-xl p-4 border border-green-200 dark:border-green-800">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                            <TrendingUp class="w-4 h-4 text-white" />
                                        </div>
                                        <h4 class="font-semibold text-green-900 dark:text-green-100">Рекомендации</h4>
                                    </div>
                                    <p class="text-green-800 dark:text-green-200 leading-relaxed ml-11">{{ analysisData.recommendations }}</p>
                                </div>
                            </div>
                            <div v-else-if="checkError" class="p-6 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-950/50 dark:to-orange-950/50 rounded-lg border border-red-200 dark:border-red-800">
                                <div class="flex items-center gap-2 mb-3">
                                    <AlertCircle class="w-5 h-5 text-red-600" />
                                    <span class="font-semibold text-red-900 dark:text-red-100">Ошибка</span>
                                </div>
                                <p class="text-sm text-red-700 dark:text-red-300">{{ checkError }}</p>
                            </div>
                            <div v-else class="text-center py-8">
                                <BarChart3 class="w-16 h-16 text-muted-foreground mx-auto mb-4" />
                                <p class="text-muted-foreground">Нажмите кнопку выше для полного анализа компании</p>
                            </div>
                        </div>
                    </SheetContent>
                </Sheet>
            </div>
        </CardContent>
    </Card>
</template>
