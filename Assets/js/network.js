/*
 * NETWORK.JS - VERSIÓN SIMPLIFICADA
 * 
 * Este archivo ha sido simplificado para mantener solo funcionalidades básicas:
 * - Gestión de IP
 * - Marca de AP Cliente (campo de texto simple)
 * 
 * FUNCIONES REMOVIDAS/DESHABILITADAS:
 * - getRouterSelect(): Selector de routers MikroTik
 * - getPasswordInput(): Gestión de contraseñas PPPoE
 * - getApClientContent(): Búsqueda avanzada de AP Clientes
 * - changeTogglePassword(): Toggle de visibilidad de contraseña
 * - changeRouter(): Cambio de router y carga de datos relacionados
 * - searchRouters(): Búsqueda de routers disponibles
 * - searchApCliente(): Búsqueda de AP Clientes en base de datos
 * - searchNapCliente(): Búsqueda de Cajas NAP
 * - networkModes: Array de modos de red (DHCP, PPPoE, etc.)
 * - clearInput(): Limpieza de campos de entrada
 * - refreshPassword(): Generación automática de contraseñas
 * - Queue Tree functions: Gestión de QoS y políticas de ancho de banda
 * 
 * Para reactivar estas funciones, restaure el archivo original desde Git y 
 * asegúrese de que los controladores correspondientes estén disponibles.
 */

// Simplified network.js for basic IP and AP Client Brand functionality

function getIpInput() {
  return $("#netIP");
}

function getApClientBrandInput() {
  return $("#ap_cliente_brand");
}

// Funciones getter para elementos de red
function getZonaNameInput() {
  return $("#netName");
}

function getRouterSelect() {
  return $("#netRouter");
}

function getPasswordInput() {
  return $("#netPassword");
}

function getLocalAddressInput() {
  return $("#netLocalAddress");
}

function getNapClientLabel() {
  return $("#nap_cliente_nombre");
}

function getNapClientValue() {
  return $("#nap_cliente_id");
}

function getApClientLabel() {
  return $("#ap_cliente_id");
}

function getApClientValue() {
  return $("#ap_cliente_value");
}

function getNetNameId() {
  return $("#netNameId");
}

// Función para encontrar modos de red
function findNetworkMode(modeId) {
  // Modos de red básicos
  const networkModes = [
    { id: "1", type: "dhcp", name: "DHCP" },
    { id: "2", type: "pppoe", name: "PPPoE" },
    { id: "3", type: "nap_client", name: "NAP Cliente" },
    { id: "4", type: "ap_client", name: "AP Cliente" }
  ];
  
  return networkModes.find(mode => mode.id === modeId);
}

function loadComponentNetwork(id = "network_mount", serviceId) {
  return new Promise((resolve, reject) => {
    axios
      .get(`${base_url}/network/network_template`)
      .then(({ data }) => {
        const root = document.getElementById(id);
        root.innerHTML = data;
        
        // Cargar datos existentes del cliente si están disponibles
        loadExistingNetworkData();
        
        resolve(root);
      })
      .catch(reject);
  });
}

/**
 * Carga los datos de red existentes del cliente en el formulario
 */
function loadExistingNetworkData() {
  try {
    // Obtener datos del cliente desde el elemento clientData
    const clientDataElement = document.getElementById('clientData');
    if (!clientDataElement) {
      console.log('No se encontraron datos del cliente');
      return;
    }
    
    const clientData = JSON.parse(clientDataElement.textContent);
    console.log('Datos del cliente cargados:', clientData);
    
    // Cargar IP del cliente
    const netIPField = document.getElementById('netIP');
    if (netIPField && clientData.net_ip) {
      netIPField.value = clientData.net_ip;
      console.log('IP cargada:', clientData.net_ip);
    }
    
    // Cargar marca AP Cliente desde el campo note (donde se guarda en modo simplificado)
    const apClienteBrandField = document.getElementById('ap_cliente_brand');
    if (apClienteBrandField) {
      // En modo simplificado, la marca del AP se guarda en el campo note
      if (clientData.note && clientData.note.trim() !== '') {
        apClienteBrandField.value = clientData.note;
        console.log('Marca AP Cliente cargada desde note:', clientData.note);
      }
      // También verificar si hay datos en ap_cliente_nombre o nap_cliente_nombre
      else if (clientData.ap_cliente_nombre && clientData.ap_cliente_nombre.trim() !== '') {
        apClienteBrandField.value = clientData.ap_cliente_nombre;
        console.log('Marca AP Cliente cargada desde ap_cliente_nombre:', clientData.ap_cliente_nombre);
      }
      else if (clientData.nap_cliente_nombre && clientData.nap_cliente_nombre.trim() !== '') {
        apClienteBrandField.value = clientData.nap_cliente_nombre;
        console.log('Marca AP Cliente cargada desde nap_cliente_nombre:', clientData.nap_cliente_nombre);
      }
    }
    
  } catch (error) {
    console.error('Error al cargar datos de red existentes:', error);
  }
}

function loadComponentIP(id = "network_ip_mount") {
  return new Promise((resolve, reject) => {
    axios
      .get(`${base_url}/network/network_ip_template`)
      .then(({ data }) => {
        const root = document.getElementById(id);
        root.innerHTML = data;
        resolve(root);
      })
      .catch(reject);
  });
}

// Validation function for network data
function validateNetworkData() {
  // Validación mejorada de IP para entrada manual
  const ipInput = getIpInput();
  if (ipInput.val()) {
    const ip = ipInput.val().trim();
    
    // Patrón más estricto para validar IP
    const ipPattern = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
    
    if (!ipPattern.test(ip)) {
      alert('Por favor ingrese una dirección IP válida.\nFormato: 192.168.4.100\nCada número debe estar entre 0 y 255');
      ipInput.focus();
      return false;
    }
    
    // Validación adicional para IPs privadas comunes
    const octets = ip.split('.').map(Number);
    const firstOctet = octets[0];
    const secondOctet = octets[1];
    
    // Sugerir rangos de IP privadas
    if (!(
      (firstOctet === 192 && secondOctet === 168) ||  // 192.168.x.x
      (firstOctet === 10) ||                          // 10.x.x.x
      (firstOctet === 172 && secondOctet >= 16 && secondOctet <= 31) // 172.16-31.x.x
    )) {
      const confirmPublic = confirm('La IP ingresada no parece ser una IP privada.\n¿Está seguro de que desea continuar?');
      if (!confirmPublic) {
        ipInput.focus();
        return false;
      }
    }
  }
  
  // Validar marca de AP Cliente si está presente
  const brandInput = getApClientBrandInput();
  if (brandInput.val()) {
    const brand = brandInput.val().trim();
    if (brand.length < 2) {
      alert('La marca del AP Cliente debe tener al menos 2 caracteres');
      brandInput.focus();
      return false;
    }
  }
  
  return true;
}

// Get network data for form submission
function getNetworkData() {
  return {
    ip: getIpInput().val(),
    ap_cliente_marca: getApClientBrandInput().val()
  };
}
