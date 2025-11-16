<?php
class Prestamos extends Controller
{
    public function __construct()
    {
        session_start();
        if (empty($_SESSION['activo'])) {
            header("location: " . base_url);
        }
        parent::__construct();
        $id_user = $_SESSION['id_usuario'];
        $perm = $this->model->verificarPermisos($id_user, "Prestamos");
        if (!$perm && $id_user != 1) {
            $this->views->getView($this, "permisos");
            exit;
        }
    }
    public function index()
    {
        $this->views->getView($this, "index");
    }
    /*
    public function listar()
    {
        $data = $this->model->getPrestamos();
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]['estado'] == 1) {
                $data[$i]['estado'] = '<span class="badge badge-secondary">Prestado</span>';
                $data[$i]['acciones'] = '<div>
                <button class="btn btn-primary" type="button" onclick="btnEntregar(' . $data[$i]['id'] . ');"><i class="fa fa-hourglass-start"></i></button>
                <a class="btn btn-danger" target="_blank" href="'.base_url.'Prestamos/ticked/'. $data[$i]['id'].'"><i class="fa fa-file-pdf-o"></i></a>
                <div/>';
            } else {
                $data[$i]['estado'] = '<span class="badge badge-primary">Devuelto</span>';
                $data[$i]['acciones'] = '<div>
                <a class="btn btn-danger" target="_blank" href="'.base_url.'Prestamos/ticked/'. $data[$i]['id'].'"><i class="fa fa-file-pdf-o"></i></a>
                <div/>';
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }*/

    public function listar()
    {
        // Listar SOLO préstamos activos (estado=1) y devueltos (estado=2), NO solicitudes (estado=0)
        $sql = "SELECT e.id, e.nombre, l.id AS id_l, l.titulo, p.id AS id_p, p.id_estudiante, p.id_libro, p.fecha_prestamo, p.fecha_devolucion, p.cantidad, p.observacion, p.estado 
                FROM prestamo p 
                INNER JOIN estudiante e ON p.id_estudiante = e.id 
                INNER JOIN libro l ON p.id_libro = l.id
                WHERE p.estado IN (1, 2)";
        $data = $this->model->selectAll($sql);
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]['estado'] == 1) {
                $data[$i]['estado'] = '<span class="badge badge-secondary">Prestado</span>';
                
                // --- CORREGIDO ---
                $data[$i]['acciones'] = '<div>
                <button class="btn btn-primary" type="button" onclick="btnEntregar(' . $data[$i]['id_p'] . ');"><i class="fa fa-hourglass-start"></i></button>
                <a class="btn btn-danger" target="_blank" href="'.base_url.'Prestamos/ticked/'. $data[$i]['id_p'] . '"><i class="fa fa-file-pdf-o"></i></a>
                <div/>';
                // --- FIN CORRECCIÓN ---

            } else {
                $data[$i]['estado'] = '<span class="badge badge-primary">Devuelto</span>';
                
                // --- CORREGIDO ---
                $data[$i]['acciones'] = '<div>
                <a class="btn btn-danger" target="_blank" href="'.base_url.'Prestamos/ticked/'. $data[$i]['id_p'] . '"><i class="fa fa-file-pdf-o"></i></a>
                <div/>';
                // --- FIN CORRECCIÓN ---
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }


