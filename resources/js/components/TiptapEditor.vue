<script setup lang="ts">
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { computed, onBeforeUnmount, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: string;
        placeholder?: string;
    }>(),
    {
        modelValue: '',
        placeholder: 'Start typing…',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3],
            },
        }),
    ],
    editorProps: {
        attributes: {
            class: 'min-h-[320px] w-full px-3 py-4 text-base leading-relaxed focus:outline-none',
        },
    },
    onUpdate({ editor }) {
        emit('update:modelValue', editor.getHTML());
    },
});

watch(
    () => props.modelValue,
    (value) => {
        if (!editor.value) {
            return;
        }

        const current = editor.value.getHTML();
        if (value !== current) {
            editor.value.commands.setContent(value || '', false);
        }
    },
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});

const canUndo = computed(() => editor.value?.can().undo() ?? false);
const canRedo = computed(() => editor.value?.can().redo() ?? false);

const run = (action: (editor: ReturnType<typeof useEditor>['value']) => void) => {
    if (!editor.value) {
        return;
    }

    action(editor.value);
};
</script>

<template>
    <div class="rounded-lg border border-border bg-background">
        <div class="flex flex-wrap items-center gap-1 border-b border-border bg-muted/60 px-2 py-1.5 text-sm">
            <button
                type="button"
                class="rounded px-2 py-1 font-semibold hover:bg-background"
                :class="editor?.isActive('bold') ? 'bg-background text-primary' : ''"
                @click="run((editor) => editor.chain().focus().toggleBold().run())"
            >
                B
            </button>
            <button
                type="button"
                class="rounded px-2 py-1 italic hover:bg-background"
                :class="editor?.isActive('italic') ? 'bg-background text-primary' : ''"
                @click="run((editor) => editor.chain().focus().toggleItalic().run())"
            >
                I
            </button>
            <button
                type="button"
                class="rounded px-2 py-1 hover:bg-background"
                :class="editor?.isActive('heading', { level: 2 }) ? 'bg-background text-primary' : ''"
                @click="run((editor) => editor.chain().focus().toggleHeading({ level: 2 }).run())"
            >
                H2
            </button>
            <button
                type="button"
                class="rounded px-2 py-1 hover:bg-background"
                :class="editor?.isActive('heading', { level: 3 }) ? 'bg-background text-primary' : ''"
                @click="run((editor) => editor.chain().focus().toggleHeading({ level: 3 }).run())"
            >
                H3
            </button>
            <button
                type="button"
                class="rounded px-2 py-1 hover:bg-background"
                :class="editor?.isActive('bulletList') ? 'bg-background text-primary' : ''"
                @click="run((editor) => editor.chain().focus().toggleBulletList().run())"
            >
                ••
            </button>
            <button
                type="button"
                class="rounded px-2 py-1 hover:bg-background"
                :class="editor?.isActive('orderedList') ? 'bg-background text-primary' : ''"
                @click="run((editor) => editor.chain().focus().toggleOrderedList().run())"
            >
                1.
            </button>
            <div class="ml-auto flex items-center gap-1">
                <button
                    type="button"
                    class="rounded px-2 py-1 text-xs uppercase tracking-wide hover:bg-background disabled:opacity-40"
                    :disabled="!canUndo"
                    @click="run((editor) => editor.chain().focus().undo().run())"
                >
                    Undo
                </button>
                <button
                    type="button"
                    class="rounded px-2 py-1 text-xs uppercase tracking-wide hover:bg-background disabled:opacity-40"
                    :disabled="!canRedo"
                    @click="run((editor) => editor.chain().focus().redo().run())"
                >
                    Redo
                </button>
            </div>
        </div>
        <div class="relative">
            <div v-if="!modelValue" class="pointer-events-none absolute left-3 top-4 text-sm text-muted-foreground">
                {{ placeholder }}
            </div>
            <EditorContent :editor="editor" class="editor-content" />
        </div>
    </div>
</template>

<style scoped>
.editor-content :deep(p) {
    margin-bottom: 0.75rem;
}

.editor-content :deep(h2) {
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    font-size: 1.25rem;
    font-weight: 600;
}

.editor-content :deep(h3) {
    margin-top: 1.25rem;
    margin-bottom: 0.75rem;
    font-size: 1.1rem;
    font-weight: 600;
}

.editor-content :deep(ul),
.editor-content :deep(ol) {
    margin-left: 1.5rem;
    margin-bottom: 0.75rem;
    list-style-position: outside;
}

.editor-content :deep(li) {
    margin-bottom: 0.25rem;
}
</style>
