<?php
include 'db.php';
session_start();

// Verificar si el usuario NO está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$trabajosPorMes = [];
$meses = [];

$sql = "SELECT DATE_FORMAT(fecha_creacion, '%Y-%m') AS mes, COUNT(*) AS cantidad
        FROM trabajo
        GROUP BY mes
        ORDER BY mes ASC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $meses[] = $row['mes'];
    $trabajosPorMes[] = $row['cantidad'];
}
?>

<?php
$empleadosNombres = [];
$empleadosTrabajos = [];

$sql = "SELECT e.nombre, COUNT(tt.id) AS total_trabajos
        FROM trabajo_trabajadores tt
        INNER JOIN empleado e ON tt.id_trabajador = e.idempleado
        GROUP BY tt.id_trabajador
        ORDER BY total_trabajos DESC
        LIMIT 10";


$result = $conn->query($sql);
if (!$result) {
    die("Error en la consulta de empleados: " . $conn->error);
}
while ($row = $result->fetch_assoc()) {
    $empleadosNombres[] = $row['nombre'];
    $empleadosTrabajos[] = $row['total_trabajos'];
}


?>

<?php
$productosNombres = [];
$productosUsados = [];

$sql = "SELECT p.nombre, SUM(tp.cantidad) AS total_usado
        FROM trabajo_producto tp
        INNER JOIN producto p ON tp.producto_id = p.idproducto
        GROUP BY tp.producto_id
        ORDER BY total_usado DESC
        LIMIT 10"; // Top 10 productos más usados

$result = $conn->query($sql);

