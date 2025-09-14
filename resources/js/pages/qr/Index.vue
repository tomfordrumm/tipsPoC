<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { show as showQr } from '@/routes/qr'

interface Props {
  slug: string
  url: string
}

const props = defineProps<Props>()

const qrContainer = ref<HTMLElement | null>(null)
let qrInstance: any = null
const copied = ref(false)

onMounted(async () => {
  const mod = await import('qr-code-styling')
  const QRCodeStyling = mod.default

  qrInstance = new QRCodeStyling({
    width: 280,
    height: 280,
    data: props.url,
    type: 'png',
    dotsOptions: { color: '#000000', type: 'rounded' },
    backgroundOptions: { color: '#ffffff' },
  })
  if (qrContainer.value) {
    qrInstance.append(qrContainer.value)
  }
})

function downloadPng() {
  if (!qrInstance) return
  qrInstance.download({ name: `tip-${props.slug}`, extension: 'png' })
}

async function copyLink() {
  try {
    await navigator.clipboard.writeText(props.url)
    copied.value = true
    setTimeout(() => (copied.value = false), 1500)
  } catch {}
}

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'QR',
    href: showQr().url,
  },
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="QR Code" />
    <div class="mx-auto max-w-xl space-y-6 p-6">
      <h1 class="text-2xl font-semibold">QR Code</h1>
      <p class="text-sm text-muted-foreground">Link: {{ props.url }}</p>

      <div class="flex flex-col items-center gap-4">
        <div ref="qrContainer" class="rounded-lg border bg-white p-4 shadow-sm"></div>
        <div class="flex gap-3">
          <Button type="button" @click="downloadPng">Download PNG</Button>
          <Button type="button" variant="outline" @click="copyLink">{{ copied ? 'Copied!' : 'Copy link' }}</Button>
        </div>
      </div>
    </div>
  </AppLayout>
  
</template>
