<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $rol = $_POST['rol'];

    $stmt = $conn->prepare("INSERT INTO usuario (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $email, $password, $rol);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = [
            'tipo' => 'success',
            'titulo' => 'Usuario agregado',
            'texto' => 'El nuevo usuario fue registrado correctamente.'
        ];
    } else {
        $_SESSION['mensaje'] = [
            'tipo' => 'error',
            'titulo' => 'Error',
            'texto' => 'No se pudo agregar el usuario.'
        ];
    }

    $stmt->close();
    header("Location: usuarios.php");
    exit;
}
?>
