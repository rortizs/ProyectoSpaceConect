<?php
head($data);

$status_bg = [];
$status_bg["CONNECTED"] = "success";
$status_bg["DISCONNECTED"] = "red";

$status_class = [];
$status_class["CONNECTED"] = "con";
$status_class["DISCONNECTED"] = "dis";

$status_spanish = [];
$status_spanish["CONNECTED"] = "CONECTADO";
$status_spanish["DISCONNECTED"] = "DESCONECTADO";
?>
<!-- INICIO TITULO -->
<style>
  .bt-a {
    width: auto; /* Ajuste automático de ancho */
    padding: 5px; /* Añadir algo de padding para que los iconos no estén tan pegados */
    display: inline-block;
    text-align: center; /* Centrar los iconos */
  }

  .panel .nav-tabs li a,
  .panel .nav-tabs li a:hover {
    color: #3D3D3D;
    text-decoration: none !important;
  }

  .panel .nav-tabs li.active a {
    color: #00AEAE;
    text-decoration: none !important;
  }

  #routerLogs {
    overflow-x: hidden;
    overflow-y: scroll;
    max-height: 400px;
  }

  #routerLogTable {
    background: black;
    color: #CCC;
  }

  #routerLogTable tr {
    padding-left: 10px;
  }

  #routerLogTable tr.log-error td {
    color: #FAA !important;
  }

  #routerLogTable td {
    white-space: nowrap;
    border: none !important;
  }

  .chart-container {
    width: 100%;
  }

  #routerGraphs.tab-pane {
    text-align: center;
  }

  #routerGraphs select {
    display: inline-block;
  }

  .bt-a i {
    font-size: 16px; /* Ajusta el tamaño de los íconos si es necesario */
    color: #333; /* Color de los íconos */
  }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<ol class="breadcrumb float-xl-right">
  <li class="breadcrumb-item"><a href="<?= base_url() ?>/dashboard"><?= $data['home_page'] ?></a></li>
  <li class="breadcrumb-item active"><?= $data['actual_page'] ?></li>
</ol>

<h1 class="page-header"><?= $data['page_title'] ?></h1>

