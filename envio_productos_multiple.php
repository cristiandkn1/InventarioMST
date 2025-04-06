<?php
session_start();
include 'db.php';
date_default_timezone_set('America/Santiago');

// ✅ Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// ✅ Saber si el usuario es administrador
$esAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador';

// ✅ Obtener productos
$productos = $conn->query("
  SELECT 
    idproducto, 
    nombre, 
    cantidad - COALESCE(en_uso, 0) AS disponibles 
  FROM producto 
  ORDER BY nombre ASC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Envío Múltiple de Productos</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

</head>
<body>
<?php if (isset($_SESSION['mensaje'])): ?>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      Swal.fire({
        icon: '<?= $_SESSION['mensaje']['tipo'] ?>',
        title: '<?= $_SESSION['mensaje']['titulo'] ?>',
        text: '<?= $_SESSION['mensaje']['texto'] ?>'
      });
    });
  </script>
  <?php unset($_SESSION['mensaje']); ?>
<?php endif; ?>
 





<div class="container mt-5">
  <h3 class="mb-4 text-center">📦 Envío Múltiple de Productos</h3>

  <!-- Formulario completo -->
  <form id="formEnvioProductos" action="procesar_envio_masivo.php" method="POST">
  
    <!-- Select Ubicación -->
    <div class="mb-4">
      <label for="ubicacion_destino" class="form-label">Ubicación destino:</label>
      <select name="ubicacion_destino" id="ubicacion_destino" class="form-select select2" required>
        <option value="">Seleccione ubicación</option>
        <?php
        $ubicaciones = $conn->query("SELECT idubicacion, nombre FROM ubicaciones ORDER BY nombre ASC");
        while ($u = $ubicaciones->fetch_assoc()) {
            echo "<option value='{$u['idubicacion']}'>{$u['nombre']}</option>";
        }
        ?>
      </select>
    </div>

    <!-- Input Encargado -->
    <div class="mb-4">
      <label for="encargado" class="form-label">Nombre del encargado:</label>
      <input type="text" name="encargado" id="encargado" class="form-control" placeholder="Nombre completo" required>
    </div>

    <!-- Select Producto -->
<div class="mb-4">
  <label for="select_producto" class="form-label">Producto:</label>
  <select id="select_producto" class="form-select select2">
    <option value="">Seleccione un producto</option>
    <?php
    $productos = $conn->query("
      SELECT idproducto, nombre, cantidad - COALESCE(en_uso, 0) AS disponibles 
      FROM producto 
      WHERE eliminado != 1 
      ORDER BY nombre ASC
    ");
    while ($p = $productos->fetch_assoc()) {
        $id = (int)$p['idproducto'];
        $nombre = $p['nombre'];
        $disp = (int)$p['disponibles'];
        echo "<option value=\"$id\" data-disponibles=\"$disp\">#$id - $nombre (disponibles: $disp)</option>";
    }
    ?>
  </select>
</div>

    <!-- Botón para agregar producto -->
    <div class="mb-3 text-end">
      <button type="button" id="btnAgregarProducto" class="btn btn-outline-success">
        <i class="fas fa-plus"></i> Agregar producto
      </button>
    </div>

    <!-- Tabla de productos agregados -->
    <div class="table-responsive mb-4">
      <table class="table table-bordered" id="tablaProductosSeleccionados">
        <thead class="table-light">
          <tr>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Disponibles</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <!-- ✅ Botón de enviar -->
    <a href="index.php" class="btn btn-secondary me-2">Volver</a>
    <div class="text-center">
<!-- Botón Guardar Envío -->
<?php if ($_SESSION['rol'] === 'Administrador'): ?>
    <button type="submit" class="btn btn-success">Guardar Envío</button>
<?php else: ?>
    <button type="button" class="btn btn-success" onclick="pedirPasswordAdmin()">Guardar Envío</button>
<?php endif; ?>

<!-- Campo oculto para clave administrador si es necesario -->
<?php if ($_SESSION['rol'] !== 'Administrador'): ?>
    <input type="hidden" name="clave_admin" id="clave_admin_input">
<?php endif; ?>    </div>
  </form>
</div>


<!-- Modal para ingresar contraseña de administrador -->
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
        alert("Por favor, ingresa la contraseña del administrador.");
        return;
    }
    document.getElementById('clave_admin_input').value = clave;
    document.querySelector('form').submit();
}
</script>

