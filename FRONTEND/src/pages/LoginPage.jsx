import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Layers, Eye, EyeOff } from 'lucide-react'
import api, { getApiError } from '../api'
import { useAuthStore, useThemeStore } from '../store'

export default function LoginPage() {
  const navigate = useNavigate()
  const login = useAuthStore((s) => s.login)
  const { theme, toggleTheme } = useThemeStore()

  const [form, setForm] = useState({ email: '', password: '' })
  const [showPwd, setShowPwd] = useState(false)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  
  // Estado para controlar el popover flotante de demo accounts (Opción B)
  const [showQuickLogin, setShowQuickLogin] = useState(false)

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value })

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      const { data } = await api.post('/auth/login', form)
      login(data.access_token, data.usuario)
      navigate('/dashboard')
    } catch (err) {
      setError(getApiError(err))
    } finally {
      setLoading(false)
    }
  }

  const handleTestLogin = (email, password) => {
    setForm({ email, password })
    setError('')
  }

  return (
    <div className="login-page">
      {/* Botón flotante para cambiar tema */}
      <div className="theme-toggle-floating">
        <button
          className="btn-icon"
          onClick={toggleTheme}
          title="Cambiar tema"
          style={{
            borderRadius: '50%',
            width: '42px',
            height: '42px',
            background: 'var(--bg-card)',
            borderColor: 'var(--border-strong)',
            color: 'var(--text)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center'
          }}
        >
          {theme === 'light' ? <span>🌙</span> : <span>☀️</span>}
        </button>
      </div>

      <div className="login-card-wrapper animated-slideUp">
        <div className="login-card">
          {/* Logo */}
          <div className="login-logo">
            <Layers size={26} />
            <span>Onion Admin</span>
          </div>

          <div>
            <h2 style={{ fontSize: '1.4rem', fontWeight: 800, marginBottom: 6, color: 'var(--login-text)' }}>
              Iniciar sesión
            </h2>
            <p style={{ fontSize: '0.85rem', color: 'var(--login-text-muted)' }}>
              Ingresa tus credenciales para continuar al panel administrativo.
            </p>
          </div>

          {error && (
            <div className="alert alert-danger animated-fadeIn" style={{ whiteSpace: 'pre-line' }}>
              {error}
            </div>
          )}

          <form className="login-form" onSubmit={handleSubmit}>
            <div className="form-group">
              <label className="form-label" htmlFor="email" style={{ color: 'var(--login-text)' }}>
                Correo electrónico
              </label>
              <input
                id="email"
                name="email"
                type="email"
                className="form-input"
                placeholder="admin@api.com"
                value={form.email}
                onChange={handleChange}
                required
                autoFocus
                style={{
                  background: 'var(--login-input-bg)',
                  border: '1px solid var(--login-input-border)',
                  color: 'var(--login-input-text)'
                }}
              />
            </div>

            <div className="form-group">
              <label className="form-label" htmlFor="password" style={{ color: 'var(--login-text)' }}>
                Contraseña
              </label>
              <div style={{ position: 'relative' }}>
                <input
                  id="password"
                  name="password"
                  type={showPwd ? 'text' : 'password'}
                  className="form-input"
                  placeholder="••••••••"
                  value={form.password}
                  onChange={handleChange}
                  required
                  style={{
                    paddingRight: 40,
                    background: 'var(--login-input-bg)',
                    border: '1px solid var(--login-input-border)',
                    color: 'var(--login-input-text)'
                  }}
                />
                <button
                  type="button"
                  className="btn-icon"
                  onClick={() => setShowPwd((v) => !v)}
                  style={{
                    position: 'absolute',
                    right: 6,
                    top: '50%',
                    transform: 'translateY(-50%)',
                    background: 'transparent',
                    border: 'none',
                    boxShadow: 'none',
                    color: 'var(--login-text-muted)'
                  }}
                >
                  {showPwd ? <EyeOff size={16} /> : <Eye size={16} />}
                </button>
              </div>
            </div>

            <button
              type="submit"
              className="btn btn-primary"
              disabled={loading}
              style={{
                width: '100%',
                justifyContent: 'center',
                padding: '12px',
                marginTop: '8px'
              }}
            >
              {loading ? 'Ingresando...' : 'Ingresar'}
            </button>
          </form>
        </div>
      </div>

      {/* Botón flotante y Popover de Demo (Opción B) */}
      <div style={{
        position: 'absolute',
        bottom: '24px',
        right: '24px',
        zIndex: 50,
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'flex-end',
        gap: '10px'
      }}>
        {showQuickLogin && (
          <div className="animated-slideUp" style={{
            background: 'var(--login-card-bg)',
            backdropFilter: 'blur(16px)',
            border: '1px solid var(--login-card-border)',
            borderRadius: '20px',
            padding: '20px',
            boxShadow: 'var(--login-shadow)',
            width: '320px',
            display: 'flex',
            flexDirection: 'column',
            gap: '14px',
            transition: 'background var(--transition), border-color var(--transition)'
          }}>
            <span style={{
              fontSize: '0.75rem',
              color: 'var(--login-text-light)',
              fontWeight: 700,
              textTransform: 'uppercase',
              letterSpacing: '0.08em',
              display: 'block'
            }}>
              Cuentas Demo de Prueba
            </span>
            
            <div className="quick-login-grid" style={{ gridTemplateColumns: '1fr', gap: '8px', marginTop: 0 }}>
              <div className="quick-login-card" onClick={() => { handleTestLogin('admin@api.com', 'Admin123'); setShowQuickLogin(false); }} style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: '12px 14px' }}>
                <span className="role-badge role-ADMIN">ADMIN</span>
                <span className="user-email" style={{ fontSize: '0.78rem', margin: 0 }}>admin@api.com</span>
              </div>
              <div className="quick-login-card" onClick={() => { handleTestLogin('admin.bog@api.com', 'AdminBog123'); setShowQuickLogin(false); }} style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: '12px 14px' }}>
                <span className="role-badge role-ADMIN_BOG">BOGOTÁ</span>
                <span className="user-email" style={{ fontSize: '0.78rem', margin: 0 }}>admin.bog@api.com</span>
              </div>
              <div className="quick-login-card" onClick={() => { handleTestLogin('admin.med@api.com', 'AdminMed123'); setShowQuickLogin(false); }} style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: '12px 14px' }}>
                <span className="role-badge role-ADMIN_MED">MEDELLÍN</span>
                <span className="user-email" style={{ fontSize: '0.78rem', margin: 0 }}>admin.med@api.com</span>
              </div>
              <div className="quick-login-card" onClick={() => { handleTestLogin('usuario@api.com', 'Usuario123'); setShowQuickLogin(false); }} style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: '12px 14px' }}>
                <span className="role-badge role-USUARIO">USUARIO</span>
                <span className="user-email" style={{ fontSize: '0.78rem', margin: 0 }}>usuario@api.com</span>
              </div>
            </div>
          </div>
        )}

        <button
          onClick={() => setShowQuickLogin((v) => !v)}
          className="btn"
          style={{
            background: 'var(--bg-card)',
            borderColor: 'var(--border-strong)',
            color: 'var(--text)',
            borderRadius: '30px',
            padding: '12px 22px',
            fontSize: '0.85rem',
            fontWeight: 700,
            display: 'flex',
            alignItems: 'center',
            gap: '8px',
            boxShadow: 'var(--login-shadow)',
            border: '1px solid var(--border-strong)',
            backdropFilter: 'blur(10px)'
          }}
        >
          <span>🔑 Cuentas de prueba</span>
          <span style={{ fontSize: '0.7rem', opacity: 0.7 }}>{showQuickLogin ? '✕' : '▲'}</span>
        </button>
      </div>
    </div>
  )
}
