<script setup lang="ts">
import NavFooter from '@/shared/components/navigation/NavFooter.vue';
import NavMain from '@/shared/components/navigation/NavMain.vue';
import NavUser from '@/shared/components/navigation/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData, type User } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, BookA, Handshake, MessageSquareMore } from 'lucide-vue-next';
import AppLogo from '../branding/AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Доска обьявлений',
        href: '/dashboard',
        icon: LayoutGrid,
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const userAdsNavItems: NavItem[] = [
    {
        title: 'Мои обьявления',
        href: route('user.ads.index', user.id),
        icon: BookA,
    },
    {
        title: 'Мои сделки',
        href: route('user.deals.index'),
        icon: Handshake,
    },
    {
        title: 'Мои сообщения',
        href: route('chats.index', user.id),
        icon: MessageSquareMore,
    },
];

const footerNavItems: NavItem[] = [
    //
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavMain :items="userAdsNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