<script>
$(document).ready(function () {
  // Confirmación con SweetAlert al enviar
  $('#formEnvioProductos').on('submit', function (e) {
    e.preventDefault(); // detenemos envío

    // Validar que haya productos agregados
    if ($('#tablaProductosSeleccionados tbody tr').length === 0) {
      Swal.fire("⚠️ Sin productos", "Debes agregar al menos un producto al envío.", "warning");
      return;
    }

    Swal.fire({
      title: '¿Guardar envío?',
      text: "Se registrará el movimiento de productos.",
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, guardar',
      cancelButtonText: 'Cancelar',
    }).then((result) => {
      if (result.isConfirmed) {
        this.submit(); // ahora sí enviamos el formulario
      }
    });
  });
});
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- jQuery (necesario para DOMContentLoaded) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<audio id="popSound" src="pop.mp3"></audio>


<!-- jQuery, Select2 y Bootstrap Scripts -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const sound = document.getElementById("popSound");
sound.volume = 0.3; // Volumen reducido

$(document).on("click", ".btn-sumar", function () {
  const input = $(this).siblings('input[type="number"]');
  const max = parseInt(input.attr('max'));
  let val = parseInt(input.val());

  if (val < max) {
    input.val(val + 1);
    sound.currentTime = 0;
    sound.play();
    Swal.fire({
      toast: true,
      position: 'bottom',
      icon: 'success',
      title: '✅ Se sumó 1 unidad',
      background: '#28a745',  // Verde fuerte
      color: '#ffffff',
      showConfirmButton: false,
      timer: 1200
    });
  }
});

$(document).on("click", ".btn-restar", function () {
  const input = $(this).siblings('input[type="number"]');
  let val = parseInt(input.val());

  if (val > 1) {
    input.val(val - 1);
    sound.currentTime = 0;
    sound.play();
    Swal.fire({
      toast: true,
      position: 'bottom',
      icon: 'info',
      title: '➖ Se restó 1 unidad',
      background: '#17a2b8',
      color: '#ffffff',
      showConfirmButton: false,
      timer: 1200
    });
  }
});
</script>



<script>
$(document).ready(function () {
  $('#select_producto').select2();
  $('#ubicacion_destino').select2();

  $('#btnAgregarProducto').on('click', function () {
    const select = $('#select_producto');
    const id = select.val();
    const nombre = select.find('option:selected').text();
    const disponibles = parseInt(select.find('option:selected').data('disponibles')) || 0;

    if (!id || $('#fila-prod-' + id).length || disponibles <= 0) return;

    const fila = `
      <tr id="fila-prod-${id}">
        <td>
          <input type="hidden" name="productos[${id}][id]" value="${id}">
          ${nombre}
        </td>
        <td class="text-center">
          <div class="d-flex justify-content-center align-items-center gap-1">
            <button type="button" class="btn btn-sm btn-outline-secondary btn-restar">-</button>
            <input type="number" name="productos[${id}][cantidad]" value="1" min="1" max="${disponibles}" class="form-control text-center" style="width: 60px;">
            <button type="button" class="btn btn-sm btn-outline-secondary btn-sumar">+</button>
          </div>
        </td>
        <td class="text-center align-middle">${disponibles}</td>
        <td class="text-center">
          <button type="button" class="btn btn-sm btn-danger btnEliminarFila" data-id="${id}">Eliminar</button>
        </td>
      </tr>
    `;

    $('#tablaProductosSeleccionados tbody').append(fila);
    select.find(`option[value="${id}"]`).prop('disabled', true);
    select.val(null).trigger('change');
  });

  // eliminar fila
  $(document).on('click', '.btnEliminarFila', function () {
    const id = $(this).data('id');
    $(`#fila-prod-${id}`).remove();
    $('#select_producto option[value="' + id + '"]').prop('disabled', false);
  });

  
});
</script>

<!-- Inicialización de Select2 -->
<script>
$(document).ready(function () {
  $('#ubicacion_destino').select2();
  $('#select_producto').select2();
});
</script>




</body>
</html>
