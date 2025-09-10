<?php
    class Unit extends Controllers{
        public function __construct(){
            parent::__construct();
			session_start();
			if(empty($_SESSION['login'])){
				header('Location: '.base_url().'/login');
				die();
			}
			consent_permission(UNITS);
        }
        public function unit(){
            if(empty($_SESSION['permits_module']['v'])){
                header("Location:".base_url().'/dashboard');
            }
            $data['page_name'] = "Unidades de Medida";
            $data['page_title'] = "Gestión de Unidades";
            $data['home_page'] = "Dashboard";
            $data['previous_page'] = "Ajustes";
            $data['actual_page'] = "Unidades de Medida";
            $data['page_functions_js'] = "unit.js";
            $this->views->getView($this,"unit",$data);
        }
        public function list_records(){
            if($_SESSION['permits_module']['v']){
                $n = 1;
                $data = $this->model->list_records();
                for($i=0; $i < count($data); $i++){
                    $update = '';
                    $delete = '';
                    $data[$i]['n'] = $n++;
                    $data[$i]['date'] = date("d/m/Y", strtotime($data[$i]['registration_date']));
                    if($_SESSION['permits_module']['a']){
                      $update = '<a href="javascript:;" class="blue" data-toggle="tooltip" data-original-title="Editar" onclick="update(\''.encrypt($data[$i]['id']).'\')"><i class="fa fa-pencil-alt"></i></a>';
                      $update_2 = '<a href="javascript:;" class="dropdown-item" onclick="update(\''.encrypt($data[$i]['id']).'\')"><i class="fa fa-pencil-alt mr-1"></i>Editar</a>';
                    }else{
                        $update = '';
                        $update_2 = '';
                    }
                    if($_SESSION['permits_module']['e']){
                        if($data[$i]['id'] !=1){
                          $delete = '<a href="javascript:;" class="red" data-toggle="tooltip" data-original-title="Eliminar" onclick="remove(\''.encrypt($data[$i]['id']).'\')"><i class="far fa-trash-alt"></i></a>';
                          $delete_2 = '<a href="javascript:;" class="dropdown-item" onclick="remove(\''.encrypt($data[$i]['id']).'\')"><i class="far fa-trash-alt mr-1"></i>Eliminar</a>';
                        }else{
                          $delete = '';
                          $delete_2 = '';
                        }
                    }else{
                      $delete = '';
                      $delete_2 = '';
                    }
                    $options = '<div class="hidden-sm hidden-xs action-buttons">'.$update.$delete.'</div>';
                    $options .='<div class="hidden-md hidden-lg"><div class="dropdown">
                    <button class="btn btn-white btn-sm" data-toggle="dropdown" aria-expanded="false">
                      <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 29px, 0px);">
                      '.$update_2.$delete_2.'
                    </div>
                    </div></div>';
                    $data[$i]['options'] = $options;
                }
                echo json_encode($data,JSON_UNESCAPED_UNICODE);
            }
            die();
        }
        public function select_record(string $idunit){
            if($_SESSION['permits_module']['v']){
                $idunit = decrypt($idunit);
                $idunit = intval($idunit);
                if($idunit > 0){
                    $data = $this->model->select_record($idunit);
                    if(empty($data)){
                        $answer = array('status' => 'error', 'msg' => 'La información buscada, no ha sido encontrada.');
                    }else{
                      $data['encrypt_id'] = encrypt($data['id']);
                        $answer = array('status' => 'success', 'data' => $data);
                    }
                }else{
                  $answer = array('status' => 'error', 'msg' => 'La información buscada, no ha sido encontrada.');
                }
                echo json_encode($answer,JSON_UNESCAPED_UNICODE);
            }
            die();
        }
        public function list_units(){
            $html = "";
            $arrData = $this->model->list_units();
            if(count($arrData) > 0){
                for($i=0; $i < count($arrData); $i++) {
                    if($arrData[$i]['state'] == 1 ){
                        $html .= '<option value="'.encrypt($arrData[$i]['id']).'">'.$arrData[$i]['united'].'</option>';
                    }
                }
            }
            echo $html;
            die();
        }
        public function action(){
            if($_POST){
                if(empty($_POST['code']) || empty($_POST['united'])){
                    $response = array("status" => 'error', "msg" => 'Campos señalados son obligatorios.');
                }else{
                    $id = decrypt($_POST['idunit']);
                    $id = intval($id);
                    $code = strtoupper(strClean($_POST['code']));
					          $united = strtoupper(strClean($_POST['united']));
                    $state = intval(strClean($_POST['listStatus']));
                    $datetime = date("Y-m-d H:i:s");
                    if($id == 0){
                        $option = 1;
                        if($_SESSION['permits_module']['r']){
                            $request = $this->model->create($code,$united,$datetime,$state);
                        }
                    }else{
                        $option = 2;
                        if($_SESSION['permits_module']['a']){
                            $request = $this->model->modify($id,$code,$united,$state);
                        }
                    }
                    if($request == "success"){
                        if($option == 1){
                            $response = array('status' => 'success', 'msg' => 'Se ha registrado exitosamente.');
                        }else{
                            $response = array('status' => 'success', 'msg' => 'Se ha actualizado el registro exitosamente.');
                        }
                    }else if($request == 'exists'){
                        $response = array('status' => 'error', 'msg' => '¡Atención! El registro ya existe, ingrese otro.');
                    }else{
                        $response = array("status" => 'error', "msg" => 'No se pudo realizar esta operaciòn, intentelo nuevamente.');
                    }
                }
                echo json_encode($response,JSON_UNESCAPED_UNICODE);
            }
            die();
        }
        public function remove(){
            if($_POST){
                if($_SESSION['permits_module']['e']){
                    $idunit = decrypt($_POST['idunit']);
                    $idunit =  intval($idunit);
                    $request = $this->model->remove($idunit);
                    if($request == 'success'){
                      $response = array('status' => 'success', 'msg' => 'El registro se ha eliminado.');
                    }else if($request == 'exists'){
                        $response = array('status' => 'exists', 'msg' => 'La unidad esta en uso, imposible eliminar');
                    }else{
                      $response = array('status' => 'error', 'msg' => 'Error no se pudo eliminar.');
                    }
                    echo json_encode($response,JSON_UNESCAPED_UNICODE);
                }
            }
            die();
        }
    }
