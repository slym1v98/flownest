<script setup lang="ts">
import { computed } from 'vue';
import { generateHTML } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';

interface ContentRendererProps {
  content: any;
  class?: string;
}

const props = withDefaults(defineProps<ContentRendererProps>(), {
  class: '',
});

// Generate HTML from Tiptap JSON
const html = computed(() => {
  if (!props.content) return '';
  
  try {
    // If content is already a string, return it
    if (typeof props.content === 'string') {
      return props.content;
    }
    
    // Generate HTML from Tiptap JSON
    return generateHTML(props.content, [StarterKit]);
  } catch (error) {
    console.error('Error rendering content:', error);
    return '<p>Error rendering content</p>';
  }
});
</script>

<template>
  <div
    :class="[
      'prose prose-slate max-w-none',
      'prose-headings:font-bold prose-headings:text-gray-900',
      'prose-h1:text-4xl prose-h1:mb-4',
      'prose-h2:text-3xl prose-h2:mb-3',
      'prose-h3:text-2xl prose-h3:mb-2',
      'prose-p:text-gray-700 prose-p:leading-relaxed prose-p:mb-4',
      'prose-a:text-blue-600 prose-a:no-underline hover:prose-a:underline',
      'prose-strong:text-gray-900 prose-strong:font-semibold',
      'prose-em:text-gray-700 prose-em:italic',
      'prose-code:text-pink-600 prose-code:bg-gray-100 prose-code:px-1 prose-code:py-0.5 prose-code:rounded',
      'prose-pre:bg-gray-900 prose-pre:text-gray-100 prose-pre:p-4 prose-pre:rounded-lg prose-pre:overflow-x-auto',
      'prose-blockquote:border-l-4 prose-blockquote:border-gray-300 prose-blockquote:pl-4 prose-blockquote:italic prose-blockquote:text-gray-600',
      'prose-ul:list-disc prose-ul:pl-6 prose-ul:mb-4',
      'prose-ol:list-decimal prose-ol:pl-6 prose-ol:mb-4',
      'prose-li:text-gray-700 prose-li:mb-1',
      'prose-img:rounded-lg prose-img:shadow-md prose-img:w-full',
      'prose-hr:border-gray-300 prose-hr:my-8',
      props.class,
    ]"
    v-html="html"
  />
</template>
