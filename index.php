<?php
include 'db.php';
session_start();

// Verificar si el usuario NO está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Define la consulta SQL
$sql = "SELECT p.idproducto, p.nombre, p.precio, p.estado, p.descripcion, 
               p.cantidad AS cantidad_total, 
               p.en_uso, 
               (p.cantidad - p.en_uso) AS disponibles, 
               p.nro_asignacion, 
               c.nombre AS categoria, 
               GROUP_CONCAT(DISTINCT CONCAT('ID ', tp.trabajo_id, ' (', t.tipo, ')') SEPARATOR ', ') AS trabajos_asignados
        FROM producto p
        LEFT JOIN categoria c ON p.categoria_id = c.idcategoria
        LEFT JOIN trabajo_producto tp ON p.idproducto = tp.producto_id
        LEFT JOIN trabajo t ON tp.trabajo_id = t.idtrabajo
        WHERE p.eliminado = 0
        GROUP BY p.idproducto, p.nombre, p.precio, p.estado, p.descripcion, 
                 p.cantidad, p.en_uso, p.nro_asignacion, c.nombre";

$result = $conn->query($sql);

// ✅ Verifica que la consulta sea válida
if (!$result) {
    die("<tr><td colspan='12' class='text-center text-danger'>❌ Error en la consulta: " . $conn->error . "</td></tr>");
}

// ✅ Cálculo del resumen
$totalProductosUnicos = 0;
$totalCantidades = 0;
$totalMontoProductos = 0;

$result->data_seek(0); // Reiniciar el puntero del resultado

while ($rowResumen = $result->fetch_assoc()) {
    $totalProductosUnicos++;
    $precio = (float) $rowResumen['precio'];
    $cantidad = (int) $rowResumen['cantidad_total'];

    $totalCantidades += $cantidad;
    $totalMontoProductos += $precio * $cantidad;
}


$result->data_seek(0); // Reiniciar otra vez para usarlo en la tabla
?>




<!DOCTYPE html>
<html lang="es">
<head>
<style>
  @media (max-width: 768px) {
    .navbar-brand img {
      height: 40px;
    }
    .logout-container {
      margin-top: 10px;
    }
  }
</style>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario</title>

    <!-- ✅ CSS Principal -->
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ✅ DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- ✅ Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">

    <!-- ✅ Scripts - Ahora se cargan al final del <body> -->
</head>


<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Inventario</a>
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
<div class="mb-4" style="max-width: 400px; margin-top: 10px;">
  <div class="card bg-light border-success">
    <div class="card-body p-3">
      <h6 class="mb-3 text-success text-center">📦 Resumen de Inventario</h6>

      <div class="d-flex justify-content-between">
        <span><strong>Productos únicos:</strong></span>
        <span><?= $totalProductosUnicos ?></span>
      </div>

      <div class="d-flex justify-content-between">
        <span><strong>Productos en total:</strong></span>
        <span><?= $totalCantidades ?></span>
      </div>

      <div class="d-flex justify-content-between">
        <span><strong>Valor total:</strong></span>
        <span>$<?= number_format($totalMontoProductos, 0, ',', '.') ?></span>
      </div>
    </div>
  </div>
</div>


<div class="container mt-4 ">
  <div class="row justify-content-center align-items-start g-3">

    <!-- 🟢 BOTONES -->
    <div class="col-12 col-md-auto text-center" style="margin-top: -73px">
      <div class="d-flex flex-wrap justify-content-center gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarProducto">
          Agregar Producto
        </button>

        <button type="button" class="btn btn-success" onclick="location.href='envio_productos_multiple.php'" style="width: 150px;">
          Envíos de Pdctos
        </button>

        <button type="button" class="btn btn-danger" onclick="location.href='devolucion_productos.php'" style="width: 130px;">
          Envios
        </button>

        <button type="button" class="btn btn-warning" onclick="location.href='sucursales.php'" style="width: 150px;">
          Sucursales
        </button>
      </div>
    </div>
    

    <!-- 🟡 FILTRO CATEGORÍA -->
    <div class="col-12 col-md-auto text-center">
      <div class="filtro-categoria-container">
        <label for="filtroCategoria" class="form-label mt-2 mt-md-0">Filtrar por Categoría:</label>
        <select id="filtroCategoria" class="form-select">
          <option value="">Todas las categorías</option>
          <?php
          $categorias = $conn->query("SELECT idcategoria, nombre FROM categoria ORDER BY nombre ASC");
          while ($cat = $categorias->fetch_assoc()) {
              echo "<option value='{$cat['nombre']}'>{$cat['nombre']}</option>";
          }
          ?>
        </select>
      </div>
    </div>

  </div>
