<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard } from '@/routes'
import { type BreadcrumbItem } from '@/types'
import { Head, router, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { computed, ref, watch } from 'vue'
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'

interface Transaction {
  id: string
  type: 'tip' | 'payout'
  amount_cents: number | null
  currency: string
  status: string | null
  occurred_at: string | null
}

interface Props {
  totals: { total_cents: number; count: number; currency: 'eur' }
  balance?: { available_cents: number; currency: 'eur'; payouts_count: number }
  transactions: Transaction[]
  admin?: { is_admin: boolean; stats?: { users_count: number; active_payouts_count: number } | null }
  filters?: { range?: string }
  rangeOptions?: Array<{ value: string; label: string }>
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: dashboard().url },
]

function formatAmount(cents: number | null, currency: string) {
  if (cents == null) return '—'
  const symbol = currency.toLowerCase() === 'eur' ? '€' : currency.toUpperCase() + ' '
  // Show cents only if non-zero
  const whole = Math.trunc(cents / 100)
  const fractional = Math.abs(cents % 100)
  return fractional === 0 ? `${symbol}${whole}` : `${symbol}${(cents / 100).toFixed(2)}`
}

const payoutForm = useForm<{ amount_cents: number | null }>({ amount_cents: null })
const payoutOpen = ref(false)
const payoutAmount = ref<string>('')
const payoutErrorInline = ref<string>('')
const availableCents = ref<number>(props.balance?.available_cents ?? 0)
const payoutsCount = ref<number>(props.balance?.payouts_count ?? 0)
const isAdmin = !!props.admin?.is_admin
const rangeOptions = computed(() => props.rangeOptions ?? [])
const activeRange = computed(() => props.filters?.range ?? 'all')

watch(
  () => props.balance?.available_cents,
  (value) => {
    availableCents.value = value ?? 0
  }
)

watch(
  () => props.balance?.payouts_count,
  (value) => {
    payoutsCount.value = value ?? 0
  }
)

function openPayoutDialog() {
  payoutErrorInline.value = ''
  payoutAmount.value = ''
  if (availableCents.value <= 0) {
    payoutErrorInline.value = 'No available balance to withdraw.'
    return
  }
  payoutOpen.value = true
}

function setAll() {
  payoutAmount.value = (availableCents.value / 100).toFixed(2)
}

function submitPayout() {
  payoutErrorInline.value = ''
  payoutForm.amount_cents = null

  const raw = (payoutAmount.value || '').trim()

  if (raw === '') {
    payoutForm.amount_cents = null
  } else {
    const sanitized = raw.replace(/[^0-9.,]/g, '').replace(/,/g, '.')
    const normalized = Number(sanitized)

    if (!sanitized || Number.isNaN(normalized)) {
      payoutErrorInline.value = 'Enter a valid amount.'
      return
    }

    if (normalized <= 0) {
      payoutErrorInline.value = 'Amount must be greater than zero.'
      return
    }

    const cents = Math.round(normalized * 100)

    if (cents > availableCents.value) {
      payoutErrorInline.value = 'Amount exceeds available balance.'
      return
    }

    payoutForm.amount_cents = cents
  }

  payoutForm.post('/payout-requests', {
    preserveScroll: true,
    onSuccess: () => {
      // Optimistically update dashboard widgets without full reload
      if (payoutForm.amount_cents === null) {
        availableCents.value = 0
      } else {
        availableCents.value = Math.max(0, availableCents.value - payoutForm.amount_cents)
      }
      payoutsCount.value = payoutsCount.value + 1
      payoutForm.reset()
      payoutAmount.value = ''
      payoutOpen.value = false
      payoutErrorInline.value = ''
    },
  })
}

function changeRange(range: string) {
  if (range === activeRange.value) {
    return
  }
  router.get(
    dashboard().url,
    { range },
    {
      preserveScroll: true,
      preserveState: true,
      replace: true,
    }
  )
}
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
          <div class="flex items-center justify-between rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border gap-4">
            <div class="text-sm">
              <div class="text-muted-foreground">Available Balance</div>
              <div class="mt-1 font-medium">{{ formatAmount(availableCents, props.totals.currency) }}</div>
              <div class="mt-2 text-muted-foreground">Payouts: {{ props.balance?.payouts_count ?? 0 }}</div>
              <p v-if="payoutErrorInline" class="mt-2 text-xs text-red-600">{{ payoutErrorInline }}</p>
            </div>
            <div>
              <Dialog :open="payoutOpen" @update:open="(v: boolean) => (payoutOpen = v)">
                <Button type="button" :disabled="payoutForm.processing" @click="openPayoutDialog">Request Payout</Button>
                <DialogContent>
                  <DialogHeader class="space-y-2">
                    <DialogTitle>Request payout</DialogTitle>
                    <DialogDescription>Enter amount to withdraw or withdraw all available funds.</DialogDescription>
                  </DialogHeader>
                  <div class="grid gap-2">
                    <label for="payout_amount" class="text-sm font-medium">Amount ({{ props.totals.currency.toUpperCase() }})</label>
                    <div class="flex items-center gap-2">
                      <Input id="payout_amount" v-model="payoutAmount" type="text" inputmode="decimal" placeholder="0.00" class="max-w-[200px]" />
                      <Button type="button" variant="secondary" @click="setAll">All ({{ formatAmount(availableCents, props.totals.currency) }})</Button>
                    </div>
                    <InputError :message="payoutForm.errors.amount_cents" />
                  </div>
                  <DialogFooter class="gap-2">
                    <DialogClose as-child>
                      <Button variant="secondary">Cancel</Button>
                    </DialogClose>
                    <Button type="button" :disabled="payoutForm.processing" @click="submitPayout">Submit</Button>
                  </DialogFooter>
                </DialogContent>
              </Dialog>
            </div>
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
        <div class="overflow-x-auto p-4 space-y-4">
          <div class="flex flex-wrap gap-2">
            <Button
              v-for="option in rangeOptions"
              :key="option.value"
              size="sm"
              type="button"
              :variant="option.value === activeRange ? 'default' : 'outline'"
              @click="changeRange(option.value)"
            >
              {{ option.label }}
            </Button>
          </div>
          <table class="w-full text-left text-sm">
            <thead class="text-xs text-muted-foreground">
              <tr>
                <th class="py-2">Date</th>
                <th class="py-2">Type</th>
                <th class="py-2">Amount</th>
                <th class="py-2">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in props.transactions" :key="t.id" class="border-t border-sidebar-border/70">
                <td class="py-2">{{ t.occurred_at ? new Date(t.occurred_at).toLocaleString() : '—' }}</td>
                <td class="py-2 capitalize">{{ t.type }}</td>
                <td class="py-2">{{ formatAmount(t.amount_cents, t.currency) }}</td>
                <td class="py-2 capitalize">{{ t.status || '—' }}</td>
              </tr>
              <tr v-if="props.transactions.length === 0">
                <td class="py-6 text-muted-foreground" colspan="4">No transactions yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
