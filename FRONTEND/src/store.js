import { create } from 'zustand'
import { persist } from 'zustand/middleware'

// ── Auth Store ────────────────────────────────────────────────────────────────
// Persiste el token y el usuario en localStorage para mantener la sesión
// entre recargas de página.
export const useAuthStore = create(
  persist(
    (set, get) => ({
      token: null,
      user: null,
      permissions: [],

      login: (token, user) => {
        let permissions = []
        try {
          const base64Url = token.split('.')[1]
          const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/')
          const payload = JSON.parse(window.atob(base64))
          permissions = payload.permissions || []
        } catch (e) {
          console.error('Error decodificando el token:', e)
        }
        set({ token, user, permissions })
      },

      logout: () => set({ token: null, user: null, permissions: [] }),

      isAuthenticated: () => !!get().token,

      // Verifica si el usuario tiene un permiso específico (del claim JWT)
      can: (permission) => get().permissions.includes(permission),

      // Verifica si el usuario tiene uno de los roles indicados
      hasRole: (...roles) => roles.includes(get().user?.role),
    }),
    { name: 'auth-store' }
  )
)

// ── Theme Store ───────────────────────────────────────────────────────────────
// Persiste el tema (light / dark) en localStorage y lo aplica al DOM.
export const useThemeStore = create(
  persist(
    (set, get) => ({
      theme: 'light',

      toggleTheme: () => {
        const next = get().theme === 'light' ? 'dark' : 'light'
        document.documentElement.setAttribute('data-theme', next)
        set({ theme: next })
      },

      applyTheme: () => {
        document.documentElement.setAttribute('data-theme', get().theme)
      },
    }),
    { name: 'theme-store' }
  )
)
