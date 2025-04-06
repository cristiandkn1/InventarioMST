<?php
session_start(); // ✅ Inicia sesión primero

include 'db.php';

// Traer los activos con su categoría y ubicación
$sql = "SELECT a.*, c.nombre AS categoria, u.nombre AS ubicacion 
        FROM activos a
        LEFT JOIN categoria c ON a.idcategoria = c.idcategoria
        LEFT JOIN ubicaciones u ON a.idubicacion = u.idubicacion
        ORDER BY a.fecha_registro DESC";
$result = $conn->query($sql);


// Traer categorías
$categorias = [];
$resCat = $conn->query("SELECT idcategoria, nombre FROM categoria ORDER BY nombre ASC");
if ($resCat && $resCat->num_rows > 0) {
    while ($row = $resCat->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// Traer ubicaciones
$ubicaciones = [];
$resUbi = $conn->query("SELECT idubicacion, nombre FROM ubicaciones ORDER BY nombre ASC");
if ($resUbi && $resUbi->num_rows > 0) {
    while ($row = $resUbi->fetch_assoc()) {
        $ubicaciones[] = $row;
    }
}

?>

<?php
if (isset($_SESSION['mensaje'])) {
  echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
          Swal.fire({
              icon: '{$_SESSION['mensaje']['tipo']}',
              title: '{$_SESSION['mensaje']['titulo']}',
              text: '{$_SESSION['mensaje']['texto']}'
          });
      });
  </script>";
  unset($_SESSION['mensaje']);
}
// Verificar si el usuario NO está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redirige al login
    exit(); // Detiene la ejecución del script
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Activos Físicos</title>

  <!-- ✅ Bootstrap CSS (solo uno) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- ✅ Tu CSS personalizado (después de Bootstrap) -->
  <link rel="stylesheet" href="css/style.css">
 <!-- ✅ Select2 CSS y JS -->
 <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <!-- ✅ jQuery primero (necesario para Bootstrap y otros plugins) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- ✅ Bootstrap JS con Popper (después de jQuery) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- ✅ SweetAlert2 CSS y JS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- ✅ Select2 CSS y JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Activos</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav">

                <!-- ✅ Dropdown Productos -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="index.php" id="inventarioDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Productos
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="inventarioDropdown">
                        <li><a class="dropdown-item" href="index.php">Inventario</a></li>
                        <li><a class="dropdown-item" href="activos.php">Activos Físicos</a></li>
                    </ul>
                </li>

                <!-- ✅ Dropdown Trabajos -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="trabajosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Trabajos
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="trabajosDropdown">
                        <li><a class="dropdown-item" href="trabajos.php">Ver Trabajos</a></li>
                        <li><a class="dropdown-item" href="clientes.php">Clientes</a></li>
                        <li><a class="dropdown-item" href="devoluciones.php">Devoluciones</a></li>
                        <li><a class="dropdown-item" href="ordenesCompra.php">Ordenes Compra</a></li>

                    </ul>
                </li>

                  <!-- ✅ Dropdown Personal -->
                  <li class="nav-item dropdown">
                      <a class="nav-link dropdown-toggle" href="#" id="personalDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                          Personal
                      </a>
                      <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="personalDropdown">
                          <li><a class="dropdown-item" href="empleados.php">Empleados</a></li>
                          <li><a class="dropdown-item" href="usuarios.php">Usuarios</a></li>
                      </ul>
                  </li>
                <!-- 🔹 Resto del menú -->
                <li class="nav-item"><a class="nav-link" href="vehiculos.php">Vehículos</a></li>
                <li class="nav-item"><a class="nav-link" href="sucursal.php">Sucursales</a></li>
                <li class="nav-item"><a class="nav-link" href="documentos.php">Documentos</a></li>
                <li class="nav-item"><a class="nav-link" href="historial.php">Historial</a></li>
                
            </ul>

            <div class="logout-container ms-auto">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <a href="logout.php" class="btn btn-danger btn-sm">Cerrar Sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>


<!-- Botón para abrir el modal -->
<div class="text-end mb-3">
  <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarActivo">
    <i class="fas fa-plus"></i> Añadir Activo/Producto
  </button>
</div>


<!-- Botones centrados -->
<div class="d-flex justify-content-center gap-3 mb-3">
  <!-- Botón Enviar Activos -->
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEnviarActivos">
    <i class="fas fa-location-arrow"></i> Enviar Activos/Productos
  </button>

  <!-- Botón Devolución de Envíos -->
  <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalDevolucionEnvios">
    <i class="fas fa-undo-alt"></i> Devolución de Envíos
  </button>
</div>


