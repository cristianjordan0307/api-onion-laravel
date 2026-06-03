# Onion Admin — Frontend React

Este es el cliente web (Frontend) para interactuar con la **API REST de Compañías y Empleados** construida con Laravel. Está implementado con **React 19**, **Vite**, **Zustand** para la gestión de estados persistentes, **React Router DOM v7** para la navegación y **Axios** para la comunicación con el backend.

---

## Características

- 🎨 **Diseño Moderno e Interactivo**: Estética premium con soporte completo de tema claro y oscuro (persistido en `localStorage`).
- 🔐 **Autenticación JWT Persistente**: Inicio y cierre de sesión seguro con manejo automático del token en cada petición.
- 🎛️ **Autorización Basada en Permisos**: Lectura y parseo del claim `permissions` en el token JWT para controlar dinámicamente la visibilidad y accesibilidad de botones, modales y acciones de CRUD en el cliente.
- 🏢 **CRUD de Compañías**: Visualización paginada, búsqueda libre, ordenamiento por columnas, creación y edición de compañías.
- 👥 **CRUD de Empleados**: Lista paginada, búsqueda, filtrado por compañía, creación, edición y eliminación de empleados.
- 🧑‍💻 **Página de Perfil**: Panel con la información del usuario logueado, su rol, su sede administrativa y una matriz dinámica detallada que muestra qué permisos específicos tiene asignados.

---

## Arquitectura del Cliente

La estructura de archivos bajo la carpeta `src/` está organizada de la siguiente manera:

```text
src/
├── assets/          # Recursos estáticos (imágenes, logos)
├── components/
│   └── Layout.jsx   # Layout de la aplicación con barra lateral, cabecera y toggle de tema
├── pages/
│   ├── LoginPage.jsx     # Formulario de inicio de sesión con credenciales de prueba rápidas
│   ├── DashboardPage.jsx # Estadísticas rápidas y resumen de permisos activos
│   ├── CompaniasPage.jsx # Gestión y CRUD de compañías (modales y tablas)
│   ├── EmpleadosPage.jsx  # Gestión y CRUD de empleados con asociación de compañías
│   └── PerfilPage.jsx    # Información del usuario actual y matriz de permisos
├── api.js           # Cliente Axios configurado con interceptores para inyección del JWT
├── store.js         # Estado global (Zustand) para sesión de usuario, permisos y tema
├── App.jsx          # Enrutador principal de la aplicación y protección de rutas privadas
├── index.css        # Sistema de diseño con variables CSS para temas e interactividad
└── main.jsx         # Punto de entrada de la aplicación React
```

---

## Requisitos

- **Node.js** v18+ o superior.
- **npm** o **yarn**.

---

## Instalación y Configuración

Sigue estos pasos para poner en marcha el frontend:

1. **Instalar dependencias**:
   ```bash
   cd FRONTEND
   npm install
   ```

2. **Configurar el proxy en desarrollo**:
   El proyecto utiliza el proxy integrado de Vite configurado en `vite.config.js` para redirigir las peticiones de `/api` hacia `http://127.0.0.1:8000`. Asegúrate de que el API Laravel esté corriendo en ese puerto.

3. **Iniciar el servidor de desarrollo**:
   ```bash
   npm run dev
   ```
   El frontend estará disponible en **`http://localhost:5173`**.

4. **Compilar para producción**:
   ```bash
   npm run build
   ```

---

## Integración de Seguridad y Roles

El cliente lee el payload codificado en Base64 del JWT obtenido tras un login exitoso. A partir de él, pobla los permisos y el rol del usuario en el store global:

- **ADMIN**: Acceso total a todas las interfaces y botones de creación/edición/eliminación.
- **ADMIN_BOG (Bogotá)**: CRUD completo. El botón y la acción de "Eliminar" se deshabilitan u ocultan visualmente y en el cliente ya que no tiene permisos de borrado.
- **ADMIN_MED (Medellín)**: CRUD completo. Las acciones rápidas de edición parcial (PATCH) están deshabilitadas ya que el API restringe esta operación.
- **USUARIO**: Acceso de consulta. Solo puede ver información general e interactuar con datos asignados a su compañía.
