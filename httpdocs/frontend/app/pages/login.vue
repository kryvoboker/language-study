<script setup lang="ts">
const email = ref('')
const password = ref('')
const errorMessage = ref('')
const loading = ref(false)
const social = (provider: string) => navigateTo(`/api/auth/social/${provider}`)
const login = async () => {
  loading.value = true; errorMessage.value = ''
  try {
    await $fetch('/api/auth/login', { method: 'POST', body: { email: email.value, password: password.value } })
    await navigateTo('/')
  } catch { errorMessage.value = 'Unable to sign in. Check your credentials and email verification.' } finally { loading.value = false }
}
</script>
<template><div class="min-h-screen bg-base-100"><AppHeader/><main class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-md place-items-center px-4 py-10"><div class="card w-full border border-base-content/10 bg-base-100 shadow-xl"><div class="card-body gap-4"><h1 class="card-title text-2xl">{{ $t('auth.welcome') }}</h1><input v-model="email" class="input" type="email" autocomplete="email" placeholder="you@example.com"><input v-model="password" class="input" type="password" autocomplete="current-password" placeholder="Password"><div v-if="errorMessage" class="alert alert-error alert-soft text-sm">{{ errorMessage }}</div><button class="btn btn-primary" :disabled="loading" @click="login"><span v-if="loading" class="loading loading-spinner loading-xs"/>{{ $t('nav.login') }}</button><NuxtLink to="/forgot-password" class="link link-primary text-sm">Forgot password?</NuxtLink><div class="divider">or</div><button v-for="provider in ['google','github','facebook']" :key="provider" class="btn btn-outline w-full capitalize" @click="social(provider)">{{ $t('auth.continueWith') }} {{ provider }}</button></div></div></main></div></template>
