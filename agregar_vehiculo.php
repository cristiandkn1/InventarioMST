<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include 'db.php';
date_default_timezone_set('America/Santiago');

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nombre = $_POST['nombre'];
        $patente = $_POST['patente'];
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

        // Manejo de archivos
        $directorio = "img/";
        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $formatos_imagen = ['jpg', 'jpeg', 'png', 'gif'];
        $formatos_documento = ['pdf', 'jpg', 'jpeg', 'png'];

        function generarNombreArchivo($tipo, $original) {
            return time() . "_{$tipo}_" . basename($original);
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

        $img = subirArchivo('img', $directorio, 'imagen', $formatos_imagen);
        $permiso_circulacion = subirArchivo('permiso_circulacion', $directorio, 'permiso', $formatos_documento);
        $revision_tecnica = subirArchivo('revision_tecnica', $directorio, 'revision', $formatos_documento);

        // Insertar en base de datos
        $sql = "INSERT INTO vehiculo (
                    nombre, patente, marca, modelo, anio, precio, estado, descripcion,
                    permiso_inicio, permiso_fin, revision_inicio, revision_fin,
                    fecha_cambio_aceite, vencimiento_cambio_aceite, img,
                    permiso_circulacion, revision_tecnica, fecha_registro
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(["status" => "error", "message" => "Error en la preparación del SQL: " . $conn->error]);
            exit();
        }

        $stmt->bind_param(
            "ssssdsdssssssssss",
            $nombre, $patente, $marca, $modelo, $anio, $precio, $estado, $descripcion,
            $permiso_inicio, $permiso_fin, $revision_inicio, $revision_fin,
            $fecha_cambio_aceite, $vencimiento_cambio_aceite, $img,
            $permiso_circulacion, $revision_tecnica
        );




















        if ($stmt->execute()) {

            $nuevo_id = $stmt->insert_id; // ✅ DEBE ir después del execute()

                // ✅ Guardar en historial
                $fecha_hora = date("Y-m-d H:i:s");
                $accion = "Creación de Vehículo";
                $detalle = "Se agregó el vehículo \"$nombre\" con patente \"$patente\".";
                $usuario_id = 1; // ⚠️ Ajusta esto con el ID del usuario actual si estás usando sesión

                $sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                                VALUES (?, ?, 'vehiculo', ?, ?, ?)";
                $stmt_historial = $conn->prepare($sql_historial);
                $stmt_historial->bind_param("ssisi", $fecha_hora, $accion, $nuevo_id, $detalle, $usuario_id);
                $stmt_historial->execute();
                $stmt_historial->close();




            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al insertar: " . $stmt->error]);
        }

        $stmt->close();
        $conn->close();
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Excepción: " . $e->getMessage()]);
}
?>
