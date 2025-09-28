# 📖 Guía de Usuario - Sistema WISP

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![Status](https://img.shields.io/badge/status-active-green.svg)

## 📋 Tabla de Contenidos

- [Introducción](#introducción)
- [Acceso al Sistema](#acceso-al-sistema)
- [Panel de Control](#panel-de-control)
- [Gestión de Clientes](#gestión-de-clientes)
- [Administración de Planes](#administración-de-planes)
- [Facturación y Pagos](#facturación-y-pagos)
- [Configuración de Red](#configuración-de-red)
- [Monitoreo y Reportes](#monitoreo-y-reportes)
- [Configuraciones del Sistema](#configuraciones-del-sistema)
- [Solución de Problemas](#solución-de-problemas)

---

## 🚀 Introducción

El Sistema WISP es una plataforma integral diseñada para la gestión completa de proveedores de servicios de internet inalámbrico. Permite administrar clientes, planes de servicio, facturación, configuración de red y monitoreo en tiempo real.

### Características Principales
- ✅ Gestión completa de clientes
- ✅ Integración con MikroTik RouterOS
- ✅ Facturación automática
- ✅ Monitoreo de red en tiempo real
- ✅ Notificaciones WhatsApp
- ✅ Reportes y estadísticas
- ✅ Gestión de inventario

---

## 🔐 Acceso al Sistema

### Inicio de Sesión

1. **Acceder a la URL del sistema**
   ```
   http://tu-dominio.com/
   ```

2. **Credenciales de acceso**
   - Usuario: `admin`
   - Contraseña: `123456` (cambiar en primera sesión)

3. **Recuperación de contraseña**
   - Hacer clic en "¿Olvidaste tu contraseña?"
   - Ingresar email registrado
   - Seguir instrucciones del correo

### Roles de Usuario

| Rol | Permisos | Descripción |
|-----|----------|-------------|
| **Administrador** | Completo | Acceso total al sistema |
| **Operador** | Limitado | Gestión de clientes y tickets |
| **Técnico** | Específico | Instalaciones y soporte técnico |
| **Contador** | Financiero | Facturación y reportes |

---

## 🏠 Panel de Control

### Dashboard Principal

El dashboard proporciona una vista general del estado del sistema:

#### Métricas Principales
- 📊 **Clientes Activos**: Total de servicios activos
- 💰 **Ingresos del Mes**: Facturación mensual
- 🌐 **Estado de Red**: Routers conectados
- 📞 **Tickets Abiertos**: Soporte pendiente

#### Widgets Disponibles
- **Gráfico de Ingresos**: Tendencia mensual
- **Mapa de Clientes**: Ubicación geográfica
- **Estado de Routers**: Conectividad MikroTik
- **Actividad Reciente**: Últimas acciones

### Navegación

```
📁 Sistema WISP
├── 🏠 Dashboard
├── 👥 Clientes
│   ├── Lista de Clientes
│   ├── Agregar Cliente
│   └── Importar Clientes
├── 📋 Planes
│   ├── Planes de Servicio
│   └── Configurar Planes
├── 💰 Facturación
│   ├── Generar Facturas
│   ├── Pagos Recibidos
│   └── Reportes
├── 🌐 Red
│   ├── Routers MikroTik
│   ├── Zonas de Cobertura
│   └── Monitoreo
├── 🎫 Soporte
│   ├── Tickets
│   └── Incidencias
└── ⚙️ Configuración
    ├── Empresa
    ├── Usuarios
    └── Sistema
```

---

## 👥 Gestión de Clientes

### Agregar Nuevo Cliente

1. **Navegar a Clientes > Agregar Cliente**

2. **Información Personal**
   ```
   - Nombres y Apellidos
   - Tipo y Número de Documento
   - Teléfono Principal
   - Teléfono Secundario (opcional)
   - Email
   ```

3. **Dirección y Ubicación**
   ```
   - Dirección Completa
   - Referencia de Ubicación
   - Coordenadas GPS (automáticas)
   - Zona de Cobertura
   ```

4. **Configuración de Servicio**
   ```
   - Plan de Servicio
   - Router Asignado
   - IP Local
   - IP Pública
   - Credenciales de Red
   ```

### Gestión de Estados

| Estado | Descripción | Acciones Disponibles |
|--------|-------------|---------------------|
| 🟢 **Activo** | Servicio funcionando | Suspender, Editar, Facturar |
| 🟡 **Suspendido** | Servicio pausado | Activar, Cortar |
| 🔴 **Cortado** | Sin servicio | Activar, Eliminar |
| ⚪ **Inactivo** | Cliente dado de baja | Reactivar |

### Acciones Masivas

- ✅ **Activar Servicios**: Múltiples clientes
- ⏸️ **Suspender por Mora**: Filtro automático
- 📧 **Envío de Notificaciones**: WhatsApp/Email
- 📊 **Exportar Datos**: Excel/PDF

---

## 📋 Administración de Planes

### Crear Plan de Servicio

1. **Información Básica**
   ```
   - Nombre del Plan
   - Descripción
   - Velocidad de Bajada
   - Velocidad de Subida
   - Precio Mensual
   ```

2. **Configuración Técnica**
   ```
   - Tipo de Conexión (PPPoE/Static)
   - Límite de Datos (opcional)
   - Prioridad de Tráfico
   - Restricciones de Horario
   ```

3. **Configuración MikroTik**
   ```
   - Queue Type
   - Burst Limit
   - Burst Threshold
   - Burst Time
   ```

### Tipos de Planes

#### Plan Residencial
- Velocidad: 10/2 Mbps
- Precio: $25/mes
- Sin límite de datos
- Soporte básico

#### Plan Empresarial
- Velocidad: 50/10 Mbps
- Precio: $75/mes
- IP estática incluida
- Soporte prioritario

#### Plan Premium
- Velocidad: 100/20 Mbps
- Precio: $150/mes
- Múltiples IPs
- SLA garantizado

---

## 💰 Facturación y Pagos

### Proceso de Facturación

#### Facturación Automática
1. **Configurar Fecha de Corte**
   - Día del mes para generar facturas
   - Días de gracia antes del corte
   - Notificaciones automáticas

2. **Generar Facturas Masivas**
   ```bash
   Sistema > Facturación > Generar Facturas
   - Seleccionar período
   - Filtrar por zona/plan
   - Confirmar generación
   ```

3. **Envío Automático**
   - Email con PDF adjunto
   - WhatsApp con enlace de pago
   - SMS de recordatorio

#### Facturación Manual
1. **Cliente Individual**
   - Ir a perfil del cliente
   - Clic en "Generar Factura"
   - Seleccionar servicios
   - Confirmar monto

### Métodos de Pago

| Método | Descripción | Comisión |
|--------|-------------|----------|
| 💳 **Tarjeta** | Visa/MasterCard | 3.5% |
| 🏦 **Transferencia** | Banco directo | Sin comisión |
| 💵 **Efectivo** | Pago en oficina | Sin comisión |
| 📱 **Billetera Digital** | PayPal/Yape | 2.9% |

### Reportes Financieros

#### Reporte de Ingresos
```
- Ingresos por período
- Comparativo mensual
- Proyección anual
- Análisis por plan
```

#### Reporte de Morosidad
```
- Clientes en mora
- Días de atraso
- Monto pendiente
- Acciones recomendadas
```

---

## 🌐 Configuración de Red

### Integración MikroTik

#### Configuración Inicial
1. **Agregar Router**
   ```
   Red > Routers > Agregar Nuevo
   - IP del Router
   - Usuario API
   - Contraseña API
   - Puerto (8728)
   ```

2. **Verificar Conexión**
   ```
   - Test de conectividad
   - Sincronización de datos
   - Configuración automática
   ```

#### Gestión de Queues

**Simple Queue (Recomendado)**
```mikrotik
/queue simple
add name="cliente-001" target=192.168.1.100/32 max-limit=10M/2M
```

**Queue Tree (Avanzado)**
```mikrotik
/queue tree
add name="download" parent=global max-limit=100M
add name="upload" parent=global max-limit=20M
```

### Zonas de Cobertura

#### Crear Nueva Zona
1. **Información Básica**
   ```
   - Nombre de la Zona
   - Descripción
   - Router Principal
   - Coordenadas del Centro
   ```

2. **Configuración de Red**
   ```
   - Rango de IPs
   - Gateway
   - DNS Servers
   - VLAN (opcional)
   ```

### Monitoreo de Red

#### Dashboard de Red
- 📊 **Ancho de Banda**: Uso en tiempo real
- 🔗 **Conectividad**: Estado de enlaces
- 📈 **Tráfico**: Estadísticas por cliente
- ⚠️ **Alertas**: Problemas detectados

#### Alertas Automáticas
- Router desconectado
- Alto uso de ancho de banda
- Cliente sin conectividad
- Falla en enlace principal

---

## 📊 Monitoreo y Reportes

### Monitoreo en Tiempo Real

#### Panel de Monitoreo
```
🔴 Crítico    🟡 Advertencia    🟢 Normal
├── Routers: 15/16 Online
├── Clientes: 245/250 Conectados
├── Ancho de Banda: 75% Utilizado
└── Latencia Promedio: 25ms
```

#### Métricas Clave
- **Uptime**: Disponibilidad del servicio
- **Throughput**: Transferencia de datos
- **Latencia**: Tiempo de respuesta
- **Packet Loss**: Pérdida de paquetes

### Reportes Disponibles

#### Reporte de Clientes
- Lista completa de clientes
- Estado de servicios
- Información de contacto
- Historial de pagos

#### Reporte de Facturación
- Facturas generadas
- Pagos recibidos
- Cuentas por cobrar
- Análisis de morosidad

#### Reporte de Red
- Estado de routers
- Uso de ancho de banda
- Estadísticas de tráfico
- Incidencias reportadas

### Exportación de Datos

#### Formatos Disponibles
- 📄 **PDF**: Reportes formateados
- 📊 **Excel**: Datos para análisis
- 📋 **CSV**: Importación a otros sistemas
- 📈 **Gráficos**: Visualización de tendencias

---

## ⚙️ Configuraciones del Sistema

### Configuración de Empresa

#### Datos de la Empresa
```
- Razón Social
- RUC/NIT
- Dirección Fiscal
- Teléfonos de Contacto
- Email Corporativo
- Logo de la Empresa
```

#### Configuración Fiscal
```
- Tipo de Contribuyente
- Régimen Tributario
- Certificado Digital
- Facturación Electrónica
```

### Configuración de Usuarios

#### Crear Usuario
1. **Información Personal**
   ```
   - Nombres y Apellidos
   - Email
   - Teléfono
   - Cargo/Posición
   ```

2. **Credenciales de Acceso**
   ```
   - Usuario
   - Contraseña
   - Confirmar Contraseña
   ```

3. **Permisos y Roles**
   ```
   - Rol Asignado
   - Módulos Permitidos
   - Restricciones Especiales
   ```

### Configuración de Notificaciones

#### WhatsApp Business API
```
- Token de API
- Número de Empresa
- Plantillas de Mensajes
- Horarios de Envío
```

#### Email SMTP
```
- Servidor SMTP
- Puerto (587/465)
- Usuario y Contraseña
- Encriptación (TLS/SSL)
```

---

## 🔧 Solución de Problemas

### Problemas Comunes

#### Cliente sin Internet
1. **Verificar Estado en Sistema**
   ```
   - Estado del cliente (Activo/Suspendido)
   - Configuración de red
   - Queue en MikroTik
   ```

2. **Diagnóstico de Red**
   ```
   - Ping al cliente
   - Verificar conectividad del router
   - Revisar configuración IP
   ```

3. **Soluciones**
   ```
   - Reactivar servicio
   - Regenerar credenciales
   - Reiniciar queue
   - Contactar soporte técnico
   ```

#### Facturación Incorrecta
1. **Verificar Configuración**
   ```
   - Plan asignado al cliente
   - Fecha de activación
   - Descuentos aplicados
   ```

2. **Corregir Factura**
   ```
   - Anular factura incorrecta
   - Generar nueva factura
   - Aplicar nota de crédito
   ```

#### Router Desconectado
1. **Verificar Conectividad**
   ```
   - Ping al router
   - Verificar credenciales API
   - Revisar configuración de red
   ```

2. **Reconectar Router**
   ```
   - Reiniciar servicio API
   - Verificar configuración
   - Sincronizar datos
   ```

### Contacto de Soporte

#### Canales de Soporte
- 📧 **Email**: soporte@empresa.com
- 📱 **WhatsApp**: +1 234 567 8900
- 🎫 **Tickets**: Sistema interno
- 📞 **Teléfono**: +1 234 567 8901

#### Horarios de Atención
- **Lunes a Viernes**: 8:00 AM - 6:00 PM
- **Sábados**: 9:00 AM - 2:00 PM
- **Emergencias**: 24/7

---

## 📚 Recursos Adicionales

### Documentación Técnica
- [API Documentation](API_DOCUMENTATION.md)
- [Esquema de Base de Datos](DATABASE_SCHEMA.md)
- [Diagramas UML](UML_DIAGRAMS.md)

### Tutoriales en Video
- Configuración inicial del sistema
- Gestión de clientes paso a paso
- Integración con MikroTik
- Generación de reportes

### Comunidad
- **Foro de Usuarios**: [forum.empresa.com](https://forum.empresa.com)
- **Grupo de Telegram**: [@sistema-wisp](https://t.me/sistema-wisp)
- **Canal de YouTube**: [Sistema WISP](https://youtube.com/sistema-wisp)

---

## 📝 Notas de Versión

### Versión 1.0.0
- ✅ Lanzamiento inicial
- ✅ Gestión completa de clientes
- ✅ Integración MikroTik
- ✅ Facturación automática
- ✅ Notificaciones WhatsApp

### Próximas Características
- 🔄 Integración con pasarelas de pago
- 📱 Aplicación móvil
- 🤖 Chatbot de soporte
- 📊 Dashboard avanzado con IA

---

<div align="center">
  <strong>🚀 Sistema WISP - Potenciando tu negocio de internet</strong><br>
  <em>Documentación actualizada: Septiembre 2025</em>
</div>