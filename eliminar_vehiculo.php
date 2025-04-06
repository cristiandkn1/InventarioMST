<?php
include 'db.php';
header('Content-Type: application/json');
date_default_timezone_set('America/Santiago');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['idvehiculo']);

    // (Opcional) obtener nombre para historial
    $stmt_nombre = $conn->prepare("SELECT nombre FROM vehiculo WHERE idvehiculo = ?");
    $stmt_nombre->bind_param("i", $id);
    $stmt_nombre->execute();
    $resultado = $stmt_nombre->get_result();
    $vehiculo = $resultado->fetch_assoc();
    $nombre = $vehiculo['nombre'] ?? 'Vehículo Desconocido';
    $stmt_nombre->close();

    $stmt = $conn->prepare("DELETE FROM vehiculo WHERE idvehiculo = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Historial
        $fecha = date("Y-m-d H:i:s");
        $accion = "Eliminación de Vehículo";
        $detalle = "Se eliminó el vehículo \"$nombre\" (ID: $id)";
        $usuario_id = 1;

        $sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                          VALUES (?, ?, 'vehiculo', ?, ?, ?)";
        $historial = $conn->prepare($sql_historial);
        $historial->bind_param("ssisi", $fecha, $accion, $id, $detalle, $usuario_id);
        $historial->execute();
        $historial->close();

        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al eliminar: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
