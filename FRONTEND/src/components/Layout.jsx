import { NavLink, Outlet, useNavigate, Link } from 'react-router-dom'
import { LayoutDashboard, Building2, Users, User, LogOut, Sun, Moon, Layers, Menu, X } from 'lucide-react'
import { useAuthStore, useThemeStore } from '../store'
import { useState } from 'react'

export default function Layout() {
  const { user, logout } = useAuthStore()
  const { theme, toggleTheme } = useThemeStore()
  const navigate = useNavigate()
  const [sidebarOpen, setSidebarOpen] = useState(false)

  const handleLogout = () => {
    logout()
    navigate('/login')
  }

  // Obtener iniciales del usuario para el avatar
  const getInitials = (name) => {
    if (!name) return 'U'
    const parts = name.split(' ')
    if (parts.length >= 2) {
      return (parts[0][0] + parts[1][0]).toUpperCase()
    }
    return parts[0][0].toUpperCase()
  }

  // Nombre de página según la ruta activa
  const pageTitles = {
    '/dashboard': 'Dashboard',
    '/companias': 'Compañías',
    '/empleados': 'Empleados',
    '/perfil':    'Mi Perfil',
  }
  const currentPath = window.location.pathname
  const pageTitle = pageTitles[currentPath] || 'Onion Admin'

  return (
    <div className="app-layout">
      {/* Sidebar overlay backdrop */}
      <div 
        className={`sidebar-overlay ${sidebarOpen ? 'active' : ''}`} 
        onClick={() => setSidebarOpen(false)} 
      />

      {/* ── Sidebar ── */}
      <aside className={`sidebar ${sidebarOpen ? 'open' : ''}`}>
        <div className="sidebar-logo">
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
            <Layers size={22} />
            <span>Onion Admin</span>
          </div>
          <button className="sidebar-close-btn" onClick={() => setSidebarOpen(false)} title="Cerrar menú">
            <X size={18} />
          </button>
        </div>

        <nav className="sidebar-nav">
          <span className="nav-section-label">Principal</span>

          <NavLink to="/dashboard" onClick={() => setSidebarOpen(false)} className={({ isActive }) => `nav-item${isActive ? ' active' : ''}`}>
            <LayoutDashboard size={18} /> Dashboard
          </NavLink>

          <NavLink to="/companias" onClick={() => setSidebarOpen(false)} className={({ isActive }) => `nav-item${isActive ? ' active' : ''}`}>
            <Building2 size={18} /> Compañías
          </NavLink>

          <NavLink to="/empleados" onClick={() => setSidebarOpen(false)} className={({ isActive }) => `nav-item${isActive ? ' active' : ''}`}>
            <Users size={18} /> Empleados
          </NavLink>

          <span className="nav-section-label" style={{ marginTop: 14 }}>Cuenta</span>

          <NavLink to="/perfil" onClick={() => setSidebarOpen(false)} className={({ isActive }) => `nav-item${isActive ? ' active' : ''}`}>
            <User size={18} /> Mi Perfil
          </NavLink>
        </nav>

        <div className="sidebar-footer">
          <button className="nav-item" onClick={toggleTheme} style={{ width: '100%', textAlign: 'left', border: 'none', background: 'none', cursor: 'pointer' }}>
            {theme === 'light' ? <Moon size={18} /> : <Sun size={18} />}
            {theme === 'light' ? 'Modo oscuro' : 'Modo claro'}
          </button>
          <button className="nav-item" onClick={handleLogout} style={{ width: '100%', textAlign: 'left', color: 'var(--danger)', border: 'none', background: 'none', cursor: 'pointer' }}>
            <LogOut size={18} /> Cerrar sesión
          </button>
        </div>
      </aside>

      {/* ── Main ── */}
      <div className="main-area">
        <header className="header">
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
            <button className="menu-toggle-btn" onClick={() => setSidebarOpen(true)} title="Abrir menú">
              <Menu size={20} />
            </button>
            <h1 className="header-title">{pageTitle}</h1>
          </div>
          <div className="header-actions">
            <span className={`role-badge role-${user?.role}`}>{user?.role}</span>
            <Link to="/perfil" className="header-avatar" title="Ver mi perfil" style={{ textDecoration: 'none' }}>
              {getInitials(user?.name)}
            </Link>
          </div>
        </header>

        <main className="page-content">
          <Outlet />
        </main>
      </div>
    </div>
  )
}
