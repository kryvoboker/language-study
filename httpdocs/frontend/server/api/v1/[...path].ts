import { authCookieName, upstreamError } from '../../utils/auth'

export default defineEventHandler(async (event): Promise<unknown> => {
  const config = useRuntimeConfig(event)
  const path = getRouterParam(event, 'path') ?? ''
  const token = getCookie(event, authCookieName)
  const headers: HeadersInit = {
    Accept: 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
  }
  const method = event.method
  const options: Parameters<typeof $fetch>[1] = { method, headers }

  if (!['GET', 'HEAD'].includes(method)) options.body = await readBody(event)

  try {
    const upstreamUrl = `${config.apiInternalBase}/${path}`
    return await $fetch<unknown>(upstreamUrl, options)
  } catch (error) {
    const statusCode = (error as { statusCode?: number; response?: { status?: number } }).statusCode
      ?? (error as { response?: { status?: number } }).response?.status

    if (statusCode !== undefined && statusCode >= 400 && statusCode < 500) {
      throw createError({
        statusCode,
        statusMessage: statusCode === 401 ? 'Unauthenticated.' : 'Upstream request rejected.',
      })
    }

    throw upstreamError(error)
  }
})
