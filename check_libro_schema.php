<?php
require_once 'Config/Config.php';
require_once 'Config/App/Conexion.php';

$con = new Conexion();
$pdo = $con->conect();

$stmt = $pdo->prepare('SHOW COLUMNS FROM libro');
$stmt->execute();
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== SCHEMA: libro ===\n";
foreach ($cols as $c) {
    echo $c['Field'] . " | " . $c['Type'] . "\n";
}
?>
