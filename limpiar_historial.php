<?php
include 'db.php';
session_start();
date_default_timezone_set('America/Santiago');

if ($_SESSION['rol'] !== 'Administrador') {
    header("Location: historial.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$fecha_inicio = $_POST['fecha_inicio'] ?? null;
$fecha_fin    = $_POST['fecha_fin'] ?? null;

$condiciones = [];
$params = [];
$tipos = "";

// Ajustar rangos de fecha
if ($fecha_inicio) {
    $condiciones[] = "fecha >= ?";
    $params[] = $fecha_inicio . " 00:00:00";
    $tipos .= "s";
}
if ($fecha_fin) {
    $condiciones[] = "fecha <= ?";
    $params[] = $fecha_fin . " 23:59:59";
    $tipos .= "s";
}

$where = "";
if (!empty($condiciones)) {
    $where = "WHERE " . implode(" AND ", $condiciones);
}

// 1. Obtener registros antes de borrar
$sql_select = "SELECT * FROM historial $where ORDER BY fecha ASC";
$stmt_select = $conn->prepare($sql_select);
if (!empty($params)) {
    $stmt_select->bind_param($tipos, ...$params);
}
$stmt_select->execute();
$result = $stmt_select->get_result();

$historial_txt = "=== HISTORIAL ELIMINADO ===\n";
while ($row = $result->fetch_assoc()) {
    // Obtener nombre y correo del usuario
    $sql_usuario = "SELECT nombre, email FROM usuario WHERE idusuario = ?";
    $stmt_user = $conn->prepare($sql_usuario);
    $stmt_user->bind_param("i", $row['usuario_id']);
    $stmt_user->execute();
    $res_user = $stmt_user->get_result();
    $usuario = $res_user->fetch_assoc();
    $stmt_user->close();

    $nombre = $usuario['nombre'] ?? 'Desconocido';
    $email = $usuario['email'] ?? 'Sin correo';

    $historial_txt .= "ID: {$row['idhistorial']}\n";
    $historial_txt .= "Fecha: {$row['fecha']}\n";
    $historial_txt .= "Acción: {$row['accion']}\n";
    $historial_txt .= "Entidad: {$row['entidad']}\n";
    $historial_txt .= "ID Entidad: {$row['entidad_id']}\n";
    $historial_txt .= "Detalle: {$row['detalle']}\n";
    $historial_txt .= "Usuario: {$nombre} ({$email})\n";
    $historial_txt .= "---------------------------\n";
}
$stmt_select->close();

// 2. Borrar registros
$sql_delete = "DELETE FROM historial $where";
$stmt_delete = $conn->prepare($sql_delete);
if (!empty($params)) {
    $stmt_delete->bind_param($tipos, ...$params);
}
$ok = $stmt_delete->execute();
$stmt_delete->close();

// 3. Registrar acción en historial
if ($ok) {
    $fecha_accion = date('Y-m-d H:i:s');
    if ($fecha_inicio && $fecha_fin) {
        $detalle = "Se eliminó el historial desde {$fecha_inicio} 00:00:00 hasta {$fecha_fin} 23:59:59.";
    } elseif ($fecha_inicio) {
        $detalle = "Se eliminó el historial desde {$fecha_inicio} 00:00:00 en adelante.";
    } elseif ($fecha_fin) {
        $detalle = "Se eliminó el historial hasta {$fecha_fin} 23:59:59.";
    } else {
        $detalle = "Se eliminó todo el historial del sistema.";
    }

    $sql_historial = "INSERT INTO historial (fecha, accion, entidad, detalle, usuario_id)
                      VALUES (?, 'Limpieza Historial', 'historial', ?, ?)";
    $stmt_historial = $conn->prepare($sql_historial);
    $stmt_historial->bind_param("ssi", $fecha_accion, $detalle, $usuario_id);
    $stmt_historial->execute();
    $stmt_historial->close();
}

// 4. Generar descarga del archivo de respaldo
$filename = "respaldo_historial_" . date("Ymd_His") . ".txt";

header("Content-Type: text/plain");
header("Content-Disposition: attachment; filename=$filename");
header("Content-Length: " . strlen($historial_txt));
echo $historial_txt;
exit;
?>
