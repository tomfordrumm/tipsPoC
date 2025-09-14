<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'

interface Props {
  profile: {
    slug: string
    display_name: string
  }
  returnUrl: string
}

const props = defineProps<Props>()
</script>

<template>
  <div class="min-h-svh bg-background">
    <Head title="Payment Canceled" />
    <div class="mx-auto max-w-xl px-6 py-12 text-center">
      <h1 class="text-2xl font-semibold">Payment canceled</h1>
      <p class="mt-2 text-muted-foreground">You can try again anytime.</p>

      <div class="mt-8">
        <Link :href="props.returnUrl" class="text-sm text-primary underline">Back to tipping page</Link>
      </div>
    </div>
  </div>
</template>

