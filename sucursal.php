<?php
include 'db.php';

// Obtener todas las sucursales
$sucursales = $conn->query("SELECT idubicacion, nombre FROM ubicaciones");

// Capturar sucursal seleccionada (si existe)
$sucursal_id = isset($_GET['sucursal_id']) ? intval($_GET['sucursal_id']) : null;

// Si no hay sucursal seleccionada, asigna un valor predeterminado (por ejemplo, la primera sucursal disponible)
if (!$sucursal_id) {
    $result_sucursales = $conn->query("SELECT idubicacion FROM ubicaciones LIMIT 1");
    if ($result_sucursales && $row = $result_sucursales->fetch_assoc()) {
        $sucursal_id = $row['idubicacion']; // Se asigna el primer ID de sucursal disponible
    }
}
?>


<?php
session_start(); // Iniciar la sesión

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
    <title>Sucursales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">


</head>
<body>
<?php $pagina = basename($_SERVER['PHP_SELF']); ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Inventario</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav">

                <!-- ✅ Dropdown Productos -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= ($pagina == 'index.php' || $pagina == 'activos.php') ? 'active-parent' : '' ?>" 
                       href="#" id="inventarioDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Productos
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="inventarioDropdown">
                        <li><a class="dropdown-item <?= ($pagina == 'index.php') ? 'active-item' : '' ?>" href="index.php">Inventario</a></li>
                        <li><a class="dropdown-item <?= ($pagina == 'activos.php') ? 'active-item' : '' ?>" href="activos.php">Activos Físicos</a></li>
                    </ul>
                </li>

                <!-- ✅ Dropdown Trabajos -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($pagina, ['trabajos.php','clientes.php','devoluciones.php','ordenesCompra.php']) ? 'active-parent' : '' ?>" 
                       href="#" id="trabajosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Trabajos
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="trabajosDropdown">
                        <li><a class="dropdown-item <?= ($pagina == 'trabajos.php') ? 'active-item' : '' ?>" href="trabajos.php">Ver Trabajos</a></li>
                        <li><a class="dropdown-item <?= ($pagina == 'clientes.php') ? 'active-item' : '' ?>" href="clientes.php">Clientes</a></li>
                        <li><a class="dropdown-item <?= ($pagina == 'devoluciones.php') ? 'active-item' : '' ?>" href="devoluciones.php">Devoluciones</a></li>
                        <li><a class="dropdown-item <?= ($pagina == 'ordenesCompra.php') ? 'active-item' : '' ?>" href="ordenesCompra.php">Ordenes Compra</a></li>
                    </ul>
                </li>

                <!-- ✅ Dropdown Personal -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($pagina, ['empleados.php','usuarios.php']) ? 'active-parent' : '' ?>" 
                       href="#" id="personalDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Personal
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="personalDropdown">
                        <li><a class="dropdown-item <?= ($pagina == 'empleados.php') ? 'active-item' : '' ?>" href="empleados.php">Empleados</a></li>
                        <li><a class="dropdown-item <?= ($pagina == 'usuarios.php') ? 'active-item' : '' ?>" href="usuarios.php">Usuarios</a></li>
                    </ul>
                </li>

                <!-- 🔹 Resto del menú -->
                <li class="nav-item"><a class="nav-link <?= ($pagina == 'vehiculos.php') ? 'active' : '' ?>" href="vehiculos.php">Vehículos</a></li>
                <li class="nav-item"><a class="nav-link <?= ($pagina == 'sucursal.php') ? 'active' : '' ?>" href="sucursal.php">Sucursales</a></li>
                <li class="nav-item"><a class="nav-link <?= ($pagina == 'documentos.php') ? 'active' : '' ?>" href="documentos.php">Documentos</a></li>
                <li class="nav-item"><a class="nav-link <?= ($pagina == 'estadisticas.php') ? 'active' : '' ?>" href="estadisticas.php">Estadísticas</a></li>
                <li class="nav-item"><a class="nav-link <?= ($pagina == 'historial.php') ? 'active' : '' ?>" href="historial.php">Historial</a></li>

            </ul>

            <div class="logout-container ms-auto">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <a href="logout.php" class="btn btn-danger btn-sm">Cerrar Sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<style>
    body {
    background-color:rgb(221, 221, 221); /* 🌑 Fondo gris oscuro */
    color:rgb(20, 20, 20);
  }
