<?php
class Catalogo extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->cargarModel();
    }
    public function index()
    {
        $libros = $this->model->getLibrosPublicos();
        $this->views->getView($this, "index", $libros);
    }
}
?>