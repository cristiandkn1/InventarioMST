<?php
session_start();
include 'db.php';
date_default_timezone_set('America/Santiago');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['devoluciones']) && !empty($_POST['envio_id'])) {
    $envio_id = intval($_POST['envio_id']);
    $observacion = trim($_POST['observacion'] ?? '');
    $fecha = date("Y-m-d H:i:s");

    // 1. Registrar devoluciones
    foreach ($_POST['devoluciones'] as $idactivo => $cantidad_devuelta) {
        $idactivo = intval($idactivo);
        $cantidad = intval($cantidad_devuelta);

        if ($idactivo > 0 && $cantidad > 0) {
            $stmt_dev = $conn->prepare("INSERT INTO envio_devolucion (envio_id, activo_id, cantidad_devuelta, fecha, observacion) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt_dev) {
                die("Error al registrar devolución: " . $conn->error);
            }
            $stmt_dev->bind_param("iiiss", $envio_id, $idactivo, $cantidad, $fecha, $observacion);
            $stmt_dev->execute();
            $stmt_dev->close();

            // Recuperar stock
            $stmt_stock = $conn->prepare("UPDATE activos SET cantidad = cantidad + ? WHERE idactivo = ?");
            $stmt_stock->bind_param("ii", $cantidad, $idactivo);
            $stmt_stock->execute();
            $stmt_stock->close();
        }
    }

    // 2. Registrar activos usados/perdidos
    if (!empty($_POST['perdidos'])) {
        foreach ($_POST['perdidos'] as $idactivo => $value) {
            $idactivo = intval($idactivo);

            // Obtener enviado y devuelto
            $stmt_env = $conn->prepare("SELECT cantidad_enviada FROM envio_detalle WHERE envio_id = ? AND activo_id = ?");
            $stmt_env->bind_param("ii", $envio_id, $idactivo);
            $stmt_env->execute();
            $res_env = $stmt_env->get_result();
            $env = $res_env->fetch_assoc();
            $enviada = intval($env['cantidad_enviada']);
            $stmt_env->close();

            $stmt_dev = $conn->prepare("SELECT SUM(cantidad_devuelta) AS devuelta FROM envio_devolucion WHERE envio_id = ? AND activo_id = ?");
            $stmt_dev->bind_param("ii", $envio_id, $idactivo);
            $stmt_dev->execute();
            $res_dev = $stmt_dev->get_result();
            $dev = $res_dev->fetch_assoc();
            $devuelta = intval($dev['devuelta']);
            $stmt_dev->close();

            $perdida = $enviada - $devuelta;

            if ($perdida > 0) {
                $stmt_usado = $conn->prepare("INSERT INTO activos_usados (envio_id, activo_id, cantidad, fecha, observacion) VALUES (?, ?, ?, ?, ?)");
                $stmt_usado->bind_param("iiiss", $envio_id, $idactivo, $perdida, $fecha, $observacion);
                $stmt_usado->execute();
                $stmt_usado->close();
            }
        }
    }

    // 3. Verificar si el envío está completamente devuelto (devueltos + perdidos = enviados)
    $sql_check = "
        SELECT ed.activo_id, ed.cantidad_enviada,
            COALESCE(dev.total_devuelta, 0) AS total_devuelta,
            COALESCE(uso.total_usado, 0) AS total_usado
        FROM envio_detalle ed
        LEFT JOIN (
            SELECT activo_id, envio_id, SUM(cantidad_devuelta) AS total_devuelta
            FROM envio_devolucion
            WHERE envio_id = ?
            GROUP BY activo_id
        ) dev ON ed.activo_id = dev.activo_id AND ed.envio_id = dev.envio_id
        LEFT JOIN (
            SELECT activo_id, envio_id, SUM(cantidad) AS total_usado
            FROM activos_usados
            WHERE envio_id = ?
            GROUP BY activo_id
        ) uso ON ed.activo_id = uso.activo_id AND ed.envio_id = uso.envio_id
        WHERE ed.envio_id = ?
    ";

    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("iii", $envio_id, $envio_id, $envio_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    $completamente_devuelto = true;

    while ($row = $result_check->fetch_assoc()) {
        $total = $row['total_devuelta'] + $row['total_usado'];
        if ((int)$total < (int)$row['cantidad_enviada']) {
            $completamente_devuelto = false;
            break;
        }
    }
    $stmt_check->close();

    // 4. Si todo ha sido devuelto o perdido, marcar como completado
    if ($completamente_devuelto) {
        $stmt_update_envio = $conn->prepare("UPDATE envio SET devuelto = 1 WHERE idenvio = ?");
        $stmt_update_envio->bind_param("i", $envio_id);
        $stmt_update_envio->execute();
        $stmt_update_envio->close();
    }

    $_SESSION['mensaje'] = [
        'tipo' => 'success',
        'titulo' => 'Devolución registrada',
        'texto' => 'La devolución fue registrada correctamente.'
    ];
    header("Location: activos.php");
    exit;
} else {
    $_SESSION['mensaje'] = [
        'tipo' => 'error',
        'titulo' => 'Error',
        'texto' => 'Faltan datos para procesar la devolución.'
    ];
    header("Location: activos.php");
    exit;
}
