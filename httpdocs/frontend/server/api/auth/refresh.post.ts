import { authCookieName, authCookieOptions, refreshCookieName, upstreamError } from '../../utils/auth'

export default defineEventHandler(async (event) => {
  const refreshToken = getCookie(event, refreshCookieName)
  const config = useRuntimeConfig(event)

  if (!refreshToken) throw createError({ statusCode: 401, statusMessage: 'Unauthenticated.' })

  try {
    const response = await $fetch<{ access_token?: string; refresh_token?: string }>(`${config.apiInternalOrigin}/oauth/token`, {
      method: 'POST',
      headers: { 'content-type': 'application/x-www-form-urlencoded', accept: 'application/json' },
      body: new URLSearchParams({
        grant_type: 'refresh_token',
        client_id: String(config.public.passportClientId),
        refresh_token: refreshToken,
      }),
    })

    if (!response.access_token) throw createError({ statusCode: 401, statusMessage: 'Unauthenticated.' })
    const options = authCookieOptions(process.env.NODE_ENV === 'production')
    setCookie(event, authCookieName, response.access_token, options)
    if (response.refresh_token) setCookie(event, refreshCookieName, response.refresh_token, options)
    return { authenticated: true }
  } catch (error) {
    deleteCookie(event, authCookieName, { path: '/' })
    deleteCookie(event, refreshCookieName, { path: '/' })
    if (isError(error) && error.statusCode >= 400 && error.statusCode < 500) throw error
    throw upstreamError(error)
  }
})
