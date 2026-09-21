<script setup lang="ts">
type RegistrationErrors = Record<string, string[]>

const config = useRuntimeConfig()
const { t } = useI18n()
const form = reactive({ name: '', email: '', password: '', passwordConfirmation: '' })
const avatar = ref<File | null>(null)
const message = ref('')
const errorMessage = ref('')
const fieldErrors = ref<RegistrationErrors>({})
const isSubmitting = ref(false)

const selectAvatar = (event: Event) => {
  const input = event.target

  avatar.value = input instanceof HTMLInputElement ? (input.files?.[0] ?? null) : null
}

const submit = async () => {
  message.value = ''
  errorMessage.value = ''
  fieldErrors.value = {}
  isSubmitting.value = true

  const body = new FormData()
  body.set('name', form.name)
  body.set('email', form.email)
  body.set('password', form.password)
  body.set('password_confirmation', form.passwordConfirmation)

  if (avatar.value) {
    body.set('avatar', avatar.value)
  }

  try {
    await $fetch(`${config.public.apiBase}/auth/register`, { method: 'POST', body })
    message.value = t('auth.registrationSuccess')
    form.name = ''
    form.email = ''
    form.password = ''
    form.passwordConfirmation = ''
    avatar.value = null
  } catch (error) {
    const requestError = error as {
      data?: { errors?: RegistrationErrors }
    }
    fieldErrors.value = requestError.data?.errors ?? {}
    errorMessage.value = Object.values(fieldErrors.value).flat()[0] ?? t('auth.registrationFailed')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-base-100">
    <AppHeader />
    <main class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-md place-items-center px-4 py-10">
      <form class="card w-full border border-base-content/10" @submit.prevent="submit">
        <div class="card-body gap-4">
          <h1 class="card-title text-2xl">{{ $t('auth.create') }}</h1>

          <label class="form-control gap-2">
            <span class="label-text">{{ $t('auth.name') }}</span>
            <input
              v-model="form.name"
              class="input"
              name="name"
              autocomplete="name"
              maxlength="120"
              required
            >
          </label>

          <label class="form-control gap-2">
            <span class="label-text">{{ $t('auth.email') }}</span>
            <input
              v-model="form.email"
              class="input"
              name="email"
              type="email"
              autocomplete="email"
              maxlength="255"
              required
            >
          </label>

          <label class="form-control gap-2">
            <span class="label-text">{{ $t('auth.password') }}</span>
            <input
              v-model="form.password"
              class="input"
              name="password"
              type="password"
              autocomplete="new-password"
              minlength="6"
              maxlength="32"
              required
            >
            <span class="label-text-alt">{{ $t('auth.passwordHelp') }}</span>
          </label>

          <label class="form-control gap-2">
            <span class="label-text">{{ $t('auth.passwordConfirmation') }}</span>
            <input
              v-model="form.passwordConfirmation"
              class="input"
              name="password_confirmation"
              type="password"
              autocomplete="new-password"
              minlength="6"
              maxlength="32"
              required
            >
          </label>

          <label class="form-control gap-2">
            <span class="label-text">{{ $t('auth.avatarOptional') }}</span>
            <input
              class="file-input input"
              name="avatar"
              type="file"
              accept="image/jpeg,image/png"
              @change="selectAvatar"
            >
            <span class="label-text-alt">{{ $t('auth.avatarHelp') }}</span>
          </label>

          <div v-if="message" class="alert alert-success alert-soft text-sm" role="status">
            {{ message }}
          </div>
          <div v-if="errorMessage" class="alert alert-error alert-soft text-sm" role="alert">
            {{ errorMessage }}
          </div>

          <button class="btn btn-primary" type="submit" :disabled="isSubmitting">
            <span v-if="isSubmitting" class="loading loading-spinner loading-xs" />
            {{ $t('auth.create') }}
          </button>
        </div>
      </form>
    </main>
  </div>
</template>