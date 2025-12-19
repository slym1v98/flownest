<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Search, Facebook, Twitter } from 'lucide-vue-next';

interface SeoData {
    meta_title?: string;
    meta_description?: string;
    meta_keywords?: string;
    og_title?: string;
    og_description?: string;
    og_image?: string;
    twitter_title?: string;
    twitter_description?: string;
    twitter_image?: string;
}

interface Props {
    modelValue: SeoData;
    fallbackTitle?: string;
    fallbackDescription?: string;
    siteUrl?: string;
}

const props = withDefaults(defineProps<Props>(), {
    fallbackTitle: '',
    fallbackDescription: '',
    siteUrl: 'https://example.com',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: SeoData): void;
}>();

const seoData = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const updateField = (field: keyof SeoData, value: string) => {
    seoData.value = {
        ...seoData.value,
        [field]: value,
    };
};

// Computed values for previews
const displayTitle = computed(() => {
    return seoData.value.meta_title || props.fallbackTitle || 'Page Title';
});

const displayDescription = computed(() => {
    return (
        seoData.value.meta_description ||
        props.fallbackDescription ||
        'Page description goes here...'
    );
});

const displayUrl = computed(() => {
    const url = props.siteUrl;
    const slug = props.fallbackTitle
        .toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^\w-]/g, '');
    return `${url}/${slug || 'page'}`;
});

// Social media specific
const ogTitle = computed(() => {
    return seoData.value.og_title || displayTitle.value;
});

const ogDescription = computed(() => {
    return seoData.value.og_description || displayDescription.value;
});

const twitterTitle = computed(() => {
    return seoData.value.twitter_title || displayTitle.value;
});

const twitterDescription = computed(() => {
    return seoData.value.twitter_description || displayDescription.value;
});

// Character counters
const titleLength = computed(() => seoData.value.meta_title?.length || 0);
const descriptionLength = computed(
    () => seoData.value.meta_description?.length || 0,
);

const titleStatus = computed(() => {
    if (titleLength.value === 0) return 'default';
    if (titleLength.value < 30) return 'warning';
    if (titleLength.value > 60) return 'warning';
    return 'success';
});

const descriptionStatus = computed(() => {
    if (descriptionLength.value === 0) return 'default';
    if (descriptionLength.value < 120) return 'warning';
    if (descriptionLength.value > 160) return 'warning';
    return 'success';
});
</script>

