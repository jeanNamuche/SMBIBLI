<?php
class EstudiantesModel extends Query{
    public function __construct()
    {
        parent::__construct();
    }
    public function getEstudiantes()
    {
        $sql = "SELECT * FROM estudiante";
        $res = $this->selectAll($sql);
        return $res;
    }
    // New insert signature to match updated schema: codigo_estudiante, numero_documento, grado, seccion,
    // apellido_paterno, apellido_materno, nombres, id_usuario (nullable)
    public function insertarEstudiante($codigo_estudiante, $numero_documento, $grado, $seccion, $apellido_paterno, $apellido_materno, $nombres, $id_usuario = null, $nivel = null)
    {
        // Check existence by codigo_estudiante or numero_documento
        $verificar = "SELECT * FROM estudiante WHERE codigo = '$codigo_estudiante' OR dni = '$numero_documento'";
        $existe = $this->select($verificar);
        if (empty($existe)) {
            // include nivel column if table has it
            $hasNivel = $this->select("SELECT COUNT(*) AS cnt_n FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".db."' AND TABLE_NAME='estudiante' AND COLUMN_NAME='nivel'");
            if ($hasNivel && isset($hasNivel['cnt_n']) && $hasNivel['cnt_n'] > 0) {
                $query = "INSERT INTO estudiante(codigo, dni, grado, seccion, nivel, apellido_paterno, apellido_materno, nombre, id_usuario) VALUES (?,?,?,?,?,?,?,?,?)";
                $datos = array($codigo_estudiante, $numero_documento, $grado, $seccion, $nivel, $apellido_paterno, $apellido_materno, $nombres, $id_usuario);
            } else {
                $query = "INSERT INTO estudiante(codigo, dni, grado, seccion, apellido_paterno, apellido_materno, nombre, id_usuario) VALUES (?,?,?,?,?,?,?,?)";
                $datos = array($codigo_estudiante, $numero_documento, $grado, $seccion, $apellido_paterno, $apellido_materno, $nombres, $id_usuario);
            }
            // Use insert to get last insert id
            $insertId = $this->insert($query, $datos);
            if ($insertId > 0) {
                $res = $insertId;
            } else {
                $res = 0;
            }
        } else {
            $res = "existe";
        }
        return $res;
    }
    public function editEstudiante($id)
    {
        $sql = "SELECT * FROM estudiante WHERE id = $id";
        $res = $this->select($sql);
        return $res;
    }
    public function actualizarEstudiante($codigo_estudiante, $numero_documento, $grado, $seccion, $apellido_paterno, $apellido_materno, $nombres, $id_usuario, $id, $nivel = null)
    {
        // update with nivel if column exists
        $hasNivel = $this->select("SELECT COUNT(*) AS cnt_n FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".db."' AND TABLE_NAME='estudiante' AND COLUMN_NAME='nivel'");
        if ($hasNivel && isset($hasNivel['cnt_n']) && $hasNivel['cnt_n'] > 0) {
            $query = "UPDATE estudiante SET codigo = ?, dni = ?, grado = ?, seccion = ?, nivel = ?, apellido_paterno = ?, apellido_materno = ?, nombre = ?, id_usuario = ? WHERE id = ?";
            $datos = array($codigo_estudiante, $numero_documento, $grado, $seccion, $nivel, $apellido_paterno, $apellido_materno, $nombres, $id_usuario, $id);
        } else {
            $query = "UPDATE estudiante SET codigo = ?, dni = ?, grado = ?, seccion = ?, apellido_paterno = ?, apellido_materno = ?, nombre = ?, id_usuario = ? WHERE id = ?";
            $datos = array($codigo_estudiante, $numero_documento, $grado, $seccion, $apellido_paterno, $apellido_materno, $nombres, $id_usuario, $id);
        }
        $data = $this->save($query, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }
    public function estadoEstudiante($estado, $id)
    {
        $query = "UPDATE estudiante SET estado = ? WHERE id = ?";
        $datos = array($estado, $id);
        $data = $this->save($query, $datos);
        return $data;
    }
    public function buscarEstudiante($valor)
    {
        $sql = "SELECT id, codigo, nombre AS text FROM estudiante WHERE codigo LIKE '%" . $valor . "%' AND estado = 1 OR nombre LIKE '%" . $valor . "%'  AND estado = 1 LIMIT 10";
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

    // Obtener id de estudiante por id_usuario; si no existe, crear un registro básico y devolver el id
    public function obtenerOCrearPorUsuario($id_usuario, $nombre = '')
    {
        $id_usuario = (int)$id_usuario;
        if ($id_usuario <= 0) return 0;
        $sql = "SELECT id FROM estudiante WHERE id_usuario = " . $id_usuario;
        $res = $this->select($sql);
        if (!empty($res) && isset($res['id'])) {
            return (int)$res['id'];
        }

        // No existe: crear un registro mínimo. Usamos valores por defecto para campos requeridos.
        $codigo = 'USR' . $id_usuario;
        $dni = 'DNI' . $id_usuario . substr((string)time(), -4);
        $grado = null;
        $seccion = null;
        $nivel = null;
        $apellido_paterno = null;
        $apellido_materno = null;
        $nombres = $nombre ?: 'Usuario ' . $id_usuario;

        $nuevoId = $this->insertarEstudiante($codigo, $dni, $grado, $seccion, $apellido_paterno, $apellido_materno, $nombres, $id_usuario, $nivel);
        if ($nuevoId && is_numeric($nuevoId)) {
            return (int)$nuevoId;
        }
        return 0;
    }
}
