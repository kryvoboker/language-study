import { authCookieName, forwardedCookieHeader, upstreamError } from '../../utils/auth'

export default defineEventHandler(async (event): Promise<unknown> => {
  const config = useRuntimeConfig(event)
  const path = getRouterParam(event, 'path') ?? ''
  const token = getCookie(event, authCookieName)
  const cookie = forwardedCookieHeader(event)
  const xsrfToken = getHeader(event, 'x-xsrf-token')
  const headers: HeadersInit = {
    Accept: 'application/json',
    ...(cookie ? { Cookie: cookie } : {}),
    ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
  }
  const method = event.method
  const options: Parameters<typeof $fetch>[1] = { method, headers }

  if (!['GET', 'HEAD'].includes(method)) options.body = await readBody(event)

  try {
    const upstreamUrl = `${config.apiInternalBase}/${path}`
    return await $fetch<unknown>(upstreamUrl, options)
  } catch (error) {
    const upstreamResponseError = error as {
      statusCode?: number
      response?: { status?: number }
      data?: { code?: unknown; errors?: unknown }
    }
    const statusCode = upstreamResponseError.statusCode
      ?? (error as { response?: { status?: number } }).response?.status

    if (statusCode !== undefined && statusCode >= 400 && statusCode < 500) {
      const code = ['login_required', 'account_blocked'].includes(String(upstreamResponseError.data?.code))
        ? String(upstreamResponseError.data?.code)
        : undefined
      const data: Record<string, unknown> = {}

      if (code) {
        data.code = code
      }

      if (
        statusCode === 422 &&
        upstreamResponseError.data?.errors !== null &&
        typeof upstreamResponseError.data?.errors === 'object' &&
        Array.isArray((upstreamResponseError.data.errors as Record<string, unknown>).source_text)
      ) {
        data.errors = { source_text: ['invalid'] }
      }

      throw createError({
        statusCode,
        statusMessage: statusCode === 401 ? 'Unauthenticated.' : 'Upstream request rejected.',
        data,
      })
    }

    throw upstreamError(error)
  }
})
