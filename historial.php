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
    <title>Historial de Movimientos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Historial</a>
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
<?php if ($_SESSION['rol'] === 'Administrador'): ?>
    <div class="d-flex justify-content-center" style="margin-top: 40px;">
        <button class="btn btn-danger mb-3" data-bs-toggle="modal" data-bs-target="#modalLimpiarHistorial">
             Limpiar Historial
        </button>
    </div>
<?php endif; ?>



<div class="container mt-4">
    <h2>Historial de Movimientos</h2>

    <!-- ✅ Contenedor con scroll horizontal -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th style="width: 80px;">Fecha</th>
                    <th style="width: 80px;">Acción</th>
                    <th style="width: 80px;">Entidad</th>
                    <th style="width: 200px;">Nombre de la Entidad</th> <!-- ✅ Ahora muestra el nombre -->
                    <th style="width: 80px;">Usuario</th>
                    <th style="max-width: 300px; white-space: nowrap; overflow-x: auto;">Detalles</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include 'db.php'; // ✅ Asegurar conexión a la BD
                
                // ✅ Consulta mejorada para obtener el nombre correcto de la entidad
                $sql = "SELECT h.fecha, h.accion, h.entidad,
       CASE 
           WHEN h.entidad = 'producto' THEN (SELECT nombre FROM producto WHERE idproducto = h.entidad_id)
           WHEN h.entidad = 'cliente' THEN (SELECT empresa FROM cliente WHERE idcliente = h.entidad_id)
           WHEN h.entidad = 'empleado' THEN (SELECT nombre FROM empleado WHERE idempleado = h.entidad_id)
           WHEN h.entidad = 'vehiculo' THEN (SELECT nombre FROM vehiculo WHERE idvehiculo = h.entidad_id)
           WHEN h.entidad = 'categoria' THEN (SELECT nombre FROM categoria WHERE idcategoria = h.entidad_id)
           WHEN h.entidad = 'trabajo' THEN (SELECT c.empresa FROM trabajo t JOIN cliente c ON t.cliente_id = c.idcliente WHERE t.idtrabajo = h.entidad_id)
           WHEN h.entidad = 'usuario' THEN (SELECT nombre FROM usuario WHERE idusuario = h.entidad_id)
           WHEN h.entidad = 'activo' THEN (SELECT nombre FROM activos WHERE idactivo = h.entidad_id)
WHEN h.entidad = 'trabajo' THEN (SELECT c.empresa FROM trabajo t JOIN cliente c ON t.cliente_id = c.idcliente WHERE t.idtrabajo = h.entidad_id)

           ELSE 'No registrado'
       END AS nombre_entidad,
       u.nombre AS usuario, h.detalle
FROM historial h
JOIN usuario u ON h.usuario_id = u.idusuario
ORDER BY h.fecha DESC";

                $result = $conn->query($sql);

                if (!$result) {
                    die("Error en la consulta: " . $conn->error);
                }

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['fecha']}</td>
                            <td>{$row['accion']}</td>
                            <td>{$row['entidad']}</td>
                            <td>{$row['nombre_entidad']}</td> <!-- ✅ Ahora mostrará correctamente el nombre -->
                            <td>{$row['usuario']}</td>
                            <td style='max-width: 300px; overflow-x: auto; white-space: nowrap;'>{$row['detalle']}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No hay registros en el historial</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<?php
// Obtener la fecha más antigua y cantidad de registros
$consulta_info = $conn->query("SELECT MIN(fecha) AS primera_fecha, COUNT(*) AS total FROM historial");
$info_historial = $consulta_info->fetch_assoc();
$primera_fecha = $info_historial['primera_fecha'];
$total_registros = $info_historial['total'];
?>

<!-- Iframe oculto para procesar la descarga sin salir de la página -->
<iframe name="descargaHistorial" style="display:none;"></iframe>

<!-- Modal Limpiar Historial -->
<div class="modal fade" id="modalLimpiarHistorial" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="limpiar_historial.php" method="POST" target="descargaHistorial" class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">🧹 Limpiar Historial</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-danger fw-bold">
          Esta acción eliminará permanentemente los registros del historial según el rango de fechas seleccionado.
        </p>

        <div class="mb-3">
          <label for="fecha_inicio" class="form-label">Desde (opcional):</label>
          <input type="date" class="form-control" name="fecha_inicio">
        </div>

        <div class="mb-3">
          <label for="fecha_fin" class="form-label">Hasta (opcional):</label>
          <input type="date" class="form-control" name="fecha_fin">
        </div>

        <div class="form-text mb-2">Si no seleccionas fechas, se eliminará <strong>todo el historial</strong>.</div>

        <div class="bg-light p-2 rounded">
          <p class="mb-1"><strong>📅 Primer registro:</strong> <?= $primera_fecha ? date("d-m-Y", strtotime($primera_fecha)) : "No hay registros" ?></p>
          <p class="mb-0"><strong>🧾 Total de registros:</strong> <?= $total_registros ?></p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="btnConfirmarLimpiarHistorial" class="btn btn-danger">Eliminar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>




<script>
document.getElementById("btnConfirmarLimpiarHistorial").addEventListener("click", function () {
    const form = document.querySelector("#modalLimpiarHistorial form");
    const fechaInicio = form.querySelector("input[name='fecha_inicio']").value;
    const fechaFin = form.querySelector("input[name='fecha_fin']").value;

    if (!fechaInicio && !fechaFin) {
        Swal.fire({
            title: "¿Estás seguro?",
            text: "No seleccionaste ninguna fecha. Esto eliminará todo el historial. ¿Deseas continuar?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar todo",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
                setTimeout(() => location.reload(), 500); // Recarga tras 1.5 segundos
            }
        });
    } else {
        form.submit();
        setTimeout(() => location.reload(), 500);
    }
});
</script>


<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>