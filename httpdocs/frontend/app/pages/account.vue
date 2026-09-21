<script setup lang="ts">
const { user, initialized, refresh, logout } = useAuth()
const localePath                             = useLocalePath()

if (import.meta.client && !initialized.value) await refresh()

if (initialized.value && !user.value) {
    await navigateTo(localePath('/login'))
}

const signOut = async () => {
    await logout()
    await navigateTo(localePath('/login'))
}
</script>

<template>
    <section class="account-section">
        <div class="container">
            <div v-if="user" class="card max-w-3xl mx-auto">
                <div class="card-body">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-primary bg-primary-content rounded px-3 py-1.5">
                            {{ $t('account.title') }}
                        </div>
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
        </div>
    </section>
</template>