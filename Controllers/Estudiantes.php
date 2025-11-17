<?php
class Estudiantes extends Controller
{
    public function __construct()
    {
        session_start();
        if (empty($_SESSION['activo'])) {
            header("location: " . base_url);
        }
        parent::__construct();
        $id_user = $_SESSION['id_usuario'];
        $perm = $this->model->verificarPermisos($id_user, "Estudiantes");
        if (!$perm && $id_user != 1) {
            $this->views->getView($this, "permisos");
            exit;
        }
    }
    public function index()
    {
        $this->views->getView($this, "index");
    }
    public function listar()
    {
        $data = $this->model->getEstudiantes();
        // Normalize rows: ensure all expected column keys exist to avoid DataTables missing-parameter errors
        for ($i = 0; $i < count($data); $i++) {
            // Ensure expected keys exist (if DB hasn't migrated yet, these will be set to empty strings)
            $data[$i]['grado'] = $data[$i]['grado'] ?? '';
            $data[$i]['seccion'] = $data[$i]['seccion'] ?? '';
            $data[$i]['apellido_paterno'] = $data[$i]['apellido_paterno'] ?? '';
            $data[$i]['apellido_materno'] = $data[$i]['apellido_materno'] ?? '';
            // Normalize name: prefer 'nombre' (full) but also allow combination of name parts
            if (empty($data[$i]['nombre']) && (!empty($data[$i]['apellido_paterno']) || !empty($data[$i]['apellido_materno']) || !empty($data[$i]['nombres']))) {
                $data[$i]['nombre'] = trim(($data[$i]['nombres'] ?? '') . ' ' . ($data[$i]['apellido_paterno'] ?? '') . ' ' . ($data[$i]['apellido_materno'] ?? ''));
            }
            if ($data[$i]['estado'] == 1) {
                $data[$i]['estado'] = '<span class="badge badge-success">Activo</span>';
                $data[$i]['acciones'] = '<div>
                <button class="btn btn-primary" type="button" onclick="btnEditarEst(' . $data[$i]['id'] . ');"><i class="fa fa-pencil-square-o"></i></button>
                <button class="btn btn-danger" type="button" onclick="btnEliminarEst(' . $data[$i]['id'] . ');"><i class="fa fa-trash-o"></i></button>
                <div/>';
            } else {
                $data[$i]['estado'] = '<span class="badge badge-danger">Inactivo</span>';
                $data[$i]['acciones'] = '<div>
                <button class="btn btn-success" type="button" onclick="btnReingresarEst(' . $data[$i]['id'] . ');"><i class="fa fa-reply-all"></i></button>
                <div/>';
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function registrar()
    {
        // New fields: codigo (codigo_estudiante), numero_documento (dni), grado, seccion,
        // apellido_paterno, apellido_materno, nombres
        $codigo = strClean($_POST['codigo']);
        $numero_documento = strClean($_POST['dni']);
        $grado = isset($_POST['grado']) ? strClean($_POST['grado']) : '';
        $seccion = isset($_POST['seccion']) ? strClean($_POST['seccion']) : '';
        $apellido_paterno = isset($_POST['apellido_paterno']) ? strClean($_POST['apellido_paterno']) : '';
        $apellido_materno = isset($_POST['apellido_materno']) ? strClean($_POST['apellido_materno']) : '';
        $nombres = isset($_POST['nombres']) ? strClean($_POST['nombres']) : '';
        $id_usuario = isset($_POST['id_usuario']) ? strClean($_POST['id_usuario']) : null;
        $id = strClean($_POST['id']);

        if (empty($codigo) || empty($numero_documento) || empty($nombres)) {
            $msg = array('msg' => 'Codigo, número de documento y nombres son requeridos', 'icono' => 'warning');
        } else {
            // If creating new student
            if ($id == "") {
                $insertId = $this->model->insertarEstudiante($codigo, $numero_documento, $grado, $seccion, $apellido_paterno, $apellido_materno, $nombres, $id_usuario);
                if ($insertId == "existe") {
                    $msg = array('msg' => 'El estudiante ya existe', 'icono' => 'warning');
                } else if ($insertId > 0) {
                    // Create associated user if not provided
                    if (empty($id_usuario)) {
                        require_once 'Models/UsuariosModel.php';
                        $userModel = new UsuariosModel();
                        // username: apellido_paterno + primera letra de nombres
                        $firstLetter = substr(trim($nombres), 0, 1);
                        $lastTwoDigits = substr($numero_documento, -2);
                        $username = strtolower(preg_replace('/\s+/', '', $apellido_paterno . $firstLetter . $lastTwoDigits));
                        // password: first 8 chars of codigo
                        $pw = substr($codigo, 0, 8);
                        $hash = hash('SHA256', $pw);
                        $reg = $userModel->registrarUsuario($username, $nombres, $hash);
                        if ($reg == 'ok') {
                            // get the new user id
                            $u = $userModel->getUsuario($username, $hash);
                            if ($u) {
                                $id_user_created = $u['id'];
                                // update student with id_usuario
                                $this->model->actualizarEstudiante($codigo, $numero_documento, $grado, $seccion, $apellido_paterno, $apellido_materno, $nombres, $id_user_created, $insertId);
                            }
                        }
                    }
                    $msg = array('msg' => 'Estudiante registrado', 'icono' => 'success');
                } else {
                    $msg = array('msg' => 'Error al registrar', 'icono' => 'error');
                }
            } else {
                // Update existing student without changing user password
                $current = $this->model->editEstudiante($id);
                $existing_user_id = $current ? ($current['id_usuario'] ?? null) : null;
                $id_usuario_final = $existing_user_id ? $existing_user_id : $id_usuario;
                $data = $this->model->actualizarEstudiante($codigo, $numero_documento, $grado, $seccion, $apellido_paterno, $apellido_materno, $nombres, $id_usuario_final, $id);
                if ($data == "modificado") {
                    $msg = array('msg' => 'Estudiante modificado', 'icono' => 'success');
                } else {
                    $msg = array('msg' => 'Error al modificar', 'icono' => 'error');
                }
            }
        }
        // Clean any output produced (warnings/notices) and return strict JSON
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }

    // Endpoint to upload Excel/CSV and import students
    // Handles rptListadoEstudiantes.xlsx format specifically
    // Expected structure: Fila 12 has headers, data starts at Fila 13
    public function importar()
    {
        // Ensure no stray output breaks JSON (start a fresh buffer)
        while (ob_get_level()) @ob_end_clean();
        ob_start();
        $msg = array('msg' => 'Error desconocido', 'icono' => 'error');
        
        try {
            if (empty($_FILES['file_excel'])) {
                $msg = array('msg' => 'No se recibió archivo', 'icono' => 'warning');
                throw new Exception('No file');
            }
            
            $file = $_FILES['file_excel'];
            $tmp = $file['tmp_name'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Parse file: CSV or XLSX
            $rows = [];
            
            if ($ext === 'xlsx' || $ext === 'xls') {
                // XLSX: Use ZIP to extract XML
                $zip = new ZipArchive();
                if ($zip->open($tmp)) {
                    $xmlData = $zip->getFromName('xl/worksheets/sheet1.xml');
                    $zip->close();
                    
                    if ($xmlData) {
                        $xml = simplexml_load_string($xmlData);
                        foreach ($xml->sheetData->row as $row) {
                            $rowNum = (int)$row['r'];
                            $rowData = [];
                            
                            foreach ($row->c as $cell) {
                                $colRef = (string)$cell['r'];
                                $colLetter = preg_replace('/[0-9]+/', '', $colRef);
                                $colIndex = ord($colLetter) - ord('A');
                                
                                $value = '';
                                if (isset($cell->v)) {
                                    $value = (string)$cell->v;
                                } elseif (isset($cell->is->t)) {
                                    $value = (string)$cell->is->t;
                                }
                                
                                $rowData[$colIndex] = $value;
                            }
                            
                            $rows[$rowNum] = $rowData;
                        }
                    }
                }
            } else {
                // CSV
                if (($handle = fopen($tmp, 'r')) !== false) {
                    $rowNum = 1;
                    while (($data = fgetcsv($handle, 5000, ',')) !== false) {
                        $rows[$rowNum] = $data;
                        $rowNum++;
                    }
                    fclose($handle);
                }
            }
            
            if (empty($rows)) {
                $msg = array('msg' => 'Archivo vacío', 'icono' => 'error');
                throw new Exception('Empty');
            }
            
            // Helper to normalize strings
            $normalize = function ($s) {
                $s = trim((string)$s);
                $s = mb_strtolower($s);
                $s = str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], $s);
                $s = preg_replace('/[^a-z0-9 ]/', '', $s);
                $s = preg_replace('/\s+/', ' ', $s);
                return trim($s);
            };
            
            // Find header row (scan first 20 rows for keywords)
            $headerRowIdx = -1;
            for ($i = 1; $i <= min(20, count($rows)); $i++) {
                if (!isset($rows[$i])) continue;
                
                $rowData = $rows[$i];
                $count = 0;
                
                foreach ($rowData as $cell) {
                    $n = $normalize($cell);
                    if (strpos($n, 'codigo') !== false || strpos($n, 'documento') !== false ||
                        strpos($n, 'apellido') !== false || strpos($n, 'nombre') !== false) {
                        $count++;
                    }
                }
                
                if ($count >= 3) {
                    $headerRowIdx = $i;
                    break;
                }
            }
            
            if ($headerRowIdx === -1) {
                $msg = array('msg' => 'No se encontró encabezado válido', 'icono' => 'error');
                throw new Exception('No header');
            }
            
            // Build column mapping
            $header = $rows[$headerRowIdx];
            $colMap = [];
            
            for ($j = 0; $j < count($header); $j++) {
                $n = $normalize($header[$j]);
                if (strpos($n, 'codigo') !== false) $colMap['codigo'] = $j;
                if (strpos($n, 'documento') !== false || strpos($n, 'dni') !== false) $colMap['dni'] = $j;
                if (strpos($n, 'grado') !== false) $colMap['grado'] = $j;
                if (strpos($n, 'seccion') !== false) $colMap['seccion'] = $j;
                if (strpos($n, 'apellido paterno') !== false) $colMap['apPaterno'] = $j;
                if (strpos($n, 'apellido materno') !== false) $colMap['apMaterno'] = $j;
                if (strpos($n, 'nombre') !== false && strpos($n, 'apellido') === false) $colMap['nombres'] = $j;
            }
            
            // Also check Fila 11 for GRADO y SECCIÓN (special case for this file)
            if (isset($rows[11])) {
                $fila11 = $rows[11];
                foreach ($fila11 as $j => $cell) {
                    $n = $normalize($cell);
                    if ($n === 'grado') $colMap['grado'] = $j;
                    if (strpos($n, 'seccion') !== false) $colMap['seccion'] = $j;
                }
            }
            
            // Validate we have minimum required columns
            if (empty($colMap['codigo']) && empty($colMap['dni'])) {
                $msg = array('msg' => 'No se encontraron columnas de código o documento', 'icono' => 'error');
                throw new Exception('Missing columns');
            }

            // Prepare UsuariosModel for user creation and permission assignment
            require_once 'Models/UsuariosModel.php';
            $um = new UsuariosModel();

            // Helper: safely get a cell value from a row using column mapping (avoids undefined index warnings)
            $getCell = function ($row, $colMap, $key) {
                if (!isset($colMap[$key])) return '';
                $idx = $colMap[$key];
                // numeric index expected; return empty if not present
                return isset($row[$idx]) ? $row[$idx] : '';
            };

            // Helper: convert possible Windows-1252 / CP1252 encoded strings to UTF-8 to avoid SQL charset issues
            $toUTF8 = function ($s) {
                $s = (string)$s;
                if ($s === '') return '';
                // If already valid UTF-8, return as-is
                if (mb_detect_encoding($s, 'UTF-8', true)) {
                    return $s;
                }
                // Try common Windows encoding -> UTF-8 conversion
                $converted = @mb_convert_encoding($s, 'UTF-8', 'CP1252');
                if ($converted !== false) return $converted;
                // Fallback to iconv
                $conv2 = @iconv('CP1252', 'UTF-8//TRANSLIT', $s);
                return $conv2 !== false ? $conv2 : $s;
            };
            
            $inserted = 0;
            $updated = 0;
            $skipped = 0;
            
            // Process data rows
            for ($r = $headerRowIdx + 1; $r <= count($rows); $r++) {
                if (!isset($rows[$r])) continue;
                
                $row = $rows[$r];
                
                // Skip empty rows
                $empty = true;
                foreach ($row as $c) {
                    if (trim((string)$c)) {
                        $empty = false;
                        break;
                    }
                }
                if ($empty) {
                    $skipped++;
                    continue;
                }
                
                // Extract fields safely (avoid undefined index warnings) and convert to UTF-8
                $cod = $toUTF8(trim((string)$getCell($row, $colMap, 'codigo')));
                $dni = $toUTF8(trim((string)$getCell($row, $colMap, 'dni')));
                $grado = $toUTF8(trim((string)$getCell($row, $colMap, 'grado')));
                $secc = $toUTF8(trim((string)$getCell($row, $colMap, 'seccion')));
                $apP = $toUTF8(trim((string)$getCell($row, $colMap, 'apPaterno')));
                $apM = $toUTF8(trim((string)$getCell($row, $colMap, 'apMaterno')));
                $nom = $toUTF8(trim((string)$getCell($row, $colMap, 'nombres')));
                
                // Validate required fields
                if (empty($dni) || (empty($nom))) {
                    $skipped++;
                    continue;
                }
                
                // Use DNI as codigo if codigo is empty
                if (empty($cod)) {
                    $cod = $dni;
                }
                
                // Check if student exists
                $ex = $this->model->select("SELECT id, id_usuario FROM estudiante WHERE codigo='" . trim($cod) . "' OR dni='" . trim($dni) . "'");
                
                if (empty($ex)) {
                    // New student
                    $id = $this->model->insertarEstudiante($cod, $dni, $grado, $secc, $apP, $apM, $nom, null);
                    if ($id > 0) {
                        $inserted++;
                        // Try to create associated user and assign permiso id 10 (Estudiante)
                        try {
                            // username: apellido_paterno + primera letra de nombres + últimos 2 dígitos de DNI
                            $firstLetter = substr(trim($nom), 0, 1);
                            $lastTwoDigits = substr($dni, -2);
                            $username = strtolower(preg_replace('/\s+/', '', $apP . $firstLetter . $lastTwoDigits));
                            $passwordPlain = substr($cod, 0, 8);
                            $passwordHash = hash('SHA256', $passwordPlain);

                            $reg = $um->registrarUsuario($username, $nom, $passwordHash);
                            // get the user row whether newly created or existed
                            $userRow = $this->model->select("SELECT * FROM usuarios WHERE usuario = '" . $username . "'");
                            if (!empty($userRow)) {
                                // update estudiante with id_usuario
                                $this->model->actualizarEstudiante($cod, $dni, $grado, $secc, $apP, $apM, $nom, $userRow['id'], $id);
                                // assign permission 10 (estudiante)
                                $um->actualizarPermisos($userRow['id'], 10);
                            }
                        } catch (Exception $ue) {
                            // Silently ignore user creation failures to not break import
                        }
                    }
                } else {
                    // Update existing
                    // If existing student has no associated user, try to create one and attach it
                    $userIdToAssign = $ex['id_usuario'] ?? null;
                    if (empty($userIdToAssign)) {
                        try {
                            $firstLetter = substr(trim($nom), 0, 1);
                            $lastTwoDigits = substr($dni, -2);
                            $username = strtolower(preg_replace('/\s+/', '', $apP . $firstLetter . $lastTwoDigits));
                            $passwordPlain = substr($cod, 0, 8);
                            $passwordHash = hash('SHA256', $passwordPlain);

                            $reg = $um->registrarUsuario($username, $nom, $passwordHash);
                            // fetch user row
                            $userRow = $this->model->select("SELECT * FROM usuarios WHERE usuario = '" . $username . "'");
                            if (!empty($userRow)) {
                                $userIdToAssign = $userRow['id'];
                                $um->actualizarPermisos($userIdToAssign, 10);
                            }
                        } catch (Exception $ue) {
                            // ignore user creation failures
                        }
                    }

                    $this->model->actualizarEstudiante($cod, $dni, $grado, $secc, $apP, $apM, $nom, $userIdToAssign, $ex['id']);
                    $updated++;
                }
            }
            
            $msg = array('msg' => "✅ $inserted nuevos, $updated actualizados, $skipped omitidos", 'icono' => 'success');
            
        } catch (Exception $e) {
            if ($msg['msg'] === 'Error desconocido') {
                $msg['msg'] = 'Error: ' . $e->getMessage();
            }
        }
        
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function editar($id)
    {
        $data = $this->model->editEstudiante($id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function eliminar($id)
    {
        $data = $this->model->estadoEstudiante(0, $id);
        if ($data == 1) {
            $msg = array('msg' => 'Estudiante dado de baja', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al eliminar', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function reingresar($id)
    {
        $data = $this->model->estadoEstudiante(1, $id);
        if ($data == 1) {
            $msg = array('msg' => 'Estudiante restaurado', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al restaurar', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function buscarEstudiante()
    {
        if (isset($_GET['est'])) {
            $valor = $_GET['est'];
            $data = $this->model->buscarEstudiante($valor);
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            die();
        }
    }
}
