<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import TiptapEditor from '@/components/TiptapEditor.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItemType } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface PagePayload {
    id: number;
    slug: string;
    title: string;
    content: string;
    updated_at: string | null;
}

const props = defineProps<{
    page: PagePayload;
    updateUrl: string;
}>();

const form = useForm({
    content: props.page.content || '',
});

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: 'Admin',
        href: '/admin/users',
    },
    {
        title: props.page.title,
        href: '',
    },
]);

const formattedUpdatedAt = computed(() => {
    if (!props.page.updated_at) {
        return null;
    }

    const date = new Date(props.page.updated_at);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    return date.toLocaleString();
});

const handleSubmit = () => {
    form.put(props.updateUrl, {
        preserveScroll: true,
        onSuccess: () => {
            // noop
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Admin • ${props.page.title}`" />
        <div class="mx-auto w-full max-w-4xl px-6 py-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold">{{ props.page.title }}</h1>
                <p v-if="formattedUpdatedAt" class="mt-1 text-sm text-muted-foreground">
                    Last updated: {{ formattedUpdatedAt }}
                </p>
            </div>
            <form class="space-y-4" @submit.prevent="handleSubmit">
                <div>
                    <TiptapEditor v-model="form.content" placeholder="Write the content for this page..." />
                    <InputError :message="form.errors.content" class="mt-2" />
                </div>
                <div class="flex items-center justify-end">
                    <Button type="submit" :disabled="form.processing">Save</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
