<script setup lang="ts">
const { t }                          = useI18n()
const { user, initialized, refresh } = useAuth()
const firstName                      = ref('')
const lastName                       = ref('')
const email                          = ref('')
const message                        = ref('')
const images                         = ref<File[]>([])
const submitting                     = ref(false)
const result                         = ref<'accepted' | 'failed' | 'invalid' | 'fileCount' | 'fileSize' | 'fileType' | null>(null)
const validationErrors               = ref<string[]>([])
const maxFiles                       = 10
const maxFileBytes                   = 5 * 1024 * 1024

onMounted(async () => {
    if (!initialized.value) {
        try {
            await refresh()
        } catch {
            // The contact form remains available if the optional session lookup fails.
        }
    }
})

const selectImages = (event: Event) => {
    result.value           = null
    validationErrors.value = []
    const input            = event.target as HTMLInputElement
    const selected         = Array.from(input.files ?? [])
    input.value            = ''

    if (images.value.length + selected.length > maxFiles) {
        result.value = 'fileCount'
        return
    }

    if (selected.some((file) => file.size > maxFileBytes)) {
        result.value = 'fileSize'
        return
    }

    if (selected.some((file) => !['image/jpeg', 'image/png'].includes(file.type))) {
        result.value = 'fileType'
        return
    }

    images.value = [... images.value, ... selected]
}

const removeImage = (index: number) => {
    images.value = images.value.filter((_, imageIndex) => imageIndex !== index)
}

const submit = async () => {
    result.value           = null
    validationErrors.value = []

    if (images.value.length > maxFiles) {
        result.value = 'invalid'
        return
    }

    const body = new FormData()
    body.append('message', message.value)

    if (!user.value) {
        body.append('first_name', firstName.value)
        body.append('last_name', lastName.value)
        body.append('email', email.value)
    }

    for (const image of images.value) {
        body.append('images[]', image, image.name)
    }

    submitting.value = true

    try {
        await $fetch('/api/v1/contact-requests', { method: 'POST', body })
        result.value    = 'accepted'
        firstName.value = ''
        lastName.value  = ''
        email.value     = ''
        message.value   = ''
        images.value    = []
    } catch (error) {
        const requestError = error as {
            statusCode?: number
            data?: {
                errors?: Record<string, unknown>
            }
        }
        const statusCode   = requestError.statusCode

        if (requestError.data?.errors) {
            validationErrors.value = Object.keys(requestError.data.errors)
        }

        result.value = statusCode === 422 ? 'invalid' : 'failed'
    } finally {
        submitting.value = false
    }
}
</script>

<template>
    <section class="contact-section">
        <div class="container">
            <div class="card max-w-3xl mx-auto">
                <div class="card-body">
                    <div class="flex flex-col gap-2">
                        <h1 class="card-title text-3xl">{{ t('contact.title') }}</h1>
                        <p class="text-base-content/70">{{ t('contact.intro') }}</p>
                    </div>

                    <div v-if="user" class="flex flex-col gap-3">
                        <p class="alert alert-info alert-soft text-sm">
                            {{ t('contact.signedInAs', { name: user.name, email: user.email }) }}
                        </p>
                        <p v-if="user.is_blocked" class="alert alert-warning alert-soft text-sm">
                            {{ t('contact.blockedNotice') }}
                        </p>
                    </div>

                    <form class="flex flex-col gap-5" @submit.prevent="submit">
                        <div v-if="!user" class="grid gap-4 sm:grid-cols-2">
                            <label class="flex flex-col gap-2">
                                <span class="label-text">{{ t('contact.firstName') }}</span>
                                <input v-model="firstName" class="input" name="first_name" autocomplete="given-name" maxlength="120" required>
                                <span v-if="validationErrors.includes('first_name')" class="text-sm text-error">{{ t('contact.invalidFirstName') }}</span>
                            </label>
                            <label class="flex flex-col gap-2">
                                <span class="label-text">{{ t('contact.lastName') }}</span>
                                <input v-model="lastName" class="input" name="last_name" autocomplete="family-name" maxlength="120" required>
                                <span v-if="validationErrors.includes('last_name')" class="text-sm text-error">{{ t('contact.invalidLastName') }}</span>
                            </label>
                            <label class="flex flex-col gap-2 sm:col-span-2">
                                <span class="label-text">{{ t('contact.email') }}</span>
                                <input v-model="email" class="input" name="email" type="email" autocomplete="email" maxlength="255" required>
                                <span v-if="validationErrors.includes('email')" class="text-sm text-error">{{ t('contact.invalidEmail') }}</span>
                            </label>
                        </div>

                        <label class="flex flex-col gap-2">
                            <span class="label-text">{{ t('contact.message') }}</span>
                            <textarea
                                v-model="message"
                                class="textarea min-h-40"
                                name="message"
                                maxlength="10000"
                                required
                                :placeholder="t('contact.messagePlaceholder')"
                            />
                            <span v-if="validationErrors.includes('message')" class="text-sm text-error">{{ t('contact.invalidMessage') }}</span>
                        </label>

                        <label class="flex flex-col gap-2">
                            <span class="label-text">{{ t('contact.images') }}</span>
                            <input
                                class="file-input input"
                                type="file"
                                accept="image/jpeg,image/png"
                                multiple
                                :disabled="images.length >= maxFiles"
                                @change="selectImages"
                            >
                            <span class="text-sm text-base-content/60">{{ t('contact.imageHelp') }}</span>
                        </label>

                        <ul v-if="images.length" class="flex flex-col gap-2" :aria-label="t('contact.selectedImages')">
                            <li v-for="(image, index) in images" :key="`${image.name}-${image.lastModified}-${index}`" class="flex items-center justify-between gap-3 rounded-lg border border-base-content/10 px-3 py-2 text-sm">
                                <span class="truncate">{{ image.name }}</span>
                                <button type="button" class="btn btn-text btn-xs" :aria-label="t('contact.removeImage', { name: image.name })" @click="removeImage(index)">
                                    <span class="icon-[tabler--x] size-4"/>
                                </button>
                            </li>
                        </ul>

                        <p v-if="result === 'accepted'" class="alert alert-success alert-soft" role="status">{{ t('contact.accepted') }}</p>
                        <p v-else-if="result === 'fileCount'" class="alert alert-warning alert-soft" role="alert">{{ t('contact.fileCountError') }}</p>
                        <p v-else-if="result === 'fileSize'" class="alert alert-warning alert-soft" role="alert">{{ t('contact.fileSizeError') }}</p>
                        <p v-else-if="result === 'fileType'" class="alert alert-warning alert-soft" role="alert">{{ t('contact.fileTypeError') }}</p>
                        <p v-else-if="result === 'invalid' || validationErrors.some((field) => field.startsWith('images'))" class="alert alert-warning alert-soft" role="alert">{{ t('contact.invalid') }}</p>
                        <p v-else-if="result === 'failed'" class="alert alert-error alert-soft" role="alert">{{ t('contact.failed') }}</p>

                        <button class="btn btn-primary self-start" type="submit" :disabled="submitting || !initialized">
                            <span v-if="submitting" class="loading loading-spinner loading-xs"/>
                            {{ submitting ? t('contact.sending') : t('contact.submit') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>