if (!$result) {
    die("Error en la consulta de productos usados: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    $productosNombres[] = $row['nombre'];
    $productosUsados[] = $row['total_usado'];
}
?>
<?php
$productosDevueltos = [];
$cantidadesDevueltas = [];

$sql = "SELECT p.nombre, SUM(d.devueltos + d.usados) AS total
        FROM devoluciones d
        INNER JOIN trabajo_producto tp ON d.trabajo_producto_id = tp.idtrabajo_producto
        INNER JOIN producto p ON tp.producto_id = p.idproducto
        GROUP BY tp.producto_id
        ORDER BY total DESC
        LIMIT 10";

$result = $conn->query($sql);
if (!$result) die("Error en la consulta de devoluciones: " . $conn->error);

while ($row = $result->fetch_assoc()) {
    $productosDevueltos[] = $row['nombre'];
    $cantidadesDevueltas[] = $row['total'];
}
?>

<?php
$productosValor = [];
$valoresTotales = [];

$sql = "SELECT p.nombre, SUM((d.devueltos + d.usados) * p.precio) AS valor_total
        FROM devoluciones d
        INNER JOIN trabajo_producto tp ON d.trabajo_producto_id = tp.idtrabajo_producto
        INNER JOIN producto p ON tp.producto_id = p.idproducto
        GROUP BY tp.producto_id
        ORDER BY valor_total DESC
        LIMIT 10";

$result = $conn->query($sql);
if (!$result) die("Error en la consulta de valores perdidos: " . $conn->error);

while ($row = $result->fetch_assoc()) {
    $productosValor[] = $row['nombre'];
    $valoresTotales[] = $row['valor_total'];
}
?>
<?php
$promedioMensual = 0;

$sql = "SELECT DATE_FORMAT(fecha_creacion, '%Y-%m') AS mes, COUNT(*) AS cantidad
        FROM trabajo
        GROUP BY mes";

$result = $conn->query($sql);
$totalMeses = 0;
$totalTrabajos = 0;

while ($row = $result->fetch_assoc()) {
    $totalMeses++;
    $totalTrabajos += $row['cantidad'];
}

if ($totalMeses > 0) {
    $promedioMensual = round($totalTrabajos / $totalMeses, 2);
}
?>
<?php
$clientes = [];
$cantidades = [];

$sql = "SELECT c.empresa, COUNT(t.idtrabajo) AS cantidad
        FROM trabajo t
        INNER JOIN cliente c ON t.cliente_id = c.idcliente
        GROUP BY c.idcliente
        ORDER BY cantidad DESC
        LIMIT 10";

$result = $conn->query($sql);
if (!$result) die("Error clientes con más trabajos: " . $conn->error);

while ($row = $result->fetch_assoc()) {
    $clientes[] = $row['empresa'];
    $cantidades[] = $row['cantidad'];
}
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
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- ✅ Scripts - Ahora se cargan al final del <body> -->
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

<style>
    body {
    background-color:rgb(187, 185, 185); /* 🌑 Fondo gris oscuro */
    color:rgb(20, 20, 20);
  }
</style>

<style>
canvas {
    max-height: 100px; /* 🔽 Puedes ajustar este valor */
    width: 100% !important;
}
</style>












<div class="container mt-5">
<style>
  .card-dark {
    background-color: #1f1f1f;
    color: #f8f9fa;
    border: 2px solid #343a40;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.4);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .card-dark:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 24px rgba(0,0,0,0.6);
  }

  .card-dark h6 {
    color: #ffc107;
    font-weight: 600;
  }
</style>

<!-- 🎨 Estadísticas en modo oscuro -->
<div class="container mt-5">
  <div class="row g-4">
    <!-- 📅 Trabajos por mes -->
    <div class="col-md-4">
      <div class="card card-dark p-3">
      <h6 class="text-center">
  <i class="fas fa-calendar-alt me-2 text-info"></i> Trabajos por Mes
</h6>      <canvas id="graficoTrabajos"></canvas>
      </div>
    </div>

    <!-- 👷 Empleados -->
    <div class="col-md-4">
      <div class="card card-dark p-3">
      <h6 class="text-center">
  <i class="fas fa-user-cog me-2 text-warning"></i> Empleados con más trabajos
</h6>      <canvas id="graficoEmpleados"></canvas>
      </div>
    </div>

    <!-- 🔧 Productos usados -->
<div class="col-md-4">
  <div class="card card-dark p-3">
    <h6 class="text-center mb-3">
      <i class="fas fa-tools me-2 text-success"></i> Productos más usados
    </h6>

    <ul class="list-group list-group-flush">
      <?php
      for ($i = 0; $i < count($productosNombres); $i++) {
          $nombre = htmlspecialchars($productosNombres[$i]);
          $cantidad = (int)$productosUsados[$i];
          echo "<li class='d-flex justify-content-between align-items-center bg-dark text-white border-bottom'>
        $nombre
        <span class='badge bg-success rounded-pill'>$cantidad</span>
      </li>";
      }
      ?>
    </ul>
  </div>
</div>

<style>
  .card-dark {
    background-color: #1f1f1f;
    color: #f1f1f1;
    border: 2px solid #343a40;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .card-dark:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 20px rgba(0,0,0,0.5);
  }

  .card-dark .card-header {
    background-color: #343a40 !important;
    color: #ffc107;
    font-size: 1.1rem;
  }

  .card-dark .text-success {
    color: #28a745 !important;
  }

  .card-dark .text-danger {
    color: #dc3545 !important;
  }

  .card-dark .highlight {
    font-weight: bold;
    color: #ffc107;
  }
</style>

<div class="container mt-5">
  <div class="row justify-content-center g-4">

    <!-- 🔁 Productos devueltos / perdidos -->
    <div class="col-md-6 col-lg-5">
      <div class="card card-dark shadow-lg rounded-4">
        <div class="card-header text-center">
        <i class="fas fa-undo-alt me-2 text-warning"></i> Productos Devueltos / Perdidos
        </div>
        <div class="card-body">
          <?php
          $sql1 = "SELECT p.nombre, 
                          SUM(d.devueltos) AS total_devueltos, 
                          SUM(d.usados) AS total_perdidos
                   FROM devoluciones d
                   INNER JOIN trabajo_producto tp ON d.trabajo_producto_id = tp.idtrabajo_producto
                   INNER JOIN producto p ON tp.producto_id = p.idproducto
                   GROUP BY tp.producto_id
                   ORDER BY SUM(d.devueltos) + SUM(d.usados) DESC
                   LIMIT 5";

          $result1 = $conn->query($sql1);
          if ($result1 && $result1->num_rows > 0):
              while ($row = $result1->fetch_assoc()):
          ?>
            <div class="mb-3 border-bottom pb-2">
              <strong class="highlight"><?= htmlspecialchars($row['nombre']) ?></strong><br>
              Devueltos: <span class="text-success"><?= $row['total_devueltos'] ?></span> |
              Perdidos: <span class="text-danger"><?= $row['total_perdidos'] ?></span> |
              Total: <strong><?= $row['total_devueltos'] + $row['total_perdidos'] ?></strong>
            </div>
          <?php endwhile; else: ?>
            <p class="text-muted text-center">No hay datos disponibles.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- 🧾 Valor total devuelto / perdido -->
    <div class="col-md-6 col-lg-5">
      <div class="card card-dark shadow-lg rounded-4">
        <div class="card-header text-center">
        <i class="fas fa-dollar-sign me-2 text-danger"></i> Valor Total Devuelto / Perdido
        </div>
        <div class="card-body">
          <?php
          $sql2 = "SELECT p.nombre, p.precio,
                          SUM(d.devueltos) AS total_devueltos, 
                          SUM(d.usados) AS total_perdidos
                   FROM devoluciones d
                   INNER JOIN trabajo_producto tp ON d.trabajo_producto_id = tp.idtrabajo_producto
                   INNER JOIN producto p ON tp.producto_id = p.idproducto
                   GROUP BY tp.producto_id
                   ORDER BY (SUM(d.devueltos) + SUM(d.usados)) * p.precio DESC
                   LIMIT 5";

          $result2 = $conn->query($sql2);
          if ($result2 && $result2->num_rows > 0):
              while ($row = $result2->fetch_assoc()):
                  $valor_devuelto = $row['total_devueltos'] * $row['precio'];
                  $valor_perdido = $row['total_perdidos'] * $row['precio'];
                  $total_valor = $valor_devuelto + $valor_perdido;
          ?>
            <div class="mb-3 border-bottom pb-2">
              <strong class="highlight"><?= htmlspecialchars($row['nombre']) ?></strong><br>
              Devuelto: <span class="text-success">$<?= number_format($valor_devuelto, 0, ',', '.') ?></span> |
              Perdido: <span class="text-danger">$<?= number_format($valor_perdido, 0, ',', '.') ?></span><br>
              Total: <strong>$<?= number_format($total_valor, 0, ',', '.') ?></strong>
            </div>
          <?php endwhile; else: ?>
            <p class="text-muted text-center">No hay datos disponibles.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>


<!-- 👥 Clientes con más trabajos -->
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
      <div class="card card-dark p-3">
      <h6 class="text-center">
            <i class="fas fa-users me-2 text-info"></i> Clientes con más trabajos
        </h6>      <canvas id="graficoClientes"></canvas>
      </div>
    </div>
  </div>
</div>



<!-- 📉 Productos con Stock Bajo -->
<div class="container mt-5">
  <div class="card bg-dark text-white shadow-sm rounded-4">
    <div class="card-header bg-danger text-white text-center fw-bold">
    <h6 class="text-center">
    <i class="fas fa-exclamation-triangle me-2" style="color: #fffdd0;"></i> Productos con Stock Bajo
    </h6>   
</div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-dark table-hover table-sm">
          <thead>
            <tr>
              <th>Producto</th>
              <th>Disponibles</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $umbral = 5;
            $sql = "SELECT nombre, disponibles 
                    FROM producto 
                    WHERE disponibles <= $umbral AND eliminado = 0 
                    ORDER BY disponibles ASC 
                    LIMIT 10";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['nombre']) . "</td>
                        <td class='text-danger fw-bold'>" . $row['disponibles'] . "</td>
                      </tr>";
              }
            } else {
                echo "<tr><td colspan='2' class='text-center text-white'>No hay productos con bajo stock</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>


<!-- 🏢 Inventario por Sucursal (Productos + Activos) -->
<div class="container mt-5">
  <div class="card bg-dark text-white shadow-sm rounded-4 border border-primary">
    <div class="card-header bg-primary text-white text-center fw-bold">
    <h6 class="text-center">
    <i class="fas fa-warehouse me-2 text-white"></i> Inventario por Sucursal (Productos y Activos)
    </h6>    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-dark table-hover table-sm align-middle text-center" id="tablaSucursalInventario">
          <thead>
            <tr>
              <th>Sucursal</th>
              <th>Tipo</th>
              <th>Nombre</th>
              <th>Cantidad</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $hayDatos = false;
            $contador = 0;

            // Productos
            $sqlProductos = "SELECT u.nombre AS sucursal, 'Producto' AS tipo, p.nombre, SUM(epd.cantidad_enviada) AS cantidad
                             FROM envio_producto_detalle epd
                             INNER JOIN producto p ON epd.producto_id = p.idproducto
                             INNER JOIN envio_producto ep ON epd.envio_id = ep.idenvio
                             INNER JOIN ubicaciones u ON ep.ubicacion_id = u.idubicacion
                             WHERE p.eliminado = 0
                             GROUP BY u.nombre, p.nombre";

            $resultProductos = $conn->query($sqlProductos);
            if ($resultProductos && $resultProductos->num_rows > 0) {
              $hayDatos = true;
              while ($row = $resultProductos->fetch_assoc()) {
                $extra = ($contador >= 10) ? "class='fila-extra d-none'" : "";
                echo "<tr $extra>
                        <td>{$row['sucursal']}</td>
                        <td><span class='badge bg-success'>{$row['tipo']}</span></td>
                        <td>" . htmlspecialchars($row['nombre']) . "</td>
                        <td class='fw-bold'>{$row['cantidad']}</td>
                      </tr>";
                $contador++;
              }
            }

            // Activos
            $sqlActivos = "SELECT u.nombre AS sucursal, 'Activo' AS tipo, a.nombre, SUM(ed.cantidad_enviada) AS cantidad
                           FROM envio_detalle ed
                           INNER JOIN activos a ON ed.activo_id = a.idactivo
                           INNER JOIN envio e ON ed.envio_id = e.idenvio
                           INNER JOIN ubicaciones u ON e.ubicacion_id = u.idubicacion
                           GROUP BY u.nombre, a.nombre";

            $resultActivos = $conn->query($sqlActivos);
            if ($resultActivos && $resultActivos->num_rows > 0) {
              while ($row = $resultActivos->fetch_assoc()) {
                $extra = ($contador >= 10) ? "class='fila-extra d-none'" : "";
                echo "<tr $extra>
                        <td>{$row['sucursal']}</td>
                        <td><span class='badge bg-warning text-dark'>{$row['tipo']}</span></td>
                        <td>" . htmlspecialchars($row['nombre']) . "</td>
                        <td class='fw-bold'>{$row['cantidad']}</td>
                      </tr>";
                $contador++;
              }
            }

            if (!$hayDatos) {
              echo "<tr><td colspan='4' class='text-center text-white'>No hay inventario registrado por sucursal.</td></tr>";
            }
            ?>
          </tbody>
        </table>
        <?php if ($contador > 10): ?>
          <div class="text-center mt-3">
            <button class="btn btn-outline-light btn-sm" id="btnVerMas">Ver más</button>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>



<!-- 📦 Movimientos entre Sucursales y 💸 Valor de Pérdidas por Mes -->
<div class="container mt-5">
  <div class="row g-4">

    <!-- 📦 Movimientos por mes -->
    <div class="col-md-6">
      <div class="card bg-dark text-white shadow-sm rounded-4 border border-info">
        <div class="card-header bg-info text-white text-center fw-bold">
        <h6 class="text-center">
        <i class="fas fa-exchange-alt me-2 text-white"></i> Movimientos Entre Sucursales por Mes
        </h6>        </div>
        <div class="card-body">
          <?php
          $sqlMov = "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, COUNT(*) AS total
                     FROM envio_producto
                     GROUP BY mes ORDER BY mes ASC";
          $resultMov = $conn->query($sqlMov);

          if ($resultMov && $resultMov->num_rows > 0) {
            while ($row = $resultMov->fetch_assoc()) {
              echo "<div class='mb-2'>
                      <strong class='text-info'>{$row['mes']}</strong>: {$row['total']} movimientos
                    </div>";
            }
          } else {
            echo "<p class='text-center text-white'>No hay movimientos registrados.</p>";
          }
          ?>
        </div>
      </div>
    </div>

    <!-- 💸 Valor perdido por mes -->
    <div class="col-md-6">
      <div class="card bg-dark text-white shadow-sm rounded-4 border border-danger">
        <div class="card-header bg-danger text-white text-center fw-bold">
        <h6 class="text-center">
        <i class="fas fa-money-bill-wave me-2 text-white"></i> Valor Total Perdido por Mes
        </h6>        </div>
        <div class="card-body">
          <?php
          $sqlPerdidas = "SELECT DATE_FORMAT(epd.fecha_devolucion, '%Y-%m') AS mes, 
                                 SUM(epd.cantidad_perdida * p.precio) AS total_perdido
                          FROM envio_producto_detalle epd
                          INNER JOIN producto p ON epd.producto_id = p.idproducto
                          WHERE epd.cantidad_perdida > 0
                          GROUP BY mes
                          ORDER BY mes ASC";
          $resultPerdidas = $conn->query($sqlPerdidas);

          if ($resultPerdidas && $resultPerdidas->num_rows > 0) {
            while ($row = $resultPerdidas->fetch_assoc()) {
              $valor = $row['total_perdido'] ?? 0;
              echo "<div class='mb-2'>
                      <strong class='text-danger'>{$row['mes']}</strong>: $".number_format($valor, 0, ',', '.')." perdidos
                    </div>";
            }
          } else {
            echo "<p class='text-center text-white'>No hay pérdidas registradas.</p>";
          }
          ?>
        </div>
      </div>
    </div>

  </div>
</div>



<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
const ctxMov = document.getElementById('graficoMovimientos').getContext('2d');
new Chart(ctxMov, {
    type: 'bar',
    data: {
        labels: <?= json_encode($movMeses) ?>,
        datasets: [{
            label: 'Movimientos entre sucursales',
            data: <?= json_encode($movTotales) ?>,
            backgroundColor: 'rgba(13, 202, 240, 0.6)',
            borderColor: '#0dcaf0',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});
// 💸 Gráfico de pérdidas por mes
const ctxPerdidas = document.getElementById('graficoPerdidas').getContext('2d');
new Chart(ctxPerdidas, {
    type: 'bar',
    data: {
        labels: <?= json_encode($perdidasMes) ?>,
        datasets: [{
            label: 'Valor perdido ($)',
            data: <?= json_encode($valoresPerdidos) ?>,
            backgroundColor: 'rgba(220, 53, 69, 0.6)',
            borderColor: '#dc3545',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<script>
const ctxTrabajos = document.getElementById('graficoTrabajos').getContext('2d');
const graficoTrabajos = new Chart(ctxTrabajos, {
    type: 'bar',
    data: {
        labels: <?= json_encode($meses) ?>,
        datasets: [{
            label: 'Trabajos creados',
            data: <?= json_encode($trabajosPorMes) ?>,
            backgroundColor: 'rgba(13, 110, 253, 0.6)',
            borderColor: '#0d6efd',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<script>
    const ctxEmpleados = document.getElementById('graficoEmpleados').getContext('2d');
const graficoEmpleados = new Chart(ctxEmpleados, {
    type: 'bar',
    data: {
        labels: <?= json_encode($empleadosNombres) ?>,
        datasets: [{
            label: 'Trabajos asignados',
            data: <?= json_encode($empleadosTrabajos) ?>,
            backgroundColor: 'rgba(255, 193, 7, 0.6)', // amarillo Bootstrap
            borderColor: '#ffc107',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

</script>
<script>
    const ctxProductosUsados = document.getElementById('graficoProductosUsados').getContext('2d');
const graficoProductosUsados = new Chart(ctxProductosUsados, {
    type: 'bar',
    data: {
        labels: <?= json_encode($productosNombres) ?>,
        datasets: [{
            label: 'Cantidad Asignada',
            data: <?= json_encode($productosUsados) ?>,
            backgroundColor: 'rgba(25, 135, 84, 0.6)', // verde Bootstrap
            borderColor: '#198754',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

</script>
<script>
    const ctxDevoluciones = document.getElementById('graficoDevoluciones').getContext('2d');
const graficoDevoluciones = new Chart(ctxDevoluciones, {
    type: 'bar',
    data: {
        labels: <?= json_encode($productosDevueltos) ?>,
        datasets: [{
            label: 'Total Devueltos + Perdidos',
            data: <?= json_encode($cantidadesDevueltas) ?>,
            backgroundColor: 'rgba(220, 53, 69, 0.6)',
            borderColor: '#dc3545',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                beginAtZero: true
            }
        }
    }
});

</script>
<script>
const ctxValor = document.getElementById('graficoValorPerdido').getContext('2d');
const graficoValor = new Chart(ctxValor, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($productosValor) ?>,
        datasets: [{
            label: 'Valor Devuelto o Perdido ($)',
            data: <?= json_encode($valoresTotales) ?>,
            backgroundColor: [
                '#0d6efd', '#198754', '#dc3545', '#ffc107',
                '#6610f2', '#20c997', '#fd7e14', '#6f42c1',
                '#0dcaf0', '#adb5bd'
            ],
            borderColor: '#fff',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 14
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const value = context.raw;
                        return `${context.label}: $${value.toLocaleString()}`;
                    }
                }
            }
        }
    }
});
</script>
<script>
    const ctxClientes = document.getElementById('graficoClientes').getContext('2d');
const graficoClientes = new Chart(ctxClientes, {
    type: 'bar',
    data: {
        labels: <?= json_encode($clientes) ?>,
        datasets: [{
            label: 'Trabajos asignados',
            data: <?= json_encode($cantidades) ?>,
            backgroundColor: 'rgba(13, 202, 240, 0.6)',
            borderColor: '#0dcaf0',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                beginAtZero: true
            }
        }
    }
});

</script>

<script>
  document.getElementById('btnVerMas')?.addEventListener('click', function () {
    const filas = document.querySelectorAll('.fila-extra');
    filas.forEach(fila => fila.classList.toggle('d-none'));
    this.textContent = this.textContent === 'Ver más' ? 'Ver menos' : 'Ver más';
  });
</script>
</body>
</html>
