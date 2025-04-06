<?php
session_start();
include 'db.php';
date_default_timezone_set('America/Santiago');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $clave_admin = $_POST['clave_admin'] ?? null;

    // Validación para usuarios que no son administradores
    if ($_SESSION['rol'] !== 'Administrador') {
        if (!$clave_admin) {
            $_SESSION['mensaje'] = [
                'tipo' => 'error',
                'titulo' => 'Autenticación requerida',
                'texto' => 'Debes ingresar una contraseña de administrador.'
            ];
            header("Location: sucursales.php");
            exit;
        }

        // Validar contra claves de administradores
        $stmt = $conn->prepare("SELECT password FROM usuario WHERE rol = 'Administrador'");
        $stmt->execute();
        $result = $stmt->get_result();
        $validado = false;

        while ($row = $result->fetch_assoc()) {
            if (password_verify($clave_admin, $row['password'])) {
                $validado = true;
                break;
            }
        }

        $stmt->close();

        if (!$validado) {
            $_SESSION['mensaje'] = [
                'tipo' => 'error',
                'titulo' => 'Clave incorrecta',
                'texto' => 'La contraseña ingresada no corresponde a un administrador.'
            ];
            header("Location: sucursales.php");
            exit;
        }
    }

    if (!empty($nombre)) {
        $stmt = $conn->prepare("INSERT INTO ubicaciones (nombre, descripcion) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $descripcion);
        $stmt->execute();
        $stmt->close();

        // Obtener el ID de la ubicación recién creada
$ubicacion_id = $conn->insert_id;

// Guardar en historial
$accion = "Creación";
$entidad = "ubicacion";
$entidad_id = $ubicacion_id;
$detalle = "Se registró la ubicación '$nombre'" . (!empty($descripcion) ? " con descripción: '$descripcion'" : "");
$fecha_hora = date("Y-m-d H:i:s");
$usuario_id = $_SESSION['usuario_id'] ?? 0;

$stmt_historial = $conn->prepare("INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
$stmt_historial->bind_param("sssisi", $fecha_hora, $accion, $entidad, $entidad_id, $detalle, $usuario_id);
$stmt_historial->execute();
$stmt_historial->close();


        $_SESSION['mensaje'] = [
            'tipo' => 'success',
            'titulo' => 'Ubicación agregada',
            'texto' => 'La ubicación fue registrada correctamente.'
        ];
    } else {
        $_SESSION['mensaje'] = [
            'tipo' => 'error',
            'titulo' => 'Error',
            'texto' => 'El nombre de la ubicación es obligatorio.'
        ];
    }

    header("Location: sucursales.php");
    exit;
}
?>
