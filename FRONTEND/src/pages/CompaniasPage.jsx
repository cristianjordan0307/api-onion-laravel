import { useState, useEffect, useCallback } from 'react'
import { Plus, Search, Pencil, Trash2, ChevronLeft, ChevronRight, RefreshCw, X, Building2 } from 'lucide-react'
import api, { getApiError } from '../api'
import { useAuthStore } from '../store'

const EMPTY = { nombre: '', direccion: '', telefono: '' }

export default function CompaniasPage() {
  const can = useAuthStore((s) => s.can)
  const user = useAuthStore((s) => s.user)
  const [data, setData]     = useState([])
  const [pag, setPag]       = useState({ pagina_actual: 1, total: 0, ultima_pagina: 1 })
  const [page, setPage]     = useState(1)
  const [buscar, setBuscar] = useState('')
  const [loading, setLoading] = useState(false)
  const [modal, setModal]   = useState(null)   // null | 'create' | 'edit' | 'delete'
  const [selected, setSelected] = useState(null)
  const [form, setForm]     = useState(EMPTY)
  const [saving, setSaving] = useState(false)
  const [error, setError]   = useState('')

  const fetchData = useCallback(async () => {
    setLoading(true)
    setError('')
    try {
      const params = { pagina: page, tamano: 10 }
      if (buscar) params.buscar = buscar
      const { data: res } = await api.get('/companias', { params })
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
  const openEdit   = (c)  => { setForm({ nombre: c.nombre, direccion: c.direccion, telefono: c.telefono }); setSelected(c); setError(''); setModal('edit') }
  const openDelete = (c)  => { setSelected(c); setModal('delete') }
  const closeModal = ()   => { setModal(null); setSelected(null); setError('') }

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value })

  const handleSave = async () => {
    setSaving(true); setError('')
    try {
      if (modal === 'create') await api.post('/companias', form)
      else await api.put(`/companias/${selected.id}`, form)
      closeModal(); fetchData()
    } catch (err) { setError(getApiError(err)) }
    finally { setSaving(false) }
  }

  const handleDelete = async () => {
    setSaving(true)
    try {
      await api.delete(`/companias/${selected.id}`)
      closeModal(); fetchData()
    } catch (err) { setError(getApiError(err)) }
    finally { setSaving(false) }
  }

  return (
    <div className="animated-fadeIn">
      <div className="page-header">
        <div>
          <h2 className="page-title">Compañías</h2>
          <p className="page-subtitle">{pag.total ?? 0} registros en total</p>
        </div>
        {can('companias:create') && (
          <button className="btn btn-primary" onClick={openCreate}>
            <Plus size={16} /> Nueva compañía
          </button>
        )}
      </div>

      <div className="card">
        <div className="card-header">
          <div className="search-bar">
            <Search size={15} />
            <input
              placeholder="Buscar compañía..."
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
              <Building2 size={40} style={{ marginBottom: 12 }} />
              <p>No se encontraron compañías</p>
            </div>
          ) : (
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Dirección</th>
                  <th>Teléfono</th>
                  <th>Fecha creación</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                {data.map((c) => (
                  <tr key={c.id}>
                    <td style={{ color: 'var(--text-muted)' }}>{c.id}</td>
                    <td style={{ fontWeight: 600 }}>{c.nombre}</td>
                    <td>{c.direccion}</td>
                    <td>{c.telefono}</td>
                    <td style={{ color: 'var(--text-muted)', fontSize: '0.82rem' }}>
                      {c.fecha_creacion ? new Date(c.fecha_creacion).toLocaleDateString('es-CO') : '—'}
                    </td>
                    <td>
                      <div style={{ display: 'flex', gap: 6 }}>
                        {(user?.role === 'ADMIN' || (can('companias:update') && user?.compania_id === c.id)) && (
                          <button className="btn-icon" onClick={() => openEdit(c)} title="Editar">
                            <Pencil size={15} />
                          </button>
                        )}
                        {(user?.role === 'ADMIN' || (can('companias:delete') && user?.compania_id === c.id)) && (
                          <button className="btn-icon" onClick={() => openDelete(c)} title="Eliminar"
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

        {/* Paginación */}
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
          <div className="modal" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h3 className="modal-title">{modal === 'create' ? 'Nueva compañía' : 'Editar compañía'}</h3>
              <button className="btn-icon" onClick={closeModal}><X size={16} /></button>
            </div>
            <div className="modal-body">
              {error && <div className="alert alert-danger">{error}</div>}
              <div className="form-group">
                <label className="form-label">Nombre</label>
                <input name="nombre" className="form-input" value={form.nombre} onChange={handleChange} placeholder="Empresa S.A.S" />
              </div>
              <div className="form-group">
                <label className="form-label">Dirección</label>
                <input name="direccion" className="form-input" value={form.direccion} onChange={handleChange} placeholder="Calle 1 # 2-3" />
              </div>
              <div className="form-group">
                <label className="form-label">Teléfono</label>
                <input name="telefono" className="form-input" value={form.telefono} onChange={handleChange} placeholder="3001234567" />
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
              <h3 className="modal-title">Eliminar compañía</h3>
              <button className="btn-icon" onClick={closeModal}><X size={16} /></button>
            </div>
            <div className="modal-body">
              {error && <div className="alert alert-danger">{error}</div>}
              <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem' }}>
                ¿Estás seguro de eliminar <strong style={{ color: 'var(--text)' }}>{selected?.nombre}</strong>?
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
