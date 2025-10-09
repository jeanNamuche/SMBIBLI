<?php include "Views/Templates/header.php"; ?>
<div class="app-title">
    <div>
        <h1><i class="fa fa-book"></i> Catálogo de Libros</h1>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="tile">
            <div class="tile-body">
                <div class="table-responsive">
                    <table class="table table-light mt-4" id="tblCatalogo">
                        <thead class="thead-dark">
                            <tr>
                                <th>Título</th>
                                <th>Autor</th>
                                <th>Editorial</th>
                                <th>Materia</th>
                                <th>Foto</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th>PDF</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($data)) {
                            foreach ($data as $libro) { ?>
                                <tr>
                                    <td><?php echo $libro['titulo']; ?></td>
                                    <td><?php echo $libro['autor']; ?></td>
                                    <td><?php echo $libro['editorial']; ?></td>
                                    <td><?php echo $libro['materia']; ?></td>
                                    <td><img class="img-thumbnail" src="<?php echo base_url . 'Assets/img/libros/' . $libro['imagen']; ?>" width="80"></td>
                                    <td><?php echo $libro['descripcion']; ?></td>
                                    <td><?php echo $libro['cantidad']; ?></td>
                                    <td>
                                        <?php if (!empty($libro['pdf_path'])) { ?>
                                            <a href="<?php echo base_url . $libro['pdf_path']; ?>" target="_blank" class="btn btn-sm btn-info">Ver PDF</a>
                                        <?php } else { ?>
                                            <span class="text-muted">No disponible</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                        <?php }
                        } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "Views/Templates/footer.php"; ?>