<?php
include 'db.php';
session_start();

$clave_admin = $_POST['clave_admin'];
$idusuario = $_POST['idusuario'];

// Buscar algún usuario administrador
$stmt = $conn->prepare("SELECT password FROM usuario WHERE rol = 'Administrador'");
$stmt->execute();
$result = $stmt->get_result();

$validado = false;
while ($admin = $result->fetch_assoc()) {
    if (password_verify($clave_admin, $admin['password'])) {
        $validado = true;
        break;
    }
}

if ($validado) {
    $_SESSION['autorizado'] = true;

    // Redirige al script que guarda los cambios (por ejemplo)
    header("Location: guardar_edicion.php?id=$idproducto&auvalidar_clave_admin.phptorizado=1");
    exit;
}
 else {
    $_SESSION['mensaje'] = [
        'tipo' => 'error',
        'titulo' => 'Autenticación fallida',
        'texto' => 'La contraseña del administrador no es válida.'
    ];
    header("Location: usuarios.php");   
}
?>
