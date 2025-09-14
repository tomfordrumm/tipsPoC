<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

interface Props {
  users: {
    data: Array<{ id: number; name: string; email: string; is_admin: boolean; profile: { slug: string; display_name: string } | null }>
    next_page_url: string | null
    prev_page_url: string | null
  }
}

const props = defineProps<Props>()
</script>

<template>
  <AppLayout>
    <Head title="Admin • Users" />
    <div class="p-6">
      <h1 class="text-2xl font-semibold mb-4">Users</h1>
      <div class="rounded-lg border">
        <table class="w-full text-left text-sm">
          <thead class="text-xs text-muted-foreground">
            <tr class="border-b">
              <th class="py-2 px-3">ID</th>
              <th class="py-2 px-3">Name</th>
              <th class="py-2 px-3">Email</th>
              <th class="py-2 px-3">Profile</th>
              <th class="py-2 px-3"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in props.users.data" :key="u.id" class="border-b">
              <td class="py-2 px-3">{{ u.id }}</td>
              <td class="py-2 px-3">{{ u.name }}</td>
              <td class="py-2 px-3">{{ u.email }}</td>
              <td class="py-2 px-3">{{ u.profile?.display_name || '—' }}</td>
              <td class="py-2 px-3"><Link :href="`/admin/users/${u.id}`" class="text-primary underline">Open</Link></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="mt-3 flex gap-3">
        <Link v-if="props.users.prev_page_url" :href="props.users.prev_page_url" class="text-sm text-primary underline">Prev</Link>
        <Link v-if="props.users.next_page_url" :href="props.users.next_page_url" class="text-sm text-primary underline">Next</Link>
      </div>
    </div>
  </AppLayout>
</template>

