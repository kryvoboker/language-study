import type { TranslationRequestDto } from '#shared/types/translation'

export const useTranslationWorkbench = () => {
  const config = useRuntimeConfig()
  const sourceText = ref('')
  const sourceLanguage = ref('en')
  const targetLanguage = ref('uk')
  const current = ref<TranslationRequestDto | null>(null)
  const isBusy = computed(() => current.value?.status === 'queued' || current.value?.status === 'processing')
  const errorMessage = ref<string | null>(null)

  let debounceTimer: ReturnType<typeof setTimeout> | undefined
  let pollTimer: ReturnType<typeof setTimeout> | undefined
  let sequence = 0

  const authHeaders = (): HeadersInit => {
    const token = useCookie<string | null>('nl_access_token').value
    return token ? { Authorization: `Bearer ${token}` } : {}
  }

  const cancelCurrent = async () => {
    sequence += 1
    if (debounceTimer) clearTimeout(debounceTimer)
    if (pollTimer) clearTimeout(pollTimer)
    const id = current.value?.id
    if (!id || current.value?.status === 'completed' || current.value?.status === 'failed' || current.value?.status === 'cancelled') return
    current.value = { ...current.value, status: 'cancelled' }
    await $fetch(`${config.public.apiBase}/translation-requests/${id}`, { method: 'DELETE', headers: authHeaders() }).catch(() => undefined)
  }

  const poll = async (id: string, runSequence: number) => {
    if (runSequence !== sequence) return
    const result = await $fetch<TranslationRequestDto>(`${config.public.apiBase}/translation-requests/${id}`, { headers: authHeaders() })
    if (runSequence !== sequence) return
    current.value = result
    if (result.status === 'queued' || result.status === 'processing') {
      pollTimer = setTimeout(() => void poll(id, runSequence), 350)
    }
  }

  const translate = async () => {
    const text = sourceText.value.trim()
    if (!text) { current.value = null; return }
    errorMessage.value = null
    const runSequence = ++sequence
    try {
      const result = await $fetch<TranslationRequestDto>(`${config.public.apiBase}/translation-requests`, {
        method: 'POST', headers: authHeaders(), body: { source_text: text, source_language: sourceLanguage.value, target_language: targetLanguage.value },
      })
      if (runSequence !== sequence) return
      current.value = result
      await poll(result.id, runSequence)
    } catch {
      if (runSequence === sequence) errorMessage.value = 'Translation request failed.'
    }
  }

  const schedule = async () => {
    await cancelCurrent()
    if (!sourceText.value.trim()) return
    debounceTimer = setTimeout(() => void translate(), 650)
  }

  return { sourceText, sourceLanguage, targetLanguage, current, isBusy, errorMessage, schedule, cancelCurrent, translate }
}
