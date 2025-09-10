<?php head($data); ?>
<!-- INICIO TITULO -->
<ol class="breadcrumb float-xl-right">
  <li class="breadcrumb-item"><a href="<?= base_url() ?>/dashboard"><?= $data['home_page'] ?></a></li>
  <li class="breadcrumb-item active"><?= $data['actual_page'] ?></li>
</ol>
<h1 class="page-header"><?= $data['page_title'] ?></h1>
<div class="panel panel-default">
  <div class="panel-body border-panel">
    <div class="row">
      <div class="col-md-12 col-sm-12 col-12">
        <center><img src="<?= base_style() ?>/images/logotypes/superwisp.png" width="200px"></center>
        <p class="m-t-10">
          Nuestro <b>Sistema de Administración de Clientes WISP e ISP</b> te permite gestionar de manera eficiente tu negocio, integrando múltiples funciones en una sola plataforma.
        </p>
        <ul>
          <li><b>Conexión con MikroTik:</b> Administración avanzada de red.</li>
          <li><b>Automatización inteligente:</b> Corte y reactivación automática del servicio.</li>
          <li><b>Gestión de Simple Queue y PPPoE:</b> Control de ancho de banda.</li>
          <li><b>Notificaciones:</b> Envío de alertas vía API de WhatsApp.</li>
          <li><b>Monitoreo y soporte técnico:</b> Optimización del servicio al cliente.</li>
          <li><b>Mapa de cliente:</b> Mapa de los clientes.</li>
          <!--<li><b>Mapa de red:</b> Mapa de las cajas NAP y MUFAS.</li>-->
          <li><b>Bloqueo de Redes Sociales:</b> Capacidad de bloquear por ip las redes sociales.</li>
        </ul>
        <p>
          Con estas funciones, facilitarás la administración de tu empresa, mejorarás el rendimiento y optimizarás la experiencia del cliente.
        </p>
      </div>

      <!-- DERECHOS DE AUTOR -->
      <div class="col-md-12 col-sm-12 col-12" data-sortable="false">
        <div class="panel panel-default" data-sortable="false">
          <div class="panel-heading">
            <h6 class="panel-title f-w-700">DERECHOS DE AUTOR</h6>
          </div>
          <div class="panel-body border-panel" align="justify">
            <ul>
              <li><b>Proyecto:</b> Sistema de administración de clientes wisp e isp de <mark><?= $_SESSION['businessData']['business_name'] ?></mark></li>
              <li><b>Versión:</b> 1.5.0</li>
              <li><b>Desarrollado por:</b> <?= DEVELOPER ?></li>
              <li><b>Web:</b> <a href="<?= DEVELOPER_WEBSITE ?>"><?= DEVELOPER_WEBSITE ?></a></li>
            </ul>
          </div>
        </div>
      </div>



      <!-- CONTACTO -->
      <div class="col-md-12 col-sm-12 col-12" data-sortable="false">
        <div class="panel panel-default" data-sortable="false">
          <div class="panel-heading">
            <h6 class="panel-title f-w-700">CONTACTO</h6>
          </div>
          <div class="panel-body border-panel" align="justify">
            <p>Para soporte técnico, errores en el sistema o sugerencias de mejoras, contáctanos:</p>
            <ul>
              <li><b>Correo:</b> <?= DEVELOPER_EMAIL ?></li>
              <li><b>WhatsApp:</b><?= DEVELOPER_MOBILE ?></li>
            </ul>
            <p>Al enviar un correo, utiliza el asunto: "<b><?= $_SESSION['businessData']['business_name'] ?> - Sistema de Administración de Clientes WISP e ISP</b>".</p>
          </div>
        </div>
      </div>

      <!-- SECCIÓN DE VIDEOS -->
      <!--<div class="col-md-12 col-sm-12 col-12" data-sortable="false">
        <div class="panel panel-default" data-sortable="false">
          <div class="panel-heading">
            <h6 class="panel-title f-w-700">TUTORIALES Y VIDEOS</h6>
          </div>
          <div class="panel-body border-panel">
            <div id="accordion">
              <div class="card">
                <div class="card-header" id="headingOne">
                  <h5 class="mb-0">
                    <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                      Introducción al sistema
                    </button>
                  </h5>
                </div>
                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                  <div class="card-body">
                    <iframe width="100%" height="315" src="https://www.youtube.com/embed/dEtIk_XkIEA" frameborder="0" allowfullscreen></iframe>
                  </div>
                </div>
              </div>
              <div class="card">
                <div class="card-header" id="headingTwo">
                  <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                      Configuración inicial
                    </button>
                  </h5>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                  <div class="card-body">
                    <iframe width="100%" height="315" src="https://www.youtube.com/embed/VIDEO_ID_2" frameborder="0" allowfullscreen></iframe>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>-->

    </div>
  </div>
</div>
<!-- FIN TITULO -->
<?php footer($data); ?>