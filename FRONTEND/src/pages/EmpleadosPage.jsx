import { useState, useEffect, useCallback } from 'react'
import { Plus, Search, Pencil, Trash2, ChevronLeft, ChevronRight, RefreshCw, X, Users } from 'lucide-react'
import api, { getApiError } from '../api'
import { useAuthStore } from '../store'

const EMPTY = { nombre: '', apellido: '', correo: '', cargo: '', salario: '', compania_id: '' }

export default function EmpleadosPage() {
  const can = useAuthStore((s) => s.can)
  const [data, setData]     = useState([])
  const [companias, setCompanias] = useState([])
  const [pag, setPag]       = useState({ pagina_actual: 1, total: 0, ultima_pagina: 1 })
  const [page, setPage]     = useState(1)
  const [buscar, setBuscar] = useState('')
  const [loading, setLoading] = useState(false)
  const [modal, setModal]   = useState(null)
  const [selected, setSelected] = useState(null)
  const [form, setForm]     = useState(EMPTY)
  const [saving, setSaving] = useState(false)
  const [error, setError]   = useState('')

  // Cargar compañías para el selector del formulario
  useEffect(() => {
    api.get('/companias?tamano=100')
      .then(({ data: r }) => setCompanias(r.datos ?? []))
      .catch(() => {})
  }, [])

  const fetchData = useCallback(async () => {
    setLoading(true)
    setError('')
    try {
      const params = { pagina: page, tamano: 10 }
      if (buscar) params.buscar = buscar
      const { data: res } = await api.get('/empleados', { params })
      setData(res.datos ?? [])
      setPag(res.paginacion ?? {})
    } catch (err) {
      setData([])
      setError(getApiError(err))
    } finally {
      setLoading(false)
    }
  }, [page, buscar])

  useEffect(() => { fetchData() }, [fetchData])

  const openCreate = () => { setForm(EMPTY); setError(''); setModal('create') }
  const openEdit   = (e) => {
    setForm({
      nombre: e.nombre, apellido: e.apellido, correo: e.correo,
      cargo: e.cargo, salario: e.salario, compania_id: e.compania_id,
    })
    setSelected(e); setError(''); setModal('edit')
  }
  const openDelete = (e) => { setSelected(e); setModal('delete') }
  const closeModal = ()  => { setModal(null); setSelected(null); setError('') }

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value })

  const handleSave = async () => {
    setSaving(true); setError('')
    const payload = { ...form, salario: parseFloat(form.salario), compania_id: parseInt(form.compania_id) }
    try {
      if (modal === 'create') await api.post('/empleados', payload)
      else await api.put(`/empleados/${selected.id}`, payload)
      closeModal(); fetchData()
    } catch (err) { setError(getApiError(err)) }
    finally { setSaving(false) }
  }

  const handleDelete = async () => {
    setSaving(true)
    try {
      await api.delete(`/empleados/${selected.id}`)
      closeModal(); fetchData()
    } catch (err) { setError(getApiError(err)) }
    finally { setSaving(false) }
  }

  const fmt = (n) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(n)

  return (
    <div className="animated-fadeIn">
      <div className="page-header">
        <div>
          <h2 className="page-title">Empleados</h2>
          <p className="page-subtitle">{pag.total ?? 0} registros en total</p>
        </div>
        {can('empleados:create') && (
          <button className="btn btn-primary" onClick={openCreate}>
            <Plus size={16} /> Nuevo empleado
          </button>
        )}
      </div>

      <div className="card">
        <div className="card-header">
          <div className="search-bar">
            <Search size={15} />
            <input
              placeholder="Buscar empleado..."
              value={buscar}
              onChange={(e) => { setBuscar(e.target.value); setPage(1) }}
            />
          </div>
          <button className="btn-icon" onClick={fetchData} title="Refrescar">
            <RefreshCw size={16} />
          </button>
        </div>

        <div className="table-wrapper">
          {error && !modal && (
            <div className="alert alert-danger" style={{ margin: '16px 20px' }}>
              {error}
            </div>
          )}
          {loading ? (
            <div className="loading-center"><div className="spinner" /></div>
          ) : data.length === 0 ? (
            <div className="empty-state">
              <Users size={40} style={{ marginBottom: 12 }} />
              <p>No se encontraron empleados</p>
            </div>
          ) : (
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Correo</th>
                  <th>Cargo</th>
                  <th>Salario</th>
                  <th>Compañía</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                {data.map((e) => (
                  <tr key={e.id}>
                    <td style={{ color: 'var(--text-muted)' }}>{e.id}</td>
                    <td style={{ fontWeight: 600 }}>{e.nombre} {e.apellido}</td>
                    <td style={{ color: 'var(--text-muted)', fontSize: '0.85rem' }}>{e.correo}</td>
                    <td><span className="badge badge-accent">{e.cargo}</span></td>
                    <td style={{ fontWeight: 600 }}>{fmt(e.salario)}</td>
                    <td style={{ color: 'var(--text-muted)', fontSize: '0.85rem' }}>#{e.compania_id}</td>
                    <td>
                      <div style={{ display: 'flex', gap: 6 }}>
                        {can('empleados:update') && (
                          <button className="btn-icon" onClick={() => openEdit(e)} title="Editar">
                            <Pencil size={15} />
                          </button>
                        )}
                        {can('empleados:delete') && (
                          <button className="btn-icon" onClick={() => openDelete(e)} title="Eliminar"
                            style={{ color: 'var(--danger)', borderColor: 'rgba(239, 68, 68, 0.1)' }}>
                            <Trash2 size={15} />
                          </button>
                        )}
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>

        {!loading && pag.ultima_pagina > 1 && !error && (
          <div className="pagination">
            <span>Página {pag.pagina_actual} de {pag.ultima_pagina}</span>
            <div className="pagination-controls">
              <button className="page-btn" disabled={page <= 1} onClick={() => setPage((p) => p - 1)}>
                <ChevronLeft size={14} />
              </button>
              <button className="page-btn" disabled={page >= pag.ultima_pagina} onClick={() => setPage((p) => p + 1)}>
                <ChevronRight size={14} />
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Modal crear / editar */}
      {(modal === 'create' || modal === 'edit') && (
        <div className="modal-overlay" onClick={closeModal}>
          <div className="modal" onClick={(e) => e.stopPropagation()} style={{ maxWidth: 620 }}>
            <div className="modal-header">
              <h3 className="modal-title">{modal === 'create' ? 'Nuevo empleado' : 'Editar empleado'}</h3>
              <button className="btn-icon" onClick={closeModal}><X size={16} /></button>
            </div>
            <div className="modal-body">
              {error && <div className="alert alert-danger">{error}</div>}
              <div className="form-grid">
                <div className="form-group">
                  <label className="form-label">Nombre</label>
                  <input name="nombre" className="form-input" value={form.nombre} onChange={handleChange} placeholder="Ana" />
                </div>
                <div className="form-group">
                  <label className="form-label">Apellido</label>
                  <input name="apellido" className="form-input" value={form.apellido} onChange={handleChange} placeholder="Gómez" />
                </div>
                <div className="form-group">
                  <label className="form-label">Correo</label>
                  <input name="correo" type="email" className="form-input" value={form.correo} onChange={handleChange} placeholder="ana@empresa.com" />
                </div>
                <div className="form-group">
                  <label className="form-label">Cargo</label>
                  <input name="cargo" className="form-input" value={form.cargo} onChange={handleChange} placeholder="Desarrolladora" />
                </div>
                <div className="form-group">
                  <label className="form-label">Salario</label>
                  <input name="salario" type="number" className="form-input" value={form.salario} onChange={handleChange} placeholder="3500000" />
                </div>
                <div className="form-group">
                  <label className="form-label">Compañía</label>
                  <select name="compania_id" className="form-input" value={form.compania_id} onChange={handleChange}>
                    <option value="">Seleccionar...</option>
                    {companias.map((c) => (
                      <option key={c.id} value={c.id}>{c.nombre}</option>
                    ))}
                  </select>
                </div>
              </div>
            </div>
            <div className="modal-footer">
              <button className="btn btn-ghost" onClick={closeModal}>Cancelar</button>
              <button className="btn btn-primary" onClick={handleSave} disabled={saving}>
                {saving ? 'Guardando...' : 'Guardar'}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Modal eliminar */}
      {modal === 'delete' && (
        <div className="modal-overlay" onClick={closeModal}>
          <div className="modal" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h3 className="modal-title">Eliminar empleado</h3>
              <button className="btn-icon" onClick={closeModal}><X size={16} /></button>
            </div>
            <div className="modal-body">
              {error && <div className="alert alert-danger">{error}</div>}
              <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem' }}>
                ¿Estás seguro de eliminar a <strong style={{ color: 'var(--text)' }}>{selected?.nombre} {selected?.apellido}</strong>?
                Esta acción no se puede deshacer.
              </p>
            </div>
            <div className="modal-footer">
              <button className="btn btn-ghost" onClick={closeModal}>Cancelar</button>
              <button className="btn btn-danger" onClick={handleDelete} disabled={saving}>
                {saving ? 'Eliminando...' : 'Sí, eliminar'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
