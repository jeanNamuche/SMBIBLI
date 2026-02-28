<?php include "Views/Templates/header.php"; ?>
<div class="app-title">
    <div>
        <h1>Créditos</h1>
        <p class="text-muted">ENCARGADOS DEL PROYECTO DE BIBLIOTECA</p>
    </div>
</div>

<div class="tile">
    <div class="tile-body">
        <div class="credits-viewport">
            <div id="creditsContent" class="credits-content">
                <!-- Se cargan los créditos vía JS -->
                <div class="credits-item">Cargando créditos...</div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?php echo base_url; ?>Assets/css/credits.css">
<script>const baseUrl = '<?php echo base_url; ?>';</script>
<script src="<?php echo base_url; ?>Assets/js/credits.js"></script>

<?php include "Views/Templates/footer.php"; ?>
