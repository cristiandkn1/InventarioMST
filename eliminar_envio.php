<?php
session_start();
include 'db.php';
date_default_timezone_set('America/Santiago');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idenvio'])) {
    $idenvio = intval($_POST['idenvio']);
    $usuario_id = $_SESSION['usuario_id'] ?? 0;
    $fecha = date("Y-m-d H:i:s");

    // Validar que el envío existe y obtener información
    $check = $conn->prepare("SELECT e.idenvio, e.fecha, u.nombre AS ubicacion, e.devuelto
                             FROM envio e
                             JOIN ubicaciones u ON e.ubicacion_id = u.idubicacion
                             WHERE e.idenvio = ?");
    $check->bind_param("i", $idenvio);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo "❌ El envío no existe.";
        exit;
    }

    $info_envio = $result->fetch_assoc();
    $check->close();

    // ✅ Si la devolución está pendiente (devuelto = 0), se devuelve el stock
    if ((int)$info_envio['devuelto'] === 0) {
        $sql_detalles = "SELECT activo_id, cantidad_enviada FROM envio_detalle WHERE envio_id = ?";
        $stmt_det = $conn->prepare($sql_detalles);
        $stmt_det->bind_param("i", $idenvio);
        $stmt_det->execute();
        $result_det = $stmt_det->get_result();

        while ($row = $result_det->fetch_assoc()) {
            $id_activo = $row['activo_id'];
            $cantidad = $row['cantidad_enviada'];

            // Devolver stock
            $stmt_update = $conn->prepare("UPDATE activos SET cantidad = cantidad + ? WHERE idactivo = ?");
            $stmt_update->bind_param("ii", $cantidad, $id_activo);
            $stmt_update->execute();
            $stmt_update->close();
        }

        $stmt_det->close();
    }
// Eliminar activos usados/perdidos asociados (si existen)
$deleteUsados = $conn->prepare("DELETE FROM activos_usados WHERE envio_id = ?");
$deleteUsados->bind_param("i", $idenvio);
$deleteUsados->execute();
$deleteUsados->close();

    // Eliminar devoluciones asociadas (si existen)
$deleteDevoluciones = $conn->prepare("DELETE FROM envio_devolucion WHERE envio_id = ?");
$deleteDevoluciones->bind_param("i", $idenvio);
$deleteDevoluciones->execute();
$deleteDevoluciones->close();
    // Eliminar detalles
    $deleteDetalle = $conn->prepare("DELETE FROM envio_detalle WHERE envio_id = ?");
    $deleteDetalle->bind_param("i", $idenvio);
    $deleteDetalle->execute();
    $deleteDetalle->close();

    // Eliminar envío
    $deleteEnvio = $conn->prepare("DELETE FROM envio WHERE idenvio = ?");
    $deleteEnvio->bind_param("i", $idenvio);

    if ($deleteEnvio->execute()) {
        // Registrar en historial
        $accion = "Eliminación";
        $entidad = "envio";
        $entidad_id = $idenvio;
        $detalle = "Se eliminó el envío #{$idenvio} a la ubicación '{$info_envio['ubicacion']}' con fecha {$info_envio['fecha']}";

        $historial = $conn->prepare("INSERT INTO historial (usuario_id, accion, entidad, entidad_id, detalle, fecha)
                                     VALUES (?, ?, ?, ?, ?, ?)");
        $historial->bind_param("ississ", $usuario_id, $accion, $entidad, $entidad_id, $detalle, $fecha);
        $historial->execute();
        $historial->close();

        echo "✅ Envío eliminado correctamente.";
    } else {
        http_response_code(500);
        echo "❌ Error al eliminar el envío: " . $conn->error;
    }

    $deleteEnvio->close();
    $conn->close();
} else {
    http_response_code(400);
    echo "❌ Solicitud inválida.";
}
