<?php
session_start(); // ✅ Necesario para usar $_SESSION['usuario_id']
include 'db.php';
date_default_timezone_set('America/Santiago');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idactivo'])) {
    $id = intval($_POST['idactivo']);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ID inválido."]);
        exit;
    }

    // Si no es admin, validar contraseña
    if ($_SESSION['rol'] !== 'Administrador') {
        $clave = $_POST['clave_admin'] ?? '';

        // Validar clave contra usuarios admin
        $stmt_check = $conn->prepare("SELECT password FROM usuario WHERE rol = 'Administrador'");
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        $valida = false;

        while ($row = $result->fetch_assoc()) {
            if (password_verify($clave, $row['password'])) {
                $valida = true;
                break;
            }
        }

        if (!$valida) {
            echo json_encode(["status" => "error", "message" => "La contraseña de administrador es incorrecta. No se eliminó el activo."]);
            exit;
        }
    }

    // ✅ Obtener información del activo antes de eliminar
    $sql_info = "SELECT nombre FROM activos WHERE idactivo = ?";
    $stmt_info = $conn->prepare($sql_info);
    $stmt_info->bind_param("i", $id);
    $stmt_info->execute();
    $result_info = $stmt_info->get_result();
    $activo_info = $result_info->fetch_assoc();
    $stmt_info->close();

    if (!$activo_info) {
        echo json_encode(["status" => "error", "message" => "Activo no encontrado."]);
        exit;
    }

    // ✅ Eliminar activo
    $stmt = $conn->prepare("DELETE FROM activos WHERE idactivo = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // ✅ Insertar en historial
        $usuario_id = $_SESSION['usuario_id'] ?? 0;
        $accion = "Eliminación";
        $entidad = "activo";
        $entidad_id = $id;
        $nombre_activo = $activo_info['nombre'];
        $detalle = "Se eliminó el activo con nombre: '$nombre_activo' (ID: $id)";
        $fecha = date("Y-m-d H:i:s");

        $sql_hist = "INSERT INTO historial (usuario_id, accion, entidad, entidad_id, detalle, fecha) 
                     VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_hist = $conn->prepare($sql_hist);

        if ($stmt_hist) {
            $stmt_hist->bind_param("ississ", $usuario_id, $accion, $entidad, $entidad_id, $detalle, $fecha);
            $stmt_hist->execute();
            $stmt_hist->close();
        } else {
            error_log("❌ Error al preparar historial: " . $conn->error);
        }

        echo json_encode(["status" => "success", "message" => "El activo fue eliminado correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al eliminar: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Solicitud no válida."]);
}
?>
