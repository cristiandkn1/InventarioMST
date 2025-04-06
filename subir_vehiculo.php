<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header('Content-Type: application/json'); // 📌 Asegura que la respuesta es JSON válido

    $nombre = $_POST['nombre'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $anio = intval($_POST['anio']);
    $precio = floatval($_POST['precio']);
    $permiso_inicio = $_POST['permiso_inicio'];
    $permiso_fin = $_POST['permiso_fin'];
    $revision_inicio = $_POST['revision_inicio'];
    $revision_fin = $_POST['revision_fin'];
    $estado = $_POST['estado'];
    $descripcion = $_POST['descripcion'];
    $ultima_mantencion = !empty($_POST['ultima_mantencion']) ? $_POST['ultima_mantencion'] : null;
    $fecha_actual = date("Y-m-d H:i:s");

    $img_nombre = NULL;

    // 🔹 Subir imagen si se ha seleccionado una
    if (!empty($_FILES['img']['name'])) {
        $directorio = "uploads/";
        $img_nombre = time() . "_" . basename($_FILES["img"]["name"]);
        $ruta_destino = $directorio . $img_nombre;
        $tipo_imagen = strtolower(pathinfo($ruta_destino, PATHINFO_EXTENSION));

        $formatos_permitidos = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($tipo_imagen, $formatos_permitidos)) {
            echo json_encode(["status" => "error", "message" => "Formato de imagen no permitido."]);
            exit();
        }

        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        if (!move_uploaded_file($_FILES["img"]["tmp_name"], $ruta_destino)) {
            echo json_encode(["status" => "error", "message" => "Error al subir la imagen."]);
            exit();
        }
    }

    // 🔹 Insertar datos en la base de datos
    $sql = "INSERT INTO vehiculo 
            (nombre, marca, modelo, anio, precio, estado, descripcion, fecha_registro, img, 
             ultima_mantencion, permiso_inicio, permiso_fin, revision_inicio, revision_fin) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Error en la consulta SQL: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("sssissssssssss", 
        $nombre, $marca, $modelo, $anio, $precio, $estado, $descripcion, $fecha_actual, 
        $img_nombre, $ultima_mantencion, $permiso_inicio, $permiso_fin, $revision_inicio, $revision_fin
    );

    if (!$stmt->execute()) {
        echo json_encode(["status" => "error", "message" => "Error al guardar: " . $stmt->error]);
        exit();
    }

    // 🔹 Obtener el ID del nuevo vehículo
    $vehiculo_id = $stmt->insert_id;

    // 🔹 Devolver JSON con los datos para actualizar la página sin recargar
    echo json_encode([
        "status" => "success",
        "message" => "Vehículo agregado correctamente.",
        "vehiculo" => [
            "id" => $vehiculo_id,
            "nombre" => $nombre,
            "marca" => $marca,
            "modelo" => $modelo,
            "anio" => $anio,
            "precio" => $precio,
            "estado" => $estado,
            "descripcion" => $descripcion,
            "img" => $img_nombre ? "uploads/" . $img_nombre : "img/no-image.png",
            "permiso_inicio" => $permiso_inicio,
            "permiso_fin" => $permiso_fin,
            "revision_inicio" => $revision_inicio,
            "revision_fin" => $revision_fin,
            "ultima_mantencion" => $ultima_mantencion
        ]
    ]);

    $stmt->close();
    $conn->close();
}
?>
