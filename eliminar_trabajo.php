<?php
include 'db.php';
session_start();
date_default_timezone_set('America/Santiago');

// ✅ Verificar si se recibió el ID del trabajo
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('Error: ID de trabajo no válido.'); window.location.href = 'trabajos.php';</script>";
    exit();
}

$trabajo_id = intval($_GET['id']);

// ✅ Restaurar stock en `producto` antes de eliminar los materiales del trabajo
$sql_get_materiales = "SELECT producto_id, cantidad FROM trabajo_producto WHERE trabajo_id = ?";
$stmt_get_materiales = $conn->prepare($sql_get_materiales);
$stmt_get_materiales->bind_param("i", $trabajo_id);
$stmt_get_materiales->execute();
$result_materiales = $stmt_get_materiales->get_result();

while ($material = $result_materiales->fetch_assoc()) {
    $id_producto = $material['producto_id'];
    $cantidad_usada = $material['cantidad'];

    // 🔹 Devolver la cantidad utilizada a `producto`
    $sql_update_stock = "UPDATE producto SET en_uso = en_uso - ? WHERE idproducto = ?";
    $stmt_update_stock = $conn->prepare($sql_update_stock);
    $stmt_update_stock->bind_param("ii", $cantidad_usada, $id_producto);
    $stmt_update_stock->execute();
    $stmt_update_stock->close();
}

$stmt_get_materiales->close();

// ✅ Eliminar los materiales asignados en `trabajo_producto`
$sql_eliminar_materiales = "DELETE FROM trabajo_producto WHERE trabajo_id = ?";
$stmt_materiales = $conn->prepare($sql_eliminar_materiales);
$stmt_materiales->bind_param("i", $trabajo_id);
$stmt_materiales->execute();
$stmt_materiales->close();

// ✅ Eliminar las operaciones asignadas en `operaciones`
$sql_eliminar_operaciones = "DELETE FROM operaciones WHERE trabajo_id = ?";
$stmt_operaciones = $conn->prepare($sql_eliminar_operaciones);
$stmt_operaciones->bind_param("i", $trabajo_id);
$stmt_operaciones->execute();
$stmt_operaciones->close();

// ✅ Finalmente, eliminar el trabajo en `trabajo`
$sql_eliminar_trabajo = "DELETE FROM trabajo WHERE idtrabajo = ?";
$stmt_trabajo = $conn->prepare($sql_eliminar_trabajo);
$stmt_trabajo->bind_param("i", $trabajo_id);

$sql_info_trabajo = "
    SELECT t.titulo, c.idcliente, c.empresa 
    FROM trabajo t 
    JOIN cliente c ON t.cliente_id = c.idcliente 
    WHERE t.idtrabajo = ?
";

$stmt_info_trabajo = $conn->prepare($sql_info_trabajo);

if (!$stmt_info_trabajo) {
    die("❌ Error al preparar la consulta: " . $conn->error);
}

$stmt_info_trabajo->bind_param("i", $trabajo_id);
$stmt_info_trabajo->execute();
$result_info_trabajo = $stmt_info_trabajo->get_result();
$trabajo_info = $result_info_trabajo->fetch_assoc();
$stmt_info_trabajo->close();

// ✅ Ejecutar eliminación
if ($stmt_trabajo->execute()) {
    // 🔹 Datos para el historial
    $usuario_id = $_SESSION['usuario_id'] ?? 0;
    $accion = "Eliminación";
    $entidad = "trabajo"; // 👈 ahora correcto para tu CASE
    $entidad_id = $trabajo_id;

    // 👇 Aquí mostramos ID del cliente y nombre de la empresa
    $cliente_id = $trabajo_info['idcliente'];
    $empresa_nombre = $trabajo_info['empresa'];
    $detalle = "Se eliminó el trabajo '{$trabajo_info['titulo']}' (ID: $trabajo_id) de la empresa '{$cliente_id} - {$empresa_nombre}'";

    $fecha = date("Y-m-d H:i:s");

    // ✅ Insertar en el historial
    $sql_insert_historial = "INSERT INTO historial (usuario_id, accion, entidad, entidad_id, detalle, fecha) 
                             VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_historial = $conn->prepare($sql_insert_historial);
    $stmt_historial->bind_param("ississ", $usuario_id, $accion, $entidad, $entidad_id, $detalle, $fecha);
    $stmt_historial->execute();
    $stmt_historial->close();

    // ✅ Redirigir después de la eliminación
    echo "<script>
            alert('✅ Trabajo eliminado correctamente.');
            window.location.href = 'trabajos.php';
          </script>";
} else {
    echo "❌ Error al eliminar el trabajo: " . $stmt_trabajo->error;
}

// ✅ Cerrar conexiones
$stmt_trabajo->close();
$conn->close();
