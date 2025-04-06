<?php
include 'db.php';

$id = intval($_POST['idtrabajo']);

$sql = "SELECT estado FROM trabajo WHERE idtrabajo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(["estado" => $row['estado']]);
} else {
    echo json_encode(["estado" => null]);
}
