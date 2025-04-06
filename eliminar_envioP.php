<?php
include 'db.php';
session_start();
date_default_timezone_set('America/Santiago');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idenvio'])) {
    $idenvio = intval($_POST['idenvio']);
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    $rol_usuario = $_SESSION['rol'] ?? null;

    // Validar clave si no es administrador
    if ($rol_usuario !== 'Administrador') {
        $clave_admin = $_POST['clave_admin'] ?? '';
        if (!$clave_admin) {
            $_SESSION['mensaje'] = [
                'tipo' => 'error',
                'titulo' => 'Falta contraseña',
                'texto' => 'Debes ingresar la contraseña de un administrador.'
            ];
            header("Location: devolucion_productos.php");
            exit;
        }

        $stmt = $conn->prepare("SELECT password FROM usuario WHERE rol = 'Administrador'");
        $stmt->execute();
        $result = $stmt->get_result();
        $validado = false;
        while ($row = $result->fetch_assoc()) {
            if (password_verify($clave_admin, $row['password'])) {
                $validado = true;
                break;
            }
        }
        $stmt->close();

        if (!$validado) {
            $_SESSION['mensaje'] = [
                'tipo' => 'error',
                'titulo' => 'Clave incorrecta',
                'texto' => 'La contraseña no corresponde a ningún administrador.'
            ];
            header("Location: devolucion_productos.php");
            exit;
        }
    }

    // Obtener los productos del envío
    $detalles = $conn->query("SELECT producto_id, cantidad_devuelta, cantidad_usada, cantidad_perdida FROM envio_producto_detalle WHERE envio_id = $idenvio");

    $detalle_log = [];

    while ($row = $detalles->fetch_assoc()) {
        $idproducto = $row['producto_id'];
        $devuelto = intval($row['cantidad_devuelta']);
        $usado = intval($row['cantidad_usada']);
        $perdido = intval($row['cantidad_perdida']);

        // Registrar detalle para historial
        $detalle_log[] = "Producto ID $idproducto: Devueltos=$devuelto, Usados=$usado, Perdidos=$perdido";

        // Restaurar el stock
        $conn->query("UPDATE producto SET cantidad = cantidad + $usado + $perdido, en_uso = GREATEST(en_uso - ($devuelto + $usado + $perdido), 0), disponibles = GREATEST(disponibles - $devuelto, 0) WHERE idproducto = $idproducto");
    }

    // Eliminar los detalles del envío
    $conn->query("DELETE FROM envio_producto_detalle WHERE envio_id = $idenvio");

    // Eliminar el envío
    $conn->query("DELETE FROM envio_producto WHERE idenvio = $idenvio");

    // Registrar en historial
    if ($usuario_id) {
        $fecha = date("Y-m-d H:i:s");
        $accion = "Eliminación de Envío de Productos";
        $entidad = "Envio producto";
        $entidad_id = $idenvio;
        $detalle = "Se eliminó el envío ID $idenvio con los siguientes detalles: " . implode(", ", $detalle_log);

        $stmt_historial = $conn->prepare("INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_historial->bind_param("sssssi", $fecha, $accion, $entidad, $entidad_id, $detalle, $usuario_id);
        $stmt_historial->execute();
        $stmt_historial->close();
    }

    $_SESSION['mensaje'] = [
        'tipo' => 'success',
        'titulo' => 'Envío eliminado',
        'texto' => 'El envío fue eliminado correctamente.'
    ];
    echo "<script>window.location.href = 'devolucion_productos.php?deleted=1';</script>";
exit;

}
?>
