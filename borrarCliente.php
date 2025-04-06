<?php
include 'db.php';
session_start();
date_default_timezone_set('America/Santiago');

// ✅ Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Verificar si se recibe el ID del cliente
$idcliente = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($idcliente <= 0) {
    echo "<script>
            alert('ID de cliente no válido.');
            window.location.href='clientes.php';
          </script>";
    exit();
}

// ✅ Obtener el cliente antes de eliminarlo
$sql = "SELECT * FROM cliente WHERE idcliente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idcliente);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>
            alert('Cliente no encontrado.');
            window.location.href='clientes.php';
          </script>";
    exit();
}

$cliente = $result->fetch_assoc();
$stmt->close();

// ✅ Eliminar el cliente
$sql_delete = "DELETE FROM cliente WHERE idcliente = ?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("i", $idcliente);

if ($stmt_delete->execute()) {
    // ✅ Registrar en historial
    $usuario_id = $_SESSION['usuario_id'];
    $fecha = date('Y-m-d H:i:s');
    $detalle = "Se eliminó el cliente '{$cliente['nombre']}' (Empresa: '{$cliente['empresa']}').";

    $sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                      VALUES (?, 'Eliminación Cliente', 'cliente', ?, ?, ?)";
    $stmt_historial = $conn->prepare($sql_historial);
    $stmt_historial->bind_param("sisi", $fecha, $idcliente, $detalle, $usuario_id);
    $stmt_historial->execute();
    $stmt_historial->close();

    echo "<script>
            alert('Cliente eliminado correctamente.');
            window.location.href='clientes.php';
          </script>";
} else {
    echo "<script>
            alert('No se pudo eliminar el cliente.');
            window.location.href='clientes.php';
          </script>";
}

// ✅ Cerrar conexiones
$stmt_delete->close();
$conn->close();
?>
