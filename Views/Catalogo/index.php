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
    <style>
        /* Estilos para el botón interactivo y animaciones */
        .btn-magic {
            background: linear-gradient(135deg, #1499f7 0%, #0d72b9 100%);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 20px 60px; /* Más grande */
            font-size: 1.5rem; /* Texto más grande */
            font-weight: bold;
            letter-spacing: 1px;
            box-shadow: 0 10px 20px rgba(20, 153, 247, 0.4);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-magic i {
            margin-right: 15px;
            font-size: 1.8rem; /* Icono más grande */
            transition: transform 0.3s ease;
        }

        .btn-magic:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 30px rgba(20, 153, 247, 0.6);
            background: linear-gradient(135deg, #1499f7 0%, #4cb5f9 100%);
            text-decoration: none;
        }

        .btn-magic:hover i {
            transform: rotate(-10deg) scale(1.2);
        }

        .btn-magic:active {
            transform: translateY(-2px) scale(0.98);
            box-shadow: 0 5px 10px rgba(20, 153, 247, 0.3);
        }

        /* Animación de entrada para el PDF */
        .reveal-pdf {
            animation: slideUpFade 0.8s ease-out forwards;
        }

        @keyframes slideUpFade {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- Estilos para Modal Deslizante (Drawer) --- */
        .modal.modal-right .modal-dialog {
            position: fixed;
            margin: auto;
            width: 100%;
            max-width: 1700px; /* Ancho del panel lateral */
            height: 100%;
            right: -800px; /* Oculto a la derecha inicialmente */
            top: 0;
            bottom: 0;
            transition: right 0.3s ease-out;
            -webkit-transform: none;
            transform: none;
        }

        .modal.modal-right.show .modal-dialog {
            right: 0; /* Se desliza a su posición */
        }

        .modal.modal-right .modal-content {
            height: 100%;
            border-radius: 0;
            border: none;
            overflow-y: auto; /* Scroll interno */
            box-shadow: -5px 0 25px rgba(0,0,0,0.2);
        }
        
        /* Ajustes del Header para poner X a la izquierda */
        .modal-header-drawer {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            background-color: #fff;
        }
        
        /* FAB Quizz Button Styles */
        .fab-quizz-container {
            position: fixed; /* Fijo respecto al modal si el modal usa transform, pero aquí lo pondremos absolute dentro del content */
            bottom: 30px;
            right: 30px;
            z-index: 1060; /* Por encima del contenido */
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .fab-quizz-btn {
            width: 150px; /* Más grande */
            height: 150px; /* Más grande */
            border-radius: 50%;
            background: linear-gradient(135deg, #1499f7 0%, #0d72b9 100%); /* Azul */
            border: none;
            box-shadow: 0 6px 20px rgba(20, 153, 247, 0.5);
            color: white;
            font-size: 32px; /* Icono más grande */
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }

        .fab-quizz-btn:hover {
            transform: scale(1.15) rotate(10deg);
            background: linear-gradient(135deg, #1499f7 0%, #4cb5f9 100%);
        }

        .fab-quizz-btn::after {
            content: '';
            position: absolute;
            top: -2px;
            right: -2px;
            width: 18px;
            height: 18px;
            background-color: #FFD700; /* Alerta de contraste */
            border-radius: 50%;
            border: 2px solid #fff;
        }

        /* El "globo" de mensaje que aparece al hover o init */
        .fab-tooltip {
            background: white;
            padding: 20px 25px; /* Más padding */
            border-radius: 20px 20px 5px 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
            max-width: 320px; /* Más ancho */
            opacity: 0;
            transform: translateY(20px) scale(0.8);
            transform-origin: bottom right;
            transition: all 0.3s ease;
            pointer-events: none; 
            position: relative;
            border: 1px solid #eef2f7;
        }

        .fab-quizz-container:hover .fab-tooltip, 
        .fab-tooltip.show {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

        .fab-tooltip h6 {
            color: #1499f7; /* Título azul */
            font-weight: 800;
            margin-bottom: 8px;
            font-size: 1.2rem; /* Título más grande */
        }
        .fab-tooltip p {
            font-size: 1.05rem; /* Texto más grande */
            color: #555;
            margin: 0;
            line-height: 1.5;
        }

        /* --- NUEVOS ESTILOS PREMIUN PARA EL MODAL --- */
        
        /* Fondo del modal con un toque sutil */
        .modal-body-custom {
            background-color: #f8f9fa;
            min-height: 100%;
        }

        /* Efecto 3D para la portada del libro */
        .book-cover-3d {
            border-radius: 12px;
            box-shadow: 
                -10px 10px 20px rgba(0,0,0,0.2),
                -2px 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            max-height: 450px;
            object-fit: cover;
            width: 100%;
        }
        .book-cover-container:hover .book-cover-3d {
            transform: translateY(-5px) rotateY(-5deg);
            box-shadow: 
                -15px 15px 25px rgba(0,0,0,0.25),
                -5px 5px 10px rgba(0,0,0,0.1);
        }

        /* Tarjetas de Metadatos (Info Grid) */
        .meta-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            border-left: 4px solid #1499f7; /* Acento azul */
            transition: transform 0.2s;
            height: 100%;
            display: flex;
            align-items: center;
        }
        .meta-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(20, 153, 247, 0.15);
        }
        .meta-icon {
            width: 45px;
            height: 45px;
            background-color: rgba(20, 153, 247, 0.1);
            color: #1499f7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-right: 15px;
        }
        .meta-content h6 {
            font-size: 0.85rem;
            color: #888;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .meta-content p {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
            margin: 0;
        }

        /* Títulos y Textos */
        .book-title-large {
            font-size: 2.2rem;
            font-weight: 800;
            color: #2c3e50;
            line-height: 1.2;
        }
        .book-author-large {
            font-size: 1.3rem;
            color: #1499f7;
            font-weight: 500;
        }
        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 20px;
            margin-top: 30px;
            display: flex;
            align-items: center;
        }
        .section-title i {
            color: #1499f7;
            margin-right: 10px;
        }
        .synopsis-text {
            font-size: 1.15rem;
            line-height: 1.8;
            color: #555;
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        /* Animaciones de Entrada (Staggered) */
        .animate-entry {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUpEntry 0.6s cubic-bezier(0.165, 0.84, 0.44, 1) forwards;
        }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }

        @keyframes fadeUpEntry {
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

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

    <!-- Modal de detalles (Estilo Drawer Lateral) -->
    <div class="modal fade modal-right" id="modalDetalles" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <!-- Header personalizado con X a la izquierda -->
          <div class="modal-header-drawer">
            <button type="button" class="btn-close-drawer" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="modal-title mb-0" id="modalTitle" style="flex-grow:1;">Detalles</h5>
          </div>

          <div class="modal-body p-0 modal-body-custom">
            <!-- Seccion Principal (Hero) -->
            <div class="p-5">
                <div class="row align-items-center">
                    <!-- Columna Portada -->
                    <div class="col-lg-4 mb-4 mb-lg-0 text-center animate-entry book-cover-container">
                        <img id="modalImagen" src="" class="book-cover-3d" alt="Portada del Libro">
                    </div>
                    
                    <!-- Columna Detalles -->
                    <div class="col-lg-8 pl-lg-5">
                        <div class="animate-entry delay-1">
                            <span id="modalBadge" class="badge badge-success px-3 py-2 mb-3" style="font-size: 1rem; border-radius: 20px;">Disponible</span>
                            <h2 id="modalTitulo" class="book-title-large mb-2"></h2>
                            <p class="book-author-large mb-4">
                                <i class="fa fa-pen-nib mr-2"></i><span id="modalAutor"></span>
                            </p>
                        </div>

                        <!-- Grid de Estadísticas (Tarjetas) -->
                        <div class="row animate-entry delay-2">
                            <div class="col-sm-6 col-md-6 mb-4">
                                <div class="meta-card">
                                    <div class="meta-icon"><i class="fa fa-bookmark"></i></div>
                                    <div class="meta-content">
                                        <h6>Categoría</h6>
                                        <p id="modalMateria"></p>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="col-sm-6 col-md-6 mb-4">
                                <div class="meta-card">
                                    <div class="meta-icon"><i class="fa fa-calendar-alt"></i></div>
                                    <div class="meta-content">
                                        <h6>Año</h6>
                                        <p id="modalAnio"></p>
                                    </div>
                                </div>
                            </div> -->
                            <!--<div class="col-sm-6 col-md-6 mb-4">
                                <div class="meta-card">
                                    <div class="meta-icon"><i class="fa fa-file-alt"></i></div>
                                    <div class="meta-content">
                                        <h6>Páginas</h6>
                                        <p id="modalPaginas"></p>
                                    </div>
                                </div>
                            </div>-->
                            <!--<div class="col-sm-6 col-md-6 mb-4">
                                <div class="meta-card">
                                    <div class="meta-icon"><i class="fa fa-fingerprint"></i></div>
                                    <div class="meta-content">
                                        <h6>ISBN</h6>
                                        <p id="modalISBN"></p>
                                    </div>
                                </div>
                            </div>-->
                        </div>
                    </div>
                </div>
            
                <!-- Sección Descripción -->
                <div class="row mt-4 animate-entry delay-3">
                    <div class="col-12">
                        <h5 class="section-title"><i class="fa fa-align-left"></i> Descripción</h5>
                        <div id="modalDescripcion" class="synopsis-text"></div>
                    </div>
                </div>

                <!-- Botón Flotante de Lectura -->
                <div id="divBotonLectura" class="row mt-5 text-center animate-entry delay-4" style="display:none;">
                    <div class="col-12">
                        <button id="btnIniciarLectura" class="btn-magic shadow">
                            <i class="fa fa-book-reader"></i> ¡Comenzar a Leer Ahora!
                        </button>
                        <p class="text-muted mt-3 lead animate__animated animate__fadeIn" style="font-size: 1.1rem;">Haz clic arriba para abrir el libro digital</p>
                    </div>
                </div>
                
                <!-- Contenedor del PDF -->
                <div id="modalPdfContainer" style="display:none;" class="row mt-5 animate-entry">
                    <div class="col-12">
                        <h5 class="section-title"><i class="fa fa-book-open"></i> Lectura Digital</h5>
                        <div class="embed-responsive embed-responsive-16by9 shadow rounded" style="height: 80vh; border: 1px solid #ddd; border-radius: 15px;">
                            <iframe id="modalPdfViewer" class="embed-responsive-item" src="" allowfullscreen style="border-radius: 15px;"></iframe>
                        </div>
                    </div>
                </div>

            </div> <!-- Fin padding -->
            
            <!-- FAB Quizz dentro del modal (inicialmente oculto) -->
            <div id="fabQuizzContainer" class="fab-quizz-container" style="display:none;">
                <div class="fab-tooltip animate__animated animate__fadeInUp">
                    <h6>¡Desafío Mental!</h6>
                    <p>¿Terminaste de leer? Pon a prueba lo aprendido con un divertido Quizz o Rompecabezas.</p>
                </div>
                <button id="btnFabQuizz" class="fab-quizz-btn animate__animated animate__bounceIn">
                    <i class="fa fa-puzzle-piece"></i>
                </button>
            </div>

          </div>
          <!-- No footer needed for drawer style usually, or minimal -->
                    <!-- <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div> -->
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
                            <button class="btn btn-outline-secondary btn-sm btn-detalles btn-block" data-id="${libro.id}">Ver detalles</button>
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
            // document.getElementById('modalAutorLinea').textContent = libro.autor; // Ya no se usa en nuevo diseño
            document.getElementById('modalMateria').textContent = libro.materia;
            //document.getElementById('modalAnio').textContent = libro.anio || libro.year || '';
            //document.getElementById('modalPaginas').textContent = libro.paginas || '-';
            // document.getElementById('modalDisponibles').textContent = libro.cantidad || '0'; // Oculto en diseño drawer
            //document.getElementById('modalISBN').textContent = libro.isbn || '-';
            document.getElementById('modalDescripcion').innerHTML = libro.descripcion || '';
            const img = libro.imagen && libro.imagen.trim() !== '' ? baseUrl + 'Assets/img/libros/' + libro.imagen : baseUrl + 'Assets/img/no-cover.png';
            document.getElementById('modalImagen').src = img;

            // badge de disponibilidad
            const badge = document.getElementById('modalBadge');
            if (Number(libro.cantidad) > 0) {
                badge.textContent = 'Disponible';
                badge.className = 'badge badge-success px-3 py-2 mb-3';
            } else {
                badge.textContent = 'No disponible';
                badge.className = 'badge badge-secondary px-3 py-2 mb-3';
            }

            // Reiniciar animaciones (hack para re-trigger)
            const animatedElements = document.querySelectorAll('.animate-entry');
            animatedElements.forEach(el => {
                el.style.animation = 'none';
                el.offsetHeight; /* trigger reflow */
                el.style.animation = null; 
            });

            // Manejo del PDF embebido - MODIFICADO para UX Interactiva
            const pdfContainer = document.getElementById('modalPdfContainer');
            const pdfViewer = document.getElementById('modalPdfViewer');
            const btnLecturaContainer = document.getElementById('divBotonLectura');
            const btnLectura = document.getElementById('btnIniciarLectura');
            const fabQuizz = document.getElementById('fabQuizzContainer');
            const btnFabQuizz = document.getElementById('btnFabQuizz');
            
            // Resetear estados
            pdfContainer.style.display = 'none';
            pdfContainer.classList.remove('reveal-pdf');
            btnLecturaContainer.style.display = 'none';
            // Limpiar posible mensaje de "No disponible" previo
            const noPdfMsg = document.getElementById('msgNoPdf');
            if(noPdfMsg) noPdfMsg.remove();

            fabQuizz.style.display = 'none'; // Ocultar FAB al inicio
            pdfViewer.src = ''; // Limpiar src previo

            if (libro.pdf_path && libro.pdf_path.trim() !== '') {
                // Mostrar botón de lectura
                btnLecturaContainer.style.display = 'block';
                
                // Configurar evento del botón
                btnLectura.onclick = function() {
                    // Ocultar botón con suavidad (opcional, o simplemente ocultar)
                    btnLecturaContainer.style.display = 'none';
                    
                    // Mostrar contenedor PDF con animación
                    pdfViewer.src = baseUrl + libro.pdf_path;
                    pdfContainer.style.display = 'flex';
                    pdfContainer.classList.add('reveal-pdf');

                    // Mostrar FAB de Quizz después de un pequeño delay para simular que aparece al empezar a leer
                    setTimeout(() => {
                        fabQuizz.style.display = 'flex';
                        // Configurar clic del FAB
                        btnFabQuizz.onclick = function() {
                            abrirMenuQuiz(libro.id);
                        };
                    }, 1000);
                };
            } else {
                // Mensaje cuando NO hay PDF
                const msgDiv = document.createElement('div');
                msgDiv.id = 'msgNoPdf';
                msgDiv.className = 'row mt-5 text-center animate-entry delay-4';
                msgDiv.innerHTML = `
                    <div class="col-12">
                        <div class="p-4 rounded bg-light border" style="border-style: dashed !important; border-color: #ccc !important;">
                            <i class="fa fa-book-open text-muted mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                            <h5 class="text-muted">Versión digital no disponible</h5>
                            <p class="text-muted small mb-0">Este título solo se encuentra disponible para préstamo físico en biblioteca.</p>
                        </div>
                    </div>
                `;
                // Insertar después de la descripción (o donde iría el botón de lectura)
                // Buscamos el contenedor padre (modal-body .p-5)
                document.querySelector('.modal-body-custom > .p-5').appendChild(msgDiv);
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

            // Manejar clic en botón "Solicitar Préstamo"
            document.addEventListener('click', function(e){
                if (e.target.classList.contains('btn-solicitud')) {
                    const id = e.target.getAttribute('data-id');
                    const titulo = e.target.getAttribute('data-titulo');
                    abrirModalSolicitud(id, titulo);
                }
                // Manejar clic en botón "Quiz"
                // if (e.target.classList.contains('btn-quiz') || e.target.closest('.btn-quiz')) {
                //     const btn = e.target.classList.contains('btn-quiz') ? e.target : e.target.closest('.btn-quiz');
                //     const idLibro = btn.getAttribute('data-id');
                //     abrirMenuQuiz(idLibro);
                // }
            });

            // Limpiar iframe al cerrar modal
            $('#modalDetalles').on('hidden.bs.modal', function () {
                const pdfViewer = document.getElementById('modalPdfViewer');
                if(pdfViewer) pdfViewer.src = "";
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

        // Funciones para el módulo de Quiz
        function abrirMenuQuiz(idLibro) {
            Swal.fire({
                title: '<span style="color:#2c3e50; font-weight:800; font-size:1.8rem;">AUTOEVALUACIÓN DIDÁCTICA</span>',
                html: `
                    <div style="text-align: center; padding: 0 20px;">
                        <p style="font-size: 1.1rem; color: #555; line-height: 1.6; margin-bottom: 30px;">
                            ¡Felicidades por tu lectura! <br>
                            Esta herramienta está diseñada para potenciar tu <strong>comprensión lectora</strong> y fortalecer tu aprendizaje. 
                            Realizar estas actividades te ayudará a concentrarte mejor, retener información clave y hacer que tu educación sea mucho más divertida. 
                            <br><br>
                            <strong>¡Elige tu desafío y demuestra lo que sabes!</strong>
                        </p>

                        <!-- Contenedor del Menú Interactivo -->
                        <div class="interaction-menu-container" style="position: relative; height: 200px; display: flex; align-items: center; justify-content: center;">
                            
                            <!-- Estilos internos para este componente (inyectados aquí para que SweetAlert los tome bien) -->
                            <style>
                                .menu-trigger {
                                    width: 80px;
                                    height: 80px;
                                    background: linear-gradient(135deg, #6c5ce7, #a29bfe);
                                    border-radius: 50%;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    cursor: pointer;
                                    box-shadow: 0 10px 25px rgba(108, 92, 231, 0.4);
                                    z-index: 10;
                                    transition: transform 0.3s, box-shadow 0.3s;
                                    position: absolute;
                                    bottom: 20px;
                                }
                                .menu-trigger i {
                                    color: white;
                                    font-size: 2rem;
                                    transition: transform 0.3s;
                                }
                                .menu-trigger:hover {
                                    transform: scale(1.1);
                                    box-shadow: 0 15px 35px rgba(108, 92, 231, 0.5);
                                }
                                
                                /* Botones de opciones (inicialmente ocultos/colapsados) */
                                .option-btn {
                                    width: 70px;
                                    height: 70px;
                                    border-radius: 50%;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    position: absolute;
                                    bottom: 25px; /* Mismo centro vertical relativo al trigger */
                                    left: 50%;
                                    transform: translateX(-50%) scale(0);
                                    opacity: 0;
                                    transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                                    cursor: pointer;
                                    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                                    text-decoration: none;
                                }
                                .option-btn i {
                                    color: white;
                                    font-size: 1.5rem;
                                }
                                
                                /* Tooltip de la opción */
                                .option-label {
                                    position: absolute;
                                    top: -35px;
                                    background: #333;
                                    color: white;
                                    padding: 5px 15px;
                                    border-radius: 15px;
                                    font-size: 0.8rem;
                                    white-space: nowrap;
                                    opacity: 0;
                                    transition: opacity 0.3s;
                                    pointer-events: none;
                                }
                                .option-btn:hover .option-label {
                                    opacity: 1;
                                }
                                .option-btn:hover {
                                    transform: translateX(-50%) scale(1.1) !important; /* Mantener la posición expandida */
                                    z-index: 12;
                                }

                                /* Estado Activo (Hover sobre el contenedor o trigger) */
                                .interaction-menu-container:hover .menu-trigger,
                                .menu-trigger.active {
                                    transform: scale(0.9); /* Efecto visual en el trigger */
                                }
                                .interaction-menu-container:hover .menu-trigger i {
                                    transform: rotate(45deg);
                                }

                                /* Posiciones al expandir - ESTABLES */
                                .interaction-menu-container:hover .opt-quiz {
                                    transform: translate(-100px, -60px) scale(1); /* Posición fija expandida */
                                    opacity: 1;
                                    background: linear-gradient(135deg, #00b894, #55efc4);
                                }
                                .interaction-menu-container:hover .opt-puzzle {
                                    transform: translate(30px, -60px) scale(1); /* Posición fija expandida */
                                    opacity: 1;
                                    background: linear-gradient(135deg, #6a11cb, #2575fc);
                                }

                                /* Animación Hover individual en los botones HIJOS (solo escalar, sin mover) */
                                .opt-quiz:hover {
                                    transform: translate(-100px, -60px) scale(1.15) !important; /* Mantiene posición, solo escala */
                                    box-shadow: 0 8px 20px rgba(0,184,148,0.4);
                                    z-index: 20;
                                }
                                .opt-puzzle:hover {
                                    transform: translate(30px, -60px) scale(1.15) !important; /* Mantiene posición, solo escala */
                                    box-shadow: 0 8px 20px rgba(225,112,85,0.4);
                                    z-index: 20;
                                }

                            </style>

                            <!-- Botón Trigger Central -->
                            <div class="menu-trigger">
                                <i class="fa fa-plus"></i>
                            </div>

                            <!-- Opción 1: Cuestionario -->
                            <div class="option-btn opt-quiz" onclick="irAlQuiz(${idLibro})">
                                <span class="option-label">Cuestionario</span>
                                <i class="fa fa-question"></i>
                            </div>

                            <!-- Opción 2: Rompecabezas -->
                            <div class="option-btn opt-puzzle" onclick="irAlRompecabezas(${idLibro})">
                                <span class="option-label">Rompecabezas</span>
                                <i class="fa fa-puzzle-piece"></i>
                            </div>

                            <p style="position: absolute; bottom: -10px; width: 100%; font-size: 0.9rem; color: #999; margin: 0;">
                                Acércate al botón para ver opciones
                            </p>
                        </div>
                    </div>
                `,
                width: 800,
                showConfirmButton: false,
                showCloseButton: true,
                didOpen: () => {
                    const swalContent = Swal.getHtmlContainer();
                    if (swalContent) {
                        swalContent.style.overflow = 'visible'; // Permitir que salgan tooltips si es necesario
                    }
                }
            });
        }

        function irAlQuiz(idLibro) {
            window.location.href = baseUrl + 'Quiz/resolver?tipo=quiz&id=' + idLibro;
        }

        function irAlRompecabezas(idLibro) {
            window.location.href = baseUrl + 'Quiz/resolver?tipo=rompecabezas&id=' + idLibro;
        }
    </script>

</div>

<?php include "Views/Templates/footer.php"; ?>