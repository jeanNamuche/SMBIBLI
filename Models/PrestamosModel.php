<?php
class PrestamosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getPrestamos()
    {
        // Esta función ya estaba correcta.
        $sql = "SELECT e.id, e.nombre, l.id AS id_l, l.titulo, p.id AS id_p, p.id_estudiante, p.id_libro, p.fecha_prestamo, p.fecha_devolucion, p.cantidad, p.observacion, p.estado 
                FROM prestamo p 
                INNER JOIN estudiante e ON p.id_estudiante = e.id 
                INNER JOIN libro l ON p.id_libro = l.id";
        $res = $this->selectAll($sql);
        return $res;
    }

    public function insertarPrestamo($estudiante, $libro, $cantidad, string $fecha_prestamo, string $fecha_devolucion, string $observacion)
    {
        // --- ¡CORREGIDO! ---
        // Se aseguran los IDs que vienen como parámetros
        $id_estudiante_seguro = (int)$estudiante;
        $id_libro_seguro = (int)$libro;
        $cantidad_segura = (int)$cantidad;

        $query = "INSERT INTO prestamo(id_estudiante, id_libro, fecha_prestamo, fecha_devolucion, cantidad, observacion, estado) VALUES (?,?,?,?,?,?,?)";
        $datos = array($id_estudiante_seguro, $id_libro_seguro, $fecha_prestamo, $fecha_devolucion, $cantidad_segura, $observacion, 1);
        $data = $this->insert($query, $datos);

        if ($data > 0) {
            // Se usa el ID seguro para la consulta
            $lib = "SELECT * FROM libro WHERE id = $id_libro_seguro";
            $resLibro = $this->select($lib);
            
            $total = (int)$resLibro['cantidad'] - $cantidad_segura;
            
            $libroUpdate = "UPDATE libro SET cantidad = ? WHERE id = ?";
            $datosLibro = array($total, $id_libro_seguro);
            $this->save($libroUpdate, $datosLibro);
            $res = $data;
        } else {
            $res = 0;
        }
        return $res;
    }

    // Nuevo método: crear una SOLICITUD de préstamo (estado = 0 = pendiente)
    // NO descuenta stock ni modifica libro (solo cuando admin aprueba)
    public function insertarSolicitud($id_estudiante, $id_libro, $cantidad, string $fecha_prestamo, string $fecha_devolucion, string $observacion = '')
    {
        $id_est_seg = (int)$id_estudiante;
        $id_lib_seg = (int)$id_libro;
        $cant_seg = (int)$cantidad;
        $obs_seg = (string)$observacion;

        $query = "INSERT INTO prestamo(id_estudiante, id_libro, fecha_prestamo, fecha_devolucion, cantidad, observacion, estado) VALUES (?,?,?,?,?,?,?)";
        $datos = array($id_est_seg, $id_lib_seg, $fecha_prestamo, $fecha_devolucion, $cant_seg, $obs_seg, 0);
        $data = $this->insert($query, $datos);

        return $data > 0 ? $data : 0;
    }

    // Método para aprobar una solicitud (cambiar estado de 0 a 1 y descontar stock)
    public function aprobarSolicitud($id_prestamo)
    {
        $id_seg = (int)$id_prestamo;

        // Obtener los datos de la solicitud
        $sql = "SELECT * FROM prestamo WHERE id = $id_seg AND estado = 0";
        $prestamo = $this->select($sql);

        if (empty($prestamo)) {
            return 0; // No existe o no está pendiente
        }

        $id_libro_seg = (int)$prestamo['id_libro'];
        $cant_seg = (int)$prestamo['cantidad'];

        // Verificar disponibilidad
        $lib = "SELECT * FROM libro WHERE id = $id_libro_seg";
        $resLibro = $this->select($lib);

        if (empty($resLibro) || (int)$resLibro['cantidad'] < $cant_seg) {
            return 0; // No hay cantidad suficiente
        }

        // Cambiar estado a 1 (activo)
        $updateEstado = "UPDATE prestamo SET estado = 1 WHERE id = $id_seg";
        $this->save($updateEstado, array());

        // Descontar stock del libro
        $total = (int)$resLibro['cantidad'] - $cant_seg;
        $updateLibro = "UPDATE libro SET cantidad = ? WHERE id = ?";
        $this->save($updateLibro, array($total, $id_libro_seg));

        return 1;
    }

    public function actualizarPrestamo($estado, $id)
    {
        // Esta función (la que te di) ya era correcta y segura.
        $id_seguro = (int)$id;
        $estado_seguro = (int)$estado;

        $sql = "UPDATE prestamo SET estado = ? WHERE id = ?";
        $datos = array($estado_seguro, $id_seguro);
        $data = $this->save($sql, $datos); 

        if ($data == 1) {
            // Si el estado es 2 (devuelto), re-sumamos el stock.
            if ($estado_seguro == 2) {
                $sql_prestamo = "SELECT id_libro, cantidad FROM prestamo WHERE id = $id_seguro";
                $resPrestamo = $this->select($sql_prestamo);
                
                $id_libro_seguro = (int)$resPrestamo['id_libro'];
                $cantidad_devuelta = (int)$resPrestamo['cantidad'];

                $sql_libro = "SELECT cantidad FROM libro WHERE id = $id_libro_seguro";
                $resLibro = $this->select($sql_libro);
                $stock_actual = (int)$resLibro['cantidad'];

                $nuevo_stock = $stock_actual + $cantidad_devuelta;
                $sql_update_libro = "UPDATE libro SET cantidad = ? WHERE id = ?";
                $datosLibro = array($nuevo_stock, $id_libro_seguro);
                $this->save($sql_update_libro, $datosLibro);
            }
            $res = "ok"; // Todo salió bien
        } else {
            $res = "error";
        }
        return $res;
    }

    public function selectDatos()
    {
        // Esta función no recibe parámetros, es segura.
        $sql = "SELECT * FROM configuracion";
        $res = $this->select($sql);
        return $res;
    }

    public function getCantLibro($libro)
    {
        // --- ¡CORREGIDO! ---
        // Se asegura el ID para evitar inyección SQL
        $id_libro_seguro = (int)$libro;
        $sql = "SELECT * FROM libro WHERE id = $id_libro_seguro";
        $res = $this->select($sql);
        return $res;
    }

    public function selectPrestamoDebe()
    {
        // Esta función ya estaba correcta.
        $sql = "SELECT e.id, e.nombre, l.id AS id_l, l.titulo, p.id AS id_p, p.id_estudiante, p.id_libro, p.fecha_prestamo, p.fecha_devolucion, p.cantidad, p.observacion, p.estado 
                FROM prestamo p 
                INNER JOIN estudiante e ON p.id_estudiante = e.id 
                INNER JOIN libro l ON p.id_libro = l.id 
                WHERE p.estado = 1 
                ORDER BY e.nombre ASC";
        $res = $this->selectAll($sql);
        return $res;
    }
