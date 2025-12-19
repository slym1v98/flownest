<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { getCsrfToken } from '@/lib/utils';
import type { Media } from '@/types/media';
import { Image as ImageIcon, Search, Upload, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Props {
    modelValue?: Media | Media[] | null;
    multiple?: boolean;
    collection?: string;
    accept?: string;
    label?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: null,
    multiple: false,
    collection: 'default',
    accept: 'image/*',
    label: 'Select Media',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: Media | Media[] | null): void;
}>();

const isOpen = ref(false);
const mediaList = ref<Media[]>([]);
const selectedMedia = ref<Media[]>([]);
const searchQuery = ref('');
const isLoading = ref(false);
const isDragging = ref(false);

// Load media from API when dialog opens
const loadMedia = async () => {
    isLoading.value = true;
    try {
        const response = await fetch(
            `/admin/media/list?collection=${props.collection}&search=${searchQuery.value}`,
        );
        const data = await response.json();
        mediaList.value = data.media;
    } catch (error) {
        console.error('Failed to load media:', error);
    } finally {
        isLoading.value = false;
    }
};

watch(isOpen, (value) => {
    if (value) {
        loadMedia();
        // Initialize selected media from modelValue
        if (props.modelValue) {
            selectedMedia.value = Array.isArray(props.modelValue)
                ? [...props.modelValue]
                : [props.modelValue];
        } else {
            selectedMedia.value = [];
        }
    }
});

const toggleSelection = (media: Media) => {
    const index = selectedMedia.value.findIndex((m) => m.id === media.id);
    if (index > -1) {
        selectedMedia.value.splice(index, 1);
    } else {
        if (props.multiple) {
            selectedMedia.value.push(media);
        } else {
            selectedMedia.value = [media];
        }
    }
};

const isSelected = (media: Media): boolean => {
    return selectedMedia.value.some((m) => m.id === media.id);
};

const confirmSelection = () => {
    if (props.multiple) {
        emit('update:modelValue', selectedMedia.value);
    } else {
        emit('update:modelValue', selectedMedia.value[0] || null);
    }
    isOpen.value = false;
};

const handleFileUpload = async (files: FileList | null) => {
    if (!files || files.length === 0) return;

    const formData = new FormData();
    Array.from(files).forEach((file) => {
        formData.append('files[]', file);
    });
    formData.append('collection', props.collection);

    try {
        const response = await fetch('/admin/media', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
        });

        if (response.ok) {
            await loadMedia();
        }
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

const removeSelectedMedia = (media: Media) => {
    const currentValue = props.modelValue;
    if (Array.isArray(currentValue)) {
        emit(
            'update:modelValue',
            currentValue.filter((m) => m.id !== media.id),
        );
    } else {
        emit('update:modelValue', null);
    }
};

const currentSelection = computed(() => {
    if (!props.modelValue) return [];
    return Array.isArray(props.modelValue)
        ? props.modelValue
        : [props.modelValue];
});
</script>

<template>
    <div class="space-y-2">
        <!-- Selected Media Preview -->
        <div v-if="currentSelection.length > 0" class="flex flex-wrap gap-2">
            <div
                v-for="media in currentSelection"
                :key="media.id"
                class="relative size-24 overflow-hidden rounded border"
            >
                <img
                    :src="media.thumbnail_url || media.url"
                    :alt="media.name"
                    class="size-full object-cover"
                />
                <button
                    type="button"
                    class="absolute top-1 right-1 rounded-full bg-destructive p-1 text-destructive-foreground"
                    @click="removeSelectedMedia(media)"
                >
                    <X class="size-3" />
                </button>
            </div>
        </div>

        <!-- Media Picker Dialog -->
        <Dialog v-model:open="isOpen">
            <DialogTrigger as-child>
                <Button type="button" variant="outline">
                    <ImageIcon class="mr-2 size-4" />
                    {{ label }}
                </Button>
            </DialogTrigger>
            <DialogContent class="max-w-4xl">
                <DialogHeader>
                    <DialogTitle>Select Media</DialogTitle>
                    <DialogDescription>
                        Choose from existing media or upload new files
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <!-- Upload Area -->
                    <div
                        class="relative rounded-lg border-2 border-dashed p-6 text-center transition-colors"
                        :class="{
                            'border-primary bg-primary/5': isDragging,
                        }"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop"
                    >
                        <input
                            id="media-upload"
                            type="file"
                            multiple
                            :accept="accept"
                            class="hidden"
                            @change="handleFileInput"
                        />
                        <label
                            for="media-upload"
                            class="flex cursor-pointer flex-col items-center gap-2"
                        >
                            <Upload class="size-8 text-muted-foreground" />
                            <p class="text-sm font-medium">
                                Click to upload or drag and drop
                            </p>
                        </label>
                    </div>

                    <!-- Search -->
                    <div class="relative">
                        <Search
                            class="absolute top-1/2 left-2 size-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search media..."
                            class="pl-8"
                            @keyup.enter="loadMedia"
                        />
                    </div>

                    <!-- Media Grid -->
                    <div class="max-h-96 overflow-y-auto rounded border p-2">
                        <div
                            v-if="isLoading"
                            class="py-8 text-center text-sm text-muted-foreground"
                        >
                            Loading...
                        </div>
                        <div
                            v-else-if="mediaList.length > 0"
                            class="grid grid-cols-4 gap-2"
                        >
                            <button
                                v-for="media in mediaList"
                                :key="media.id"
                                type="button"
                                class="relative aspect-square overflow-hidden rounded border transition-all hover:border-primary"
                                :class="{
                                    'ring-2 ring-primary': isSelected(media),
                                }"
                                @click="toggleSelection(media)"
                            >
                                <img
                                    :src="media.thumbnail_url || media.url"
                                    :alt="media.name"
                                    class="size-full object-cover"
                                />
                                <div
                                    v-if="isSelected(media)"
                                    class="absolute inset-0 flex items-center justify-center bg-primary/20"
                                >
                                    <div
                                        class="flex size-6 items-center justify-center rounded-full bg-primary text-primary-foreground"
                                    >
                                        ✓
                                    </div>
                                </div>
                            </button>
                        </div>
                        <div
                            v-else
                            class="py-8 text-center text-sm text-muted-foreground"
                        >
                            No media found. Upload some files to get started.
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="isOpen = false"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        @click="confirmSelection"
                        :disabled="selectedMedia.length === 0"
                    >
                        Select
                        {{
                            selectedMedia.length > 0
                                ? `(${selectedMedia.length})`
                                : ''
                        }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
