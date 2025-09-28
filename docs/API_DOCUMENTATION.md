# 🔌 API Documentation - Sistema WISP

![Version](https://img.shields.io/badge/API-v1.0-blue.svg)
![Status](https://img.shields.io/badge/status-stable-green.svg)
![License](https://img.shields.io/badge/license-MIT-yellow.svg)

## 📋 Tabla de Contenidos

- [Introducción](#introducción)
- [Autenticación](#autenticación)
- [Endpoints de Clientes](#endpoints-de-clientes)
- [Endpoints de Planes](#endpoints-de-planes)
- [Endpoints de Facturación](#endpoints-de-facturación)
- [Endpoints de Red](#endpoints-de-red)
- [Endpoints de Reportes](#endpoints-de-reportes)
- [Webhooks](#webhooks)
- [Códigos de Error](#códigos-de-error)
- [Ejemplos de Integración](#ejemplos-de-integración)

---

## 🚀 Introducción

La API del Sistema WISP proporciona acceso programático a todas las funcionalidades del sistema, permitiendo integración con aplicaciones externas, automatización de procesos y desarrollo de aplicaciones personalizadas.

### Características de la API
- ✅ **RESTful**: Arquitectura REST estándar
- ✅ **JSON**: Formato de intercambio de datos
- ✅ **Autenticación**: JWT Token-based
- ✅ **Rate Limiting**: Control de velocidad
- ✅ **Versionado**: Compatibilidad hacia atrás
- ✅ **Documentación**: Swagger/OpenAPI

### URL Base
```
https://api.tu-dominio.com/v1/
```

### Formato de Respuesta
```json
{
  "success": true,
  "data": {},
  "message": "Operación exitosa",
  "timestamp": "2025-09-27T10:30:00Z",
  "version": "1.0.0"
}
```

---

## 🔐 Autenticación

### Obtener Token de Acceso

**Endpoint:** `POST /auth/login`

**Request:**
```json
{
  "username": "admin",
  "password": "password123",
  "remember": false
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_in": 3600,
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "username": "admin",
      "role": "administrator",
      "permissions": ["read", "write", "delete"]
    }
  },
  "message": "Autenticación exitosa"
}
```

### Uso del Token

**Header requerido en todas las peticiones:**
```http
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
Content-Type: application/json
```

### Renovar Token

**Endpoint:** `POST /auth/refresh`

**Request:**
```json
{
  "refresh_token": "refresh_token_here"
}
```

---

## 👥 Endpoints de Clientes

### Listar Clientes

**Endpoint:** `GET /clients`

**Parámetros de consulta:**
```
?page=1&limit=50&status=active&zone=1&search=juan
```

**Response:**
```json
{
  "success": true,
  "data": {
    "clients": [
      {
        "id": 1,
        "names": "Juan Carlos",
        "surnames": "Pérez López",
        "document": "12345678",
        "email": "juan@email.com",
        "mobile": "987654321",
        "address": "Av. Principal 123",
        "status": "active",
        "plan": {
          "id": 1,
          "name": "Plan Básico",
          "speed": "10/2"
        },
        "created_at": "2025-01-15T10:30:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 10,
      "total_records": 500,
      "per_page": 50
    }
  }
}
```

### Obtener Cliente por ID

**Endpoint:** `GET /clients/{id}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "names": "Juan Carlos",
    "surnames": "Pérez López",
    "document_type": "DNI",
    "document": "12345678",
    "email": "juan@email.com",
    "mobile": "987654321",
    "mobile_optional": "123456789",
    "address": "Av. Principal 123",
    "reference": "Frente al parque",
    "latitude": "-12.0464",
    "longitude": "-77.0428",
    "status": "active",
    "plan": {
      "id": 1,
      "name": "Plan Básico",
      "speed_download": 10,
      "speed_upload": 2,
      "price": 50.00
    },
    "network": {
      "router_id": 1,
      "local_ip": "192.168.1.100",
      "public_ip": "200.123.45.67",
      "username": "client001",
      "password": "encrypted_password"
    },
    "billing": {
      "last_payment": "2025-09-01",
      "next_due": "2025-10-01",
      "balance": 0.00,
      "status": "current"
    },
    "created_at": "2025-01-15T10:30:00Z",
    "updated_at": "2025-09-27T10:30:00Z"
  }
}
```

### Crear Cliente

**Endpoint:** `POST /clients`

**Request:**
```json
{
  "names": "María Elena",
  "surnames": "García Rodríguez",
  "document_type": "DNI",
  "document": "87654321",
  "email": "maria@email.com",
  "mobile": "987654322",
  "mobile_optional": "",
  "address": "Jr. Los Olivos 456",
  "reference": "Casa azul",
  "latitude": "-12.0500",
  "longitude": "-77.0450",
  "plan_id": 2,
  "router_id": 1,
  "zone_id": 1,
  "notes": "Cliente referido"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 251,
    "names": "María Elena",
    "surnames": "García Rodríguez",
    "network": {
      "username": "client251",
      "password": "auto_generated_password",
      "local_ip": "192.168.1.251",
      "public_ip": "200.123.45.68"
    }
  },
  "message": "Cliente creado exitosamente"
}
```

### Actualizar Cliente

**Endpoint:** `PUT /clients/{id}`

**Request:**
```json
{
  "email": "nuevo_email@email.com",
  "mobile": "987654323",
  "address": "Nueva dirección 789",
  "plan_id": 3
}
```

### Cambiar Estado de Cliente

**Endpoint:** `PATCH /clients/{id}/status`

**Request:**
```json
{
  "status": "suspended",
  "reason": "Mora en pagos",
  "notify": true
}
```

**Estados disponibles:**
- `active`: Servicio activo
- `suspended`: Servicio suspendido
- `cut`: Servicio cortado
- `inactive`: Cliente inactivo

### Eliminar Cliente

**Endpoint:** `DELETE /clients/{id}`

**Response:**
```json
{
  "success": true,
  "message": "Cliente eliminado exitosamente"
}
```

---

## 📋 Endpoints de Planes

### Listar Planes

**Endpoint:** `GET /plans`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Plan Básico",
      "description": "Internet básico para uso doméstico",
      "speed_download": 10,
      "speed_upload": 2,
      "price": 50.00,
      "currency": "PEN",
      "data_limit": null,
      "connection_type": "pppoe",
      "status": "active",
      "clients_count": 150,
      "created_at": "2025-01-01T00:00:00Z"
    }
  ]
}
```

### Crear Plan

**Endpoint:** `POST /plans`

**Request:**
```json
{
  "name": "Plan Premium",
  "description": "Internet de alta velocidad",
  "speed_download": 100,
  "speed_upload": 20,
  "price": 150.00,
  "currency": "PEN",
  "data_limit": null,
  "connection_type": "static",
  "mikrotik_config": {
    "queue_type": "default",
    "burst_limit": "120M/24M",
    "burst_threshold": "80M/16M",
    "burst_time": "8s/8s"
  }
}
```

---

## 💰 Endpoints de Facturación

### Generar Factura

**Endpoint:** `POST /billing/invoices`

**Request:**
```json
{
  "client_id": 1,
  "period": "2025-10",
  "services": [
    {
      "description": "Internet Plan Básico",
      "quantity": 1,
      "unit_price": 50.00,
      "total": 50.00
    }
  ],
  "due_date": "2025-10-15",
  "send_notification": true
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "invoice_id": "INV-2025-001234",
    "client_id": 1,
    "amount": 50.00,
    "tax": 9.00,
    "total": 59.00,
    "due_date": "2025-10-15",
    "status": "pending",
    "pdf_url": "https://api.tu-dominio.com/invoices/INV-2025-001234.pdf"
  },
  "message": "Factura generada exitosamente"
}
```

### Registrar Pago

**Endpoint:** `POST /billing/payments`

**Request:**
```json
{
  "invoice_id": "INV-2025-001234",
  "amount": 59.00,
  "payment_method": "cash",
  "payment_date": "2025-10-01",
  "reference": "PAGO-001",
  "notes": "Pago en efectivo"
}
```

### Listar Facturas

**Endpoint:** `GET /billing/invoices`

**Parámetros:**
```
?client_id=1&status=pending&from=2025-10-01&to=2025-10-31
```

---

## 🌐 Endpoints de Red

### Listar Routers

**Endpoint:** `GET /network/routers`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Router Principal",
      "ip_address": "192.168.1.1",
      "api_port": 8728,
      "status": "online",
      "clients_count": 45,
      "cpu_usage": 15,
      "memory_usage": 32,
      "uptime": "15d 8h 30m",
      "last_seen": "2025-09-27T10:25:00Z"
    }
  ]
}
```

### Estado del Router

**Endpoint:** `GET /network/routers/{id}/status`

**Response:**
```json
{
  "success": true,
  "data": {
    "status": "online",
    "cpu_usage": 15,
    "memory_usage": 32,
    "disk_usage": 45,
    "uptime": "15d 8h 30m",
    "interfaces": [
      {
        "name": "ether1",
        "status": "running",
        "rx_bytes": 1024000000,
        "tx_bytes": 512000000
      }
    ],
    "queues": {
      "total": 45,
      "active": 43,
      "disabled": 2
    }
  }
}
```

### Sincronizar con MikroTik

**Endpoint:** `POST /network/routers/{id}/sync`

**Response:**
```json
{
  "success": true,
  "data": {
    "clients_synced": 45,
    "queues_updated": 43,
    "new_clients": 2,
    "errors": 0
  },
  "message": "Sincronización completada"
}
```

---

## 📊 Endpoints de Reportes

### Reporte de Ingresos

**Endpoint:** `GET /reports/revenue`

**Parámetros:**
```
?from=2025-01-01&to=2025-12-31&group_by=month
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_revenue": 125000.00,
    "period": {
      "from": "2025-01-01",
      "to": "2025-12-31"
    },
    "breakdown": [
      {
        "period": "2025-01",
        "revenue": 10500.00,
        "invoices": 210,
        "payments": 195
      }
    ]
  }
}
```

### Reporte de Clientes

**Endpoint:** `GET /reports/clients`

**Response:**
```json
{
  "success": true,
  "data": {
    "total_clients": 500,
    "active": 450,
    "suspended": 30,
    "cut": 15,
    "inactive": 5,
    "new_this_month": 25,
    "churn_rate": 2.5
  }
}
```

### Reporte de Red

**Endpoint:** `GET /reports/network`

**Response:**
```json
{
  "success": true,
  "data": {
    "routers": {
      "total": 5,
      "online": 5,
      "offline": 0
    },
    "bandwidth": {
      "total_capacity": "1000M",
      "used": "750M",
      "utilization": 75
    },
    "clients_online": 425,
    "average_latency": 25
  }
}
```

---

## 🔗 Webhooks

### Configurar Webhook

**Endpoint:** `POST /webhooks`

**Request:**
```json
{
  "url": "https://tu-app.com/webhook",
  "events": ["client.created", "payment.received", "invoice.generated"],
  "secret": "webhook_secret_key",
  "active": true
}
```

### Eventos Disponibles

| Evento | Descripción |
|--------|-------------|
| `client.created` | Nuevo cliente creado |
| `client.updated` | Cliente actualizado |
| `client.status_changed` | Estado de cliente cambiado |
| `invoice.generated` | Factura generada |
| `payment.received` | Pago recibido |
| `router.offline` | Router desconectado |
| `router.online` | Router reconectado |

### Formato de Webhook

```json
{
  "event": "client.created",
  "timestamp": "2025-09-27T10:30:00Z",
  "data": {
    "client_id": 251,
    "names": "María Elena",
    "surnames": "García Rodríguez"
  },
  "signature": "sha256=hash_signature"
}
```

---

## ❌ Códigos de Error

### Códigos HTTP

| Código | Descripción |
|--------|-------------|
| `200` | Éxito |
| `201` | Creado |
| `400` | Solicitud incorrecta |
| `401` | No autorizado |
| `403` | Prohibido |
| `404` | No encontrado |
| `422` | Entidad no procesable |
| `429` | Demasiadas solicitudes |
| `500` | Error interno del servidor |

### Formato de Error

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Los datos proporcionados no son válidos",
    "details": [
      {
        "field": "email",
        "message": "El email no tiene un formato válido"
      }
    ]
  },
  "timestamp": "2025-09-27T10:30:00Z"
}
```

### Códigos de Error Personalizados

| Código | Descripción |
|--------|-------------|
| `AUTH_FAILED` | Autenticación fallida |
| `TOKEN_EXPIRED` | Token expirado |
| `VALIDATION_ERROR` | Error de validación |
| `CLIENT_NOT_FOUND` | Cliente no encontrado |
| `PLAN_NOT_FOUND` | Plan no encontrado |
| `ROUTER_OFFLINE` | Router desconectado |
| `INSUFFICIENT_PERMISSIONS` | Permisos insuficientes |
| `RATE_LIMIT_EXCEEDED` | Límite de velocidad excedido |

---

## 💻 Ejemplos de Integración

### PHP

```php
<?php
class WispAPI {
    private $baseUrl = 'https://api.tu-dominio.com/v1/';
    private $token;
    
    public function __construct($username, $password) {
        $this->authenticate($username, $password);
    }
    
    private function authenticate($username, $password) {
        $response = $this->request('POST', 'auth/login', [
            'username' => $username,
            'password' => $password
        ]);
        
        $this->token = $response['data']['token'];
    }
    
    public function getClients($page = 1, $limit = 50) {
        return $this->request('GET', "clients?page={$page}&limit={$limit}");
    }
    
    public function createClient($data) {
        return $this->request('POST', 'clients', $data);
    }
    
    private function request($method, $endpoint, $data = null) {
        $curl = curl_init();
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        ];
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $data ? json_encode($data) : null
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        return json_decode($response, true);
    }
}

// Uso
$api = new WispAPI('admin', 'password123');
$clients = $api->getClients();
?>
```

### JavaScript/Node.js

```javascript
class WispAPI {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
        this.token = null;
    }
    
    async authenticate(username, password) {
        const response = await fetch(`${this.baseUrl}/auth/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username, password })
        });
        
        const data = await response.json();
        this.token = data.data.token;
        return data;
    }
    
    async getClients(page = 1, limit = 50) {
        return this.request('GET', `clients?page=${page}&limit=${limit}`);
    }
    
    async createClient(clientData) {
        return this.request('POST', 'clients', clientData);
    }
    
    async request(method, endpoint, data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.token}`
            }
        };
        
        if (data) {
            options.body = JSON.stringify(data);
        }
        
        const response = await fetch(`${this.baseUrl}/${endpoint}`, options);
        return response.json();
    }
}

// Uso
const api = new WispAPI('https://api.tu-dominio.com/v1');
await api.authenticate('admin', 'password123');
const clients = await api.getClients();
```

### Python

```python
import requests
import json

class WispAPI:
    def __init__(self, base_url):
        self.base_url = base_url
        self.token = None
        self.session = requests.Session()
    
    def authenticate(self, username, password):
        response = self.session.post(
            f"{self.base_url}/auth/login",
            json={"username": username, "password": password}
        )
        data = response.json()
        self.token = data['data']['token']
        self.session.headers.update({
            'Authorization': f'Bearer {self.token}'
        })
        return data
    
    def get_clients(self, page=1, limit=50):
        response = self.session.get(
            f"{self.base_url}/clients",
            params={"page": page, "limit": limit}
        )
        return response.json()
    
    def create_client(self, client_data):
        response = self.session.post(
            f"{self.base_url}/clients",
            json=client_data
        )
        return response.json()

# Uso
api = WispAPI('https://api.tu-dominio.com/v1')
api.authenticate('admin', 'password123')
clients = api.get_clients()
```

---

## 🔄 Rate Limiting

### Límites por Endpoint

| Endpoint | Límite | Ventana |
|----------|--------|---------|
| `/auth/*` | 5 requests | 1 minuto |
| `/clients` | 100 requests | 1 minuto |
| `/billing/*` | 50 requests | 1 minuto |
| `/reports/*` | 20 requests | 1 minuto |

### Headers de Rate Limiting

```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1640995200
```

---

## 📝 Changelog

### v1.0.0 (2025-09-27)
- ✅ Lanzamiento inicial de la API
- ✅ Endpoints básicos de clientes
- ✅ Autenticación JWT
- ✅ Integración con MikroTik
- ✅ Sistema de webhooks

### Próximas Versiones
- 🔄 v1.1.0: Endpoints de inventario
- 🔄 v1.2.0: API de tickets y soporte
- 🔄 v1.3.0: Integración con pasarelas de pago

---

<div align="center">
  <strong>🔌 API Sistema WISP - Conectando tu negocio</strong><br>
  <em>Documentación actualizada: Septiembre 2025</em>
</div>