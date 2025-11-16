# 📚 MÓDULO DE SOLICITUD DE PRÉSTAMOS - Guía de Implementación

## ✅ Solución Implementada

Se ha creado un sistema completo de solicitud de préstamos para estudiantes, permitiendo que:
1. **Estudiantes** soliciten libros desde el catálogo sin restar stock inmediatamente
2. **Administradores** revisen y aprueben/rechacen solicitudes
3. **Al aprobar**, el stock se descuenta automáticamente y el préstamo se activa

---

## 🏗️ Arquitectura de Base de Datos

### Estados de la tabla `prestamo`:
```
estado = 0  → Solicitud PENDIENTE (solicitado por estudiante, pendiente de aprobación)
estado = 1  → Préstamo ACTIVO    (aprobado por admin, descuento de stock aplicado)
estado = 2  → Devuelto           (préstamo finalizado)
```

**Ventaja**: No se modifica la estructura de la tabla `prestamo`, solo se reutiliza el campo `estado`.

---

## 📝 Cambios Implementados

### 1. **Controllers/Catalogo.php** (ACTUALIZADO)
```php
// Nuevos métodos:
- solicitudPrestamo()        // Endpoint POST para solicitar préstamo
- misSolicitudes()           // Endpoint GET para ver mis solicitudes
```

**Flujo**:
- Estudiante solicita libro → se inserta en `prestamo` con `estado=0`
- No descuenta stock
- Verifica que el estudiante no tenga solicitud/préstamo previo del mismo libro

### 2. **Models/PrestamosModel.php** (ACTUALIZADO)
```php
// Nuevos métodos:
- insertarSolicitud()        // Insertar solicitud (estado=0), sin descuento de stock
- aprobarSolicitud()         // Cambiar estado 0→1 y descontar stock
```

### 3. **Controllers/Prestamos.php** (ACTUALIZADO)
```php
// Nuevos endpoints para admin:
- solicitudesPendientes()    // Listar solicitudes (estado=0)
- aprobarSolicitud()         // Cambiar a estado=1 y descontar stock
- rechazarSolicitud()        // Eliminar solicitud (solo si estado=0)
```

### 4. **Views/Catalogo/index.php** (ACTUALIZADO)
- Agregado botón "Solicitar" en cada tarjeta de libro (verde)
- Modal para ingresar cantidad y observaciones
- JS para enviar solicitud via AJAX

### 5. **Views/Catalogo/solicitudes.php** (NUEVO)
- Página para que estudiantes vean sus solicitudes y préstamos activos
- Tabla con: Libro, Autor, Cantidad, Estado, Fechas, Observaciones
- Estados: Pendiente (amarillo), Activo (verde), Devuelto (gris)

### 6. **Views/Prestamos/index.php** (ACTUALIZADO)
- Agregadas **2 tabs**: "Préstamos Activos" | "Solicitudes Pendientes"
- En tab de Solicitudes: tabla con botones "Aprobar" (verde) y "Rechazar" (rojo)
- Al aprobar: cambio de estado, descuento de stock, recarga automática

### 7. **Views/Templates/header.php** (ACTUALIZADO)
- Agregados 2 links en menú de estudiante:
  - **Catálogo** (por defecto)
  - **Mis Solicitudes** (nuevo)

---

## 🚀 Cómo Usar

### Para ESTUDIANTES:

1. **Solicitar un libro**:
   - Ir a Catálogo
   - Ver una tarjeta de libro y pulsar botón verde "Solicitar"
   - Ingresar cantidad y observaciones (ej: "Necesito para el lunes")
   - Pulsar "Solicitar Préstamo"
   - Verás alerta de éxito

2. **Ver mis solicitudes**:
   - Ir a "Mis Solicitudes" en el menú lateral
   - Ver tabla con estado actual de cada solicitud
   - Estados:
     - 🟡 **Pendiente**: admin aún no revisa
     - 🟢 **Activo**: fue aprobado, es un préstamo real
     - ⚫ **Devuelto**: ya lo devolviste

