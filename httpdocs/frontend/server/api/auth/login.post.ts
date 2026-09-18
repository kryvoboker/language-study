import { authCookieName, authCookieOptions, upstreamError, xdebugSessionCookieHeader } from '../../utils/auth'

export default defineEventHandler(async (event) => {
  const body = await readBody<{ email?: string; password?: string }>(event)
  const config = useRuntimeConfig(event)
  const xdebugCookie = xdebugSessionCookieHeader(event)

  try {
    const response = await $fetch<{ access_token?: string }>(`${config.apiInternalBase}/auth/login`, {
      method: 'POST',
      body,
      ...(xdebugCookie ? { headers: { Cookie: xdebugCookie } } : {}),
    })

    if (!response.access_token) {
      throw createError({ statusCode: 502, statusMessage: 'Authentication service returned an invalid response.' })
    }

    setCookie(event, authCookieName, response.access_token, authCookieOptions(process.env.NODE_ENV === 'production'))
    return { authenticated: true }
  } catch (error) {
    if (isError(error) && error.statusCode >= 400 && error.statusCode < 500) throw error
    throw upstreamError(error)
  }
})
