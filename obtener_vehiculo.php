<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['idvehiculo'])) {
    $vehiculo_id = intval($_POST['idvehiculo']);

    if ($vehiculo_id <= 0) {
        echo json_encode(["status" => "error", "message" => "ID inválido."]);
        exit();
    }

    // 📌 Depuración
    error_log("ID recibido en obtener_vehiculo.php: " . $vehiculo_id);

    $sql = "SELECT idvehiculo, nombre, patente, marca, modelo, anio, precio, estado, descripcion, 
        permiso_inicio, permiso_fin, revision_inicio, revision_fin, 
        fecha_cambio_aceite, vencimiento_cambio_aceite, ultima_mantencion, fecha_registro, img, 
        permiso_circulacion, revision_tecnica 
        FROM vehiculo WHERE idvehiculo = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Error en la consulta SQL: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("i", $vehiculo_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $vehiculo = $result->fetch_assoc();

        $directorio = "img/";

        // ✅ Imagen
        if (!empty($vehiculo['img'])) {
            if (!str_starts_with($vehiculo['img'], $directorio)) {
                $vehiculo['img'] = $directorio . $vehiculo['img'];
            }
        } else {
            $vehiculo['img'] = $directorio . "no-image.png";
        }

        // ✅ Documento permiso de circulación
        if (!empty($vehiculo['permiso_circulacion'])) {
            if (!str_starts_with($vehiculo['permiso_circulacion'], $directorio)) {
                $vehiculo['permiso_circulacion'] = $directorio . $vehiculo['permiso_circulacion'];
            }
        } else {
            $vehiculo['permiso_circulacion'] = null; // Si no hay documento, aseguramos que no devuelva una ruta inválida
        }

        // ✅ Documento revisión técnica
        if (!empty($vehiculo['revision_tecnica'])) {
            if (!str_starts_with($vehiculo['revision_tecnica'], $directorio)) {
                $vehiculo['revision_tecnica'] = $directorio . $vehiculo['revision_tecnica'];
            }
        } else {
            $vehiculo['revision_tecnica'] = null;
        }

        // ✅ Estado del vehículo: Si está vacío, asignamos un valor por defecto
        if (empty($vehiculo['estado'])) {
            $vehiculo['estado'] = "Activo"; // Valor por defecto si no hay estado registrado
        }

        // 🔍 Depuración
        error_log("Imagen final enviada: " . $vehiculo['img']);
        error_log("Estado del vehículo enviado: " . $vehiculo['estado']);

        header('Content-Type: application/json');
        echo json_encode([
            "status" => "success",
            "vehiculo" => $vehiculo
        ], JSON_PRETTY_PRINT);
    } else {
        echo json_encode(["status" => "error", "message" => "Vehículo no encontrado en la base de datos."]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "ID no proporcionado o método incorrecto."]);
}
?>
