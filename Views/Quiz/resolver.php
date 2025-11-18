<?php include "Views/Templates/header.php"; ?>

<style>
    .pregunta-container {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        transition: all 0.3s;
    }

    .pregunta-container.correcta {
        background: #d4edda;
        border: 2px solid #28a745;
    }

    .pregunta-container.incorrecta {
        background: #f8d7da;
        border: 2px solid #dc3545;
    }

    .opcion-btn {
        display: block;
        width: 100%;
        text-align: left;
        padding: 15px;
        margin: 10px 0;
        border: 2px solid #ddd;
        border-radius: 5px;
        background: white;
        cursor: pointer;
        transition: all 0.3s;
    }

    .opcion-btn:hover {
        border-color: #007bff;
        background: #f0f7ff;
    }

    .opcion-btn.seleccionada {
        border-color: #007bff;
        background: #cfe2ff;
    }

    .pieza-rompecabezas {
        display: inline-block;
        padding: 10px 15px;
        margin: 5px;
        background: #fff3cd;
        border: 2px dashed #ffc107;
        border-radius: 5px;
        cursor: move;
        font-weight: bold;
    }

    .posicion-rompecabezas {
        display: inline-block;
        min-width: 150px;
        min-height: 50px;
        padding: 10px;
        margin: 10px;
        background: #e7f3ff;
        border: 2px dashed #0275d8;
        border-radius: 5px;
        text-align: center;
        color: #ccc;
    }

    .posicion-rompecabezas.completada {
        background: #d4edda;
        border-color: #28a745;
        color: #000;
    }

    .resultado-mensaje {
        font-size: 24px;
        font-weight: bold;
        text-align: center;
        padding: 20px;
        border-radius: 8px;
    }

    .resultado-exito {
        background: #d4edda;
        color: #155724;
    }

    .resultado-parcial {
        background: #fff3cd;
        color: #856404;
    }

    .resultado-fallo {
        background: #f8d7da;
        color: #721c24;
    }
</style>

<div class="app-title">
    <div>
        <h1 id="tipoActividad"><i class="fa fa-book"></i> Quiz</h1>
    </div>
</div>

<!-- QUIZ -->
<div id="quizContainer" style="display:none;">
    <div class="row">
        <div class="col-md-3">
            <div class="tile">
                <h3>Progreso</h3>
                <div class="progress">
                    <div class="progress-bar" id="progresoBarra" style="width: 0%"></div>
                </div>
                <p id="progresoText">0/5</p>
            </div>
        </div>
        <div class="col-md-9">
            <div id="preguntasContainer"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 text-center">
            <button class="btn btn-primary btn-lg" onclick="enviarQuiz()">
                <i class="fa fa-check"></i> Enviar Respuestas
            </button>
        </div>
    </div>
</div>

<!-- ROMPECABEZAS -->
<div id="rompecabezasContainer" style="display:none;">
    <div class="row">
        <div class="col-12">
            <div class="tile">
                <h3 id="tituloRompecabezas"></h3>
                <p id="instruccionesRompecabezas"></p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="tile">
                <h4>Piezas Disponibles</h4>
                <div id="piezasDisponibles"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="tile">
                <h4>Ordena las Piezas</h4>
                <div id="posicionesRompecabezas"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 text-center">
            <button class="btn btn-primary btn-lg" onclick="enviarRompecabezas()">
                <i class="fa fa-check"></i> Verificar Rompecabezas
            </button>
        </div>
    </div>
</div>

<!-- RESULTADO -->
<div id="resultadoContainer" style="display:none;">
    <div class="row">
        <div class="col-12">
            <div id="resultadoCard" class="tile resultado-mensaje"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 text-center">
            <button class="btn btn-primary" onclick="location.href=baseUrl+'Catalogo'">
                <i class="fa fa-arrow-left"></i> Volver al Catálogo
            </button>
        </div>
    </div>
</div>

