# UML del Sistema ISP (internet_online)

Este documento contiene el código PlantUML para generar diagramas que describen los flujos y funcionalidades principales del sistema ISP basado en PHP (MVC) con integración a routers MikroTik.


## 1) Diagrama de Casos de Uso

```plantuml
@startuml
left to right direction
skinparam actorStyle awesome
skinparam shadowing false
skinparam packageStyle rectangle

actor "Administrador" as Admin
actor "Técnico" as Tech
actor "Cobranzas" as Cashier
actor "Cliente" as Client

rectangle "Sistema ISP" {
  usecase "Gestionar Clientes" as UC_Clientes
  usecase "Gestionar Contratos" as UC_Contratos
  usecase "Gestionar Planes de Internet" as UC_Planes
  usecase "Gestionar Routers y Zonas" as UC_Routers
  usecase "Aprovisionar Servicio (MikroTik)" as UC_Provision
  usecase "Generar Facturas" as UC_Facturas
  usecase "Registrar Pagos" as UC_Pagos
  usecase "Suspender/Reactivar Servicio" as UC_Suspender
  usecase "Notificar por Email/WhatsApp" as UC_Notificar
  usecase "Reportes y Exportaciones" as UC_Reportes
}

Admin -- UC_Clientes
Admin -- UC_Contratos
Admin -- UC_Planes
Admin -- UC_Routers
Admin -- UC_Reportes

Tech -- UC_Provision
Tech -- UC_Suspender
Tech -- UC_Routers

Cashier -- UC_Facturas
Cashier -- UC_Pagos
Cashier -- UC_Reportes

Client -- UC_Pagos
Client -- UC_Notificar

UC_Contratos <-- UC_Clientes : <<incluye>>
UC_Provision <-- UC_Contratos : <<incluye>>
UC_Facturas <-- UC_Contratos : <<incluye>>
UC_Suspender .u.> UC_Facturas : <<extiende>>
UC_Notificar .d.> UC_Facturas : <<extiende>>
@enduml
```


## 2) Diagramas de Secuencia

### 2.1 Diagrama_Secuencia_AltadeClienteyAprovisionamientodelServicio
Basado en Customers controller, services de red y Router API.

```plantuml
@startuml
actor Admin as A
participant "Customers Controller" as C
participant "CustomersModel" as M
participant "ClientRouterService" as S
participant "Router (MikroTik)" as R
database "DB MySQL" as DB

A -> C: Enviar formulario de nuevo cliente/contrato
C -> M: create_client(datos)
M -> DB: INSERT clientes, contratos, detail_contracts
DB --> M: OK (ids creados)
M --> C: cliente/contrato creado

C -> S: provisionar_servicio(contractId)
S -> DB: SELECT plan, router, credenciales
DB --> S: datos red/plan
S -> R: conectar(ip, port, user, pass)
R --> S: sesión establecida
S -> R: crear perfil/queue PPPoE o Simple Queue
R --> S: OK
S --> C: provisioning OK
C --> A: Confirmación de alta exitosa
@enduml
```

### 2.2 DiagramaSecuencia_GeneracióndeFacturas(Ciclo Mensual)
Basado en Bills y/o Cronjob.

```plantuml
@startuml
participant "Cronjob" as CRON
participant "Bills Controller/Model" as B
database "DB MySQL" as DB
participant "BillInfoService" as BI
participant "Email/WhatsApp Service" as N

CRON -> B: generar_facturas(mes)
B -> DB: SELECT contratos activos
DB --> B: lista contratos
loop por contrato
  B -> BI: calcular_montos(contrato)
  BI --> B: subtotal, descuentos, total
  B -> DB: INSERT bill(contrato, mes, totales)
  B -> N: notificar(cliente, factura)
  N --> B: OK
end
B --> CRON: Resumen de facturación
@enduml
```

### 2.3 DiagramaSecuenca_RegistroPagodeFactura
Basado en Payments controller.

