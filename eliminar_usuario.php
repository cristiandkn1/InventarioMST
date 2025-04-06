<?php
include 'db.php';
session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM usuario WHERE idusuario = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = [
            'tipo' => 'success',
            'titulo' => 'Usuario eliminado',
            'texto' => 'El usuario fue borrado correctamente.'
        ];
    } else {
        $_SESSION['mensaje'] = [
            'tipo' => 'error',
            'titulo' => 'Error',
            'texto' => 'No se pudo eliminar el usuario.'
        ];
    }

    $stmt->close();
}

header("Location: usuarios.php");
exit;
?>
