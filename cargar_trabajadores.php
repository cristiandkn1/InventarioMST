<?php
include 'db.php'; // Conexión a la base de datos

$sql = "SELECT e.idempleado, e.nombre,
               (SELECT COUNT(*) FROM trabajo_trabajadores tt 
                JOIN trabajo t ON tt.id_trabajo = t.idtrabajo 
                WHERE tt.id_trabajador = e.idempleado AND t.estado != 'Completado') AS asignado
        FROM empleado e";

$result = $conn->query($sql);

echo "<option value=''>Seleccione un trabajador</option>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $asignado = $row['asignado'] > 0 ? 'data-asignado="1"' : '';
        $texto = $row['asignado'] > 0 ? "{$row['nombre']} (Ya asignado)" : $row['nombre'];
        echo "<option value='{$row['idempleado']}' $asignado>$texto</option>";
    }
}

$conn->close();
?>