```plantuml
@startuml
actor "Cobranzas" as Cashier
participant "Payments Controller" as P
participant "PaymentsModel" as PM
participant "BillsModel" as BM
database "DB MySQL" as DB
participant "Notificador" as N

Cashier -> P: registrar_pago(id_factura, monto, forma)
P -> PM: add_payment(datos)
PM -> DB: INSERT payments
DB --> PM: OK
P -> BM: actualizar_estado_factura(id_factura)
BM -> DB: UPDATE bills (amount_paid, remaining_amount, state)
DB --> BM: OK
P -> N: enviar comprobante/confirmación
N --> P: OK
P --> Cashier: Pago registrado y factura actualizada
@enduml
```


## 3) Diagramas de Actividad

### 3.1 Ciclo_de_Facturación_Mensual

```plantuml
@startuml
start
:Determinar rango de facturación (mes actual);
:Obtener contratos activos;
if (¿Hay contratos?) then (Sí)
  repeat
    :Calcular cargos (plan, prorrateos, descuentos);
    :Generar factura (INSERT bills);
    :Notificar cliente (Email/WhatsApp);
  repeat while (quedan contratos?)
  :Generar reporte/resumen;
else (No)
  :No hay contratos activos;
endif
stop
@enduml
```

### 3.2 Actualización_de_Planes_en_Router(Internet->update_router_plans)

```plantuml
@startuml
start
:Recibir id del plan a actualizar;
:Validar existencia del plan;
:Consultar routers asociados a clientes del plan;
if (¿Hay routers?) then (Sí)
  fork
    :Para cada router encontrado;
    :Abrir sesión API MikroTik;
    :Actualizar perfiles/queues del plan;
    :Cerrar sesión;
  fork again
    :Registrar resultados por router;
  end fork
  :Resumen de actualizaciones;
else (No)
  :No hay routers vinculados al plan;
endif
stop
@enduml
```


## 4) Diagramas Adicionales por Módulo

### 4.1 Diagrama de Casos de Uso: Sistema_de_Tickets_e_Incidencias

```plantuml
@startuml
left to right direction
skinparam actorStyle awesome
skinparam shadowing false
skinparam packageStyle rectangle

actor "Administrador" as Admin
actor "Técnico" as Tech
actor "Cliente" as Client

rectangle "Sistema de Tickets" {
  usecase "Crear Ticket de Soporte" as UC_CreateTicket
  usecase "Asignar Técnico" as UC_AssignTech
  usecase "Gestionar Tickets Actuales" as UC_ManageTickets
  usecase "Atender Ticket" as UC_AttendTicket
  usecase "Finalizar Ticket" as UC_ResolveTicket
  usecase "Visualizar Ubicación Cliente" as UC_ViewLocation
  usecase "Notificar Resolución" as UC_NotifyResolution
  usecase "Generar Reporte Soporte" as UC_GenerateReport
  usecase "Categorizar Incidentes" as UC_CategorizeIncident
}

Admin -- UC_CreateTicket
Admin -- UC_ManageTickets
Admin -- UC_AssignTech
Admin -- UC_CategorizeIncident
Admin -- UC_GenerateReport

Tech -- UC_AttendTicket
Tech -- UC_ResolveTicket
Tech -- UC_ViewLocation

Client -- UC_CreateTicket
Client -- UC_NotifyResolution

UC_AssignTech ..> UC_ManageTickets : <<extends>>
UC_AttendTicket ..> UC_ViewLocation : <<includes>>
UC_ResolveTicket ..> UC_NotifyResolution : <<includes>>
@enduml
```

### 4.2 Diagrama de Secuencia: Proceso de Atención de Ticket

