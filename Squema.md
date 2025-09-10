# Diagrama de Clases de la Base de Datos

```plantuml
@startuml

entity "ap_clientes" {
  * id : int
  * nombre : varchar(100)
  * ip : varchar(100)
  * version : varchar(100)
}

entity "ap_emisor" {
  * id : int
  * nombre : varchar(100)
  * direccion : varchar(255)
  * telefono : varchar(50)
}

entity "ap_receptor" {
  * id : int
  * nombre : varchar(100)
  * ip : varchar(100)
  * version : varchar(100)
}

entity "archivos" {
  * id : int
  * nombre : varchar(100)
  * tipo : varchar(100)
  * size : int
  * ruta : text
  * tabla : varchar(100)
  * object_id : int
}

entity "backups" {
  * id : bigint
  * archive : varchar(100)
  * size : varchar(50)
  * registration_date : datetime
}

entity "bills" {
  * id : bigint
  * userid : bigint
  * clientid : bigint
  * voucherid : bigint
  * serieid : bigint
  * internal_code : varchar(50)
  * correlative : bigint
  * date_issue : date
  * expiration_date : date
  * billed_month : date
  * subtotal : decimal(12,2)
  * discount : decimal(12,2)
  * total : decimal(12,2)
  * amount_paid : decimal(12,2)
  * remaining_amount : decimal(12,2)
  * type : bigint
  * sales_method : bigint
  * observation : text
  * promise_enabled : tinyint
  * promise_date : date
  * promise_set_date : date
  * promise_comment : varchar(512)
  * state : bigint
  * compromise_date : date
}

entity "business" {
  * id : bigint
  * documentid : bigint
  * ruc : char(11)
  * business_name : varchar(100)
  * tradename : varchar(100)
  * slogan : text
  * mobile : varchar(10)
  * mobile_refrence : varchar(10)
  * email : varchar(200)
  * password : varchar(200)
  * server_host : varchar(200)
  * port : varchar(50)
  * address : text
  * department : varchar(100)
  * province : varchar(100)
  * district : varchar(100)
  * ubigeo : char(6)
  * footer_text : text
  * currencyid : bigint
  * print_format : varchar(100)
  * logotyope : varchar(200)
  * logo_login : varchar(200)
  * logo_email : varchar(1000)
  * favicon : varchar(200)
  * country_code : varchar(20)
  * google_apikey : text
  * reniec_apikey : text
  * background : varchar(100)
  * whatsapp_api : varchar(100)
  * whatsapp_key : varchar(100)
}

entity "clients" {
  * id : bigint
  * names : varchar(100)
  * surnames : varchar(100)
  * documentid : bigint
  * document : varchar(15)
  * mobile : varchar(10)
  * mobile_optional : varchar(10)
  * email : varchar(100)
  * address : text
  * reference : text
  * note : varchar(255)
  * latitud : varchar(50)
  * longitud : varchar(50)
  * state : bigint
  * net_router : int
  * net_name : varchar(128)
  * net_password : varchar(128)
  * net_localaddress : varchar(64)
  * net_ip : varchar(64)
  * nap_cliente_id : int
  * ap_cliente_id : int
  * zonaid : bigint
}

entity "contracts" {
  * id : bigint
  * userid : bigint
  * clientid : bigint
  * internal_code : varchar(50)
  * payday : bigint
  * create_invoice : bigint
  * days_grace : bigint
  * discount : bigint