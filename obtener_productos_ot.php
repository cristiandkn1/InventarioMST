<?php
include 'db.php';

$trabajo_id = intval($_GET['trabajo_id']);

$sql = "SELECT p.nombre, p.descripcion, tp.cantidad, p.precio 
        FROM trabajos_productos tp
        INNER JOIN producto p ON tp.producto_id = p.idproducto
        WHERE tp.trabajo_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $trabajo_id);
$stmt->execute();
$result = $stmt->get_result();

$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($productos);
