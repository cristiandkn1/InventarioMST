<?php
session_start();

include 'db.php';
header('Content-Type: application/json');
date_default_timezone_set('America/Santiago');


if ($_SESSION['rol'] !== 'Administrador' && isset($_POST['from_auth_modal'])) {
    $clave_admin = $_POST['clave_admin'] ?? '';

    // Validar clave contra usuarios admin
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

    if (!$validado) {
        $_SESSION['mensaje'] = [
            'tipo' => 'error',
            'titulo' => 'Clave incorrecta',
            'texto' => 'La contraseña ingresada no corresponde a un administrador.'
        ];
        echo json_encode([
            "status" => "error",
            "message" => "La contraseña ingresada no corresponde a un administrador.",
            "redirect" => "activos.php"
        ]);
        exit;
        
    }
}

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nombre = $_POST['nombre'];
        $idcategoria = intval($_POST['idcategoria']);
        $idubicacion = intval($_POST['idubicacion']);
        $nro_asignacion = $_POST['nro_asignacion'] ?? null;
        $estado = $_POST['estado'] ?? 'Disponible';
        $descripcion = $_POST['descripcion'] ?? null;
        $cantidad = isset($_POST['cantidad']) && intval($_POST['cantidad']) > 0 ? intval($_POST['cantidad']) : 1;

        // ✅ Manejo de imagen
        $img = null;
        $directorio = "img/";
        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!empty($_FILES['img']['name'])) {
            $extension = strtolower(pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $permitidos)) {
                echo json_encode(["status" => "error", "message" => "Formato de imagen no permitido."]);
                exit();
            }

            $img = time() . "_activo_" . basename($_FILES['img']['name']);
            $ruta_destino = $directorio . $img;

            if (!move_uploaded_file($_FILES['img']['tmp_name'], $ruta_destino)) {
                echo json_encode(["status" => "error", "message" => "No se pudo guardar la imagen en el servidor."]);
                exit();
            }
        }

       // ✅ Insertar
$sql = "INSERT INTO activos (nombre, idcategoria, idubicacion, nro_asignacion, estado, descripcion, cantidad, img)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Error SQL: " . $conn->error]);
    exit();
}

$stmt->bind_param("siisssis", $nombre, $idcategoria, $idubicacion, $nro_asignacion, $estado, $descripcion, $cantidad, $img);

if ($stmt->execute()) {

    // ✅ Guardar registro en historial al agregar un activo
    $fecha_hora = date("Y-m-d H:i:s");
    $accion = "Creación";
    $entidad = "activo";
    $entidad_id = $conn->insert_id; // El id generado del nuevo activo
    $detalle = "Se agregó un nuevo activo \"$nombre\" (ID: $entidad_id)";
    $usuario_id = $_SESSION['usuario_id'] ?? 1;

    $sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                      VALUES (?, ?, ?, ?, ?, ?)";

    $stmt_historial = $conn->prepare($sql_historial);
    $stmt_historial->bind_param("sssisi", $fecha_hora, $accion, $entidad, $entidad_id, $detalle, $usuario_id);

    if (!$stmt_historial->execute()) {
        echo json_encode(["status" => "error", "message" => "Error al guardar historial: " . $stmt_historial->error]);
        exit;
    }

    $stmt_historial->close();

    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error al guardar: " . $stmt->error]);
}

$stmt->close();
$conn->close();
        
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Excepción: " . $e->getMessage()]);
}
?>
