<script setup lang="ts">
const { locale, locales, setLocale }         = useI18n()
const localePath                             = useLocalePath()
const { user, initialized, refresh, logout } = useAuth()

onMounted(() => {
    if (!initialized.value) void refresh()
})

const signOut = async () => {
    await logout()
    await navigateTo(localePath('/login'))
}
</script>

<template>
    <header class="sticky top-0 z-30 border-b border-base-content/10 bg-base-200 backdrop-blur-xl">
        <div class="container py-0!">
            <div class="flex h-16 items-center justify-between px-4 sm:px-6">
                <NuxtLink :to="localePath('/')" class="flex items-center gap-2 font-semibold tracking-tight">
                    <span class="grid size-8 place-items-center rounded-xl border border-primary/50 bg-primary-content text-primary">
                      <span class="icon-[tabler--language] size-5"/>
                    </span>
                    <span>NativeLens</span>
                </NuxtLink>
                <nav class="flex items-center gap-2">
                    <NuxtLink :to="localePath('/')" class="btn btn-outline btn-sm">
                        {{ $t('nav.home') }}
                    </NuxtLink>

                    <NuxtLink :to="localePath('/contact')" class="btn btn-outline btn-sm">
                        {{ $t('nav.contact') }}
                    </NuxtLink>

                    <ThemeMenu/>

                    <div class="dropdown dropdown-end">
                        <button type="button" class="btn btn-text btn-sm" aria-haspopup="menu">
                            <span class="icon-[tabler--world] size-4"/>
                            {{ locale.toUpperCase() }}
                        </button>
                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu">
                            <li v-for="item in locales" :key="item.code">
                                <button type="button" @click="setLocale(item.code)">{{ item.name }}</button>
                            </li>
                        </ul>
                    </div>
                    <template v-if="user">
                        <NuxtLink :to="localePath('/account')" class="btn btn-outline btn-sm">
                            {{ $t('nav.account') }}
                        </NuxtLink>
                        <button type="button" class="btn btn-primary btn-sm hidden sm:inline-flex" @click="signOut">
                            {{ $t('nav.logout') }}
                        </button>
                    </template>
                    <template v-else-if="initialized">
                        <NuxtLink :to="localePath('/login')" class="btn btn-outline btn-sm">
                            {{ $t('nav.login') }}
                        </NuxtLink>
                        <NuxtLink :to="localePath('/register')" class="btn btn-primary btn-sm hidden sm:inline-flex">
                            {{ $t('nav.signup') }}
                        </NuxtLink>
                    </template>
                </nav>
            </div>
        </div>
    </header>
</template>