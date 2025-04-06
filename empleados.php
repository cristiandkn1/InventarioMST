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
    <title>Empleados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Empleados</a>
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

<!-- ✅ Contenido de Empleados -->
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2>Lista de Empleados</h2>
        <!-- ✅ Botón para agregar nuevo empleado -->
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarEmpleado">Agregar Empleado</button>
    </div>

    <!-- ✅ Tabla de empleados con buscador y contador -->
    <table id="empleadosTable" class="table table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>RUT</th> <!-- ✅ Nueva columna -->
                <th>Cargo</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Fecha de Ingreso</th>
                <th>Estado</th>
                <th>Fecha de Registro</th>
                <th>Trabajos Asignados</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            include 'db.php'; // ✅ Conexión a la BD

            // ✅ Obtener todos los empleados
            $sql_empleados = "SELECT * FROM empleado ORDER BY idempleado DESC";
            $empleados = $conn->query($sql_empleados);

            if (!$empleados) {
                die("Error en la consulta de empleados: " . $conn->error);
            }

            while ($emp = $empleados->fetch_assoc()) {
                // Obtener trabajos asignados que no están completados
                $sql_trabajos = "SELECT t.idtrabajo FROM trabajo t
                                 JOIN trabajo_trabajadores tt ON t.idtrabajo = tt.id_trabajo
                                 WHERE tt.id_trabajador = {$emp['idempleado']} AND t.estado != 'Completado'";

                $trabajos_result = $conn->query($sql_trabajos);
                $trabajos_asignados = [];
                while ($trabajo = $trabajos_result->fetch_assoc()) {
                    $trabajos_asignados[] = "#" . $trabajo['idtrabajo'];
                }

                $trabajo_texto = count($trabajos_asignados) > 0 ? implode(", ", $trabajos_asignados) : "No asignado";

                echo "<tr>
                        <td>{$emp['idempleado']}</td>
                        <td>{$emp['nombre']}</td>
                        <td>{$emp['rut']}</td> <!-- ✅ Mostrar RUT -->
                        <td>{$emp['cargo']}</td>
                        <td>{$emp['telefono']}</td>
                        <td>{$emp['correo']}</td>
                        <td>{$emp['fecha_ingreso']}</td>
                        <td>{$emp['estado']}</td>
                        <td>{$emp['fecha_creacion']}</td>
                        <td>$trabajo_texto</td>
                        <td>
                            <button class='btn btn-warning btn-sm btnEditarEmpleado' data-id='{$emp['idempleado']}'>Editar</button>
                        </td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Modal Clave Admin para Editar Empleado -->
<div class="modal fade" id="modalClaveEditarEmpleado" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formClaveEditarEmpleado" class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">Autenticación requerida</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editarEmpleado_id" name="id_empleado">
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

<!-- ✅ Integración con DataTables para buscador y contador -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

<script>
$(document).ready(function() {
    $('#empleadosTable').DataTable({
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
});
</script>
<script>
const esAdmin = "<?= $_SESSION['rol'] === 'Administrador' ? '1' : '0' ?>";

$(document).on("click", ".btnEditarEmpleado", function () {
  const id = $(this).data("id");

  if (esAdmin === "1") {
    window.location.href = `editar_empleado.php?id=${id}`;
  } else {
    $("#editarEmpleado_id").val(id);
    const modal = new bootstrap.Modal(document.getElementById("modalClaveEditarEmpleado"));
    modal.show();
  }
});

$("#formClaveEditarEmpleado").submit(function (e) {
  e.preventDefault();
  const id = $("#editarEmpleado_id").val();
  const clave = $(this).find("input[name='clave_admin']").val();

  $.post("validar_clave_admin.php", { clave_admin: clave }, function (res) {
    if (res.status === "success") {
      window.location.href = `editar_empleado.php?id=${id}`;
    } else {
      Swal.fire("Acceso denegado", res.message, "error");
    }
  }, "json");
});
</script>
<!-- ✅ Modal para Agregar Empleado -->
<div class="modal fade" id="modalAgregarEmpleado" tabindex="-1" aria-labelledby="modalAgregarEmpleadoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarEmpleadoLabel">Agregar Empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="guardarEmpleado.php" method="POST">
                    <div class="mb-3">
                        <label>Nombre del Empleado:</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>RUT:</label> <!-- ✅ Nuevo campo RUT -->
                        <input type="text" name="rut" class="form-control" placeholder="Ej: 12.345.678-9" required>
                    </div>
                    <div class="mb-3">
                        <label>Cargo:</label>
                        <input type="text" name="cargo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Teléfono:</label>
                        <input type="text" name="telefono" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Correo:</label>
                        <input type="email" name="correo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Fecha de Ingreso:</label>
                        <input type="date" name="fecha_ingreso" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Estado:</label>
                        <select name="estado" class="form-control">
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Empleado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ✅ Bootstrap & jQuery para manejar el modal -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Abre el modal cuando se presiona el botón de agregar empleado
    $("#modalAgregarEmpleado").modal({
        show: false,
        backdrop: "static"
    });
});
</script>

</body>
</html>