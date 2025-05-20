<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import type { Company, User } from '@/types';
import { computed } from 'vue';
import {
    MapPin,
    Building,
    Phone,
    Mail,
    Globe,
    ShieldCheck,
    ShieldX
} from 'lucide-vue-next';

const { company, user } = defineProps<{
    company: Company;
    user: User;
}>();

const badgeColor = computed(() =>
    company.verified ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
);
</script>

<template>
    <Card class="w-full border shadow-sm">
        <!-- Header -->
        <CardHeader class="flex items-center gap-6 px-8 py-6">
            <Avatar class="h-20 w-20">
                <AvatarImage :src="user.avatar_url || '/default-avatar.png'" alt="Логотип компании" />
                <AvatarFallback>{{ company.name.charAt(0) }}</AvatarFallback>
            </Avatar>
            <div class="flex-1">
                <CardTitle class="text-2xl font-semibold">{{ company.name }}</CardTitle>
                <p class="text-sm text-muted-foreground">ИНН: {{ company.inn }}</p>
                <Badge class="mt-2 inline-flex items-center gap-1 px-2 py-1 text-xs rounded-md" :class="badgeColor">
                    <component :is="company.verified ? ShieldCheck : ShieldX" class="w-4 h-4" />
                    {{ company.verified ? 'Проверена' : 'Не подтверждена' }}
                </Badge>
            </div>
        </CardHeader>

        <!-- Content -->
        <CardContent class="px-8 pb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8 text-sm text-muted-foreground">
                <div class="flex items-start gap-3">
                    <MapPin class="w-5 h-5 text-foreground mt-0.5" />
                    <div>
                        <div class="font-medium text-foreground">Город</div>
                        <div>{{ company.city }}</div>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <Building class="w-5 h-5 text-foreground mt-0.5" />
                    <div>
                        <div class="font-medium text-foreground">Адрес</div>
                        <div>{{ company.address }}</div>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <Phone class="w-5 h-5 text-foreground mt-0.5" />
                    <div>
                        <div class="font-medium text-foreground">Телефон</div>
                        <div>{{ company.phone }}</div>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <Mail class="w-5 h-5 text-foreground mt-0.5" />
                    <div>
                        <div class="font-medium text-foreground">Email</div>
                        <div>{{ company.email }}</div>
                    </div>
                </div>
                <div
                    v-if="company.website"
                    class="flex items-start gap-3 md:col-span-2 pt-2 border-t mt-4"
                >
                    <Globe class="w-5 h-5 text-foreground mt-0.5" />
                    <div>
                        <div class="font-medium text-foreground">Сайт</div>
                        <a
                            :href="company.website"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-blue-600 hover:underline break-words"
                        >
                            {{ company.website }}
                        </a>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
