<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { logout } from '@/routes';
import { LogOut } from 'lucide-vue-next';

// Define the interface for our component props
interface Props {
    status: number;
}

const props = defineProps<Props>();

// Typing the mapping objects for better safety
const title = computed((): string => {
    const titles: Record<number, string> = {
        503: '503: Service Unavailable',
        500: '500: Server Error',
        404: '404: Page Not Found',
        403: '403: Forbidden',
    };
    return titles[props.status] ?? 'An Error Occurred';
});

const description = computed((): string => {
    const descriptions: Record<number, string> = {
        503: 'Sorry, we are doing some maintenance. Please check back soon.',
        500: 'Whoops, something went wrong on our servers.',
        404: 'Sorry, the page you are looking for could not be found.',
        403: 'Sorry, you do not have permission to access this page.',
    };
    return descriptions[props.status] ?? 'An unexpected error has occurred.';
});

const handleLogout = () => {
    router.flushAll();
};
</script>

<template>
    <Head :title="title" />

    <AuthLayout :title="title" :description="description">
        <Link
            class="inline-flex justify-center items-center cursor-pointer"
            :href="logout()"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </AuthLayout>
</template>