</style>
<style>
/* 🎯 Estilo para el ítem activo (páginas individuales) */
.navbar .nav-link.active,
.navbar .dropdown-item.active-item {
    background-color: #0d6efd;
    color: white !important;
    font-weight: bold;
    border-radius: 5px;
}

/* 🎯 Estilo para el padre del dropdown activo */
.navbar .nav-link.active-parent {
    background-color: #198754;
    color: white !important;
    font-weight: bold;
    border-radius: 5px;
}

/* ✅ Responsive navbar mobile */
@media (max-width: 578px) {
    body {
        padding-top: 70px;
    }

    .navbar {
        z-index: 1050;
    }

    .navbar-collapse {
        background-color: rgba(0, 0, 0, 0.95);
        padding: 1rem;
        border-radius: 0 0 10px 10px;
    }

    .navbar-nav .nav-link {
        color: white !important;
        padding: 10px 15px;
        font-weight: 500;
    }

    .logout-container {
        margin-top: 1rem;
    }
}

/* ✅ Escritorio */
@media (min-width: 579px) {
    body {
        padding-top: 60px;
    }
}
</style>


<!-- jQuery (debe ir primero) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>



<!-- SweetAlert2 (opcional pero recomendado) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>








<?php if (isset($_GET['eliminado']) && $_GET['eliminado'] == 1): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: 'success',
    title: 'Activo eliminado',
    text: 'El activo fue eliminado correctamente.',
    timer: 1000,
    showConfirmButton: false
}).then(() => {
    window.location.href = 'sucursal.php';
});
</script>
<?php endif; ?>



<div class="text-end mb-3">
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevoItem">
        ➕ Nuevo Activo o Producto en Sucursal
    </button>
</div>


<div class="container mt-4">
    <h3 class="text-center mb-4">Inventario por Sucursal</h3>

    <form method="GET" class="mb-4 text-center">
    <label for="sucursal_id" class="form-label">Selecciona una Sucursal:</label>
    <select name="sucursal_id" id="sucursal_id" class="form-select w-auto d-inline-block" onchange="this.form.submit()">
        <option value="" disabled>-- Selecciona --</option>
        <?php 
        // Hacer que la primera opción siempre sea seleccionada por defecto
        $first = true;
        while ($sucursal = $sucursales->fetch_assoc()): ?>
            <option value="<?= $sucursal['idubicacion'] ?>" <?= ($first || $sucursal_id == $sucursal['idubicacion']) ? 'selected' : '' ?>>
                <?= $sucursal['nombre'] ?>
            </option>
            <?php $first = false; ?>
        <?php endwhile; ?>
    </select>
</form>


    <?php if ($sucursal_id): ?>
        <?php
// (puedes agregar esto arriba para los badges)
function badgeEstado($estado) {
    switch (strtolower($estado)) {
        case 'excelente': return '<span class="badge bg-success">Excelente</span>';
        case 'bueno': return '<span class="badge bg-primary">Bueno</span>';
        case 'regular': return '<span class="badge bg-warning text-dark">Regular</span>';
        case 'deteriorado': return '<span class="badge bg-danger">Deteriorado</span>';
        case 'inservible': return '<span class="badge bg-secondary">Inservible</span>';
        default: return '<span class="badge bg-light text-dark">' . htmlspecialchars($estado) . '</span>';
    }
}
?>

