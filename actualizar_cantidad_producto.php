<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idproducto = intval($_POST['idproducto'] ?? 0);
    $accion = $_POST['accion'] ?? '';
    $usuario_id = $_SESSION['usuario_id'] ?? null;

    if ($idproducto <= 0 || !in_array($accion, ['aumentar', 'reducir'])) {
        header("Location: index.php");
        exit;
    }

    // Obtener cantidad actual
    $query = $conn->prepare("SELECT cantidad FROM producto WHERE idproducto = ?");
    $query->bind_param("i", $idproducto);
    $query->execute();
    $result = $query->get_result();
    $producto = $result->fetch_assoc();
    $query->close();

    if (!$producto) {
        $_SESSION['mensaje'] = [
            'tipo' => 'error',
            'titulo' => 'Producto no encontrado',
            'texto' => 'No se pudo actualizar la cantidad porque el producto no existe.'
        ];
        header("Location: index.php");
        exit;
    }

    $cantidad_actual = intval($producto['cantidad']);
    $cantidad_nueva = ($accion === 'aumentar') ? $cantidad_actual + 1 : max(0, $cantidad_actual - 1);

    // Actualizar cantidad
    $update = $conn->prepare("UPDATE producto SET cantidad = ? WHERE idproducto = ?");
    $update->bind_param("ii", $cantidad_nueva, $idproducto);
    $update->execute();
    $update->close();

    // Registrar historial
    if ($usuario_id) {
        $fecha = date("Y-m-d H:i:s");
        $accion_hist = $accion === 'aumentar' ? 'Aumento de cantidad' : 'Reducción de cantidad';
        $detalle = "Se {$accion} la cantidad del producto ID $idproducto. Nueva cantidad: $cantidad_nueva.";

        $hist = $conn->prepare("INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                                VALUES (?, ?, 'producto', ?, ?, ?)");
        $hist->bind_param("ssisi", $fecha, $accion_hist, $idproducto, $detalle, $usuario_id);
        $hist->execute();
        $hist->close();
    }

    // ✅ Guardar en sesión y redirigir sin GET
    $_SESSION['cambio'] = $accion; // 'aumentar' o 'reducir'
    header("Location: index.php");
    exit;
}
?>
