<?php
include 'db.php';

$iddetalle = intval($_POST['iddetalle']);
$cantidad_devuelta = intval($_POST['cantidad_devuelta']);

// Obtener la cantidad enviada para validación
$sql = "SELECT cantidad_enviada FROM envio_producto_detalle WHERE iddetalle = $iddetalle";
$res = $conn->query($sql);
$data = $res->fetch_assoc();

if ($cantidad_devuelta > $data['cantidad_enviada']) {
    die("Error: no puedes devolver más de lo enviado.");
}

// Actualizar la cantidad devuelta y la fecha
$update = "UPDATE envio_producto_detalle 
           SET cantidad_devuelta = $cantidad_devuelta, fecha_devolucion = NOW() 
           WHERE iddetalle = $iddetalle";

if ($conn->query($update)) {
    header("Location: index.php?devolucion=ok");
} else {
    echo "Error al registrar la devolución: " . $conn->error;
}