<style>
@media (max-width: 578px) {
    /* Ocultar el selector "Mostrar X registros por página" */
    div.dataTables_length {
        display: none !important;
    }

    /* Reducir el tamaño del buscador */
    div.dataTables_filter input {
        width: 130px !important;
        font-size: 14px;
        padding: 4px 8px;
    }

    div.dataTables_filter {
        text-align: center;
    }
}
@media (max-width: 578px) {
    /* Estilo compacto para tabla sucursal */
    #tablaSucursal {
        font-size: 12px;
    }

    #tablaSucursal th,
    #tablaSucursal td {
        padding: 6px 8px !important;
        white-space: nowrap;
    }

    /* Habilita scroll horizontal si la tabla es ancha */
    #tablaSucursal {
        overflow-x: auto;
        display: block;
    }
}

</style>

<!-- PRODUCTOS -->
<h5 class="mt-4">📦 Productos en esta Sucursal</h5>
<table id="tablaSucursal" class="table table-bordered text-center align-middle">
<thead class="table-dark">
    <tr>
        <th>Tipo</th>
        <th>Nombre</th>
        <th>Categoría</th>
        <th>Total Enviado / Asignado</th>
        <th>Estado</th>
        <th>Acciones</th>
    </tr>
</thead>
<tbody>
<?php
// PRODUCTOS
$sql_productos = "
    SELECT 
        'Producto' AS tipo,
        p.idproducto,
        p.nombre,
        c.nombre AS categoria,
        p.estado,
        SUM(d.cantidad_enviada) AS total
    FROM envio_producto_detalle d
    JOIN envio_producto e ON d.envio_id = e.idenvio
    JOIN producto p ON d.producto_id = p.idproducto
    JOIN categoria c ON p.categoria_id = c.idcategoria
    WHERE e.ubicacion_id = $sucursal_id 
        AND p.eliminado = 0
        AND e.devuelto = 0 
    GROUP BY d.producto_id
";

$productos = $conn->query($sql_productos);
if (!$productos) {
    die("Error productos: " . $conn->error);
}

while ($row = $productos->fetch_assoc()): ?>
    <tr>
        <td><?= $row['tipo'] ?></td>
        <td><?= $row['nombre'] ?></td>
        <td><?= $row['categoria'] ?></td>
        <td><?= $row['total'] ?></td>
        <td><?= badgeEstado($row['estado']) ?></td>
        <td>
        <!-- Botón Editar con return_url -->
<button class="btn btn-sm btn-outline-primary btnAuth"
        data-id="<?= $row['idproducto'] ?>"
        data-tipo="producto"
        data-accion="editar"
        data-return="<?= urlencode($_SERVER['REQUEST_URI']) ?>">
  Editar
</button>

<!-- Botón Cambiar Estado -->
<button class="btn btn-sm btn-outline-warning btnAuth"
        data-id="<?= $row['idproducto'] ?>"
        data-tipo="producto"
        data-accion="estado">
  Cambiar Estado
</button>

        </td>
    </tr>
<?php endwhile; ?>

<?php
// ACTIVOS
$sql_activos = "
    SELECT 
        'Activo' AS tipo,
        a.idactivo,
        a.nombre,
        c.nombre AS categoria,
        SUM(d.cantidad_enviada) AS total,
        a.estado
    FROM envio_detalle d
    JOIN envio e ON d.envio_id = e.idenvio
    JOIN activos a ON d.activo_id = a.idactivo
    JOIN categoria c ON a.idcategoria = c.idcategoria
    WHERE e.ubicacion_id = $sucursal_id AND e.devuelto = 0
    GROUP BY d.activo_id

    UNION

    SELECT 
        'Activo' AS tipo,
        a.idactivo,
        a.nombre,
        c.nombre AS categoria,
        a.cantidad AS total,
        a.estado
    FROM activos a
    JOIN categoria c ON a.idcategoria = c.idcategoria
    WHERE a.idubicacion = $sucursal_id
      AND a.idactivo NOT IN (
        SELECT d.activo_id
        FROM envio_detalle d
        JOIN envio e ON d.envio_id = e.idenvio
        WHERE e.ubicacion_id = $sucursal_id
      )
