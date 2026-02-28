<?php
class LibrosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getLibros()
    {
        $sql = "SELECT l.*, m.materia, a.autor, e.editorial FROM libro l INNER JOIN materia m ON l.id_materia = m.id INNER JOIN autor a ON l.id_autor = a.id INNER JOIN editorial e ON l.id_editorial = e.id";
        $res = $this->selectAll($sql);
        return $res;
    }
    public function insertarLibros($titulo,$id_autor,$id_editorial,$id_materia,$cantidad,$num_pagina,$nivel,$lugar_estante,$anio_edicion,$descripcion,$imgNombre,$pdfPath=null)
    {
        $verificar = "SELECT * FROM libro WHERE titulo = '$titulo'";
        $existe = $this->select($verificar);
        if (empty($existe)) {
            // Verificar si la tabla tiene las nuevas columnas
            $cols = array('nivel', 'lugar_estante', 'anio_edicion');
            $in = "'" . implode("','", $cols) . "'";
            $sqlCols = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".db."' AND TABLE_NAME='libro' AND COLUMN_NAME IN ($in)";
            $cntRes = $this->select($sqlCols);
            $usesNew = ($cntRes && isset($cntRes['cnt']) && $cntRes['cnt'] == count($cols));

            // Verificar si existe la columna num_pagina
            $sqlNum = "SELECT COUNT(*) AS cnt_num FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".db."' AND TABLE_NAME='libro' AND COLUMN_NAME='num_pagina'";
            $numRes = $this->select($sqlNum);
            $hasNum = ($numRes && isset($numRes['cnt_num']) && $numRes['cnt_num'] > 0);

            if ($usesNew) {
                if ($hasNum) {
                    // Orden de columnas: nivel, lugar_estante, anio_edicion, num_pagina, descripcion, imagen, pdf_path
                    $query = "INSERT INTO libro(titulo, id_autor, id_editorial, id_materia, cantidad, nivel, lugar_estante, anio_edicion, num_pagina, descripcion, imagen, pdf_path) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
                    $datos = array($titulo, $id_autor, $id_editorial, $id_materia, $cantidad, $nivel, $lugar_estante, (!empty($anio_edicion) ? $anio_edicion : date('Y-m-d')), (!empty($num_pagina) ? $num_pagina : 0), $descripcion, $imgNombre, $pdfPath);
                } else {
                    // No existe num_pagina en esquema nuevo
                    $query = "INSERT INTO libro(titulo, id_autor, id_editorial, id_materia, cantidad, nivel, lugar_estante, anio_edicion, descripcion, imagen, pdf_path) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
                    $datos = array($titulo, $id_autor, $id_editorial, $id_materia, $cantidad, $nivel, $lugar_estante, (!empty($anio_edicion) ? $anio_edicion : date('Y-m-d')), $descripcion, $imgNombre, $pdfPath);
                }
            } else {
                // Base de datos antigua: usar anio_edicion (hoy) y num_pagina = 0 para compatibilidad
                $query = "INSERT INTO libro(titulo, id_autor, id_editorial, id_materia, cantidad, anio_edicion, num_pagina, descripcion, imagen, pdf_path) VALUES (?,?,?,?,?,?,?,?,?,?)";
                $datos = array($titulo, $id_autor, $id_editorial, $id_materia, $cantidad, date('Y-m-d'), 0, $descripcion, $imgNombre, $pdfPath);
            }

            try {
                $data = $this->save($query, $datos);
                if ($data == 1) {
                    $res = "ok";
                } else {
                    $res = "error";
                }
            } catch (Exception $e) {
                $res = "error: " . $e->getMessage();
            }
        } else {
            $res = "existe";
        }
        return $res;
    }
    public function editLibros($id)
    {
        // Obtener libro junto con nombres de autor, editorial y materia
    $sql = "SELECT l.*, m.materia, a.autor, e.editorial FROM libro l
        INNER JOIN materia m ON l.id_materia = m.id
        INNER JOIN autor a ON l.id_autor = a.id
        INNER JOIN editorial e ON l.id_editorial = e.id
        WHERE l.id = $id";
        $res = $this->select($sql);
        return $res;
    }
    public function actualizarLibros($titulo, $id_autor, $id_editorial, $id_materia, $cantidad, $num_pagina, $nivel, $lugar_estante, $anio_edicion, $descripcion, $imgNombre, $pdfPath, $id)
    {
        // Detectar esquema
        $cols = array('nivel', 'lugar_estante');
        $in = "'" . implode("','", $cols) . "'";
        $sqlCols = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".db."' AND TABLE_NAME='libro' AND COLUMN_NAME IN ($in)";
        $cntRes = $this->select($sqlCols);
        $usesNew = ($cntRes && isset($cntRes['cnt']) && $cntRes['cnt'] == count($cols));

        // Verificar si existe la columna num_pagina
        $sqlNum = "SELECT COUNT(*) AS cnt_num FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".db."' AND TABLE_NAME='libro' AND COLUMN_NAME='num_pagina'";
        $numRes = $this->select($sqlNum);
        $hasNum = ($numRes && isset($numRes['cnt_num']) && $numRes['cnt_num'] > 0);

        if ($usesNew) {
            $anio = (!empty($anio_edicion)) ? $anio_edicion : date('Y-m-d');
            if ($hasNum) {
                // Orden: nivel, lugar_estante, anio_edicion, num_pagina, descripcion, imagen, pdf_path
                $query = "UPDATE libro SET titulo = ?, id_autor=?, id_editorial=?, id_materia=?, cantidad=?, nivel=?, lugar_estante=?, anio_edicion=?, num_pagina=?, descripcion=?, imagen=?, pdf_path=? WHERE id = ?";
                $datos = array($titulo, $id_autor, $id_editorial, $id_materia, $cantidad, $nivel, $lugar_estante, $anio, (!empty($num_pagina) ? $num_pagina : 0), $descripcion, $imgNombre, $pdfPath, $id);
            } else {
                $query = "UPDATE libro SET titulo = ?, id_autor=?, id_editorial=?, id_materia=?, cantidad=?, nivel=?, lugar_estante=?, anio_edicion=?, descripcion=?, imagen=?, pdf_path=? WHERE id = ?";
                $datos = array($titulo, $id_autor, $id_editorial, $id_materia, $cantidad, $nivel, $lugar_estante, $anio, $descripcion, $imgNombre, $pdfPath, $id);
            }
        } else {
            // BD antigua: actualizar anio_edicion con fecha actual y num_pagina a 0 si es necesario
            $query = "UPDATE libro SET titulo = ?, id_autor=?, id_editorial=?, id_materia=?, cantidad=?, anio_edicion=?, num_pagina=?, descripcion=?, imagen=?, pdf_path=? WHERE id = ?";
            $datos = array($titulo, $id_autor, $id_editorial, $id_materia, $cantidad, date('Y-m-d'), 0, $descripcion, $imgNombre, $pdfPath, $id);
        }

        try {
            $data = $this->save($query, $datos);
            if ($data == 1) {
                $res = "modificado";
            } else {
                $res = "error";
            }
        } catch (Exception $e) {
            $res = "error: " . $e->getMessage();
        }
        return $res;
    }
    public function estadoLibros($estado, $id)
    {
        $query = "UPDATE libro SET estado = ? WHERE id = ?";
        $datos = array($estado, $id);
        $data = $this->save($query, $datos);
        return $data;
    }
    public function buscarLibro($valor, $nivel = null)
    {
        $sql = "SELECT id, titulo AS text FROM libro WHERE titulo LIKE '%" . $valor . "%' AND estado = 1";
        if (!empty($nivel)) {
            $sql .= " AND nivel = '" . $nivel . "'";
        }
        $sql .= " LIMIT 10";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function verificarPermisos($id_user, $permiso)
    {
        $tiene = false;
        $sql = "SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = $id_user AND p.nombre = '$permiso'";
        $existe = $this->select($sql);
        if ($existe != null || $existe != "") {
            $tiene = true;
        }
        return $tiene;
    }
    //f
    public function getAutores()
    {
        $sql = "SELECT id, autor FROM autor ORDER BY autor ASC";
        return $this->selectAll($sql);
    }

    public function getEditoriales()
    {
        $sql = "SELECT id, editorial FROM editorial ORDER BY editorial ASC";
        return $this->selectAll($sql);
    }

    public function getMaterias()
    {
        $sql = "SELECT id, materia FROM materia ORDER BY materia ASC";
        return $this->selectAll($sql);
    }
}
