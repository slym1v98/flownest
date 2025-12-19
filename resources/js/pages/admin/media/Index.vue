<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatFileSize } from '@/lib/utils';
import type { Media } from '@/types/media';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Copy,
    Download,
    Grid3x3,
    List,
    Search,
    Trash2,
    Upload,
} from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    media: {
        data: Media[];
        links: any;
        meta: any;
    };
    filters: {
        search?: string;
        collection?: string;
    };
}

const props = defineProps<Props>();

const viewMode = ref<'grid' | 'list'>('grid');
const searchForm = useForm({
    search: props.filters.search || '',
});

const isDragging = ref(false);
const uploadProgress = ref<Record<string, number>>({});

const handleFileUpload = async (files: FileList | null) => {
    if (!files || files.length === 0) return;

    const formData = new FormData();
    Array.from(files).forEach((file) => {
        formData.append('files[]', file);
    });
    formData.append('collection', 'default');

    try {
        await router.post('/admin/media', formData, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['media'] });
            },
        });
    } catch (error) {
        console.error('Upload failed:', error);
    }
};

const handleDrop = (e: DragEvent) => {
    isDragging.value = false;
    const files = e.dataTransfer?.files;
    if (files) {
        handleFileUpload(files);
    }
};

const handleFileInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    handleFileUpload(target.files);
    target.value = '';
};

const deleteMedia = (mediaId: number) => {
    if (!confirm('Are you sure you want to delete this media?')) return;

    router.delete(`/admin/media/${mediaId}`, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['media'] });
        },
    });
};

const copyUrl = (url: string) => {
    navigator.clipboard.writeText(url);
    // You could add a toast notification here
    alert('URL copied to clipboard!');
};

const search = () => {
    searchForm.get('/admin/media', {
        preserveState: true,
        preserveScroll: true,
    });
};

const isImage = (mimeType: string): boolean => {
    return mimeType.startsWith('image/');
};
</script>

<template>
    <Head title="Media Library" />

    <AppLayout>
        <div class="space-y-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Media Library
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Manage your uploaded files and images
                    </p>
                </div>
            </div>

            <!-- Upload Area -->
            <Card
                class="relative border-2 border-dashed p-8 text-center transition-colors"
                :class="{
                    'border-primary bg-primary/5': isDragging,
                }"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop"
            >
                <input
                    id="file-upload"
                    type="file"
                    multiple
                    accept="image/*,.pdf,.doc,.docx"
                    class="hidden"
                    @change="handleFileInput"
                />
                <label
                    for="file-upload"
                    class="flex cursor-pointer flex-col items-center gap-2"
                >
                    <Upload class="size-12 text-muted-foreground" />
                    <div>
                        <p class="text-sm font-medium">
                            Click to upload or drag and drop
                        </p>
                        <p class="text-xs text-muted-foreground">
                            JPG, PNG, GIF, WEBP, PDF, DOC (max 10MB)
                        </p>
                    </div>
                </label>
            </Card>

            <!-- Toolbar -->
            <div class="flex items-center justify-between gap-4">
                <div class="flex flex-1 items-center gap-2">
                    <div class="relative max-w-sm flex-1">
                        <Search
                            class="absolute top-1/2 left-2 size-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="searchForm.search"
                            placeholder="Search media..."
                            class="pl-8"
                            @keyup.enter="search"
                        />
                    </div>
                    <Button @click="search" variant="outline" size="sm">
                        Search
                    </Button>
                </div>
                <div class="flex items-center gap-1 rounded-lg border p-1">
                    <Button
                        variant="ghost"
                        size="sm"
                        :class="{ 'bg-muted': viewMode === 'grid' }"
                        @click="viewMode = 'grid'"
                    >
                        <Grid3x3 class="size-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="sm"
                        :class="{ 'bg-muted': viewMode === 'list' }"
                        @click="viewMode = 'list'"
                    >
                        <List class="size-4" />
                    </Button>
                </div>
            </div>

            <!-- Media Grid/List -->
            <div v-if="media.data.length > 0">
                <!-- Grid View -->
                <div
                    v-if="viewMode === 'grid'"
                    class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6"
                >
                    <Card
                        v-for="item in media.data"
                        :key="item.id"
                        class="group relative overflow-hidden"
                    >
                        <div class="aspect-square">
                            <img
                                v-if="isImage(item.mime_type)"
                                :src="item.thumbnail_url"
                                :alt="item.name"
                                class="size-full object-cover"
                            />
                            <div
                                v-else
                                class="flex size-full items-center justify-center bg-muted"
                            >
                                <span class="text-xs text-muted-foreground">{{
                                    item.file_name
                                        .split('.')
                                        .pop()
                                        ?.toUpperCase()
                                }}</span>
                            </div>
                        </div>
                        <div
                            class="absolute inset-0 flex items-center justify-center gap-2 bg-black/60 opacity-0 transition-opacity group-hover:opacity-100"
                        >
                            <Button
                                size="sm"
                                variant="secondary"
                                @click="copyUrl(item.url)"
                            >
                                <Copy class="size-4" />
                            </Button>
                            <Button
                                size="sm"
                                variant="secondary"
                                as="a"
                                :href="item.url"
                                target="_blank"
                            >
                                <Download class="size-4" />
                            </Button>
                            <Button
                                size="sm"
                                variant="destructive"
                                @click="deleteMedia(item.id)"
                            >
                                <Trash2 class="size-4" />
                            </Button>
                        </div>
                        <div class="p-2">
                            <p class="truncate text-xs font-medium">
                                {{ item.name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ formatFileSize(item.size) }}
                            </p>
                        </div>
                    </Card>
                </div>

                <!-- List View -->
                <div v-else class="space-y-2">
                    <Card
                        v-for="item in media.data"
                        :key="item.id"
                        class="flex items-center gap-4 p-3"
                    >
                        <div
                            class="size-16 flex-shrink-0 overflow-hidden rounded"
                        >
                            <img
                                v-if="isImage(item.mime_type)"
                                :src="item.thumbnail_url"
                                :alt="item.name"
                                class="size-full object-cover"
                            />
                            <div
                                v-else
                                class="flex size-full items-center justify-center bg-muted"
                            >
                                <span class="text-xs text-muted-foreground">{{
                                    item.file_name
                                        .split('.')
                                        .pop()
                                        ?.toUpperCase()
                                }}</span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium">{{ item.name }}</p>
                            <p class="text-sm text-muted-foreground">
                                {{ item.file_name }} •
                                {{ formatFileSize(item.size) }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <Button
                                size="sm"
                                variant="outline"
                                @click="copyUrl(item.url)"
                            >
                                <Copy class="mr-2 size-4" />
                                Copy URL
                            </Button>
                            <Button
                                size="sm"
                                variant="outline"
                                as="a"
                                :href="item.url"
                                target="_blank"
                            >
                                <Download class="size-4" />
                            </Button>
                            <Button
                                size="sm"
                                variant="destructive"
                                @click="deleteMedia(item.id)"
                            >
                                <Trash2 class="size-4" />
                            </Button>
                        </div>
                    </Card>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-center gap-2 pt-4">
                    <Button
                        v-for="link in media.links"
                        :key="link.label"
                        variant="outline"
                        size="sm"
                        :disabled="!link.url || link.active"
                        :class="{ 'bg-muted': link.active }"
                        @click="link.url && router.visit(link.url)"
                        v-html="link.label"
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-12 text-center">
                <p class="text-muted-foreground">
                    No media files found. Upload some files to get started.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
