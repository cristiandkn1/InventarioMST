<?php
session_start();
include 'db.php';
date_default_timezone_set('America/Santiago');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ubicacion_destino']) && !empty($_POST['activos'])) {
    $ubicacion_id = intval($_POST['ubicacion_destino']);
    $fecha = date("Y-m-d H:i:s");
    $usuario_id = $_SESSION['usuario_id'] ?? 0;

    // Crear nuevo envío
    $stmt_envio = $conn->prepare("INSERT INTO envio (fecha, ubicacion_id, devuelto) VALUES (?, ?, 0)");
    $stmt_envio->bind_param("si", $fecha, $ubicacion_id);
    $stmt_envio->execute();
    $envio_id = $stmt_envio->insert_id;
    $stmt_envio->close();

    // Insertar detalles y actualizar stock
    foreach ($_POST['activos'] as $activo) {
        $activo_id = intval($activo['id'] ?? 0);
        $cantidad = intval($activo['cantidad'] ?? 0);

        if ($activo_id > 0 && $cantidad > 0) {
            // Insertar detalle del envío
            $stmt_detalle = $conn->prepare("INSERT INTO envio_detalle (envio_id, activo_id, cantidad_enviada) VALUES (?, ?, ?)");
            $stmt_detalle->bind_param("iii", $envio_id, $activo_id, $cantidad);
            $stmt_detalle->execute();
            $stmt_detalle->close();

            // Descontar stock del activo
            $stmt_stock = $conn->prepare("UPDATE activos SET cantidad = cantidad - ? WHERE idactivo = ?");
            $stmt_stock->bind_param("ii", $cantidad, $activo_id);
            $stmt_stock->execute();
            $stmt_stock->close();
        }
    }

    $_SESSION['mensaje'] = [
        'tipo' => 'success',
        'titulo' => 'Envío registrado',
        'texto' => 'Los activos fueron enviados correctamente.'
    ];
    header("Location: activos.php");
    exit;
} else {
    $_SESSION['mensaje'] = [
        'tipo' => 'error',
        'titulo' => 'Error',
        'texto' => 'Faltan datos para procesar el envío.'
    ];
    header("Location: activos.php");
    exit;
}
