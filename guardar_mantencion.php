<?php
include 'db.php';
date_default_timezone_set('America/Santiago');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['idvehiculo']);
    $fecha = $_POST['ultima_mantencion'] ?? null;

    if (!$id || !$fecha) {
        echo json_encode(["status" => "error", "message" => "Datos inválidos."]);
        exit;
    }

    // ✅ Obtener nombre del vehículo antes de actualizar
    $query_nombre = $conn->prepare("SELECT nombre FROM vehiculo WHERE idvehiculo = ?");
    $query_nombre->bind_param("i", $id);
    $query_nombre->execute();
    $resultado = $query_nombre->get_result();
    $vehiculo = $resultado->fetch_assoc();
    $nombre_vehiculo = $vehiculo['nombre'] ?? '(desconocido)';
    $query_nombre->close();

    // ✅ Actualizar fecha de última mantención
    $sql = "UPDATE vehiculo SET ultima_mantencion = ? WHERE idvehiculo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $fecha, $id);

    if ($stmt->execute()) {
        // ✅ Guardar en historial
        $fecha_hora = date("Y-m-d H:i:s");
        $accion = "Mantención Registrada";
        $detalle = "Se registró mantención para el vehículo \"$nombre_vehiculo\" (ID: $id), Fecha: " . date("d-m-Y", strtotime($fecha));
        $usuario_id = 1; // Puedes ajustarlo dinámicamente según el sistema de sesión

        $sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                          VALUES (?, ?, 'vehiculo', ?, ?, ?)";
        $stmt_historial = $conn->prepare($sql_historial);
        $stmt_historial->bind_param("ssisi", $fecha_hora, $accion, $id, $detalle, $usuario_id);
        $stmt_historial->execute();
        $stmt_historial->close();

        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al guardar: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>