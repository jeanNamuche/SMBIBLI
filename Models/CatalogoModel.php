<?php
class CatalogoModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getLibrosPublicos()
    {
        $sql = "SELECT l.*, m.materia, a.autor, e.editorial FROM libro l INNER JOIN materia m ON l.id_materia = m.id INNER JOIN autor a ON l.id_autor = a.id INNER JOIN editorial e ON l.id_editorial = e.id WHERE l.estado = 1";
        return $this->selectAll($sql);
    }
}
?>