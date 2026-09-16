export type StorefrontTheme = 'light' | 'black'

const THEME_STORAGE_KEY = 'nativelens.storefront.theme'

function getCurrentTheme(): StorefrontTheme {
  const explicitTheme = document.documentElement.dataset.theme

  if (explicitTheme === 'light' || explicitTheme === 'black') {
    return explicitTheme
  }

  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'black' : 'light'
}

export function useStorefrontTheme() {
  const toggleTheme = () => {
    if (!import.meta.client) {
      return
    }

    const nextTheme: StorefrontTheme = getCurrentTheme() === 'black' ? 'light' : 'black'

    document.documentElement.dataset.theme = nextTheme

    try {
      localStorage.setItem(THEME_STORAGE_KEY, nextTheme)
    } catch {
      // The selected theme still applies for the current page when storage is unavailable.
    }
  }

  return {
    toggleTheme,
  }
}
