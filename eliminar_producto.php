<?php
session_start();
include 'db.php';
date_default_timezone_set('America/Santiago');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>
            alert('ID de producto inválido.');
            window.location.href='index.php';
          </script>";
    exit();
}

$return_url = $_GET['return_url'] ?? 'index.php';

$idproducto = intval($_GET['id']);

// Obtener el nombre del producto
$sql_select = "SELECT nombre FROM producto WHERE idproducto = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("i", $idproducto);
$stmt_select->execute();
$result = $stmt_select->get_result();
$producto = $result->fetch_assoc();
$stmt_select->close();

if (!$producto) {
    echo "<script>
            alert('Producto no encontrado.');
            window.location.href='$return_url';
          </script>";
    exit();
}

$nombre_producto = $producto['nombre'];

// Marcar como eliminado
$sql = "UPDATE producto SET eliminado = 1 WHERE idproducto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idproducto);

if ($stmt->execute()) {
    $usuario_id = $_SESSION['usuario_id'] ?? 1;
    $accion = "Eliminación lógica de Producto";
    $detalle = "El producto '$nombre_producto' (ID: $idproducto) fue marcado como eliminado.";
    $fecha_hora = date("Y-m-d H:i:s");

    $sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                      VALUES (?, ?, 'producto', ?, ?, ?)";
    $stmt_historial = $conn->prepare($sql_historial);
    $stmt_historial->bind_param("ssisi", $fecha_hora, $accion, $idproducto, $detalle, $usuario_id);
    $stmt_historial->execute();
    $stmt_historial->close();

    echo "<script>
            alert('Producto marcado como eliminado.');
            window.location.href='$return_url';
          </script>";
} else {
    echo "Error al marcar como eliminado: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
