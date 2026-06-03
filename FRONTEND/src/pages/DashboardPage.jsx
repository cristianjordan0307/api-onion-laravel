import { useEffect, useState } from 'react'
import { Building2, Users, TrendingUp, Shield, Mail } from 'lucide-react'
import api from '../api'
import { useAuthStore } from '../store'

export default function DashboardPage() {
  const user = useAuthStore((s) => s.user)
  
  const [stats, setStats] = useState({ companias: 0, empleados: 0 })
  const [recentEmployees, setRecentEmployees] = useState([])
  const [recentCompanies, setRecentCompanies] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true)
      try {
        // Consultar métricas de conteos
        const cRes = await api.get('/companias?tamano=1').catch(() => null)
        const eRes = await api.get('/empleados?tamano=1').catch(() => null)
        
        setStats({
          companias: cRes?.data?.paginacion?.total ?? 0,
          empleados: eRes?.data?.paginacion?.total ?? 0,
        })
      } catch (e) {}

      try {
        // Consultar los últimos 5 empleados registrados
        const recentEmployeesRes = await api.get('/empleados?tamano=5').catch(() => null)
        setRecentEmployees(recentEmployeesRes?.data?.datos ?? [])
      } catch (e) {}

      try {
        // Consultar las últimas 3 compañías registradas
        const recentCompaniesRes = await api.get('/companias?tamano=3').catch(() => null)
        setRecentCompanies(recentCompaniesRes?.data?.datos ?? [])
      } catch (e) {}
      
      setLoading(false)
    }
    fetchData()
  }, [])

  const getInitials = (name) => {
    if (!name) return 'U'
    const parts = name.split(' ')
    if (parts.length >= 2) {
      return (parts[0][0] + parts[1][0]).toUpperCase()
    }
    return parts[0][0].toUpperCase()
  }

  const roleLabels = {
    ADMIN:     'Administrador Global',
    ADMIN_BOG: 'Administrador Bogotá',
    ADMIN_MED: 'Administrador Medellín',
    USUARIO:   'Usuario Estándar',
  }

  const userLocation = {
    ADMIN:     'Sede Central (Global)',
    ADMIN_BOG: 'Sede Bogotá, D.C.',
    ADMIN_MED: 'Sede Medellín, Ant.',
    USUARIO:   'Sede Local Asignada',
  }

  const fmt = (n) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(n)

  return (
    <div className="animated-fadeIn" style={{ display: 'flex', flexDirection: 'column', gap: '28px' }}>
      {/* Saludo */}
      <div className="page-header" style={{ flexDirection: 'column', alignItems: 'flex-start', marginBottom: 0 }}>
        <h2 className="page-title" style={{ fontSize: '1.6rem', fontWeight: 800 }}>
          Bienvenido de nuevo, {user?.name} <span className="waving-hand">👋</span>
        </h2>
      </div>

      {/* Stats */}
      <div className="stats-grid">
        <div className="stat-card">
          <div className="stat-info">
            <span className="stat-label">Compañías</span>
            <span className="stat-value">{loading ? '—' : stats.companias}</span>
          </div>
          <div className="stat-icon" style={{ background: 'var(--accent-light)' }}>
            <Building2 size={22} color="var(--accent)" />
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-info">
            <span className="stat-label">Empleados</span>
            <span className="stat-value">{loading ? '—' : stats.empleados}</span>
          </div>
          <div className="stat-icon" style={{ background: 'var(--success-light)' }}>
            <Users size={22} color="var(--success)" />
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-info">
            <span className="stat-label">Compañía asignada</span>
            <span className="stat-value" style={{ fontSize: '1.1rem', marginTop: 8, fontWeight: 700 }}>
              {user?.compania_id ? `ID #${user.compania_id}` : 'Acceso Global'}
            </span>
          </div>
          <div className="stat-icon" style={{ background: 'var(--warning-light)' }}>
            <TrendingUp size={22} color="var(--warning)" />
          </div>
        </div>
      </div>

      {/* Grid de Dos Columnas: Actividad Reciente y Perfil / Compañías */}
      <div className="dashboard-columns-grid" style={{ alignItems: 'stretch' }}>
        {/* Columna Izquierda: Últimos Empleados Registrados */}
        <div className="card" style={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
          <div className="card-header">
            <h3 className="card-title">Últimos Empleados Registrados</h3>
            <span style={{ fontSize: '0.78rem', color: 'var(--text-muted)', fontWeight: 600 }}>En Tiempo Real</span>
          </div>
          <div className="card-body" style={{ padding: '24px', flex: 1, display: 'flex', flexDirection: 'column', justifyContent: 'flex-start' }}>
            {loading ? (
              <div className="loading-center" style={{ padding: '20px 0' }}><div className="spinner" /></div>
            ) : recentEmployees.length === 0 ? (
              <div style={{ textAlign: 'center', padding: '40px 20px', color: 'var(--text-muted)', fontSize: '0.88rem' }}>
                No hay empleados registrados o no tienes permisos de consulta.
              </div>
            ) : (
              <div style={{ display: 'flex', flexDirection: 'column', gap: '12px', width: '100%' }}>
                {recentEmployees.map((emp) => (
                  <div key={emp.id} className="recent-employee-row" style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    padding: '12px 16px',
                    borderRadius: 'var(--radius-sm)',
                    background: 'var(--bg-input)',
                    border: '1px solid var(--border)'
                  }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
                      <div style={{
                        width: '38px',
                        height: '38px',
                        borderRadius: '50%',
                        background: 'var(--accent-light)',
                        color: 'var(--accent)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        fontWeight: 700,
                        fontSize: '0.85rem',
                        border: '1px solid var(--border-strong)'
                      }}>
                        {getInitials(emp.nombre + ' ' + (emp.apellido ?? ''))}
                      </div>
                      <div>
                        <div style={{ fontSize: '0.88rem', fontWeight: 600, color: 'var(--text)' }}>
                          {emp.nombre} {emp.apellido}
                        </div>
                        <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>
                          {emp.correo}
                        </div>
                      </div>
                    </div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                      <span className="badge badge-accent" style={{ fontSize: '0.68rem', padding: '2px 8px' }}>
                        {emp.cargo}
                      </span>
                      <span style={{ fontSize: '0.88rem', fontWeight: 700, color: 'var(--text)' }}>
                        {fmt(emp.salario)}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Columna Derecha: Resumen de Cuenta (Top) + Últimas Compañías (Bottom) */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '24px', height: '100%', justifyContent: 'space-between' }}>
          
          {/* Tarjeta de Resumen de Cuenta (Alternativa 3) */}
          <div className="card">
            <div className="card-header">
              <h3 className="card-title">Mi Cuenta</h3>
              <span style={{ fontSize: '0.78rem', color: 'var(--text-muted)', fontWeight: 600 }}>Usuario Activo</span>
            </div>
            <div className="card-body" style={{ padding: '24px' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '16px', marginBottom: '20px' }}>
                <div style={{
                  width: '56px',
                  height: '56px',
                  borderRadius: '50%',
                  background: 'linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%)',
                  color: '#ffffff',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontWeight: 700,
                  fontSize: '1.2rem',
                  boxShadow: 'var(--shadow-sm)',
                  border: '2px solid var(--border-strong)'
                }}>
                  {getInitials(user?.name)}
                </div>
                <div>
                  <div style={{ fontSize: '1rem', fontWeight: 700, color: 'var(--text)', lineHeight: 1.2 }}>
                    {user?.name}
                  </div>
                  <span className={`role-badge role-${user?.role}`} style={{ marginTop: '6px', display: 'inline-block' }}>
                    {user?.role}
                  </span>
                </div>
              </div>

              <div style={{ display: 'flex', flexDirection: 'column', gap: '10px', fontSize: '0.85rem' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', color: 'var(--text-muted)' }}>
                  <Mail size={15} />
                  <span style={{ color: 'var(--text)', fontWeight: 500 }}>{user?.email}</span>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', color: 'var(--text-muted)' }}>
                  <Shield size={15} />
                  <span style={{ color: 'var(--text)', fontWeight: 500 }}>{roleLabels[user?.role] ?? user?.role}</span>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', color: 'var(--text-muted)' }}>
                  <TrendingUp size={15} />
                  <span style={{ color: 'var(--text)', fontWeight: 500 }}>{userLocation[user?.role] ?? 'Sede no definida'}</span>
                </div>
              </div>
            </div>
          </div>

          {/* Tarjeta de Últimas Compañías Registradas (Alternativa 1) */}
          <div className="card" style={{ flex: 1, display: 'flex', flexDirection: 'column' }}>
            <div className="card-header">
              <h3 className="card-title">Últimas Compañías</h3>
              <span style={{ fontSize: '0.78rem', color: 'var(--text-muted)', fontWeight: 600 }}>Registros Recientes</span>
            </div>
            <div className="card-body" style={{ padding: '24px', flex: 1, display: 'flex', flexDirection: 'column', justifyContent: 'flex-start' }}>
              {loading ? (
                <div className="loading-center" style={{ padding: '10px 0' }}><div className="spinner" /></div>
              ) : recentCompanies.length === 0 ? (
                <div style={{ textAlign: 'center', padding: '20px', color: 'var(--text-muted)', fontSize: '0.85rem' }}>
                  No hay compañías registradas.
                </div>
              ) : (
                <div style={{ display: 'flex', flexDirection: 'column', gap: '12px', width: '100%' }}>
                  {recentCompanies.map((comp) => (
                    <div key={comp.id} className="recent-employee-row" style={{
                      display: 'flex',
                      alignItems: 'center',
                      gap: '12px',
                      padding: '10px 14px',
                      borderRadius: 'var(--radius-sm)',
                      background: 'var(--bg-input)',
                      border: '1px solid var(--border)'
                    }}>
                      <div style={{
                        width: '32px',
                        height: '32px',
                        borderRadius: '6px',
                        background: 'var(--accent-light)',
                        color: 'var(--accent)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        fontWeight: 700,
                        fontSize: '0.8rem',
                        border: '1px solid var(--border-strong)',
                        flexShrink: 0
                      }}>
                        {getInitials(comp.nombre)}
                      </div>
                      <div style={{ overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                        <div style={{ fontSize: '0.85rem', fontWeight: 600, color: 'var(--text)', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                          {comp.nombre}
                        </div>
                        <div style={{ fontSize: '0.72rem', color: 'var(--text-muted)', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                          {comp.direccion || 'Sin dirección'}
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>

        </div>
      </div>
    </div>
  )
}