<div class="panel panel-default panel-routers">
  <div class="panel-heading">
    <h4 class="panel-title">Lista de routers</h4>
    <div class="panel-heading-btn">
      <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-iconpanel" data-click="panel-expand"><i class="fa fa-expand"></i></a>
      <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-iconpanel" data-click="panel-reload" onclick="refresh_table()"><i class="fas fa-sync-alt"></i></a>
    </div>
  </div>

  <div class="panel-body border-panel">
    <div class="row">
      <div id="list-btns-tools">
        <div class="options-group btn-group m-r-5">
          <button type="button" class="btn btn-white" onclick="add()"><i class="fas fa-plus mr-1"></i>Nuevo</button>

          <select class="form-control" id="filter_states" name="tblestado" style="width: 130px">
            <option value="" disabled>-SELECCIONE UN ESTADO-</option>
            <option value="" selected>TODOS</option>
            <option value="1">CONECTADO</option>
            <option value="2">DESCONECTADO</option>
          </select>

          <select class="form-control" id="filter_zones" name="tblzone" style="width: 130px">
            <option value="" disabled>-SELECCIONE UN MODO-</option>
            <option value="" selected>TODAS</option>
            <?php
            foreach ($data['zones'] as $k => $r) {
              $r = (object) $r;
            ?>
              <option value="<?= $r->id ?>"><?= $r->name ?></option>
            <?php } ?>
          </select>
        </div>
      </div>

      <div class="col-md-12 col-sm-12 col-12">
        <div class="table-responsive">
          <table id="list" class="table table-bordered dt-responsive nowrap dataTable dtr-inline collapsed" data-order='[[ 1, "asc" ]]' style="width: 100%;">
            <thead>
              <tr>
                <th style="max-width: 20px !important; width: 20px;">ID</th>
                <th>Descripción</th>
                <th>IP</th>
                <th>Identity</th>
                <th>Modelo</th>
                <th>Versión</th>
                <th>Clientes</th>
                <th>Estado</th>
                <th class="all" data-orderable="false" style="max-width: 40px !important; width: 40px;"></th>
              </tr>
            </thead>

            <tbody>
              <?php
              foreach ($data['records'] as $k => $r) {
                $r = (object) $r;
              ?>
                <tr class="status-<?= $r->status_id ?> zone-<?= $r->zone_id ?>">
                  <td style="max-width: 20px !important; width: 20px;"><?= $r->id ?></td>
                  <td><a href="#!" onclick="info(<?= $r->id ?>)"><?= $r->name ?></a><small style="display: block;" class="text-warning"><?= $r->zone_name ?></small></td>
                  <td><i class="fa fa-network-wired mr-1"></i> <?= $r->ip ?></td>
                  <td><?= $r->identity ?></td>
                  <td><i class="fa fa-server mr-1"></i> <?= $r->board_name ?></td>
                  <td><i class="fa fa-cogs mr-1"></i> <?= $r->version ?></td>
                  <td><span class="badge" style="font-size: 16px;"><?= $r->customers ?></span></td>
                  <td><span class="label label-pill label-<?= $status_bg[$r->status] ?>"><?= $status_spanish[$r->status] ?></span></td>
                  <td class="all" data-orderable="false">
                    <a href="#!" class="bt-a" onclick="edit(<?= $r->id ?>)" title="Editar"><i class="fa fa-edit"></i></a>
                    <a href="#!" class="bt-a" onclick="info(<?= $r->id ?>, 'routerLogs')" title="Logs"><i class="fa fa-th-list"></i></a>
                    <a href="#!" class="bt-a" onclick="info(<?= $r->id ?>, 'routerGraphs')" title="Gráficos"><i class="fa fa-bar-chart"></i></a>
                    <a href="#!" class="bt-a" onclick="router_reboot(<?= $r->id ?>)" title="Reiniciar Router"><i class="fa fa-refresh"></i></a>
                    <a href="#!" class="bt-a" onclick="regla_moroso(<?= $r->id ?>)" title="Crear reglas de moroso"><i class="fa fa-wrench"></i></a>
                    <a href="#!" class="bt-a" onclick="remove(<?= $r->id ?>)" title="Eliminar"><i class="fa fa-trash"></i></a>
                  </td>
                </tr>
              <?php
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="modal-add" class="modal fade p-0" role="dialog" style="display: none;">
  <form autocomplete="off" name="addRouterForm" id="addRouterForm">
    <div class="modal-dialog modal-dialog-centered modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title text-uppercase" id="text-ticket">Agregar router</h6>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12 form-group">
              <label class="control-label">Descripción*</label>
              <input type="text" class="form-control" id="routerAddDescription" maxlength="60" placeholder="Ingrese la descripción del router">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-white" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-blue"><i class="fas fa-save mr-2"></i>Guardar</button>
        </div>
      </div>
    </div>
  </form>
</div>
<div id="modal-edit" class="modal fade p-0" role="dialog" style="display: none;">
  <form autocomplete="off" name="editRouterForm" id="editRouterForm">
    <div class="modal-dialog modal-dialog-centered modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title text-uppercase" id="text-ticket">Editar router</h6>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <input type="hidden" class="form-control" id="routerEditID" maxlength="60">
            <div class="col-md-12 form-group">
              <label class="control-label">Descripción*</label>
              <input type="text" class="form-control" id="routerEditDescription" maxlength="60" placeholder="Ingrese la descripción del router">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-white" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-blue"><i class="fas fa-save mr-2"></i>Actualizar</button>
        </div>
      </div>
    </div>
  </form>
</div>
<div id="modal-info" class="modal fade p-0" role="dialog" style="display: none;">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title text-uppercase" id="text-ticket">Información del router</h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="panel panel-default">
          <div class="panel-heading panel-heading-nav">
            <ul class="nav nav-tabs">
              <li role="presentation" class="active">
                <a href="#routerInfo" aria-controls="routerInfo" role="tab" data-toggle="tab">Info</a>
              </li>
              <li role="presentation">
                <a href="#routerGraphs" aria-controls="routerGraphs" role="tab" data-toggle="tab">Gráficos</a>
              </li>
              <li role="presentation">
                <a href="#routerLogs" aria-controls="routerLogs" role="tab" data-toggle="tab">Logs</a>
              </li>
            </ul>
          </div>
          <div class="panel-body">
            <div class="tab-content">
              <div role="tabpanel" class="tab-pane fade in active" id="routerInfo">
                <table id="routerInfoTable" class="table">
                  <tbody>

                  </tbody>
                </table>
              </div>
              <div role="tabpanel" class="tab-pane fade" id="routerGraphs">

                <select class="form-control" id="interfaceFilter" style="width: 130px">
                  <option value="" disabled>Cargando...</option>
                </select>
                <div class="chart-container">
                  <canvas id="trafficChart" width="400" height="200"></canvas>
                </div>
              </div>
              <div role="tabpanel" class="tab-pane fade" id="routerLogs">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div class="btn-group" role="group">
                    <button type="button" id="pauseLogsBtn" class="btn btn-warning btn-sm" onclick="toggleLogUpdates()">
                      <i class="fas fa-pause"></i> Pausar
                    </button>
                    <button type="button" id="refreshLogsBtn" class="btn btn-info btn-sm" onclick="fetchLogData()">
                      <i class="fas fa-sync"></i> Actualizar
                    </button>
                  </div>
                  <div>
                    <select id="logLimitSelect" class="form-control form-control-sm" onchange="changeLogLimit()" style="width: auto; display: inline-block;">
                      <option value="25">25 logs</option>
                      <option value="50" selected>50 logs</option>
                      <option value="100">100 logs</option>
                      <option value="200">200 logs</option>
                    </select>
                    <span id="logCount" class="text-muted ml-2"></span>
                  </div>
                </div>
                <table id="routerLogTable" class="table">
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- FIN TITULO -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.14.5/sweetalert2.all.min.js" integrity="sha512-m4zOGknNg3h+mK09EizkXi9Nf7B3zwsN9ow+YkYIPZoA6iX2vSzLezg4FnW0Q6Z1CPaJdwgUFQ3WSAUC4E/5Hg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
  $(function() {
    var blockRes = false;
    $('#addRouterForm').submit(function(event) {
      event.preventDefault();
      var valid = true;

      if (!blockRes) {
        blockRes = true;

        $('#addRouterForm').find(".form-control").each(function() {
          if ($(this).val().length == 0) {
            $(this).focus();
            valid = false;
          }
        });

        if (valid) {
          var data = {};

          data.name = $("#routerAddDescription").val();

          Swal.fire({
            title: 'Por favor espere...',
            allowOutsideClick: false,
            showConfirmButton: false,
            onBeforeOpen: () => {
              Swal.showLoading();
            }
          });
          $.post('<?= base_url(); ?>/network/add_router', data).done(function(data) {
            try {
              var res = JSON.parse(data);
              if (res.result == "success") {
                window.location.reload();
              } else {
                blockRes = false;
                Swal.fire({
                  icon: 'error',
                  title: 'Error de conexión',
                  text: res.message || 'Revisa la información de conexión del Router.',
                });
              }
            } catch (e) {
              blockRes = false;
              Swal.fire({
                icon: 'error',
                title: 'Error de sesión',
                text: 'Tu sesión ha expirado. Por favor recarga la página.',
              });
            }
          }).fail(function() {
            blockRes = false;
            Swal.fire({
              icon: 'error',
              title: 'Error de red',
              text: 'No se pudo enviar la petición. Verifica tu conexión.',
            });
          });

        }
      }

    });
    $('#editRouterForm').submit(function(event) {
      event.preventDefault();
      var valid = true;

      if (!blockRes) {
        blockRes = true;

        $('#editRouterForm').find(".form-control").each(function() {
          if ($(this).val().length == 0) {
            $(this).focus();
            valid = false;
          }
        });

        if (valid) {
          var data = {};

          data.id = $("#routerEditID").val();
          data.name = $("#routerEditDescription").val();

          Swal.fire({
            title: 'Por favor espere...',
            allowOutsideClick: false,
            showConfirmButton: false,
            onBeforeOpen: () => {
              Swal.showLoading();
            }
          });
          $.post('<?= base_url(); ?>/network/edit_router', data).done(function(data) {
            try {
              var res = JSON.parse(data);
              if (res.result == "success") {
                window.location.reload();
              } else {
                blockRes = false;
                Swal.fire({
                  icon: 'error',
                  title: 'Error de conexión',
                  text: res.message || 'Revisa la información de conexión del Router.',
                });
              }
            } catch (e) {
              blockRes = false;
              Swal.fire({
                icon: 'error',
                title: 'Error de sesión',
                text: 'Tu sesión ha expirado. Por favor recarga la página.',
              });
            }
          }).fail(function() {
            blockRes = false;
            Swal.fire({
              icon: 'error',
              title: 'Error de red',
              text: 'No se pudo enviar la petición. Verifica tu conexión.',
            });
          });

        }
      }

    });
    $(".toggle-password").click(function() {
      if ($(this).siblings("input").attr("type") == "text") {
        $(this).siblings("input").attr("type", "password");
        $(this).find("i").removeClass("icon-eye-slash-open").addClass("icon-eye-open");
      } else {
        $(this).siblings("input").attr("type", "text");
        $(this).find("i").addClass("icon-eye-slash-open").removeClass("icon-eye-open");
      }
    })
    $("#filter_states").change(function() {
      updateFilter();
    });
    $("#filter_zones").change(function() {
      updateFilter();
    });
    $(".panel .nav-tabs li a").click(function() {
      $(".panel .nav-tabs li.active").removeClass("active");
      $(this).parent().addClass("active");
    });
    $('#interfaceFilter').change(function() {
      readingRouterInterface = $(this).val();
      updateIntChart();
    });
  });

  function add() {
    $('#modal-add').modal('show');
  }

  function edit(id) {
    var data = {};
    data.id = id;
    $.post('<?= base_url(); ?>/network/get_edit_router', data).done(function(data) {
      var res = JSON.parse(data);
      $("#routerEditID").val(id);
      $("#routerEditDescription").val(res.data.name);
      $('#modal-edit').modal('show');
    });
  }

  function remove(id) {
    var data = {};
    data.id = id;
    Swal.fire({
      title: "¿Esta seguro?",
      text: "Esta a punto de eliminar un router",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Continuar"
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Por favor espere...',
          allowOutsideClick: false,
          showConfirmButton: false,
          onBeforeOpen: () => {
            Swal.showLoading();
          }
        });
        $.post('<?= base_url(); ?>/network/remove_router', data).done(function(data) {
          var res = JSON.parse(data);
          if (res.result == "success") {
            window.location.reload();
          }
        });
      }
    });

  }

  var trafficInterval = null;
  var readingRouterId = 0;
  var readingRouterInterface = 0;
  var lastReadingRx = [];
  var lastReadingTx = [];
  var logInterval = null;
  var logsPaused = false;
  var currentLogLimit = 50;

  var chartlabels = [];
  var chart0data = [];
  var chart1data = [];

  function info(id, btn = "routerInfo") {
    limpiarCache();

    $('#modal-info').modal('show');
    setTimeout(function() {
      $("a[href='#" + btn + "']").click();

      var data = {
        id: id
      };

      $("#routerInfoTable tbody").html('<tr><td>Loading...</td><td></td></tr>');
      $("#routerLogTable tbody").html('<tr><td>Loading...</td><td></td><td></td></tr>');

      $('#interfaceFilter').empty().append('<option selected disabled>Recargando...</option>');

      if (readingRouterId !== id) {

        chart.data.labels = [];
        chart.data.datasets.forEach(dataset => dataset.data = []);
        chart.update();

        chartlabels = {};
        chart0data = {};
        chart1data = {};
        lastReadingRx = {};
        lastReadingTx = {};

        readingRouterInterface = 0;
        if (trafficInterval) clearInterval(trafficInterval);
        if (logInterval) clearInterval(logInterval);
        $('#routerLogs').removeClass("initiated");
      }

      $.post('<?= base_url(); ?>/network/router_system_info', data).done(function(data) {
        var res = JSON.parse(data);
        if (res.result === "success") {

          // Increase intervals for better performance
          trafficInterval = setInterval(fetchTrafficData, 5000); // 5 seconds for traffic
          fetchTrafficData();
          logInterval = setInterval(fetchLogData, 10000); // 10 seconds for logs
          fetchLogData();
        } else {
          $("#routerLogTable tbody").html('<tr><td>Could not connect!</td><td></td><td></td></tr>');
          $('#interfaceFilter').html('<option selected disabled>Could not connect!</option>');
        }

        readingRouterId = id;
        $("#routerInfoTable tbody").html(res.html);
      });
    }, 300);
  }

  function limpiarCache() {
    console.log("🔄 Limpiando caché antes de abrir modal...");

    if (trafficInterval) {
      clearInterval(trafficInterval);
      trafficInterval = null;
    }
    if (logInterval) {
      clearInterval(logInterval);
      logInterval = null;
    }

    readingRouterId = null;

    $('#interfaceFilter').empty().append('<option selected disabled>Cargando...</option>');

    chart.data.labels = [];
    chart.data.datasets.forEach(dataset => dataset.data = []);
    chart.update();

    chartlabels = {};
    chart0data = {};
    chart1data = {};
    lastReadingRx = {};
    lastReadingTx = {};
    readingRouterInterface = 0;

    $('#routerLogs').removeClass("initiated");
    console.log("✔ Caché limpiado correctamente.");
  }

  const ctx = document.getElementById('trafficChart').getContext('2d');
  const chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: [],
      datasets: [{
        label: 'RX',
        data: [],
        backgroundColor: 'rgba(255, 99, 132, 0.2)',
        borderColor: 'rgba(255, 99, 132, 1)',
        borderWidth: 1
      }, {
        label: 'TX',
        data: [],
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            beginAtZero: false,
            callback: function(value, index, values) {
              return value == 0 ? "0" : formatSpeed(value);
            }
          }
        }
      }
    }
  });

  function router_reboot(id) {
    Swal.fire({
        title: "¿Reiniciar el MikroTik?",
        text: "El router se reiniciará inmediatamente.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, reiniciar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: base_url + "/network/router_reboot",
                type: "POST",
                data: { id: id },
                dataType: "json",
                success: function(response) {
                    Swal.fire(response.result === "success" ? "¡Éxito!" : "Error", response.message, response.result === "success" ? "success" : "error");
                },
                error: function() {
                    Swal.fire("Error", "No se pudo conectar con el servidor.", "error");
                }
            });
        }
    });
}

