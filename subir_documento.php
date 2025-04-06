<?php
include 'db.php';
session_start();

$usuario_origen = $_SESSION['usuario_id'];
$usuario_destino = $_POST['usuario_destino'];

$nombre = $_POST['nombre']; // nombre personalizado
$archivo = $_FILES['archivo'];
$archivo_original = $archivo['name'];
$tmp = $archivo['tmp_name'];

$permitidos = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'zip', 'rar'];
$ext = strtolower(pathinfo($archivo_original, PATHINFO_EXTENSION));

if (!in_array($ext, $permitidos)) {
    die("Tipo de archivo no permitido.");
}

$archivo_nombre = time() . "_" . basename($archivo_original);
$destino = "documentos/$archivo_nombre";

if (move_uploaded_file($tmp, $destino)) {
    $stmt = $conn->prepare("INSERT INTO documento (nombre, archivo, tipo, usuario_origen, usuario_destino) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $nombre, $archivo_nombre, $ext, $usuario_origen, $usuario_destino);
    $stmt->execute();

    $_SESSION['mensaje'] = [
        'tipo' => 'success',
        'titulo' => 'Documento subido',
        'texto' => 'El documento fue enviado correctamente.'
    ];

    header("Location: documentos.php");
    exit;
} else {
    $_SESSION['mensaje'] = [
        'tipo' => 'error',
        'titulo' => 'Error',
        'texto' => 'No se pudo subir el documento.'
    ];
    header("Location: documentos.php");
    exit;
}
?>
