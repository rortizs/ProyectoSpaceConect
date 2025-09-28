<!-- Modal Asignar Política a Cliente -->
<div class="modal fade" id="modalAssignPolicy" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header headerRegister">
                <h5 class="modal-title" id="titleModalAssign">Asignar Política QoS</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formAssignPolicy" name="formAssignPolicy" class="form-horizontal">
                    <input type="hidden" id="idAssignment" name="idAssignment" value="">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Cliente <span class="required">*</span></label>
                                <select class="form-control" id="listClient" name="client_id" required="">
                                    <option value="">Seleccionar cliente</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Política QoS <span class="required">*</span></label>
                                <select class="form-control" id="listPolicy" name="policy_id" required="">
                                    <option value="">Seleccionar política</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <strong>Cliente seleccionado:</strong>
                                <div id="clientInfo" style="display: none;">
                                    <p><strong>Nombre:</strong> <span id="clientName"></span></p>
                                    <p><strong>IP:</strong> <span id="clientIP"></span></p>
                                    <p><strong>Producto:</strong> <span id="clientProduct"></span></p>
                                    <p><strong>Estado:</strong> <span id="clientStatus"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h6>Configuración de Ancho de Banda</h6>
                            <hr>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Límite Upload <span class="required">*</span></label>
                                <input class="form-control" id="txtUploadLimit" name="upload_limit" type="text" placeholder="5M" required="">
                                <small class="form-text text-muted">Ej: 5M, 1024k, 512k</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Límite Download <span class="required">*</span></label>
                                <input class="form-control" id="txtDownloadLimit" name="download_limit" type="text" placeholder="10M" required="">
                                <small class="form-text text-muted">Ej: 10M, 2048k, 1024k</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Prioridad</label>
                                <select class="form-control" id="listAssignPriority" name="priority">
                                    <option value="1">1 - Muy Alta</option>
                                    <option value="2">2 - Alta</option>
                                    <option value="3">3 - Normal Alta</option>
                                    <option value="4" selected>4 - Normal</option>
                                    <option value="5">5 - Normal Baja</option>
                                    <option value="6">6 - Baja</option>
                                    <option value="7">7 - Muy Baja</option>
                                    <option value="8">8 - Mínima</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Burst Upload</label>
                                <input class="form-control" id="txtBurstUpload" name="burst_upload" type="text" placeholder="7M">
                                <small class="form-text text-muted">Burst temporal de subida</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Burst Download</label>
                                <input class="form-control" id="txtBurstDownload" name="burst_download" type="text" placeholder="15M">
                                <small class="form-text text-muted">Burst temporal de bajada</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h6>Usar Template Predefinido</h6>
                            <hr>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Template</label>
                                <select class="form-control" id="listTemplate" name="template_id" onchange="applyTemplate()">
                                    <option value="">Configuración manual</option>
                                </select>
                                <small class="form-text text-muted">Selecciona un template para aplicar configuración automática</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="chkApplyNow" name="apply_now" checked>
                                    <label class="form-check-label" for="chkApplyNow">
                                        Aplicar inmediatamente en MikroTik
                                    </label>
                                </div>
                                <small class="form-text text-muted">Si está desmarcado, se guardará como pendiente de sincronización</small>
                            </div>
                        </div>
                    </div>

                    <div class="tile-footer">
                        <button id="btnActionAssign" class="btn btn-primary" type="submit">
                            <i class="fas fa-save"></i> Asignar Política
                        </button>
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cerrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>