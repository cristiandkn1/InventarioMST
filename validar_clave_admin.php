<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

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

if ($valido) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "La contraseña ingresada no es válida."
    ]);
}
?>
