<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'

interface Props {
  user: { id: number; name: string; email: string; is_admin: boolean }
  profile: { id: number; slug: string; display_name: string; bio: string | null; review_url: string | null; quick_amounts: number[] } | null
}

const props = defineProps<Props>()

const form = useForm({
  _method: 'PATCH',
  display_name: props.profile?.display_name || props.user.name,
  slug: props.profile?.slug || '',
  bio: props.profile?.bio || '',
  review_url: props.profile?.review_url || '',
  quick_amounts: props.profile?.quick_amounts || [500, 1000, 2000, 5000],
})

function submit() {
  form.post(`/admin/users/${props.user.id}/profile`)
}

function toNumber(v: any) {
  const n = Number(v)
  return Number.isFinite(n) ? Math.round(n) : 0
}
</script>

<template>
  <AppLayout>
    <Head :title="`Admin • User ${props.user.id}`" />
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-semibold">User #{{ props.user.id }} • {{ props.user.name }}</h1>
      <div class="rounded-lg border p-4">
        <h2 class="font-semibold mb-3">Profile</h2>
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="text-sm font-medium">Display name</label>
            <Input v-model="form.display_name" />
            <InputError :message="form.errors.display_name" />
          </div>
          <div>
            <label class="text-sm font-medium">Slug</label>
            <Input v-model="form.slug" />
            <InputError :message="form.errors.slug" />
          </div>
          <div>
            <label class="text-sm font-medium">Bio</label>
            <textarea v-model="form.bio" rows="3" class="min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"></textarea>
            <InputError :message="form.errors.bio" />
          </div>
          <div>
            <label class="text-sm font-medium">Review URL</label>
            <Input v-model="form.review_url" />
            <InputError :message="form.errors.review_url" />
          </div>
          <div>
            <label class="text-sm font-medium">Quick amounts (cents)</label>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mt-1">
              <Input v-for="(amt, i) in form.quick_amounts" :key="i" type="number" min="1" step="1" v-model.number="form.quick_amounts[i]" @change="form.quick_amounts[i] = toNumber(form.quick_amounts[i])" />
            </div>
            <InputError :message="form.errors.quick_amounts" />
          </div>
          <div>
            <Button type="submit" :disabled="form.processing">Save</Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

