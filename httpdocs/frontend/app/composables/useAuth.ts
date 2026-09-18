export interface AuthUser {
  id: string
  name: string
  email: string
  max_input_characters: number
}

export const useAuth = () => {
  const user = useState<AuthUser | null>('auth-user', () => null)
  const initialized = useState('auth-initialized', () => false)
  const pending = ref(false)

  const refresh = async () => {
    if (pending.value) return user.value

    pending.value = true
    try {
      user.value = await $fetch<AuthUser>('/api/v1/me')
    } catch (error) {
      if ((error as { statusCode?: number }).statusCode !== 401) throw error
      user.value = null
    } finally {
      initialized.value = true
      pending.value = false
    }

    return user.value
  }

  const logout = async () => {
    await $fetch('/api/auth/logout', { method: 'POST' })
    user.value = null
    initialized.value = true
  }

  return { user, initialized, pending, refresh, logout }
}
