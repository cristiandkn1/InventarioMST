<?php
include 'db.php';

// Obtener el término de búsqueda ingresado por el usuario
$search = $_GET['q'] ?? '';

// Consulta para obtener productos de la base de datos
$sql = "SELECT idproducto, nombre FROM producto WHERE nombre LIKE ? ORDER BY nombre ASC";
$stmt = $conn->prepare($sql);
$searchTerm = "%$search%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = ["id" => $row["idproducto"], "text" => $row["nombre"]];
}

// Enviar la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($productos);

$stmt->close();
$conn->close();
?>
