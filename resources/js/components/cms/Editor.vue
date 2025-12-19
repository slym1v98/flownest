<script setup lang="ts">
import { Button } from '@/components/ui/button';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import {
    Bold,
    Heading2,
    Heading3,
    Italic,
    List,
    ListOrdered,
    Quote,
    Redo,
    Undo,
} from 'lucide-vue-next';
import { watch } from 'vue';

interface Props {
    modelValue?: any;
    placeholder?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: null,
    placeholder: 'Write something...',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: any): void;
}>();

const editor = useEditor({
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [2, 3],
            },
        }),
    ],
    content: props.modelValue || '',
    editorProps: {
        attributes: {
            class: 'prose prose-sm sm:prose-base lg:prose-lg xl:prose-xl dark:prose-invert max-w-none min-h-[300px] focus:outline-none px-4 py-3',
        },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getJSON());
    },
});

// Watch for external changes to modelValue
watch(
    () => props.modelValue,
    (value) => {
        if (!editor.value) return;

        const isSame =
            JSON.stringify(editor.value.getJSON()) === JSON.stringify(value);

        if (!isSame && value) {
            editor.value.commands.setContent(value, false);
        }
    },
);
</script>

<template>
    <div
        class="rounded-lg border border-input bg-background focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2"
    >
        <!-- Toolbar -->
        <div
            v-if="editor"
            class="flex flex-wrap gap-1 border-b border-input p-2"
        >
            <Button
                variant="ghost"
                size="sm"
                type="button"
                @click="editor.chain().focus().toggleBold().run()"
                :class="{
                    'bg-muted': editor.isActive('bold'),
                }"
            >
                <Bold class="size-4" />
            </Button>

            <Button
                variant="ghost"
                size="sm"
                type="button"
                @click="editor.chain().focus().toggleItalic().run()"
                :class="{
                    'bg-muted': editor.isActive('italic'),
                }"
            >
                <Italic class="size-4" />
            </Button>

            <div class="mx-1 w-px bg-border" />

            <Button
                variant="ghost"
                size="sm"
                type="button"
                @click="
                    editor.chain().focus().toggleHeading({ level: 2 }).run()
                "
                :class="{
                    'bg-muted': editor.isActive('heading', { level: 2 }),
                }"
            >
                <Heading2 class="size-4" />
            </Button>

            <Button
                variant="ghost"
                size="sm"
                type="button"
                @click="
                    editor.chain().focus().toggleHeading({ level: 3 }).run()
                "
                :class="{
                    'bg-muted': editor.isActive('heading', { level: 3 }),
                }"
            >
                <Heading3 class="size-4" />
            </Button>

            <div class="mx-1 w-px bg-border" />

            <Button
                variant="ghost"
                size="sm"
                type="button"
                @click="editor.chain().focus().toggleBulletList().run()"
                :class="{
                    'bg-muted': editor.isActive('bulletList'),
                }"
            >
                <List class="size-4" />
            </Button>

            <Button
                variant="ghost"
                size="sm"
                type="button"
                @click="editor.chain().focus().toggleOrderedList().run()"
                :class="{
                    'bg-muted': editor.isActive('orderedList'),
                }"
            >
                <ListOrdered class="size-4" />
            </Button>

            <Button
                variant="ghost"
                size="sm"
                type="button"
                @click="editor.chain().focus().toggleBlockquote().run()"
                :class="{
                    'bg-muted': editor.isActive('blockquote'),
                }"
            >
                <Quote class="size-4" />
            </Button>

            <div class="mx-1 w-px bg-border" />

            <Button
                variant="ghost"
                size="sm"
                type="button"
                @click="editor.chain().focus().undo().run()"
                :disabled="!editor.can().undo()"
            >
                <Undo class="size-4" />
            </Button>

            <Button
                variant="ghost"
                size="sm"
                type="button"
                @click="editor.chain().focus().redo().run()"
                :disabled="!editor.can().redo()"
            >
                <Redo class="size-4" />
            </Button>
        </div>

        <!-- Editor Content -->
        <EditorContent :editor="editor" />
    </div>
</template>

<style>
/* Tiptap Editor Styles */
.ProseMirror {
    outline: none;
}

.ProseMirror p.is-editor-empty:first-child::before {
    color: var(--muted-foreground);
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
}

.ProseMirror blockquote {
    border-left: 3px solid var(--border);
    padding-left: 1rem;
}
</style>
