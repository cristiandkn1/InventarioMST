<?php include 'db.php'; ?>


<?php
session_start(); // Iniciar la sesión

// Verificar si el usuario NO está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redirige al login
    exit(); // Detiene la ejecución del script
}
?>


<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo_oc'])) {
    $archivo = $_FILES['archivo_oc'];
    $nombre = basename($archivo['name']);
    $ruta = 'ordenes_subidas/' . $nombre;
    $extensiones_permitidas = ['pdf', 'xlsx', 'xls', 'csv', 'docx', 'jpg', 'jpeg', 'png', 'webp', 'gif'];
    $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION)); // 👈 MOVER AQUÍ

    $usuario_id = $_SESSION['usuario_id'] ?? 0;

    if (in_array($extension, $extensiones_permitidas)) {
        // Comprimir si es imagen JPG/JPEG
        if (in_array($extension, ['jpg', 'jpeg'])) {
            $image = imagecreatefromjpeg($archivo['tmp_name']);
            imagejpeg($image, $ruta, 75); // Calidad 75%
            imagedestroy($image);
        } elseif (in_array($extension, ['png'])) {
            $image = imagecreatefrompng($archivo['tmp_name']);
            imagepng($image, $ruta, 7); // Compresión PNG
            imagedestroy($image);
        } else {
            move_uploaded_file($archivo['tmp_name'], $ruta);
        }

        // Guardar en historial
        $fecha = date("Y-m-d H:i:s");
        $accion = "Subida de Orden de Compra";
        $entidad = "orden_compra";
        $detalle = "Archivo subido: $nombre";

        $stmt = $conn->prepare("INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                                VALUES (?, ?, ?, NULL, ?, ?)");
        $stmt->bind_param("ssssi", $fecha, $accion, $entidad, $detalle, $usuario_id);
        $stmt->execute();
        $stmt->close();

        echo "<script>Swal.fire('✅ Éxito', 'Orden de compra subida correctamente.', 'success');</script>";
    } else {
        echo "<script>Swal.fire('⚠️ Archivo no válido', 'Se permiten PDF, Excel, Word e imágenes (jpg, png, etc).', 'warning');</script>";
    }
}
?>


<?php
// 🔴 Procesar eliminación del archivo
if (isset($_GET['eliminar']) && !empty($_GET['eliminar'])) {
    $archivo_eliminar = basename($_GET['eliminar']);
    $ruta = "ordenes_subidas/$archivo_eliminar";
    if (file_exists($ruta)) {
        unlink($ruta); // Eliminar archivo
        echo "<script>
            Swal.fire('✅ Eliminado', 'El archivo ha sido eliminado.', 'success');
            setTimeout(() => window.location.href = 'ordenesCompra.php', 1000);
        </script>";
    } else {
        echo "<script>Swal.fire('⚠️ Error', 'El archivo no existe.', 'error');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ordenes de Compra</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
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

<!-- 🔘 Botón para mostrar sección oculta -->
<div class="container mt-4">
  <button class="btn btn-outline-secondary mb-3" onclick="document.getElementById('subidaOC').classList.toggle('d-none')">
    📂 Ver Órdenes de Compra Subidas
  </button>
</div>

<!-- 🔒 Sección OCULTA por defecto -->
<div class="container mt-4 d-none" id="subidaOC">
  <h3 class="text-center mb-4">📂 Subir Orden de Compra Existente</h3>
  <form method="POST" enctype="multipart/form-data" class="mb-4">
    <div class="input-group">
      <input type="file" name="archivo_oc" class="form-control" required>
      <button class="btn btn-primary" type="submit">Subir</button>
    </div>
  </form>

  <h5 class="mt-4 mb-3">🧾 Órdenes de Compra Subidas</h5>
  <table class="table table-bordered table-hover bg-white">
    <thead class="table-dark">
      <tr>
        <th>Nombre del Archivo</th>
        <th>Ver / Descargar</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $archivos = glob('ordenes_subidas/*');
      if (count($archivos) == 0) {
          echo "<tr><td colspan='2' class='text-center'>No hay órdenes subidas</td></tr>";
      } else {
        foreach ($archivos as $archivo) {
            $nombre_archivo = basename($archivo);
            echo "<tr>
                    <td>$nombre_archivo</td>
                    <td>
                      <a href='$archivo' target='_blank' class='btn btn-sm btn-outline-primary'>Ver</a>
                      <a href='$archivo' download class='btn btn-sm btn-outline-success'>Descargar</a>
                      <a href='ordenesCompra.php?eliminar=$nombre_archivo' class='btn btn-sm btn-outline-danger'
                         onclick=\"return confirm('¿Estás seguro de eliminar este archivo?')\">Eliminar</a>
                    </td>
                  </tr>";
        }
        
      }
      ?>
    </tbody>
  </table>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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



</body>
</html>