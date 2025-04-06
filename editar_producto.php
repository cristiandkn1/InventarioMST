<?php
session_start();
include 'db.php';

// ✅ Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Verificar si se recibe el ID del producto
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>
            alert('ID de producto no válido.');
            window.location.href='index.php';
          </script>";
    exit();
}

$idproducto = intval($_GET['id']); 

// ✅ Obtener datos del producto
$sql = "SELECT idproducto, nombre, precio, estado, descripcion, cantidad, nro_asignacion, categoria_id 
        FROM producto 
        WHERE idproducto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idproducto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>
            alert('Producto no encontrado.');
            window.location.href='index.php';
          </script>";
    exit();
}

$producto = $result->fetch_assoc();
$stmt->close();

// ✅ Obtener categorías para el select
$sql_categorias = "SELECT * FROM categoria"; // Se cambió de "categorias" a "categoria"
$result_categorias = $conn->query($sql_categorias);

if (!$result_categorias) {
    die("Error al obtener categorías: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="container mt-4">
    <h2 class="text-center">Editar Producto</h2>

    <form id="formPrincipal" action="guardar_edicion.php" method="POST">
        <input type="hidden" name="idproducto" value="<?php echo $producto['idproducto']; ?>">
        <input type="hidden" name="return_url" value="<?= isset($_GET['return_url']) ? htmlspecialchars($_GET['return_url']) : '' ?>">

        <?php if ($_SESSION['rol'] !== 'Administrador'): ?>
            <input type="hidden" name="clave_admin" id="clave_admin_input">
        <?php endif; ?>

        <div class="mb-3">
            <label>Nombre del Producto:</label>
            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars_decode($producto['nombre'], ENT_QUOTES); ?>" required>
        </div>

        <div class="mb-3">
            <label>Precio:</label>
            <input type="number" step="0.01" name="precio" class="form-control" value="<?php echo $producto['precio']; ?>" required>
        </div>

        <div class="mb-3">
            <label>Estado:</label>
            <select name="estado" class="form-control">
                <option value="Excelente" <?php echo ($producto['estado'] == 'Excelente') ? 'selected' : ''; ?>>Excelente</option>
                <option value="Bueno" <?php echo ($producto['estado'] == 'Bueno') ? 'selected' : ''; ?>>Bueno</option>
                <option value="Regular" <?php echo ($producto['estado'] == 'Regular') ? 'selected' : ''; ?>>Regular</option>
                <option value="Deteriorado" <?php echo ($producto['estado'] == 'Deteriorado') ? 'selected' : ''; ?>>Deteriorado</option>
                <option value="Inservible" <?php echo ($producto['estado'] == 'Inservible') ? 'selected' : ''; ?>>Inservible</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Categoría:</label>
            <select name="categoria_id" class="form-control">
                <?php while ($categoria = $result_categorias->fetch_assoc()): ?>
                    <option value="<?php echo $categoria['idcategoria']; ?>" <?php echo ($producto['categoria_id'] == $categoria['idcategoria']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Descripción:</label>
            <textarea name="descripcion" class="form-control"><?php echo htmlspecialchars_decode($producto['descripcion'], ENT_QUOTES); ?></textarea>
        </div>

        <div class="mb-3">
            <label>Cantidad:</label>
            <input type="number" name="cantidad" class="form-control" value="<?php echo $producto['cantidad']; ?>" required>
        </div>

        <div class="mb-3">
            <label>Nro Asignación:</label>
            <input type="text" name="nro_asignacion" class="form-control" value="<?php echo $producto['nro_asignacion']; ?>">
        </div>

        <div class="d-flex justify-content-between">
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador'): ?>
                <a href="eliminar_producto.php?id=<?= $producto['idproducto']; ?>&return_url=<?= urlencode($_GET['return_url'] ?? 'index.php') ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este producto?');">Eliminar</a>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <?php else: ?>
                <button type="button" class="btn btn-primary" onclick="pedirPasswordAdmin()">Guardar Cambios</button>
            <?php endif; ?>
        </div>
    </form>

    <!-- Modal de confirmación para usuarios no administradores -->
    <div class="modal fade" id="modalAdminPassword" tabindex="-1" aria-labelledby="modalAdminPasswordLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmación de administrador</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label>Contraseña de un administrador:</label>
              <input type="password" id="modalClaveAdmin" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-success" onclick="confirmarClaveAdmin()">Confirmar y Guardar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          </div>
        </div>
      </div>
    </div>


<script>
function pedirPasswordAdmin() {
    const modal = new bootstrap.Modal(document.getElementById('modalAdminPassword'));
    modal.show();
}

function confirmarClaveAdmin() {
    const clave = document.getElementById('modalClaveAdmin').value;

    if (!clave) {
        alert("Por favor, ingresa la contraseña.");
        return;
    }

    // Asignar la clave al input oculto del formulario principal
    document.getElementById('clave_admin_input').value = clave;

    // Enviar el formulario principal
    document.getElementById('formPrincipal').submit();
}
</script>

<!-- jQuery (necesario para Bootstrap) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS con Popper incluido -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>