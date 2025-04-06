<?php
session_start();

include 'db.php';
date_default_timezone_set('America/Santiago');

if (isset($_GET['rehacer']) && isset($_GET['idenvio'])) {
    $idenvio = intval($_GET['idenvio']);

    // Limpiar los campos de los productos del envío
    $conn->query("UPDATE envio_producto_detalle 
                  SET cantidad_devuelta = 0, cantidad_usada = 0, cantidad_perdida = 0, fecha_devolucion = NULL 
                  WHERE envio_id = $idenvio");

    // Marcar el envío como no devuelto
    $conn->query("UPDATE envio_producto SET devuelto = 0 WHERE idenvio = $idenvio");

    // Redirigir nuevamente al formulario limpio
    header("Location: procesar_devolucion.php?idenvio=$idenvio");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['idenvio'])) {
        die("ID de envío no proporcionado.");
    }

    $idenvio = intval($_GET['idenvio']);

    // Obtener datos del envío
    $envio = $conn->query("SELECT * FROM envio_producto WHERE idenvio = $idenvio")->fetch_assoc();
    if (!$envio) {
        die("Envío no encontrado.");
    }

    $ya_devuelto = $envio['devuelto'] == 1;

    // Obtener productos del envío
    $detalles = $conn->query("
        SELECT d.*, p.nombre 
        FROM envio_producto_detalle d
        JOIN producto p ON d.producto_id = p.idproducto
        WHERE d.envio_id = $idenvio
    ");
}
?>

<?php if ($_SERVER['REQUEST_METHOD'] === 'GET'): ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Devolución</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="container mt-5">
    <h3 class="mb-4 text-center">Devolución - Envío #<?= $envio['idenvio'] ?></h3>

    <form method="POST" action="procesar_devolucion.php">
        <input type="hidden" name="envio_id" value="<?= $envio['idenvio'] ?>">

        <?php if (!$ya_devuelto): ?>
        <div class="mb-3 text-end">
            <button type="button" class="btn btn-outline-success btn-sm" onclick="marcarTodoDevuelto()">
                Marcar todo como devuelto
            </button>
        </div>
        <?php endif; ?>

        <table class="table table-bordered text-center align-middle">
            <thead class="table-light">
                <tr>
                    <th>Producto</th>
                    <th>Enviado</th>
                    <th>Devuelto</th>
                    <th>Usado</th>
                    <th>Perdido</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $detalles->fetch_assoc()): 
                    $max = $row['cantidad_enviada'];
                ?>
                <tr>
                    <td><?= $row['nombre'] ?></td>
                    <td><?= $row['cantidad_enviada'] ?></td>
                    <td>
                        <input type="number" name="detalle[<?= $row['iddetalle'] ?>][devuelto]" class="form-control"
                               min="0" max="<?= $max ?>"
                               value="<?= $row['cantidad_devuelta'] ?>"
                               <?= $ya_devuelto ? 'readonly' : '' ?>>
                    </td>
                    <td>
                        <input type="number" name="detalle[<?= $row['iddetalle'] ?>][usado]" class="form-control"
                               min="0" max="<?= $max ?>"
                               value="<?= isset($row['cantidad_usada']) ? $row['cantidad_usada'] : 0 ?>"
                               <?= $ya_devuelto ? 'readonly' : '' ?>>
                    </td>
                    <td>
                        <input type="number" name="detalle[<?= $row['iddetalle'] ?>][perdido]" class="form-control"
                               min="0" max="<?= $max ?>"
                               value="<?= isset($row['cantidad_perdida']) ? $row['cantidad_perdida'] : 0 ?>"
                               <?= $ya_devuelto ? 'readonly' : '' ?>>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="text-center">
            
            <?php if (!$ya_devuelto): ?>
                <button type="submit" class="btn btn-success">Guardar Devolución</button>
            <?php else: ?>
                <div class="alert alert-info">Este envío ya fue marcado como devuelto. No puedes modificarlo.</div>
            <?php endif; ?>
            <a href="devolucion_productos.php" class="btn btn-secondary">Volver</a>
        </div>
    </form>
    <?php if ($ya_devuelto): ?>
    <div class="text-center mt-3">
        <a href="procesar_devolucion.php?idenvio=<?= $envio['idenvio'] ?>&rehacer=1" class="btn btn-danger">
            Rehacer Devolución
        </a>
    </div>
<?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmarRehacer(idenvio) {
    Swal.fire({
        title: '¿Rehacer devolución?',
        text: 'Esto borrará todos los datos devueltos, usados y perdidos de este envío. ¿Deseas continuar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, rehacer',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `procesar_devolucion.php?idenvio=${idenvio}&rehacer=1`;
        }
    });
}
</script>
<script>
function marcarTodoDevuelto() {
    document.querySelectorAll('tbody tr').forEach(row => {
        const enviado = parseInt(row.querySelector('td:nth-child(2)')?.textContent || 0);
        if (enviado > 0) {
            const inputs = row.querySelectorAll('input');
            if (inputs.length === 3) {
                inputs[0].value = enviado; // devuelto
                inputs[1].value = 0;        // usado
                inputs[2].value = 0;        // perdido
            }
        }
    });
}
</script>

