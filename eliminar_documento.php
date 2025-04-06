<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['iddocumento']);
    $usuario_id = $_SESSION['usuario_id'];

    $sql = "SELECT usuario_origen, usuario_destino FROM documento WHERE iddocumento = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $doc = $res->fetch_assoc();
    $stmt->close();

    if (!$doc) {
        echo json_encode(["status" => "error", "message" => "Documento no encontrado."]);
        exit;
    }

    // ✅ Caso especial: el documento fue enviado a sí mismo
    if ($usuario_id == $doc['usuario_origen'] && $usuario_id == $doc['usuario_destino']) {
        $sql_update = "UPDATE documento SET eliminado_origen = 1, eliminado_destino = 1 WHERE iddocumento = ?";
    }
    // ✅ Usuario es origen
    elseif ($usuario_id == $doc['usuario_origen']) {
        $sql_update = "UPDATE documento SET eliminado_origen = 1 WHERE iddocumento = ?";
    }
    // ✅ Usuario es destino
    elseif ($usuario_id == $doc['usuario_destino']) {
        $sql_update = "UPDATE documento SET eliminado_destino = 1 WHERE iddocumento = ?";
    }
    // ❌ Usuario no tiene permisos
    else {
        echo json_encode(["status" => "error", "message" => "No tienes permisos para ocultar este documento."]);
        exit;
    }

    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("i", $id);

    if ($stmt_update->execute()) {
        echo json_encode(["status" => "success", "message" => "Documento ocultado correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al ocultar el documento."]);
    }

    $stmt_update->close();
}
?>