<div class="container mt-4">
  <h2 class="text-center mb-4">Catálogo de Activos/Productos</h2>
  <div class="row">
    
    <?php
    function obtenerClaseEstado($estado) {
        return match ($estado) {
          'Óptimo'      => 'bg-success text-white',
          'Bueno'       => 'bg-primary text-white',
          'Regular'     => 'bg-warning text-dark',
          'Deficiente'  => 'bg-danger text-white',
          'Inoperativa' => 'bg-dark text-white',
          default       => 'bg-secondary text-white',
        };
      }

      function obtenerUbicacionesActivas($conn, $id_activo, $id_ubicacion_base, $cantidad_actual) {
    $ubicaciones = [];
    $cantidad_enviada = 0;
    $nombre_base = 'Base';

    // Obtener nombre de la ubicación base
    if ($id_ubicacion_base) {
        $res_base = $conn->query("SELECT nombre FROM ubicaciones WHERE idubicacion = $id_ubicacion_base");
        if ($res_base && $base = $res_base->fetch_assoc()) {
            $nombre_base = $base['nombre'];
        }
    }

    // Obtener stock real por ubicación: enviados - devueltos - usados/perdidos
    $sql = "
        SELECT u.nombre, 
               SUM(ed.cantidad_enviada) 
               - IFNULL(SUM(dev.cantidad_devuelta), 0) 
               - IFNULL(SUM(uso.cantidad_usada), 0) AS cantidad
        FROM envio_detalle ed
        JOIN envio e ON ed.envio_id = e.idenvio
        JOIN ubicaciones u ON e.ubicacion_id = u.idubicacion
        LEFT JOIN (
            SELECT envio_id, activo_id, SUM(cantidad_devuelta) AS cantidad_devuelta
            FROM envio_devolucion
            WHERE activo_id = $id_activo
            GROUP BY envio_id, activo_id
        ) dev ON dev.envio_id = ed.envio_id AND dev.activo_id = ed.activo_id
        LEFT JOIN (
            SELECT envio_id, activo_id, SUM(cantidad) AS cantidad_usada
            FROM activos_usados
            WHERE activo_id = $id_activo
            GROUP BY envio_id, activo_id
        ) uso ON uso.envio_id = ed.envio_id AND uso.activo_id = ed.activo_id
        WHERE ed.activo_id = $id_activo
        GROUP BY u.idubicacion
        HAVING cantidad > 0
    ";

    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $ubicaciones[] = "{$row['nombre']} ({$row['cantidad']})";
            $cantidad_enviada += $row['cantidad'];
        }
    }

    // Sumar lo que queda en base
    if ($cantidad_actual > 0 && $nombre_base !== '') {
        $ubicaciones[] = "$nombre_base ($cantidad_actual)";
    }

    return implode(', ', $ubicaciones);
}

    
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $img = !empty($row['img']) ? "img/" . htmlspecialchars($row['img']) : "img/no-image.png";
        $nombre = htmlspecialchars($row['nombre']);
        $categoria = htmlspecialchars($row['categoria'] ?? 'Sin categoría');
        $cantidad = intval($row['cantidad']);

$ubicacion = obtenerUbicacionesActivas($conn, $row['idactivo'], $row['idubicacion'], $cantidad);
        $estado = htmlspecialchars($row['estado']);
        $descripcion = htmlspecialchars($row['descripcion'] ?? '');
        $asignacion = htmlspecialchars($row['nro_asignacion'] ?? '');
          

        echo "
        <div class='col-lg-3 col-md-4 col-sm-6 mb-4'>
          <div class='card h-100 shadow-sm border-0 rounded-3'>
            <img src='$img' class='card-img-top' alt='Imagen del activo' style='height: 200px; object-fit: cover; border-top-left-radius: .5rem; border-top-right-radius: .5rem;'>
            
            <div class='card-body d-flex flex-column'>
              <h5 class='card-title text-primary text-center fw-bold mb-3'>$nombre</h5>
              
              <p class='mb-1'><i class='fas fa-map-marker-alt text-muted'></i> <strong>Ubicación:</strong><br> $ubicacion</p>
              <p class='mb-1'>
                <i class='fas fa-circle-notch text-muted'></i> <strong>Estado:</strong>
                <span class='badge " . obtenerClaseEstado($estado) . "'>$estado</span>
              </p>
              <p class='mb-3'><i class='fas fa-boxes text-muted'></i> <strong>Cantidad Base:</strong> $cantidad</p>
        
              <div class='mt-auto'>
                <div class='d-flex justify-content-center gap-2 flex-wrap'>
                  <button class='btn btn-warning btn-sm btnEditarActivo'
                          data-idactivo='{$row['idactivo']}'
                          data-bs-toggle='modal'
                          data-bs-target='#modalEditarActivo'>
                    <i class='fas fa-edit'></i> Editar
                  </button>
        
                  <button class='btn btn-info btn-sm btnVerDetallesActivo'
                          data-idactivo='{$row['idactivo']}'
                          data-bs-toggle='modal'
                          data-bs-target='#modalVerActivo'>
                    <i class='fas fa-eye'></i> Ver Detalles
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>";
        
          

      }
    } else {
      echo "<p class='text-center'>No hay activos registrados.</p>";
    }
    ?>
  </div>
