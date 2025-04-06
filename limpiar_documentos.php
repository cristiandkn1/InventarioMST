<?php
include 'db.php';

// Buscar documentos que han sido eliminados por ambos usuarios
$sql = "SELECT iddocumento, archivo FROM documento 
        WHERE eliminado_origen = 1 AND eliminado_destino = 1";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $id = $row['iddocumento'];
    $archivo = $row['archivo'];
    $ruta = "documentos/" . $archivo;

    // Eliminar archivo físico si existe
    if (file_exists($ruta)) {
        unlink($ruta);
    }

    // Borrar registro de la base de datos
    $conn->query("DELETE FROM documento WHERE iddocumento = $id");
}

echo "Limpieza completada.";
?>
