import { useEffect } from 'react'
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { useAuthStore, useThemeStore } from './store'
import LoginPage    from './pages/LoginPage'
import DashboardPage from './pages/DashboardPage'
import CompaniasPage from './pages/CompaniasPage'
import EmpleadosPage from './pages/EmpleadosPage'
import PerfilPage    from './pages/PerfilPage'
import Layout        from './components/Layout'
import './index.css'

// Ruta protegida: redirige a /login si no hay sesión activa
function PrivateRoute({ children }) {
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated)
  return isAuthenticated() ? children : <Navigate to="/login" replace />
}

export default function App() {
  const applyTheme = useThemeStore((s) => s.applyTheme)

  // Aplica el tema guardado al montar la app
  useEffect(() => { applyTheme() }, [applyTheme])

  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<LoginPage />} />
        <Route
          path="/"
          element={
            <PrivateRoute>
              <Layout />
            </PrivateRoute>
          }
        >
          <Route index element={<Navigate to="/dashboard" replace />} />
          <Route path="dashboard"  element={<DashboardPage />} />
          <Route path="companias"  element={<CompaniasPage />} />
          <Route path="empleados"  element={<EmpleadosPage />} />
          <Route path="perfil"     element={<PerfilPage />} />
        </Route>
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  )
}