/*
    public function registrar()
    {
        $libro = strClean($_POST['libro']);
        $estudiante = strClean($_POST['estudiante']);
        $cantidad = strClean($_POST['cantidad']);
        $fecha_prestamo = strClean($_POST['fecha_prestamo']);
        $fecha_devolucion = strClean($_POST['fecha_devolucion']);
        $observacion = strClean($_POST['observacion']);
        if (empty($libro) || empty($estudiante) || empty($cantidad) || empty($fecha_prestamo) || empty($fecha_devolucion)) {
            $msg = array('msg' => 'Todo los campos son requeridos', 'icono' => 'warning');
        } else {
            $verificar_cant = $this->model->getCantLibro($libro);
            if ($verificar_cant['cantidad'] >= $cantidad) {
                $data = $this->model->insertarPrestamo($estudiante,$libro, $cantidad, $fecha_prestamo, $fecha_devolucion, $observacion);
                if ($data > 0) {
                    $msg = array('msg' => 'Libro Prestado', 'icono' => 'success', 'id' => $data);
                } else if ($data == "existe") {
                    $msg = array('msg' => 'El libro ya esta prestado', 'icono' => 'warning');
                } else {
                    $msg = array('msg' => 'Error al prestar', 'icono' => 'error');
                }
            }else{
                $msg = array('msg' => 'Stock no disponible', 'icono' => 'warning');
            }
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }*/



    public function registrar()
{
    $libro = strClean($_POST['libro']);
    $estudiante = strClean($_POST['estudiante']);
    $cantidad = strClean($_POST['cantidad']);
    $fecha_prestamo = strClean($_POST['fecha_prestamo']);
    $fecha_devolucion = strClean($_POST['fecha_devolucion']);
    $observacion = strClean($_POST['observacion']);
    
    if (empty($libro) || empty($estudiante) || empty($cantidad) || empty($fecha_prestamo) || empty($fecha_devolucion)) {
        $msg = array('msg' => 'Todo los campos son requeridos', 'icono' => 'warning');
    } else {
        // 1. Verificamos el stock disponible (esto está perfecto)
        $verificar_cant = $this->model->getCantLibro($libro);
        
        if ($verificar_cant['cantidad'] >= $cantidad) {
            
            // 2. Intentamos insertar el préstamo
            $data = $this->model->insertarPrestamo($estudiante, $libro, $cantidad, $fecha_prestamo, $fecha_devolucion, $observacion);

            // 3. Lógica limpia: solo hay éxito (> 0) o error (0)
            if ($data > 0) {
                $msg = array('msg' => 'Libro Prestado', 'icono' => 'success', 'id' => $data);
            } else {
                $msg = array('msg' => 'Error al registrar el préstamo', 'icono' => 'error');
            }

        } else {
            $msg = array('msg' => 'Stock no disponible', 'icono' => 'warning');
        }
    }
    echo json_encode($msg, JSON_UNESCAPED_UNICODE);
    die();
}


    public function entregar($id)
    {
        // Cambiar estado a 2 (devuelto) para que se re-sume el stock
        $datos = $this->model->actualizarPrestamo(2, $id);
        if ($datos == "ok") {
            $msg = array('msg' => 'Libro recibido', 'icono' => 'success');
        }else{
            $msg = array('msg' => 'Error al recibir el libro', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();

    }
    public function pdf()
    {
        $datos = $this->model->selectDatos();
        $prestamo = $this->model->selectPrestamoDebe();
        if (empty($prestamo)) {
            header('Location: ' . base_url . 'Configuracion/vacio');
        }
        require_once 'Libraries/pdf/fpdf.php';
        $pdf = new FPDF('P', 'mm', 'letter');
        $pdf->AddPage();
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetTitle("Prestamos");
        $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(195, 5, (function_exists('safe_utf8_decode') ? safe_utf8_decode($datos['nombre']) : (function_exists('utf8_decode') ? utf8_decode($datos['nombre']) : $datos['nombre'])), 0, 1, 'C');

        $pdf->Image(base_url. "Assets/img/logo.png", 180, 10, 30, 30, 'PNG');
        $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 5, (function_exists('safe_utf8_decode') ? safe_utf8_decode("Teléfono: ") : (function_exists('utf8_decode') ? utf8_decode("Teléfono: ") : "Teléfono: ")), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, $datos['telefono'], 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 5, (function_exists('safe_utf8_decode') ? safe_utf8_decode("Dirección: ") : (function_exists('utf8_decode') ? utf8_decode("Dirección: ") : "Dirección: ")), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(20, 5, (function_exists('safe_utf8_decode') ? safe_utf8_decode($datos['direccion']) : (function_exists('utf8_decode') ? utf8_decode($datos['direccion']) : $datos['direccion'])), 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, "Correo: ", 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(20, 5, (function_exists('safe_utf8_decode') ? safe_utf8_decode($datos['correo']) : (function_exists('utf8_decode') ? utf8_decode($datos['correo']) : $datos['correo'])), 0, 1, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(196, 5, "Detalle de Prestamos", 1, 1, 'C', 1);
        $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(14, 5, (function_exists('safe_utf8_decode') ? safe_utf8_decode('N°') : (function_exists('utf8_decode') ? utf8_decode('N°') : 'N°')), 1, 0, 'L');
    $pdf->Cell(50, 5, (function_exists('safe_utf8_decode') ? safe_utf8_decode('Estudiantes') : (function_exists('utf8_decode') ? utf8_decode('Estudiantes') : 'Estudiantes')), 1, 0, 'L');
        $pdf->Cell(87, 5, 'Libros', 1, 0, 'L');
        $pdf->Cell(30, 5, 'Fecha Prestamo', 1, 0, 'L');
        $pdf->Cell(15, 5, 'Cant.', 1, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $contador = 1;
        foreach ($prestamo as $row) {
            $pdf->Cell(14, 5, $contador, 1, 0, 'L');
            $pdf->Cell(50, 5, $row['nombre'], 1, 0, 'L');
            $pdf->Cell(87, 5, (function_exists('safe_utf8_decode') ? safe_utf8_decode($row['titulo']) : (function_exists('utf8_decode') ? utf8_decode($row['titulo']) : $row['titulo'])), 1, 0, 'L');
            $pdf->Cell(30, 5, $row['fecha_prestamo'], 1, 0, 'L');
            $pdf->Cell(15, 5, $row['cantidad'], 1, 1, 'L');
            $contador++;
        }
        $pdf->Output("prestamos.pdf", "I");
    }
    public function ticked($id_prestamo)
    {
        $datos = $this->model->selectDatos();
        $prestamo = $this->model->getPrestamoLibro($id_prestamo);
        if (empty($prestamo)) {
            header('Location: '.base_url. 'Configuracion/vacio');
        }
        require_once 'Libraries/pdf/fpdf.php';
        $pdf = new FPDF('P', 'mm', array(80, 200));
        $pdf->AddPage();
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetTitle("Prestamos");
        $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(65, 5, (function_exists('safe_utf8_decode') ? safe_utf8_decode($datos['nombre']) : (function_exists('utf8_decode') ? utf8_decode($datos['nombre']) : $datos['nombre'])), 0, 1, 'C');

        $pdf->Image(base_url . "Assets/img/logo.png", 55, 15, 20, 20, 'PNG');
        $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(15, 5, (function_exists('safe_utf8_decode') ? safe_utf8_decode("Teléfono: ") : (function_exists('utf8_decode') ? utf8_decode("Teléfono: ") : "Teléfono: ")), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(15, 5, $datos['telefono'], 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(15, 5, (function_exists('safe_utf8_decode') ? safe_utf8_decode("Dirección: ") : (function_exists('utf8_decode') ? utf8_decode("Dirección: ") : "Dirección: ")), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(15, 5, (function_exists('safe_utf8_decode') ? safe_utf8_decode($datos['direccion']) : (function_exists('utf8_decode') ? utf8_decode($datos['direccion']) : $datos['direccion'])), 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(15, 5, "Correo: ", 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(15, 5, (function_exists('safe_utf8_decode') ? safe_utf8_decode($datos['correo']) : (function_exists('utf8_decode') ? utf8_decode($datos['correo']) : $datos['correo'])), 0, 1, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(72, 5, "Detalle de Prestamos", 1, 1, 'C', 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(60, 5, 'Libros', 1, 0, 'L');
        $pdf->Cell(12, 5, 'Cant.', 1, 1, 'L');
        $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(60, 5, (function_exists('safe_utf8_decode') ? safe_utf8_decode($prestamo['titulo']) : (function_exists('utf8_decode') ? utf8_decode($prestamo['titulo']) : $prestamo['titulo'])), 1, 0, 'L');
        $pdf->Cell(12, 5, $prestamo['cantidad'], 1, 1, 'L');
        $pdf->Ln();
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(72, 5, "Estudiante", 1, 1, 'C', 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(35, 5, 'Nombre.', 1, 0, 'L');
        $pdf->Cell(37, 5, 'Grado.', 1, 1, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(35, 5, $prestamo['nombre'], 1, 0, 'L');
        $pdf->Cell(37, 5, $prestamo['grado'], 1, 1, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(72, 5, 'Fecha Prestamo', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(72, 5, $prestamo['fecha_prestamo'], 0, 1, 'C');
        $pdf->Output("prestamos.pdf", "I");
    }

    // Endpoint: Listar solicitudes pendientes (estado = 0)
    // Estados: 0=Solicitud, 1=Préstamo Activo, 2=Devuelto
    public function solicitudesPendientes()
    {
        header('Content-Type: application/json; charset=utf-8');
        $sql = "SELECT p.id, e.id AS id_estudiante, e.nombre, e.codigo, l.id AS id_libro, l.titulo, a.autor AS autor, 
                       p.cantidad, p.observacion, p.fecha_prestamo, p.estado
                FROM prestamo p
                INNER JOIN estudiante e ON p.id_estudiante = e.id
                INNER JOIN libro l ON p.id_libro = l.id
                LEFT JOIN autor a ON l.id_autor = a.id
                WHERE p.estado = 0
                ORDER BY p.id DESC";
        $data = $this->model->selectAll($sql);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    // Endpoint: Aprobar una solicitud (cambiar estado de 0 a 1)
    public function aprobarSolicitud()
    {
        header('Content-Type: application/json; charset=utf-8');
        $msg = array('msg' => 'Error desconocido', 'icono' => 'error');

        try {
            $id_prestamo = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id_prestamo <= 0) {
                $msg = array('msg' => 'ID inválido', 'icono' => 'warning');
                throw new Exception('Invalid ID');
            }

            $resultado = $this->model->aprobarSolicitud($id_prestamo);
            if ($resultado > 0) {
                $msg = array('msg' => '✅ Solicitud aprobada. El préstamo ahora está activo.', 'icono' => 'success');
            } else {
                $msg = array('msg' => 'No se pudo aprobar la solicitud. Verifica disponibilidad.', 'icono' => 'error');
            }

        } catch (Exception $e) {
            if ($msg['msg'] === 'Error desconocido') {
                $msg['msg'] = 'Error: ' . $e->getMessage();
            }
        }

        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }

    // Endpoint: Rechazar una solicitud (eliminar si estado = 0)
    public function rechazarSolicitud()
    {
        header('Content-Type: application/json; charset=utf-8');
        $msg = array('msg' => 'Error desconocido', 'icono' => 'error');

        try {
            $id_prestamo = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id_prestamo <= 0) {
                $msg = array('msg' => 'ID inválido', 'icono' => 'warning');
                throw new Exception('Invalid ID');
            }

            // Solo borrar si está en estado 0 (solicitud)
            $sql = "DELETE FROM prestamo WHERE id = $id_prestamo AND estado = 0";
            $resultado = $this->model->save($sql, array());

            if ($resultado > 0) {
                $msg = array('msg' => '✅ Solicitud rechazada y eliminada.', 'icono' => 'success');
            } else {
                $msg = array('msg' => 'No se pudo rechazar la solicitud.', 'icono' => 'error');
            }

        } catch (Exception $e) {
            if ($msg['msg'] === 'Error desconocido') {
                $msg['msg'] = 'Error: ' . $e->getMessage();
            }
        }

        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    
}
