
<?php
include 'db.php';
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Sucursales</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container mt-5">
  <h3 class="mb-4 text-center">🏢 Gestión de Sucursales</h3>

  <!-- Botón para agregar ubicación -->
  <div class="mb-3 text-end">
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarUbicacion">
      + Nueva Ubicación
    </button>
  </div>

  <!-- Tabla de ubicaciones -->
  <div class="table-responsive">
    <table class="table table-bordered text-center">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $res = $conn->query("SELECT * FROM ubicaciones ORDER BY idubicacion DESC");
        while ($row = $res->fetch_assoc()):
        ?>
        <tr>
          <td><?= $row['idubicacion'] ?></td>
          <td><?= htmlspecialchars($row['nombre']) ?></td>
          <td><?= htmlspecialchars($row['descripcion']) ?></td>
          <td>
  <!-- Botón Editar -->
  <button class="btn btn-sm btn-warning btnEditar" 
          data-id="<?= $row['idubicacion'] ?>"
          data-nombre="<?= htmlspecialchars($row['nombre']) ?>"
          data-descripcion="<?= htmlspecialchars($row['descripcion']) ?>"
          data-bs-toggle="modal" data-bs-target="#modalEditarUbicacion">
    Editar
  </button>

  <!-- Botón Eliminar (con validación de rol) -->
<?php if ($_SESSION['rol'] === 'Administrador'): ?>
  <form action="eliminar_ubicacion.php" method="POST" class="d-inline-block formEliminarUbicacion">
    <input type="hidden" name="idubicacion" value="<?= $row['idubicacion'] ?>">
    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
  </form>
<?php else: ?>
  <!-- Si el rol no es Administrador, se pide autenticación -->
  <button type="button" class="btn btn-sm btn-danger" onclick="confirmarEliminacion(<?= $row['idubicacion'] ?>)">Eliminar</button>
<?php endif; ?>
<!-- Modal para Confirmar Eliminación con clave admin -->
<div class="modal fade" id="modalClaveEliminar" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="eliminar_ubicacion.php" class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Confirmar Eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="idubicacion" id="inputEliminarId">
        <div class="mb-3">
          <label>Contraseña de un administrador:</label>
          <input type="password" name="clave_admin" class="form-control" required>
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
function confirmarEliminacion(idubicacion) {
  document.getElementById('inputEliminarId').value = idubicacion;
  const modal = new bootstrap.Modal(document.getElementById('modalClaveEliminar'));
  modal.show();
}
</script>


</td>

        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<!-- Modal Agregar Ubicación -->
<div class="modal fade" id="modalAgregarUbicacion" tabindex="-1">
  <div class="modal-dialog">
    <form id="formAgregarUbicacion" action="agregar_ubicacion.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Agregar Ubicación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="nombre" class="form-label">Nombre:</label>
          <input type="text" name="nombre" id="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="descripcion" class="form-label">Descripción:</label>
          <textarea name="descripcion" id="descripcion" class="form-control"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <?php if ($_SESSION['rol'] === 'Administrador'): ?>
          <button type="submit" class="btn btn-success">Guardar</button>
        <?php else: ?>
          <button type="button" class="btn btn-success" onclick="abrirModalAdmin()">Guardar</button>
        <?php endif; ?>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Clave Admin -->
<div class="modal fade" id="modalClaveAdmin" tabindex="-1" aria-labelledby="modalClaveAdminLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="agregar_ubicacion.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmación de administrador</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="nombre" id="nombreHidden">
        <input type="hidden" name="descripcion" id="descripcionHidden">
        <div class="mb-3">
          <label>Contraseña de un administrador:</label>
          <input type="password" name="clave_admin" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Confirmar y Guardar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
function abrirModalAdmin() {
  document.getElementById('nombreHidden').value = document.getElementById('nombre').value;
  document.getElementById('descripcionHidden').value = document.getElementById('descripcion').value;
  const modal = new bootstrap.Modal(document.getElementById('modalClaveAdmin'));
  modal.show();
}
</script>