<template>
    <div class="space-y-4">
        <!-- SEO Fields -->
        <Card class="p-4">
            <h3 class="mb-4 font-semibold">SEO Settings</h3>
            <div class="space-y-4">
                <!-- Meta Title -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <Label for="meta_title">Meta Title</Label>
                        <span
                            class="text-xs"
                            :class="{
                                'text-muted-foreground':
                                    titleStatus === 'default',
                                'text-warning': titleStatus === 'warning',
                                'text-success': titleStatus === 'success',
                            }"
                        >
                            {{ titleLength }} / 60
                        </span>
                    </div>
                    <Input
                        id="meta_title"
                        :value="seoData.meta_title"
                        placeholder="Optimized title for search engines"
                        @input="updateField('meta_title', ($event.target as HTMLInputElement).value)"
                    />
                    <p class="text-xs text-muted-foreground">
                        Recommended: 30-60 characters
                    </p>
                </div>

                <!-- Meta Description -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <Label for="meta_description">Meta Description</Label>
                        <span
                            class="text-xs"
                            :class="{
                                'text-muted-foreground':
                                    descriptionStatus === 'default',
                                'text-warning': descriptionStatus === 'warning',
                                'text-success': descriptionStatus === 'success',
                            }"
                        >
                            {{ descriptionLength }} / 160
                        </span>
                    </div>
                    <Textarea
                        id="meta_description"
                        :value="seoData.meta_description"
                        placeholder="Brief description for search results"
                        rows="3"
                        @input="updateField('meta_description', ($event.target as HTMLTextAreaElement).value)"
                    />
                    <p class="text-xs text-muted-foreground">
                        Recommended: 120-160 characters
                    </p>
                </div>

                <!-- Meta Keywords -->
                <div class="space-y-2">
                    <Label for="meta_keywords">Meta Keywords</Label>
                    <Input
                        id="meta_keywords"
                        :value="seoData.meta_keywords"
                        placeholder="keyword1, keyword2, keyword3"
                        @input="updateField('meta_keywords', ($event.target as HTMLInputElement).value)"
                    />
                    <p class="text-xs text-muted-foreground">
                        Comma-separated keywords
                    </p>
                </div>
            </div>
        </Card>

        <!-- Google Search Preview -->
        <Card class="p-4">
            <div class="mb-3 flex items-center gap-2">
                <Search class="size-4 text-muted-foreground" />
                <h3 class="font-semibold">Google Search Preview</h3>
            </div>
            <div class="space-y-1 rounded border bg-muted/30 p-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-sm text-muted-foreground">
                        {{ displayUrl }}
                    </span>
                </div>
                <div class="text-lg font-medium text-[#1a0dab] hover:underline">
                    {{ displayTitle }}
                </div>
                <div class="text-sm text-muted-foreground">
                    {{ displayDescription }}
                </div>
            </div>
        </Card>

        <!-- Social Media Previews -->
        <Card class="p-4">
            <h3 class="mb-4 font-semibold">Social Share Preview</h3>

            <!-- Facebook/Open Graph Preview -->
            <div class="mb-4 space-y-3">
                <div class="flex items-center gap-2">
                    <Facebook class="size-4 text-[#1877f2]" />
                    <h4 class="text-sm font-medium">Facebook Preview</h4>
                </div>
                <div
                    class="overflow-hidden rounded-lg border bg-background"
                >
                    <div
                        v-if="seoData.og_image"
                        class="aspect-video w-full bg-muted"
                    >
                        <img
                            :src="seoData.og_image"
                            :alt="ogTitle"
                            class="size-full object-cover"
                        />
                    </div>
                    <div
                        v-else
                        class="flex aspect-video w-full items-center justify-center bg-muted text-sm text-muted-foreground"
                    >
                        No image set
                    </div>
                    <div class="space-y-1 border-t p-3">
                        <div class="text-xs uppercase text-muted-foreground">
                            {{ new URL(displayUrl).hostname }}
                        </div>
                        <div class="font-semibold">{{ ogTitle }}</div>
                        <div class="line-clamp-2 text-sm text-muted-foreground">
                            {{ ogDescription }}
                        </div>
                    </div>
                </div>

                <!-- OG Fields (Collapsed by default) -->
                <details class="space-y-2">
                    <summary class="cursor-pointer text-sm text-muted-foreground hover:text-foreground">
                        Customize Open Graph tags
                    </summary>
                    <div class="space-y-3 pt-2">
                        <div class="space-y-1.5">
                            <Label for="og_title">OG Title</Label>
                            <Input
                                id="og_title"
                                :value="seoData.og_title"
                                placeholder="Leave empty to use meta title"
                                @input="updateField('og_title', ($event.target as HTMLInputElement).value)"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <Label for="og_description">OG Description</Label>
                            <Textarea
                                id="og_description"
                                :value="seoData.og_description"
                                placeholder="Leave empty to use meta description"
                                rows="2"
                                @input="updateField('og_description', ($event.target as HTMLTextAreaElement).value)"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <Label for="og_image">OG Image URL</Label>
                            <Input
                                id="og_image"
                                :value="seoData.og_image"
                                placeholder="https://example.com/image.jpg"
                                @input="updateField('og_image', ($event.target as HTMLInputElement).value)"
                            />
                        </div>
                    </div>
                </details>
            </div>

            <Separator class="my-4" />

            <!-- Twitter Card Preview -->
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <Twitter class="size-4 text-[#1da1f2]" />
                    <h4 class="text-sm font-medium">Twitter Card Preview</h4>
                </div>
                <div
                    class="overflow-hidden rounded-lg border bg-background"
                >
                    <div
                        v-if="seoData.twitter_image || seoData.og_image"
                        class="aspect-video w-full bg-muted"
                    >
                        <img
                            :src="seoData.twitter_image || seoData.og_image"
                            :alt="twitterTitle"
                            class="size-full object-cover"
                        />
                    </div>
                    <div
                        v-else
                        class="flex aspect-video w-full items-center justify-center bg-muted text-sm text-muted-foreground"
                    >
                        No image set
                    </div>
                    <div class="space-y-1 border-t p-3">
                        <div class="font-semibold">{{ twitterTitle }}</div>
                        <div class="line-clamp-2 text-sm text-muted-foreground">
                            {{ twitterDescription }}
                        </div>
                        <div class="text-xs text-muted-foreground">
                            {{ new URL(displayUrl).hostname }}
                        </div>
                    </div>
                </div>

                <!-- Twitter Fields (Collapsed by default) -->
                <details class="space-y-2">
                    <summary class="cursor-pointer text-sm text-muted-foreground hover:text-foreground">
                        Customize Twitter Card tags
                    </summary>
                    <div class="space-y-3 pt-2">
                        <div class="space-y-1.5">
                            <Label for="twitter_title">Twitter Title</Label>
                            <Input
                                id="twitter_title"
                                :value="seoData.twitter_title"
                                placeholder="Leave empty to use meta title"
                                @input="updateField('twitter_title', ($event.target as HTMLInputElement).value)"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <Label for="twitter_description">Twitter Description</Label>
                            <Textarea
                                id="twitter_description"
                                :value="seoData.twitter_description"
                                placeholder="Leave empty to use meta description"
                                rows="2"
                                @input="updateField('twitter_description', ($event.target as HTMLTextAreaElement).value)"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <Label for="twitter_image">Twitter Image URL</Label>
                            <Input
                                id="twitter_image"
                                :value="seoData.twitter_image"
                                placeholder="Leave empty to use OG image"
                                @input="updateField('twitter_image', ($event.target as HTMLInputElement).value)"
                            />
                        </div>
                    </div>
                </details>
            </div>
        </Card>
    </div>
</template>

<style scoped>
.text-warning {
    color: hsl(38 92% 50%);
}

.text-success {
    color: hsl(142 76% 36%);
}
</style>
