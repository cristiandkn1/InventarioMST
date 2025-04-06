<?php
include 'db.php';
session_start();
date_default_timezone_set('America/Santiago');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idubicacion = intval($_POST['idubicacion'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $clave_admin = $_POST['clave_admin'] ?? null;
    $usuario_id = $_SESSION['usuario_id'] ?? null;

    // Validar si el usuario es administrador o necesita autenticar
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

    // Obtener datos anteriores para el historial
    $stmt_old = $conn->prepare("SELECT nombre, descripcion FROM ubicaciones WHERE idubicacion = ?");
    $stmt_old->bind_param("i", $idubicacion);
    $stmt_old->execute();
    $result_old = $stmt_old->get_result();
    $ubicacion_old = $result_old->fetch_assoc();
    $stmt_old->close();

    if ($idubicacion > 0 && !empty($nombre)) {
        $stmt = $conn->prepare("UPDATE ubicaciones SET nombre = ?, descripcion = ? WHERE idubicacion = ?");
        $stmt->bind_param("ssi", $nombre, $descripcion, $idubicacion);
        $stmt->execute();
        $stmt->close();

        // Guardar en historial si hubo cambios
        $cambios = [];
        if ($ubicacion_old['nombre'] != $nombre) {
            $cambios[] = "Nombre: '{$ubicacion_old['nombre']}' → '$nombre'";
        }
        if ($ubicacion_old['descripcion'] != $descripcion) {
            $cambios[] = "Descripción: '{$ubicacion_old['descripcion']}' → '$descripcion'";
        }

        if (!empty($cambios)) {
            $accion = "Actualización de Ubicación";
            $detalle = "Cambios: " . implode(", ", $cambios);
            $fecha = date("Y-m-d H:i:s");

            $stmt_hist = $conn->prepare("INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id) VALUES (?, ?, 'ubicacion', ?, ?, ?)");
            $stmt_hist->bind_param("ssisi", $fecha, $accion, $idubicacion, $detalle, $usuario_id);
            $stmt_hist->execute();
            $stmt_hist->close();
        }

        $_SESSION['mensaje'] = ['tipo' => 'success', 'titulo' => 'Ubicación actualizada', 'texto' => 'La ubicación fue modificada correctamente.'];
    } else {
        $_SESSION['mensaje'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'Faltan datos para actualizar.'];
    }

    header("Location: sucursales.php");
    exit;
}