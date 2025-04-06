<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo         = $_POST['tipo'];
    $nombre       = trim($_POST['nombre']);
    $cantidad     = intval($_POST['cantidad']);
    $sucursal_id  = intval($_POST['sucursal_id']);
    $estado       = trim($_POST['estado']);

    if ($tipo === 'producto') {
        $categoria_id = intval($_POST['categoria_id_producto']);
        $precio       = floatval($_POST['precio']);

        $sql = "INSERT INTO producto 
                (nombre, categoria_id, precio, estado, cantidad, en_uso, disponibles) 
                VALUES (?, ?, ?, ?, ?, 0, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisdii", $nombre, $categoria_id, $precio, $estado, $cantidad, $cantidad);

        if ($stmt->execute()) {
            $idproducto = $conn->insert_id;

            $encargado = "Sistema";
            $conn->query("
                INSERT INTO envio_producto (ubicacion_id, encargado, fecha, devuelto)
                VALUES ($sucursal_id, '$encargado', NOW(), 0)
            ");
            $idenvio = $conn->insert_id;

            $conn->query("
                INSERT INTO envio_producto_detalle (envio_id, producto_id, cantidad_enviada, cantidad_devuelta)
                VALUES ($idenvio, $idproducto, $cantidad, 0)
            ");

            header("Location: sucursal.php?sucursal_id=$sucursal_id&ok=producto");
            exit;
        } else {
            die("\u274C Error al insertar producto: " . $conn->error);
        }

    } elseif ($tipo === 'activo') {
        $categoria_id   = intval($_POST['categoria_id']);
        $nro_asignacion = trim($_POST['nro_asignacion']);

        $img = null;
        $directorio = "img/";
        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!empty($_FILES['img']['name'])) {
            $extension = strtolower(pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $permitidos)) {
                die("\u274C Formato de imagen no permitido.");
            }

            $img = time() . "_activo_" . basename($_FILES['img']['name']);
            $ruta_destino = $directorio . $img;

            if (!move_uploaded_file($_FILES['img']['tmp_name'], $ruta_destino)) {
                die("\u274C No se pudo guardar la imagen.");
            }
        }

        $sql = "INSERT INTO activos 
                (nombre, idcategoria, nro_asignacion, estado, cantidad, idubicacion, fecha_registro, img) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sissiis", $nombre, $categoria_id, $nro_asignacion, $estado, $cantidad, $sucursal_id, $img);

        if ($stmt->execute()) {
            header("Location: sucursal.php?sucursal_id=$sucursal_id&ok=activo");
            exit;
        } else {
            die("\u274C Error al insertar activo: " . $conn->error);
        }
    } else {
        die("Tipo inválido.");
    }
} else {
    header("Location: sucursal.php");
    exit;
}