```plantuml
@startuml
actor "Técnico" as Tech
participant "Tickets Controller" as TC
participant "TicketsModel" as TM
database "DB MySQL" as DB
participant "NotificaciónService" as NS
participant "ClienteModel" as CM

Tech -> TC: attend(id_ticket)
TC -> TM: select_record(id_ticket)
TM -> DB: SELECT * FROM tickets WHERE id = ?
DB --> TM: ticket info
TC -> TM: open_ticket(id, fecha, estado=3)
TM -> DB: UPDATE tickets SET state=3, datetime=NOW()
DB --> TM: OK
TC -> TM: reassign_technical(id, tech_id)
TM -> DB: UPDATE tickets SET technical=?, state=3
DB --> TM: OK

== Resolución del Ticket ==

Tech -> TC: finalize(id_ticket, comentario, solución)
TC -> TM: finalize_ticket(id, fecha, comentario, solución)
TM -> DB: UPDATE tickets SET closing_date=NOW(), state=6, solution=?
DB --> TM: OK
TC -> CM: get_client_info(client_id)
CM -> DB: SELECT contact FROM clients WHERE id=?
DB --> CM: client contact
TC -> NS: notificar_resolución(contacto, ticket_id)
NS --> Tech: Notificación enviada
TC --> Tech: Ticket resuelto con éxito
@enduml
```

### 4.3 Diagrama de Actividad: Ciclo de Vida de una Instalación

```plantuml
@startuml
start
:Registrar nueva instalación;
:Asignar técnico responsable;

if (¿Requiere materiales?) then (Sí)
  :Registrar materiales requeridos;
  :Generar orden de salida de inventario;
else (No)
  :Continuar con proceso;
endif

:Técnico agenda visita;
:Técnico atiende instalación;

fork
  :Realizar configuración física;
  :Instalar equipos y cableado;
fork again
  :Documentar con fotografías;
  :Registrar coordenadas GPS;
end fork

:Conectar a router de zona;
:Configurar credenciales de red;
:Ejecutar pruebas de conectividad;

if (¿Pruebas exitosas?) then (Sí)
  :Finalizar instalación como exitosa;
  :Activar cliente en sistema;
  :Generar contrato y factura inicial;
else (No)
  :Registrar fallas técnicas;
  :Agendar revisión;
endif

:Notificar al cliente;
stop
@enduml
```

### 4.4 Diagrama de Secuencia: Monitoreo y Gestión de Routers

```plantuml
@startuml
actor "Administrador" as Admin
participant "Network Controller" as NC
participant "Router Class" as RC
participant "MikroTik Router" as MT
database "DB MySQL" as DB

Admin -> NC: routers()
NC -> DB: SELECT routers, zones
DB --> NC: datos routers

loop para cada router
  NC -> RC: new Router(ip, port, user, pass)
  RC -> MT: conectar()
  MT --> RC: sesión API
  RC -> MT: getSystemResources()
  MT --> RC: info (identity, board, version)
  RC --> NC: estado conexión y recursos
end

NC --> Admin: Vista de gestión de routers

== Agregar Router ==

Admin -> NC: add_router(datos)
NC -> RC: new Router(ip, port, user, pass)
RC -> MT: prueba de conexión
MT --> RC: conexión exitosa
RC --> NC: conexión confirmada
NC -> DB: INSERT network_routers
DB --> NC: OK
NC --> Admin: Router agregado exitosamente
@enduml
```

### 4.5 Diagrama de Actividad: Tarea Automatizada de Facturación

```plantuml
@startuml
start
:Cronjob ejecuta Tasks->invoice_receipts();
:Obtener contratos activos del mes actual;

if (¿Existen contratos?) then (Sí)
  repeat
    :Obtener fecha de pago del cliente;
    :Calcular fecha de vencimiento;
    :Generar número de factura y correlativo;
    :Obtener servicios contratados;
    
    fork
      :Calcular totales;
      :Generar registro de factura;
    fork again
      :Preparar descripción de servicios;
      :Crear detalle de factura;
    end fork
    
    if (¿Cliente tiene email/WhatsApp?) then (Sí)
      :Notificar al cliente;
    else (No)
      :Continuar sin notificación;
    endif
  repeat while (quedan contratos?)  
else (No)
  :No hay contratos para facturar;
endif

:Retornar resumen de facturas generadas;
stop
@enduml
```

