<script setup lang="ts">
const email        = ref('')
const password     = ref('')
const errorMessage = ref('')
const loading      = ref(false)
const { refresh }  = useAuth()
const localePath   = useLocalePath()
const social       = (provider: string) => navigateTo(`/api/auth/social/${provider}`)
const login        = async () => {
    loading.value      = true;
    errorMessage.value = ''
    try {
        await $fetch('/api/auth/login', { method: 'POST', body: { email: email.value, password: password.value } })
        await refresh()
        await navigateTo(localePath('/'))
    } catch { errorMessage.value = 'Unable to sign in. Check your credentials and try again.' } finally { loading.value = false }
}
</script>
<template>
    <section class="login-section">
        <div class="container">
            <div class="card max-w-3xl mx-auto">
                <div class="card-body gap-4">
                    <h1 class="card-title text-2xl">{{ $t('auth.welcome') }}</h1>
                    <input v-model="email" class="input" type="email" autocomplete="email" placeholder="you@example.com">
                    <input v-model="password" class="input" type="password" autocomplete="current-password" placeholder="Password">
                    <div v-if="errorMessage" class="alert alert-error alert-soft text-sm">{{ errorMessage }}</div>
                    <button class="btn btn-primary" :disabled="loading" @click="login">
                        <span v-if="loading" class="loading loading-spinner loading-xs"/>
                        {{ $t('nav.login') }}
                    </button>
                    <div class="flex items-center justify-between gap-3">
                        <NuxtLink :to="localePath('/forgot-password')" class="link link-primary text-sm">Forgot password?</NuxtLink>
                        <NuxtLink :to="localePath('/register')" class="btn btn-outline btn-sm">{{ $t('nav.signup') }}</NuxtLink>
                    </div>
                    <div class="divider">or</div>
                    <button class="btn btn-outline w-full capitalize" @click="social('google')">
                        {{ $t('auth.continueWith') }} Google
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>