";



$activos = $conn->query($sql_activos);
if (!$activos) {
    die("Error activos: " . $conn->error);
}

while ($row = $activos->fetch_assoc()): ?>
    <tr>
        <td><?= $row['tipo'] ?></td>
        <td><?= $row['nombre'] ?></td>
        <td><?= $row['categoria'] ?></td>
        <td><?= $row['total'] ?></td>
        <td><?= badgeEstado($row['estado']) ?></td>
        <td>
            <!-- Botón Editar Activo -->
<button class="btn btn-sm btn-outline-primary btnAuth"
        data-id="<?= $row['idactivo'] ?>"
        data-tipo="activo"
        data-accion="editar">
  Editar
</button>

<!-- Botón Cambiar Estado Activo -->
<button class="btn btn-sm btn-outline-warning btnAuth"
        data-id="<?= $row['idactivo'] ?>"
        data-tipo="activo"
        data-accion="estado">
  Cambiar Estado
</button>

        </td>
    </tr>
<?php endwhile; ?>

    </tbody>
</table>

<div class="modal fade" id="modalClaveGeneral" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formClaveGeneral" class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">Autenticación requerida</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="id_auth">
        <input type="hidden" id="tipo_auth">
        <input type="hidden" id="accion_auth">
        <input type="hidden" id="return_url_auth">
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
const esAdmin = "<?= $_SESSION['rol'] === 'Administrador' ? '1' : '0' ?>";

$(document).on("click", ".btnAuth", function () {
  const id = $(this).data("id");
  const tipo = $(this).data("tipo"); // "activo" o "producto"
  const accion = $(this).data("accion"); // "editar" o "estado"
  const returnUrl = "<?= urlencode($_SERVER['REQUEST_URI']) ?>";

  if (esAdmin === "1") {
    redirigirSegunAccion(id, tipo, accion, returnUrl);
  } else {
    $("#id_auth").val(id);
    $("#tipo_auth").val(tipo);
    $("#accion_auth").val(accion);
    $("#return_url_auth").val(returnUrl);
    const modal = new bootstrap.Modal(document.getElementById("modalClaveGeneral"));
    modal.show();
  }
});

function redirigirSegunAccion(id, tipo, accion, returnUrl = "") {
  if (accion === "editar") {
    let url = `editar_${tipo}.php?id=${id}`;
    if (tipo === "producto" && returnUrl) {
      url += `&return_url=${returnUrl}`;
    }
    window.location.href = url;
  } else {
    window.location.href = `cambiar_estado.php?tipo=${tipo}&id=${id}`;
  }
}

$("#formClaveGeneral").submit(function (e) {
  e.preventDefault();
  const id = $("#id_auth").val();
  const tipo = $("#tipo_auth").val();
  const accion = $("#accion_auth").val();
  const returnUrl = $("#return_url_auth").val();
  const clave = $(this).find("input[name='clave_admin']").val();

  $.post("validar_clave_admin.php", { clave_admin: clave }, function (res) {
    if (res.status === "success") {
      $("#modalClaveGeneral").modal("hide");
      redirigirSegunAccion(id, tipo, accion, returnUrl);
    } else {
      Swal.fire("Acceso denegado", res.message, "error");
    }
  }, "json");
});
</script>