</div>














































<!-- Modal -->
<div class="modal fade" id="modalAgregarActivo" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="formAgregarActivo" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Activo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <label>Nombre:</label>
              <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Categoría:</label>
              <select name="idcategoria" id="editar_idcategoria" class="form-control" required>
            <option value="">Seleccione una categoría</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['idcategoria'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
            <?php endforeach; ?>
            </select>


            </div>
          </div>

          <div class="row mt-2">
            <div class="col-md-6">
              <label>Ubicación:</label>
              <select name="idubicacion" id="editar_idubicacion" class="form-control" required>
  <option value="">Seleccione una ubicación</option>
  <?php foreach ($ubicaciones as $ubi): ?>
    <option value="<?= $ubi['idubicacion'] ?>"><?= htmlspecialchars($ubi['nombre']) ?></option>
  <?php endforeach; ?>
</select>   

            </div>
            <div class="col-md-6">
              <label>N° Asignación:</label>
              <input type="text" name="nro_asignacion" class="form-control">
            </div>
          </div>

          <div class="row mt-2">
            <div class="col-md-6">
              <label>Estado:</label>
              <select name="estado" class="form-control" required>
  <option value="">Seleccione estado...</option>
  <option value="Óptimo">🟢 Óptimo</option>
  <option value="Bueno">🟡 Bueno</option>
  <option value="Regular">🟠 Regular</option>
  <option value="Deficiente">🔴 Deficiente</option>
  <option value="Inoperativa">⚫ Inoperativa</option>
</select>

            </div>
            <div class="col-md-6">
              <label>Cantidad:</label>
              <input type="number" name="cantidad" class="form-control" min="1">
            </div>
          </div>

          <div class="mt-3">
            <label>Descripción:</label>
            <textarea name="descripcion" class="form-control"></textarea>
          </div>

          <div class="mt-3">
            <label>Imagen:</label>
            <input type="file" name="img" class="form-control" accept="image/*">
          </div>
        </div>

        <div class="modal-footer">
        <?php if ($_SESSION['rol'] === 'Administrador'): ?>
  <button type="submit" class="btn btn-success">Guardar</button>
<?php else: ?>
  <button type="button" class="btn btn-success" onclick="abrirModalClaveActivo()">Guardar</button>
<?php endif; ?>



          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal para Editar Activo --><!-- Modal para Editar Activo --><!-- Modal para Editar Activo --><!-- Modal para Editar Activo --><!-- Modal para Editar Activo --><!-- Modal para Editar Activo -->

<div class="modal fade" id="modalEditarActivo" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="formEditarActivo" enctype="multipart/form-data">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Editar Activo</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="idactivo" id="editar_idactivo">
          <div class="mt-3">
  <label>Imagen actual:</label><br>
  <img id="editar_preview" src="img/no-image.png" alt="Imagen actual" class="img-thumbnail" style="max-height: 200px;">
</div>

          <div class="row">
            <div class="col-md-6">
              <label>Nombre:</label>
              <input type="text" name="nombre" id="editar_nombre" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Categoría:</label>
              <select name="idcategoria" id="editar_idcategoria" class="form-control" required>
  <option value="">Seleccione una categoría</option>
  <?php foreach ($categorias as $cat): ?>
    <option value="<?= $cat['idcategoria'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
  <?php endforeach; ?>
</select>
            </div>
          </div>

          <div class="row mt-2">
            <div class="col-md-6">
              <label>Ubicación:</label>
              <select name="idubicacion" id="editar_idubicacion" class="form-control" required>
  <option value="">Seleccione una ubicación</option>
  <?php foreach ($ubicaciones as $ubi): ?>
    <option value="<?= $ubi['idubicacion'] ?>"><?= htmlspecialchars($ubi['nombre']) ?></option>
  <?php endforeach; ?>
</select>
            </div>
            <div class="col-md-6">
              <label>N° Asignación:</label>
              <input type="text" name="nro_asignacion" id="editar_nro_asignacion" class="form-control">
            </div>
          </div>

          <div class="row mt-2">
            <div class="col-md-6">
              <label>Estado:</label>
              <select name="estado" id="editar_estado" class="form-control" required>
  <option value="">Seleccione estado...</option>
  <option value="Óptimo">🟢 Óptimo</option>
  <option value="Bueno">🟡 Bueno</option>
  <option value="Regular">🟠 Regular</option>
  <option value="Deficiente">🔴 Deficiente</option>
  <option value="Inoperativa">⚫ Inoperativa</option>
</select>
            </div>
            <div class="col-md-6">
              <label>Cantidad:</label>
              <input type="number" name="cantidad" id="editar_cantidad" class="form-control" min="1">
            </div>
          </div>

          <div class="mt-3">
            <label>Descripción:</label>
            <textarea name="descripcion" id="editar_descripcion" class="form-control"></textarea>
          </div>

          <div class="mt-3">
            <label>Imagen:</label>
            <input type="file" name="img" class="form-control">
          </div>
        </div>

        <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

        <button type="button" class="btn btn-danger" id="btnEliminarActivo">Eliminar</button>

        <?php if ($_SESSION['rol'] === 'Administrador'): ?>
          <button type="button" class="btn btn-primary" id="btnGuardarCambiosActivo">Guardar Cambios</button>
          <?php else: ?>
  <button type="button" class="btn btn-primary" onclick="abrirModalClaveEditar()">Guardar Cambios</button>
  <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Modal para Editar Activo --><!-- Modal para Editar Activo --><!-- Modal para Editar Activo --><!-- Modal para Editar Activo --><!-- Modal para Editar Activo --><!-- Modal para Editar Activo -->
<!-- Modal Clave Admin para Eliminar Activo -->
<div class="modal fade" id="modalClaveEliminarActivo" tabindex="-1">
  <div class="modal-dialog">
    <form id="formClaveEliminarActivo" class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Eliminar Activo</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="idactivo" id="eliminar_idactivo">
        <input type="hidden" name="from_auth_modal" value="1">

        <div class="mb-3">
          <label for="clave_admin_eliminar" class="form-label">Contraseña de administrador:</label>
          <input type="password" name="clave_admin" id="clave_admin_eliminar" class="form-control" required>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">Confirmar Eliminación</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>


<!-- Modal Clave Admin para Editar Activo -->
<!-- Modal Clave Admin para Editar Activo -->
<div class="modal fade" id="modalClaveEditarActivo" tabindex="-1">
  <div class="modal-dialog">
    <form id="formClaveEditarActivo" action="guardar_edicion_activo.php" method="POST" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Autenticación requerida</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <!-- Campos ocultos -->
        <input type="hidden" name="idactivo">
        <input type="hidden" name="nombre">
        <input type="hidden" name="idcategoria">
        <input type="hidden" name="idubicacion">
        <input type="hidden" name="nro_asignacion">
        <input type="hidden" name="estado">
        <input type="hidden" name="cantidad">
        <input type="hidden" name="descripcion">
        <input type="hidden" name="from_auth_modal" value="1">

        <!-- Imagen nueva -->
        <label>Imagen (debe volver a subirla):</label>
        <input type="file" name="img" class="form-control mb-3" accept="image/*">

        <!-- Clave admin -->
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
function abrirModalClaveEditar() {
  const form = document.getElementById("formEditarActivo");
  const modal = document.getElementById("formClaveEditarActivo");

  ["idactivo", "nombre", "idcategoria", "idubicacion", "nro_asignacion", "estado", "cantidad", "descripcion"].forEach(campo => {
    modal.querySelector(`[name="${campo}"]`).value = form.querySelector(`[name="${campo}"]`).value;
  });

  const bsModal = new bootstrap.Modal(document.getElementById('modalClaveEditarActivo'));
  bsModal.show();
}
</script>




<script>
function abrirModalClaveActivo() {
  const formMain = document.getElementById('formAgregarActivo');
  const formModal = document.getElementById('formClaveAdminActivo');

  // Copiar todos los campos
  ['nombre', 'idcategoria', 'idubicacion', 'nro_asignacion', 'estado', 'cantidad', 'descripcion'].forEach(campo => {
    formModal.querySelector(`[name="${campo}"]`).value = formMain.querySelector(`[name="${campo}"]`).value;
  });

  // Imagen (archivo): no se puede copiar por JS, así que el usuario deberá volver a seleccionarla si es necesario

  const modal = new bootstrap.Modal(document.getElementById('modalClaveAdminActivo'));
  modal.show();
}
</script>
<!-- Modal Clave Admin --><!-- Modal Clave Admin --><!-- Modal Clave Admin --><!-- Modal Clave Admin --><!-- Modal Clave Admin --><!-- Modal Clave Admin --><!-- Modal Clave Admin -->
<div class="modal fade" id="modalClaveAdminActivo" tabindex="-1">
  <div class="modal-dialog">
    <form id="formClaveAdminActivo" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Autenticación requerida</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Copia de los campos -->
        <input type="hidden" name="nombre">
        <input type="hidden" name="idcategoria">
        <input type="hidden" name="idubicacion">
        <input type="hidden" name="nro_asignacion">
        <input type="hidden" name="estado">
        <input type="hidden" name="cantidad">
        <input type="hidden" name="descripcion">
        <input type="hidden" name="from_auth_modal" value="1">

        <!-- Imagen -->
        <label class="form-label text-danger">⚠️ Debes volver a seleccionar la imagen del activo:</label>
        <input type="file" name="img" class="form-control mb-3" accept="image/*" required>

        <!-- Clave admin -->
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
<!-- Modal Clave Admin --><!-- Modal Clave Admin --><!-- Modal Clave Admin --><!-- Modal Clave Admin --><!-- Modal Clave Admin --><!-- Modal Clave Admin --><!-- Modal Clave Admin -->

<!--- Modal para ver activos--><!--- Modal para ver activos--><!--- Modal para ver activos--><!--- Modal para ver activos--><!--- Modal para ver activos--><!--- Modal para ver activos-->
<div class="modal fade" id="modalVerActivo" tabindex="-1" aria-labelledby="modalVerActivoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="modalVerActivoLabel">Detalles del Activo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <!-- Imagen -->
          <div class="col-md-4 text-center mb-3">
            <img id="ver_img_activo" src="" alt="Imagen del activo" class="img-fluid rounded border" style="max-height: 200px;">
          </div>
          <!-- Datos -->
          <div class="col-md-8">
            <p><strong>ID:</strong> <span id="ver_idactivo"></span></p>
            <p><strong>Nombre:</strong> <span id="ver_nombre"></span></p>
            <p><strong>Categoría:</strong> <span id="ver_categoria"></span></p>
            <p><strong>Nro Asignación:</strong> <span id="ver_nro_asignacion"></span></p>
            <p><strong>Estado:</strong> <span id="ver_estado"></span></p>
            <p><strong>Descripción:</strong> <span id="ver_descripcion"></span></p>
            <p><strong>Cantidad:</strong> <span id="ver_cantidad"></span></p>
            <p><strong>Ubicación:</strong> <span id="ver_ubicacion"></span></p>
            <p><strong>Fecha de Registro:</strong> <span id="ver_fecha_registro"></span></p>
          </div>
        </div>
      </div>
      <div class="modal-footer border-top">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>



<!--- Modal para ENVIAR activos--><!--- Modal para ENVIAR activos--><!--- Modal para ENVIAR activos--><!--- Modal para ENVIAR activos--><!--- Modal para ENVIAR activos--><!--- Modal para ENVIAR activos-->

<div class="modal fade" id="modalEnviarActivos" tabindex="-1" aria-labelledby="modalEnviarActivosLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="procesar_envio_activos.php" method="POST">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalEnviarActivosLabel">Enviar Activos a Ubicación</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <!-- Ubicación destino -->
          <div class="mb-3">
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

          <!-- Select2 para elegir activos -->
          <div class="mb-3 row align-items-center">
  <div class="col-md-9">
    <label for="select_activo" class="form-label">Buscar Activo/Producto:</label>
    <select id="select_activo" class="form-select select2">
      <option value="">Seleccione un activo</option>
      <?php
$activos = $conn->query("SELECT idactivo, nombre, cantidad FROM activos ORDER BY nombre ASC");
while ($a = $activos->fetch_assoc()) {
  $cantidad = (int) $a['cantidad']; // Forzamos cantidad numérica
  echo "<option value='{$a['idactivo']}' data-cantidad='{$cantidad}'>{$a['nombre']}</option>";
}
?>

    </select>
  </div>



  <div class="col-md-3 mt-3 mt-md-4">
    <button type="button" id="btnAgregarActivo" class="btn btn-outline-primary w-100">
      <i class="fas fa-plus"></i> Agregar
    </button>
  </div>
</div>
          <!-- Tabla dinámica para agregar activos -->
          <div class="table-responsive">
            <table class="table table-bordered" id="tablaActivosSeleccionados">
            <thead class="table-light">
  <tr>
    <th>Activo/Producto</th>
    <th>Cantidad</th>
    <th>Existente</th>
    <th>Acciones</th>
  </tr>
</thead>

              <tbody>
                <!-- Se insertarán dinámicamente -->
              </tbody>
            </table>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Enviar Activos</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!--- Modal para ENVIAR activos--><!--- Modal para ENVIAR activos--><!--- Modal para ENVIAR activos--><!--- Modal para ENVIAR activos--><!--- Modal para ENVIAR activos--><!--- Modal para ENVIAR activos-->



<!-- Modal Devolución de Envíos -->
<div class="modal fade" id="modalDevolucionEnvios" tabindex="-1" aria-labelledby="modalDevolucionEnviosLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="modalDevolucionEnviosLabel">Gestión de Devoluciones</h5>
        <button type="button" class="btn-close btnCerrarDevoluciones" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <!-- Tabla de todos los envíos -->
        <div class="table-responsive">
          <table class="table table-bordered align-middle text-center" id="tablaEnvios">
            <thead class="table-dark">
              <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Ubicación</th>
                <th>Estado</th>
                <th>Activos Enviados</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $sql = "
                SELECT 
                  e.idenvio, 
                  e.fecha, 
                  u.nombre AS ubicacion,
                  CASE 
                    WHEN e.devuelto = 1 THEN 'Devuelto'
                    ELSE 'Pendiente'
                  END AS estado,
                  GROUP_CONCAT(p.nombre SEPARATOR ', ') AS activos
                FROM envio e
                JOIN ubicaciones u ON e.ubicacion_id = u.idubicacion
                LEFT JOIN envio_detalle ed ON e.idenvio = ed.envio_id
                LEFT JOIN activos p ON ed.activo_id = p.idactivo
                GROUP BY e.idenvio, e.fecha, u.nombre, e.devuelto
                ORDER BY e.fecha DESC
              ";

              $envios = $conn->query($sql);

              if (!$envios) {
                  echo "<tr><td colspan='6' class='text-danger'>❌ Error en la consulta: " . $conn->error . "</td></tr>";
              } else {
                  while ($envio = $envios->fetch_assoc()) {
                      $activos = $envio['activos'] ?: 'Sin activos';
                      echo "
                      <tr>
                        <td>{$envio['idenvio']}</td>
                        <td>{$envio['fecha']}</td>
                        <td>{$envio['ubicacion']}</td>
                        <td>{$envio['estado']}</td>
                        <td>{$activos}</td>
                        <td>
                          <a href='obtener_detalle_envio.php?id={$envio['idenvio']}' class='btn btn-sm btn-info'>
                            Ver Detalles
                          </a>
                          <button class='btn btn-sm btn-danger btnEliminarEnvio' data-id='{$envio['idenvio']}'>
                            Eliminar
                          </button>
                        </td>
                      </tr>";
                  }
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer border-top">
        <button type="button" class="btn btn-secondary btnCerrarDevoluciones" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>








<script>
let cambiosPendientes = false;

// Activar bandera si se modifica el formulario de devolución
$(document).on('change', '#formDevolucionEnvios input, #formDevolucionEnvios select', function () {
  cambiosPendientes = true;
});

// Confirmar antes de cerrar el modal si hay cambios
$(document).on('click', '.btnCerrarDevoluciones', function (e) {
  if (cambiosPendientes) {
    e.preventDefault();
    Swal.fire({
      title: '¿Deseas cerrar?',
      text: 'Tienes cambios sin guardar. ¿Seguro que quieres salir?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, cerrar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        cambiosPendientes = false;
        $('#modalDevolucionEnvios').modal('hide');
      }
    });
  }
});
</script>



<script>
$(document).on('click', '.btnEliminarEnvio', function () {
  const id = $(this).data('id');
  Swal.fire({
    title: '¿Eliminar Envío?',
    text: "Esta acción no se puede deshacer.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      $.post('eliminar_envio.php', { idenvio: id }, function (response) {
        Swal.fire('¡Eliminado!', response, 'success').then(() => {
          location.reload();
        });
      }).fail(() => {
        Swal.fire('Error', 'No se pudo eliminar el envío.', 'error');
      });
    }
  });
});
</script>
















































