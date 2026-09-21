<script setup lang="ts">
const route = useRoute()
const localePath = useLocalePath()
const errorMessage = ref('')

onMounted(async () => {
  try {
    await $fetch('/api/auth/social/exchange', {
      method: 'POST',
      body: { ticket: String(route.query.ticket ?? '') },
    })
    await navigateTo(localePath('/'))
  } catch {
    errorMessage.value = 'Social sign-in ticket is invalid or expired.'
  }
})
</script>

<template>
  <div class="grid min-h-screen place-items-center">
    <div class="text-center">
      <span v-if="!errorMessage" class="loading loading-spinner loading-lg" />
      <p v-else class="text-error">{{ errorMessage }}</p>
    </div>
  </div>
</template>
