import type { TranslationRequestDto } from '#shared/types/translation'

export const useTranslationWorkbench = () => {
  const { t, locale } = useI18n()
  const { user } = useAuth()
  const sourceText = ref('')
  const sourceLanguage = useCookie('nativelens.source-language', {
    default: () => 'en',
    maxAge: 60 * 60 * 24 * 365,
    path: '/',
    sameSite: 'lax',
  })
  const targetLanguage = useCookie('nativelens.target-language', {
    default: () => 'uk',
    maxAge: 60 * 60 * 24 * 365,
    path: '/',
    sameSite: 'lax',
  })
  const current = ref<TranslationRequestDto | null>(null)
  const isBusy = computed(() => current.value?.status === 'queued' || current.value?.status === 'processing')
  const errorMessage = ref<string | null>(null)
  const accessError = ref<'loginRequired' | 'accountBlocked' | null>(null)
  const inputCharacterLimit = computed(() => user.value?.max_input_characters ?? 12000)

  const setLimitError = () => {
    errorMessage.value = t('translator.limitError', { limit: inputCharacterLimit.value })
  }

  let debounceTimer: ReturnType<typeof setTimeout> | undefined
  let pollTimer: ReturnType<typeof setTimeout> | undefined
  let sequence = 0

  const requestWithAuthRecovery = async <T>(
    url: string,
    options?: Parameters<typeof $fetch<T>>[1],
  ): Promise<T> => {
    const requestUrl: string = url

    try {
      const response: unknown = await $fetch<T>(requestUrl, options)
      return response as T
    } catch (error) {
      if ((error as { statusCode?: number }).statusCode !== 401) throw error

      try {
        await $fetch('/api/auth/refresh', { method: 'POST' })
        const response: unknown = await $fetch<T>(requestUrl, options)
        return response as T
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
    if (text.length > inputCharacterLimit.value) {
      current.value = null
      setLimitError()
      return
    }
    errorMessage.value = null
    accessError.value = null
    const runSequence = ++sequence
    try {
      const result = await requestWithAuthRecovery<TranslationRequestDto>('/api/v1/translation-requests', {
        method: 'POST',
        body: {
          source_text: text,
          source_language: sourceLanguage.value,
          target_language: targetLanguage.value,
          locale: locale.value,
        },
      })
      if (runSequence !== sequence) return
      current.value = result
      await poll(result.id, runSequence)
    } catch (error) {
      if (runSequence !== sequence) return
      const requestError = error as {
        statusCode?: number
        data?: {
          code?: string
          errors?: { source_text?: unknown[] }
          data?: { code?: string; errors?: { source_text?: unknown[] } }
        }
      }
      const errorCode = requestError.data?.code ?? requestError.data?.data?.code
      const validationErrors = requestError.data?.errors ?? requestError.data?.data?.errors

      if (requestError.statusCode === 422 && validationErrors?.source_text) {
        setLimitError()
        return
      }

      if (errorCode === 'account_blocked') {
        accessError.value = 'accountBlocked'
        errorMessage.value = t('translator.accountBlocked')
      } else if (requestError.statusCode === 401 || errorCode === 'login_required') {
        accessError.value = 'loginRequired'
        errorMessage.value = t('translator.loginRequired')
      } else {
        errorMessage.value = 'Translation request failed.'
      }
    }
  }

  const schedule = async () => {
    await cancelCurrent()
    if (!sourceText.value.trim()) {
      errorMessage.value = null
      return
    }
    if (sourceText.value.trim().length > inputCharacterLimit.value) {
      setLimitError()
      return
    }
    errorMessage.value = null
    accessError.value = null
    debounceTimer = setTimeout(() => void translate(), 1000)
  }

  return {
    sourceText,
    sourceLanguage,
    targetLanguage,
    current,
    isBusy,
    errorMessage,
    accessError,
    inputCharacterLimit,
    schedule,
    cancelCurrent,
    translate,
  }
}