</body>
</html>
<?php endif; ?>

<?php
// Proceso POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $envio_id = intval($_POST['envio_id']);

    // Evitar doble registro si ya fue devuelto
    $checkEnvio = $conn->query("SELECT devuelto FROM envio_producto WHERE idenvio = $envio_id")->fetch_assoc();
    if ($checkEnvio['devuelto'] == 1) {
        echo "<script>
            Swal.fire({
                title: 'No permitido',
                text: 'Este envío ya fue devuelto completamente.',
                icon: 'info',
                confirmButtonText: 'Volver'
            }).then(() => {
                window.location.href = 'devolucion_productos.php';
            });
        </script>";
        exit;
    }

    $detalles = $_POST['detalle'];
    $errores = [];

    foreach ($detalles as $iddetalle => $valores) {
        $devuelto = isset($valores['devuelto']) && $valores['devuelto'] !== '' ? intval($valores['devuelto']) : 0;
        $usado    = isset($valores['usado'])    && $valores['usado']    !== '' ? intval($valores['usado'])    : 0;
        $perdido  = isset($valores['perdido'])  && $valores['perdido']  !== '' ? intval($valores['perdido'])  : 0;
    
        // Obtener información actual
        $q = $conn->query("SELECT cantidad_enviada, producto_id FROM envio_producto_detalle WHERE iddetalle = $iddetalle");
        $row = $q->fetch_assoc();
        $enviada = intval($row['cantidad_enviada']);
        $idproducto = intval($row['producto_id']);
    
        $total = $devuelto + $usado + $perdido;
        if ($total > $enviada) {
            $errores[] = "Detalle #$iddetalle: la suma excede la cantidad enviada ($enviada)";
            continue;
        }
    
        // 1️⃣ Actualizar el detalle
        $conn->query("
            UPDATE envio_producto_detalle
            SET 
                cantidad_devuelta = $devuelto,
                cantidad_usada = $usado,
                cantidad_perdida = $perdido,
                fecha_devolucion = NOW()
            WHERE iddetalle = $iddetalle
        ");
    
        // 2️⃣ Ajustar stock del producto
        // Obtener valores actuales
        $p = $conn->query("SELECT cantidad, en_uso FROM producto WHERE idproducto = $idproducto")->fetch_assoc();
        $stock_actual = $p['cantidad'];
        $en_uso_actual = $p['en_uso'];
    
        // Restar lo usado/perdido del stock total
        $nueva_cantidad = $stock_actual - ($usado + $perdido);
    
        // Restar lo devuelto del "en uso"
        $nueva_en_uso = $en_uso_actual - ($devuelto + $usado + $perdido);
    
        // Recalcular disponibles
        $nueva_disponible = $nueva_cantidad - $nueva_en_uso;
        if ($nueva_en_uso < 0) $nueva_en_uso = 0;
        if ($nueva_disponible < 0) $nueva_disponible = 0;
    
        // Actualizar producto
        $conn->query("
            UPDATE producto 
            SET 
                cantidad = $nueva_cantidad,
                en_uso = $nueva_en_uso,
                disponibles = $nueva_disponible
            WHERE idproducto = $idproducto
        ");
    }

    // Verificar si todo fue tratado
    $check = $conn->query("
        SELECT COUNT(*) as pendientes
        FROM envio_producto_detalle
        WHERE envio_id = $envio_id
        AND (cantidad_devuelta + cantidad_usada + cantidad_perdida) < cantidad_enviada
    ");
    if ($check->fetch_assoc()['pendientes'] == 0) {
        $conn->query("UPDATE envio_producto SET devuelto = 1 WHERE idenvio = $envio_id");
    }

    if (empty($errores)) {
        // ✅ Registrar en historial
        if (isset($_SESSION['usuario_id'])) {
            $usuario_id = $_SESSION['usuario_id'];
            $fecha = date("Y-m-d H:i:s");
            $accion = "Registro de Devolución de Productos";
            $entidad = "Envio Producto";
            $entidad_id = $envio_id;

            // Recopilar los detalles de los productos devueltos
            $detalle_log = [];
            foreach ($detalles as $iddetalle => $valores) {
                $dev = intval($valores['devuelto'] ?? 0);
                $usa = intval($valores['usado'] ?? 0);
                $per = intval($valores['perdido'] ?? 0);
                $detalle_log[] = "Detalle ID $iddetalle → Devueltos=$dev, Usados=$usa, Perdidos=$per";
            }

            $detalle = "Se registró una devolución para el envío ID $envio_id con los siguientes detalles: " . implode("; ", $detalle_log);

            $stmt = $conn->prepare("INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $fecha, $accion, $entidad, $entidad_id, $detalle, $usuario_id);
            $stmt->execute();
            $stmt->close();
        }

        // Redirigir después del registro
        header("Location: devolucion_productos.php?ok=1");
        exit;
    } else {
        $mensaje = implode('\n', $errores);
        echo "<script>
            Swal.fire({
                title: 'Error en la devolución',
                text: '$mensaje',
                icon: 'error',
                confirmButtonText: 'Volver'
            }).then(() => {
                window.location.href = 'devolucion_productos.php';
            });
        </script>";
    }
}
?>
