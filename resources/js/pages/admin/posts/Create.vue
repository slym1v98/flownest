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
import { Save } from 'lucide-vue-next';

const form = useForm({
    title: '',
    slug: '',
    content: null as any,
    excerpt: '',
    status: 'draft' as 'draft' | 'published' | 'archived',
    is_featured: false,
    seo_data: {
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
    },
});

const submit = () => {
    form.post('/admin/posts', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Post" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Create Post</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Create a new blog post
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="space-y-6 lg:col-span-2">
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
                    <div class="space-y-6">
                        <!-- Publish Settings -->
                        <Card class="p-4">
                            <h3 class="mb-4 font-semibold">Publish Settings</h3>
                            <div class="space-y-4">
                                <!-- Status -->
                                <div class="space-y-2">
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
                        <Card class="p-4">
                            <h3 class="mb-4 font-semibold">SEO Settings</h3>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="meta_title">Meta Title</Label>
                                    <Input
                                        id="meta_title"
                                        v-model="form.seo_data.meta_title"
                                        placeholder="SEO title"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label for="meta_description"
                                        >Meta Description</Label
                                    >
                                    <Textarea
                                        id="meta_description"
                                        v-model="form.seo_data.meta_description"
                                        placeholder="SEO description"
                                        rows="3"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label for="meta_keywords"
                                        >Meta Keywords</Label
                                    >
                                    <Input
                                        id="meta_keywords"
                                        v-model="form.seo_data.meta_keywords"
                                        placeholder="keyword1, keyword2"
                                    />
                                </div>
                            </div>
                        </Card>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            class="w-full"
                            :disabled="form.processing"
                        >
                            <Save class="mr-2 size-4" />
                            {{ form.processing ? 'Creating...' : 'Create Post' }}
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
