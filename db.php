<?php
$servername = "localhost";
$username = "root";  // Cambia esto si tienes otro usuario
$password = "";  // Cambia esto si tienes contraseña
$database = "inventariomineria";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