</div>










<div class="container mt-4">
  <h2 class="text-center">Inventario de Productos</h2>
  <div class="table-responsive">
    <table id="productosTable" class="table table-striped table-bordered align-middle text-center">

        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Categoría</th>
                <th>Descripción</th>
                <th>Cantidad Total</th>
                <th>En Uso</th>
                <th>Disponibles</th>
                <th>Nro Asignación</th>
                <th>Trabajo Asignado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // ✅ Consulta optimizada para mostrar los valores correctos sin modificar cantidad_total
            $sql = "SELECT p.idproducto, p.nombre, p.precio, p.estado, p.descripcion, 
               p.cantidad AS cantidad_total, 
               p.en_uso, 
               (p.cantidad - p.en_uso) AS disponibles, 
               p.nro_asignacion, 
               c.nombre AS categoria, 
               GROUP_CONCAT(DISTINCT CONCAT('ID ', tp.trabajo_id, ' (', t.tipo, ')') SEPARATOR ', ') AS trabajos_asignados
        FROM producto p
        LEFT JOIN categoria c ON p.categoria_id = c.idcategoria
        LEFT JOIN trabajo_producto tp ON p.idproducto = tp.producto_id
        LEFT JOIN trabajo t ON tp.trabajo_id = t.idtrabajo
        WHERE p.eliminado = 0
        GROUP BY p.idproducto, p.nombre, p.precio, p.estado, p.descripcion, 
                 p.cantidad, p.en_uso, p.nro_asignacion, c.nombre";


            $result = $conn->query($sql);

            // ✅ Si la consulta falla, mostrar error
            if (!$result) {
                die("<tr><td colspan='12' class='text-center text-danger'>❌ Error en la consulta: " . $conn->error . "</td></tr>");
            }

            // ✅ Mostrar los productos sin modificar la cantidad total
            while ($row = $result->fetch_assoc()) {
                $precio_sin_formato = $row['precio'];
                $precio_formateado = "$" . number_format($row['precio'], 0, '', '.');

                // ✅ Asegurar que los valores sean números
                $cantidad_total = intval($row['cantidad_total']); // 🔹 Nunca se modifica
                $en_uso = intval($row['en_uso']); // 🔹 Calculado según trabajo_producto
                $disponibles = max($cantidad_total - $en_uso, 0); // 🔹 Se calcula dinámicamente

                // ✅ Mostrar trabajos asignados o "Sin asignar"
                $trabajo_asignado = !empty($row['trabajos_asignados']) ? $row['trabajos_asignados'] : "Sin asignar";

                echo '<tr>
    <td>' . $row['idproducto'] . '</td>
    <td>' . $row['nombre'] . '</td>
    <td data-order="' . $precio_sin_formato . '">' . $precio_formateado . '</td>
    <td>' . $row['estado'] . '</td>
    <td>' . $row['categoria'] . '</td>
    <td>' . $row['descripcion'] . '</td>
    <td>' . $cantidad_total . '</td>
    <td>' . $en_uso . '</td>
    <td>' . $disponibles . '</td>
    <td>' . $row['nro_asignacion'] . '</td>
    <td>' . $trabajo_asignado . '</td>
    <td class="text-nowrap">
        <!-- Botón Editar -->
        <a href="editar_producto.php?id=' . $row['idproducto'] . '&return_url=' . urlencode($_SERVER['REQUEST_URI']) . '" 
           class="btn btn-warning btn-sm btn-icon me-1" title="Editar">
           Editar
        </a>

        <!-- Botón Aumentar -->
        <form method="POST" action="actualizar_cantidad_producto.php" class="d-inline">
            <input type="hidden" name="idproducto" value="' . $row['idproducto'] . '">
            <input type="hidden" name="accion" value="aumentar">
            <button type="submit" 
                    class="btn btn-success btn-xs d-inline-flex align-items-center justify-content-center"
                    style="width: 30px; height: 30px; font-size: 12px; padding: 0;" 
                    title="Aumentar">
                +
            </button>
        </form>

        <!-- Botón Reducir -->
        <form method="POST" action="actualizar_cantidad_producto.php" class="d-inline" onsubmit="return confirmarReduccion()">
            <input type="hidden" name="idproducto" value="' . $row['idproducto'] . '">
            <input type="hidden" name="accion" value="reducir">
            <button type="submit" 
                    class="btn btn-danger btn-xs d-inline-flex align-items-center justify-content-center"
                    style="width: 30px; height: 30px; font-size: 12px; padding: 0;" 
                    title="Reducir">
                -
            </button>
        </form>
    </td>
</tr>';


            }
            ?>
        </tbody>
    </table>
