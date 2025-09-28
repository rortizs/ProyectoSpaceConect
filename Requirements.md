# Capítulo 4 – Desarrollo del Software

## 4.1 Herramientas y Tecnología Utilizadas

### Lenguajes de Programación
- **PHP 8.3+ +**: Lenguaje principal para el desarrollo del backend
- **JavaScript (ES6+)**: Para funcionalidades del frontend e interactividad
- **HTML5**: Estructura del contenido web
- **CSS3**: Estilos y diseño visual
- **SQL**: Gestión de base de datos MySQL

### Framework y Arquitectura
- **Framework MVC Personalizado**: Arquitectura propia basada en el patrón Modelo-Vista-Controlador
- **Patrón Service Layer**: Para lógica de negocio específica
- **Event-Driven Architecture**: Sistema de eventos y listeners para operaciones desacopladas
- **Repository Pattern**: Abstracción de acceso a datos mediante clases modelo

### Base de Datos
- **MySQL/MariaDB 10.11+**: Sistema de gestión de base de datos relacional
- **phpMyAdmin**: Interfaz web para administración de la base de datos
- **Codificación UTF-8**: Soporte completo para caracteres especiales

### Bibliotecas y Dependencias PHP
- **DomPDF**: Generación de documentos PDF (facturas, reportes)
- **PhpSpreadsheet**: Importación/exportación de archivos Excel
- **PHPMailer**: Envío de correos electrónicos
- **phpQRCode**: Generación de códigos QR para pagos
- **Verot Upload**: Manejo de subida de archivos e imágenes

### Frontend y UI/UX
- **Bootstrap 4+**: Framework CSS para diseño responsivo
- **jQuery**: Biblioteca JavaScript para manipulación del DOM
- **DataTables**: Plugin para tablas interactivas con filtros y paginación
- **TinyMCE**: Editor de texto enriquecido
- **Moment.js**: Manipulación y formato de fechas
- **Font Awesome**: Iconografía vectorial

### APIs y Servicios Externos
- **MikroTik RouterOS API**: Integración con routers para gestión de red
- **Google Maps API**: Geolocalización y mapas para ubicación de clientes

### Herramientas de Desarrollo
- **Git**: Control de versiones
- **Apache/Nginx**: Servidor web
- **Composer**: Gestión de dependencias (en subcarpetas específicas)
- **PlantUML**: Generación de diagramas UML

### Seguridad
- **AES-256-CBC**: Cifrado de datos sensibles
- **Validación de Sesiones**: Control de acceso por roles
- **Sanitización de Datos**: Prevención de inyecciones SQL y XSS
- **Encriptación de Contraseñas**: Para credenciales de routers y usuarios

## 4.1 Análisis de la Base de Datos

### Características Técnicas
- **Motor**: MySQL/MariaDB con InnoDB
- **Codificación**: UTF-8 (utf8mb4_unicode_ci)
- **Tamaño**: Aproximadamente 50+ tablas principales
- **Relaciones**: Múltiples claves foráneas y relaciones complejas

### Tablas Principales y su Propósito

#### Gestión de Clientes
- **`clients`**: Información personal y contacto de clientes
- **`contracts`**: Contratos de servicio asociados a clientes
- **`detail_contracts`**: Detalles específicos de cada contrato (servicios, precios)

#### Sistema de Facturación
- **`bills`**: Facturas generadas con estados y montos
- **`detail_bills`**: Líneas de detalle de cada factura
- **`payments`**: Registro de pagos recibidos
- **`vouchers`**: Tipos de comprobantes disponibles

#### Gestión de Servicios
- **`services`**: Planes de internet y servicios ofrecidos
- **`products`**: Productos físicos (equipos, materiales)
- **`categories`**: Clasificación de productos y servicios

#### Infraestructura de Red
- **`network_routers`**: Routers MikroTik registrados
- **`network_zones`**: Zonas geográficas de cobertura
- **`network_naps`**: Puntos de acceso de red (NAPs)

#### Soporte Técnico
- **`tickets`**: Tickets de soporte y reparaciones
- **`installations`**: Instalaciones programadas y ejecutadas
- **`incidents`**: Catálogo de tipos de incidencias

#### Administración del Sistema
- **`users`**: Usuarios del sistema (admin, técnicos, cobranzas)
- **`profiles`**: Perfiles de usuario con permisos específicos
- **`permissions`**: Matriz de permisos por módulo
- **`business`**: Configuración de la empresa

#### Archivos y Backups
- **`archivos`**: Registro de archivos subidos al sistema
- **`backups`**: Control de respaldos de la base de datos

### Relaciones Clave
1. **Cliente ↔ Contrato**: Relación uno a muchos
2. **Contrato ↔ Factura**: Generación automática mensual
3. **Cliente ↔ Router**: Asignación de equipos de red
4. **Factura ↔ Pago**: Control de cobranzas
5. **Usuario ↔ Tickets**: Asignación de técnicos

