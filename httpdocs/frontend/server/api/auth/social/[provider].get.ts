export default defineEventHandler((event) => {
  const provider = getRouterParam(event, 'provider')
  const config = useRuntimeConfig(event)

  if (!provider || !['google', 'github', 'facebook'].includes(provider)) {
    throw createError({ statusCode: 404, statusMessage: 'Unknown authentication provider.' })
  }

  return sendRedirect(event, `${config.public.backendOrigin}/auth/social/${provider}`)
})
