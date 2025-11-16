<?php include "Views/Templates/header.php"; ?>

<style>
    .solicitud-card {
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .solicitud-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }

    .solicitud-card.pendiente {
        border-left: 5px solid #ffc107;
        background-color: #fffbf0;
    }

    .solicitud-card.aprobada {
        border-left: 5px solid #28a745;
        background-color: #f0fdf4;
    }

    .solicitud-card.rechazada {
        border-left: 5px solid #dc3545;
        background-color: #fef2f2;
    }

    .solicitud-card.devuelto {
        border-left: 5px solid #6c757d;
        background-color: #f8f9fa;
    }

    .solicitud-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        gap: 15px;
    }

    .solicitud-cover {
        width: 100px;
        height: 140px;
        object-fit: cover;
        border-radius: 5px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .solicitud-info {
        flex: 1;
    }

    .solicitud-titulo {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }

    .solicitud-autor {
        font-size: 14px;
        color: #666;
        margin-bottom: 10px;
    }

    .solicitud-estado-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .estado-pendiente {
        background-color: #ffc107;
        color: #333;
    }

    .estado-aprobada {
        background-color: #28a745;
        color: white;
    }

    .estado-rechazada {
        background-color: #dc3545;
        color: white;
    }

    .estado-devuelto {
        background-color: #6c757d;
        color: white;
    }

    .solicitud-detalles {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
        margin-top: 12px;
        font-size: 13px;
    }

    .detalle-item {
        display: flex;
        flex-direction: column;
    }

    .detalle-label {
        color: #999;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
    }

    .detalle-valor {
        color: #333;
        font-weight: 500;
        margin-top: 3px;
    }

    .solicitud-observacion {
        margin-top: 12px;
        padding: 10px;
        background-color: rgba(0,0,0,0.03);
        border-left: 3px solid #999;
        border-radius: 3px;
        font-size: 13px;
        color: #666;
        font-style: italic;
    }

    .sin-solicitudes {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }

    .sin-solicitudes-icono {
        font-size: 48px;
        color: #ddd;
        margin-bottom: 15px;
    }

    .titulo-rechazo {
        color: #dc3545;
        font-weight: 600;
    }

    .motivo-rechazo {
        background-color: #fff5f5;
        border-left: 3px solid #dc3545;
        padding: 10px;
        margin-top: 8px;
        border-radius: 3px;
        color: #dc3545;
        font-size: 13px;
    }
</style>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fa fa-clipboard-list"></i> Mis Solicitudes de Préstamo</h2>
            <p class="text-muted">Revisa el estado de tus solicitudes de préstamos de libros</p>
        </div>
    </div>

    <div id="contenedorSolicitudes" class="row">
        <!-- Las tarjetas se cargarán aquí -->
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <a href="<?php echo base_url; ?>Catalogo" class="btn btn-primary">
                <i class="fa fa-arrow-left"></i> Volver al Catálogo
            </a>
        </div>
    </div>
</div>

<script>
    const baseUrl = '<?php echo base_url; ?>';

    function getEstadoClase(estado) {
        switch(estado) {
            case 0: return 'pendiente';
            case 1: return 'aprobada';
            case 2: return 'devuelto';
            default: return 'pendiente';
        }
    }

    function getEstadoTexto(estado) {
        switch(estado) {
            case 0: return 'Solicitud Pendiente';
            case 1: return 'Préstamo Activo';
            case 2: return 'Devuelto';
            default: return 'Desconocido';
        }
    }

    function getEstadoBadgeClase(estado) {
        switch(estado) {
            case 0: return 'estado-pendiente';
            case 1: return 'estado-aprobada';
            case 2: return 'estado-devuelto';
            default: return 'estado-pendiente';
        }
    }

    function cargarMisSolicitudes() {
        fetch(baseUrl + 'Catalogo/misSolicitudes')
            .then(res => res.json())
            .then(data => {
                const contenedor = document.getElementById('contenedorSolicitudes');
                contenedor.innerHTML = '';

                if (!data || data.length === 0) {
                    contenedor.innerHTML = `
                        <div class="col-12">
                            <div class="sin-solicitudes">
                                <div class="sin-solicitudes-icono">
                                    <i class="fa fa-inbox"></i>
                                </div>
                                <h4>No tienes solicitudes aún</h4>
                                <p>Dirígete al catálogo para solicitar préstamos de libros</p>
                            </div>
                        </div>
                    `;
                    return;
                }

                data.forEach(sol => {
                    const imagenSrc = sol.imagen && sol.imagen.trim() !== '' 
                        ? `${baseUrl}Assets/img/libros/${sol.imagen}` 
                        : `${baseUrl}Assets/img/libros/default.png`;
                    
                    const estadoClase = getEstadoClase(sol.estado);
                    const estadoTexto = getEstadoTexto(sol.estado);
                    const estadoBadgeClase = getEstadoBadgeClase(sol.estado);

                    const card = `
                        <div class="col-md-6 col-lg-4">
                            <div class="solicitud-card ${estadoClase}">
                                <div class="solicitud-header">
                                    <img src="${imagenSrc}" alt="${sol.titulo}" class="solicitud-cover" onerror="this.src='${baseUrl}Assets/img/libros/default.png'">
                                    <div class="solicitud-info">
                                        <div class="solicitud-titulo">${sol.titulo}</div>
                                        <div class="solicitud-autor"><strong>Autor:</strong> ${sol.autor || 'Desconocido'}</div>
                                        <span class="solicitud-estado-badge ${estadoBadgeClase}">${estadoTexto}</span>
                                        
                                        <div class="solicitud-detalles">
                                            <div class="detalle-item">
                                                <span class="detalle-label">Cantidad</span>
                                                <span class="detalle-valor">${sol.cantidad}</span>
                                            </div>
                                            <div class="detalle-item">
                                                <span class="detalle-label">Solicitud</span>
                                                <span class="detalle-valor">${sol.fecha_prestamo}</span>
                                            </div>
                                            ${sol.estado === 1 ? `
                                            <div class="detalle-item">
                                                <span class="detalle-label">Devolución</span>
                                                <span class="detalle-valor">${sol.fecha_devolucion}</span>
                                            </div>
                                            ` : ''}
                                        </div>

                                        ${sol.observacion ? `
                                        <div class="solicitud-observacion">
                                            <strong>Observación:</strong> ${sol.observacion}
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    contenedor.innerHTML += card;
                });
            })
            .catch(err => {
                console.error('Error cargando solicitudes:', err);
                document.getElementById('contenedorSolicitudes').innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger" role="alert">
                            <i class="fa fa-exclamation-circle"></i> Error al cargar solicitudes. Intenta de nuevo.
                        </div>
                    </div>
                `;
            });
    }

    document.addEventListener('DOMContentLoaded', cargarMisSolicitudes);
</script>

<?php include "Views/Templates/footer.php"; ?>
