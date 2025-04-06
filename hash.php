<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "inventariomineria";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Buscar al usuario por email
$email = "juanito@gmail.com";
$sql = "SELECT idusuario FROM usuario WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $id = $row['idusuario'];

    // Nueva contraseña a hashear
    $password_plano = "hola1234";
    $password_hash = password_hash($password_plano, PASSWORD_BCRYPT);

    // Actualizar contraseña
    $update = $conn->prepare("UPDATE usuario SET password = ? WHERE idusuario = ?");
    $update->bind_param("si", $password_hash, $id);

    if ($update->execute()) {
        echo "Contraseña actualizada para '$email' (ID $id)<br>";
        echo "Nuevo hash: $password_hash";
    } else {
        echo "Error al actualizar: " . $conn->error;
    }

    $update->close();
} else {
    echo "No se encontró el usuario '$email'";
}

$stmt->close();
$conn->close();
?>
