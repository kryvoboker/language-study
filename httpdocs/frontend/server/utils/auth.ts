import { createHash, randomBytes } from 'node:crypto'
import type { H3Event } from 'h3'

export const authCookieName = 'nl_auth'
export const xdebugSessionCookieName = 'XDEBUG_SESSION'
export const pkceStateCookieName = 'nl_pkce_state'
export const pkceVerifierCookieName = 'nl_pkce_verifier'
export const refreshCookieName = 'nl_refresh'

export const authCookieOptions = (secure: boolean) => ({
  httpOnly: true,
  sameSite: 'lax' as const,
  secure,
  path: '/',
  maxAge: 60 * 60 * 24 * 30,
})

export const randomUrlSafe = (bytes = 32) => randomBytes(bytes).toString('base64url')

export const pkceChallenge = (verifier: string) => createHash('sha256').update(verifier).digest('base64url')

export const xdebugSessionCookieHeader = (event: H3Event): string | undefined => {
  if (process.env.NODE_ENV === 'production') return undefined

  const value = getCookie(event, xdebugSessionCookieName)

  return value ? `${xdebugSessionCookieName}=${encodeURIComponent(value)}` : undefined
}

export const forwardedCookieHeader = (event: H3Event): string | undefined => {
  const cookie = getHeader(event, 'cookie')

  if (process.env.NODE_ENV === 'production') {
    const safeCookies = cookie
      ?.split(';')
      .map((part) => part.trim())
      .filter((part) => !part.startsWith(`${xdebugSessionCookieName}=`))

    return safeCookies?.length ? safeCookies.join('; ') : undefined
  }

  const xdebugCookie = xdebugSessionCookieHeader(event)

  if (!xdebugCookie || cookie?.includes(`${xdebugSessionCookieName}=`)) return cookie

  return [cookie, xdebugCookie].filter(Boolean).join('; ')
}

export const upstreamError = (error: unknown) => {
  const statusCode = (error as { response?: { status?: number } })?.response?.status ?? 502
  return createError({ statusCode, statusMessage: 'Authentication service unavailable.' })
}
