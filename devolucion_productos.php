<?php
session_start();

include 'db.php';


// Obtener todos los envíos
$sql = "SELECT e.idenvio, e.fecha FROM envio_producto e ORDER BY e.fecha DESC";
$envios = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Devolución de Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php if (isset($_SESSION['mensaje'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        icon: '<?= $_SESSION['mensaje']['tipo'] ?>',
        title: '<?= $_SESSION['mensaje']['titulo'] ?>',
        html: '<?= $_SESSION['mensaje']['texto'] ?>'
    });
</script>
<?php unset($_SESSION['mensaje']); ?>
<?php endif; ?>

<!-- Incluye estos scripts en el <head> o antes de cerrar </body> -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>



<style>
    /* 📱 Estilo compacto para móviles */
@media (max-width: 578px) {
  #tablaDevoluciones {
    font-size: 11px;
  }

  #tablaDevoluciones th,
  #tablaDevoluciones td {
    padding: 4px 6px;
  }

  #tablaDevoluciones .btn {
    font-size: 10px;
    padding: 3px 6px;
  }
}
</style>







<div class="container mt-5">
    <h3 class="mb-4 text-center">Envíos de Productos</h3>
    <table id="tablaDevoluciones" class="table table-bordered text-center align-middle">
    <thead class="table-dark">
            <tr>
                <th>ID Envío</th>
                <th>Fecha</th>
                <th>Productos Enviados/ ID</th>
                <th>Total Enviado</th>
                <th>Total Devuelto</th>
                <th>Total Usado</th>
                <th>Total Perdido</th>
                <th>Valor Devuelto</th>
                <th>Valor Perdido</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
    <?php while ($envio = $envios->fetch_assoc()): ?>
        <?php
        $detalle_sql = "SELECT d.*, p.nombre, p.precio 
                        FROM envio_producto_detalle d
                        JOIN producto p ON d.producto_id = p.idproducto
                        WHERE d.envio_id = " . $envio['idenvio'];
        $detalles = $conn->query($detalle_sql);

        // Totales por envío
        $total_enviado = 0;
        $total_devuelto = 0;
        $total_usado = 0;
        $total_perdido = 0;
        $valor_devuelto = 0;
        $valor_perdido = 0;
        $todos_tratados = true;
        $productos_ids = array();

        while ($detalle = $detalles->fetch_assoc()) {
            $enviado = $detalle['cantidad_enviada'];
            $devuelto = $detalle['cantidad_devuelta'];
            $usado = isset($detalle['cantidad_usada']) ? $detalle['cantidad_usada'] : 0;
            $perdido = isset($detalle['cantidad_perdida']) ? $detalle['cantidad_perdida'] : 0;
            $precio = $detalle['precio'];

            $total_enviado += $enviado;
            $total_devuelto += $devuelto;
            $total_usado += $usado;
            $total_perdido += $perdido;
            
            // Cálculo de valores
            $valor_devuelto += $devuelto * $precio;
            $valor_perdido += ($usado + $perdido) * $precio;

            // Agregar ID del producto al array
            $productos_ids[] = $detalle['producto_id'];

            if (($devuelto + $usado + $perdido) < $enviado) {
                $todos_tratados = false;
            }
        }
        
        // Eliminar IDs duplicados y ordenar
        $productos_ids = array_unique($productos_ids);
        sort($productos_ids);
        ?>
        <tr>
            <td><?= $envio['idenvio'] ?></td>
            <td><?= $envio['fecha'] ?></td>
            <td><?= implode(', ', $productos_ids) ?></td>
            <td><?= $total_enviado ?></td>
            <td><?= $total_devuelto ?></td>
            <td><?= $total_usado ?></td>
            <td><?= $total_perdido ?></td>
            <td>$<?= number_format($valor_devuelto, 0, '', '') ?></td>
            <td>$<?= number_format($valor_perdido, 0, '', '') ?></td>
            <td>
                <?php if ($todos_tratados): ?>
                    <span class="badge bg-success">Devuelto</span>
                    <div class="text-muted small">🔒 Bloqueado</div>
                <?php else: ?>
                    <span class="badge bg-warning text-dark">Pendiente</span>
                <?php endif; ?>
            </td>
            <td>
    <?php if ($_SESSION['rol'] === 'Administrador'): ?>
        <a href="procesar_devolucion.php?idenvio=<?= $envio['idenvio'] ?>" class="btn btn-sm btn-primary">Devolver</a>
        <?php if ($todos_tratados): ?>
            <form method="POST" action="eliminar_envioP.php" onsubmit="return confirm('¿Estás seguro de eliminar este envío? Esta acción no se puede deshacer.');">
                <input type="hidden" name="idenvio" value="<?= $envio['idenvio'] ?>">
                <button type="submit" class="btn btn-sm btn-danger mt-1">Eliminar</button>
            </form>
        <?php else: ?>
            <button type="button" class="btn btn-sm btn-secondary mt-1" disabled title="Debes devolver los productos antes de eliminar el envío.">
                No disponible
            </button>
        <?php endif; ?>
    <?php else: ?>
        <button type="button" class="btn btn-sm btn-primary" onclick="abrirModalClaveAdmin(<?= $envio['idenvio'] ?>)">Devolver</button>
        <button type="button" class="btn btn-sm btn-danger mt-1" onclick="abrirModalEliminar(<?= $envio['idenvio'] ?>, <?= $todos_tratados ? 'true' : 'false' ?>)">Eliminar</button>
    <?php endif; ?>
