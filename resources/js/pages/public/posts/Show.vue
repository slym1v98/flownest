<script setup lang="ts">
import ContentRenderer from '@/components/cms/ContentRenderer.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, User } from 'lucide-vue-next';

interface Author {
    name: string;
}

interface Image {
    id: number;
    url: string;
    preview_url: string;
    thumb_url: string;
    name: string;
}

interface Post {
    id: number;
    title: string;
    slug: string;
    content: any;
    excerpt: string;
    created_at: string;
    updated_at: string;
    author: Author;
    featured_image: string | null;
    images: Image[];
}

interface Seo {
    title: string;
    description: string;
    keywords: string;
    og_title: string;
    og_description: string;
    og_image: string;
    twitter_title: string;
    twitter_description: string;
    twitter_image: string;
}

interface Props {
    post: Post;
    seo: Seo;
}

const props = defineProps<Props>();

// Compute canonical URL
const canonicalUrl = `${window.location.origin}/posts/${props.post.slug}`;
</script>

<template>
    <div>
        <!-- SEO Meta Tags -->
        <Head>
            <title>{{ seo.title }}</title>
            <meta name="description" :content="seo.description" />
            <meta v-if="seo.keywords" name="keywords" :content="seo.keywords" />
            <link rel="canonical" :href="canonicalUrl" />

            <!-- Open Graph Tags -->
            <meta property="og:title" :content="seo.og_title" />
            <meta property="og:description" :content="seo.og_description" />
            <meta property="og:type" content="article" />
            <meta property="og:url" :content="canonicalUrl" />
            <meta
                v-if="seo.og_image"
                property="og:image"
                :content="seo.og_image"
            />

            <!-- Twitter Card Tags -->
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" :content="seo.twitter_title" />
            <meta
                name="twitter:description"
                :content="seo.twitter_description"
            />
            <meta
                v-if="seo.twitter_image"
                name="twitter:image"
                :content="seo.twitter_image"
            />

            <!-- Article Meta -->
            <meta
                property="article:published_time"
                :content="post.created_at"
            />
            <meta property="article:modified_time" :content="post.updated_at" />
            <meta property="article:author" :content="post.author.name" />
        </Head>

        <!-- Main Content -->
        <div class="min-h-screen bg-gray-50">
            <!-- Header -->
            <header class="bg-white shadow-sm">
                <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
                    <Link
                        href="/posts"
                        class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 transition-colors hover:text-gray-900"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back to all posts
                    </Link>
                </div>
            </header>

            <!-- Article -->
            <article class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
                <!-- Article Header -->
                <header class="mb-8">
                    <h1
                        class="mb-4 text-4xl font-bold text-gray-900 lg:text-5xl"
                    >
                        {{ post.title }}
                    </h1>

                    <div class="flex items-center gap-6 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            <User class="h-4 w-4" />
                            <span>{{ post.author.name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Calendar class="h-4 w-4" />
                            <time :datetime="post.created_at">{{
                                post.created_at
                            }}</time>
                        </div>
                    </div>

                    <p
                        v-if="post.excerpt"
                        class="mt-6 text-lg leading-relaxed text-gray-600"
                    >
                        {{ post.excerpt }}
                    </p>
                </header>

                <!-- Featured Image -->
                <div
                    v-if="post.featured_image"
                    class="mb-8 overflow-hidden rounded-lg shadow-lg"
                >
                    <img
                        :src="post.featured_image"
                        :alt="post.title"
                        class="h-auto w-full object-cover"
                        loading="lazy"
                    />
                </div>

                <!-- Article Content -->
                <div class="rounded-lg bg-white p-8 shadow-sm lg:p-12">
                    <ContentRenderer :content="post.content" />
                </div>

                <!-- Article Footer -->
                <footer class="mt-12 border-t border-gray-200 pt-8">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Last updated: {{ post.updated_at }}
                        </div>
                        <Link
                            href="/posts"
                            class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 transition-colors hover:text-blue-700"
                        >
                            View all posts
                        </Link>
                    </div>
                </footer>
            </article>
        </div>
    </div>
</template>
