<?php
include 'db.php';

$producto_id = 8; // Cambia por un ID válido de tu base de datos
$cantidad_a_liberar = 2;

echo "<h3>Test: Liberar $cantidad_a_liberar unidades del producto ID $producto_id</h3>";

// Leer valores actuales
$sql_estado = "SELECT cantidad, en_uso FROM producto WHERE idproducto = ?";
$stmt = $conn->prepare($sql_estado);
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$result = $stmt->get_result();
$datos = $result->fetch_assoc();

if (!$datos) {
    die("❌ Producto no encontrado.");
}

echo "<p><strong>ANTES:</strong> Total: {$datos['cantidad']}, En Uso: {$datos['en_uso']}</p>";

// Ejecutar liberación
$sql_update = "UPDATE producto SET en_uso = GREATEST(0, en_uso - ?) WHERE idproducto = ?";
$stmt = $conn->prepare($sql_update);
$stmt->bind_param("ii", $cantidad_a_liberar, $producto_id);
$stmt->execute();

if ($stmt->errno) {
    die("❌ Error en SQL: " . $stmt->error);
}

echo "<p>✅ Se ejecutó el UPDATE. Filas afectadas: " . $stmt->affected_rows . "</p>";

// Leer valores actualizados
$stmt = $conn->prepare($sql_estado);
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$result = $stmt->get_result();
$datos_despues = $result->fetch_assoc();

echo "<p><strong>DESPUÉS:</strong> Total: {$datos_despues['cantidad']}, En Uso: {$datos_despues['en_uso']}</p>";

$conn->close();
?>