### 4.6 Diagrama de Secuencia: Gestión de Suspensión por Falta de Pago

```plantuml
@startuml
participant "Cronjob" as CRON
participant "Tasks Controller" as TC
participant "Router Class" as RC
participant "MikroTik Router" as MT
database "DB MySQL" as DB

CRON -> TC: expired_bills()
TC -> DB: SELECT facturas vencidas
DB --> TC: lista facturas

loop por cada factura vencida
  TC -> TC: verificar fecha expiración
  
  alt Tiene promesa de pago activa
    TC -> TC: verificar fecha promesa
    note right
    Si la promesa no ha expirado,
    no se suspende el servicio
    end note
  else Sin promesa o promesa expirada
    TC -> DB: UPDATE bills SET promise_enabled=0
    DB --> TC: OK
    TC -> DB: SELECT client, router info
    DB --> TC: datos cliente y router
    TC -> RC: new Router(ip, port, user, pass)
    RC -> MT: conectar()
    MT --> RC: sesión API
    RC -> MT: APIAddFirewallAddress(IP, "moroso")
    MT --> RC: dirección agregada
    TC -> DB: UPDATE bills SET state=3
    DB --> TC: OK
  end
end

TC --> CRON: Facturas procesadas
@enduml
```

## 5) Diagramas de Casos de Uso Completos por Módulo

### 5.1 Diagrama_Casos_de_Uso_Gestión_de_Clientes

```plantuml
@startuml
left to right direction
skinparam actorStyle awesome
skinparam shadowing false
skinparam packageStyle rectangle

actor "Administrador" as Admin
actor "Técnico" as Tech
actor "Cobranzas" as Cashier

rectangle "Gestión de Clientes" {
  usecase "Registrar Cliente" as UC_RegisterClient
  usecase "Consultar Cliente" as UC_SearchClient
  usecase "Editar Cliente" as UC_EditClient
  usecase "Eliminar Cliente" as UC_DeleteClient
  usecase "Consultar Documento (RENIEC/API)" as UC_ValidateDoc
  usecase "Asignar Ubicación GPS" as UC_SetLocation
  usecase "Gestionar Fotos de Cliente" as UC_ManagePhotos
  usecase "Cambiar Estado Cliente" as UC_ChangeStatus
  usecase "Ver Historial de Servicios" as UC_ViewHistory
}

Admin -- UC_RegisterClient
Admin -- UC_SearchClient
Admin -- UC_EditClient
Admin -- UC_DeleteClient
Admin -- UC_ChangeStatus
Admin -- UC_ViewHistory

Tech -- UC_SearchClient
Tech -- UC_SetLocation
Tech -- UC_ManagePhotos
Tech -- UC_ViewHistory

Cashier -- UC_SearchClient
Cashier -- UC_ViewHistory

UC_RegisterClient ..> UC_ValidateDoc : <<includes>>
UC_RegisterClient ..> UC_SetLocation : <<extends>>
UC_EditClient ..> UC_ManagePhotos : <<extends>>
@enduml
```

### 5.2 Diagrama_Casos_de_Uso_Gestión_de_Contratos

