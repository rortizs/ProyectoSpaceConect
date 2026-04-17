</div>

<a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade" data-click="scroll-top"><i
    class="fa fa-angle-up"></i></a>
</div>
<!-- ================== FIN FOOTER ================== -->
<!-- ================== INICIO RUTA  ============== -->
<?php $assets_version = '1.0.3'; ?>
<script>
  const base_url = "<?= base_url(); ?>";
  const permission_create = "<?= empty($_SESSION['permits_module']['r']) ? 0 : $_SESSION['permits_module']['r'] ?>";
  const permission_edit = "<?= empty($_SESSION['permits_module']['a']) ? 0 : $_SESSION['permits_module']['a'] ?>";
  const permission_remove = "<?= empty($_SESSION['permits_module']['e']) ? 0 : $_SESSION['permits_module']['e'] ?>";
  const permission_view = "<?= empty($_SESSION['permits_module']['v']) ? 0 : $_SESSION['permits_module']['v'] ?>";
  const currency_symbol = "<?= empty($_SESSION['businessData']['symbol']) ? "" : $_SESSION['businessData']['symbol'] ?>";
  const user_profile = "<?= empty($_SESSION['userData']['profileid']) ? 0 : $_SESSION['userData']['profileid'] ?>";
  const key_google = "<?= empty($_SESSION['businessData']['google_apikey']) ? "" : $_SESSION['businessData']['google_apikey'] ?>";
  const assets_version = '1.0.3'; // Frontend-only mirror
</script>
<!-- ================== INICIO RUTA  ============== -->
<!-- ================== CORE JS (Always loaded) ======== -->
<script src="<?= base_style() ?>/js/app.min.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/js/moment.min.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/js/functions.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/js/utils.min.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/js/initial.min.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/js/theme/default.min.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/js/jquery-confirm.min.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/bookstores/select2/js/select2.min.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/bookstores/parsleyjs/parsley.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/bookstores/gritter/js/jquery.gritter.min.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/bookstores/axios/axios.min.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/js/search.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/js/inatividad.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/js/network.js?v=<?= $assets_version ?>"></script>

<!-- ================== CONDITIONAL JS (Lazy loaded) ======== -->
<?php
// Only load these heavy libraries on pages that actually use them
$page_route = $_GET['route'] ?? '';

// DataTables - only on list/table pages
if (
  strpos($page_route, 'list') !== false ||
  strpos($page_route, 'routers') !== false ||
  strpos($page_route, 'clients') !== false ||
  strpos($page_route, 'users') !== false
) {
?>
<script src="<?= base_style() ?>/js/jquery.bootstrap-touchspin.min.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/js/datatables.min.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/bookstores/jszip/jszip.min.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/bookstores/pdfmake/pdfmake.min.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/bookstores/pdfmake/vfs_fonts.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/js/datatable-helper.js?v=<?= $assets_version ?>"></script>
<?php } ?>

<!-- TinyMCE - only on editor pages -->
<?php if (strpos($page_route, 'email') !== false || strpos($page_route, 'ticket') !== false) { ?>
<script src="<?= base_style() ?>/bookstores/tinymce/tinymce.min.js?v=<?= $assets_version ?>"></script>
<?php } ?>

<!-- Chart.js - only on dashboard/reports -->
<?php if (strpos($page_route, 'dashboard') !== false || strpos($page_route, 'report') !== false) { ?>
<script src="<?= base_style() ?>/bookstores/chartjs/js/chart.min.js?v=<?= $assets_version ?>"></script>
<?php } ?>

<!-- SmartWizard - only on forms with steps -->
<?php if (strpos($page_route, 'install') !== false || strpos($page_route, 'wizard') !== false) { ?>
<script src="<?= base_style() ?>/bookstores/smartwizard/js/jquery.smartWizard.js?v=<?= $assets_version ?>"></script>
<?php } ?>

<!-- Lightbox - only on pages with images/gallery -->
<?php if (strpos($page_route, 'client') !== false || strpos($page_route, 'ticket') !== false) { ?>
<script src="<?= base_style() ?>/bookstores/lightbox/js/lightbox.min.js?v=<?= $assets_version ?>"></script>
<?php } ?>

<!-- DateTime Picker -->
<script src="<?= base_style() ?>/bookstores/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js?v=<?= $assets_version ?>"></script>

<!-- jQuery Mask -->
<script src="<?= base_style() ?>/bookstores/jquery.maskedinput/jquery.maskedinput.js?v=<?= $assets_version ?>"></script>

<!-- WhatsApp & Files (lightweight, keep always) -->
<script src="<?= base_style() ?>/js/files.js?v=<?= $assets_version ?>"></script>
<script src="<?= base_style() ?>/js/whatsapp.js?v=<?= $assets_version ?>"></script>

<!-- ================== FIN ARCHIVOS JS =========== -->
<?php if (isset($data['page_functions_js'])) { ?>
  <!-- ================== INICIO FUNCION JS ============ -->
  <?php
  // Load MuniProgressLoader before muni JS files (dependency)
  $muniPages = ['munidashboard.js', 'munired.js'];
  if (in_array($data['page_functions_js'], $muniPages)) { ?>
    <script src="<?= base_style() ?>/js/functions/muni-progress-loader.js?v=<?= $assets_version ?>"></script>
  <?php } ?>
  <script src="<?= base_style() ?>/js/functions/<?= $data['page_functions_js']; ?>?v=<?= $assets_version ?>"></script>
  <!-- ================== FIN FUNCION JS =============== -->
<?php } ?>
<!-- keys -->
<input type="hidden" id="whatsapp_key_value" value="<?= $_SESSION['businessData']['whatsapp_key'] ?>" />
<input type="hidden" id="whatsapp_api_value" value="<?= $_SESSION['businessData']['whatsapp_api'] ?>" />
<!-- MONEDA -->
<input type="hidden" value="<?= $_SESSION['businessData']['symbol'] ?>" id="moneda_simbol">
</body>

</html>
