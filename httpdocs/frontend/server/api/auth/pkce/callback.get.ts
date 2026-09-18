import { authCookieName, authCookieOptions, pkceStateCookieName, pkceVerifierCookieName, refreshCookieName, upstreamError, xdebugSessionCookieHeader } from '../../../utils/auth'

export default defineEventHandler(async (event) => {
  const query = getQuery(event)
  const state = getCookie(event, pkceStateCookieName)
  const verifier = getCookie(event, pkceVerifierCookieName)
  const config = useRuntimeConfig(event)
  const xdebugCookie = xdebugSessionCookieHeader(event)

  if (!state || !verifier || typeof query.state !== 'string' || query.state !== state || typeof query.code !== 'string') {
    throw createError({ statusCode: 400, statusMessage: 'Invalid authorization response.' })
  }

  try {
    const response = await $fetch<{ access_token?: string; refresh_token?: string }>(`${config.apiInternalOrigin}/oauth/token`, {
      method: 'POST',
      headers: {
        'content-type': 'application/x-www-form-urlencoded',
        accept: 'application/json',
        ...(xdebugCookie ? { Cookie: xdebugCookie } : {}),
      },
      body: new URLSearchParams({
        grant_type: 'authorization_code',
        client_id: String(config.public.passportClientId),
        redirect_uri: String(config.public.passportRedirectUri),
        code: query.code,
        code_verifier: verifier,
      }),
    })

    if (!response.access_token) throw createError({ statusCode: 502, statusMessage: 'Authentication service returned an invalid response.' })
    const options = authCookieOptions(process.env.NODE_ENV === 'production')
    setCookie(event, authCookieName, response.access_token, options)
    if (response.refresh_token) setCookie(event, refreshCookieName, response.refresh_token, options)
    deleteCookie(event, pkceStateCookieName, { path: '/' })
    deleteCookie(event, pkceVerifierCookieName, { path: '/' })
    return { authenticated: true }
  } catch (error) {
    if (isError(error) && error.statusCode >= 400 && error.statusCode < 500) throw error
    throw upstreamError(error)
  }
})
