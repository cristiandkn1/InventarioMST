<?php
session_start();
include 'db.php';
date_default_timezone_set('America/Santiago');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar clave si no es administrador
    if ($_SESSION['rol'] !== 'Administrador') {
        $clave_admin = $_POST['clave_admin'] ?? '';

        if (empty($clave_admin)) {
            $_SESSION['mensaje'] = [
                'tipo' => 'error',
                'titulo' => 'Acceso restringido',
                'texto' => 'Debes ingresar la contraseña de un administrador para guardar.'
            ];
            header("Location: envio_productos_multiple.php");
            exit;
        }

        // Buscar un administrador con esa clave
        $sql_admin = "SELECT password FROM usuario WHERE rol = 'Administrador'";
        $result = mysqli_query($conn, $sql_admin);
        $validado = false;

        while ($admin = mysqli_fetch_assoc($result)) {
            if (password_verify($clave_admin, $admin['password'])) {
                $validado = true;
                break;
            }
        }

        if (!$validado) {
            $_SESSION['mensaje'] = [
                'tipo' => 'error',
                'titulo' => 'Clave incorrecta',
                'texto' => 'La contraseña ingresada no corresponde a un administrador.'
            ];
            header("Location: envio_productos_multiple.php");
            exit;
        }
    }

    $ubicacion_id = intval($_POST['ubicacion_destino'] ?? 0);
    $encargado = trim($_POST['encargado'] ?? '');
    $productos = $_POST['productos'] ?? [];

    if ($ubicacion_id <= 0 || empty($encargado) || empty($productos)) {
        $_SESSION['mensaje'] = [
            'tipo' => 'error',
            'titulo' => 'Error',
            'texto' => '❌ Faltan datos para procesar el envío.'
        ];
        header("Location: envio_productos_multiple.php");
        exit;
    }

    // Insertar envío principal
    $stmt_envio = $conn->prepare("INSERT INTO envio_producto (ubicacion_id, encargado, fecha) VALUES (?, ?, NOW())");
    $stmt_envio->bind_param("is", $ubicacion_id, $encargado);
    $stmt_envio->execute();
    $envio_id = $stmt_envio->insert_id;
    $stmt_envio->close();

    foreach ($productos as $prod) {
        $id = intval($prod['id']);
        $cantidad = intval($prod['cantidad']);
    
        if ($id > 0 && $cantidad > 0) {
            // Verificar stock disponible
            $sql_check = "SELECT nombre, cantidad, COALESCE(en_uso, 0) AS en_uso FROM producto WHERE idproducto = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("i", $id);
            $stmt_check->execute();
            $res = $stmt_check->get_result();
            $row = $res->fetch_assoc();
            $stmt_check->close();
    
            $nombre_producto = $row['nombre'];
            $cantidad_total = (int)$row['cantidad'];
            $en_uso = (int)$row['en_uso'];
            $disponibles = $cantidad_total - $en_uso;
    
            if ($cantidad > $disponibles) {
                $_SESSION['mensaje'] = [
                    'tipo' => 'error',
                    'titulo' => 'Stock insuficiente',
                    'texto' => "El producto ID $id no tiene suficientes unidades disponibles."
                ];
                header("Location: envio_productos_multiple.php");
                exit;
            }
    
            // Insertar detalle del producto enviado
            $stmt_det = $conn->prepare("INSERT INTO envio_producto_detalle (envio_id, producto_id, cantidad_enviada) VALUES (?, ?, ?)");
            $stmt_det->bind_param("iii", $envio_id, $id, $cantidad);
            $stmt_det->execute();
            $stmt_det->close();
    
            // Actualizar en uso
            $stmt_update = $conn->prepare("UPDATE producto SET en_uso = COALESCE(en_uso, 0) + ? WHERE idproducto = ?");
            $stmt_update->bind_param("ii", $cantidad, $id);
            $stmt_update->execute();
            $stmt_update->close();
    
            // Insertar en historial
            $fecha_hora = date("Y-m-d H:i:s");
            $usuario_id = $_SESSION['usuario_id'] ?? null;
            $accion = "Envío de Producto";
            $detalle = "Se enviaron $cantidad unidades del producto '$nombre_producto' (ID: $id) en el envío ID $envio_id";
    
            $stmt_historial = $conn->prepare("INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id) VALUES (?, ?, 'producto', ?, ?, ?)");
            $stmt_historial->bind_param("ssisi", $fecha_hora, $accion, $id, $detalle, $usuario_id);
            $stmt_historial->execute();
            $stmt_historial->close();
        }
    }

    // ✅ Éxito
    $_SESSION['mensaje'] = [
        'tipo' => 'success',
        'titulo' => '✅ Envío registrado',
        'texto' => 'El envío múltiple fue guardado correctamente.'
    ];
    header("Location: index.php");
    exit;
} else {
    $_SESSION['mensaje'] = [
        'tipo' => 'error',
        'titulo' => 'Acceso denegado',
        'texto' => 'Debes enviar el formulario correctamente.'
    ];
    header("Location: envio_productos_multiple.php");
    exit;
}