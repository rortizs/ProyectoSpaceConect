# 🔧 INSTRUCCIONES DE ACTUALIZACIÓN - SPACECONNECT

## 📋 PROBLEMA IDENTIFICADO

El error reportado donde "no se guardan los datos de clientes" se debe a que el sistema ha sido actualizado con nuevas funcionalidades de **Queue Tree** que requieren tablas adicionales en la base de datos que no existen en su instalación actual.

## 🚨 IMPORTANTE - LEA ANTES DE PROCEDER

**⚠️ HAGA UN RESPALDO COMPLETO DE SU BASE DE DATOS ANTES DE CONTINUAR**

```sql
-- Comando para hacer respaldo (ejecutar en phpMyAdmin o línea de comandos)
mysqldump -u [usuario] -p [nombre_base_datos] > respaldo_$(date +%Y%m%d_%H%M%S).sql
```

## 🛠️ SOLUCIÓN 1: MIGRACIÓN AUTOMÁTICA (RECOMENDADA)

### Paso 1: Subir archivo de migración
1. Suba el archivo `migrate_database.php` a la raíz de su proyecto
2. Asegúrese de que el archivo `sql/migration_queue_tree.sql` esté presente

### Paso 2: Ejecutar migración
1. Abra su navegador y vaya a: `http://su-dominio.com/migrate_database.php`
2. Ingrese la contraseña de seguridad: `MIGRATE2024!`
3. Haga clic en "Ejecutar Migración"
4. Espere a que aparezca el mensaje de éxito

### Paso 3: Verificar
1. Intente agregar un nuevo cliente
2. Verifique que se guarde correctamente
3. **ELIMINE** el archivo `migrate_database.php` por seguridad

## 🛠️ SOLUCIÓN 2: MIGRACIÓN MANUAL

Si prefiere ejecutar la migración manualmente:

### Paso 1: Acceder a phpMyAdmin
1. Vaya a su panel de control (cPanel, XAMPP, etc.)
2. Abra phpMyAdmin
3. Seleccione su base de datos

### Paso 2: Ejecutar SQL
1. Vaya a la pestaña "SQL"
2. Copie y pegue el contenido del archivo `sql/migration_queue_tree.sql`
3. Haga clic en "Continuar"

### Paso 3: Verificar tablas creadas
Verifique que se hayan creado las siguientes tablas:
- `queue_tree_policies`
- `client_queue_assignments` 
- `queue_tree_templates`

Y que se hayan agregado las columnas a la tabla `clients`:
- `net_ip`
- `nap_cliente_id`
- `ap_cliente_id`

## 🆕 INSTALACIÓN NUEVA (OPCIONAL)

Si prefiere hacer una instalación completamente nueva:

### Paso 1: Preparar
1. Haga respaldo de su base de datos actual
2. Cree una nueva base de datos vacía
3. Suba el archivo `installer.php` a la raíz del proyecto

### Paso 2: Ejecutar instalador
1. Vaya a: `http://su-dominio.com/installer.php`
2. Siga el asistente de 4 pasos:
   - **Paso 1:** Configuración de base de datos
   - **Paso 2:** Datos de empresa y administrador
   - **Paso 3:** Ejecutar instalación
   - **Paso 4:** Completado

### Paso 3: Migrar datos (si es necesario)
Si necesita migrar datos de la instalación anterior:
1. Exporte los datos importantes de la base de datos anterior
2. Impórtelos a la nueva instalación
3. Ajuste los IDs según sea necesario

## 🔍 VERIFICACIÓN POST-ACTUALIZACIÓN

Después de aplicar cualquiera de las soluciones:

### ✅ Pruebas a realizar:
1. **Agregar nuevo cliente:**
   - Vaya a Clientes → Agregar Cliente
   - Complete todos los campos
   - Verifique que se guarde correctamente

2. **Verificar funcionalidades existentes:**
   - Login de usuarios
   - Listado de clientes existentes
   - Generación de reportes
   - Configuración de servicios

3. **Nuevas funcionalidades disponibles:**
   - Gestión de Queue Tree (Menú → Queue Tree)
   - Asignación de políticas a clientes
   - Plantillas de configuración

## 🆘 SOLUCIÓN DE PROBLEMAS

### Error: "Table doesn't exist"
- **Causa:** La migración no se ejecutó correctamente
- **Solución:** Ejecute nuevamente la migración o verifique los permisos de base de datos

### Error: "Access denied"
- **Causa:** Permisos insuficientes de base de datos
- **Solución:** Verifique que el usuario tenga permisos CREATE, ALTER, INSERT

### Error: "File not found"
- **Causa:** Archivos de migración no están presentes
- **Solución:** Verifique que todos los archivos estén subidos correctamente

### Los clientes siguen sin guardarse
- **Causa:** Puede haber otros errores en el código
- **Solución:** 
  1. Active el modo debug en `Config/Config.php`
  2. Revise los logs de error del servidor
  3. Contacte soporte técnico con los mensajes de error específicos

## 📞 SOPORTE

Si encuentra problemas durante la actualización:

1. **Documente el error:**
   - Mensaje de error exacto
   - Pasos que realizó
   - Navegador utilizado

2. **Información del sistema:**
   - Versión de PHP
   - Versión de MySQL
   - Sistema operativo del servidor

3. **Archivos de log:**
   - Error log del servidor web
   - Error log de PHP
   - Cualquier mensaje en la consola del navegador

## 📝 NOTAS IMPORTANTES

- ✅ **La migración es segura** - Solo agrega tablas y columnas nuevas
- ✅ **No se pierden datos existentes** - Los datos actuales se mantienen intactos
- ✅ **Proceso reversible** - Se puede restaurar desde el respaldo si es necesario
- ⚠️ **Elimine archivos de instalación** después de completar el proceso
- 🔒 **Cambie contraseñas por defecto** después de la instalación

---

**Fecha de creación:** $(date)
**Versión del sistema:** SpaceConnect v2.0
**Tipo de actualización:** Migración de base de datos + Nuevas funcionalidades