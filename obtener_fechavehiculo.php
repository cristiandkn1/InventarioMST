<?php
include 'db.php';

// Asegurar que la solicitud sea POST y que se haya enviado el ID
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['idvehiculo'])) {
    $idvehiculo = intval($_POST['idvehiculo']); // ✅ Usamos 'idvehiculo' en lugar de 'id'

    // Depuración: Imprimir el ID recibido en el log del servidor
    error_log("ID recibido en obtener_fechavehiculo.php: " . $idvehiculo);

    if ($idvehiculo <= 0) {
        echo json_encode(["status" => "error", "message" => "ID inválido."]);
        exit;
    }

    // Consulta SQL para obtener los datos del vehículo
    $sql = "SELECT idvehiculo, nombre, marca, modelo, anio, precio, revision_tecnica, 
                   permiso_circulacion, estado, descripcion, fecha_registro, img, 
                   ultima_mantencion, permiso_inicio, permiso_fin, revision_inicio, revision_fin 
            FROM vehiculo WHERE idvehiculo = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Error en la preparación de la consulta."]);
        exit;
    }

    $stmt->bind_param("i", $idvehiculo); // ✅ Ahora usamos 'idvehiculo'
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $vehiculo = $result->fetch_assoc();
        echo json_encode(["status" => "success", "vehiculo" => $vehiculo]);
    } else {
        echo json_encode(["status" => "error", "message" => "Vehículo no encontrado."]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "ID no proporcionado o método incorrecto."]);
}
?>
