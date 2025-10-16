<script setup lang="ts">
import { dashboard, login, register } from '@/routes'
import { Head, Link } from '@inertiajs/vue3'
import AppLogoIcon from '@/components/AppLogoIcon.vue'
</script>

<template>
  <Head title="Home">
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
  </Head>

  <div class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a]">
    <!-- Top Bar -->
    <header class="w-full">
      <nav class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-4">
        <div class="flex items-center gap-2">
          <AppLogoIcon class="size-6" />
          <span class="text-sm font-semibold tracking-tight">Tips</span>
        </div>

        <div class="flex items-center gap-3 text-sm">
          <template v-if="$page.props.auth?.user">
            <Link
              :href="dashboard()"
              class="inline-block rounded-sm border border-[#19140035] px-4 py-1.5 leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
            >
              Dashboard
            </Link>
          </template>
          <template v-else>
            <Link
              :href="login()"
              class="inline-block rounded-sm border border-transparent px-4 py-1.5 leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
            >
              Log in
            </Link>
            <Link
              :href="register()"
              class="inline-block rounded-sm border border-[#19140035] px-4 py-1.5 leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
            >
              Register
            </Link>
          </template>
        </div>
      </nav>
    </header>

    <!-- Hero -->
    <section class="mx-auto w-full max-w-6xl grow px-6 py-8 lg:flex lg:items-center lg:py-16">
      <div class="grid w-full gap-10 lg:grid-cols-2 lg:items-center">
        <div class="max-w-xl">
          <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
            Simple, contactless tips for waiters
          </h1>
          <p class="mt-3 text-[15px] leading-6 text-[#706f6c] dark:text-[#A1A09A]">
            Create a personal tipping page, print QR codes for your tables,
            and receive secure payments via Stripe. No apps, no hassle — just
            fast, cashless gratitude.
          </p>

          <div class="mt-6 flex items-center gap-3 text-sm">
            <Link
              :href="register()"
              class="inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-2 text-white hover:border-black hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
            >
              Get started free
            </Link>
            <Link
              :href="login()"
              class="inline-block rounded-sm border border-[#19140035] px-5 py-2 text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
            >
              Sign in
            </Link>
          </div>

          <div class="mt-6 flex items-center gap-4 text-xs text-[#706f6c] dark:text-[#A1A09A]">
            <div class="flex items-center gap-2">
              <span class="inline-block h-2 w-2 rounded-full bg-[#f53003] dark:bg-[#FF4433]" />
              Stripe Test Mode ready
            </div>
            <div class="hidden items-center gap-2 sm:flex">
              <span class="inline-block h-2 w-2 rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A]" />
              No app required
            </div>
          </div>
        </div>

        <div class="relative overflow-hidden rounded-lg bg-[#fff2f2] p-6 dark:bg-[#1D0002]">
          <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-md border border-[#e3e3e0] bg-white p-4 dark:border-[#3E3E3A] dark:bg-[#161615]">
              <div class="text-sm font-medium">Your Tipping Page</div>
              <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">Share a clean profile with quick amounts and custom tips.</p>
            </div>
            <div class="rounded-md border border-[#e3e3e0] bg-white p-4 dark:border-[#3E3E3A] dark:bg-[#161615]">
              <div class="text-sm font-medium">QR Codes</div>
              <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">Print and place on menus or tables for instant tips.</p>
            </div>
            <div class="rounded-md border border-[#e3e3e0] bg-white p-4 dark:border-[#3E3E3A] dark:bg-[#161615]">
              <div class="text-sm font-medium">Secure Payments</div>
              <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">Powered by Stripe Payment Links. Safe and convenient.</p>
            </div>
            <div class="rounded-md border border-[#e3e3e0] bg-white p-4 dark:border-[#3E3E3A] dark:bg-[#161615]">
              <div class="text-sm font-medium">Dashboard</div>
              <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">Track tips and request payouts from a simple dashboard.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="mx-auto w-full max-w-6xl px-6 py-8 text-xs text-[#706f6c] dark:text-[#A1A09A]">
      <div class="flex flex-col gap-2 border-t border-[#e3e3e0] py-4 dark:border-[#3E3E3A] sm:flex-row sm:items-center sm:justify-between">
        <span>© {{ new Date().getFullYear() }} Tips — Simple tipping for waiters</span>
        <div class="flex gap-4">
          <Link href="/terms-and-conditions" class="hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">Terms</Link>
          <Link href="/privacy-policy" class="hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">Privacy</Link>
        </div>
      </div>
    </footer>
  </div>
</template>
