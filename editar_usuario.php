<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['idusuario'];
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $rol = $_POST['rol'];
    $nueva_pass = $_POST['nueva_contrasena']; // Este campo debe venir del formulario

    if (!empty($nueva_pass)) {
        // Hashear la nueva contraseña si fue ingresada
        $hash = password_hash($nueva_pass, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE usuario SET nombre = ?, email = ?, rol = ?, password = ? WHERE idusuario = ?");
        $stmt->bind_param("ssssi", $nombre, $email, $rol, $hash, $id);
    } else {
        // Si no cambia contraseña
        $stmt = $conn->prepare("UPDATE usuario SET nombre = ?, email = ?, rol = ? WHERE idusuario = ?");
        $stmt->bind_param("sssi", $nombre, $email, $rol, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = [
            'tipo' => 'success',
            'titulo' => 'Usuario actualizado',
            'texto' => 'Los cambios fueron guardados.'
        ];
    } else {
        $_SESSION['mensaje'] = [
            'tipo' => 'error',
            'titulo' => 'Error',
            'texto' => 'No se pudo actualizar el usuario.'
        ];
    }

    $stmt->close();
    header("Location: usuarios.php");
    exit;
}
?>