```plantuml
@startuml
left to right direction
skinparam actorStyle awesome
skinparam shadowing false
skinparam packageStyle rectangle

actor "Administrador" as Admin
actor "Técnico" as Tech
actor "Cobranzas" as Cashier

rectangle "Gestión de Contratos" {
  usecase "Crear Contrato" as UC_CreateContract
  usecase "Editar Contrato" as UC_EditContract
  usecase "Renovar Contrato" as UC_RenewContract
  usecase "Cancelar Contrato" as UC_CancelContract
  usecase "Suspender Servicio" as UC_SuspendService
  usecase "Reactivar Servicio" as UC_ReactivateService
  usecase "Asignar Plan Internet" as UC_AssignPlan
  usecase "Cambiar Plan" as UC_ChangePlan
  usecase "Configurar Router" as UC_ConfigureRouter
  usecase "Ver Estado de Contrato" as UC_ViewStatus
}

Admin -- UC_CreateContract
Admin -- UC_EditContract
Admin -- UC_RenewContract
Admin -- UC_CancelContract
Admin -- UC_ChangePlan
Admin -- UC_ViewStatus

Tech -- UC_SuspendService
Tech -- UC_ReactivateService
Tech -- UC_ConfigureRouter
Tech -- UC_ViewStatus

Cashier -- UC_SuspendService
Cashier -- UC_ViewStatus

UC_CreateContract ..> UC_AssignPlan : <<includes>>
UC_CreateContract ..> UC_ConfigureRouter : <<includes>>
UC_ChangePlan ..> UC_ConfigureRouter : <<includes>>
UC_SuspendService ..> UC_ConfigureRouter : <<includes>>
UC_ReactivateService ..> UC_ConfigureRouter : <<includes>>
@enduml
```

### 5.3 Diagrama_Casos_de_Uso_Gestión_Financiera

```plantuml
@startuml
left to right direction
skinparam actorStyle awesome
skinparam shadowing false
skinparam packageStyle rectangle

actor "Administrador" as Admin
actor "Cobranzas" as Cashier
actor "Cliente" as Client

rectangle "Gestión Financiera" {
  usecase "Generar Facturas" as UC_GenerateBills
  usecase "Registrar Pagos" as UC_RegisterPayments
  usecase "Consultar Estado de Cuenta" as UC_CheckAccount
  usecase "Emitir Comprobantes" as UC_IssueReceipts
  usecase "Gestionar Promesas de Pago" as UC_ManagePromises
  usecase "Aplicar Descuentos" as UC_ApplyDiscounts
  usecase "Generar Reportes Financieros" as UC_FinancialReports
  usecase "Exportar a Excel" as UC_ExportExcel
  usecase "Enviar Notificaciones de Pago" as UC_SendNotifications
  usecase "Configurar Métodos de Pago" as UC_ConfigurePayments
}

Admin -- UC_GenerateBills
Admin -- UC_ApplyDiscounts
Admin -- UC_FinancialReports
Admin -- UC_ConfigurePayments

Cashier -- UC_RegisterPayments
Cashier -- UC_CheckAccount
Cashier -- UC_IssueReceipts
Cashier -- UC_ManagePromises
Cashier -- UC_ExportExcel
Cashier -- UC_SendNotifications

Client -- UC_CheckAccount

UC_GenerateBills ..> UC_SendNotifications : <<extends>>
UC_RegisterPayments ..> UC_IssueReceipts : <<includes>>
UC_FinancialReports ..> UC_ExportExcel : <<extends>>
@enduml
```

### 5.4 Diagrama_Casos_de_Uso_Gestión_de_Red

```plantuml
@startuml
left to right direction
skinparam actorStyle awesome
skinparam shadowing false
skinparam packageStyle rectangle

actor "Administrador" as Admin
actor "Técnico" as Tech

rectangle "Gestión de Red" {
  usecase "Configurar Routers" as UC_ConfigRouters
  usecase "Gestionar Zonas de Red" as UC_ManageZones
  usecase "Crear Planes de Internet" as UC_CreatePlans
  usecase "Monitorear Estado de Red" as UC_MonitorNetwork
  usecase "Gestionar Access Points" as UC_ManageAP
  usecase "Configurar QoS/Bandwidth" as UC_ConfigureQoS
  usecase "Gestionar NAP (Cajas)" as UC_ManageNAP
  usecase "Filtrado de Contenido" as UC_ContentFilter
  usecase "Backup de Configuraciones" as UC_BackupConfigs
  usecase "Actualizar Firmware" as UC_UpdateFirmware
}

Admin -- UC_ConfigRouters
Admin -- UC_ManageZones
Admin -- UC_CreatePlans
Admin -- UC_ManageAP
Admin -- UC_ManageNAP
Admin -- UC_ContentFilter
Admin -- UC_BackupConfigs

Tech -- UC_MonitorNetwork
Tech -- UC_ConfigureQoS
Tech -- UC_UpdateFirmware

UC_ConfigRouters ..> UC_BackupConfigs : <<extends>>
UC_CreatePlans ..> UC_ConfigureQoS : <<includes>>
UC_ManageAP ..> UC_ManageZones : <<includes>>
UC_ContentFilter ..> UC_ConfigRouters : <<includes>>
@enduml
```

