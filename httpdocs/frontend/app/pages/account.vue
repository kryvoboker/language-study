<script setup lang="ts">
const { user, initialized, refresh, logout } = useAuth()

if (import.meta.client && !initialized.value) await refresh()

if (initialized.value && !user.value) {
  await navigateTo('/login')
}

const signOut = async () => {
  await logout()
  await navigateTo('/login')
}
</script>

<template>
  <div class="min-h-screen bg-base-100">
    <AppHeader />
    <main class="mx-auto max-w-2xl px-4 py-10 sm:px-6">
      <div v-if="user" class="card border border-base-content/10 bg-base-100 shadow-xl">
        <div class="card-body gap-5">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary">{{ $t('account.title') }}</p>
            <h1 class="mt-2 text-3xl font-semibold">{{ user.name }}</h1>
          </div>
          <dl class="grid gap-3 text-sm">
            <div>
              <dt class="text-base-content/55">{{ $t('account.email') }}</dt>
              <dd class="mt-1 font-medium">{{ user.email }}</dd>
            </div>
          </dl>
          <div>
            <button type="button" class="btn btn-outline" @click="signOut">{{ $t('nav.logout') }}</button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
