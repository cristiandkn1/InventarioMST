<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['idvehiculo'])) {
    $vehiculo_id = intval($_POST['idvehiculo']); // ✅ Cambio de 'id' a 'idvehiculo'

    $sql = "SELECT * FROM vehiculo WHERE idvehiculo = ?"; // ✅ Cambio en la consulta SQL
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Error en la preparación de la consulta."]);
        exit;
    }

    $stmt->bind_param("i", $vehiculo_id); // ✅ Cambio en el bind_param
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $vehiculo = $result->fetch_assoc();

        // ✅ Formatear el precio correctamente antes de enviarlo
        $vehiculo['precio'] = "$" . number_format($vehiculo['precio'], 0, ',', '.');

        echo json_encode(["status" => "success", "vehiculo" => $vehiculo]);
    } else {
        echo json_encode(["status" => "error", "message" => "Vehículo no encontrado"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "ID no proporcionado o método incorrecto."]);
}
?>
