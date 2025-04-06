<?php
include 'db.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['id'])) {
    die("❌ Error: ID de activo no proporcionado.");
}

$id = intval($_GET['id']);

// Validar si existe
$check = $conn->query("SELECT idactivo FROM activos WHERE idactivo = $id");
if (!$check || $check->num_rows === 0) {
    die("❌ Error: El activo con ID $id no existe.");
}

// Eliminar
if ($conn->query("DELETE FROM activos WHERE idactivo = $id")) {
    header("Location: sucursal.php?eliminado=1");
exit;

} else {
    die("❌ Error al eliminar: " . $conn->error);
}
