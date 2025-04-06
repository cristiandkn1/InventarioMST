<?php
session_start();
include 'db.php';
date_default_timezone_set('America/Santiago');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idproducto = intval($_POST['idproducto']);
    $nombre_nuevo = $conn->real_escape_string(htmlspecialchars_decode($_POST['nombre'], ENT_QUOTES));
    $precio_nuevo = floatval($_POST['precio']);
    $estado_nuevo = $conn->real_escape_string($_POST['estado']);
    $categoria_id_nuevo = intval($_POST['categoria_id']);
    $descripcion_nueva = isset($_POST['descripcion']) ? $conn->real_escape_string(trim($_POST['descripcion'])) : '';
    $cantidad_nueva = intval($_POST['cantidad']);
    $nro_asignacion_nuevo = isset($_POST['nro_asignacion']) && trim($_POST['nro_asignacion']) !== '' ? $conn->real_escape_string($_POST['nro_asignacion']) : '';

    $return_url = $_POST['return_url'] ?? 'index.php';
    $usuario_id = $_SESSION['usuario_id'] ?? 1;

    // 🔍 DEBUG 1 - Mostrar lo que llega del formulario
    file_put_contents('debug_post.txt', print_r($_POST, true));

    if ($_SESSION['rol'] !== 'Administrador') {
        $clave_admin = $_POST['clave_admin'] ?? '';
        $validado = false;

        $result = $conn->query("SELECT password FROM usuario WHERE rol = 'Administrador'");
        while ($admin = $result->fetch_assoc()) {
            if (password_verify($clave_admin, $admin['password'])) {
                $validado = true;
                break;
            }
        }

        if (!$validado) {
            echo "<script>alert('Contraseña de administrador incorrecta.'); window.location.href = 'editar_producto.php?id=$idproducto';</script>";
            exit;
        }
    }

    $stmt_old = $conn->prepare("SELECT * FROM producto WHERE idproducto = ?");
    $stmt_old->bind_param("i", $idproducto);
    $stmt_old->execute();
    $result_old = $stmt_old->get_result();
    $producto_old = $result_old->fetch_assoc();
    $stmt_old->close();

    if (!$producto_old) {
        echo "<script>alert('Producto no encontrado.'); window.location.href='index.php';</script>";
        exit();
    }

    $en_uso_actual = intval($producto_old['en_uso']);
    $disponibles_nuevo = max($cantidad_nueva - $en_uso_actual, 0);

    $cambios = [];
    if ($producto_old['nombre'] != $nombre_nuevo) $cambios[] = "Nombre: '{$producto_old['nombre']}' → '$nombre_nuevo'";
    if ($producto_old['precio'] != $precio_nuevo) $cambios[] = "Precio: '{$producto_old['precio']}' → '$precio_nuevo'";
    if ($producto_old['estado'] != $estado_nuevo) $cambios[] = "Estado: '{$producto_old['estado']}' → '$estado_nuevo'";
    if ($producto_old['categoria_id'] != $categoria_id_nuevo) $cambios[] = "Categoría: '{$producto_old['categoria_id']}' → '$categoria_id_nuevo'";
    if ($producto_old['descripcion'] !== $descripcion_nueva) $cambios[] = "Descripción: '{$producto_old['descripcion']}' → '$descripcion_nueva'";
    if ($producto_old['cantidad'] != $cantidad_nueva) $cambios[] = "Cantidad: '{$producto_old['cantidad']}' → '$cantidad_nueva'";
    if ($producto_old['nro_asignacion'] !== $nro_asignacion_nuevo) $cambios[] = "Nro Asignación: '{$producto_old['nro_asignacion']}' → '$nro_asignacion_nuevo'";

    if (empty($cambios)) {
        echo "<script>alert('No se realizaron cambios.'); window.location.href='" . htmlspecialchars($return_url) . "';</script>";
        exit();
    }

    // 🔍 DEBUG 2 - Mostrar variables preparadas para el UPDATE
    $debug_info = [
        'nombre' => $nombre_nuevo,
        'precio' => $precio_nuevo,
        'estado' => $estado_nuevo,
        'categoria_id' => $categoria_id_nuevo,
        'descripcion' => $descripcion_nueva,
        'cantidad' => $cantidad_nueva,
        'nro_asignacion' => $nro_asignacion_nuevo,
        'disponibles' => $disponibles_nuevo,
        'idproducto' => $idproducto
    ];
    file_put_contents('debug_update.txt', print_r($debug_info, true));

    $sql = "UPDATE producto SET 
        nombre=?, precio=?, estado=?, categoria_id=?, 
        descripcion=?, cantidad=?, nro_asignacion=?, disponibles=?
        WHERE idproducto=?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("❌ Error al preparar: " . $conn->error);
    }

    $stmt->bind_param("sdsssissi",
    $nombre_nuevo,
        $precio_nuevo,
        $estado_nuevo,
        $categoria_id_nuevo,
        $descripcion_nueva,
        $cantidad_nueva,
        $nro_asignacion_nuevo,
        $disponibles_nuevo,
        $idproducto
    );

    if (!$stmt->execute()) {
        die("❌ Error al ejecutar UPDATE: " . $stmt->error);
    }

    // Historial
    $accion = "Edición de Producto";
    $detalle = "Cambios: " . implode(", ", $cambios);
    $fecha_hora = date("Y-m-d H:i:s");

    $sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                      VALUES (?, ?, 'producto', ?, ?, ?)";
    $stmt_historial = $conn->prepare($sql_historial);
    $stmt_historial->bind_param("ssisi", $fecha_hora, $accion, $idproducto, $detalle, $usuario_id);
    $stmt_historial->execute();
    $stmt_historial->close();

    echo "<script>alert('Producto actualizado correctamente.'); window.location.href='" . htmlspecialchars($return_url) . "';</script>";

    $stmt->close();
    $conn->close();
}
?>