<audio id="popSound" src="pop.mp3"></audio>


<script>
const sound = document.getElementById("popSound");
sound.volume = 0.3; // Volumen más bajo

$(document).on("click", ".btn-sumar", function () {
  const input = $(this).siblings("input[type='number']");
  const max = parseInt(input.attr("max"));
  let val = parseInt(input.val());

  if (val < max) {
    input.val(val + 1);
    sound.currentTime = 0;
    sound.play();
    Swal.fire({
      toast: true,
      position: "bottom",
      icon: "success",
      title: "✅ Se sumó 1 unidad",
      background: "#28a745",
      color: "#ffffff",
      showConfirmButton: false,
      timer: 1200
    });
  }
});

$(document).on("click", ".btn-restar", function () {
  const input = $(this).siblings("input[type='number']");
  let val = parseInt(input.val());

  if (val > 1) {
    input.val(val - 1);
    sound.currentTime = 0;
    sound.play();
    Swal.fire({
      toast: true,
      position: "bottom",
      icon: "info",
      title: "➖ Se restó 1 unidad",
      background: "#17a2b8",
      color: "#ffffff",
      showConfirmButton: false,
      timer: 1200
    });
  }
});
</script>










<script>
  // ENVIAR ACTIVO AGREGAR ENVIAR ACTIVO AGREGAR ENVIAR ACTIVO AGREGAR -------------------------------------------------------------------------------------------
