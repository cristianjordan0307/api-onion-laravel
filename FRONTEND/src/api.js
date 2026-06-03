import axios from 'axios'
import { useAuthStore } from './store'

// ── Cliente Axios ─────────────────────────────────────────────────────────────
// Base URL apunta a la variable de entorno o usa '/api' (proxy de desarrollo local).
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
})

// Interceptor de request: agrega el token JWT en cada petición protegida
api.interceptors.request.use((config) => {
  const token = useAuthStore.getState().token
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// Interceptor de response: maneja el 401 global (token expirado)
api.interceptors.response.use(
  (res) => res,
  (error) => {
    if (error.response?.status === 401) {
      useAuthStore.getState().logout()
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api

// ── Helpers para extraer errores de validación del API ────────────────────────
export const getApiError = (error) => {
  const data = error.response?.data
  if (data?.mensaje && data?.errores) {
    return data.errores.map((e) => `${e.campo}: ${e.detalle}`).join('\n')
  }
  return data?.error || data?.message || 'Error inesperado. Intenta de nuevo.'
}
