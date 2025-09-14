<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard } from '@/routes'
import { type BreadcrumbItem } from '@/types'
import { Head, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

interface Props {
  totals: { total_cents: number; count: number; currency: 'eur' }
  transactions: Array<{ id: number; amount_cents: number; currency: string; status: string; created_at: string; paid_at?: string | null }>
  admin?: { is_admin: boolean; stats?: { users_count: number; active_payouts_count: number } | null }
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: dashboard().url },
]

function formatAmount(cents: number, currency: string) {
  const symbol = currency.toLowerCase() === 'eur' ? '€' : currency.toUpperCase() + ' '
  return `${symbol}${(cents / 100).toFixed(2)}`
}

const payoutForm = useForm<{ amount_cents: number | null }>({ amount_cents: null })
const isAdmin = !!props.admin?.is_admin
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
      <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-4">
        <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
          <div class="text-sm text-muted-foreground">Total Tips</div>
          <div class="mt-1 text-2xl font-semibold">{{ formatAmount(props.totals.total_cents, props.totals.currency) }}</div>
        </div>
        <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
          <div class="text-sm text-muted-foreground">Tips Count</div>
          <div class="mt-1 text-2xl font-semibold">{{ props.totals.count }}</div>
        </div>
        <template v-if="!isAdmin">
          <div class="flex items-end justify-end rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
            <form @submit.prevent="payoutForm.post('/payout-requests')">
              <Button type="submit" :disabled="payoutForm.processing">Request Payout</Button>
            </form>
          </div>
        </template>
        <template v-else>
          <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
            <div class="text-sm text-muted-foreground">Users</div>
            <div class="mt-1 text-2xl font-semibold">{{ props.admin?.stats?.users_count ?? 0 }}</div>
          </div>
          <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
            <div class="text-sm text-muted-foreground">Active Payouts</div>
            <div class="mt-1 text-2xl font-semibold">{{ props.admin?.stats?.active_payouts_count ?? 0 }}</div>
          </div>
        </template>
      </div>

      <div v-if="!isAdmin" class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <div class="border-b border-sidebar-border/70 px-4 py-3 text-sm font-medium dark:border-sidebar-border">Transactions</div>
        <div class="overflow-x-auto p-4">
          <table class="w-full text-left text-sm">
            <thead class="text-xs text-muted-foreground">
              <tr>
                <th class="py-2">Date</th>
                <th class="py-2">Amount</th>
                <th class="py-2">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in props.transactions" :key="t.id" class="border-t border-sidebar-border/70">
                <td class="py-2">{{ new Date(t.paid_at || t.created_at).toLocaleString() }}</td>
                <td class="py-2">{{ formatAmount(t.amount_cents, t.currency) }}</td>
                <td class="py-2 capitalize">{{ t.status }}</td>
              </tr>
              <tr v-if="props.transactions.length === 0">
                <td class="py-6 text-muted-foreground" colspan="3">No transactions yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
