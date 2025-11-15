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
        // Obtener materias para los filtros de categoría
        $materias = array();
        if (method_exists($this->model, 'getMaterias')) {
            $materias = $this->model->getMaterias();
        }
        $data = array(
            'libros' => $libros,
            'materias' => $materias
        );
        $this->views->getView($this, "index", $data);
    }
    
}
?>