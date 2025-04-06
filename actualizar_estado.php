<?php
include 'db.php';
session_start();
date_default_timezone_set('America/Santiago');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trabajo_id = intval($_POST['trabajo_id']);
    $nuevo_estado = $_POST['nuevo_estado'];

    // Verificar que el estado sea válido
    $estados_validos = ["Pendiente", "En Proceso", "Completado"];
    if (!in_array($nuevo_estado, $estados_validos)) {
        die("Estado inválido.");
    }

    // ✅ Obtener el estado actual antes de actualizar
    $sql_estado_actual = "SELECT estado FROM trabajo WHERE idtrabajo = ?";
    $stmt_estado_actual = $conn->prepare($sql_estado_actual);
    $stmt_estado_actual->bind_param("i", $trabajo_id);
    $stmt_estado_actual->execute();
    $result_estado_actual = $stmt_estado_actual->get_result();
    $estado_anterior = ($result_estado_actual->num_rows > 0) ? $result_estado_actual->fetch_assoc()['estado'] : "Desconocido";
    $stmt_estado_actual->close();

    // ✅ Actualizar el estado en la base de datos
    $sql = "UPDATE trabajo SET estado = ? WHERE idtrabajo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_estado, $trabajo_id);

    if ($stmt->execute()) {
        // ✅ Guardar en el historial la actualización del estado
        $sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id) 
                          VALUES (NOW(), ?, ?, ?, ?, ?)";

        $stmt_historial = $conn->prepare($sql_historial);

        if (!$stmt_historial) {
            die("Error en la preparación de la consulta del historial: " . $conn->error);
        }

        // 🔹 Obtener el usuario actual
        $usuario_id = $_SESSION['usuario_id'] ?? 0; // Obtiene el ID del usuario si está en sesión
        $accion = "Actualización";
        $entidad = "trabajo";
        $detalle = "El estado del trabajo (ID: $trabajo_id) cambió de '$estado_anterior' a '$nuevo_estado'";

        $stmt_historial->bind_param("ssisi", $accion, $entidad, $trabajo_id, $detalle, $usuario_id);
        $stmt_historial->execute();
        $stmt_historial->close();

        // ✅ Redirigir con mensaje de éxito
        echo "<script>
                alert('Estado actualizado correctamente.');
                window.location.href = 'ver_trabajo.php?id=$trabajo_id';
              </script>";
    } else {
        echo "Error al actualizar el estado: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>