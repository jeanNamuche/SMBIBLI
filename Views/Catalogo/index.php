<?php include "Views/Templates/header.php"; ?>
<?php
    // Compatibilidad: $data puede venir como array asociativo ('libros','materias') o como lista simple de libros.
    $libros_list = array();
    $materias_list = array();
    if (isset($data['libros'])) {
        $libros_list = $data['libros'];
    } elseif (!empty($data) && array_values($data) === $data) {
        // si $data es una lista indexada de libros (uso antiguo)
        $libros_list = $data;
    }
    if (isset($data['materias'])) {
        $materias_list = $data['materias'];
    }
?>

<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-12">
            <div class="input-group">
                <input id="buscar" type="search" class="form-control" placeholder="Buscar libros, autores, categorías...">
                <div class="input-group-append">
                    <button id="btnBuscar" class="btn btn-primary">Buscar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <div class="btn-group" role="group">
                <button id="filtroCatalogo" class="btn btn-outline-dark active">Catálogo</button>
                <button id="filtroNuevos" class="btn btn-outline-dark">Nuevos Ingresos</button>
            </div>
        </div>
        <div class="col-md-6 text-md-right mt-2 mt-md-0">
            <div class="btn-group" role="group" aria-label="categorias">
                <button class="btn btn-sm btn-light active categoria-btn" data-materia="all">Todas</button>
                <?php if (!empty($materias_list)) { foreach ($materias_list as $m) { ?>
                    <button class="btn btn-sm btn-light categoria-btn" data-materia="<?php echo $m['id']; ?>"><?php echo $m['materia']; ?></button>
                <?php } } ?>
            </div>
        </div>
    </div>

    <div id="listadoLibros" class="row">
        <!-- tarjetas se renderizarán por JS -->
    </div>

    <!-- Modal de detalles -->
    <div class="modal fade" id="modalDetalles" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
          <div class="modal-header border-bottom-0">
            <h5 class="modal-title" id="modalTitle">Detalles</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="row">
                <div class="col-md-5 text-center">
                    <div class="p-3">
                        <img id="modalImagen" src="" class="img-fluid rounded-lg" alt="Portada" style="max-height:320px; object-fit:cover; border-radius:10px;">
                    </div>
                </div>
                <div class="col-md-7">
                    <h4 id="modalTitulo" class="mb-1"></h4>
                    <p class="text-muted small mb-3" id="modalAutor"></p>

                    <div class="mb-3">
                        <span id="modalBadge" class="badge badge-secondary">Estado</span>
                    </div>

                    <div class="mb-3">
                        <p class="mb-2"><i class="fa fa-user mr-2"></i> <strong>Autor:</strong> <span id="modalAutorLinea"></span></p>
                        <p class="mb-2"><i class="fa fa-bookmark mr-2"></i> <strong>Categoría:</strong> <span id="modalMateria"></span></p>
                        <p class="mb-2"><i class="fa fa-calendar mr-2"></i> <strong>Año:</strong> <span id="modalAnio"></span></p>
                        <p class="mb-2"><i class="fa fa-file-text mr-2"></i> <strong>Páginas:</strong> <span id="modalPaginas"></span></p>
                        <p class="mb-2"><i class="fa fa-layer-group mr-2"></i> <strong>Disponibles:</strong> <span id="modalDisponibles"></span></p>
                        <p class="mb-2"><i class="fa fa-hashtag mr-2"></i> <strong>ISBN:</strong> <span id="modalISBN"></span></p>
                    </div>

                    <h6>Descripción</h6>
                    <div id="modalDescripcion" class="text-muted mb-4"></div>

                </div>
            </div>
          </div>
                    <div class="modal-footer">
                        <button id="modalLeerBtn" class="btn btn-primary" disabled>Leer</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
        </div>
      </div>
    </div>

    <!-- Modal para solicitar préstamo -->
    <div class="modal fade" id="modalSolicitud" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title">Solicitar Préstamo</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="frmSolicitud">
              <div class="form-group">
                <label><strong id="libroTitulo">Libro</strong></label>
              </div>
              <div class="form-group">
                <label for="solCantidad">Cantidad</label>
                <input type="number" id="solCantidad" class="form-control" name="cantidad" value="1" min="1" max="5" required>
              </div>
              <div class="form-group">
                <label for="solObservacion">Observaciones (opcional)</label>
                <textarea id="solObservacion" class="form-control" name="observacion" rows="3" placeholder="Ej: Necesito para el 20 de noviembre..."></textarea>
              </div>
              <input type="hidden" id="solLibroId" name="id_libro">
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-success" onclick="enviarSolicitud()">Solicitar Préstamo</button>
          </div>
        </div>
      </div>
    </div>

    <script>
        // Datos inyectados desde PHP
        const libros = <?php echo !empty($libros_list) ? json_encode($libros_list) : '[]'; ?>;

        const baseUrl = '<?php echo base_url; ?>';

        // Utilidades para renderizar tarjetas y modal
        function crearTarjeta(libro) {
            const disponible = Number(libro.cantidad) > 0 ? 'Disponible' : 'No disponible';
            const badgeClass = Number(libro.cantidad) > 0 ? 'badge-success' : 'badge-secondary';
            const hasPdf = libro.pdf_path && libro.pdf_path.trim() !== '';
            const imagen = libro.imagen && libro.imagen.trim() !== '' ? baseUrl + 'Assets/img/libros/' + libro.imagen : baseUrl + 'Assets/img/no-cover.png';

            return `
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm">
                    <img src="${imagen}" class="card-img-top" style="height:240px; object-fit:cover;">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2"><span class="badge ${badgeClass}">${disponible}</span></div>
                        <h6 class="card-title">${libro.titulo}</h6>
                        <p class="card-text text-muted small mb-2">${libro.autor}</p>
                        <p class="card-text text-muted small">${libro.materia}</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center pt-2">
                            <button class="btn btn-outline-secondary btn-sm btn-detalles" data-id="${libro.id}">Ver detalles</button>
                            ${hasPdf ? `<a href="${baseUrl + libro.pdf_path}" target="_blank" class="btn btn-sm btn-primary">Leer</a>` : `<button class="btn btn-sm btn-light" disabled>Leer</button>`}
                        </div>
                        <button class="btn btn-success btn-sm btn-block mt-2 btn-solicitud" data-id="${libro.id}" data-titulo="${libro.titulo}">
                            <i class="fa fa-plus"></i> Solicitar
                        </button>
                    </div>
                </div>
            </div>`;
        }

        function renderizarLista(items) {
            const cont = document.getElementById('listadoLibros');
            cont.innerHTML = '';
            if (!items.length) {
                cont.innerHTML = '<div class="col-12"><p class="text-center text-muted">No se encontraron libros.</p></div>';
                return;
            }
            let html = '';
            items.forEach(l => html += crearTarjeta(l));
            cont.innerHTML = html;

            // asociar eventos de detalles
            document.querySelectorAll('.btn-detalles').forEach(btn => {
                btn.addEventListener('click', function(){
                    const id = this.getAttribute('data-id');
                    const libro = libros.find(x => String(x.id) === String(id));
                    if (libro) abrirModal(libro);
                });
            });
        }

        function abrirModal(libro) {
            document.getElementById('modalTitle').textContent = libro.titulo;
            document.getElementById('modalTitulo').textContent = libro.titulo;
            document.getElementById('modalAutor').textContent = libro.autor;
            // campo adicional para la línea con icono
            document.getElementById('modalAutorLinea').textContent = libro.autor;
            document.getElementById('modalMateria').textContent = libro.materia;
            document.getElementById('modalAnio').textContent = libro.anio || libro.year || '';
            document.getElementById('modalPaginas').textContent = libro.paginas || '-';
            document.getElementById('modalDisponibles').textContent = libro.cantidad || '0';
            document.getElementById('modalISBN').textContent = libro.isbn || '-';
            document.getElementById('modalDescripcion').innerHTML = libro.descripcion || '';
            const img = libro.imagen && libro.imagen.trim() !== '' ? baseUrl + 'Assets/img/libros/' + libro.imagen : baseUrl + 'Assets/img/no-cover.png';
            document.getElementById('modalImagen').src = img;

            // badge de disponibilidad
            const badge = document.getElementById('modalBadge');
            if (Number(libro.cantidad) > 0) {
                badge.textContent = 'Disponible';
                badge.className = 'badge badge-success';
            } else {
                badge.textContent = 'No disponible';
                badge.className = 'badge badge-secondary';
            }

            const leerBtn = document.getElementById('modalLeerBtn');
            if (libro.pdf_path && libro.pdf_path.trim() !== '') {
                // guardar ruta en data attribute y habilitar
                leerBtn.dataset.pdf = baseUrl + libro.pdf_path;
                leerBtn.disabled = false;
            } else {
                leerBtn.dataset.pdf = '';
                leerBtn.disabled = true;
            }

            // abrir modal
            $('#modalDetalles').modal('show');
        }

        // filtros y búsqueda
        function aplicarFiltros() {
            const q = document.getElementById('buscar').value.trim().toLowerCase();
            const catBtn = document.querySelector('.categoria-btn.active');
            const categoria = catBtn ? catBtn.getAttribute('data-materia') : 'all';
            const filtroNuevos = document.getElementById('filtroNuevos').classList.contains('active');

            let resultados = libros.slice();
            // categoria
            if (categoria && categoria !== 'all') {
                resultados = resultados.filter(l => String(l.id_materia) === String(categoria));
            }
            // búsqueda por título, autor, materia
            if (q) {
                resultados = resultados.filter(l => (l.titulo || '').toLowerCase().includes(q) || (l.autor || '').toLowerCase().includes(q) || (l.materia || '').toLowerCase().includes(q));
            }
            // nuevos ingresos -> ordenar por id desc
            if (filtroNuevos) {
                resultados.sort((a,b) => (Number(b.id) || 0) - (Number(a.id) || 0));
            }

            renderizarLista(resultados);
        }

        // eventos UI
        document.addEventListener('DOMContentLoaded', function(){
            // por defecto mostrar catálogo ordenado por id asc
            renderizarLista(libros);

            document.getElementById('btnBuscar').addEventListener('click', aplicarFiltros);
            document.getElementById('buscar').addEventListener('keypress', function(e){ if (e.key === 'Enter') aplicarFiltros(); });

            document.getElementById('filtroCatalogo').addEventListener('click', function(){
                this.classList.add('active');
                document.getElementById('filtroNuevos').classList.remove('active');
                aplicarFiltros();
            });
            document.getElementById('filtroNuevos').addEventListener('click', function(){
                this.classList.add('active');
                document.getElementById('filtroCatalogo').classList.remove('active');
                aplicarFiltros();
            });

            document.querySelectorAll('.categoria-btn').forEach(btn => {
                btn.addEventListener('click', function(){
                    document.querySelectorAll('.categoria-btn').forEach(b => b.classList.remove('active','btn-primary'));
                    this.classList.add('active','btn-primary');
                    aplicarFiltros();
                });
            });

            // handler para botón Leer dentro del modal
            const modalLeer = document.getElementById('modalLeerBtn');
            if (modalLeer) {
                modalLeer.addEventListener('click', function(){
                    const url = this.dataset.pdf || '';
                    if (url) {
                        window.open(url, '_blank');
                    }
                });
            }

            // Manejar clic en botón "Solicitar Préstamo"
            document.addEventListener('click', function(e){
                if (e.target.classList.contains('btn-solicitud')) {
                    const id = e.target.getAttribute('data-id');
                    const titulo = e.target.getAttribute('data-titulo');
                    abrirModalSolicitud(id, titulo);
                }
            });
        });

        function abrirModalSolicitud(id_libro, titulo) {
            document.getElementById('solLibroId').value = id_libro;
            document.getElementById('libroTitulo').textContent = titulo;
            document.getElementById('solCantidad').value = '1';
            document.getElementById('solObservacion').value = '';
            $('#modalSolicitud').modal('show');
        }

        function enviarSolicitud() {
            const id_libro = document.getElementById('solLibroId').value;
            const cantidad = document.getElementById('solCantidad').value;
            const observacion = document.getElementById('solObservacion').value;

            if (!id_libro || !cantidad) {
                Swal.fire('Error', 'Datos incompletos', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('id_libro', id_libro);
            formData.append('cantidad', cantidad);
            formData.append('observacion', observacion);

            fetch(baseUrl + 'Catalogo/solicitudPrestamo', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                Swal.fire('Resultado', data.msg, data.icono);
                if (data.icono === 'success') {
                    $('#modalSolicitud').modal('hide');
                    // Recargar tabla de solicitudes si existe
                    if (typeof cargarMisSolicitudes === 'function') {
                        cargarMisSolicitudes();
                    }
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'No se pudo procesar la solicitud', 'error');
            });
        }
    </script>

</div>

<?php include "Views/Templates/footer.php"; ?>