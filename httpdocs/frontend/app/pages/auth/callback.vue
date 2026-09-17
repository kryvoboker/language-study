<script setup lang="ts">
const errorMessage = ref('')

onMounted(async () => {
  try {
    const route = useRoute()
    await $fetch('/api/auth/pkce/callback', { query: route.query })
    await navigateTo('/')
  } catch {
    errorMessage.value = 'Authorization expired or was denied. Please try again.'
  }
})
</script>

<template>
  <div class="grid min-h-screen place-items-center">
    <div class="text-center">
      <span v-if="!errorMessage" class="loading loading-spinner loading-lg" />
      <p v-else class="text-error">{{ errorMessage }}</p>
      <NuxtLink v-if="errorMessage" class="link link-primary" to="/login">Return to sign in</NuxtLink>
    </div>
  </div>
</template>