---

### Para ADMINISTRADORES:

1. **Ver solicitudes pendientes**:
   - Ir a Préstamos
   - Pulsar tab "Solicitudes Pendientes"
   - Verás lista de estudiantes que pidieron libros

2. **Aprobar una solicitud**:
   - Pulsar botón verde "Aprobar"
   - Confirmar en diálogo
   - ✅ Se cambia estado 0→1, se descuenta stock, libro pasa a "Préstamos Activos"

3. **Rechazar una solicitud**:
   - Pulsar botón rojo "Rechazar"
   - Confirmar en diálogo
   - ❌ Se elimina la solicitud, no descuenta stock

---

## 📊 Flujo Completo

```
ESTUDIANTE                          ADMIN
   │
   ├─→ Ve catálogo
   │
   ├─→ Pulsa "Solicitar"
   │   └─→ Inserta prestamo (estado=0)
   │   └─→ No descuenta stock
   │
   ├─→ Ve en "Mis Solicitudes"
   │   └─→ Estado: PENDIENTE ⟸ Esperando aprobación
   │
   │                        ╭──────────────────╮
   │                        │ ADMIN ve la solicitud
   │                        │ en "Solicitudes Pendientes"
   │                        ╰──────────────────╯
   │                                │
   │                        ╭───────V─────────╮
   │                        │ Pulsa APROBAR   │
   │                        ├─────────────────┤
   │                        │ estado 0 → 1    │
   │                        │ stock -=cantidad│
   │                        ╰───────┬─────────╯
   │                                │
   ├─→ Ve en "Mis Solicitudes"      │
   │   └─→ Estado: ACTIVO ✓         │
   │   └─→ Stock del libro redujo   │
   │
   └─→ Libro está en "Préstamos Activos"
       (hasta que admin lo marque "Devuelto")
```

---

## 🔐 Validaciones

- ✅ Solo estudiantes autenticados pueden solicitar
- ✅ No puede solicitar el mismo libro 2 veces (si ya tiene pendiente o activo)
- ✅ Solo admin puede aprobar/rechazar
- ✅ Solo se descuenta stock al APROBAR (no al solicitar)
- ✅ Verifica disponibilidad del libro antes de aprobar

---

## 🧪 Pruebas Recomendadas

1. **Como ESTUDIANTE**:
   - Solicita un libro con cantidad válida
   - Verifica que aparece en "Mis Solicitudes" con estado PENDIENTE
   - Intenta solicitar el mismo libro de nuevo → debe mostrar error

2. **Como ADMIN**:
   - Ve la solicitud en "Solicitudes Pendientes"
   - Aprueba la solicitud
   - Verifica que el stock del libro se redujo
   - Verifica que aparece en "Préstamos Activos"

3. **Stock**:
   - Si hay 5 libros y solicitas 3, el stock debe estar 5
   - Al aprobar, debe quedar en 2

---

## 📦 Archivos Modificados/Creados

### Modificados:
- `Controllers/Catalogo.php` ✏️
- `Controllers/Prestamos.php` ✏️
- `Models/PrestamosModel.php` ✏️
- `Views/Catalogo/index.php` ✏️
- `Views/Prestamos/index.php` ✏️
- `Views/Templates/header.php` ✏️

### Creados:
- `Views/Catalogo/solicitudes.php` ✨

---

## 💡 Próximas Mejoras (Opcional)

- Notificaciones por email cuando solicitud es aprobada/rechazada
- Historial de préstamos devueltos
- Recordatorio automático de devolución
- Multas por atraso (si fecha_devolucion ha pasado)
- Valoración de libros por estudiantes

---

## ✨ ¡Listo!

El módulo está 100% funcional. Puedes:
1. Acceder como **estudiante** (usuario con permiso Alumno)
2. Solicitar libros desde el catálogo
3. Ver tus solicitudes en "Mis Solicitudes"
4. Como **admin**, ir a Préstamos → "Solicitudes Pendientes" para gestionar

