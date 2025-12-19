<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Search } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

interface Author {
  name: string;
}

interface Post {
  id: number;
  title: string;
  slug: string;
  excerpt: string;
  is_featured: boolean;
  created_at: string;
  author: Author;
  featured_image: string | null;
  thumbnail: string | null;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface Posts {
  data: Post[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  links: PaginationLink[];
}

interface Filters {
  search?: string;
  featured?: boolean;
}

interface Props {
  posts: Posts;
  filters: Filters;
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');

// Debounce search
let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, (value) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get('/posts', { search: value || undefined }, {
      preserveState: true,
      preserveScroll: true,
    });
  }, 300);
});

const handlePageChange = (url: string | null) => {
  if (url) {
    router.get(url, {}, {
      preserveState: true,
      preserveScroll: false,
    });
  }
};
</script>

<template>
  <div>
    <Head>
      <title>Blog Posts</title>
      <meta name="description" content="Explore our latest blog posts and articles" />
    </Head>

    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <header class="bg-white shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
          <h1 class="text-4xl font-bold text-gray-900 mb-4">Blog</h1>
          <p class="text-lg text-gray-600">Explore our latest posts and articles</p>
        </div>
      </header>

      <!-- Main Content -->
      <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <!-- Search Bar -->
        <div class="mb-8">
          <div class="relative max-w-md">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
            <Input
              v-model="search"
              type="search"
              placeholder="Search posts..."
              class="pl-10"
            />
          </div>
        </div>

        <!-- Posts Grid -->
        <div v-if="posts.data.length > 0" class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
          <article
            v-for="post in posts.data"
            :key="post.id"
            class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow"
          >
            <!-- Featured Image -->
            <Link :href="`/posts/${post.slug}`" class="block">
              <div class="aspect-video bg-gray-200 overflow-hidden">
                <img
                  v-if="post.thumbnail"
                  :src="post.thumbnail"
                  :alt="post.title"
                  class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                  loading="lazy"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                  <span class="text-sm">No image</span>
                </div>
              </div>
            </Link>

            <!-- Post Content -->
            <div class="p-6">
              <!-- Featured Badge -->
              <div v-if="post.is_featured" class="mb-2">
                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                  Featured
                </span>
              </div>

              <!-- Title -->
              <h2 class="mb-2">
                <Link
                  :href="`/posts/${post.slug}`"
                  class="text-xl font-semibold text-gray-900 hover:text-blue-600 transition-colors line-clamp-2"
                >
                  {{ post.title }}
                </Link>
              </h2>

              <!-- Excerpt -->
              <p v-if="post.excerpt" class="text-gray-600 mb-4 line-clamp-3">
                {{ post.excerpt }}
              </p>

              <!-- Meta -->
              <div class="flex items-center justify-between text-sm text-gray-500">
                <span>{{ post.author.name }}</span>
                <time :datetime="post.created_at">{{ post.created_at }}</time>
              </div>
            </div>
          </article>
        </div>

        <!-- No Results -->
        <div v-else class="text-center py-12">
          <p class="text-gray-600 text-lg">No posts found.</p>
        </div>

        <!-- Pagination -->
        <div v-if="posts.last_page > 1" class="mt-12 flex items-center justify-center gap-2">
          <Button
            v-for="link in posts.links"
            :key="link.label"
            :variant="link.active ? 'default' : 'outline'"
            :disabled="!link.url"
            @click="handlePageChange(link.url)"
            v-html="link.label"
          />
        </div>
      </main>
    </div>
  </div>
</template>
