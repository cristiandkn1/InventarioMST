<?php
include 'db.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Verifica sesión
if (!isset($_SESSION['usuario_id'], $_SESSION['rol'])) {
    echo json_encode(["status" => "error", "message" => "No autorizado."]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$rol = $_SESSION['rol'];
$trabajo_id = intval($_POST['trabajo_id']);
$password = $_POST['password'] ?? '';

if (!$trabajo_id) {
    echo json_encode(["status" => "error", "message" => "Trabajo no especificado."]);
    exit;
}

// ✅ Si el usuario NO es administrador, validar contraseña
if ($rol !== 'Administrador') {
    if (!$password) {
        echo json_encode(["status" => "error", "message" => "Contraseña requerida."]);
        exit;
    }

    // Buscar su contraseña real (del mismo usuario logueado)
    $stmt_user = $conn->prepare("SELECT password FROM usuario WHERE idusuario = ?");
    if (!$stmt_user) {
        echo json_encode(["status" => "error", "message" => "Error al validar usuario."]);
        exit;
    }

    $stmt_user->bind_param("i", $usuario_id);
    $stmt_user->execute();
    $stmt_user->bind_result($hash);
    $stmt_user->fetch();
    $stmt_user->close();

    if (!$hash || !password_verify($password, $hash)) {
        echo json_encode(["status" => "error", "message" => "Contraseña incorrecta."]);
        exit;
    }
}

// ✅ Obtener productos relacionados
$sql_get = "SELECT trabajo_producto_id FROM devoluciones WHERE trabajo_id = ?";
$stmt = $conn->prepare($sql_get);
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Error al obtener devoluciones: " . $conn->error]);
    exit;
}
$stmt->bind_param("i", $trabajo_id);
$stmt->execute();
$result = $stmt->get_result();

$producto_ids = [];

while ($row = $result->fetch_assoc()) {
    $tp_id = $row['trabajo_producto_id'];
    $sql_prod = "SELECT producto_id FROM trabajo_producto WHERE idtrabajo_producto = ?";
    $stmt_prod = $conn->prepare($sql_prod);
    if ($stmt_prod) {
        $stmt_prod->bind_param("i", $tp_id);
        $stmt_prod->execute();
        $stmt_prod->bind_result($idproducto);
        $stmt_prod->fetch();
        $stmt_prod->close();

        $producto_ids[] = $idproducto;
    }
}
$stmt->close();

// ✅ Eliminar devoluciones del trabajo
$stmt_del = $conn->prepare("DELETE FROM devoluciones WHERE trabajo_id = ?");
if (!$stmt_del) {
    echo json_encode(["status" => "error", "message" => "Error al eliminar devoluciones: " . $conn->error]);
    exit;
}
$stmt_del->bind_param("i", $trabajo_id);
$stmt_del->execute();
$stmt_del->close();

// ✅ Log de seguridad
file_put_contents("log_rehacer_devolucion.txt", "[" . date("Y-m-d H:i:s") . "] Rehecho trabajo ID: $trabajo_id por ID Usuario: $usuario_id (Rol: $rol)\n", FILE_APPEND);

// ✅ Registro en historial
$accion = "Rehacer Devolución";
$entidad = "trabajo";
$detalle = "Se eliminaron todas las devoluciones asociadas al trabajo ID $trabajo_id.";
$fecha = date("Y-m-d H:i:s");

$stmt_historial = $conn->prepare("INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
$stmt_historial->bind_param("sssssi", $fecha, $accion, $entidad, $trabajo_id, $detalle, $usuario_id);
$stmt_historial->execute();
$stmt_historial->close();

echo json_encode(["status" => "success", "message" => "Devoluciones eliminadas correctamente."]);
