<?php
include 'db.php';
session_start();
date_default_timezone_set('America/Santiago');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idubicacion = intval($_POST['idubicacion'] ?? 0);
    $clave_admin = $_POST['clave_admin'] ?? null;
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    $es_admin = $_SESSION['rol'] === 'Administrador';

    // 🔐 Validar contraseña si NO es administrador
    if (!$es_admin) {
        if (!$clave_admin) {
            $_SESSION['mensaje'] = [
                'tipo' => 'error',
                'titulo' => 'Autenticación requerida',
                'texto' => 'Debes ingresar una contraseña de administrador.'
            ];
            header("Location: sucursales.php");
            exit;
        }

        // Validar clave contra cualquier administrador
        $stmt_admin = $conn->prepare("SELECT password FROM usuario WHERE rol = 'Administrador'");
        $stmt_admin->execute();
        $res_admin = $stmt_admin->get_result();
        $valido = false;

        while ($row = $res_admin->fetch_assoc()) {
            if (password_verify($clave_admin, $row['password'])) {
                $valido = true;
                break;
            }
        }
        $stmt_admin->close();

        if (!$valido) {
            $_SESSION['mensaje'] = [
                'tipo' => 'error',
                'titulo' => 'Clave incorrecta',
                'texto' => 'La contraseña ingresada no corresponde a un administrador.'
            ];
            header("Location: sucursales.php");
            exit;
        }
    }

    if ($idubicacion > 0) {
        $bloqueos = [];

        // Verificar productos
        $productos = $conn->query("SELECT idproducto, nombre FROM producto WHERE ubicacion_id = $idubicacion");
        if ($productos->num_rows > 0) {
            $bloqueos['📦 Productos'] = [];
            while ($p = $productos->fetch_assoc()) {
                $bloqueos['📦 Productos'][] = "{$p['nombre']} (ID {$p['idproducto']})";
            }
        }

        // Verificar activos
        $activos = $conn->query("SELECT idactivo, nombre FROM activos WHERE idubicacion = $idubicacion");
        if ($activos->num_rows > 0) {
            $bloqueos['🛠️ Activos'] = [];
            while ($a = $activos->fetch_assoc()) {
                $bloqueos['🛠️ Activos'][] = "{$a['nombre']} (ID {$a['idactivo']})";
            }
        }

        // Verificar envíos de activos
        $envios = $conn->query("SELECT idenvio FROM envio WHERE ubicacion_id = $idubicacion AND devuelto = 0");
        if ($envios->num_rows > 0) {
            $bloqueos['🚚 Envíos de Activos Pendientes'] = [];
            while ($e = $envios->fetch_assoc()) {
                $bloqueos['🚚 Envíos de Activos Pendientes'][] = "Envío ID {$e['idenvio']}";
            }
        }

        // Verificar envíos de productos
        $envios_prod = $conn->query("SELECT idenvio FROM envio_producto WHERE ubicacion_id = $idubicacion AND devuelto = 0");
        if ($envios_prod->num_rows > 0) {
            $bloqueos['📦 Envíos de Productos Pendientes'] = [];
            while ($ep = $envios_prod->fetch_assoc()) {
                $bloqueos['📦 Envíos de Productos Pendientes'][] = "Envío ID {$ep['idenvio']}";
            }
        }

        if (!empty($bloqueos)) {
            $mensaje = "<strong>No se puede eliminar esta ubicación porque está asociada a los siguientes elementos:</strong><br><br>";
            foreach ($bloqueos as $tipo => $items) {
                $mensaje .= "$tipo:<ul>";
                foreach ($items as $item) {
                    $mensaje .= "<li>$item</li>";
                }
                $mensaje .= "</ul>";
            }

            $_SESSION['mensaje'] = [
                'tipo' => 'error',
                'titulo' => 'Ubicación bloqueada',
                'texto' => $mensaje
            ];
            header("Location: sucursales.php");
            exit;
        }

        // Obtener información antes de eliminar para el historial
        $info = $conn->query("SELECT * FROM ubicaciones WHERE idubicacion = $idubicacion")->fetch_assoc();
        $nombre_antiguo = $info['nombre'] ?? 'Desconocido';
        $descripcion_antigua = $info['descripcion'] ?? '';

        // Eliminar ubicación
        $stmt = $conn->prepare("DELETE FROM ubicaciones WHERE idubicacion = ?");
        $stmt->bind_param("i", $idubicacion);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Guardar en historial
            $accion = "Eliminación";
            $entidad = "ubicaciones";
            $detalle = "Se eliminó la ubicación <strong>$nombre_antiguo</strong> (ID: $idubicacion). Descripción: '$descripcion_antigua'";
            $fecha = date("Y-m-d H:i:s");

            $historial = $conn->prepare("INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                                         VALUES (?, ?, ?, ?, ?, ?)");
            $historial->bind_param("sssssi", $fecha, $accion, $entidad, $idubicacion, $detalle, $usuario_id);
            $historial->execute();
            $historial->close();

            $_SESSION['mensaje'] = [
                'tipo' => 'success',
                'titulo' => 'Ubicación eliminada',
                'texto' => 'La ubicación fue eliminada correctamente.'
            ];
        } else {
            $_SESSION['mensaje'] = [
                'tipo' => 'error',
                'titulo' => 'Error al eliminar',
                'texto' => 'No se pudo eliminar la ubicación. Puede que ya no exista.'
            ];
        }

        $stmt->close();
    } else {
        $_SESSION['mensaje'] = [
            'tipo' => 'error',
            'titulo' => 'ID inválido',
            'texto' => 'El ID de la ubicación no es válido.'
        ];
    }

    header("Location: sucursales.php");
    exit;
}
?>
