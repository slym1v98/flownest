<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Card } from '@/components/ui/card';
import Editor from '@/components/cms/Editor.vue';
import SeoManager from '@/components/cms/SeoManager.vue';
import { Save } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Post {
    id: number;
    title: string;
    slug: string;
    content: any;
    excerpt: string | null;
    status: 'draft' | 'published' | 'archived';
    is_featured: boolean;
    seo_data: {
        meta_title?: string;
        meta_description?: string;
        meta_keywords?: string;
    } | null;
    user: User;
    created_at: string;
    updated_at: string;
}

interface Props {
    post: Post;
}

const props = defineProps<Props>();

const form = useForm({
    title: props.post.title,
    slug: props.post.slug,
    content: props.post.content,
    excerpt: props.post.excerpt || '',
    status: props.post.status,
    is_featured: props.post.is_featured,
    seo_data: {
        meta_title: props.post.seo_data?.meta_title || '',
        meta_description: props.post.seo_data?.meta_description || '',
        meta_keywords: props.post.seo_data?.meta_keywords || '',
    },
});

const submit = () => {
    form.put(`/admin/posts/${props.post.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Edit: ${post.title}`" />

    <AppLayout>
        <div class="space-y-4">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Edit Post</h1>
                <p class="text-sm text-muted-foreground">
                    Update your blog post
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid gap-4 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="space-y-4 lg:col-span-2">
                        <!-- Title -->
                        <div class="space-y-2">
                            <Label for="title">Title</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                placeholder="Enter post title"
                                required
                                :class="{
                                    'border-destructive': form.errors.title,
                                }"
                            />
                            <p
                                v-if="form.errors.title"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <!-- Slug -->
                        <div class="space-y-2">
                            <Label for="slug">Slug</Label>
                            <Input
                                id="slug"
                                v-model="form.slug"
                                placeholder="Auto-generated from title"
                                :class="{
                                    'border-destructive': form.errors.slug,
                                }"
                            />
                            <p class="text-xs text-muted-foreground">
                                Leave empty to auto-generate from title
                            </p>
                            <p
                                v-if="form.errors.slug"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.slug }}
                            </p>
                        </div>

                        <!-- Content -->
                        <div class="space-y-2">
                            <Label>Content</Label>
                            <Editor
                                v-model="form.content"
                                placeholder="Write your post content..."
                            />
                            <p
                                v-if="form.errors.content"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.content }}
                            </p>
                        </div>

                        <!-- Excerpt -->
                        <div class="space-y-2">
                            <Label for="excerpt">Excerpt</Label>
                            <Textarea
                                id="excerpt"
                                v-model="form.excerpt"
                                placeholder="Brief summary of the post"
                                rows="3"
                                :class="{
                                    'border-destructive': form.errors.excerpt,
                                }"
                            />
                            <p
                                v-if="form.errors.excerpt"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.excerpt }}
                            </p>
                        </div>
                    </div>

                    <!-- Sidebar Settings -->
                    <div class="space-y-4">
                        <!-- Publish Settings -->
                        <Card class="p-3">
                            <h3 class="mb-3 font-semibold">Publish Settings</h3>
                            <div class="space-y-3">
                                <!-- Status -->
                                <div class="space-y-1.5">
                                    <Label for="status">Status</Label>
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    >
                                        <option value="draft">Draft</option>
                                        <option value="published">
                                            Published
                                        </option>
                                        <option value="archived">Archived</option>
                                    </select>
                                </div>

                                <!-- Featured -->
                                <div class="flex items-center justify-between">
                                    <Label for="is_featured">Featured Post</Label>
                                    <Switch
                                        id="is_featured"
                                        v-model:checked="form.is_featured"
                                    />
                                </div>
                            </div>
                        </Card>

                        <!-- SEO Settings -->
                        <SeoManager
                            v-model="form.seo_data"
                            :fallback-title="form.title"
                            :fallback-description="form.excerpt"
                        />

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            class="w-full"
                            :disabled="form.processing"
                        >
                            <Save class="mr-2 size-4" />
                            {{ form.processing ? 'Updating...' : 'Update Post' }}
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
