<?php
include 'db.php';
session_start();

$categoria = $_GET['categoria'] ?? '';

$sql = "SELECT COUNT(*) AS productos_unicos, SUM(cantidad) AS total_cantidades, SUM(precio * cantidad) AS total_valor
        FROM producto WHERE eliminado = 0";

if (!empty($categoria)) {
    $sql .= " AND categoria_id = (SELECT idcategoria FROM categoria WHERE nombre = ? LIMIT 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $categoria);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'unicos' => intval($result['productos_unicos']),
    'cantidad' => intval($result['total_cantidades']),
    'valor' => '$' . number_format($result['total_valor'] ?? 0, 0, ',', '.')
]);