### Integridad Referencial
- Uso extensivo de claves foráneas
- Campos obligatorios para datos críticos
- Validaciones de estado en triggers y procedimientos

## 4.2 Análisis de Requerimientos del Software

### Requerimientos Funcionales

#### RF01: Gestión de Clientes
- **Descripción**: El sistema debe permitir registrar, modificar, consultar y eliminar información de clientes
- **Actores**: Administrador, Personal de Atención al Cliente
- **Criterios de Aceptación**:
  - Registrar datos personales, contacto y ubicación GPS
  - Validar documentos de identidad mediante API externa
  - Gestionar múltiples números de contacto
  - Visualización en mapa de ubicaciones

#### RF02: Gestión de Contratos de Servicio
- **Descripción**: Administrar contratos de servicios de internet para cada cliente
- **Actores**: Administrador, Personal de Ventas
- **Criterios de Aceptación**:
  - Asociar planes de internet a clientes
  - Definir fechas de pago y condiciones
  - Configurar descuentos y promociones
  - Control de estados del contrato

#### RF03: Facturación Automatizada
- **Descripción**: Generar facturas mensuales de forma automática
- **Actores**: Sistema (Cronjob), Administrador
- **Criterios de Aceptación**:
  - Facturación masiva mensual automática
  - Cálculo de prorrateos y descuentos
  - Generación de PDF con formato personalizable
  - Envío automático por email/WhatsApp

#### RF04: Gestión de Pagos y Cobranzas
- **Descripción**: Registrar pagos recibidos y controlar cartera vencida
- **Actores**: Personal de Cobranzas
- **Criterios de Aceptación**:
  - Registro de pagos parciales y completos
  - Múltiples formas de pago
  - Control de promesas de pago
  - Reportes de cobranzas

#### RF05: Integración con Routers MikroTik
- **Descripción**: Gestionar la infraestructura de red mediante API
- **Actores**: Administrador, Técnico de Redes
- **Criterios de Aceptación**:
  - Conexión automática a routers
  - Aprovisionamiento de servicios (PPPoE/Simple Queue)
  - Suspensión automática por falta de pago
  - Monitoreo de estado de equipos

#### RF06: Sistema de Tickets de Soporte
- **Descripción**: Gestionar solicitudes de soporte técnico
- **Actores**: Cliente, Técnico, Administrador
- **Criterios de Aceptación**:
  - Creación y asignación de tickets
  - Seguimiento de estado y resolución
  - Documentación con fotografías
  - Notificaciones de progreso

#### RF07: Gestión de Instalaciones
- **Descripción**: Controlar el proceso completo de instalación de servicios
- **Actores**: Técnico de Instalación
- **Criterios de Aceptación**:
  - Programación de instalaciones
  - Control de materiales utilizados
  - Documentación GPS y fotográfica
  - Integración con aprovisionamiento de red

#### RF08: Sistema de Notificaciones
- **Descripción**: Enviar notificaciones multicanal a clientes
- **Actores**: Sistema automático
- **Criterios de Aceptación**:
  - Notificaciones por email
  - Mensajes WhatsApp masivos
  - Recordatorios de pago
  - Confirmaciones de servicio

### Requerimientos No Funcionales

#### RNF01: Rendimiento
- **Descripción**: El sistema debe soportar al menos 1000 clientes simultáneos
- **Métricas**: Tiempo de respuesta < 3 segundos para consultas complejas

#### RNF02: Disponibilidad
- **Descripción**: Disponibilidad del 99.5% durante horarios comerciales
- **Consideraciones**: Backups automáticos, recuperación ante desastres

#### RNF03: Seguridad
- **Descripción**: Protección de datos sensibles y acceso por roles
- **Medidas**: 
  - Encriptación AES-256 para datos críticos
  - Autenticación por sesiones
  - Control de permisos granular por módulo

#### RNF04: Usabilidad
- **Descripción**: Interfaz intuitiva para usuarios no técnicos
- **Características**:
  - Diseño responsive para móviles
  - Tiempos de carga < 2 segundos
  - Compatibilidad con navegadores modernos

#### RNF05: Escalabilidad
- **Descripción**: Capacidad de crecimiento horizontal
- **Consideraciones**:
  - Arquitectura modular
  - Separación de responsabilidades
  - Optimización de consultas SQL

#### RNF06: Mantenibilidad
- **Descripción**: Facilidad para modificaciones y actualizaciones
- **Características**:
  - Código documentado
  - Patrones de diseño consistentes
  - Logs detallados para debugging

### Requerimientos del Sistema

#### Hardware Mínimo (Servidor)
- **CPU**: 4 cores, 2.0 GHz mínimo
- **RAM**: 8 GB mínimo, 16 GB recomendado
- **Almacenamiento**: 100 GB SSD para datos del sistema
- **Red**: Conexión estable 100 Mbps

#### Software Base
- **Sistema Operativo**: Linux (Ubuntu 20.04+ / CentOS 8+)
- **Servidor Web**: Apache 2.4+ o Nginx 1.18+
- **PHP**: Versión 7.2 o superior
- **MySQL**: Versión 8.0+ o MariaDB 10.4+

