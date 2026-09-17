import type { TranslationRequestDto } from '#shared/types/translation'

export const useTranslationWorkbench = () => {
  const sourceText = ref('')
  const sourceLanguage = ref('en')
  const targetLanguage = ref('uk')
  const current = ref<TranslationRequestDto | null>(null)
  const isBusy = computed(() => current.value?.status === 'queued' || current.value?.status === 'processing')
  const errorMessage = ref<string | null>(null)

  let debounceTimer: ReturnType<typeof setTimeout> | undefined
  let pollTimer: ReturnType<typeof setTimeout> | undefined
  let sequence = 0

  const requestWithAuthRecovery = async <T>(
    url: string,
    options?: Parameters<typeof $fetch<T>>[1],
  ): Promise<T> => {
    const requestUrl: string = url

    try {
      return await $fetch<T>(requestUrl, options) as T
    } catch (error) {
      if ((error as { statusCode?: number }).statusCode !== 401) throw error

      try {
        await $fetch('/api/auth/refresh', { method: 'POST' })
        return await $fetch<T>(requestUrl, options) as T
      } catch {
        throw error
      }
    }
  }

  const cancelCurrent = async () => {
    sequence += 1
    if (debounceTimer) clearTimeout(debounceTimer)
    if (pollTimer) clearTimeout(pollTimer)
    const currentRequest = current.value
    const id = currentRequest?.id
    if (
      !id ||
      currentRequest.status === 'completed' ||
      currentRequest.status === 'failed' ||
      currentRequest.status === 'cancelled'
    )
      return
    current.value = { ...currentRequest, status: 'cancelled' }
    await $fetch(`/api/v1/translation-requests/${id}`, { method: 'DELETE' }).catch(() => undefined)
  }

  const poll = async (id: string, runSequence: number) => {
    if (runSequence !== sequence) return
    const result = await requestWithAuthRecovery<TranslationRequestDto>(`/api/v1/translation-requests/${id}`)
    if (runSequence !== sequence) return
    current.value = result
    if (result.status === 'queued' || result.status === 'processing') {
      pollTimer = setTimeout(() => void poll(id, runSequence), 350)
    }
  }

  const translate = async () => {
    const text = sourceText.value.trim()
    if (!text) {
      current.value = null
      return
    }
    errorMessage.value = null
    const runSequence = ++sequence
    try {
      const result = await requestWithAuthRecovery<TranslationRequestDto>('/api/v1/translation-requests', {
        method: 'POST',
        body: {
          source_text: text,
          source_language: sourceLanguage.value,
          target_language: targetLanguage.value,
        },
      })
      if (runSequence !== sequence) return
      current.value = result
      await poll(result.id, runSequence)
    } catch (error) {
      if (runSequence !== sequence) return
      errorMessage.value =
        (error as { statusCode?: number }).statusCode === 401
          ? 'Your session has expired. Please sign in again.'
          : 'Translation request failed.'
    }
  }

  const schedule = async () => {
    await cancelCurrent()
    if (!sourceText.value.trim()) return
    debounceTimer = setTimeout(() => void translate(), 650)
  }

  return {
    sourceText,
    sourceLanguage,
    targetLanguage,
    current,
    isBusy,
    errorMessage,
    schedule,
    cancelCurrent,
    translate,
  }
}
