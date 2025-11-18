<?php
class Quiz extends Controller {
    public function __construct() {
        session_start();
        if (empty($_SESSION['activo'])) {
            header("location: " . base_url);
        }
        parent::__construct();
        $id_user = $_SESSION['id_usuario'];
        $this->cargarModel();
    }

    public function index() {
        // Cargar lista de libros para el selector en la vista
        require_once 'Models/LibrosModel.php';
        $librosModel = new LibrosModel();
        $data = array();
        $data['libros'] = $librosModel->getLibros();
        $this->views->getView($this, "index", $data);
    }

    // Obtener libros en formato JSON (opcional)
    public function obtenerLibros() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            require_once 'Models/LibrosModel.php';
            $librosModel = new LibrosModel();
            $libros = $librosModel->getLibros();
            echo json_encode($libros, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(array('error' => $e->getMessage()));
        }
        die();
    }

    // Renderiza la vista para que el estudiante resuelva Quiz o Rompecabezas
    public function resolver() {
        $this->views->getView($this, "resolver");
    }

    // ===== ENDPOINTS ADMIN - GESTIÓN DE PREGUNTAS =====

    // Obtener todas las preguntas de un libro
    public function obtenerPreguntas() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id_libro = isset($_GET['id_libro']) ? (int)$_GET['id_libro'] : 0;
            if ($id_libro <= 0) {
                echo json_encode(array('error' => 'ID de libro inválido'));
                die();
            }

            $preguntas = $this->model->obtenerPreguntasLibro($id_libro);
            
            // Cargar opciones para cada pregunta
            foreach ($preguntas as &$preg) {
                $preg['opciones'] = $this->model->obtenerOpcionesPregunta($preg['id']);
            }

            echo json_encode($preguntas, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(array('error' => $e->getMessage()));
        }
        die();
    }

    // Guardar pregunta
    public function guardarPregunta() {
        header('Content-Type: application/json; charset=utf-8');
        $msg = array('msg' => 'Error desconocido', 'icono' => 'error');
        
        try {
            $id_libro = isset($_POST['id_libro']) ? (int)$_POST['id_libro'] : 0;
            $texto = isset($_POST['texto']) ? trim($_POST['texto']) : '';
            $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : 'multiple_choice';
            $numero = isset($_POST['numero']) ? (int)$_POST['numero'] : 0;

            if ($id_libro <= 0 || empty($texto) || $numero <= 0) {
                $msg = array('msg' => 'Datos inválidos', 'icono' => 'warning');
                throw new Exception('Invalid data');
            }

            $resultado = $this->model->guardarPregunta($id_libro, $texto, $tipo, $numero);
            if ($resultado > 0) {
                $msg = array('msg' => '✅ Pregunta guardada', 'icono' => 'success', 'id' => $resultado);
            } else {
                $msg = array('msg' => 'Error al guardar pregunta', 'icono' => 'error');
            }
        } catch (Exception $e) {
            if ($msg['msg'] === 'Error desconocido') {
                $msg['msg'] = 'Error: ' . $e->getMessage();
            }
        }

        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }

    // Guardar opción de respuesta
    public function guardarOpcion() {
        header('Content-Type: application/json; charset=utf-8');
        $msg = array('msg' => 'Error desconocido', 'icono' => 'error');
        
        try {
            $id_pregunta = isset($_POST['id_pregunta']) ? (int)$_POST['id_pregunta'] : 0;
            $texto = isset($_POST['texto']) ? trim($_POST['texto']) : '';
            $es_correcta = isset($_POST['es_correcta']) ? (int)$_POST['es_correcta'] : 0;
            $orden = isset($_POST['orden']) ? (int)$_POST['orden'] : 0;

            if ($id_pregunta <= 0 || empty($texto)) {
                $msg = array('msg' => 'Datos inválidos', 'icono' => 'warning');
                throw new Exception('Invalid data');
            }

            $resultado = $this->model->guardarOpcion($id_pregunta, $texto, $es_correcta, $orden);
            if ($resultado > 0) {
                $msg = array('msg' => '✅ Opción guardada', 'icono' => 'success', 'id' => $resultado);
            } else {
                $msg = array('msg' => 'Error al guardar opción', 'icono' => 'error');
            }
        } catch (Exception $e) {
            if ($msg['msg'] === 'Error desconocido') {
                $msg['msg'] = 'Error: ' . $e->getMessage();
            }
        }

        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }

    // Limpiar opciones de una pregunta (endpoint auxiliar para admin)
    public function limpiarOpciones() {
        header('Content-Type: application/json; charset=utf-8');
        $msg = array('msg' => 'Error desconocido', 'icono' => 'error');
        try {
            $id_pregunta = isset($_POST['id_pregunta']) ? (int)$_POST['id_pregunta'] : 0;
            if ($id_pregunta <= 0) {
                $msg = array('msg' => 'ID pregunta inválido', 'icono' => 'warning');
                throw new Exception('Invalid data');
            }
            $res = $this->model->limpiarOpcionesPregunta($id_pregunta);
            if ($res) {
                $msg = array('msg' => 'Opciones limpiadas', 'icono' => 'success');
            } else {
                $msg = array('msg' => 'No se pudo limpiar opciones', 'icono' => 'error');
            }
        } catch (Exception $e) {
            if ($msg['msg'] === 'Error desconocido') $msg['msg'] = 'Error: ' . $e->getMessage();
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }

    // ===== ENDPOINTS ADMIN - GESTIÓN DE ROMPECABEZAS =====

    // Obtener rompecabezas de un libro
    public function obtenerRompecabezas() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id_libro = isset($_GET['id_libro']) ? (int)$_GET['id_libro'] : 0;
            if ($id_libro <= 0) {
                echo json_encode(array('error' => 'ID de libro inválido'));
                die();
            }

            $rompecabezas = $this->model->obtenerRompecabezas($id_libro);
            if ($rompecabezas) {
                $rompecabezas['piezas'] = $this->model->obtenerPiezasRompecabezas($rompecabezas['id']);
            }

            echo json_encode($rompecabezas ?: array(), JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(array('error' => $e->getMessage()));
        }
        die();
    }

    // Guardar rompecabezas
    public function guardarRompecabezas() {
        header('Content-Type: application/json; charset=utf-8');
        $msg = array('msg' => 'Error desconocido', 'icono' => 'error');
        
        try {
            $id_libro = isset($_POST['id_libro']) ? (int)$_POST['id_libro'] : 0;
            $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
            $instrucciones = isset($_POST['instrucciones']) ? trim($_POST['instrucciones']) : '';

            if ($id_libro <= 0 || empty($titulo)) {
                $msg = array('msg' => 'Datos inválidos', 'icono' => 'warning');
                throw new Exception('Invalid data');
            }

            $resultado = $this->model->guardarRompecabezas($id_libro, $titulo, $instrucciones);
            if ($resultado > 0) {
                $msg = array('msg' => '✅ Rompecabezas guardado', 'icono' => 'success', 'id' => $resultado);
            } else {
                $msg = array('msg' => 'Error al guardar rompecabezas', 'icono' => 'error');
            }
        } catch (Exception $e) {
            if ($msg['msg'] === 'Error desconocido') {
                $msg['msg'] = 'Error: ' . $e->getMessage();
            }
        }

        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }

    // Guardar pieza de rompecabezas
    public function guardarPieza() {
        header('Content-Type: application/json; charset=utf-8');
        $msg = array('msg' => 'Error desconocido', 'icono' => 'error');
        
        try {
            $id_rompecabezas = isset($_POST['id_rompecabezas']) ? (int)$_POST['id_rompecabezas'] : 0;
            $texto = isset($_POST['texto']) ? trim($_POST['texto']) : '';
            $posicion = isset($_POST['posicion']) ? (int)$_POST['posicion'] : 0;
            $orden = isset($_POST['orden']) ? (int)$_POST['orden'] : 0;

            if ($id_rompecabezas <= 0 || empty($texto) || $posicion <= 0) {
                $msg = array('msg' => 'Datos inválidos', 'icono' => 'warning');
                throw new Exception('Invalid data');
            }

            $resultado = $this->model->guardarPiezaRompecabezas($id_rompecabezas, $texto, $posicion, $orden);
            if ($resultado > 0) {
                $msg = array('msg' => '✅ Pieza guardada', 'icono' => 'success', 'id' => $resultado);
            } else {
                $msg = array('msg' => 'Error al guardar pieza', 'icono' => 'error');
            }
        } catch (Exception $e) {
            if ($msg['msg'] === 'Error desconocido') {
                $msg['msg'] = 'Error: ' . $e->getMessage();
            }
        }

        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }

    // ===== ENDPOINTS ESTUDIANTE - RESOLVER QUIZ =====

    // Obtener quiz para responder
    public function obtenerQuizEstudiante() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id_libro = isset($_GET['id_libro']) ? (int)$_GET['id_libro'] : 0;
            if ($id_libro <= 0) {
                echo json_encode(array('error' => 'ID de libro inválido'));
                die();
            }

            $preguntas = $this->model->obtenerPreguntasLibro($id_libro);
            
            // Solo retornar las primeras 5 preguntas
            $preguntas = array_slice($preguntas, 0, 5);
            
            // Cargar opciones y mezclarlas
            foreach ($preguntas as &$preg) {
                $preg['opciones'] = $this->model->obtenerOpcionesPregunta($preg['id']);
                // Mezclar opciones
                shuffle($preg['opciones']);
            }

            echo json_encode($preguntas, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(array('error' => $e->getMessage()));
        }
        die();
    }

    // Calificar respuestas del quiz
    public function calificarQuiz() {
        header('Content-Type: application/json; charset=utf-8');
        $msg = array('msg' => 'Error desconocido', 'icono' => 'error');
        
        try {
            $id_estudiante = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : 0;
            $id_libro = isset($_POST['id_libro']) ? (int)$_POST['id_libro'] : 0;
            $respuestas = isset($_POST['respuestas']) ? json_decode($_POST['respuestas'], true) : array();

            if ($id_estudiante <= 0 || $id_libro <= 0 || empty($respuestas)) {
                $msg = array('msg' => 'Datos inválidos', 'icono' => 'warning');
                throw new Exception('Invalid data');
            }

            // Obtener datos del estudiante (crear perfil mínimo si no existe)
            require_once 'Models/EstudiantesModel.php';
            $estModel = new EstudiantesModel();
            $id_est = $estModel->obtenerOCrearPorUsuario($id_estudiante, isset($_SESSION['nombre']) ? $_SESSION['nombre'] : '');
            if (!$id_est) {
                $msg = array('msg' => 'Estudiante no encontrado', 'icono' => 'error');
                throw new Exception('Student not found');
            }
            $puntuacion = 0;
            $total = 0;
            $detalles = array();

            // Validar cada respuesta y construir detalles por pregunta
            foreach ($respuestas as $id_pregunta => $id_opcion_seleccionada) {
                $id_pregunta = (int)$id_pregunta;
                $id_opcion_seleccionada = (int)$id_opcion_seleccionada;
                $opcion = $this->model->select("SELECT es_correcta FROM quiz_opciones WHERE id = " . $id_opcion_seleccionada);
                $es_correcta = ($opcion && $opcion['es_correcta'] == 1) ? 1 : 0;
                if ($es_correcta) $puntuacion++;

                // obtener id de la opcion correcta para esta pregunta
                $correcta = $this->model->select("SELECT id FROM quiz_opciones WHERE id_pregunta = " . $id_pregunta . " AND es_correcta = 1 LIMIT 1");
                $id_correcta = $correcta ? (int)$correcta['id'] : 0;

                $detalles[$id_pregunta] = array(
                    'seleccionada' => $id_opcion_seleccionada,
                    'es_correcta' => $es_correcta,
                    'id_correcta' => $id_correcta
                );

                $total++;
            }

            $porcentaje = $total > 0 ? round(($puntuacion / $total) * 100) : 0;

            // Guardar intento
            $resultado = $this->model->guardarIntento($id_est, $id_libro, 'quiz', $porcentaje, json_encode($respuestas));
            
            if ($resultado > 0) {
                $msg = array(
                    'msg' => '✅ Quiz completado',
                    'icono' => 'success',
                    'puntuacion' => $puntuacion,
                    'total' => $total,
                    'porcentaje' => $porcentaje,
                    'detalles' => $detalles
                );
            } else {
                $msg = array('msg' => 'Error al guardar resultado', 'icono' => 'error');
            }
        } catch (Exception $e) {
            if ($msg['msg'] === 'Error desconocido') {
                $msg['msg'] = 'Error: ' . $e->getMessage();
            }
        }

        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }

    // ===== ENDPOINTS ESTUDIANTE - RESOLVER ROMPECABEZAS =====

    // Obtener rompecabezas para resolver
    public function obtenerRompecabezasEstudiante() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id_libro = isset($_GET['id_libro']) ? (int)$_GET['id_libro'] : 0;
            if ($id_libro <= 0) {
                echo json_encode(array('error' => 'ID de libro inválido'));
                die();
            }

            $rompecabezas = $this->model->obtenerRompecabezas($id_libro);
            if ($rompecabezas) {
                $piezas = $this->model->obtenerPiezasRompecabezas($rompecabezas['id']);
                // Mezclar piezas para mostrar
                shuffle($piezas);
                $rompecabezas['piezas'] = $piezas;
            }

            echo json_encode($rompecabezas ?: array(), JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(array('error' => $e->getMessage()));
        }
        die();
    }

    // Calificar rompecabezas
    public function calificarRompecabezas() {
        header('Content-Type: application/json; charset=utf-8');
        $msg = array('msg' => 'Error desconocido', 'icono' => 'error');
        
        try {
            $id_estudiante = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : 0;
            $id_libro = isset($_POST['id_libro']) ? (int)$_POST['id_libro'] : 0;
            $respuestas = isset($_POST['respuestas']) ? json_decode($_POST['respuestas'], true) : array();

            if ($id_estudiante <= 0 || $id_libro <= 0 || empty($respuestas)) {
                $msg = array('msg' => 'Datos inválidos', 'icono' => 'warning');
                throw new Exception('Invalid data');
            }

            // Obtener datos del estudiante (crear perfil mínimo si no existe)
            require_once 'Models/EstudiantesModel.php';
            $estModel = new EstudiantesModel();
            $id_est = $estModel->obtenerOCrearPorUsuario($id_estudiante, isset($_SESSION['nombre']) ? $_SESSION['nombre'] : '');
            if (!$id_est) {
                $msg = array('msg' => 'Estudiante no encontrado', 'icono' => 'error');
                throw new Exception('Student not found');
            }
            $puntuacion = 0;
            $total = count($respuestas);

            // Validar cada respuesta
            foreach ($respuestas as $id_pieza => $posicion_seleccionada) {
                $pieza = $this->model->select("SELECT posicion_correcta FROM quiz_rompecabezas_piezas WHERE id = " . (int)$id_pieza);
                if ($pieza && $pieza['posicion_correcta'] == $posicion_seleccionada) {
                    $puntuacion++;
                }
            }

            $porcentaje = $total > 0 ? round(($puntuacion / $total) * 100) : 0;

            // Guardar intento
            $resultado = $this->model->guardarIntento($id_est, $id_libro, 'rompecabezas', $porcentaje, json_encode($respuestas));
            
            if ($resultado > 0) {
                $msg = array(
                    'msg' => '✅ Rompecabezas completado',
                    'icono' => 'success',
                    'puntuacion' => $puntuacion,
                    'total' => $total,
                    'porcentaje' => $porcentaje
                );
            } else {
                $msg = array('msg' => 'Error al guardar resultado', 'icono' => 'error');
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
?>