$(document).ready(function () {
  // Inicializar select2
  $('.select2').select2({
    dropdownParent: $('#modalEnviarActivos')
  });

  // Botón "Agregar activo"
  $('#btnAgregarActivo').on('click', function () {
    const select = $('#select_activo');
    const id = select.val();
    const nombre = select.find('option:selected').text();
    const cantidadExistente = parseInt(select.find('option:selected').data('cantidad')) || 0;

    if (!id) return;

    if (cantidadExistente <= 0) {
      Swal.fire("Sin stock", "Este activo no tiene unidades disponibles para enviar.", "warning");
      return;
    }

    if (!$('#fila-' + id).length) {
      const fila = `
        <tr id="fila-${id}">
          <td>
            <input type="hidden" name="activos[${id}][id]" value="${id}">
            ${nombre}
          </td>
          <td class="text-center">
            <div class="d-flex justify-content-center align-items-center gap-1">
              <button type="button" class="btn btn-sm btn-outline-secondary btn-restar">-</button>
              <input type="number" name="activos[${id}][cantidad]" value="1" min="1" max="${cantidadExistente}" class="form-control text-center cantidad-input" style="width: 60px;">
              <button type="button" class="btn btn-sm btn-outline-secondary btn-sumar">+</button>
            </div>
          </td>
          <td class="text-center align-middle">${cantidadExistente}</td>
          <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger btnEliminarFila" data-idactivo="${id}">Eliminar</button>
          </td>
        </tr>
      `;
      $('#tablaActivosSeleccionados tbody').append(fila);

      // Desactivar la opción en el select
      select.find(`option[value="${id}"]`).prop('disabled', true);
      select.val(null).trigger('change');
    }
  });

  // Eliminar fila y reactivar en select
  $(document).on('click', '.btnEliminarFila', function () {
    const id = $(this).data('idactivo');
    $('#fila-' + id).remove();
    $('#select_activo option[value="' + id + '"]').prop('disabled', false);
  });

});