</div>


<div style="width: 95%; max-width: 1300px; margin: 20px auto;">
  <!-- Aquí va tu contenido, como una tabla, tarjetas, gráficos, etc. -->
   <a> </a>
</div>




<!--------------------------------------------------------------------------------- Modal Agregar Producto ----------------------------------------------------------------------------------->
<div class="modal fade" id="modalAgregarProducto" tabindex="-1" aria-labelledby="modalAgregarProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="modalAgregarProductoLabel">Agregar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="guardar_producto.php" method="POST">
                    <div class="mb-3">
                        <label>Nombre del Producto:</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Precio:</label>
                        <input type="number" step="0.01" name="precio" class="form-control" required>
                    </div>
                    <div class="mb-3">
                            <label>Estado:</label>
                            <select name="estado" class="form-control">
                                <option value="Excelente">Excelente</option>
                                <option value="Bueno">Bueno</option>
                                <option value="Regular">Regular</option>
                                <option value="Deteriorado">Deteriorado</option>
                                <option value="Inservible">Inservible</option>
                            </select>
                        </div>
                    <div class="mb-3">
                        <label>Categoría:</label>
                        <select name="categoria_id" class="form-control select2">
                            <?php
                            include 'db.php';
                            $sql = "SELECT * FROM categoria"; // Cambiado de 'categorias' a 'categoria'
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['idcategoria']}'>{$row['nombre']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Descripción:</label>
                        <textarea name="descripcion" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Cantidad:</label>
                        <input type="number" name="cantidad" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Nro Asignación:</label>
                        <input type="text" name="nro_asignacion" class="form-control">
                    </div>
                    
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>













































<!-- ✅ Scripts - Cargados al final para mejorar rendimiento -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>








<!-- ✅ Inicialización de DataTables -->
<script>
$(document).ready(function() {
    // Inicializa la tabla
    var table = $('#productosTable').DataTable({
        "pageLength": 100,
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando página _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros disponibles",
            "infoFiltered": "(filtrado de _MAX_ registros en total)",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        }
    });

    // Filtro de categoría
    $('#filtroCategoria').on('change', function () {
        const selected = $(this).val();
        table.column(4).search(selected).draw(); // 4 = columna "Categoría"
    });
});
</script>

<script>
$(document).ready(function() {
    $('#modalAgregarProducto').on('shown.bs.modal', function () {
        $('.select2').select2({
            dropdownParent: $('#modalAgregarProducto')
        });
    });
});
</script>

<script>
<?php if (isset($_SESSION['cambio'])): ?>
    const accion = '<?= $_SESSION['cambio'] ?>';
    const audio = new Audio('pop.mp3');
    audio.play();

    Swal.fire({
        toast: true,
        position: 'bottom',
        icon: 'success',
        title: accion === 'aumentar' ? 'Cantidad aumentada correctamente' : 'Cantidad reducida correctamente',
        background: accion === 'aumentar' ? '#d4edda' : '#f8d7da',
        color: accion === 'aumentar' ? '#155724' : '#721c24',
        showConfirmButton: false,
        timer: 1300,
        timerProgressBar: true
    });

    <?php unset($_SESSION['cambio']); ?>
<?php endif; ?>
</script>







<?php if (isset($_SESSION['mensaje'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
  icon: '<?= $_SESSION['mensaje']['tipo'] ?>',
  title: '<?= $_SESSION['mensaje']['titulo'] ?>',
  text: '<?= $_SESSION['mensaje']['texto'] ?>',
});
</script>
<?php unset($_SESSION['mensaje']); ?>
<?php endif; ?>


</body>
</html>
