<!-- Modal Nueva/Editar Política Queue Tree -->
<div class="modal fade" id="modalQueueTreePolicy" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header headerRegister">
                <h5 class="modal-title" id="titleModal">Nueva Política Queue Tree</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formQueueTreePolicy" name="formQueueTreePolicy" class="form-horizontal">
                    <input type="hidden" id="idPolicy" name="idPolicy" value="">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Nombre de la Política <span class="required">*</span></label>
                                <input class="form-control" id="txtName" name="name" type="text" placeholder="Ej: Plan 10M Residencial" required="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Router <span class="required">*</span></label>
                                <select class="form-control" id="listRouter" name="router_id" required="">
                                    <option value="">Seleccionar router</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Parent Queue</label>
                                <input class="form-control" id="txtParentQueue" name="parent_queue" type="text" placeholder="global" value="global">
                                <small class="form-text text-muted">Interfaz padre (ej: global, ether1, bridge1)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Target <span class="required">*</span></label>
                                <input class="form-control" id="txtTarget" name="target" type="text" placeholder="192.168.1.0/24" required="">
                                <small class="form-text text-muted">IP, subnet o interfaz objetivo</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Límite Máximo <span class="required">*</span></label>
                                <input class="form-control" id="txtMaxLimit" name="max_limit" type="text" placeholder="5M/10M" required="">
                                <small class="form-text text-muted">Formato: upload/download (ej: 5M/10M)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Límite Burst</label>
                                <input class="form-control" id="txtBurstLimit" name="burst_limit" type="text" placeholder="7M/15M">
                                <small class="form-text text-muted">Límite de ráfaga temporal</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Burst Threshold</label>
                                <input class="form-control" id="txtBurstThreshold" name="burst_threshold" type="text" placeholder="4M/8M">
                                <small class="form-text text-muted">Umbral para activar burst</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Burst Time</label>
                                <input class="form-control" id="txtBurstTime" name="burst_time" type="text" placeholder="8s/4s" value="8s/4s">
                                <small class="form-text text-muted">Duración del burst</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Prioridad</label>
                                <select class="form-control" id="listPriority" name="priority">
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
                                <label class="control-label">Queue Type</label>
                                <select class="form-control" id="listQueueType" name="queue_type">
                                    <option value="default" selected>Default</option>
                                    <option value="ethernet-default">Ethernet Default</option>
                                    <option value="wireless-default">Wireless Default</option>
                                    <option value="synchronization-default">Synchronization Default</option>
                                    <option value="hotspot-default">Hotspot Default</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">DSCP</label>
                                <input class="form-control" id="txtDscp" name="dscp" type="number" min="0" max="63" placeholder="0-63">
                                <small class="form-text text-muted">Marcado DSCP (0-63)</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Packet Mark</label>
                                <input class="form-control" id="txtPacketMark" name="packet_mark" type="text" placeholder="web-traffic">
                                <small class="form-text text-muted">Marca de paquete para filtrar</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Connection Mark</label>
                                <input class="form-control" id="txtConnectionMark" name="connection_mark" type="text" placeholder="web-conn">
                                <small class="form-text text-muted">Marca de conexión para filtrar</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Descripción</label>
                                <textarea class="form-control" id="txtDescription" name="description" rows="3" placeholder="Descripción detallada de la política QoS"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="tile-footer">
                        <button id="btnActionPolicy" class="btn btn-primary" type="submit">
                            <i class="fas fa-save"></i> Guardar
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