<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url; ?>Assets/css/main.css">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url; ?>Assets/css/font-awesome.min.css">
    <title>Iniciar | Sesión</title>

    <style>
        body {
        margin: 0;
        padding: 0;
        overflow: hidden;
        background: #eaeaea;
        font-family: "Segoe UI", sans-serif;
    }

    /* CONTENEDOR GENERAL */
    .login-wrapper {
        position: relative;
        height: 100vh;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* FONDO 3D DE SPLINE */
    .spline-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        filter: brightness(1.05) contrast(1.1) saturate(1.2);
    }

    .spline-bg iframe {
        width: 100%;
        height: 100%;
        border: none;
        pointer-events: none; /* evita que el usuario mueva el fondo */
    }

    /* LOGIN (capa superior) */
    .login-content {
        position: relative;
        z-index: 2;
        background: rgba(255, 255, 255, 0.12); /* más transparente */
        backdrop-filter: blur(3px); /* menos desenfoque */
        padding: 40px 50px;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
    }

    /* EFECTO AL PASAR EL MOUSE */
    .login-content:hover {
        background: rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(2px);
    }

    .logo h1 {
        text-align: center;
        color: #fff;
        font-weight: 300;
        margin-bottom: 20px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .login-box {
        width: 320px;
    }

    .login-head {
        text-align: center;
        color: #000;
    }

    .control-label {
        color: #111;
        font-weight: 600;
    }

    .form-control {
        background: rgba(255, 255, 255, 0.8);
        border: none;
        border-radius: 6px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    }

    .form-control:focus {
        background: rgba(255, 255, 255, 1);
        outline: none;
        box-shadow: 0 0 5px rgba(0, 150, 136, 0.5);
    }

    .btn-primary {
        background-color: #009688;
        border: none;
        border-radius: 6px;
    }

    .btn-primary:hover {
        background-color: #00796b;
    }
    </style>
</head>

<body>

        <div class="spline-bg">
        <iframe src="https://my.spline.design/draganddropbookpencilschoolcopy-HtAKYAQvk0h7XxdidtrFiXY8/" frameborder="0"></iframe>
        </div>


    <section class="material-half-bg">
        
        <div class="cover"></div>
    </section>
    <section class="login-content">
        <div class="logo">
            <h1>Bienvenidos</h1>
        </div>
        <div class="login-box">
            <form class="login-form" id="frmLogin" onsubmit="frmLogin(event);">
                <h3 class="login-head"><i class="fa fa-lg fa-fw fa-user"></i>Iniciar Sesión</h3>
                <div class="form-group">
                    <label class="control-label">USUARIO</label>
                    <input class="form-control" type="text" placeholder="Usuario" id="usuario" name="usuario" autofocus required>
                </div>
                <div class="form-group">
                    <label class="control-label">CONTRASEÑA</label>
                    <input class="form-control" type="password" placeholder="Contraseña" id="clave" name="clave" required>
                </div>
                <div class="alert alert-danger d-none" role="alert" id="alerta">
                    
                </div>
                <div class="form-group btn-container">
                    <button class="btn btn-primary btn-block" type="submit"><i class="fa fa-sign-in fa-lg fa-fw"></i>Login</button>
                </div>
            </form>
        </div>
    </section>
    <!-- Essential javascripts for application to work-->
    <script src="<?php echo base_url; ?>Assets/js/jquery-3.6.0.min.js"></script>
    <script src="<?php echo base_url; ?>Assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo base_url; ?>Assets/js/main.js"></script>
    <!-- The javascript plugin to display page loading on top-->
    <script src="<?php echo base_url; ?>Assets/js/pace.min.js"></script>
    <script>
        const base_url = '<?php echo base_url; ?>';
    </script>
    <script src="<?php echo base_url; ?>Assets/js/login.js"></script>
    <script type="text/javascript">
        // Login Page Flipbox control
        $('.login-content [data-toggle="flip"]').click(function() {
            $('.login-box').toggleClass('flipped');
            return false;
        });
    </script>
</body>

</html>