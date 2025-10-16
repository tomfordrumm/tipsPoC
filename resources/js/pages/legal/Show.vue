<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    title: string;
    content: string;
    updated_at: string | null;
}>();

const updatedAt = computed(() => {
    if (!props.updated_at) {
        return null;
    }

    const date = new Date(props.updated_at);
    if (Number.isNaN(date.getTime())) {
        return null;
    }

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = String(date.getFullYear());

    return `${day}.${month}.${year}`;
});
</script>

<template>
    <Head :title="title" />
    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <header class="border-b border-border/60 bg-background/80">
            <nav class="mx-auto flex w-full max-w-5xl items-center justify-between px-6 py-5">
                <div class="flex items-center gap-2 text-sm font-semibold">
                    <AppLogoIcon class="size-6" />
                    <span>Tips</span>
                </div>
                <Link href="/" class="text-sm text-muted-foreground hover:text-foreground">Home</Link>
            </nav>
        </header>

        <main class="mx-auto w-full max-w-3xl flex-1 px-6 py-12">
            <h1 class="text-3xl font-semibold tracking-tight">{{ title }}</h1>
            <p v-if="updatedAt" class="mt-2 text-sm text-muted-foreground">
                Last updated {{ updatedAt }}
            </p>
            <div class="legal-html mt-8 text-sm leading-relaxed legal-content" v-html="content || '<p>No content yet.</p>'" />
        </main>

        <footer class="border-t border-border/60 bg-background/80">
            <div class="mx-auto flex w-full max-w-5xl flex-col gap-2 px-6 py-6 text-xs text-muted-foreground sm:flex-row sm:items-center sm:justify-between">
                <span>© {{ new Date().getFullYear() }} Tips</span>
                <div class="flex gap-4">
                    <Link href="/terms-and-conditions" class="hover:text-foreground">Terms</Link>
                    <Link href="/privacy-policy" class="hover:text-foreground">Privacy</Link>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.legal-html :deep(h2) {
    margin-top: 1.75rem;
    margin-bottom: 0.75rem;
    font-size: 1.25rem;
    font-weight: 600;
}

.legal-html :deep(h3) {
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    font-size: 1.1rem;
    font-weight: 600;
}

.legal-html :deep(p) {
    margin-bottom: 1rem;
}

.legal-html :deep(ul),
.legal-html :deep(ol) {
    margin-bottom: 1rem;
    margin-left: 1.5rem;
    list-style-position: outside;
}

.legal-html :deep(li) {
    margin-bottom: 0.4rem;
}

.legal-content :deep(ul) {
    list-style-type: disc;
}

.legal-content :deep(ol) {
    list-style-type: decimal;
}

.legal-html :deep(a) {
    color: hsl(var(--primary));
    text-decoration: underline;
}

.legal-html :deep(strong) {
    font-weight: 600;
}
</style>
