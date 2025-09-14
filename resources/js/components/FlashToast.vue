<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { computed, ref, watch, onMounted } from 'vue'

const page = usePage()
const message = computed(() => (page.props as any).flash?.status || (page.props as any).status)
const show = ref(false)

let timer: any = null
watch(message, (val) => {
  if (val) {
    show.value = true
    clearTimeout(timer)
    timer = setTimeout(() => (show.value = false), 2500)
  }
})
onMounted(() => {
  if (message.value) {
    show.value = true
    timer = setTimeout(() => (show.value = false), 2500)
  }
})
</script>

<template>
  <transition name="fade">
    <div v-if="show" class="fixed bottom-4 right-4 z-50 rounded-md bg-black/85 px-3 py-2 text-sm text-white shadow-lg">
      {{ typeof message === 'string' ? message : 'Saved' }}
    </div>
  </transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