### 5.5 Diagrama_Casos_de_Uso_Sistema_de_Inventarios

```plantuml
@startuml
left to right direction
skinparam actorStyle awesome
skinparam shadowing false
skinparam packageStyle rectangle

actor "Administrador" as Admin
actor "Técnico" as Tech

rectangle "Sistema de Inventarios" {
  usecase "Registrar Productos" as UC_RegisterProducts
  usecase "Gestionar Entradas" as UC_ManageIncome
  usecase "Gestionar Salidas" as UC_ManageDepartures
  usecase "Consultar Stock" as UC_CheckStock
  usecase "Generar Kardex" as UC_GenerateKardex
  usecase "Asignar Material a Instalación" as UC_AssignMaterial
  usecase "Control de Inventario Mínimo" as UC_MinimumControl
  usecase "Reportes de Inventario" as UC_InventoryReports
  usecase "Valorización de Stock" as UC_StockValuation
}

Admin -- UC_RegisterProducts
Admin -- UC_ManageIncome
Admin -- UC_ManageDepartures
Admin -- UC_CheckStock
Admin -- UC_GenerateKardex
Admin -- UC_MinimumControl
Admin -- UC_InventoryReports
Admin -- UC_StockValuation

Tech -- UC_CheckStock
Tech -- UC_AssignMaterial

UC_ManageDepartures ..> UC_AssignMaterial : <<extends>>
UC_GenerateKardex ..> UC_StockValuation : <<includes>>
UC_CheckStock ..> UC_MinimumControl : <<extends>>
@enduml
```

### 5.6 Diagrama_Casos_de_Uso_Comunicaciones

```plantuml
@startuml
left to right direction
skinparam actorStyle awesome
skinparam shadowing false
skinparam packageStyle rectangle

actor "Administrador" as Admin
actor "Cobranzas" as Cashier
actor "Cliente" as Client

rectangle "Sistema de Comunicaciones" {
  usecase "Enviar Email" as UC_SendEmail
  usecase "Enviar WhatsApp" as UC_SendWhatsApp
  usecase "Crear Campañas" as UC_CreateCampaigns
  usecase "Gestionar Plantillas" as UC_ManageTemplates
  usecase "Programar Envíos" as UC_ScheduleSending
  usecase "Notificaciones Automáticas" as UC_AutoNotifications
  usecase "Recordatorios de Pago" as UC_PaymentReminders
  usecase "Confirmaciones de Servicio" as UC_ServiceConfirmations
  usecase "Reportes de Envíos" as UC_SendingReports
}

Admin -- UC_CreateCampaigns
Admin -- UC_ManageTemplates
Admin -- UC_ScheduleSending
Admin -- UC_SendingReports

Cashier -- UC_SendEmail
Cashier -- UC_SendWhatsApp
Cashier -- UC_PaymentReminders

Client -- UC_ServiceConfirmations

UC_CreateCampaigns ..> UC_SendEmail : <<includes>>
UC_CreateCampaigns ..> UC_SendWhatsApp : <<includes>>
UC_AutoNotifications ..> UC_PaymentReminders : <<extends>>
UC_AutoNotifications ..> UC_ServiceConfirmations : <<extends>>
@enduml
```

## 6) Diagramas de Secuencia Adicionales para Tickets e Incidencias

