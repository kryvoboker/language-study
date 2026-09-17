import { authCookieName, upstreamError } from '../../utils/auth'

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig(event)
  const path = getRouterParam(event, 'path') ?? ''
  const token = getCookie(event, authCookieName)
  const headers: HeadersInit = token ? { Authorization: `Bearer ${token}` } : {}
  const method = event.method
  const options: Parameters<typeof $fetch>[1] = { method, headers }

  if (!['GET', 'HEAD'].includes(method)) options.body = await readBody(event)

  try {
    return await $fetch(`${config.apiInternalBase}/${path}`, options)
  } catch (error) {
    if (isError(error) && error.statusCode >= 400 && error.statusCode < 500) throw error
    throw upstreamError(error)
  }
})
