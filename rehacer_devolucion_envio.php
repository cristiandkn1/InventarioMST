<?php
session_start();
include 'db.php';
date_default_timezone_set('America/Santiago');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['envio_id'])) {
    $envio_id = intval($_POST['envio_id']);

    // Obtener las devoluciones para restar del stock
    $sql = "SELECT activo_id, cantidad_devuelta FROM envio_devolucion WHERE envio_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $envio_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $activo_id = $row['activo_id'];
        $cantidad = $row['cantidad_devuelta'];

        // Restar del stock
        $stmt_update = $conn->prepare("UPDATE activos SET cantidad = cantidad - ? WHERE idactivo = ?");
        $stmt_update->bind_param("ii", $cantidad, $activo_id);
        $stmt_update->execute();
        $stmt_update->close();
    }
    $stmt->close();

    // Eliminar devoluciones registradas
    $stmt_delete = $conn->prepare("DELETE FROM envio_devolucion WHERE envio_id = ?");
    $stmt_delete->bind_param("i", $envio_id);
    $stmt_delete->execute();
    $stmt_delete->close();

    // Marcar el envío como NO devuelto
    $stmt_envio = $conn->prepare("UPDATE envio SET devuelto = 0 WHERE idenvio = ?");
    $stmt_envio->bind_param("i", $envio_id);
    $stmt_envio->execute();
    $stmt_envio->close();

    $_SESSION['mensaje'] = [
        'tipo' => 'success',
        'titulo' => 'Devolución revertida',
        'texto' => 'La devolución fue revertida correctamente. Ahora puedes volver a registrar la devolución.'
    ];
    header("Location: obtener_detalle_envio.php?id=" . $envio_id);
    exit;
} else {
    $_SESSION['mensaje'] = [
        'tipo' => 'error',
        'titulo' => 'Error',
        'texto' => 'No se pudo rehacer la devolución.'
    ];
    header("Location: activos.php");
    exit;
}
