<?php
include 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idactivo'])) {
    $id = intval($_POST['idactivo']);

    if ($id <= 0) {
        echo json_encode(["status" => "error", "message" => "ID inválido."]);
        exit;
    }

    $sql = "SELECT 
                a.idactivo, 
                a.nombre, 
                a.idcategoria, 
                c.nombre AS categoria,
                a.idubicacion, 
                u.nombre AS ubicacion,
                a.nro_asignacion, 
                a.estado, 
                a.descripcion, 
                a.cantidad, 
                a.img
            FROM activos a
            LEFT JOIN categoria c ON a.idcategoria = c.idcategoria
            LEFT JOIN ubicaciones u ON a.idubicacion = u.idubicacion
            WHERE a.idactivo = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($activo = $resultado->fetch_assoc()) {
            // Aseguramos que se devuelvan strings para evitar problemas con JS
            $activo['idcategoria'] = (string)$activo['idcategoria'];
            $activo['idubicacion'] = (string)$activo['idubicacion'];

            echo json_encode(["status" => "success", "activo" => $activo]);
        } else {
            echo json_encode(["status" => "error", "message" => "Activo no encontrado."]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Error SQL: " . $conn->error]);
    }

    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Petición no válida."]);
}
?>
