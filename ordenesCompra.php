<?php include 'db.php'; ?>


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
    <title>Ordenes de Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Ordenes Compra</a>
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



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>