import { createHash, randomBytes } from 'node:crypto'

export const authCookieName = 'nl_auth'
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

export const upstreamError = (error: unknown) => {
  const statusCode = (error as { response?: { status?: number } })?.response?.status ?? 502
  return createError({ statusCode, statusMessage: 'Authentication service unavailable.' })
}
