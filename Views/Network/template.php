<!-- 
CAMPOS REMOVIDOS PARA SIMPLIFICACIÓN:
- Router: Campo para seleccionar router MikroTik
- Modo de asignación: Configuración de asignación de IP
- Nombre Simple Queue: Configuración de QoS
- Contraseña PPPoE: Autenticación PPPoE
- Local Address PPPoE: Dirección local para PPPoE

Para reactivar estos campos, descomente las secciones correspondientes y 
asegúrese de que las funciones JavaScript estén disponibles en network.js
-->

<!-- CAMPOS REMOVIDOS - COMENTADOS PARA REFERENCIA FUTURA
<div class="form-group row m-b-10">
  <label class="col-md-3 text-lg-right col-form-label">Router <span class="text-danger">*</span></label>
  <div class="col-md-4">
    <div class="input-group">
      <select class="form-control" name="netRouter" id="netRouter">
      </select>
    </div>
  </div>
</div>

<div class="form-group row m-b-10">
  <label class="col-md-3 text-lg-right col-form-label">
  Modo de asignación <span class="text-danger">*</span>
  </label>
  <div class="col-md-4">
    <input type="text" class="form-control" id="netZone" maxlength="120" style="width:100%;" disabled>
  </div>
</div>

<div class="form-group row m-b-10">
  <label class="col-md-3 text-lg-right col-form-label">
    Nombre Simple Queue <span class="text-danger">*</span>
  </label>
  <div class="col-md-4">
    <input type="text" class="form-control" id="netName" name="netName" maxlength="100" style="width:100%;">
    <input type="hidden" class="form-control" id="netNameId">
  </div>
</div>

<div class="form-group row m-b-10">
  <label class="col-md-3 text-lg-right col-form-label">
    Contraseña PPPoE <span class="text-danger">*</span>
  </label>
  <div class="col-md-4">
    <div class="input-append input-group">
      <input type="password" class="form-control" id="netPassword" name="netPassword">
      <span tabindex="100" onclick="refreshPassword()" title="Generar contraseña"
        class="add-on input-group-addon refresh-password" style="cursor: pointer;">
        <i class="icon-refresh glyphicon far fa-refresh"></i>
      </span>
      <span tabindex="100" title="Mostrar/ocultar contraseña" class="add-on input-group-addon toggle-password"
        style="cursor: pointer;">
        <i class="icon-eye-open glyphicon far fa-eye-slash"></i>
      </span>
    </div>
  </div>
</div>
FIN CAMPOS REMOVIDOS -->

<div class="form-group row m-b-10">
  <label class="col-md-3 text-lg-right col-form-label">
    IP <span class="text-danger">*</span>
  </label>
  <div class="col-md-4">
    <input type="text" class="form-control" name="netIP" id="netIP" 
           placeholder="192.168.4.100" 
           pattern="^(\d{1,3}\.){3}\d{1,3}$"
           title="Ingrese una dirección IP válida (ej: 192.168.4.100)"
           maxlength="15">
    <small class="text-muted">Formato: 192.168.4.100</small>
  </div>
</div>

<!-- CAMPO REMOVIDO: Local Address PPPoE
<div class="form-group row m-b-10">
  <label class="col-md-3 text-lg-right col-form-label">
    Local Address PPPoE <span class="text-danger">*</span>
  </label>
  <div class="col-md-4">
    <input type="text" class="form-control" id="netLocalAddress" name="netLocalAddress" maxlength="100"
      style="width:100%;">
  </div>
</div>
-->

<!-- AP Cliente simplificado - solo texto simple -->
<div class="form-group row m-b-10">
  <label class="col-md-3 text-lg-right col-form-label">Marca AP Cliente</label>
  <div class="col-md-4">
    <input type="text" class="form-control" name="ap_cliente_brand" id="ap_cliente_brand" 
           placeholder="Ingrese marca del AP Cliente" maxlength="100">
  </div>
</div>

<!-- CAMPOS REMOVIDOS: Caja Nap y funcionalidades de búsqueda
<div class="form-group row m-b-10" id="content-ap_cliente_id">
  <label class="col-md-3 text-lg-right col-form-label">AP Cliente</label>
  <div class="col-md-4">
    <div class="search-input" id="container-search-apcliente">
      <input type="hidden" name="ap_cliente_id" id="ap_cliente_value" />
      <input type="text" id="ap_cliente_id" placeholder="BUSCAR AP CLIENTE" onkeyup="search_ap_cliente()" />
      <ul id="box-search-apcliente" class="autocom-box"></ul>
      <div class="icon"><i class="fas fa-search"></i></div>
    </div>
  </div>
</div>

<div class="form-group row m-b-10" id="content-nap_cliente_id">
  <label class="col-md-3 text-lg-right col-form-label">Caja Nap</label>
  <div class="col-md-4">
    <div class="search-input" id="container-search-nap">
      <input type="hidden" name="nap_cliente_id" id="nap_cliente_value" />
      <input type="text" id="nap_cliente_id" placeholder="BUSCAR CAJA NAP" onkeyup="search_nap_cliente()" />
      <ul id="box-search-nap" class="autocom-box"></ul>
      <div class="icon"><i class="fas fa-search"></i></div>
    </div>
  </div>
</div>
-->

<!-- CAMPOS REMOVIDOS: Queue Tree y QoS
<div class="form-group row m-b-10" id="content-queue_tree" style="display: none;">
  <label class="col-md-3 text-lg-right col-form-label">Política Queue Tree <span class="text-danger">*</span></label>
  <div class="col-md-4">
    <select class="form-control" id="queue_tree_policy" name="queue_tree_policy">
      <option value="">Seleccionar política</option>
    </select>
  </div>
</div>

<div class="form-group row m-b-10" id="content-queue_tree_limits" style="display: none;">
  <label class="col-md-3 text-lg-right col-form-label">Límites Personalizados</label>
  <div class="col-md-4">
    <div class="row">
      <div class="col-6">
        <input type="text" class="form-control" id="queue_tree_upload" name="queue_tree_upload" placeholder="Upload (ej: 5M)" />
        <small class="text-muted">Upload</small>
      </div>
      <div class="col-6">
        <input type="text" class="form-control" id="queue_tree_download" name="queue_tree_download" placeholder="Download (ej: 10M)" />
        <small class="text-muted">Download</small>
      </div>
    </div>
  </div>
</div>
FIN CAMPOS REMOVIDOS: Queue Tree -->