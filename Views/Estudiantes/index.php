<?php include "Views/Templates/header.php"; ?>
<div class="app-title">
    <div>
        <h1></i>Estudiantes</h1>
    </div>
</div>
<button class="btn btn-primary mb-2" type="button" onclick="frmEstudiante()"><i class="fa fa-plus"></i></button>
<button class="btn btn-secondary mb-2 ml-2" type="button" onclick="frmImportar()"><i class="fa fa-upload"></i> Importar Excel</button>
<div class="row">
    <div class="col-lg-12">
        <div class="tile">
            <div class="tile-body">
                <div class="table-responsive">
                    <table class="table table-light mt-4" id="tblEst">
                        <thead class="thead-dark">
                            <tr>
                                <th>Id</th>
                                <th>Código</th>
                                <th>Dni</th>
                                <th>Grado</th>
                                <th>Sección</th>
                                <th>Apellido Paterno</th>
                                <th>Apellido Materno</th>
                                <th>Nombres</th>
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
</div>
<div id="nuevoEstudiante" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="title">Registro Estudiante</h5>
                <button class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frmEstudiante">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="codigo">Código</label>
                                <input type="hidden" id="id" name="id">
                                <input id="codigo" class="form-control" type="text" name="codigo" required placeholder="Código del estudiante">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dni">Número documento</label>
                                <input id="dni" class="form-control" type="text" name="dni" required placeholder="Número de documento">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="grado">Grado</label>
                                <input id="grado" class="form-control" type="text" name="grado" placeholder="Grado">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="seccion">Sección</label>
                                <input id="seccion" class="form-control" type="text" name="seccion" placeholder="Sección">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="nombres">Nombres</label>
                                <input id="nombres" class="form-control" type="text" name="nombres" required placeholder="Nombres">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="apellido_paterno">Apellido paterno</label>
                                <input id="apellido_paterno" class="form-control" type="text" name="apellido_paterno" placeholder="Apellido paterno">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="apellido_materno">Apellido materno</label>
                                <input id="apellido_materno" class="form-control" type="text" name="apellido_materno" placeholder="Apellido materno">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="id_usuario">Usuario asociado (opcional)</label>
                                <input id="id_usuario" class="form-control" type="text" name="id_usuario" placeholder="Id usuario (si ya existe)">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-primary" type="submit" onclick="registrarEstudiante(event)" id="btnAccion">Registrar</button>
                                <button class="btn btn-danger" type="button" data-dismiss="modal">Atras</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include "Views/Templates/footer.php"; ?>
<div id="modalImportar" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">Importar Estudiantes</h5>
                <button class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frmImportar" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="file_excel">Seleccionar archivo Excel o CSV</label>
                        <input type="file" id="file_excel" name="file_excel" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary" type="button" onclick="importarExcel(event)">Importar</button>
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function frmImportar(){
        $('#modalImportar').modal('show');
    }
    function importarExcel(e){
        e.preventDefault();
        var form = document.getElementById('frmImportar');
        var data = new FormData(form);
        // Fetch response as text so we can show raw server output when JSON parsing fails
        fetch(base_url + 'Estudiantes/importar', {method: 'POST', body: data})
        .then(res => res.text())
        .then(text => {
            // Try to parse JSON; if it fails show the raw response (HTML/PHP warnings) to help debug
            try{
                var data = JSON.parse(text);
            }catch(err){
                console.error('Import response is not JSON:', err, text);
                // small helper to escape HTML so the raw output can be displayed safely
                function escapeHtml(unsafe) {
                    return unsafe
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error al importar (respuesta inesperada del servidor)',
                    html: '<div style="text-align:left; max-height:360px; overflow:auto; white-space:pre-wrap;">' + escapeHtml(text) + '</div>',
                    width: '80%'
                });
                // rethrow to fall into catch if needed
                throw err;
            }
            // If we have valid JSON, handle it normally
            Swal.fire('Resultado', data.msg, data.icono);
            if(data.icono === 'success'){
                $('#modalImportar').modal('hide');
                tblEst.ajax.reload();
            }
        }).catch(err => {
            // If the error was already shown above, this catch will still allow logging
            console.error('Error en importarExcel:', err);
            // If the error was a network/fetch error, show a concise message
            if(!err || !err.message) return;
            // Don't duplicate PHP/HTML errors (they are displayed above). Only show a short message for other errors.
            if(err.message && err.message.indexOf('Unexpected') !== -1){
                // already handled by the parse block
                return;
            }
            Swal.fire('Error', 'No se pudo procesar la importación. Revisa la consola del navegador y el log del servidor.', 'error');
        });
    }
</script>