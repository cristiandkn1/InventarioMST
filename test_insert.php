<?php
include 'db.php';

$descripcion = "PRUEBA 456abc 😎";
$nombre = "Producto test";
$precio = 9999.99;
$estado = "Excelente";
$categoria_id = 1;
$cantidad = 10;
$en_uso = 0;
$disponibles = 10;
$nro_asignacion = "TEST-123";

// PRUEBA DIRECTA
$sql = "INSERT INTO producto (nombre, precio, estado, categoria_id, descripcion, cantidad, en_uso, disponibles, nro_asignacion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sdsssisii", 
    $nombre,
    $precio,
    $estado,
    $categoria_id,
    $descripcion,   // 👈 Asegurado como string
    $cantidad,
    $en_uso,
    $disponibles,
    $nro_asignacion
);

if ($stmt->execute()) {
    echo "✅ Insertado correctamente. ID: " . $stmt->insert_id;
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