// ENVIAR ACTIVO AGREGAR ENVIAR ACTIVO AGREGAR ENVIAR ACTIVO AGREGAR -------------------------------------------------------------------------------------------
</script>




<script>
$(document).on("click", "#btnEliminarActivo", function () {
  const id = $(this).data("idactivo");
  const esAdmin = "<?= $_SESSION['rol'] === 'Administrador' ? '1' : '0' ?>";

  if (!id) {
    Swal.fire("Error", "❌ ID del activo no disponible.", "error");
    return;
  }

  if (esAdmin === "1") {
    // ✅ Si es admin, eliminar directamente con confirmación
    Swal.fire({
      title: '¿Eliminar activo?',
      text: "Esta acción no se puede deshacer.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#d33'
    }).then((result) => {
      if (result.isConfirmed) {
        eliminarActivo(id);
      }
    });
  } else {
    // ❌ No admin → mostrar modal de clave
    $("#eliminar_idactivo").val(id);
    $("#clave_admin_eliminar").val(""); // limpiar
    const modal = new bootstrap.Modal(document.getElementById('modalClaveEliminarActivo'));
    modal.show();
  }
});
</script>
<script>
$("#formClaveEliminarActivo").submit(function (e) {
  e.preventDefault();
  const formData = $(this).serialize();

  $.post("eliminar_activo.php", formData, function (res) {
    try {
      const response = typeof res === "string" ? JSON.parse(res) : res;

      if (response.status === "success") {
        Swal.fire("¡Eliminado!", response.message, "success").then(() => {
          location.reload();
        });
      } else {
        Swal.fire("Error", response.message, "error");
      }
    } catch (e) {
      console.error("Respuesta inesperada:", res);
      Swal.fire("Error", "❌ Error inesperado en el servidor.", "error");
    }
  }).fail(() => {
    Swal.fire("Error", "❌ No se pudo eliminar el activo.", "error");
  });
});
</script>
