<div class="modal fade" id="modalNuevoItem" tabindex="-1" aria-labelledby="modalNuevoItemLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="registrar_nuevo_item.php" method="POST" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalNuevoItemLabel">Nuevo Activo o Producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          
          <!-- Tipo -->
          <div class="mb-3">
            <label class="form-label">Tipo</label>
            <select name="tipo" class="form-select" required onchange="toggleNuevoTipo(this.value)">
              <option value="">Seleccionar...</option>
              <option value="producto">Producto</option>
              <option value="activo">Activo</option>
            </select>
          </div>

          <!-- Nombre -->
          <div class="mb-3">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
          </div>

          <!-- Precio (solo producto) -->
          <div class="mb-3 d-none" id="precioProducto">
            <label>Precio</label>
            <input type="number" step="0.01" name="precio" class="form-control">
          </div>

          <!-- Estado (solo activo) -->
          <div class="mb-3 d-none" id="estadoUnico">
            <label>Estado</label>
            <select name="estado" class="form-select">
              <option value="">Selecciona estado...</option>
              <option value="Excelente">Excelente</option>
              <option value="Bueno">Bueno</option>
              <option value="Regular">Regular</option>
              <option value="Deteriorado">Deteriorado</option>
              <option value="Inservible">Inservible</option>
            </select>
          </div>

          <!-- Categoría Producto -->
          <div class="mb-3 d-none" id="categoriaProducto">
            <label>Categoría (Producto)</label>
            <select name="categoria_id_producto" class="form-select">
              <?php
              $categorias = $conn->query("SELECT idcategoria, nombre FROM categoria");
              while ($c = $categorias->fetch_assoc()):
              ?>
                <option value="<?= $c['idcategoria'] ?>"><?= $c['nombre'] ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Categoría Activo -->
          <div class="mb-3 d-none" id="categoriaActivo">
            <label>Categoría (Activo)</label>
            <select name="categoria_id" class="form-select">
              <?php
              $categorias = $conn->query("SELECT idcategoria, nombre FROM categoria");
              while ($c = $categorias->fetch_assoc()):
              ?>
                <option value="<?= $c['idcategoria'] ?>"><?= $c['nombre'] ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- N° Asignación (solo activo) -->
          <div class="mb-3 d-none" id="nroAsignacion">
            <label>N° de Asignación</label>
            <input type="text" name="nro_asignacion" class="form-control">
          </div>

          <!-- Imagen (solo activo) -->
<div class="mb-3 d-none" id="imagenActivo">
  <label>Imagen</label>
  <input type="file" name="img" class="form-control" accept=".jpg,.jpeg,.png,.webp">
</div>


          <!-- Cantidad -->
          <div class="mb-3">
            <label>Cantidad</label>
            <input type="number" name="cantidad" class="form-control" min="1" required>
          </div>

          <!-- Sucursal -->
          <div class="mb-3">
            <label>Sucursal</label>
            <select name="sucursal_id" class="form-select" required>
              <?php
              $sucursales = $conn->query("SELECT idubicacion, nombre FROM ubicaciones");
              while ($s = $sucursales->fetch_assoc()):
              ?>
                <option value="<?= $s['idubicacion'] ?>"><?= $s['nombre'] ?></option>
              <?php endwhile; ?>
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>


<script>
function toggleNuevoTipo(tipo) {
    const show = id => document.getElementById(id).classList.remove('d-none');
    const hide = id => document.getElementById(id).classList.add('d-none');

    ['precioProducto', 'categoriaProducto', 'categoriaActivo', 'nroAsignacion', 'estadoUnico', 'imagenActivo'].forEach(hide);

if (tipo === 'producto') {
    show('precioProducto');
    show('categoriaProducto');
} else if (tipo === 'activo') {
    show('categoriaActivo');
    show('nroAsignacion');
    show('estadoUnico');
    show('imagenActivo'); // mostrar el campo imagen
}

}
</script>






<?php if (isset($_GET['ok'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        title: '¡Éxito!',
        text: '<?= $_GET['ok'] === "producto" ? "Producto" : "Activo" ?> agregado correctamente a la sucursal.',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    });
</script>
<?php endif; ?>




























<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#tablaSucursal').DataTable({
        pageLength: 100,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        }
    });
});
</script>

<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>