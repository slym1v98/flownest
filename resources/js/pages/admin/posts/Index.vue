<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Plus, Search, Edit, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { debounce } from 'lodash-es';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Post {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    status: 'draft' | 'published' | 'archived';
    is_featured: boolean;
    user: User;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedPosts {
    data: Post[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}

interface Props {
    posts: PaginatedPosts;
    filters: {
        search?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const performSearch = debounce(() => {
    router.get(
        '/admin/posts',
        {
            search: search.value,
            status: status.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}, 300);

watch([search, status], () => {
    performSearch();
});

const deletePost = (postId: number) => {
    if (confirm('Are you sure you want to delete this post?')) {
        router.delete(`/admin/posts/${postId}`, {
            preserveScroll: true,
        });
    }
};

const getStatusVariant = (
    status: string,
): 'default' | 'secondary' | 'destructive' => {
    switch (status) {
        case 'published':
            return 'default';
        case 'draft':
            return 'secondary';
        case 'archived':
            return 'destructive';
        default:
            return 'secondary';
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Posts" />

    <AppLayout>
        <div class="space-y-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Posts</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage your blog posts
                    </p>
                </div>
                <Link :href="`/admin/posts/create`">
                    <Button>
                        <Plus class="mr-2 size-4" />
                        New Post
                    </Button>
                </Link>
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <Search
                        class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="search"
                        placeholder="Search posts..."
                        class="pl-9"
                    />
                </div>
                <select
                    v-model="status"
                    class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
            </div>

            <!-- Table -->
            <div class="rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Title</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Author</TableHead>
                            <TableHead>Created</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="post in posts.data"
                            :key="post.id"
                        >
                            <TableCell>
                                <div>
                                    <div class="font-medium">
                                        {{ post.title }}
                                    </div>
                                    <div
                                        v-if="post.excerpt"
                                        class="mt-1 line-clamp-1 text-sm text-muted-foreground"
                                    >
                                        {{ post.excerpt }}
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="getStatusVariant(post.status)">
                                    {{ post.status }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="text-sm">{{ post.user.name }}</div>
                            </TableCell>
                            <TableCell>
                                <div class="text-sm">
                                    {{ formatDate(post.created_at) }}
                                </div>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="`/admin/posts/${post.id}/edit`">
                                        <Button variant="ghost" size="sm">
                                            <Edit class="size-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="deletePost(post.id)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="posts.data.length === 0">
                            <TableCell colspan="5" class="text-center">
                                <div class="py-8 text-muted-foreground">
                                    No posts found.
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div
                v-if="posts.last_page > 1"
                class="flex items-center justify-between"
            >
                <div class="text-sm text-muted-foreground">
                    Showing {{ (posts.current_page - 1) * posts.per_page + 1 }}
                    to
                    {{
                        Math.min(
                            posts.current_page * posts.per_page,
                            posts.total,
                        )
                    }}
                    of {{ posts.total }} results
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        v-for="link in posts.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="{
                            'pointer-events-none opacity-50': !link.url,
                        }"
                    >
                        <Button
                            variant="outline"
                            size="sm"
                            :class="{
                                'bg-primary text-primary-foreground':
                                    link.active,
                            }"
                        >
                            {{ link.label.replace(/&laquo;|&raquo;/g, (m) => m === '&laquo;' ? '«' : '»') }}
                        </Button>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
