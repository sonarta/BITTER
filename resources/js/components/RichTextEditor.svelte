<script lang="ts">
    import { onMount } from 'svelte';
    import Quill from 'quill';
    import 'quill/dist/quill.snow.css';

    let {
        value = $bindable(''),
        placeholder = 'Write content here...',
    }: {
        value?: string;
        placeholder?: string;
    } = $props();

    let editorRef = $state<HTMLDivElement | null>(null);
    let quill: Quill | null = null;

    $effect(() => {
        if (!editorRef || quill) return;

        quill = new Quill(editorRef, {
            theme: 'snow',
            placeholder,
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['clean'],
                ],
            },
        });

        // Set initial value
        if (value) {
            quill.root.innerHTML = value;
        }

        // Listen for changes in the editor
        quill.on('text-change', () => {
            if (!quill) return;
            const html = quill.root.innerHTML;
            const text = quill.getText().trim();

            // Set value to empty string if editor is empty
            const newVal = text === '' && !html.includes('<img') ? '' : html;
            if (value !== newVal) {
                value = newVal;
            }
        });
    });

    // Update editor content when value prop changes externally
    $effect(() => {
        if (quill && value !== undefined && value !== quill.root.innerHTML) {
            // Avoid setting empty default paragraph to prevent infinite updates
            const currentHTML = quill.root.innerHTML;
            if (value !== currentHTML) {
                quill.root.innerHTML = value || '';
            }
        }
    });
</script>

<div class="quill-editor-container">
    <div
        bind:this={editorRef}
        class="min-h-[120px] max-h-[350px] overflow-y-auto"
    ></div>
</div>

<style>
    :global(.ql-toolbar.ql-snow) {
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
        border-color: hsl(var(--border)) !important;
        background-color: hsl(var(--muted) / 0.3);
    }
    :global(.ql-container.ql-snow) {
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
        border-color: hsl(var(--border)) !important;
        font-family: inherit !important;
        font-size: 0.875rem !important;
    }
    :global(.ql-editor) {
        min-height: 120px;
    }
    :global(.ql-snow .ql-stroke) {
        stroke: hsl(var(--foreground)) !important;
    }
    :global(.ql-snow .ql-fill) {
        fill: hsl(var(--foreground)) !important;
    }
    :global(.ql-snow .ql-picker) {
        color: hsl(var(--foreground)) !important;
    }
    :global(.ql-snow .ql-picker-options) {
        background-color: hsl(var(--popover)) !important;
        border-color: hsl(var(--border)) !important;
    }
</style>
