<?php
include 'db.php';
session_start();

$id = $_POST['id'];
$nombre = $_POST['nombre'];
$usuario_destino = $_POST['usuario_destino'];

if (!empty($_FILES['archivo']['name'])) {
    $archivo = $_FILES['archivo'];
    $archivo_nombre = time() . "_" . basename($archivo['name']);
    $tmp = $archivo['tmp_name'];
    $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

    $permitidos = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'zip', 'rar'];
    if (!in_array($ext, $permitidos)) {
        die("Tipo de archivo no permitido.");
    }

    move_uploaded_file($tmp, "documentos/$archivo_nombre");

    // Actualiza con archivo nuevo
    $stmt = $conn->prepare("UPDATE documento SET nombre = ?, archivo = ?, tipo = ?, usuario_destino = ? WHERE iddocumento = ?");
    $stmt->bind_param("sssii", $nombre, $archivo_nombre, $ext, $usuario_destino, $id);
} else {
    // Solo nombre y destinatario
    $stmt = $conn->prepare("UPDATE documento SET nombre = ?, usuario_destino = ? WHERE iddocumento = ?");
    $stmt->bind_param("sii", $nombre, $usuario_destino, $id);
}

$stmt->execute();

$_SESSION['mensaje'] = [
    'tipo' => 'success',
    'titulo' => 'Documento actualizado',
    'texto' => 'El documento fue editado correctamente.'
];

header("Location: documentos.php");
exit;
?>