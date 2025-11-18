<?php
class QuizModel extends Query {
    public function __construct() {
        parent::__construct();
    }

    // ===== PREGUNTAS =====
    public function obtenerPreguntasLibro($id_libro) {
        $sql = "SELECT p.id, p.id_libro, p.texto_pregunta, p.tipo, p.numero_pregunta, COUNT(o.id) as total_opciones 
                FROM quiz_preguntas p
                LEFT JOIN quiz_opciones o ON p.id = o.id_pregunta AND o.estado = 1
                WHERE p.id_libro = " . (int)$id_libro . " AND p.estado = 1
                GROUP BY p.id
                ORDER BY p.numero_pregunta ASC";
        return $this->selectAll($sql);
    }

    public function obtenerPregunta($id_pregunta) {
        $sql = "SELECT * FROM quiz_preguntas WHERE id = " . (int)$id_pregunta . " AND estado = 1";
        return $this->select($sql);
    }

    public function guardarPregunta($id_libro, $texto_pregunta, $tipo, $numero_pregunta) {
        // Verificar si ya existe pregunta con ese número
        $check = "SELECT id FROM quiz_preguntas WHERE id_libro = " . (int)$id_libro . " AND numero_pregunta = " . (int)$numero_pregunta;
        $existe = $this->select($check);

        if ($existe) {
            // Actualizar
            $sql = "UPDATE quiz_preguntas SET texto_pregunta = ?, tipo = ? WHERE id = ?";
            $datos = array($texto_pregunta, $tipo, $existe['id']);
            return $this->save($sql, $datos);
        } else {
            // Insertar
            $sql = "INSERT INTO quiz_preguntas(id_libro, texto_pregunta, tipo, numero_pregunta) VALUES (?,?,?,?)";
            $datos = array($id_libro, $texto_pregunta, $tipo, $numero_pregunta);
            return $this->insert($sql, $datos);
        }
    }

    public function eliminarPregunta($id_pregunta) {
        $sql = "UPDATE quiz_preguntas SET estado = 0 WHERE id = ?";
        $datos = array($id_pregunta);
        return $this->save($sql, $datos);
    }

    // ===== OPCIONES =====
    public function obtenerOpcionesPregunta($id_pregunta) {
        $sql = "SELECT * FROM quiz_opciones 
                WHERE id_pregunta = " . (int)$id_pregunta . " AND estado = 1
                ORDER BY orden ASC";
        return $this->selectAll($sql);
    }

    public function guardarOpcion($id_pregunta, $texto_opcion, $es_correcta, $orden) {
        $sql = "INSERT INTO quiz_opciones(id_pregunta, texto_opcion, es_correcta, orden) 
                VALUES (?,?,?,?)";
        $datos = array($id_pregunta, $texto_opcion, $es_correcta, $orden);
        return $this->insert($sql, $datos);
    }

    public function actualizarOpcion($id_opcion, $texto_opcion, $es_correcta) {
        $sql = "UPDATE quiz_opciones SET texto_opcion = ?, es_correcta = ? WHERE id = ?";
        $datos = array($texto_opcion, $es_correcta, $id_opcion);
        return $this->save($sql, $datos);
    }

    public function eliminarOpcion($id_opcion) {
        $sql = "UPDATE quiz_opciones SET estado = 0 WHERE id = ?";
        $datos = array($id_opcion);
        return $this->save($sql, $datos);
    }

    public function limpiarOpcionesPregunta($id_pregunta) {
        $sql = "UPDATE quiz_opciones SET estado = 0 WHERE id_pregunta = ?";
        $datos = array($id_pregunta);
        return $this->save($sql, $datos);
    }

    // ===== ROMPECABEZAS =====
    public function obtenerRompecabezas($id_libro) {
        $sql = "SELECT * FROM quiz_rompecabezas WHERE id_libro = " . (int)$id_libro . " AND estado = 1";
        return $this->select($sql);
    }

    public function guardarRompecabezas($id_libro, $titulo, $instrucciones) {
        $check = "SELECT id FROM quiz_rompecabezas WHERE id_libro = " . (int)$id_libro;
        $existe = $this->select($check);

        if ($existe) {
            $sql = "UPDATE quiz_rompecabezas SET titulo = ?, instrucciones = ? WHERE id = ?";
            $datos = array($titulo, $instrucciones, $existe['id']);
            return $this->save($sql, $datos) ? $existe['id'] : 0;
        } else {
            $sql = "INSERT INTO quiz_rompecabezas(id_libro, titulo, instrucciones) VALUES (?,?,?)";
            $datos = array($id_libro, $titulo, $instrucciones);
            return $this->insert($sql, $datos);
        }
    }

    public function obtenerPiezasRompecabezas($id_rompecabezas) {
        $sql = "SELECT * FROM quiz_rompecabezas_piezas 
                WHERE id_rompecabezas = " . (int)$id_rompecabezas . " AND estado = 1
                ORDER BY orden_display ASC";
        return $this->selectAll($sql);
    }

    public function guardarPiezaRompecabezas($id_rompecabezas, $texto_pieza, $posicion_correcta, $orden_display) {
        $sql = "INSERT INTO quiz_rompecabezas_piezas(id_rompecabezas, texto_pieza, posicion_correcta, orden_display) 
                VALUES (?,?,?,?)";
        $datos = array($id_rompecabezas, $texto_pieza, $posicion_correcta, $orden_display);
        return $this->insert($sql, $datos);
    }

    public function limpiarPiezasRompecabezas($id_rompecabezas) {
        $sql = "UPDATE quiz_rompecabezas_piezas SET estado = 0 WHERE id_rompecabezas = ?";
        $datos = array($id_rompecabezas);
        return $this->save($sql, $datos);
    }

    public function eliminarRompecabezas($id_rompecabezas) {
        $sql = "UPDATE quiz_rompecabezas SET estado = 0 WHERE id = ?";
        $datos = array($id_rompecabezas);
        return $this->save($sql, $datos);
    }

    // ===== INTENTOS =====
    public function guardarIntento($id_estudiante, $id_libro, $tipo, $puntuacion, $respuestas) {
        $sql = "INSERT INTO quiz_intentos(id_estudiante, id_libro, tipo, puntuacion, respuestas) 
                VALUES (?,?,?,?,?)";
        $datos = array($id_estudiante, $id_libro, $tipo, $puntuacion, $respuestas);
        return $this->insert($sql, $datos);
    }

    public function obtenerIntentosEstudiante($id_estudiante, $id_libro, $tipo) {
        $sql = "SELECT * FROM quiz_intentos 
                WHERE id_estudiante = " . (int)$id_estudiante . " AND id_libro = " . (int)$id_libro . " AND tipo = '" . $tipo . "' AND estado = 1
                ORDER BY fecha_intento DESC";
        return $this->selectAll($sql);
    }

    public function obtenerMejorIntento($id_estudiante, $id_libro, $tipo) {
        $sql = "SELECT * FROM quiz_intentos 
                WHERE id_estudiante = " . (int)$id_estudiante . " AND id_libro = " . (int)$id_libro . " AND tipo = '" . $tipo . "' AND estado = 1
                ORDER BY puntuacion DESC, fecha_intento DESC
                LIMIT 1";
        return $this->select($sql);
    }
}
?>
