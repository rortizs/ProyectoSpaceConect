<?php
class Controllers
{
    protected $views;
    protected $model;
    
    public function __construct()
    {
        $this->views = new Views();
        $this->loadModel();
    }

    public function loadModel()
    {
        $model = get_class($this) . "Model";
        $modelFile = "Models/" . $model . ".php";
        if (file_exists($modelFile)) {
            require_once($modelFile);
            $this->model = new $model();
        }
    }

    public function json($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
    }

    public function send($data)
    {
        echo $data;
    }
}
