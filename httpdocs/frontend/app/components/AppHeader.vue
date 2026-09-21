<script setup lang="ts">
const { locale, locales, setLocale } = useI18n()
const { toggleTheme } = useStorefrontTheme()
const { user, initialized, refresh, logout } = useAuth()

onMounted(() => {
  if (!initialized.value) void refresh()
})

const signOut = async () => {
  await logout()
  await navigateTo('/login')
}
</script>

<template>
  <header class="sticky top-0 z-30 border-b border-base-content/10 bg-base-100/85 backdrop-blur-xl">
    <div class="mx-auto flex h-16 max-w-[1500px] items-center justify-between px-4 sm:px-6">
      <NuxtLink to="/" class="flex items-center gap-2 font-semibold tracking-tight">
        <span class="grid size-8 place-items-center rounded-xl border border-primary/30 bg-primary/10 text-primary">
          <span class="icon-[tabler--language] size-5" />
        </span>
        <span>NativeLens</span>
      </NuxtLink>
      <nav class="flex items-center gap-2">
        <NuxtLink to="/contact" class="btn btn-text btn-sm">{{ $t('nav.contact') }}</NuxtLink>
        <button
          type="button"
          class="btn btn-square btn-text btn-sm"
          :aria-label="$t('theme.toggle')"
          :title="$t('theme.toggle')"
          @click="toggleTheme"
        >
          <span class="theme-switch__to-light icon-[tabler--sun-high] size-5" />
          <span class="theme-switch__to-dark icon-[tabler--moon] size-5" />
        </button>
        <div class="dropdown dropdown-end">
          <button type="button" class="btn btn-text btn-sm" aria-haspopup="menu">
            <span class="icon-[tabler--world] size-4" />
            {{ locale.toUpperCase() }}
          </button>
          <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu">
            <li v-for="item in locales" :key="item.code">
              <button type="button" @click="setLocale(item.code)">{{ item.name }}</button>
            </li>
          </ul>
        </div>
        <template v-if="user">
          <NuxtLink to="/account" class="btn btn-outline btn-sm">{{ $t('nav.account') }}</NuxtLink>
          <button type="button" class="btn btn-primary btn-sm hidden sm:inline-flex" @click="signOut">
            {{ $t('nav.logout') }}
          </button>
        </template>
        <template v-else-if="initialized">
          <NuxtLink to="/login" class="btn btn-outline btn-sm">{{ $t('nav.login') }}</NuxtLink>
          <NuxtLink to="/register" class="btn btn-primary btn-sm hidden sm:inline-flex">
            {{ $t('nav.signup') }}
          </NuxtLink>
        </template>
      </nav>
    </div>
  </header>
</template>