<script>
$(document).on('click', '.btnVerDetallesActivo', function () {
    const idactivo = $(this).data('idactivo');

    console.log("Enviando ID a obtener_activo2.php:", idactivo);

    $.ajax({
        url: 'obtener_activo2.php',
        method: 'POST',
        data: { idactivo: idactivo },
        dataType: 'json',
        success: function (data) {
    console.log("Datos recibidos del activo:", data);

            if (Object.keys(data).length === 0) {
                alert('❌ Activo no encontrado.');
                return;
            }

            $('#ver_idactivo').text(data.idactivo);
            $('#ver_nombre').text(data.nombre);
            $('#ver_categoria').text(data.categoria);
            $('#ver_nro_asignacion').text(data.nro_asignacion);
            $('#ver_estado').text(data.estado);
            $('#ver_descripcion').text(data.descripcion);
            $('#ver_cantidad').text(data.cantidad);
            $('#ver_ubicacion').text(data.ubicacion);
            $('#ver_fecha_registro').text(data.fecha_registro ?? 'No disponible');
            $('#ver_img_activo').attr('src', 'img/' + (data.img ? data.img : 'no-image.png'));
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.log("Respuesta del servidor:", xhr.responseText);
            alert('❌ Error al obtener los detalles del activo.');
        }
    });
});
</script>



