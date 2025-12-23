<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import type {DrawerDirection} from 'vaul-vue'
import { Button } from '@/components/ui/button';
import { Bell } from 'lucide-vue-next';
import {
    Drawer,
    DrawerContent,
    DrawerDescription,
    DrawerHeader,
    DrawerTitle,
    DrawerTrigger,
} from '@/components/ui/drawer';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <Drawer direction="right">
                <DrawerTrigger>
                    <Button
                        variant="ghost"
                        as-child
                        size="sm"
                        class="hidden sm:flex"
                    >
                        <a
                            href="javascript:void(0);"
                            class="dark:text-foreground"
                        >
                            <Bell />
                        </a>
                    </Button>
                </DrawerTrigger>
                <DrawerContent>
                    <DrawerHeader>
                        <DrawerTitle>Notification</DrawerTitle>
                        <DrawerDescription>
                            You have no new notifications.
                        </DrawerDescription>
                    </DrawerHeader>
                </DrawerContent>
            </Drawer>
        </div>
    </header>
</template>
