export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.hook('page:finish', async () => {
    await import('flyonui/flyonui')
    window.HSStaticMethods?.autoInit()
  })
})

declare global {
  interface Window {
    HSStaticMethods?: { autoInit: () => void }
  }
}