#### Conectividad Externa
- **Internet**: Conexión estable para APIs externas
- **Routers MikroTik**: Acceso por API en red local/VPN
- **Email**: Servidor SMTP configurado

## 4.3 Análisis y Diseño de Diagramas UML

### Diagramas Implementados

El sistema cuenta con un conjunto completo de diagramas UML que documentan tanto los procesos de negocio como la arquitectura técnica. Estos diagramas se encuentran disponibles en el archivo `UML.md` del proyecto.

#### 4.3.1 Diagramas de Casos de Uso

##### Diagrama General del Sistema ISP
Documenta las interacciones principales entre los actores del sistema:
- **Actores Identificados**:
  - Administrador: Gestión completa del sistema
  - Técnico: Instalaciones, soporte y mantenimiento
  - Personal de Cobranzas: Facturación y pagos
  - Cliente: Consulta de servicios y reportes

- **Casos de Uso Principales**:
  - Gestión de clientes y contratos
  - Generación y control de facturación
  - Aprovisionamiento de servicios de red
  - Sistema de tickets y soporte técnico

##### Diagrama Específico: Tickets e Incidencias
Detalla el flujo completo de atención al cliente:
- Creación y categorización de tickets
- Asignación automática de técnicos
- Seguimiento y resolución
- Notificaciones de estado

#### 4.3.2 Diagramas de Secuencia

##### Alta de Cliente y Aprovisionamiento
Documenta el proceso completo desde el registro hasta la activación:
1. Registro de datos del cliente
2. Creación del contrato de servicio
3. Aprovisionamiento en router MikroTik
4. Configuración de credenciales de red
5. Activación del servicio

##### Ciclo de Facturación Mensual
Describe el proceso automatizado de facturación:
1. Ejecución de tarea programada (cronjob)
2. Identificación de contratos activos
3. Cálculo de montos y descuentos
4. Generación de facturas
5. Envío de notificaciones

##### Registro de Pagos
Flujo de procesamiento de cobranzas:
1. Recepción del pago
2. Actualización de estado de factura
3. Registro en historial de pagos
4. Notificación al cliente

##### Gestión de Suspensión por Morosidad
Proceso automático de control de cartera:
1. Identificación de facturas vencidas
2. Validación de promesas de pago
3. Suspensión de servicio vía router
4. Actualización de estados

#### 4.3.3 Diagramas de Actividad

##### Ciclo de Facturación Automatizada
Representa el flujo de decisiones en la facturación mensual:
- Validación de contratos activos
- Bucles de procesamiento por cliente
- Cálculos de montos y descuentos
- Decisiones de notificación

##### Proceso de Instalación de Servicios
Documenta el ciclo completo de instalación:
- Programación y asignación de técnico
- Gestión de materiales e inventario
- Configuración física y de red
- Pruebas y documentación
- Activación final del servicio

##### Actualización Masiva de Planes en Routers
Proceso técnico de actualización de infraestructura:
- Identificación de routers afectados
- Conexiones paralelas a equipos
- Actualización de configuraciones
- Verificación y logging de resultados

#### 4.3.4 Cobertura de Módulos

Los diagramas UML cubren los siguientes módulos del sistema:

1. **Módulo de Clientes**: Gestión completa del ciclo de vida del cliente
2. **Módulo de Facturación**: Procesos automatizados y manuales
3. **Módulo de Red**: Integración con infraestructura MikroTik
4. **Módulo de Soporte**: Tickets e incidencias técnicas
5. **Módulo de Instalaciones**: Proceso completo de puesta en marcha
6. **Módulo de Pagos**: Control de cobranzas y cartera
7. **Tareas Automatizadas**: Cronjobs y procesos de background

### Metodología de Diseño

#### Análisis de Código Fuente
Los diagramas fueron generados mediante análisis directo del código fuente del proyecto, garantizando que representen fielmente la implementación actual.

#### Patrones Identificados
- **MVC (Model-View-Controller)**: Separación clara de responsabilidades
- **Service Layer**: Lógica de negocio encapsulada
- **Observer Pattern**: Sistema de eventos y listeners
- **Repository Pattern**: Abstracción de acceso a datos

#### Beneficios de la Documentación UML
1. **Comprensión Rápida**: Nuevos desarrolladores pueden entender el sistema
2. **Mantenimiento**: Facilita modificaciones y mejoras
3. **Comunicación**: Lenguaje común entre stakeholders técnicos y de negocio
4. **Planificación**: Base para futuras implementaciones y mejoras

### Herramientas Utilizadas

- **PlantUML**: Generación de diagramas mediante código
- **Análisis de Código**: Revisión manual de controllers, models y services
- **Documentación Existente**: Referencias cruzadas con CLAUDE.md y README.md

Los diagramas UML están disponibles en formato PlantUML en el archivo `UML.md`, permitiendo su renderizado en múltiples formatos (PNG, SVG, PDF) según las necesidades del proyecto.
