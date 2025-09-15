<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'

interface Props {
  profile: {
    slug: string
    display_name: string
    bio: string | null
    avatar_url: string | null
    quick_amounts: number[]
  }
  currency: 'eur'
  minAmountCents: number
  maxAmountCents: number
}

const props = defineProps<Props>()

function formatCents(cents: number) {
  const symbol = props.currency === 'eur' ? '€' : '$'
  const whole = Math.trunc(cents / 100)
  const fractional = Math.abs(cents % 100)
  // Show cents only when they are non-zero
  return fractional === 0
    ? `${symbol}${whole}`
    : `${symbol}${(cents / 100).toFixed(2)}`
}

// Choose initial amount: first quick amount if available and > 0, otherwise fallback to 1 cent
const initialAmount = Array.isArray(props.profile.quick_amounts) && props.profile.quick_amounts.length > 0 && (props.profile.quick_amounts[0] ?? 0) > 0
  ? props.profile.quick_amounts[0]
  : 1

const form = useForm({
  slug: props.profile.slug,
  amount_cents: initialAmount,
  currency: props.currency,
})

// For custom input, bind dollars then convert to cents via watcher
const customAmount = ref<string>((form.amount_cents / 100).toFixed(2))
const min = props.minAmountCents
const max = props.maxAmountCents

function setQuickAmount(cents: number) {
  form.amount_cents = cents
  customAmount.value = (cents / 100).toFixed(2)
  if (cents >= 1) form.clearErrors('amount_cents')
}

watch(customAmount, (value) => {
  const normalized = Number((value || '0').replace(/[^0-9.]/g, ''))
  if (!isFinite(normalized)) return
  let cents = Math.round(normalized * 100)
  // Allow 0 while typing; clamp only to [0, max]
  if (value !== '') {
    cents = Math.min(Math.max(cents, 0), max)
  }
  form.amount_cents = cents
  if (cents >= 1) {
    form.clearErrors('amount_cents')
  }
})

function blockInvalidChars(e: KeyboardEvent) {
  const invalid = ['e', 'E', '+', '-']
  if (invalid.includes(e.key)) e.preventDefault()
  if (e.key === '.') {
    const el = e.currentTarget as HTMLInputElement
    if (el.value.includes('.')) e.preventDefault()
  }
}

function onCustomInput(e: Event) {
  const el = e.target as HTMLInputElement
  // keep digits and one dot, max 2 decimals
  let v = el.value.replace(/[^0-9.]/g, '')
  v = v.replace(/(\..*)\./g, '$1')
  const parts = v.split('.')
  if (parts.length > 1) {
    parts[1] = parts[1].slice(0, 2)
    v = parts[0] + '.' + parts[1]
  }
  customAmount.value = v
}

function numericAmount(): number {
  const n = Number((form.amount_cents as unknown) as number)
  return Number.isFinite(n) ? Math.round(n) : 0
}

function isAmountValid() {
  // Frontend rule: block only when 0; respect max cap
  const n = numericAmount()
  return n >= 1 && n <= max
}

function submit() {
  const n = numericAmount()
  if (!(n >= 1)) {
    form.setError('amount_cents', 'Amount must be at least 1 cent')
    return
  }
  // Ensure payload is an integer number of cents
  form.amount_cents = n
  form.post('/pay/checkout', {
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="min-h-svh bg-background">
    <Head :title="`Tip ${props.profile.display_name}`" />

    <div class="mx-auto max-w-xl px-6 py-10">
      <div class="flex flex-col items-center text-center">
        <img v-if="props.profile.avatar_url" :src="props.profile.avatar_url" alt="Avatar" class="mb-4 h-20 w-20 rounded-full object-cover" />
        <div v-else class="mb-4 h-20 w-20 rounded-full bg-neutral-200" />
        <h1 class="text-2xl font-semibold">{{ props.profile.display_name }}</h1>
        <p v-if="props.profile.bio" class="mt-2 text-sm text-muted-foreground whitespace-pre-line">{{ props.profile.bio }}</p>
      </div>

      <div class="mt-8 rounded-lg border bg-card p-6 shadow-sm">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
          <Button
            v-for="(amt, i) in props.profile.quick_amounts"
            :key="i"
            type="button"
            variant="outline"
            :class="form.amount_cents === amt ? 'border-primary text-primary' : ''"
            @click="setQuickAmount(amt)"
          >
            {{ formatCents(amt) }}
          </Button>
        </div>

        <div class="mt-6 grid gap-2">
          <label for="custom" class="text-sm font-medium">Custom amount ({{ props.currency.toUpperCase() }})</label>
          <div class="flex flex-col gap-1">
            <Input
              id="custom"
              v-model="customAmount"
              type="number"
              inputmode="decimal"
              step="0.01"
              :min="(min / 100).toFixed(2)"
              :max="(max / 100).toFixed(2)"
              placeholder="10.00"
              @keydown="blockInvalidChars"
              @input="onCustomInput"
            />
            <span class="text-xs text-muted-foreground">Min {{ formatCents(min) }}, Max {{ formatCents(max) }}</span>
          </div>
          <InputError :message="form.errors.amount_cents" />
        </div>

        <div class="mt-6">
          <Button class="w-full" :disabled="form.processing" @click="submit">Pay {{ formatCents(form.amount_cents) }}</Button>
        </div>
      </div>
    </div>
  </div>
</template>