<script>
    const baseUrl = '<?php echo base_url; ?>';
    const tipoActividad = new URLSearchParams(window.location.search).get('tipo') || 'quiz';
    const idLibro = new URLSearchParams(window.location.search).get('id');
    let respuestasSeleccionadas = {};
    let piezasOrdenadas = {};

    // Cargar actividad
    function cargarActividad() {
        if (tipoActividad === 'quiz') {
            cargarQuiz();
        } else if (tipoActividad === 'rompecabezas') {
            cargarRompecabezas();
        }
    }

    function cargarQuiz() {
        document.getElementById('tipoActividad').innerHTML = '<i class="fa fa-question-circle"></i> Quiz (5 Preguntas)';
        document.getElementById('quizContainer').style.display = 'block';

        console.log('Cargando Quiz para libro:', idLibro);
        fetch(baseUrl + 'Quiz/obtenerQuizEstudiante?id_libro=' + idLibro)
            .then(res => {
                console.log('Response status:', res.status);
                return res.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                if (data.error || !data || data.length === 0) {
                    document.getElementById('preguntasContainer').innerHTML = '<p class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> No hay preguntas disponibles para este libro</p>';
                    return;
                }

                let html = '';
                data.forEach((preg, idx) => {
                    html += '<div class="pregunta-container" id="pregunta_' + preg.id + '">';
                    html += '<h5>Pregunta ' + (idx + 1) + ': ' + preg.texto_pregunta + '</h5>';
                    html += '<div>';
                    if (preg.opciones && preg.opciones.length > 0) {
                        preg.opciones.forEach(opt => {
                            html += '<button type="button" class="opcion-btn" onclick="seleccionarOpcion(' + preg.id + ', ' + opt.id + ', this)">';
                            html += '<span class="opcion-radio" style="display:inline-block; margin-right: 10px;">○</span>';
                            html += opt.texto_opcion;
                            html += '</button>';
                        });
                    } else {
                        html += '<p class="text-danger">Error: No hay opciones para esta pregunta</p>';
                    }
                    html += '</div>';
                    html += '</div>';
                });

                document.getElementById('preguntasContainer').innerHTML = html;
            })
            .catch(err => {
                console.error('Error en cargarQuiz:', err);
                document.getElementById('preguntasContainer').innerHTML = '<p class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Error al cargar quiz</p>';
            });
    }

    function seleccionarOpcion(idPregunta, idOpcion, btn) {
        // Deseleccionar otros botones de la misma pregunta
        document.querySelectorAll('#pregunta_' + idPregunta + ' .opcion-btn').forEach(b => {
            b.classList.remove('seleccionada');
            b.querySelector('.opcion-radio').textContent = '○';
        });

        // Seleccionar este botón
        btn.classList.add('seleccionada');
        btn.querySelector('.opcion-radio').textContent = '●';
        respuestasSeleccionadas[idPregunta] = idOpcion;

        // Actualizar progreso
        actualizarProgreso();
    }

    function actualizarProgreso() {
        const total = document.querySelectorAll('.pregunta-container').length;
        const respondidas = Object.keys(respuestasSeleccionadas).length;
        const porcentaje = (respondidas / total) * 100;

        document.getElementById('progresoBarra').style.width = porcentaje + '%';
        document.getElementById('progresoText').textContent = respondidas + '/' + total;
    }

    function enviarQuiz() {
        const fd = new FormData();
        fd.append('id_libro', idLibro);
        fd.append('respuestas', JSON.stringify(respuestasSeleccionadas));

        fetch(baseUrl + 'Quiz/calificarQuiz', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                mostrarResultado(data);
            })
            .catch(err => console.error(err));
    }

    function cargarRompecabezas() {
        document.getElementById('tipoActividad').innerHTML = '<i class="fa fa-puzzle-piece"></i> Rompecabezas';
        document.getElementById('rompecabezasContainer').style.display = 'block';

        console.log('Cargando Rompecabezas para libro:', idLibro);
        fetch(baseUrl + 'Quiz/obtenerRompecabezasEstudiante?id_libro=' + idLibro)
            .then(res => {
                console.log('Response status:', res.status);
                return res.json();
            })
            .then(data => {
                console.log('Datos rompecabezas:', data);
                if (data.error || !data.id || !data.piezas) {
                    document.getElementById('piezasDisponibles').innerHTML = '<p class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> No hay rompecabezas disponible para este libro</p>';
                    return;
                }

                document.getElementById('tituloRompecabezas').textContent = data.titulo || 'Rompecabezas';
                document.getElementById('instruccionesRompecabezas').textContent = data.instrucciones || '';

                let htmlPiezas = '';
                data.piezas.forEach(pieza => {
                    htmlPiezas += '<div class="pieza-rompecabezas" draggable="true" ondragstart="dragStart(event, ' + pieza.id + ')" data-id="' + pieza.id + '">';
                    htmlPiezas += pieza.texto_pieza;
                    htmlPiezas += '</div>';
                });
                document.getElementById('piezasDisponibles').innerHTML = htmlPiezas;

                // Crear posiciones
                let htmlPosiciones = '';
                for (let i = 1; i <= data.piezas.length; i++) {
                    htmlPosiciones += '<div class="posicion-rompecabezas" ondrop="drop(event, ' + i + ')" ondragover="dragover(event)" id="posicion_' + i + '">';
                    htmlPosiciones += 'Posición ' + i;
                    htmlPosiciones += '</div>';
                }
                document.getElementById('posicionesRompecabezas').innerHTML = htmlPosiciones;
            })
            .catch(err => {
                console.error('Error en cargarRompecabezas:', err);
                document.getElementById('piezasDisponibles').innerHTML = '<p class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Error al cargar rompecabezas</p>';
            });
    }

    let piezaArrastrada = null;

    function dragStart(e, idPieza) {
        piezaArrastrada = idPieza;
    }

    function dragover(e) {
        e.preventDefault();
    }

    function drop(e, posicion) {
        e.preventDefault();
        if (!piezaArrastrada) return;

        piezasOrdenadas[piezaArrastrada] = posicion;

        const piezaEl = document.querySelector('[data-id="' + piezaArrastrada + '"]');
        const posicionEl = document.getElementById('posicion_' + posicion);
        posicionEl.innerHTML = piezaEl.innerHTML;
        posicionEl.classList.add('completada');

        piezaEl.style.opacity = '0.5';
        piezaArrastrada = null;
    }

    function enviarRompecabezas() {
        const fd = new FormData();
        fd.append('id_libro', idLibro);
        fd.append('respuestas', JSON.stringify(piezasOrdenadas));

        fetch(baseUrl + 'Quiz/calificarRompecabezas', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                mostrarResultado(data);
            })
            .catch(err => console.error(err));
    }

    function mostrarResultado(data) {
        // Manejar respuestas de error o formato inesperado
        if (!data) {
            Swal.fire('Error','Respuesta inválida del servidor','error');
            return;
        }
        if (data.icono && data.icono !== 'success') {
            const msg = data.msg || 'Ocurrió un error';
            Swal.fire(msg, '', data.icono || 'error');
            return;
        }

        // Normalizar valores (evitar undefined)
        const detalles = data.detalles || {};
        const puntuacion = (typeof data.puntuacion !== 'undefined') ? data.puntuacion : (Object.values(detalles).filter(d => d.es_correcta == 1).length || 0);
        const total = (typeof data.total !== 'undefined') ? data.total : (Object.keys(detalles).length || 0);
        const porcentaje = (typeof data.porcentaje !== 'undefined') ? data.porcentaje : (total > 0 ? Math.round((puntuacion/total)*100) : 0);

        // Si hay detalles por pregunta, marcar cada pregunta con clase correcta/incorrecta
        if (Object.keys(detalles).length) {
            Object.keys(detalles).forEach(idPreg => {
                const det = detalles[idPreg];
                const cont = document.getElementById('pregunta_' + idPreg);
                if (!cont) return;
                if (parseInt(det.es_correcta) === 1) {
                    cont.classList.add('correcta');
                    cont.classList.remove('incorrecta');
                } else {
                    cont.classList.add('incorrecta');
                    cont.classList.remove('correcta');
                    // opcional: resaltar la opción correcta
                    if (det.id_correcta) {
                        const botonCorrecto = cont.querySelector('.opcion-btn[onclick*="' + det.id_correcta + '"]');
                        if (botonCorrecto) botonCorrecto.classList.add('seleccionada');
                    }
                }
            });
        }

        document.getElementById('quizContainer').style.display = 'none';
        document.getElementById('rompecabezasContainer').style.display = 'none';
        document.getElementById('resultadoContainer').style.display = 'block';

        const resultCard = document.getElementById('resultadoCard');
        let clase = 'resultado-fallo';
        if (porcentaje >= 80) clase = 'resultado-exito';
        else if (porcentaje >= 60) clase = 'resultado-parcial';

        resultCard.className = 'tile resultado-mensaje ' + clase;
        resultCard.innerHTML = '✅ ¡Completado!<br>';
        resultCard.innerHTML += 'Puntuación: <strong>' + porcentaje + '%</strong><br>';
        resultCard.innerHTML += '(' + puntuacion + ' de ' + total + ' correctas)';
    }

    // Cargar al iniciar
    document.addEventListener('DOMContentLoaded', function() {
        if (!idLibro) {
            alert('Libro no especificado. Regresando al catálogo.');
            window.location.href = baseUrl + 'Catalogo';
            return;
        }
        cargarActividad();
    });
</script>

<?php include "Views/Templates/footer.php"; ?>
