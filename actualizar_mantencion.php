<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $fecha_registro = isset($_POST['fecha_registro']) ? $_POST['fecha_registro'] : null;
    $permiso_inicio = isset($_POST['permiso_inicio']) ? $_POST['permiso_inicio'] : null;
    $permiso_fin = isset($_POST['permiso_fin']) ? $_POST['permiso_fin'] : null;
    $revision_inicio = isset($_POST['revision_inicio']) ? $_POST['revision_inicio'] : null;
    $revision_fin = isset($_POST['revision_fin']) ? $_POST['revision_fin'] : null;
    $mantencion = !empty($_POST['ultima_mantencion']) ? $_POST['ultima_mantencion'] : null;

    // Validar que el ID sea válido
    if ($id <= 0) {
        echo json_encode(["status" => "error", "message" => "ID de vehículo inválido."]);
        exit;
    }

    // Validación de formato de fechas (YYYY-MM-DD)
    function validar_fecha($fecha) {
        return preg_match("/^\d{4}-\d{2}-\d{2}$/", $fecha);
    }

    $fechas = [$fecha_registro, $permiso_inicio, $permiso_fin, $revision_inicio, $revision_fin, $mantencion];
    foreach ($fechas as $fecha) {
        if ($fecha && !validar_fecha($fecha)) {
            echo json_encode(["status" => "error", "message" => "Formato de fecha inválido."]);
            exit;
        }
    }

    // Preparar la consulta SQL con la nueva columna fecha_registro
    $sql = "UPDATE vehiculo SET fecha_registro=?, permiso_inicio=?, permiso_fin=?, revision_inicio=?, revision_fin=?, ultima_mantencion=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Error en la preparación de la consulta."]);
        exit;
    }

    $stmt->bind_param("ssssssi", $fecha_registro, $permiso_inicio, $permiso_fin, $revision_inicio, $revision_fin, $mantencion, $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Vehículo actualizado correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al actualizar."]);
    }

    $stmt->close();
    $conn->close();
}
?>
