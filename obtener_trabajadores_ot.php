<?php
include 'db.php';

$trabajo_id = intval($_GET['trabajo_id']); 

$sql = "SELECT t.id, t.nombre, tt.cargo AS rol
        FROM trabajadores t
        JOIN trabajos_trabajadores tt ON t.id = tt.trabajador_id
        WHERE tt.trabajo_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $trabajo_id);
$stmt->execute();
$result = $stmt->get_result();

$trabajadores = [];
while ($row = $result->fetch_assoc()) {
    $trabajadores[] = $row;
}

echo json_encode($trabajadores);
?>
