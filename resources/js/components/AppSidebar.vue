<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavDevelop from '@/components/NavDevelop.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import contentTypes from '@/routes/content-types';
import contents from '@/routes/content-types/contents';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Captions, BookOpenText, Library } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { urlIsActive } from '@/lib/utils';

const page = usePage();

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
        isActive: urlIsActive(dashboard(), page.url),
    },
    {
        title: 'Post',
        href: contents.index({ content_type: 'posts' }),
        icon: Library,
        isActive: urlIsActive(
            contents.index({ content_type: 'posts' }),
            page.url,
        ),
    },
    {
        title: 'Page',
        href: contents.index({ content_type: 'pages' }),
        icon: Captions,
        isActive: urlIsActive(
            contents.index({ content_type: 'pages' }),
            page.url,
        ),
    },
];

const developNavItems: NavItem[] = [
    {
        title: 'Content Type',
        href: contentTypes.index(),
        icon: BookOpenText,
        isActive: urlIsActive(contentTypes.index(), page.url),
    },
];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavDevelop :items="developNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