### 6.1 Diagrama_Secuencia_Creación_de_Ticket_por_Cliente

```plantuml
@startuml
actor "Cliente" as Client
participant "Tickets Controller" as TC
participant "TicketsModel" as TM
participant "ClientsModel" as CM
database "DB MySQL" as DB
participant "NotificationService" as NS

Client -> TC: crear_ticket(descripción, tipo)
TC -> CM: verificar_cliente(client_id)
CM -> DB: SELECT * FROM clients WHERE id=?
DB --> CM: datos cliente
CM --> TC: cliente verificado

TC -> TM: create_ticket(client_id, descripción, tipo)
TM -> DB: INSERT INTO tickets (client_id, description, type, state=1)
DB --> TM: ticket_id generado

TC -> TM: generar_codigo_ticket(ticket_id)
TM -> DB: UPDATE tickets SET code=? WHERE id=?
DB --> TM: OK

TC -> NS: notificar_nuevo_ticket(ticket_id, admin_email)
NS --> TC: notificación enviada

TC --> Client: Ticket creado exitosamente (código: #12345)
@enduml
```

### 6.2 Diagrama_Secuencia_Asignación_Automática_de_Técnico

```plantuml
@startuml
participant "System" as SYS
participant "Tickets Controller" as TC
participant "TicketsModel" as TM
participant "UsersModel" as UM
database "DB MySQL" as DB
participant "NotificationService" as NS

SYS -> TC: auto_assign_technician(ticket_id)
TC -> TM: get_ticket_location(ticket_id)
TM -> DB: SELECT client location FROM tickets JOIN clients
DB --> TM: coordenadas GPS

TC -> UM: get_available_technicians_by_zone(zone)
UM -> DB: SELECT users WHERE profile=2 AND state=1
DB --> UM: lista técnicos disponibles

TC -> TC: calcular_distancia_técnicos(coordenadas, técnicos)
TC -> TC: seleccionar_técnico_más_cercano()

TC -> TM: assign_technician(ticket_id, tech_id)
TM -> DB: UPDATE tickets SET technical=?, state=2
DB --> TM: OK

TC -> NS: notificar_asignación(tech_id, ticket_id)
NS --> TC: notificación enviada

TC --> SYS: Técnico asignado automáticamente
@enduml
```

### 6.3 Diagrama_Actividad_Gestión_Completa_de_Incidencias

```plantuml
@startuml
start
:Incidencia reportada;
:Clasificar tipo de incidencia;

if (¿Es crítica?) then (Sí)
  :Prioridad ALTA;
  :Notificar inmediatamente;
else (No)
  :Prioridad NORMAL;
endif

:Identificar cliente afectado;
:Obtener ubicación GPS;
:Consultar historial técnico;
:Asignar técnico disponible;
:Notificar al técnico;

partition "Atención en Campo" {
  :Técnico atiende incidencia;
  :Diagnosticar problema;
  
  if (¿Requiere materiales?) then (Sí)
    :Solicitar materiales de inventario;
    :Esperar disponibilidad;
  else (No)
    :Continuar con reparación;
  endif
  
  :Implementar solución;
  :Probar funcionamiento;
  
  if (¿Solución exitosa?) then (Sí)
    :Documentar solución aplicada;
    :Tomar fotografías de evidencia;
    :Finalizar ticket;
    :Notificar resolución al cliente;
  else (No)
    :Escalar a técnico especializado;
    :Documentar intentos realizados;
    note right
      El ticket permanece abierto
      hasta resolución completa
    end note
  endif
}

:Actualizar base de conocimiento;
:Generar reporte de incidencia;
stop
@enduml
```

## Notas
- Los nombres de clases/participantes son representativos, alineados con los directorios Controllers, Models, Services y Libraries/MikroTik.
- Puedes copiar cualquier bloque y renderizarlo con tu extensión/servidor de PlantUML preferido.
- Estos diagramas complementan los principales y proporcionan una visión detallada de los módulos específicos del sistema ISP.

