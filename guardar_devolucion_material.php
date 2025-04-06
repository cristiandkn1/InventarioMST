<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

$trabajo_id = intval($_POST['trabajo_id']);
$devueltos = $_POST['devueltos'];
$usados = $_POST['usados'];

// Validar contraseña si no es admin
if ($_SESSION['rol'] !== 'Administrador' && isset($_POST['from_auth_modal'])) {
    $clave_admin = $_POST['clave_admin'] ?? '';

    $stmt = $conn->prepare("SELECT password FROM usuario WHERE rol = 'Administrador'");
    $stmt->execute();
    $res = $stmt->get_result();

    $valido = false;
    while ($row = $res->fetch_assoc()) {
        if (password_verify($clave_admin, $row['password'])) {
            $valido = true;
            break;
        }
    }

    if (!$valido) {
        echo json_encode([
            "status" => "error",
            "message" => "La contraseña del administrador es incorrecta. No se guardaron los cambios."
        ]);
        exit;
    }
}

foreach ($devueltos as $idtrabajo_producto => $cant_devuelta) {
    $cant_devuelta = intval($cant_devuelta);
    $cant_usada = isset($usados[$idtrabajo_producto]) ? intval($usados[$idtrabajo_producto]) : 0;

    // 1. Obtener el ID del producto real desde trabajo_producto (solo para referencia)
    $sql_get = "SELECT producto_id FROM trabajo_producto WHERE idtrabajo_producto = ?";
    $stmt_get = $conn->prepare($sql_get);
    $stmt_get->bind_param("i", $idtrabajo_producto);
    $stmt_get->execute();
    $stmt_get->bind_result($producto_id);
    $stmt_get->fetch();
    $stmt_get->close();

    // 2. Insertar registro de devolución (historial)
    $sql_hist = "INSERT INTO devoluciones (trabajo_producto_id, trabajo_id, devueltos, usados, fecha)
                 VALUES (?, ?, ?, ?, NOW())";
    $stmt_hist = $conn->prepare($sql_hist);
    $stmt_hist->bind_param("iiii", $idtrabajo_producto, $trabajo_id, $cant_devuelta, $cant_usada);
    $stmt_hist->execute();
    $stmt_hist->close();

    // 3. Obtener datos actuales del producto
    $sql_prod = "SELECT cantidad, en_uso FROM producto WHERE idproducto = ?";
    $stmt_prod = $conn->prepare($sql_prod);
    $stmt_prod->bind_param("i", $producto_id);
    $stmt_prod->execute();
    $stmt_prod->bind_result($cantidad_actual, $en_uso_actual);
    $stmt_prod->fetch();
    $stmt_prod->close();

    // 4. Calcular nuevos valores
    $nueva_en_uso = max($en_uso_actual - ($cant_devuelta + $cant_usada), 0);
    $nueva_cantidad = max($cantidad_actual - $cant_usada, 0);
    $nueva_disponible = max($nueva_cantidad - $nueva_en_uso, 0);

    // 5. Actualizar tabla producto
    $sql_upd = "UPDATE producto 
                SET en_uso = ?, cantidad = ?, disponibles = ?
                WHERE idproducto = ?";
    $stmt_upd = $conn->prepare($sql_upd);
    $stmt_upd->bind_param("iiii", $nueva_en_uso, $nueva_cantidad, $nueva_disponible, $producto_id);
    if (!$stmt_upd->execute()) {
        echo json_encode(["status" => "error", "message" => "Error al actualizar producto: " . $stmt_upd->error]);
        exit;
    }
    $stmt_upd->close();
}

echo json_encode(["status" => "success"]);
?>