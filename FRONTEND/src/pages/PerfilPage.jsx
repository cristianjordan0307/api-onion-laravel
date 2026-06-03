import { useState, useEffect } from 'react'
import { useAuthStore } from '../store'
import { User, Mail, Shield, Building, MapPin, CheckCircle, XCircle, Activity, ExternalLink } from 'lucide-react'

export default function PerfilPage() {
  const user = useAuthStore((s) => s.user)
  const permissions = useAuthStore((s) => s.permissions)

  const [apiStatus, setApiStatus] = useState('checking')
  const [latency, setLatency] = useState(0)

  useEffect(() => {
    const startTime = Date.now()
    const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
    const checkUrl = apiUrl.replace(/\/api$/, '') + '/openapi.yaml'
    
    fetch(checkUrl, { method: 'HEAD' })
      .then((res) => {
        if (res.ok) {
          setApiStatus('online')
          setLatency(Date.now() - startTime)
        } else {
          setApiStatus('offline')
        }
      })
      .catch(() => {
        setApiStatus('offline')
      })
  }, [])

  const docUrl = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace(/\/api$/, '/swagger')

  const roleLabels = {
    ADMIN:     'Administrador Global',
    ADMIN_BOG: 'Administrador Bogotá',
    ADMIN_MED: 'Administrador Medellín',
    USUARIO:   'Usuario Estándar',
  }

  const roleDescriptions = {
    ADMIN:     'Tiene acceso y control total sobre el sistema, incluyendo todas las operaciones CRUD para compañías y empleados sin restricciones.',
    ADMIN_BOG: 'Administrador regional de la sede de Bogotá. Puede realizar consultas, crear y actualizar (tanto PUT como PATCH) registros, pero tiene restringida la eliminación de datos.',
    ADMIN_MED: 'Administrador regional de la sede de Medellín. Puede realizar consultas, crear y actualizar registros mediante PUT, y eliminar datos, pero tiene restringido el uso de actualizaciones parciales (PATCH).',
    USUARIO:   'Usuario general con permisos limitados. Solo puede interactuar con registros específicos y tiene restricciones en la eliminación.',
  }

  const userLocation = {
    ADMIN:     'Global / Remoto',
    ADMIN_BOG: 'Sede Bogotá, D.C.',
    ADMIN_MED: 'Sede Medellín, Antioquia',
    USUARIO:   'Sede Asignada',
  }

  const resourcePermissions = [
    {
      resource: 'Compañías',
      actions: [
        { name: 'Visualizar', key: 'companias:read' },
        { name: 'Crear', key: 'companias:create' },
        { name: 'Modificar (Completo/PUT)', key: 'companias:update' },
        { name: 'Modificar (Parcial/PATCH)', key: 'companias:patch' },
        { name: 'Eliminar', key: 'companias:delete' },
      ]
    },
    {
      resource: 'Empleados',
      actions: [
        { name: 'Visualizar', key: 'empleados:read' },
        { name: 'Crear', key: 'empleados:create' },
        { name: 'Modificar (Completo/PUT)', key: 'empleados:update' },
        { name: 'Modificar (Parcial/PATCH)', key: 'empleados:patch' },
        { name: 'Eliminar', key: 'empleados:delete' },
      ]
    }
  ]

  return (
    <div className="animated-fadeIn" style={{ maxWidth: '900px', margin: '0 auto', display: 'flex', flexDirection: 'column', gap: '24px' }}>
      
      {/* Tarjeta de Información Principal */}
      <div className="card" style={{ overflow: 'hidden' }}>
        <div style={{
          background: 'linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%)',
          padding: '40px 32px',
          color: '#ffffff',
          position: 'relative'
        }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '24px', flexWrap: 'wrap' }}>
            <div style={{
              width: '80px',
              height: '80px',
              borderRadius: '50%',
              background: 'rgba(255, 255, 255, 0.2)',
              backdropFilter: 'blur(5px)',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              border: '2px solid rgba(255, 255, 255, 0.4)'
            }}>
              <User size={40} color="#ffffff" />
            </div>
            <div>
              <h2 style={{ fontSize: '1.8rem', fontWeight: 800, marginBottom: '4px', letterSpacing: '-0.5px' }}>{user?.name}</h2>
              <div style={{ display: 'flex', alignItems: 'center', gap: '8px', opacity: 0.9 }}>
                <span className={`role-badge role-${user?.role}`} style={{ border: '1px solid rgba(255,255,255,0.4)', background: 'rgba(255,255,255,0.1)', color: '#fff' }}>
                  {user?.role}
                </span>
                <span style={{ fontSize: '0.85rem' }}>•</span>
                <span style={{ fontSize: '0.85rem', fontWeight: 600 }}>ID #{user?.id}</span>
              </div>
            </div>
          </div>
        </div>

        <div className="card-body" style={{ padding: '32px' }}>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '24px' }}>
            
            <div style={{ display: 'flex', gap: '16px', alignItems: 'flex-start' }}>
              <div style={{ color: 'var(--accent)', marginTop: '2px' }}>
                <Mail size={18} />
              </div>
              <div>
                <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', textTransform: 'uppercase', letterSpacing: '0.05em', fontWeight: 700, marginBottom: '2px' }}>Correo Electrónico</div>
                <div style={{ fontSize: '0.95rem', fontWeight: 600 }}>{user?.email}</div>
              </div>
            </div>

            <div style={{ display: 'flex', gap: '16px', alignItems: 'flex-start' }}>
              <div style={{ color: 'var(--accent)', marginTop: '2px' }}>
                <MapPin size={18} />
              </div>
              <div>
                <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', textTransform: 'uppercase', letterSpacing: '0.05em', fontWeight: 700, marginBottom: '2px' }}>Ubicación Administrativa</div>
                <div style={{ fontSize: '0.95rem', fontWeight: 600 }}>{userLocation[user?.role] || 'No definida'}</div>
              </div>
            </div>

            <div style={{ display: 'flex', gap: '16px', alignItems: 'flex-start' }}>
              <div style={{ color: 'var(--accent)', marginTop: '2px' }}>
                <Building size={18} />
              </div>
              <div>
                <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', textTransform: 'uppercase', letterSpacing: '0.05em', fontWeight: 700, marginBottom: '2px' }}>Compañía Asociada</div>
                <div style={{ fontSize: '0.95rem', fontWeight: 600 }}>
                  {user?.compania_id ? `Compañía ID: ${user.compania_id}` : 'Acceso Global'}
                </div>
              </div>
            </div>

          </div>

          <div style={{
            marginTop: '32px',
            paddingTop: '24px',
            borderTop: '1px solid var(--border-strong)',
            display: 'flex',
            gap: '16px',
            alignItems: 'flex-start'
          }}>
            <div style={{ color: 'var(--accent)', marginTop: '2px' }}>
              <Shield size={18} />
            </div>
            <div>
              <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', textTransform: 'uppercase', letterSpacing: '0.05em', fontWeight: 700, marginBottom: '4px' }}>
                Descripción de Rol ({roleLabels[user?.role] || user?.role})
              </div>
              <p style={{ fontSize: '0.9rem', color: 'var(--text-muted)', lineHeight: 1.6 }}>
                {roleDescriptions[user?.role] || 'No hay descripción disponible para este rol.'}
              </p>
            </div>
          </div>

        </div>
      </div>

      {/* Tarjeta de Matriz de Permisos Detallada */}
      <div className="card">
        <div className="card-header">
          <h3 className="card-title">Matriz Detallada de Permisos</h3>
        </div>
        <div className="card-body" style={{ padding: '32px' }}>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '32px' }}>
            {resourcePermissions.map((res, i) => (
              <div key={i}>
                <h4 style={{ fontSize: '1rem', fontWeight: 700, color: 'var(--text)', marginBottom: '16px', borderBottom: '1px solid var(--border-strong)', paddingBottom: '8px' }}>
                  {res.resource}
                </h4>
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: '12px' }}>
                  {res.actions.map((act, j) => {
                    const hasPerm = permissions.includes(act.key) || user?.role === 'ADMIN'
                    return (
                      <div key={j} style={{
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                        padding: '12px 16px',
                        borderRadius: 'var(--radius-sm)',
                        background: hasPerm ? 'var(--success-light)' : 'var(--bg-input)',
                        border: `1px solid ${hasPerm ? 'rgba(16, 185, 129, 0.15)' : 'var(--border-strong)'}`,
                        transition: 'transform var(--transition)'
                      }}>
                        <span style={{ fontSize: '0.85rem', fontWeight: 600, color: hasPerm ? 'var(--text)' : 'var(--text-muted)' }}>
                          {act.name}
                        </span>
                        {hasPerm ? (
                          <div style={{ display: 'flex', alignItems: 'center', gap: '4px', color: 'var(--success)' }}>
                            <CheckCircle size={16} />
                            <span style={{ fontSize: '0.75rem', fontWeight: 700 }}>Permitido</span>
                          </div>
                        ) : (
                          <div style={{ display: 'flex', alignItems: 'center', gap: '4px', color: 'var(--text-light)' }}>
                            <XCircle size={16} />
                            <span style={{ fontSize: '0.75rem', fontWeight: 700 }}>Restringido</span>
                          </div>
                        )}
                      </div>
                    )
                  })}
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Tarjeta de Estado de la API y Documentación */}
      <div className="card">
        <div className="card-header" style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
          <Activity size={18} color="var(--accent)" />
          <h3 className="card-title" style={{ margin: 0 }}>Estado del Servidor y Documentación</h3>
        </div>
        <div className="card-body" style={{ padding: '32px' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '20px' }}>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '6px' }}>
              <div style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
                Dirección del Servidor API
              </div>
              <code style={{ 
                background: 'var(--bg-input)', 
                padding: '6px 12px', 
                borderRadius: '6px', 
                fontSize: '0.85rem', 
                fontFamily: 'monospace',
                border: '1px solid var(--border-strong)',
                color: 'var(--accent)'
              }}>
                {import.meta.env.VITE_API_URL || 'http://localhost:8000/api'}
              </code>
            </div>

            <div style={{ display: 'flex', alignItems: 'center', gap: '24px' }}>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '4px' }}>
                <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)', textTransform: 'uppercase', fontWeight: 700, letterSpacing: '0.05em' }}>Conexión</span>
                {apiStatus === 'checking' && (
                  <div style={{ display: 'flex', alignItems: 'center', gap: '8px', color: 'var(--text-muted)' }}>
                    <div className="spinner" style={{ width: '12px', height: '12px', borderWidth: '2px', margin: 0, display: 'inline-block' }} />
                    <span style={{ fontSize: '0.9rem', fontWeight: 600 }}>Verificando...</span>
                  </div>
                )}
                {apiStatus === 'online' && (
                  <div style={{ display: 'flex', alignItems: 'center', gap: '8px', color: 'var(--success)' }}>
                    <span style={{
                      display: 'inline-block',
                      width: '8px',
                      height: '8px',
                      borderRadius: '50%',
                      background: 'var(--success)',
                      boxShadow: '0 0 8px var(--success)'
                    }} />
                    <span style={{ fontSize: '0.9rem', fontWeight: 700 }}>Conectado ({latency} ms)</span>
                  </div>
                )}
                {apiStatus === 'offline' && (
                  <div style={{ display: 'flex', alignItems: 'center', gap: '8px', color: 'var(--danger)' }}>
                    <span style={{
                      display: 'inline-block',
                      width: '8px',
                      height: '8px',
                      borderRadius: '50%',
                      background: 'var(--danger)',
                      boxShadow: '0 0 8px var(--danger)'
                    }} />
                    <span style={{ fontSize: '0.9rem', fontWeight: 700 }}>Desconectado</span>
                  </div>
                )}
              </div>

              <a 
                href={docUrl} 
                target="_blank" 
                rel="noreferrer" 
                className="btn btn-primary" 
                style={{ 
                  display: 'inline-flex', 
                  alignItems: 'center', 
                  gap: '8px', 
                  textDecoration: 'none',
                  fontSize: '0.85rem',
                  padding: '10px 18px'
                }}
              >
                Documentación API <ExternalLink size={14} />
              </a>
            </div>
          </div>
        </div>
      </div>

    </div>
  )
}
