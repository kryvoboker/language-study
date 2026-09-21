<script setup lang="ts">
const { init } = useTheme();
onMounted(init);
</script>

<template>
    <NuxtLayout>
        <NuxtPage />
    </NuxtLayout>
</template>