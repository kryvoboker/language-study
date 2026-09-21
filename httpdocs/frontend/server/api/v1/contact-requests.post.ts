import { authCookieName, forwardedCookieHeader, upstreamError } from '../../utils/auth'

export default defineEventHandler(async (event): Promise<unknown> => {
  const config = useRuntimeConfig(event)
  const token = getCookie(event, authCookieName)
  const cookie = forwardedCookieHeader(event)
  const xsrfToken = getHeader(event, 'x-xsrf-token') ?? getCookie(event, 'XSRF-TOKEN')
  const parts = await readMultipartFormData(event)

  if (!parts) {
    throw createError({ statusCode: 400, statusMessage: 'A multipart contact request is required.' })
  }

  const body = new FormData()

  for (const part of parts) {
    if (!part.name) continue

    if (part.filename) {
      body.append(part.name, new Blob([new Uint8Array(part.data)], { type: part.type ?? 'application/octet-stream' }), part.filename)
      continue
    }

    body.append(part.name, part.data.toString('utf8'))
  }

  const headers: HeadersInit = {
    Accept: 'application/json',
    ...(cookie ? { Cookie: cookie } : {}),
    ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
  }

  try {
    return await $fetch<unknown>(`${config.apiInternalBase}/contact-requests`, {
      method: 'POST',
      headers,
      body,
    })
  } catch (error) {
    const upstreamErrorData = error as {
      statusCode?: number
      response?: { status?: number }
      data?: { errors?: unknown }
    }
    const statusCode = upstreamErrorData.statusCode ?? upstreamErrorData.response?.status

    if (statusCode !== undefined && statusCode >= 400 && statusCode < 500) {
      const fieldErrors = upstreamErrorData.data?.errors
      const safeErrors: Record<string, string[]> = {}

      if (fieldErrors && typeof fieldErrors === 'object') {
        for (const [field, messages] of Object.entries(fieldErrors as Record<string, unknown>)) {
          if (
            ['first_name', 'last_name', 'email', 'message', 'images'].includes(field)
            || /^images\.\d+$/.test(field)
          ) {
            if (Array.isArray(messages)) safeErrors[field] = ['invalid']
          }
        }
      }

      throw createError({
        statusCode,
        statusMessage: statusCode === 429 ? 'Too many contact requests.' : 'Contact request was rejected.',
        data: Object.keys(safeErrors).length > 0 ? { errors: safeErrors } : undefined,
      })
    }

    throw upstreamError(error)
  }
})
