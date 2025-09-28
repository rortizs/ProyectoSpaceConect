# 🌐 Sistema de Gestión WISP/ISP

> **Plataforma integral para la administración de servicios de internet inalámbrico para clientes**

[![Version](https://img.shields.io/badge/version-1.5.0-blue.svg)](https://github.com/tu-usuario/tu-repo)
[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

## 📋 Descripción

Sistema de administración diseñado específicamente para emprendimientos WISP (Wireless Internet Service Provider) e ISP que buscan automatizar y optimizar la gestión de sus clientes, infraestructura de red y procesos de facturación.

## ✨ Características Principales

### 🔧 **Gestión de Clientes**

- Registro completo de clientes con geolocalización
- Gestión de contratos y planes de servicio
- Historial de pagos y facturación automática
- Estados de servicio (activo, suspendido, cancelado)

### 🌐 **Integración MikroTik**

- Conexión directa con routers MikroTik via API
- Gestión automática de Simple Queue y PPPoE
- Control de ancho de banda por cliente
- Corte y reactivación automática del servicio

### 💰 **Facturación Inteligente**

- Generación automática de facturas
- Múltiples métodos de pago
- Gestión de morosos y recordatorios
- Reportes financieros detallados

### 📡 **Infraestructura de Red**

- Mapeo de cajas NAP y puntos de acceso
- Gestión de zonas y routers
- Monitoreo de equipos en tiempo real
- Asignación de puertos y direcciones IP

### 🔒 **Filtrado de Contenido**

- Bloqueo de redes sociales y contenido adulto
- Políticas personalizables por cliente
- Gestión masiva de filtros
- Integración con DNS y proxy de MikroTik

### 📱 **Comunicación Automatizada**

- Notificaciones vía WhatsApp API
- Alertas de vencimiento de pagos
- Mensajes de corte y reactivación
- Soporte técnico integrado

## 🏗️ Módulos Principales

| Módulo          | Descripción                       | Funcionalidades                             |
| --------------- | --------------------------------- | ------------------------------------------- |
| **Clientes**    | Gestión integral de clientes      | Registro, contratos, facturación, estados   |
| **Red**         | Administración de infraestructura | Routers, zonas, NAPs, filtrado de contenido |
| **Facturación** | Sistema de cobros y pagos         | Facturas, recibos, reportes, morosos        |
| **Planes**      | Catálogo de servicios             | Velocidades, precios, promociones           |
| **Tickets**     | Soporte técnico                   | Incidencias, seguimiento, resolución        |
| **Reportes**    | Análisis y estadísticas           | Ingresos, clientes, red, rendimiento        |

## 🛠️ Tecnologías Utilizadas

### **Backend**

- **PHP 8.0+** - Lenguaje principal
- **MySQL 8.0+** - Base de datos
- **MVC Personalizado** - Arquitectura del framework
- **MikroTik API** - Integración con routers

### **Frontend**

- **HTML5/CSS3** - Estructura y estilos
- **JavaScript ES6+** - Interactividad
- **Bootstrap 4** - Framework CSS
- **jQuery** - Manipulación DOM

### **Librerías y Servicios**

- **PHPMailer** - Envío de correos
- **DOMPDF** - Generación de PDFs
- **WhatsApp API** - Mensajería
- **Google Maps API** - Geolocalización

## 🚀 Instalación y Configuración

### **Requisitos del Sistema**

```bash
- PHP >= 8.0
- MySQL >= 8.0
- Apache/Nginx
- Extensiones PHP: mysqli, curl, gd, zip
- Routers MikroTik con API habilitada
```

### **Instalación Local**

1. **Clonar el repositorio**

```bash
git clone https://github.com/tu-usuario/sistema-wisp.git
cd sistema-wisp
```

2. **Configurar base de datos**

```bash
# Crear base de datos
mysql -u root -p -e "CREATE DATABASE wisp_system"

# Importar esquema
mysql -u root -p wisp_system < sql/base_de_datos.sql
```

3. **Configurar conexión**

```bash
# Copiar archivo de configuración
cp Config/Config.example.php Config/Config.php

# Editar credenciales de base de datos
nano Config/Config.php
```

4. **Configurar servidor web**

```apache
# Apache Virtual Host
<VirtualHost *:80>
    DocumentRoot /path/to/sistema-wisp
    ServerName wisp.local

    <Directory /path/to/sistema-wisp>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

5. **Acceder al sistema**

```
URL: http://wisp.local
Usuario: admin
Contraseña: admin123
```

### **Despliegue en Producción**

1. **Servidor VPS/Dedicado**

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar LAMP Stack
sudo apt install apache2 mysql-server php8.0 php8.0-mysql php8.0-curl php8.0-gd -y

# Configurar SSL con Let's Encrypt
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d tu-dominio.com
```

2. **Configuración de seguridad**

```bash
# Configurar firewall
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable

# Configurar MySQL
sudo mysql_secure_installation
```

3. **Optimización**

```bash
# Configurar PHP para producción
sudo nano /etc/php/8.0/apache2/php.ini

# Ajustes recomendados:
memory_limit = 256M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
```

## 📚 Documentación Adicional

- [**Guía de Usuario**](docs/USER_GUIDE.md) - Manual completo del sistema
- [**API Documentation**](docs/API.md) - Endpoints y integración
- [**Esquema de Base de Datos**](Squema.md) - Estructura de datos
- [**Diagramas UML**](UML.md) - Arquitectura del sistema

## 🤝 Contribución

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -m 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

## 👨‍💻 Desarrollador

**Junior Espinoza**

- 🌐 Website: [www.sntelecom.net.pe](https://www.sntelecom.net.pe)
- 📧 Email: walterrengifo08@gmail.com
- 📱 WhatsApp: +51 999 220 735

---

<div align="center">
  <strong>🚀 Impulsa tu emprendimiento WISP con tecnología de vanguardia</strong>
</div>
