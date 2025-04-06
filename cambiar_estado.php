<?php
include 'db.php';

if (!isset($_GET['tipo'], $_GET['id'])) {
    die("Faltan datos.");
}

$tipo = $_GET['tipo'];
$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_estado = trim($_POST['estado']);

    if ($tipo === 'producto') {
        $sql = "UPDATE producto SET estado = ? WHERE idproducto = ?";
    } elseif ($tipo === 'activo') {
        $sql = "UPDATE activos SET estado = ? WHERE idactivo = ?";
    } else {
        die("Tipo no válido.");
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_estado, $id);

    if ($stmt->execute()) {
        header("Location: sucursal.php?sucursal_id=" . ($_GET['sucursal_id'] ?? ''));
        exit;
    } else {
        die("Error al actualizar estado: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Estado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3 class="mb-4 text-center">Cambiar Estado - <?= ucfirst($tipo) ?> #<?= $id ?></h3>

    <form method="POST">
        <div class="mb-3">
            <label>Nuevo Estado</label>
            <select name="estado" class="form-select" required>
                <option value="">Selecciona estado...</option>
                <option value="Excelente">Excelente</option>
                <option value="Bueno">Bueno</option>
                <option value="Regular">Regular</option>
                <option value="Deteriorado">Deteriorado</option>
                <option value="Inservible">Inservible</option>
            </select>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-success">Guardar Estado</button>
            <a href="sucursal.php?sucursal_id=<?= $_GET['sucursal_id'] ?? '' ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>