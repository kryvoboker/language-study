import { authCookieName, refreshCookieName } from '../../utils/auth'

export default defineEventHandler((event) => {
  deleteCookie(event, authCookieName, { path: '/' })
  deleteCookie(event, refreshCookieName, { path: '/' })
  return { authenticated: false }
})
