<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem } from '@/types';
import { edit as editPublicProfile } from '@/routes/public-profile';

interface Props {
  profile: {
    slug: string
    display_name: string
    bio: string | null
    review_url: string | null
    quick_amounts: (number | null)[]
    avatar_path: string | null
    avatar_url: string | null
  }
}

const props = defineProps<Props>();

const form = useForm({
  _method: 'PATCH',
  avatar: null as File | null,
  slug: props.profile.slug ?? '',
  display_name: props.profile.display_name ?? '',
  bio: props.profile.bio ?? '',
  review_url: props.profile.review_url ?? '',
  quick_amounts: (props.profile.quick_amounts ?? [null, null, null, null]).map((v) => (typeof v === 'number' ? v : 0)),
});

function submit() {
  form.post('/public-profile', {
    forceFormData: true,
    onSuccess: () => {
      // no-op; Inertia will keep flash/state
    },
  });
}

function onFileChange(e: Event) {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    form.avatar = target.files[0];
  } else {
    form.avatar = null;
  }
}

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Public profile',
    href: editPublicProfile().url,
  },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Edit Profile" />

    <div class="mx-auto max-w-3xl space-y-8 px-4 pt-4 sm:px-6 lg:px-8">
      <h1 class="text-2xl font-semibold tracking-tight">Profile</h1>

      <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-6">
        <div class="grid gap-2">
          <Label for="avatar">Avatar</Label>
          <div class="flex items-center gap-4">
            <img v-if="props.profile.avatar_url" :src="props.profile.avatar_url" alt="Avatar" class="h-16 w-16 rounded-full object-cover" />
            <div v-else class="h-16 w-16 rounded-full bg-neutral-200" />
            <Input id="avatar" type="file" accept="image/*" @change="onFileChange" />
          </div>
          <InputError :message="form.errors.avatar" />
        </div>

        <div class="grid gap-2">
          <Label for="display_name">Display name</Label>
          <Input id="display_name" v-model="form.display_name" placeholder="Public display name" />
          <InputError :message="form.errors.display_name" />
        </div>

        <div class="grid gap-2">
          <Label for="bio">Bio (plain text or markdown)</Label>
          <textarea id="bio" v-model="form.bio" rows="4" class="min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
          <InputError :message="form.errors.bio" />
        </div>

        <div class="grid gap-2">
          <div class="flex items-center justify-between gap-2">
            <Label for="review_url">Reviews URL</Label>
            <a
              href="https://support.google.com/business/answer/16334724"
              target="_blank"
              rel="noopener"
              class="text-xs text-primary underline"
            >
              How to find this link?
            </a>
          </div>
          <Input id="review_url" v-model="form.review_url" placeholder="https://..." />
          <InputError :message="form.errors.review_url" />
        </div>

        <div class="grid gap-2">
          <Label for="slug">Slug</Label>
          <Input id="slug" v-model="form.slug" placeholder="your-slug" />
          <InputError :message="form.errors.slug" />
        </div>

        <div class="grid gap-2">
          <Label>Quick amounts (4 values, cents)</Label>
          <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <Input
              v-for="(amt, i) in form.quick_amounts"
              :key="i"
              type="number"
              min="100"
              step="50"
              v-model.number="form.quick_amounts[i]"
            />
          </div>
          <p class="text-xs text-muted-foreground">
            Minimum 100 (1&nbsp;€) and each value must be unique.
          </p>
          <InputError :message="form.errors.quick_amounts" />
          <InputError
            v-for="i in 4"
            :key="i"
            :message="(form.errors as any)[`quick_amounts.${i-1}`]"
          />
        </div>

        <div class="flex items-center gap-4">
          <Button type="submit" :disabled="form.processing">Save</Button>
          <p v-if="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
        </div>
      </form>
    </div>
  </AppLayout>

</template>
