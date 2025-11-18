<?php include "Views/Templates/header.php"; ?>

<div class="app-title">
    <div>
        <h1><i class="fa fa-puzzle-piece"></i> Administración: Quiz y Rompecabezas</h1>
        <p class="lead">Cree y edite preguntas (5) y rompecabezas por libro.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">Seleccionar Libro</h3>
            <div class="tile-body">
                <div class="form-group">
                    <label for="selectLibro">Libro</label>
                    <select id="selectLibro" class="form-control"></select>
                </div>
                <button id="btnCargar" class="btn btn-primary">Cargar datos del libro</button>
            </div>
        </div>

        <div class="tile mt-3" id="preguntasPanel" style="display:none;">
            <h3 class="tile-title">Preguntas (5)</h3>
            <div class="tile-body" id="preguntasBody">
                <!-- las 5 preguntas se generan por JS -->
            </div>
            <div class="tile-footer text-right">
                <button id="btnGuardarTodasPreguntas" class="btn btn-success">Guardar todas las preguntas</button>
            </div>
        </div>

        <div class="tile mt-3" id="rompecabezasPanel" style="display:none;">
            <h3 class="tile-title">Rompecabezas</h3>
            <div class="tile-body">
                <div class="form-group">
                    <label for="tituloRom">Título</label>
                    <input id="tituloRom" class="form-control" />
                </div>
                <div class="form-group">
                    <label for="instruccionesRom">Instrucciones</label>
                    <textarea id="instruccionesRom" class="form-control" rows="3"></textarea>
                </div>

                <h5>Piezas</h5>
                <div id="piezasList"></div>
                <div class="form-row align-items-end mt-2">
                    <div class="col"><input id="textoPieza" class="form-control" placeholder="Texto de la pieza" /></div>
                    <div class="col-2"><input id="posicionPieza" type="number" class="form-control" placeholder="Pos." min="1" /></div>
                    <div class="col-2"><button id="btnAgregarPieza" class="btn btn-secondary btn-block">Agregar</button></div>
                </div>
            </div>
            <div class="tile-footer text-right">
                <button id="btnGuardarRompecabezas" class="btn btn-success">Guardar rompecabezas y piezas</button>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">Ayuda rápida</h3>
            <div class="tile-body">
                <ul>
                    <li>Seleccione un libro y presione <strong>Cargar datos</strong>.</li>
                    <li>Complete las 5 preguntas. Para cada pregunta agregue 4 opciones y marque la correcta.</li>
                    <li>Guarde las preguntas y luego el rompecabezas.</li>
                    <li>Las actividades estarán disponibles para estudiantes desde el catálogo si el libro tiene PDF.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    const baseUrl = '<?php echo base_url; ?>';

    // Cargar lista de libros enviada por el controller (fallback) o usar Select2 con búsqueda remota
    let libros = [];
    function cargarLibros() {
        fetch(baseUrl + 'Quiz/obtenerLibros')
            .then(r => r.json())
            .then(data => {
                libros = data || [];
                const sel = document.getElementById('selectLibro');
                sel.innerHTML = '<option value="0">-- Seleccione un libro --</option>';
                libros.forEach(l => {
                    const opt = document.createElement('option');
                    opt.value = l.id;
                    opt.text = l.titulo + ' - ' + (l.autor || '');
                    sel.appendChild(opt);
                });
            })
            .catch(err => console.error('No se pudo obtener lista de libros', err));
    }

    // Inicializa Select2 con búsqueda remota si la librería está disponible.
    function initSelect2Buscable() {
        try {
            if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
                const $sel = jQuery('#selectLibro');
                $sel.select2({
                    placeholder: 'Buscar Libro',
                    allowClear: true,
                    minimumInputLength: 2,
                    ajax: {
                        url: baseUrl + 'Quiz/obtenerLibros',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return { q: params.term };
                        },
                        processResults: function(data) {
                            data = data || [];
                            return {
                                results: data.map(function(l){
                                    return { id: l.id, text: l.titulo + (l.autor ? ' - ' + l.autor : '') };
                                })
                            };
                        },
                        cache: true
                    },
                    width: '100%'
                });
                return true;
            }
        } catch (e) {
            console.warn('Error inicializando Select2', e);
        }
        return false;
    }

    function generarPreguntasUI() {
        const cont = document.getElementById('preguntasBody');
        cont.innerHTML = '';
        for (let i = 1; i <= 5; i++) {
            const idx = i;
            const card = document.createElement('div');
            card.className = 'mb-3';
            card.innerHTML = `
                <div class="card">
                    <div class="card-body">
                        <h5>Pregunta ${idx}</h5>
                        <div class="form-group">
                            <label>Texto</label>
                            <input class="form-control pregunta-texto" data-num="${idx}" />
                        </div>
                        <div class="form-row">
                            <div class="col-9">
                                <input class="form-control opcion-texto" data-num="${idx}" data-orden="1" placeholder="Opción 1" />
                            </div>
                            <div class="col-3"><div class="form-check"><input class="form-check-input opcion-correcta" type="radio" name="correcta_${idx}" data-num="${idx}" data-orden="1" /></div></div>
                        </div>
                        <div class="form-row mt-2">
                            <div class="col-9">
                                <input class="form-control opcion-texto" data-num="${idx}" data-orden="2" placeholder="Opción 2" />
                            </div>
                            <div class="col-3"><div class="form-check"><input class="form-check-input opcion-correcta" type="radio" name="correcta_${idx}" data-num="${idx}" data-orden="2" /></div></div>
                        </div>
                        <div class="form-row mt-2">
                            <div class="col-9">
                                <input class="form-control opcion-texto" data-num="${idx}" data-orden="3" placeholder="Opción 3" />
                            </div>
                            <div class="col-3"><div class="form-check"><input class="form-check-input opcion-correcta" type="radio" name="correcta_${idx}" data-num="${idx}" data-orden="3" /></div></div>
                        </div>
                        <div class="form-row mt-2">
                            <div class="col-9">
                                <input class="form-control opcion-texto" data-num="${idx}" data-orden="4" placeholder="Opción 4" />
                            </div>
                            <div class="col-3"><div class="form-check"><input class="form-check-input opcion-correcta" type="radio" name="correcta_${idx}" data-num="${idx}" data-orden="4" /></div></div>
                        </div>
                    </div>
                </div>
            `;
            cont.appendChild(card);
        }
    }

    // Cargar preguntas y rompecabezas existentes
    function cargarDatosLibro(id_libro) {
        if (!id_libro || id_libro == 0) return;
        // Preguntas
        fetch(baseUrl + 'Quiz/obtenerPreguntas?id_libro=' + id_libro)
            .then(r => r.json())
            .then(preguntas => {
                // reset UI
                generarPreguntasUI();
                // rellenar
                preguntas.forEach(p => {
                    const num = p.numero_pregunta;
                    const textoEl = document.querySelector('.pregunta-texto[data-num="' + num + '"]');
                    if (textoEl) textoEl.value = p.texto_pregunta;
                    // limpiar opciones actuales en DB no necesarias aquí; mostraremos opciones existentes
                    if (p.opciones && p.opciones.length) {
                        p.opciones.forEach((op, idx) => {
                            const orden = op.orden || (idx+1);
                            const opEl = document.querySelector('.opcion-texto[data-num="' + num + '"][data-orden="' + orden + '"]');
                            if (opEl) opEl.value = op.texto_opcion;
                            // marcar correcta
                            if (op.es_correcta == 1) {
                                const radio = document.querySelector('.opcion-correcta[data-num="' + num + '"][data-orden="' + orden + '"]');
                                if (radio) radio.checked = true;
                            }
                        });
                    }
                });

                document.getElementById('preguntasPanel').style.display = 'block';
            })
            .catch(err => {
                console.error('Error al obtener preguntas', err);
                generarPreguntasUI();
                document.getElementById('preguntasPanel').style.display = 'block';
            });

        // Rompecabezas
        fetch(baseUrl + 'Quiz/obtenerRompecabezas?id_libro=' + id_libro)
            .then(r => r.json())
            .then(data => {
                if (!data || !data.id) {
                    document.getElementById('tituloRom').value = '';
                    document.getElementById('instruccionesRom').value = '';
                    document.getElementById('piezasList').innerHTML = '';
                    document.getElementById('rompecabezasPanel').style.display = 'block';
                    return;
                }
                document.getElementById('tituloRom').value = data.titulo || '';
                document.getElementById('instruccionesRom').value = data.instrucciones || '';
                const piezas = data.piezas || [];
                const cont = document.getElementById('piezasList');
                cont.innerHTML = '';
                piezas.forEach(p => {
                    const div = document.createElement('div');
                    div.className = 'border p-2 mb-1 d-flex justify-content-between align-items-center';
                    div.innerHTML = `<div>${p.posicion_correcta}. ${p.texto_pieza}</div>`;
                    cont.appendChild(div);
                });
                document.getElementById('rompecabezasPanel').style.display = 'block';
            })
            .catch(err => {
                console.error('Error al obtener rompecabezas', err);
                document.getElementById('rompecabezasPanel').style.display = 'block';
            });
    }

    // Guardar preguntas -> guardamos pregunta por pregunta y sus opciones
    async function guardarTodasPreguntas() {
        const id_libro = document.getElementById('selectLibro').value;
        if (!id_libro || id_libro == 0) { Swal.fire('Atención','Seleccione un libro','warning'); return; }

        for (let i=1;i<=5;i++) {
            const texto = document.querySelector('.pregunta-texto[data-num="' + i + '"]').value.trim();
            if (!texto) continue; // omitir preguntas vacías

            const fd = new FormData();
            fd.append('id_libro', id_libro);
            fd.append('texto', texto);
            fd.append('numero', i);
            fd.append('tipo', 'multiple_choice');

            const res = await fetch(baseUrl + 'Quiz/guardarPregunta', { method: 'POST', body: fd });
            const j = await res.json();
            if (j.id) {
                // Limpiar opciones anteriores para evitar duplicados
                try {
                    const fdClear = new FormData();
                    fdClear.append('id_pregunta', j.id);
                    await fetch(baseUrl + 'Quiz/limpiarOpciones', { method: 'POST', body: fdClear });
                } catch (e) {
                    console.warn('No se pudo limpiar opciones anteriores', e);
                }
                // Guardar opciones actuales
                const opciones = document.querySelectorAll('.opcion-texto[data-num="' + i + '"]');
                for (let opEl of opciones) {
                    const orden = opEl.getAttribute('data-orden');
                    const textoOp = opEl.value.trim();
                    if (!textoOp) continue;
                    const esCorr = document.querySelector('.opcion-correcta[data-num="' + i + '"][data-orden="' + orden + '"]').checked ? 1 : 0;
                    const fd2 = new FormData();
                    fd2.append('id_pregunta', j.id);
                    fd2.append('texto', textoOp);
                    fd2.append('es_correcta', esCorr);
                    fd2.append('orden', orden);
                    await fetch(baseUrl + 'Quiz/guardarOpcion', { method: 'POST', body: fd2 });
                }
            }
        }

        Swal.fire('Listo','Preguntas guardadas','success');
    }

    // Agregar pieza a la lista local
    function agregarPiezaLista() {
        const texto = document.getElementById('textoPieza').value.trim();
        const pos = parseInt(document.getElementById('posicionPieza').value,10) || 0;
        if (!texto || pos <= 0) { Swal.fire('Atención','Ingrese texto y posición válidos','warning'); return; }
        const cont = document.getElementById('piezasList');
        const div = document.createElement('div');
        div.className = 'border p-2 mb-1 d-flex justify-content-between align-items-center';
        div.dataset.pos = pos;
        div.innerHTML = `<div>${pos}. ${texto}</div><div><button class="btn btn-sm btn-danger btn-eliminar-pieza">Eliminar</button></div>`;
        cont.appendChild(div);
        document.getElementById('textoPieza').value = '';
        document.getElementById('posicionPieza').value = '';
    }

    async function guardarRompecabezas() {
        const id_libro = document.getElementById('selectLibro').value;
        if (!id_libro || id_libro==0) { Swal.fire('Atención','Seleccione un libro','warning'); return; }
        const titulo = document.getElementById('tituloRom').value.trim();
        const instr = document.getElementById('instruccionesRom').value.trim();
        if (!titulo) { Swal.fire('Atención','Ingrese título del rompecabezas','warning'); return; }

        const fd = new FormData();
        fd.append('id_libro', id_libro);
        fd.append('titulo', titulo);
        fd.append('instrucciones', instr);
        const res = await fetch(baseUrl + 'Quiz/guardarRompecabezas', { method: 'POST', body: fd });
        const j = await res.json();
        if (j.id) {
            // guardar piezas
            const piezas = Array.from(document.querySelectorAll('#piezasList > div'));
            for (let p of piezas) {
                const texto = p.querySelector('div').textContent.replace(/^\d+\.\s*/,'').trim();
                const pos = p.dataset.pos || 0;
                const fd2 = new FormData();
                fd2.append('id_rompecabezas', j.id);
                fd2.append('texto', texto);
                fd2.append('posicion', pos);
                fd2.append('orden', pos);
                await fetch(baseUrl + 'Quiz/guardarPieza', { method: 'POST', body: fd2 });
            }
            Swal.fire('Listo','Rompecabezas y piezas guardadas','success');
        } else {
            Swal.fire('Error','No se pudo guardar rompecabezas','error');
        }
    }

    document.addEventListener('DOMContentLoaded', function(){
        // Intentamos inicializar Select2 buscable; si falla, usamos el cargado completo de libros
        const ok = initSelect2Buscable();
        if (!ok) cargarLibros();
        generarPreguntasUI();

        document.getElementById('btnCargar').addEventListener('click', function(){
            const id = document.getElementById('selectLibro').value;
            if (!id || id==0) { Swal.fire('Atención','Seleccione un libro','warning'); return; }
            cargarDatosLibro(id);
        });

        document.getElementById('btnGuardarTodasPreguntas').addEventListener('click', guardarTodasPreguntas);
        document.getElementById('btnAgregarPieza').addEventListener('click', agregarPiezaLista);
        document.getElementById('piezasList').addEventListener('click', function(e){
            if (e.target.classList.contains('btn-eliminar-pieza')) {
                e.target.closest('div').remove();
            }
        });
        document.getElementById('btnGuardarRompecabezas').addEventListener('click', guardarRompecabezas);
    });
</script>

<?php include "Views/Templates/footer.php"; ?>