<script>
$(document).on("click", ".btnEditarActivo", function () {
  const id = $(this).data("idactivo");

  $.ajax({
    url: "obtener_activo.php",
    type: "POST",
    data: { idactivo: id },
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        const a = response.activo;

        console.log("ID Categoría devuelto:", a.idcategoria);
        console.log("ID Ubicación devuelto:", a.idubicacion);

        $("#editar_idactivo").val(a.idactivo);
        $("#editar_nombre").val(a.nombre);
        $("#editar_nro_asignacion").val(a.nro_asignacion);
        $("#editar_descripcion").val(a.descripcion);
        $("#editar_cantidad").val(a.cantidad);

        // Forzar la selección (método alternativo, muy claro)
        $("#editar_idcategoria option").each(function(){
          if($(this).val() == a.idcategoria){
            $(this).prop('selected', true);
            console.log("Categoría seleccionada correctamente");
          }
        });

        $("#editar_idubicacion option").each(function(){
          if($(this).val() == a.idubicacion){
            $(this).prop('selected', true);
            console.log("Ubicación seleccionada correctamente");
          }
        });

        // Seleccionar estado
        $("#editar_estado").val(a.estado);

        // ✅ Asignar el ID al botón eliminar
        $('#btnEliminarActivo').data('idactivo', a.idactivo);

        $("#modalEditarActivo").modal("show");
      } else {
        Swal.fire("Error", response.message, "error");
      }
    },
    error: function () {
      Swal.fire("Error", "No se pudo obtener el activo.", "error");
    }
  });
});
</script>










<script>
    $("#btnGuardarCambiosActivo").on("click", function () {
        const form = $("#formEditarActivo")[0];
        const formData = new FormData(form);

        $.ajax({
            url: "guardar_edicion_activo.php",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "¡Actualizado!",
                        text: response.message
                    }).then(() => {
                        $("#modalEditarActivo").modal("hide");
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Autenticación fallida",
                        text: response.message,
                        confirmButtonText: "Intentar de nuevo",
                        confirmButtonColor: "#d33"
                    });
                }
            },
            error: function () {
                Swal.fire("Error", "Error del servidor al guardar.", "error");
            }
        });
    });
</script>





















<script>
$(document).on("click", ".btnEditarActivo", function () {
  const id = $(this).data("idactivo");

  $.ajax({
    url: "obtener_activo.php",
    type: "POST",
    data: { idactivo: id },
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        const a = response.activo;

        console.log("ID Categoría devuelto:", a.idcategoria);
        console.log("ID Ubicación devuelto:", a.idubicacion);

        $("#editar_idactivo").val(a.idactivo);
        $("#editar_nombre").val(a.nombre);
        $("#editar_nro_asignacion").val(a.nro_asignacion);
        $("#editar_descripcion").val(a.descripcion);
        $("#editar_cantidad").val(a.cantidad);

        // Selección dinámica
        $("#editar_idcategoria option").each(function(){
          if($(this).val() == a.idcategoria){
            $(this).prop('selected', true);
            console.log("Categoría seleccionada correctamente");
          }
        });

        $("#editar_idubicacion option").each(function(){
          if($(this).val() == a.idubicacion){
            $(this).prop('selected', true);
            console.log("Ubicación seleccionada correctamente");
          }
        });

        $("#editar_estado").val(a.estado);

        // Manejo de imagen (solución al problema)
        if (a.img && a.img !== "") {
            $("#editar_preview").attr("src", "img/" + a.img).show();
        } else {
            $("#editar_preview").attr("src", "img/no-image.png").show();
        }

        $("#modalEditarActivo").modal("show");
      } else {
        Swal.fire("Error", response.message, "error");
      }
    },
    error: function () {
      Swal.fire("Error", "No se pudo obtener el activo.", "error");
    }
  });
});

</script>




<script>
$("#formClaveAdminActivo").submit(function(e) {
  e.preventDefault();
  const formData = new FormData(this);

  $.ajax({
    url: "agregar_activo.php",
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function(response) {
      if (response.status === "success") {
        Swal.fire("¡Guardado!", "El activo ha sido agregado correctamente.", "success").then(() => {
          $("#modalClaveAdminActivo").modal("hide");
          location.reload();
        });
      } else {
        Swal.fire("Error", response.message, "error");
      }
    },
    error: function() {
      Swal.fire("Error", "Error del servidor al agregar el activo.", "error");
    }
  });
});
</script>



<script>
$(document).ready(function () {
  // Inicializar Select2 con su contenedor modal
  $('#envio_id').select2({
    dropdownParent: $('#modalDevolucionEnvios')
  });
});
</script>

</body>
</html> 