function regla_moroso(id) {
    Swal.fire({
        title: "¿Crear las reglas moroso?",
        text: "Se está creando las reglas de moroso.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, crear regla",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: base_url + "/network/regla_moroso",
                type: "POST",
                data: { id: id },
                dataType: "json",
                success: function(response) {
                    Swal.fire(response.result === "success" ? "¡Éxito!" : "Error", response.message, response.result === "success" ? "success" : "error");
                },
                error: function() {
                    Swal.fire("Error", "No se pudo conectar con el servidor.", "error");
                }
            });
        }
    });
}


  function fetchTrafficData() {
    var data = {
      id: readingRouterId
    };
    
    $.ajax({
      url: '<?= base_url(); ?>/network/router_system_interface',
      type: 'POST',
      data: data,
      dataType: 'json',
      timeout: 8000, // 8 seconds timeout for interface data
      success: function(res) {
      if (res.result == "success") {
        var rid = readingRouterId;

        if (!chartlabels[rid]) {
          chartlabels[rid] = [];
          chart0data[rid] = [];
          chart1data[rid] = [];
          lastReadingRx[rid] = [];
          lastReadingTx[rid] = [];
        }

        if (!lastReadingRx[rid][0]) {
          $('#interfaceFilter option:first').remove();
        }

        for (let i = 0; i < res.interface.length; i++) {
          const ce = res.interface[i];
          let iRx = ce["rx-byte"];
          let iTx = ce["tx-byte"];

          if (!lastReadingRx[rid][i]) {
            var option = $('<option>').text(ce["name"]).val(i);
            if (i == 0) option.attr('selected', 'selected');
            $('#interfaceFilter').append(option);

            chartlabels[rid][i] = [];
            chart0data[rid][i] = [];
            chart1data[rid][i] = [];
          } else {
            let cRx = iRx - lastReadingRx[rid][i];
            let cTx = iTx - lastReadingTx[rid][i];

            if (cRx < 0) cRx = 0;
            if (cTx < 0) cTx = 0;

            chartlabels[rid][i].push(new Date().toLocaleTimeString());
            chart0data[rid][i].push(cRx);
            chart1data[rid][i].push(cTx);

            if (chartlabels[rid][i].length > 20) {
              chartlabels[rid][i].shift();
              chart0data[rid][i].shift();
              chart1data[rid][i].shift();
            }
          }

          lastReadingRx[rid][i] = iRx;
          lastReadingTx[rid][i] = iTx;
        }

        if (chartlabels[rid].length > 0) {
          updateIntChart();
        }
      } else {
        console.error("Failed to fetch interface data:", res.message || 'Unknown error');
        // Show error in chart area or interface list
        $('#interfaceFilter').html('<option selected disabled>Error al cargar interfaces</option>');
      }
    },
    error: function(xhr, status, error) {
      console.error("Error fetching traffic data:", status, error);
      if (status === 'timeout') {
        $('#interfaceFilter').html('<option selected disabled>Timeout - Router muy lento</option>');
      } else {
        $('#interfaceFilter').html('<option selected disabled>Error de conexión</option>');
      }
    }
    });
  }

  function updateIntChart() {
    var rid = readingRouterId;

    if (chartlabels[rid] != null) {

      chart.data.labels = chartlabels[rid][readingRouterInterface];
      chart.data.datasets[0].data = chart0data[rid][readingRouterInterface];
      chart.data.datasets[1].data = chart1data[rid][readingRouterInterface];

      console.log(chart0data[rid][readingRouterInterface]);

      chart.update();

    }
  }

  function fetchLogData() {
    if (logsPaused) return; // Don't fetch if paused
    
    var data = {};
    data.id = readingRouterId;
    data.limit = currentLogLimit;
    
    $.ajax({
      url: '<?= base_url(); ?>/network/router_system_log',
      type: 'POST',
      data: data,
      dataType: 'json',
      timeout: 10000, // 10 seconds timeout
      success: function(response) {
        if (response.result === "success") {
          $("#routerLogTable tbody").html(response.html);
          
          // Update log count if provided
          if (response.total_logs) {
            $("#logCount").text(`(${response.total_logs} logs)`);
          }
          
          var rlogs = $('#routerLogs');
          
          // Auto-scroll logic
          if (rlogs.scrollTop() + rlogs.innerHeight() < rlogs.prop('scrollHeight') && rlogs.hasClass("initiated")) {
            // User is scrolled up, don't auto-scroll
          } else {
            rlogs.scrollTop(rlogs.prop('scrollHeight'));
            rlogs.addClass("initiated");
          }
          
        } else {
          $("#routerLogTable tbody").html(`<tr><td colspan="3" class="text-center text-danger">${response.message || 'Error al cargar logs'}</td></tr>`);
        }
      },
      error: function(xhr, status, error) {
        if (status === 'timeout') {
          $("#routerLogTable tbody").html('<tr><td colspan="3" class="text-center text-warning">Tiempo de espera agotado. El router puede estar sobrecargado.</td></tr>');
        } else {
          try {
            var response = JSON.parse(xhr.responseText);
            $("#routerLogTable tbody").html(`<tr><td colspan="3" class="text-center text-danger">${response.message || 'Error de conexión'}</td></tr>`);
          } catch (e) {
            $("#routerLogTable tbody").html('<tr><td colspan="3" class="text-center text-danger">Error de red. Verifica tu conexión.</td></tr>');
          }
        }
      }
    });
  }

  function updateFilter() {
    $("#list tbody tr").each(function() {
      var sf = $("#filter_states").val();
      var zf = $("#filter_zones").val();
      if (($(this).hasClass("status-" + sf) && $(this).hasClass("zone-" + zf)) || ($(this).hasClass("status-" + sf) && zf == "") || ($(this).hasClass("zone-" + zf) && sf == "") || (sf == "" && zf == "")) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  }

  // Function to convert bytes to human-readable format
  function formatBytes(bytes) {
    if (speed === null || speed === undefined || speed === '' || speed === 0 || isNaN(speed)) {
      return '';
    }
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const index = Math.floor(Math.log(bytes) / Math.log(1024));
    const value = bytes / Math.pow(1024, index);
    return `${value.toFixed(2)} ${units[index] ?? ""}`;
  }

  function formatSpeed(speed) {
    const units = ['bps', 'kbps', 'Mbps', 'Gbps', 'Tbps'];
    const index = Math.floor(Math.log(speed) / Math.log(1000));
    const value = speed / Math.pow(1000, index);
    return `${value.toFixed(2)} ${units[index] !== undefined ? units[index] : ""}`;
  }
  
  // Log control functions
  function toggleLogUpdates() {
    logsPaused = !logsPaused;
    
    var btn = $('#pauseLogsBtn');
    if (logsPaused) {
      btn.html('<i class="fas fa-play"></i> Reanudar');
      btn.removeClass('btn-warning').addClass('btn-success');
    } else {
      btn.html('<i class="fas fa-pause"></i> Pausar');
      btn.removeClass('btn-success').addClass('btn-warning');
      // Resume and fetch immediately
      fetchLogData();
    }
  }
  
  function changeLogLimit() {
    currentLogLimit = parseInt($('#logLimitSelect').val());
    if (!logsPaused) {
      fetchLogData();
    }
  }
  
  // Performance monitoring
  function logPerformance(operation, startTime) {
    var duration = Date.now() - startTime;
    if (duration > 5000) { // Log slow operations (>5 seconds)
      console.warn(`Slow ${operation} operation: ${duration}ms`);
    }
  }
</script>
<?php footer($data); ?>