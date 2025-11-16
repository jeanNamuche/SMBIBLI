<?php
class Catalogo extends Controller
{
    public function __construct()
    {
        parent::__construct();
        session_start();
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

    // Vista: Mostrar mis solicitudes (renderiza la vista)
    public function solicitudes()
    {
        $this->views->getView($this, "solicitudes");
    }

    // Endpoint para que estudiantes soliciten préstamo de un libro
    // POST: id_libro, cantidad, observacion (opcional)
    public function solicitudPrestamo()
    {
        header('Content-Type: application/json; charset=utf-8');
        $msg = array('msg' => 'Error desconocido', 'icono' => 'error');

        try {
            // Verificar que el usuario está autenticado y es estudiante
            if (empty($_SESSION['id_usuario'])) {
                $msg = array('msg' => 'No estás autenticado', 'icono' => 'warning');
                throw new Exception('Not authenticated');
            }

            // Verificar que el usuario tiene permiso "Alumno"
            require_once 'Models/UsuariosModel.php';
            $userModel = new UsuariosModel();
            $esAlumno = $userModel->verificarPermisos($_SESSION['id_usuario'], 'Alumno');
            if (!$esAlumno) {
                $msg = array('msg' => 'No tienes permisos para solicitar préstamos', 'icono' => 'warning');
                throw new Exception('Permission denied');
            }

            // Obtener datos del estudiante desde id_usuario
            require_once 'Models/EstudiantesModel.php';
            $estModel = new EstudiantesModel();
            $sql = "SELECT id FROM estudiante WHERE id_usuario = " . (int)$_SESSION['id_usuario'];
            $estudiante = $estModel->select($sql);
            if (empty($estudiante)) {
                $msg = array('msg' => 'No se encontró perfil de estudiante', 'icono' => 'error');
                throw new Exception('Student profile not found');
            }

            $id_estudiante = $estudiante['id'];
            $id_libro = isset($_POST['id_libro']) ? (int)$_POST['id_libro'] : 0;
            $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
            $observacion = isset($_POST['observacion']) ? trim($_POST['observacion']) : '';

            if ($id_libro <= 0 || $cantidad <= 0) {
                $msg = array('msg' => 'Datos inválidos', 'icono' => 'warning');
                throw new Exception('Invalid data');
            }

            // Verificar que el libro existe
            $libroSql = "SELECT * FROM libro WHERE id = $id_libro";
            $libro = $estModel->select($libroSql);
            if (empty($libro)) {
                $msg = array('msg' => 'Libro no encontrado', 'icono' => 'error');
                throw new Exception('Book not found');
            }

            // Verificar que no hay una solicitud pendiente o préstamo activo del mismo libro
            $checkSql = "SELECT * FROM prestamo WHERE id_estudiante = $id_estudiante AND id_libro = $id_libro AND estado IN (0, 1)";
            $existe = $estModel->select($checkSql);
            if (!empty($existe)) {
                $msg = array('msg' => 'Ya tienes una solicitud o préstamo pendiente de este libro', 'icono' => 'warning');
                throw new Exception('Already requested');
            }

            // Insertar solicitud con estado = 0 (pendiente)
            require_once 'Models/PrestamosModel.php';
            $prestModel = new PrestamosModel();
            
            // Usar fechas por defecto (admin las confirmará luego)
            $hoy = date('Y-m-d');
            $resultado = $prestModel->insertarSolicitud($id_estudiante, $id_libro, $cantidad, $hoy, $hoy, $observacion);

            if ($resultado > 0) {
                $msg = array('msg' => '✅ Solicitud de préstamo enviada. El administrador la revisará pronto.', 'icono' => 'success');
            } else {
                $msg = array('msg' => 'Error al procesar la solicitud', 'icono' => 'error');
            }

        } catch (Exception $e) {
            if ($msg['msg'] === 'Error desconocido') {
                $msg['msg'] = 'Error: ' . $e->getMessage();
            }
        }

        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }

    // Listar solicitudes y préstamos del estudiante autenticado
    public function misSolicitudes()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            if (empty($_SESSION['id_usuario'])) {
                echo json_encode(array('msg' => 'No autenticado', 'icono' => 'error'));
                die();
            }

            require_once 'Models/EstudiantesModel.php';
            $estModel = new EstudiantesModel();
            $sql = "SELECT id FROM estudiante WHERE id_usuario = " . (int)$_SESSION['id_usuario'];
            $estudiante = $estModel->select($sql);

            if (empty($estudiante)) {
                echo json_encode(array());
                die();
            }

            // Traer solicitudes (estado=0), préstamos activos (estado=1) y devueltos (estado=2)
            // Estados: 0=Solicitud, 1=Préstamo Activo, 2=Devuelto
            $id_est = $estudiante['id'];
            $sql = "SELECT p.id, p.id_estudiante, p.id_libro, l.titulo, COALESCE(a.autor, 'Desconocido') AS autor, l.imagen, 
                           p.fecha_prestamo, p.fecha_devolucion, p.cantidad, p.observacion, p.estado,
                           CASE 
                               WHEN p.estado = 0 THEN 'Solicitud Pendiente'
                               WHEN p.estado = 1 THEN 'Préstamo Activo'
                               WHEN p.estado = 2 THEN 'Devuelto'
                               ELSE 'Desconocido'
                           END AS estado_texto
                    FROM prestamo p
                    INNER JOIN libro l ON p.id_libro = l.id
                    LEFT JOIN autor a ON l.id_autor = a.id
                    WHERE p.id_estudiante = $id_est AND p.estado IN (0, 1, 2)
                    ORDER BY p.estado ASC, p.fecha_prestamo DESC";
            
            $solicitudes = $estModel->selectAll($sql);
            echo json_encode($solicitudes, JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            echo json_encode(array('error' => $e->getMessage()));
        }
        die();
    }
    
}
?>