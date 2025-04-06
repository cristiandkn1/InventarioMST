<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clave = $_POST['clave_admin'] ?? '';
    $idenvio = intval($_POST['idenvio'] ?? 0);

    if (empty($clave) || $idenvio <= 0) {
        $_SESSION['mensaje'] = [
            'tipo' => 'error',
            'titulo' => 'Datos inválidos',
            'texto' => 'Debe ingresar la contraseña y seleccionar un envío válido.'
        ];
        header("Location: devolucion_productos.php");
        exit;
    }

    // Buscar usuarios administradores
    $stmt = $conn->prepare("SELECT password FROM usuario WHERE rol = 'Administrador'");
    $stmt->execute();
    $result = $stmt->get_result();

    $valido = false;
    while ($row = $result->fetch_assoc()) {
        if (password_verify($clave, $row['password'])) {
            $valido = true;
            break;
        }
    }
    $stmt->close();

    if ($valido) {
        header("Location: procesar_devolucion.php?idenvio=$idenvio");
        exit;
    } else {
        $_SESSION['mensaje'] = [
            'tipo' => 'error',
            'titulo' => 'Clave incorrecta',
            'texto' => 'La contraseña ingresada no corresponde a un administrador.'
        ];
        header("Location: devolucion_productos.php");
        exit;
    }
} else {
    header("Location: devolucion_productos.php");
    exit;
}
