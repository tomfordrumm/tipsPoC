<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

interface Payout {
  id: number
  user: { id: number; name: string } | null
  amount_cents: number | null
  status: string
  requested_at: string | null
  processed_at: string | null
  admin_note: string | null
}

interface Props {
  payouts: {
    data: Payout[]
    next_page_url: string | null
    prev_page_url: string | null
  }
}

const props = defineProps<Props>()

function formatAmount(cents: number | null) {
  if (cents == null) return '—'
  return `€${(cents / 100).toFixed(2)}`
}

function updateStatus(p: Payout, status: 'paid' | 'rejected') {
  const form = useForm({ _method: 'PATCH', status, admin_note: p.admin_note || '' })
  form.post(`/admin/payout-requests/${p.id}`, {
    onSuccess: () => {
      // Optimistically reflect new status so action buttons hide immediately
      p.status = status
      p.processed_at = new Date().toISOString()
    },
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Admin • Payout Requests" />
    <div class="p-6">
      <h1 class="text-2xl font-semibold mb-4">Payout Requests</h1>
      <div class="rounded-lg border">
        <table class="w-full text-left text-sm">
          <thead class="text-xs text-muted-foreground">
            <tr class="border-b">
              <th class="py-2 px-3">ID</th>
              <th class="py-2 px-3">User</th>
              <th class="py-2 px-3">Amount</th>
              <th class="py-2 px-3">Status</th>
              <th class="py-2 px-3">Requested</th>
              <th class="py-2 px-3">Processed</th>
              <th class="py-2 px-3">Admin note</th>
              <th class="py-2 px-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in props.payouts.data" :key="p.id" class="border-b align-top">
              <td class="py-2 px-3">{{ p.id }}</td>
              <td class="py-2 px-3">{{ p.user?.name || '—' }}</td>
              <td class="py-2 px-3">{{ formatAmount(p.amount_cents) }}</td>
              <td class="py-2 px-3 capitalize">{{ p.status }}</td>
              <td class="py-2 px-3">{{ p.requested_at ? new Date(p.requested_at).toLocaleString() : '—' }}</td>
              <td class="py-2 px-3">{{ p.processed_at ? new Date(p.processed_at).toLocaleString() : '—' }}</td>
              <td class="py-2 px-3">
                <template v-if="p.status === 'requested'">
                  <textarea v-model="p.admin_note" rows="2" class="min-h-16 w-full rounded-md border border-input bg-background px-2 py-1 text-sm"></textarea>
                </template>
                <template v-else>
                  <div v-if="p.admin_note" class="rounded-md border border-input bg-muted/40 px-3 py-2 text-sm">
                    {{ p.admin_note }}
                  </div>
                  <span v-else class="text-sm text-muted-foreground">—</span>
                </template>
              </td>
              <td class="py-2 px-3">
                <div v-if="p.status === 'requested'" class="flex gap-2">
                  <Button size="sm" @click="updateStatus(p, 'paid')">Mark as paid</Button>
                  <Button size="sm" variant="destructive" @click="updateStatus(p, 'rejected')">Reject</Button>
                </div>
                <div v-else class="text-muted-foreground text-xs">—</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="mt-3 flex gap-3">
        <a v-if="props.payouts.prev_page_url" :href="props.payouts.prev_page_url" class="text-sm text-primary underline">Prev</a>
        <a v-if="props.payouts.next_page_url" :href="props.payouts.next_page_url" class="text-sm text-primary underline">Next</a>
      </div>
    </div>
  </AppLayout>
</template>
