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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

</head>
<body>
<?php if (isset($_GET['msg']) && $_GET['msg'] == "Devolucion Exitosa 😁"): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            title: 'Éxito',
            text: 'La devolución se realizó correctamente.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then(() => {
            // Limpiar el parámetro de la URL después de mostrar la alerta
            window.history.replaceState(null, null, window.location.pathname);
        });
    </script>
<?php endif; ?>
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
    /* Reducir fuente y padding en la tabla de clientes */
    #clientesTable {
        font-size: 12px;
    }

    #clientesTable th,
    #clientesTable td {
        padding: 6px 8px !important;
        white-space: nowrap;
    }

    /* Scroll horizontal si es necesario */
    .dataTables_wrapper {
        overflow-x: auto;
    }
}


</style>



<!-- ✅ Contenido de Clientes -->
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2>Lista de Clientes</h2>
        <!-- ✅ Botón para abrir modal de nuevo cliente -->
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarCliente">Agregar Cliente</button>
    </div>

    <table id="clientesTable" class="table table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>RUT</th> <!-- ✅ Nueva columna agregada -->
                <th>Empresa</th>
                <th>Correo</th>
                <th>Contacto</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            include 'db.php'; // ✅ Asegurar conexión a la BD

            // ✅ Consulta SQL optimizada
            $sql = "SELECT idcliente, nombre, rut, empresa, correo, contacto, direccion FROM cliente ORDER BY idcliente DESC";
            $result = $conn->query($sql);

            if (!$result) {
                die("Error en la consulta: " . $conn->error);
            }

            while ($row = $result->fetch_assoc()) {
                echo "<tr>
        <td>{$row['idcliente']}</td>
        <td>{$row['nombre']}</td>
        <td>{$row['rut']}</td> <!-- ✅ Mostrar el RUT -->
        <td>{$row['empresa']}</td>
        <td>{$row['correo']}</td>
        <td>{$row['contacto']}</td>
        <td>{$row['direccion']}</td>
        <td>
            <button class='btn btn-warning btn-sm btnEditarCliente' data-id='{$row['idcliente']}'>Editar</button>
        </td>
      </tr>";
            }
            
            ?>
        </tbody>
    </table>
</div>

<!-- ✅ Modal para Agregar Cliente -->
<div class="modal fade" id="modalAgregarCliente" tabindex="-1" aria-labelledby="modalAgregarClienteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarClienteLabel">Agregar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="guardar_cliente.php" method="POST">
                    <div class="mb-3">
                        <label>Nombre del Cliente:</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>RUT:</label> <!-- ✅ Nuevo campo RUT -->
                        <input type="text" name="rut" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Nombre de la Empresa:</label>
                        <input type="text" name="empresa" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Correo:</label>
                        <input type="email" name="correo" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Contacto:</label>
                        <input type="text" name="contacto" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Dirección:</label>
                        <input type="text" name="direccion" class="form-control">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Modal Clave Admin para Editar Cliente -->
<div class="modal fade" id="modalClaveEditarCliente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formClaveEditarCliente" class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">Autenticación requerida</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editarCliente_id" name="id_cliente">
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
const esAdmin = "<?= $_SESSION['rol'] === 'Administrador' ? '1' : '0' ?>";

$(document).on("click", ".btnEditarCliente", function () {
  const id = $(this).data("id");

  if (esAdmin === "1") {
    // Administrador → redirigir directamente
    window.location.href = `editar_cliente.php?id=${id}`;
  } else {
    // Usuario → pedir contraseña
    $("#editarCliente_id").val(id);
    const modal = new bootstrap.Modal(document.getElementById("modalClaveEditarCliente"));
    modal.show();
  }
});

$("#formClaveEditarCliente").submit(function (e) {
  e.preventDefault();
  const id = $("#editarCliente_id").val();
  const clave = $(this).find("input[name='clave_admin']").val();

  $.post("validar_clave_admin.php", { clave_admin: clave }, function (res) {
    if (res.status === "success") {
      window.location.href = `editar_cliente.php?id=${id}`;
    } else {
      Swal.fire("Acceso denegado", res.message, "error");
    }
  }, "json");
});
</script>




<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#clientesTable').DataTable({
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/Spanish.json"
        },
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "pageLength": 10,
        "ordering": true,
        "searching": true,
        "info": true,
        "paging": true,
        "responsive": true,
        "autoWidth": false
    });
});
</script>



</body>



</html>