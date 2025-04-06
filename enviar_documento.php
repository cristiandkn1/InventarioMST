<?php
include 'db.php';
session_start();
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



if (!isset($_GET['id'])) {
    $_SESSION['mensaje'] = [
        'tipo' => 'error',
        'titulo' => 'Error',
        'texto' => 'ID de documento no válido.'
    ];
    header("Location: documentos.php");
    exit;
}

$id = $_GET['id'];

$query = "SELECT * FROM documento WHERE iddocumento = $id";
$result = mysqli_query($conn, $query);
$doc = mysqli_fetch_assoc($result);

if (!$doc) {
    $_SESSION['mensaje'] = [
        'tipo' => 'error',
        'titulo' => 'No encontrado',
        'texto' => 'Documento no encontrado.'
    ];
    header("Location: documentos.php");
    exit;
}

// Si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo_destino = $_POST['correo'];

    $mail = new PHPMailer(true);

    try {
        // Configuración SMTP (ejemplo usando Gmail)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'tucorreo@gmail.com'; // Tu correo Gmail
        $mail->Password = 'tu_contraseña_o_app_password'; // Tu contraseña o app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Configuración del correo
        $mail->setFrom('tucorreo@gmail.com', 'Sistema de Documentos');
        $mail->addAddress($correo_destino);
        $mail->Subject = '📎 Documento: ' . $doc['nombre'];

        $archivo_path = "documentos/" . $doc['archivo'];
        $mail->Body = "Hola, se te ha enviado un documento: <strong>{$doc['nombre']}</strong><br><br>
        Puedes descargarlo también como adjunto.";
        $mail->isHTML(true);
        $mail->addAttachment($archivo_path);

        $mail->send();

        $_SESSION['mensaje'] = [
            'tipo' => 'success',
            'titulo' => 'Correo enviado',
            'texto' => "El documento fue enviado exitosamente a $correo_destino."
        ];
    } catch (Exception $e) {
        $_SESSION['mensaje'] = [
            'tipo' => 'error',
            'titulo' => 'Error al enviar',
            'texto' => 'Mailer Error: ' . $mail->ErrorInfo
        ];
    }

    header("Location: documentos.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Enviar Documento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h3>Enviar documento: <?= $doc['nombre'] ?></h3>

    <form method="POST">
        <div class="mb-3">
            <label for="correo" class="form-label">Correo destinatario:</label>
            <input type="email" name="correo" class="form-control" placeholder="ejemplo@correo.com" required>
        </div>
        <button type="submit" class="btn btn-primary">Enviar</button>
        <a href="documentos.php" class="btn btn-secondary">Cancelar</a>
    </form>
</body>
</html>
