<?php include "Views/Templates/header.php"; ?>
<?php
// Verificar si el usuario tiene solo el permiso Alumno
$esAlumno = false;
if (isset($_SESSION['id_usuario'])) {
    require_once "Models/UsuariosModel.php";
    $userModel = new UsuariosModel();
    $esAlumno = $userModel->verificarPermisos($_SESSION['id_usuario'], "Alumno");
}
?>

<?php if ($esAlumno) { ?>
    <script>window.location = '<?php echo base_url; ?>Catalogo';</script>
<?php } else { ?>
<div class="app-title">
    <div>
        <h1></i>Panel de administración</h1>
    </div>
</div>
<div class="row">
    <div class="col-md-6 col-lg-3">
        <div class="widget-small primary coloured-icon"><i class="icon fa fa-users fa-3x"></i>
            <a class="info" href="<?php echo base_url; ?>Usuarios">
                <h4>USUARIOS</h4>
                <p><b><?php echo $data['usuarios']['total'] ?></b></p>
            </a>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small info coloured-icon"><i class="icon fa fa-book fa-3x"></i>
            <a class="info" href="<?php echo base_url; ?>Libros">
                <h4>LIBROS</h4>
                <p><b><?php echo $data['libros']['total'] ?></b></p>
            </a>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small warning coloured-icon"><i class="icon fa fa-address-book-o fa-3x"></i>
            <a class="info" href="<?php echo base_url; ?>Autor">
                <h4>AUTORES</h4>
                <p><b><?php echo $data['autor']['total'] ?></b></p>
            </a>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small danger coloured-icon"><i class="icon fa fa-tags fa-3x"></i>
            <a class="info" href="<?php echo base_url; ?>Editorial">
                <h4>EDITORIALES</h4>
                <p><b><?php echo $data['editorial']['total'] ?></b></p>
            </a>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
<?php } ?>
        <div class="widget-small warning coloured-icon"><i class="icon fa fa-graduation-cap fa-3x"></i>
            <a class="info" href="<?php echo base_url; ?>Estudiantes">
                <h4>ESTUDIANTES</h4>
                <p><b><?php echo $data['estudiantes']['total'] ?></b></p>
            </a>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small danger coloured-icon"><i class="icon fa fa-hourglass-start fa-3x"></i>
            <a class="info" href="<?php echo base_url; ?>Prestamos">
                <h4>PRÉSTAMOS</h4>
                <p><b><?php echo $data['prestamos']['total'] ?></b></p>
            </a>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small info coloured-icon"><i class="icon fa fa-list-alt fa-3x"></i>
            <a class="info" href="<?php echo base_url; ?>Materia">
                <h4>MATERIAS</h4>
                <p><b><?php echo $data['materias']['total'] ?></b></p>
            </a>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small primary coloured-icon"><i class="icon fa fa-cogs fa-3x"></i>
            <a class="info" href="<?php echo base_url; ?>Configuracion">
                <h6>CONFIGURACIÓN</h6>
            </a>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <h3 class="tile-title" style="color: #0D072E;">TOP 5 LIBROS MÁS PRESTADOS</h3>
            <div class="embed-responsive embed-responsive-16by9">
                <canvas class="embed-responsive-item" id="reportePrestamo"></canvas>
            </div>
        </div>
    </div>
</div>
<?php include "Views/Templates/footer.php"; ?>