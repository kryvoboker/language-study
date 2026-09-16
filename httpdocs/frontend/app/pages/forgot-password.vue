<script setup lang="ts">
const config=useRuntimeConfig(); const email=ref(''); const sent=ref(false); const submit=async()=>{ await $fetch(`${config.public.apiBase}/auth/forgot-password`, {method:'POST', body:{email:email.value}}); sent.value=true }
</script>
<template><div class="min-h-screen"><AppHeader/><main class="mx-auto max-w-md px-4 py-16"><div class="card border border-base-content/10"><div class="card-body"><h1 class="card-title">Reset password</h1><input v-model="email" class="input" type="email" placeholder="Email"><button class="btn btn-primary" @click="submit">Send reset link</button><p v-if="sent" class="text-sm text-success">If the account exists, the email has been sent.</p></div></div></main></div></template>