<!-- Modal Editar Ubicación -->
<div class="modal fade" id="modalEditarUbicacion" tabindex="-1">
  <div class="modal-dialog">
    <form action="editar_ubicacion.php" method="POST" class="modal-content">
      <input type="hidden" name="idubicacion" id="editar_idubicacion">
      <input type="hidden" name="accion" id="accion_editar" value="actualizar">

      <div class="modal-header bg-warning">
        <h5 class="modal-title">Editar Ubicación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label for="editar_nombre" class="form-label">Nombre:</label>
          <input type="text" name="nombre" id="editar_nombre" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="editar_descripcion" class="form-label">Descripción:</label>
          <textarea name="descripcion" id="editar_descripcion" class="form-control"></textarea>
        </div>
      </div>

      <div class="modal-footer d-flex justify-content-between">
        
        <div>
        <?php if ($_SESSION['rol'] === 'Administrador'): ?>
  <button type="submit" class="btn btn-warning" onclick="document.getElementById('accion_editar').value='actualizar'">Actualizar</button>
<?php else: ?>
  <button type="button" class="btn btn-warning" onclick="pedirClaveAdminEditar()">Actualizar</button>
<?php endif; ?>

          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal de clave admin para editar ubicación -->
<div class="modal fade" id="modalClaveAdminEditar" tabindex="-1" aria-labelledby="modalClaveAdminEditarLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formClaveAdminEditar" method="POST" action="editar_ubicacion.php" class="modal-content">
      <input type="hidden" name="idubicacion" id="editar_idubicacion_hidden">
      <input type="hidden" name="nombre" id="editar_nombre_hidden">
      <input type="hidden" name="descripcion" id="editar_descripcion_hidden">
      <input type="hidden" name="accion" value="actualizar">

      <div class="modal-header">
        <h5 class="modal-title">Confirmación de administrador</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label>Contraseña de administrador:</label>
        <input type="password" name="clave_admin" class="form-control" required>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Confirmar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
function pedirClaveAdminEditar() {
  // Copia los valores actuales del modal de edición al modal de clave
  document.getElementById('editar_idubicacion_hidden').value = document.getElementById('editar_idubicacion').value;
  document.getElementById('editar_nombre_hidden').value = document.getElementById('editar_nombre').value;
  document.getElementById('editar_descripcion_hidden').value = document.getElementById('editar_descripcion').value;

  const modal = new bootstrap.Modal(document.getElementById('modalClaveAdminEditar'));
  modal.show();
}
</script>





<!-- Separador -->
<div style="width: 200px; height: 140px;"></div>

<!-- Botón Volver al Inicio (centrado) -->
<div class="mb-4 text-center">
  <a href="index.php" class="btn btn-secondary">
    ← Volver al Inicio
  </a>
</div>




<script>
$(document).on('click', '.btnEditar, .btnEliminar', function () {
  const id = $(this).data('id');
  const nombre = $(this).data('nombre');
  const descripcion = $(this).data('descripcion');

  $('#editar_idubicacion').val(id);
  $('#editar_nombre').val(nombre);
  $('#editar_descripcion').val(descripcion);

  // Si es botón de eliminar, preseleccionamos la acción eliminar
  if ($(this).hasClass('btnEliminar')) {
    $('#accion_editar').val('eliminar');
  } else {
    $('#accion_editar').val('actualizar');
  }
});
</script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).on('click', '.btnEliminarUbicacion', function (e) {
  e.preventDefault();

  const form = $(this).closest('form');

  Swal.fire({
    title: '¿Estás seguro?',
    text: "Esta acción eliminará la ubicación permanentemente.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonText: 'Cancelar',
    confirmButtonText: 'Sí, eliminar'
  }).then((result) => {
    if (result.isConfirmed) {
      form.submit(); // enviar el formulario si el usuario confirma
    }
  });
});
</script>
<?php if (!empty($_SESSION['mensaje'])): ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      Swal.fire({
        icon: '<?= $_SESSION['mensaje']['tipo'] ?>',
        title: <?= json_encode($_SESSION['mensaje']['titulo']) ?>,
        html: <?= json_encode($_SESSION['mensaje']['texto']) ?>,
        confirmButtonText: 'OK'
      });
    });
  </script>
  <?php unset($_SESSION['mensaje']); ?>
<?php endif; ?>



</body>
</html>
