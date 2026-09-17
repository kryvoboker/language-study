import { authCookieOptions, pkceChallenge, pkceStateCookieName, pkceVerifierCookieName, randomUrlSafe } from '../../../utils/auth'

export default defineEventHandler((event) => {
  const config = useRuntimeConfig(event)
  const state = randomUrlSafe(32)
  const verifier = randomUrlSafe(48)
  const options = { ...authCookieOptions(process.env.NODE_ENV === 'production'), maxAge: 600 }

  setCookie(event, pkceStateCookieName, state, options)
  setCookie(event, pkceVerifierCookieName, verifier, options)

  const query = new URLSearchParams({
    client_id: String(config.public.passportClientId),
    redirect_uri: String(config.public.passportRedirectUri),
    response_type: 'code',
    scope: 'profile',
    state,
    code_challenge: pkceChallenge(verifier),
    code_challenge_method: 'S256',
  })

  return sendRedirect(event, `${config.public.backendOrigin}/oauth/authorize?${query.toString()}`)
})
