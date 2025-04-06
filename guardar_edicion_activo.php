<?php
session_start();
include 'db.php';
header('Content-Type: application/json');
date_default_timezone_set('America/Santiago');

// Verificar autenticación si no es administrador
if ($_SESSION['rol'] !== 'Administrador' && isset($_POST['from_auth_modal'])) {
    $clave_admin = $_POST['clave_admin'] ?? '';

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

    if (!$validado) {
        echo json_encode([
            "status" => "error",
            "message" => "La contraseña de administrador es incorrecta. No se guardaron los cambios."
        ]);
        exit;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idactivo = intval($_POST['idactivo']);
    $nombre = $_POST['nombre'];
    $idcategoria = intval($_POST['idcategoria']);
    $idubicacion = intval($_POST['idubicacion']);
    $nro_asignacion = $_POST['nro_asignacion'];
    $estado = $_POST['estado'];
    $descripcion = $_POST['descripcion'];
    $cantidad = intval($_POST['cantidad']);


    // Primero obtén los datos anteriores para comparación (para historial)
$sql_ant = "SELECT nombre, cantidad, estado FROM activos WHERE idactivo = ?";
$stmt_ant = $conn->prepare($sql_ant);
$stmt_ant->bind_param("i", $idactivo);
$stmt_ant->execute();
$resultado_ant = $stmt_ant->get_result();
$activo_ant = $resultado_ant->fetch_assoc();
$stmt_ant->close();

    // Manejar imagen (si se sube una nueva)
    $img_nombre = null;
    if (isset($_FILES['img']) && $_FILES['img']['error'] == 0) {
        $directorio = "img/";
        $img_nombre = time() . "_" . basename($_FILES['img']['name']);
        $img_ruta = $directorio . $img_nombre;

        if (!move_uploaded_file($_FILES['img']['tmp_name'], $img_ruta)) {
            echo json_encode(["status" => "error", "message" => "Error al subir la imagen."]);
            exit;
        }

        // Actualizar activo incluyendo imagen
        $sql = "UPDATE activos SET nombre=?, idcategoria=?, idubicacion=?, nro_asignacion=?, estado=?, descripcion=?, cantidad=?, img=? WHERE idactivo=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siisssssi", $nombre, $idcategoria, $idubicacion, $nro_asignacion, $estado, $descripcion, $cantidad, $img_nombre, $idactivo);
    } else {
        // Actualizar activo sin cambiar imagen
        $sql = "UPDATE activos SET nombre=?, idcategoria=?, idubicacion=?, nro_asignacion=?, estado=?, descripcion=?, cantidad=? WHERE idactivo=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siissssi", $nombre, $idcategoria, $idubicacion, $nro_asignacion, $estado, $descripcion, $cantidad, $idactivo);
    }
    if ($stmt->execute()) {


    
        // Crear detalle específico de cambios
        $detalle_cambios = [];
    
        if ($activo_ant['nombre'] != $nombre) {
            $detalle_cambios[] = "Nombre: de \"{$activo_ant['nombre']}\" a \"$nombre\"";
        }
        if ($activo_ant['cantidad'] != $cantidad) {
            $detalle_cambios[] = "Cantidad: de {$activo_ant['cantidad']} a $cantidad";
        }
        if ($activo_ant['estado'] != $estado) {
            $detalle_cambios[] = "Estado: de {$activo_ant['estado']} a $estado";
        }
    
        $detalle = "Se actualizó el activo \"$nombre\" (ID: $idactivo). " . implode('; ', $detalle_cambios);
    
        // ✅ Guardar en historial correctamente
        $fecha_hora = date("Y-m-d H:i:s");
        $accion = "Actualización";
        $entidad = "activo";
        $entidad_id = $idactivo; // Aquí guardas el ID del activo editado
        $usuario_id = 1; // Cambia dinámicamente según tu sesión
    
        $sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                          VALUES (?, ?, ?, ?, ?, ?)";
    
        $stmt_historial = $conn->prepare($sql_historial);
        $stmt_historial->bind_param("sssisi", $fecha_hora, $accion, $entidad, $entidad_id, $detalle, $usuario_id);
    
        if (!$stmt_historial->execute()) {
            // Mostrar error si no se guarda
            echo json_encode(["status" => "error", "message" => "Error al guardar historial: " . $stmt_historial->error]);
            exit;
        }
    
        $stmt_historial->close();
    
    
        





        echo json_encode(["status" => "success", "message" => "Activo actualizado correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al actualizar el activo: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Petición no válida."]);
}
?>
