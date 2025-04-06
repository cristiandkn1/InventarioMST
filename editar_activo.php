<?php
include 'db.php';

if (!isset($_GET['id'])) {
    die("ID no proporcionado");
}

$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $categoria_id = intval($_POST['categoria_id']);
    $nro_asignacion = trim($_POST['nro_asignacion']);
    $cantidad = intval($_POST['cantidad']);
    $estado = trim($_POST['estado']);
    $img = $_POST['img_actual']; // imagen actual por defecto

    $directorio = "img/";
    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!empty($_FILES['nueva_img']['name'])) {
        $extension = strtolower(pathinfo($_FILES['nueva_img']['name'], PATHINFO_EXTENSION));
        if (in_array($extension, $permitidos)) {
            $img = time() . "_activo_" . basename($_FILES['nueva_img']['name']);
            $ruta_destino = $directorio . $img;
            move_uploaded_file($_FILES['nueva_img']['tmp_name'], $ruta_destino);
        }
    }

    $sql = "UPDATE activos 
            SET nombre = ?, idcategoria = ?, nro_asignacion = ?, cantidad = ?, estado = ?, img = ?
            WHERE idactivo = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sissssi", $nombre, $categoria_id, $nro_asignacion, $cantidad, $estado, $img, $id);

    if ($stmt->execute()) {
        header("Location: sucursal.php?mensaje=activo_editado");
        exit;
    } else {
        die("Error al actualizar: " . $conn->error);
    }
}

$resultado = $conn->query("SELECT * FROM activos WHERE idactivo = $id");
if (!$resultado || $resultado->num_rows === 0) {
    die("Activo no encontrado");
}
$activo = $resultado->fetch_assoc();

$categorias = $conn->query("SELECT idcategoria, nombre FROM categoria");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Activo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3 class="mb-4">Editar Activo</h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($activo['nombre']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Categoría</label>
            <select name="categoria_id" class="form-select" required>
                <?php while ($cat = $categorias->fetch_assoc()): ?>
                    <option value="<?= $cat['idcategoria'] ?>" <?= $cat['idcategoria'] == $activo['idcategoria'] ? 'selected' : '' ?>>
                        <?= $cat['nombre'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Nº de Asignación</label>
            <input type="text" name="nro_asignacion" class="form-control" value="<?= htmlspecialchars($activo['nro_asignacion']) ?>">
        </div>
        <div class="mb-3">
            <label>Cantidad</label>
            <input type="number" name="cantidad" class="form-control" value="<?= $activo['cantidad'] ?>" min="0" required>
        </div>
        <div class="mb-3">
            <label>Estado</label>
            <select name="estado" class="form-select" required>
                <?php
                $estados = ['Excelente', 'Bueno', 'Regular', 'Deteriorado', 'Inservible'];
                foreach ($estados as $estado):
                ?>
                    <option value="<?= $estado ?>" <?= $estado == $activo['estado'] ? 'selected' : '' ?>>
                        <?= $estado ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Imagen Actual</label><br>
            <?php if (!empty($activo['img'])): ?>
                <img src="img/<?= $activo['img'] ?>" alt="Imagen actual" style="max-width: 200px;">
            <?php else: ?>
                <p>No hay imagen</p>
            <?php endif; ?>
            <input type="hidden" name="img_actual" value="<?= $activo['img'] ?>">
        </div>

        <div class="mb-3">
            <label>Nueva Imagen (opcional)</label>
            <input type="file" name="nueva_img" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif">
        </div>

        <div class="text-center">
            <a href="eliminar_activo_alt.php?id=<?= $activo['idactivo'] ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este activo?')">🗑️ Eliminar</a>
            <a href="sucursal.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </form>
</div>
</body>
</html>
