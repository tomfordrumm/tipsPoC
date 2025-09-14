<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'

interface Props {
  profile: {
    slug: string
    display_name: string
    review_url: string | null
  }
  returnUrl: string
}

const props = defineProps<Props>()
</script>

<template>
  <div class="min-h-svh bg-background">
    <Head title="Payment Successful" />
    <div class="mx-auto max-w-xl px-6 py-12 text-center">
      <h1 class="text-2xl font-semibold">Thank you for your tip!</h1>
      <p class="mt-2 text-muted-foreground">Your payment was successful.</p>

      <div v-if="props.profile.review_url" class="mt-6">
        <a :href="props.profile.review_url!" target="_blank" rel="noopener" class="text-primary underline">
          Leave a review for {{ props.profile.display_name }}
        </a>
      </div>

      <div class="mt-8">
        <Link :href="props.returnUrl" class="text-sm text-primary underline">Back to tipping page</Link>
      </div>
    </div>
  </div>
</template>

