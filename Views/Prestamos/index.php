<?php include "Views/Templates/header.php"; ?>
<div class="app-title">
    <div>
        <h1></i>Préstamos</h1>
    </div>
</div>

<!-- Tabs de navegación -->
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#tabPrestamos">Préstamos Activos</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tabSolicitudes">Solicitudes Pendientes</a>
    </li>
</ul>

<!-- Contenido de Tabs -->
<div class="tab-content">
    <!-- Tab: Préstamos Activos -->
    <div id="tabPrestamos" class="tab-pane fade show active">
        <button class="btn btn-primary mb-2" onclick="frmPrestar()"><i class="fa fa-plus"></i> Nuevo Préstamo</button>
        <div class="tile">
            <div class="tile-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped mt-4" id="tblPrestar">
                        <thead class="thead-dark">
                            <tr>
                                <th>Id</th>
                                <th>Libro</th>
                                <th>Estudiante</th>
                                <th>Fecha Prestamo</th>
                                <th>Fecha Devolución</th>
                                <th>Cant</th>
                                <th>Observación</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Solicitudes Pendientes -->
    <div id="tabSolicitudes" class="tab-pane fade">
        <div class="tile">
            <div class="tile-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped mt-4" id="tblSolicitudes">
                        <thead class="thead-dark">
                            <tr>
                                <th>Id</th>
                                <th>Libro</th>
                                <th>Estudiante</th>
                                <th>Código</th>
                                <th>Cant</th>
                                <th>Observación</th>
                                <th>Fecha Solicitud</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="bodyTabSolicitudes">
                            <tr><td colspan="8" class="text-center text-muted">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="prestar" class="modal fade" role="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="title">Prestar Libro</h5>
                <button class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frmPrestar" onsubmit="registroPrestamos(event)">
                    <div class="form-group">
                        <label for="libro">Libro</label><br>
                        <select id="libro" class="form-control libro" name="libro" onchange="verificarLibro()" required style="width: 100%;">

                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-9">
                            <div class="form-group">
                                <label for="estudiante">Estudiante</label><br>
                                <select name="estudiante" id="estudiante" class="form-control estudiante" required style="width: 100%;">

                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cantidad">Cant</label>
                                <input id="cantidad" class="form-control" min="1" type="number" name="cantidad" min="1" required onkeyup="verificarLibro()">
                                <strong id="msg_error"></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_prestamo">Fecha de Prestamo</label>
                                <input id="fecha_prestamo" class="form-control" type="date" name="fecha_prestamo" value="<?php echo date("Y-m-d"); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_devolucion">Fecha de Devolución</label>
                                <input id="fecha_devolucion" class="form-control" type="date" name="fecha_devolucion" value="<?php echo date("Y-m-d"); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="observacion">Observación</label>
                        <textarea id="observacion" class="form-control" placeholder="Observación" name="observacion" rows="3"></textarea>
                    </div>
                    <button class="btn btn-primary" type="submit" id="btnAccion">Prestar</button>
                    <button class="btn btn-danger" type="button" data-dismiss="modal">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const baseUrl = '<?php echo base_url; ?>';

    // Cargar solicitudes pendientes cuando se abre la pestaña
    function cargarSolicitudesPendientes() {
        fetch(baseUrl + 'Prestamos/solicitudesPendientes')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('bodyTabSolicitudes');
                tbody.innerHTML = '';

                if (!data || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No hay solicitudes pendientes.</td></tr>';
                    return;
                }

                data.forEach(sol => {
                    const row = `
                        <tr>
                            <td>${sol.id}</td>
                            <td>${sol.titulo} <br><small class="text-muted">${sol.autor}</small></td>
                            <td>${sol.nombre}</td>
                            <td>${sol.codigo}</td>
                            <td>${sol.cantidad}</td>
                            <td>${sol.observacion || '-'}</td>
                            <td>${sol.fecha_prestamo}</td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="aprobarSolicitud(${sol.id})">
                                    <i class="fa fa-check"></i> Aprobar
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="rechazarSolicitud(${sol.id})">
                                    <i class="fa fa-times"></i> Rechazar
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            })
            .catch(err => {
                console.error('Error cargando solicitudes:', err);
                document.getElementById('bodyTabSolicitudes').innerHTML = 
                    '<tr><td colspan="8" class="text-center text-danger">Error al cargar solicitudes</td></tr>';
            });
    }

    function aprobarSolicitud(idPrestamo) {
        Swal.fire({
            title: '¿Aprobar solicitud?',
            text: 'Se convertirá en un préstamo activo',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, aprobar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id', idPrestamo);

                fetch(baseUrl + 'Prestamos/aprobarSolicitud', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    Swal.fire('Resultado', data.msg, data.icono);
                    if (data.icono === 'success') {
                        cargarSolicitudesPendientes();
                        tblPrestar.ajax.reload();
                    }
                })
                .catch(err => console.error(err));
            }
        });
    }

    function rechazarSolicitud(idPrestamo) {
        Swal.fire({
            title: '¿Rechazar solicitud?',
            text: 'Se eliminará la solicitud',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, rechazar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id', idPrestamo);

                fetch(baseUrl + 'Prestamos/rechazarSolicitud', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    Swal.fire('Resultado', data.msg, data.icono);
                    if (data.icono === 'success') {
                        cargarSolicitudesPendientes();
                    }
                })
                .catch(err => console.error(err));
            }
        });
    }

    // Cargar solicitudes cuando se abre la pestaña
    document.addEventListener('shown.bs.tab', function(e) {
        if (e.target.getAttribute('href') === '#tabSolicitudes') {
            cargarSolicitudesPendientes();
        }
    });

    // Cargar inicialmente al abrir la página
    document.addEventListener('DOMContentLoaded', function() {
        // Cargar solicitudes pendientes al iniciar
        cargarSolicitudesPendientes();
    });
</script>

<?php include "Views/Templates/footer.php"; ?>