<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

interface Props {
  tips: {
    data: Array<{ id: number; user: { id: number; name: string } | null; amount_cents: number; currency: string; status: string; paid_at?: string | null; created_at: string }>
    next_page_url: string | null
    prev_page_url: string | null
  }
}

const props = defineProps<Props>()

function formatAmount(cents: number, currency: string) {
  const symbol = currency.toLowerCase() === 'eur' ? '€' : currency.toUpperCase() + ' '
  return `${symbol}${(cents / 100).toFixed(2)}`
}
</script>

<template>
  <AppLayout>
    <Head title="Admin • Tips" />
    <div class="p-6">
      <h1 class="text-2xl font-semibold mb-4">Tips</h1>
      <div class="rounded-lg border">
        <table class="w-full text-left text-sm">
          <thead class="text-xs text-muted-foreground">
            <tr class="border-b">
              <th class="py-2 px-3">ID</th>
              <th class="py-2 px-3">User</th>
              <th class="py-2 px-3">Amount</th>
              <th class="py-2 px-3">Status</th>
              <th class="py-2 px-3">Paid at</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in props.tips.data" :key="t.id" class="border-b">
              <td class="py-2 px-3">{{ t.id }}</td>
              <td class="py-2 px-3">{{ t.user?.name || '—' }}</td>
              <td class="py-2 px-3">{{ formatAmount(t.amount_cents, t.currency) }}</td>
              <td class="py-2 px-3">{{ t.status }}</td>
              <td class="py-2 px-3">{{ t.paid_at ? new Date(t.paid_at).toLocaleString() : '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="mt-3 flex gap-3">
        <Link v-if="props.tips.prev_page_url" :href="props.tips.prev_page_url" class="text-sm text-primary underline">Prev</Link>
        <Link v-if="props.tips.next_page_url" :href="props.tips.next_page_url" class="text-sm text-primary underline">Next</Link>
      </div>
    </div>
  </AppLayout>
</template>