/*
    public function verificarPermisos($id_user, $permiso)
    {
        // --- ¡CORREGIDO! ---
        // Se asegura el ID del usuario
        $id_user_seguro = (int)$id_user;

        // Asumimos que $permiso es un valor seguro/interno y lo escapamos
        $permiso_seguro = filter_var($permiso, FILTER_SANITIZE_STRING);

        $sql = "SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = $id_user_seguro AND p.nombre = '$permiso_seguro'";
        $existe = $this->select($sql);
        if ($existe != null || $existe != "") {
            $tiene = true;
        }
        return $tiene;
    }*/



    public function verificarPermisos($id_user, $permiso)
    {
        $tiene = false;
        // 1. Aseguramos el ID del usuario
        $id_user_seguro = (int)$id_user;

        // 2. Usamos addslashes() en lugar del filtro eliminado.
        // Esto es seguro porque $permiso viene de tu controlador (ej: "Prestamos")
        $permiso_seguro = addslashes($permiso);

        $sql = "SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = $id_user_seguro AND p.nombre = '$permiso_seguro'";
        $existe = $this->select($sql);
        
        // Tu lógica original (corregida para evitar un bug si $existe está vacío)
        if (!empty($existe)) {
            $tiene = true;
        }
        return $tiene;
    }

    public function getPrestamoLibro($id_prestamo)
    {
        // Esta función ya estaba correcta y segura.
        $id_seguro = (int)$id_prestamo;
        $sql = "SELECT e.id, e.codigo, e.nombre, e.grado, l.id, l.titulo, p.id, p.id_estudiante, p.id_libro, p.fecha_prestamo, p.fecha_devolucion, p.cantidad, p.observacion, p.estado 
                FROM prestamo p
                INNER JOIN estudiante e ON p.id_estudiante = e.id
                INNER JOIN libro l ON p.id_libro = l.id
                WHERE p.id = $id_seguro";
        $res = $this->select($sql);
        return $res;
    }
}
?>