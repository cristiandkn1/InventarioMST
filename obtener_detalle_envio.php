<?php
include 'db.php';
session_start();
date_default_timezone_set('America/Santiago');

$envio_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Obtener datos del envío
$sql_envio = "SELECT e.fecha, e.estado_devolucion, e.observacion, e.devuelto, u.nombre AS ubicacion
              FROM envio e
              JOIN ubicaciones u ON e.ubicacion_id = u.idubicacion
              WHERE e.idenvio = ?";
$stmt_envio = $conn->prepare($sql_envio);
$stmt_envio->bind_param("i", $envio_id);
$stmt_envio->execute();
$result_envio = $stmt_envio->get_result();
$envio = $result_envio->fetch_assoc();
$stmt_envio->close();

if (!$envio) {
    echo "<div class='alert alert-danger'>❌ Envío no encontrado.</div>";
    exit;
}

// Obtener detalles de los activos enviados
$sql_detalles = "SELECT a.idactivo, a.nombre, ed.cantidad_enviada
                 FROM envio_detalle ed
                 JOIN activos a ON ed.activo_id = a.idactivo
                 WHERE ed.envio_id = ?";
$stmt_detalles = $conn->prepare($sql_detalles);
$stmt_detalles->bind_param("i", $envio_id);
$stmt_detalles->execute();
$result_detalles = $stmt_detalles->get_result();
$activos = $result_detalles->fetch_all(MYSQLI_ASSOC);
$stmt_detalles->close();

// Obtener cantidades devueltas por activo
$sql_devueltas = "SELECT activo_id, SUM(cantidad_devuelta) AS total_devuelta
                  FROM envio_devolucion
                  WHERE envio_id = ?
                  GROUP BY activo_id";
$stmt_devueltas = $conn->prepare($sql_devueltas);
$stmt_devueltas->bind_param("i", $envio_id);
$stmt_devueltas->execute();
$result_devueltas = $stmt_devueltas->get_result();

$devoluciones = [];
while ($row = $result_devueltas->fetch_assoc()) {
    $devoluciones[$row['activo_id']] = (int)$row['total_devuelta'];
}
$stmt_devueltas->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Devolución de Envío</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="bg-light">


<div class="container mt-4">
  <h3 class="mb-4">📦 Devolución del Envío #<?= $envio_id ?></h3>

  <p><strong>Ubicación destino:</strong> <?= htmlspecialchars($envio['ubicacion']) ?></p>
  <p><strong>Fecha de envío:</strong> <?= $envio['fecha'] ?></p>

  <?php if ($envio['devuelto'] == 1): ?>
    <div class="alert alert-info">ℹ️ Este envío ya fue devuelto. Solo puedes visualizar la información.</div>
  <?php endif; ?>

  <form action="procesar_devolucion_envios.php" method="POST">
    <input type="hidden" name="envio_id" value="<?= $envio_id ?>">

    <div class="table-responsive mb-4">
      <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
          <tr>
            <th>Activo</th>
            <th>Cantidad Enviada</th>
            <th>Cantidad Devuelta</th>
            <th>Usada/Perdida</th>
            <th>Cantidad a Devolver</th>
            <th>Marcar como Usado/Perdido</th>

          </tr>
        </thead>
        <tbody>
          <?php foreach ($activos as $activo): ?>
            <?php
              $devuelta = $devoluciones[$activo['idactivo']] ?? 0;
              $usada_perdida = $activo['cantidad_enviada'] - $devuelta;
              $cantidad_restante = $activo['cantidad_enviada'] - $devuelta;
              $bloquear = ($cantidad_restante <= 0 || $envio['devuelto'] == 1);
            ?>
            <tr>
              <td><?= htmlspecialchars($activo['nombre']) ?></td>
              <td><?= $activo['cantidad_enviada'] ?></td>
              <td><?= $devuelta ?></td>
              <td><?= $usada_perdida ?></td>
              <td>
                <input type="number"
                       name="devoluciones[<?= $activo['idactivo'] ?>]"
                       min="0"
                       max="<?= $cantidad_restante ?>"
                       value="0"
                       class="form-control"
                       <?= $bloquear ? 'disabled' : '' ?>>
              </td>
              <td>
  <?php if ($cantidad_restante > 0 && $envio['devuelto'] != 1): ?>
    <input type="checkbox" name="perdidos[<?= $activo['idactivo'] ?>]" value="1">
  <?php else: ?>
    <span class="text-muted">-</span>
  <?php endif; ?>
</td>

            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="mb-3">
      <label for="observacion" class="form-label">Observación (opcional):</label>
      <textarea name="observacion"
                id="observacion"
                class="form-control"
                rows="3"
                <?= ($envio['devuelto'] == 1) ? 'readonly' : '' ?>><?= htmlspecialchars($envio['observacion'] ?? '') ?></textarea>
    </div>

    <div class="text-end">
      <button type="submit" class="btn btn-success" <?= ($envio['devuelto'] == 1) ? 'disabled' : '' ?>>Registrar Devolución</button>
      <a href="activos.php" class="btn btn-secondary">Volver</a>
    </div>
  </form>

  <?php if ($envio['devuelto'] == 1): ?>
    <form action="rehacer_devolucion_envio.php" method="POST" class="text-end mt-2" onsubmit="return confirm('¿Estás seguro de rehacer la devolución? Esto ajustará el stock y permitirá registrar una nueva devolución.');">
      <input type="hidden" name="envio_id" value="<?= $envio_id ?>">
      <button type="submit" class="btn btn-danger">Rehacer Devolución</button>
    </form>
  <?php endif; ?>
</div>


<?php if (isset($_SESSION['mensaje'])): ?>
  <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
    <div id="toastMensaje" class="toast align-items-center text-white bg-<?= $_SESSION['mensaje']['tipo'] == 'success' ? 'success' : 'danger' ?> border-0 show" role="alert">
      <div class="d-flex">
        <div class="toast-body">
          <strong><?= $_SESSION['mensaje']['titulo'] ?>:</strong> <?= $_SESSION['mensaje']['texto'] ?>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>
  <?php unset($_SESSION['mensaje']); ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



</body>
</html>
