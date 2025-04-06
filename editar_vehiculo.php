<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include 'db.php';
date_default_timezone_set('America/Santiago');

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id = intval($_POST['idvehiculo']);
        $nombre = $_POST['nombre'];
        $marca = $_POST['marca'];
        $modelo = $_POST['modelo'];
        $anio = intval($_POST['anio']);
        $precio = floatval($_POST['precio']);
        $estado = $_POST['estado'];
        $descripcion = $_POST['descripcion'];
        $permiso_inicio = $_POST['permiso_inicio'] ?? null;
        $permiso_fin = $_POST['permiso_fin'] ?? null;
        $revision_inicio = $_POST['revision_inicio'] ?? null;
        $revision_fin = $_POST['revision_fin'] ?? null;
        $fecha_cambio_aceite = $_POST['fecha_cambio_aceite'] ?? null;
        $vencimiento_cambio_aceite = $_POST['vencimiento_cambio_aceite'] ?? null;
        $imagen_actual = $_POST['imagen_actual'] ?? null;
        $patente = $_POST['patente']; // ✅ Campo patente
        $detalle = "Se editó el vehículo \"$nombre\" (ID: $id).";



        // -------------------------------
        // Subida de Archivos
        // -------------------------------
        $directorio = "img/";
        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $formatos_imagen = ['jpg', 'jpeg', 'png', 'gif'];
        $formatos_documento = ['pdf', 'jpg', 'jpeg', 'png'];

        function generarNombreArchivo($tipo, $nombre_original) {
            return time() . "_{$tipo}_" . basename($nombre_original);
        }

        function subirArchivo($campo, $directorio, $tipo, $permitidos) {
            if (!empty($_FILES[$campo]['name'])) {
                $nombre_archivo = generarNombreArchivo($tipo, $_FILES[$campo]['name']);
                $ruta = $directorio . $nombre_archivo;
                $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
                if (!in_array($ext, $permitidos)) {
                    echo json_encode(["status" => "error", "message" => "Formato no permitido: $campo"]);
                    exit();
                }
                if (!move_uploaded_file($_FILES[$campo]['tmp_name'], $ruta)) {
                    echo json_encode(["status" => "error", "message" => "Error al subir archivo: $campo"]);
                    exit();
                }
                return $nombre_archivo;
            }
            return null;
        }

        $img_nueva = subirArchivo("editar_img", $directorio, "imagen", $formatos_imagen);
        $permiso_circulacion_nuevo = subirArchivo("editar_permiso_circulacion", $directorio, "permiso", $formatos_documento);
        $revision_tecnica_nuevo = subirArchivo("editar_revision_tecnica", $directorio, "revision", $formatos_documento);

        $img_final = $img_nueva ?? $imagen_actual;

        // -------------------------------
        // Actualización en base de datos
        // -------------------------------
        $sql = "UPDATE vehiculo 
                SET nombre=?, patente=?, marca=?, modelo=?, anio=?, precio=?, estado=?, descripcion=?, 
                    permiso_inicio=?, permiso_fin=?, revision_inicio=?, revision_fin=?, 
                    fecha_cambio_aceite=?, vencimiento_cambio_aceite=?, img=?";


        // Agregar campos condicionalmente
        if ($permiso_circulacion_nuevo) {
            $sql .= ", permiso_circulacion='$permiso_circulacion_nuevo'";
        }
        if ($revision_tecnica_nuevo) {
            $sql .= ", revision_tecnica='$revision_tecnica_nuevo'";
        }

        $sql .= " WHERE idvehiculo=?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(["status" => "error", "message" => "Error en la preparación del SQL: " . $conn->error]);
            exit();
        }

        $stmt->bind_param(
            "ssssissssssssssi",
            $nombre, $patente, $marca, $modelo, $anio, $precio, $estado, $descripcion,
            $permiso_inicio, $permiso_fin, $revision_inicio, $revision_fin,
            $fecha_cambio_aceite, $vencimiento_cambio_aceite, $img_final, $id
        );
        



        $fecha_hora = date("Y-m-d H:i:s");
        $accion = "Edición de Vehículo";
        $detalle = "Se editó el vehículo \"$nombre\" (ID: $id).";
        $usuario_id = 1; // Cambiar por el ID real del usuario si usas sesiones
        
        $sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                          VALUES (?, ?, 'vehiculo', ?, ?, ?)";
        $stmt_historial = $conn->prepare($sql_historial);
        $stmt_historial->bind_param("ssisi", $fecha_hora, $accion, $id, $detalle, $usuario_id);
        $stmt_historial->execute();
        $stmt_historial->close();
        















        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al actualizar: " . $stmt->error]);
        }

        $stmt->close();
        $conn->close();
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Excepción: " . $e->getMessage()]);
}
?>
