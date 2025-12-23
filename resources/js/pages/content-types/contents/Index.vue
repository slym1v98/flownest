<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import contentTypes from '@/routes/content-types';
import contents from '@/routes/content-types/contents';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

interface Props {
    contentType: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Content Type',
        href: contentTypes.index().url,
    },
    {
        title: 'Content',
        href: contents.index({ content_type: props.contentType }).url,
    },
];
</script>

<template>
    <Head title="Content Type" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            Content Type ({{ props.contentType }})
        </div>
    </AppLayout>
</template>
