<?php
include 'db.php';
header('Content-Type: application/json');

$id = $_POST['idactivo'] ?? 0;

// Validación simple
if (!is_numeric($id) || $id <= 0) {
    echo json_encode(["error" => "ID inválido."], JSON_PRETTY_PRINT);
    exit;
}

$sql = "SELECT a.*, 
               c.nombre AS categoria, 
               u.nombre AS ubicacion 
        FROM activos a
        LEFT JOIN categoria c ON a.idcategoria = c.idcategoria
LEFT JOIN ubicaciones u ON a.idubicacion = u.idubicacion
        WHERE a.idactivo = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["error" => "Error al preparar la consulta: " . $conn->error], JSON_PRETTY_PRINT);
    exit;
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row, JSON_PRETTY_PRINT);
} else {
    echo json_encode(["error" => "Activo no encontrado."], JSON_PRETTY_PRINT);
}

$stmt->close();
$conn->close();
?>