</td>
        </tr>
    <?php endwhile; ?>
</tbody>
    </table>
</div>

<!-- Modal Clave Admin para Eliminar -->
<div class="modal fade" id="modalEliminarEnvio" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="eliminar_envioP.php" class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Confirmar eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="idenvio" id="modal_idenvio_eliminar">
        <div class="mb-3">
          <label>Contraseña de un administrador:</label>
          <input type="password" name="clave_admin" class="form-control" required>
        </div>
        <div class="alert alert-warning small mt-2">
          Esta acción eliminará permanentemente el envío y no se puede deshacer.
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">Eliminar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
function abrirModalEliminar(idenvio, estaDevuelto) {
    if (!estaDevuelto) {
        Swal.fire({
            icon: 'warning',
            title: 'Envío no devuelto',
            text: 'No puedes eliminar un envío que aún está pendiente. Primero registra la devolución.',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    document.getElementById('modal_idenvio_eliminar').value = idenvio;
    const modal = new bootstrap.Modal(document.getElementById('modalEliminarEnvio'));
    modal.show();
}
</script>


<!-- Modal Clave de Administrador -->
<div class="modal fade" id="modalClaveAdmin" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="validar_clave_devolucion.php" class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Validación de administrador</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="idenvio" id="modal_idenvio">
        <div class="mb-3">
          <label>Contraseña de un administrador:</label>
          <input type="password" name="clave_admin" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Validar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
function abrirModalClaveAdmin(idenvio) {
    document.getElementById('modal_idenvio').value = idenvio;
    const modal = new bootstrap.Modal(document.getElementById('modalClaveAdmin'));
    modal.show();
}
</script>
<div class="d-flex flex-column align-items-center mb-4">
    <!-- 🔷 Div superior con ancho fijo y margen inferior -->
    <div style="width: 400px; margin-bottom: 200px;">
        <!-- Aquí podrías poner un logo, texto, etc. -->
    </div>

    <!-- 🔹 Botón centrado -->
    <div>
        <a href="index.php" class="btn btn-outline-primary">
            ← Volver al Inicio
        </a>
    </div>
</div>






























<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    // Inicialización de DataTables
    $(document).ready(function () {
        $('#tablaDevoluciones').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
        });
    });

    // Mostrar alerta si se eliminó correctamente
    <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
    Swal.fire({
        title: '¡Envío eliminado!',
        text: 'Los productos han sido restaurados correctamente.',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    });
    <?php endif; ?>

    // Mostrar alerta si se registró una devolución
    <?php if (isset($_GET['ok']) && $_GET['ok'] == 1): ?>
    Swal.fire({
        title: '¡Devolución registrada!',
        text: 'La devolución del envío se guardó correctamente.',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    });
    <?php endif; ?>

    // Validación previa a mostrar el modal de eliminación
    function abrirModalEliminar(idenvio, estaDevuelto) {
        if (!estaDevuelto) {
            Swal.fire({
                icon: 'warning',
                title: 'Envío no devuelto',
                text: 'No puedes eliminar un envío que aún está pendiente. Primero registra la devolución.',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        document.getElementById('modal_idenvio_eliminar').value = idenvio;
        const modal = new bootstrap.Modal(document.getElementById('modalEliminarEnvio'));
        modal.show();
    }

    // Mostrar modal de contraseña admin
    function abrirModalClaveAdmin(idenvio) {
        document.getElementById('modal_idenvio').value = idenvio;
        const modal = new bootstrap.Modal(document.getElementById('modalClaveAdmin'));
        modal.show();
    }
</script>


</body>
</html>