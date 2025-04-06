<?php
include 'db.php';
session_start(); // ✅ Iniciar sesión

// ✅ Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Obtener todos los trabajos
$sql = "SELECT t.idtrabajo, t.tipo, t.titulo, t.nro_orden, t.fecha_creacion,  t.fecha_entrega, t.estado, c.nombre AS cliente
        FROM trabajo t
        LEFT JOIN cliente c ON t.cliente_id = c.idcliente
        ORDER BY t.fecha_creacion DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Error en la consulta: " . $conn->error);
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trabajos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- ✅ SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    
    
    
    <link rel="stylesheet" href="css/style.css">

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
    /* Reducir fuente y espaciado en la tabla de trabajos */
    #trabajosTable {
        font-size: 12px;
    }

    #trabajosTable th,
    #trabajosTable td {
        padding: 6px 8px !important;
        white-space: nowrap;
    }

    /* Scroll horizontal si es necesario */
    .dataTables_wrapper {
        overflow-x: auto;
    }
}

</style>


<!-- ✅ Contenido de Trabajos -->
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2>Lista de Trabajos</h2>
        <!-- ✅ Botón para abrir el modal de Agregar OT -->
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarTrabajo">Agregar OT</button>
    </div>

    <!-- ✅ Tabla de trabajos con buscador y contador -->
    <table id="trabajosTable" class="table table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>Título</th>
                <th>N° Orden</th>
                <th>Cliente</th>
                <th>Fecha de Creación</th>
                <th>Fecha de Entrega</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['idtrabajo']; ?></td>
                    <td><?php echo $row['tipo']; ?></td>
                    <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                    <td><?php echo htmlspecialchars($row['nro_orden']); ?></td>
                    <td><?php echo htmlspecialchars($row['cliente'] ?? 'Sin Cliente'); ?></td>
                    <td><?php echo date("d-m-Y", strtotime($row['fecha_creacion'])); ?></td>
                    <td><?php echo date("d-m-Y", strtotime($row['fecha_entrega'])); ?></td>
                    <td><?php echo $row['estado']; ?></td>
                    <td>
                    <button class="btn btn-warning btn-sm btnEditarTrabajo"
        data-id="<?php echo $row['idtrabajo']; ?>">
  Editar
</button>
                        <a href="ver_trabajo.php?id=<?php echo $row['idtrabajo']; ?>" class="btn btn-info btn-sm">Ver</a>
                        <a href="#" class="btn btn-secondary btn-sm btnDevolverMaterial" data-idtrabajo="<?php echo $row['idtrabajo']; ?>">Devolver Material</a>

                    </td>
                                
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>


<!-- ✅ Integración con DataTables para buscador y contador -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- ✅ jQuery debe cargarse primero -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- ✅ DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<!-- ✅ Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

<!-- ✅ Bootstrap (Debe cargarse después de jQuery y Select2) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<script>
$(document).ready(function() {
    $('#trabajosTable').DataTable({
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
});
</script>




<!-- Modal Clave Admin para Editar Trabajo -->
<div class="modal fade" id="modalClaveEditarTrabajo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formClaveEditarTrabajo" class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">Autenticación requerida</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editarTrabajo_id" name="id_trabajo">
        <label>Contraseña de administrador:</label>
        <input type="password" name="clave_admin" class="form-control" required>
      </div>
      <div class="modal-footer">
      <button type="button" class="btn btn-success" id="btnConfirmarClaveEditarTrabajo">Confirmar</button>
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

      </div>
    </form>
  </div>
</div>


<script>
const esAdmin = "<?= $_SESSION['rol'] === 'Administrador' ? '1' : '0' ?>";

$(document).on("click", ".btnEditarTrabajo", function () {
  const id = $(this).data("id");

  if (esAdmin === "1") {
    window.location.href = `editar_trabajo.php?id=${id}`;
  } else {
    $("#editarTrabajo_id").val(id);
    const modal = new bootstrap.Modal(document.getElementById("modalClaveEditarTrabajo"));
    modal.show();
  }
});

$("#btnConfirmarClaveEditarTrabajo").on("click", function () {
  const id = $("#editarTrabajo_id").val();
  const clave = $("input[name='clave_admin']").val();

  $.post("validar_clave_admin.php", { clave_admin: clave }, function (res) {
    if (res.status === "success") {
      $("#modalClaveEditarTrabajo").modal("hide");
      window.location.href = `editar_trabajo.php?id=${id}`;
    } else {
      Swal.fire("Acceso denegado", res.message, "error");
    }
  }, "json");
});
</script>












<!-- ✅ MODAL PARA AGREGAR TRABAJO (OT/OM) -->
<div class="modal fade" id="modalAgregarTrabajo" tabindex="-1" aria-labelledby="modalAgregarTrabajoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl"> <!-- ⬅️ Aumentamos el tamaño con modal-xl -->
        <div class="modal-content" style="max-width: 100%; margin: auto;"> <!-- ⬅️ Expandimos el modal -->
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarTrabajoLabel">Agregar Orden de Trabajo (OT/OM)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="guardar_trabajo.php" method="POST">


                    <div class="mb-3">
                        <label>Tipo de Trabajo:</label>
                        <select name="tipo" class="form-control" required>
                            <option value="OT">Orden de Trabajo (OT)</option>
                            <option value="OM">Orden de Mantenimiento (OM)</option>
                        </select>
                    </div>

                    <div class="mb-3 d-flex">
    <!-- Título -->
    <div class="me-2" style="flex: 1;">
        <label>Descripcion OT:</label>
        <input type="text" name="titulo" class="form-control" style="height: 40px;" required>
    </div>

    <!-- Empresa -->
    <div style="flex: 1;">
        <label>Empresa Mandante:</label>
        <select name="cliente_id" class="form-control select2" required>
            <option value="">Seleccione una Empresa</option>
            <?php
            // Cargar empresas desde la BD
            $empresas_sql = "SELECT idcliente, empresa FROM cliente ORDER BY empresa ASC";
            $empresas_result = $conn->query($empresas_sql);
            while ($empresa = $empresas_result->fetch_assoc()) {
                echo "<option value='{$empresa['idcliente']}'>{$empresa['empresa']}</option>";
            }
            ?>
        </select>
    </div>
</div>    

<div class="mb-3 d-flex">
    <!-- Columna 1 -->
    <div class="me-3" style="flex: 1;">
        <label>Descripción de la Orden:</label>
        <textarea name="descripcion_orden" class="form-control" rows="1" style="height: 50px; resize: none;"></textarea>
        
        <label class="mt-2">N° Aviso:</label>
        <input type="text" name="nro_aviso" class="form-control">

        <label class="mt-2">Ubicación Técnica:</label>
        <input type="text" name="ubicacion_tecnica" class="form-control">

        <label class="mt-2">Equipo:</label>
        <input type="text" name="equipo" class="form-control" >

        <label class="mt-2">Denominación del Equipo:</label>
        <input type="text" name="den_equipo" class="form-control" placeholder="Ejemplo: Cambio de Rodamiento...." required>
    </div>

    <!-- Columna 2 -->
    <div style="flex: 1;">
        <label>Clase de Orden:</label>
        <input type="text" name="clase_orden" class="form-control">

        <label class="mt-2">Clase de Actividad PL:</label>
        <input type="text" name="clase_actividad_pl" class="form-control">

        <label class="mt-2">Prioridad:</label>
        <input type="text" name="prioridad" class="form-control" placeholder="Baja, Media, Alta, Urgente">

        <label class="mt-2">Revisión:</label>
        <input type="text" name="revision" class="form-control" placeholder="Ejemplo: Primera Revision">

        <label class="mt-2">Grupo de Planificación:</label>
        <input type="text" name="grp_planificacion" class="form-control">
    </div>
</div>


<div class="mb-3 d-flex">
    <!-- Columna 1 -->
    <div class="me-3" style="flex: 1;">
        <label>Punto de Trabajo Responsable:</label>
        <input type="text" name="pto_trab_responsable" class="form-control">

        <div class="mb-3">
            <label>Fecha de Entrega:</label>
            <input type="date" name="fecha_entrega" class="form-control" required>
        </div>
                
        

        <label class="mt-2">Hora de Fin:</label>
        <input type="time" name="hora_fin" class="form-control" >

        <label>Duración Estimada (Días):</label>
        <input type="number" name="duracion_dias" class="form-control" placeholder="1, 2, 3, 4 . . . . Días">
    </div>

    <!-- Columna 2 -->
    <div style="flex: 1;">
    <label class="mt-2">Fecha de Inicio:</label>
    <input type="date" name="fecha_inicio" class="form-control" required>

        <label class="mt-2">Hora de Inicio:</label>
        <input type="time" name="hora_inicio" class="form-control">
        

        <label class="mt-2">Reserva:</label>
        <input type="text" name="reserva" class="form-control" placeholder="Numero de reserva" onblur="if(this.value=='')">
    </div>
</div>


                    <div class="mb-3">
                        <label>Solicitud de Pedido:</label>
                        <select name="sol_ped" class="form-control">
                            <option value="Servicios">Servicios</option>
                            <option value="Materiales">Materiales</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Descripción Detallada de la Orden:</label>
                        <textarea name="descripcion_detallada" class="form-control" rows="3" placeholder="Ingrese una descripción detallada aquí..."></textarea>
                    </div>



                    <div class="mb-3">
                    <label>Operaciones:</label>
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Operación</th>
                                <th>Pto. Trab.</th>
                                <th>Desc. Pto. Trab.</th>
                                <th>Desc. Oper.</th>
                                <th>N° Pers.</th>
                                <th>H Est.</th>
                                <th>HH Tot. Prog.</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="operacionesBody">
                            <!-- Filas dinámicas se agregarán aquí -->
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success" onclick="agregarOperacion()">Agregar Operación</button>
                </div>



                <div class="mb-3">
    <label>Materiales:</label>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Producto</th>
                    <th>Descripción</th>
                    <th style="width: 5%;">Nro. Asignacion</th>
                    <th style="width: 5%;">Cant. Disp</th>
                    <th style="width: 5%;">Cantidad</th>
                    <th style="width: 5%;">Acción</th>
                </tr>
            </thead>
            <tbody id="materialesBody">
                <!-- Filas dinámicas se agregarán aquí -->
            </tbody>
        </table>
    </div>

    <!-- ✅ Select para seleccionar materiales -->
    <div class="mb-3">
        <label for="materialSelect">Selecciona un Producto:</label>
        <select id="materialSelect" class="form-control select2-material">
    <option value="">Seleccione un Producto</option>
    <?php
    include 'db.php';
    $productos_sql = "SELECT idproducto, nombre, descripcion, nro_asignacion, cantidad, en_uso, (cantidad - en_uso) AS disponibles 
                      FROM producto 
                      WHERE eliminado != 1 OR eliminado IS NULL
                      ORDER BY nombre ASC";
    $productos_result = $conn->query($productos_sql);

    while ($producto = $productos_result->fetch_assoc()) {
        echo "<option value='{$producto['idproducto']}' 
        data-nombre='{$producto['nombre']}'
        data-descripcion='{$producto['descripcion']}'
        data-nroasignacion='{$producto['nro_asignacion']}'
        data-disponibles='{$producto['disponibles']}'>
    {$producto['idproducto']} - {$producto['nombre']} (Disp: {$producto['disponibles']})
</option>";
    }
    ?>
</select>


        <button type="button" class="btn btn-success mt-2" onclick="agregarMaterial()">Agregar Material</button>

    </div>
</div>
                <div class="mb-3">
                    <label>Asignar Trabajadores:</label>
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Trabajador</th>
                                <th>Horas Trabajadas</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="trabajadoresBody">
                            <!-- Filas dinámicas se agregarán aquí -->
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success" onclick="agregarTrabajador()">Agregar Trabajador</button>
                </div>

                    <div class="mb-3">
                        <label>Comentarios:</label>
                        <textarea name="comentarios" class="form-control" placeholder="Escriba comentarios adicionales Aqui."></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Estado:</label>
                        <select name="estado" class="form-control" required>
                            <option value="Pendiente">Pendiente</option>
                            <option value="En Proceso">En Proceso</option>
                            <option value="Finalizado">Finalizado</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Trabajo</button>
                    </div>


                </form>
            </div>
        </div>
    </div>
</div>










<!-- ✅ Modal para Devolver Material -->
<div class="modal fade" id="modalDevolverMaterial" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="formDevolverMaterial">
        <div class="modal-header bg-secondary text-white">
          <h5 class="modal-title">Devolver Material</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="trabajo_id" id="trabajo_id">
          <div id="productosTrabajoContainer">
            <!-- Aquí se cargarán dinámicamente los productos del trabajo -->
          </div>
        </div>

        <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnRehacerDevolucion">
    🔁 Rehacer Devolución
</button>

<button type="button" class="btn btn-primary" id="btnGuardarDevolucion">Guardar Devolución</button>
          
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal de clave para devolución -->
<div class="modal fade" id="modalClaveGuardarDevolucion" tabindex="-1">
  <div class="modal-dialog">
    <form id="formClaveGuardarDevolucion" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Autenticación requerida</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="clave_contexto" value="guardar_devolucion">
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
    $(document).on("click", ".btnDevolverMaterial", function () {
    const idtrabajo = $(this).data("idtrabajo");

    // 1. Consultar el estado del trabajo
    $.ajax({
        url: "obtener_estado_trabajo.php",
        type: "POST",
        data: { idtrabajo },
        dataType: "json",
        success: function (res) {
            if (res.estado === "Pendiente") {
                Swal.fire({
                    title: "⚠️ El trabajo aún no está completado",
                    text: "Devolver material mientras el trabajo está pendiente puede generar inconsistencias. ¿Deseas continuar de todos modos?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Sí, continuar",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        cargarProductosYMostrarModal(idtrabajo);
                    }
                });
            } else {
                cargarProductosYMostrarModal(idtrabajo);
            }
        },
        error: function () {
            Swal.fire("Error", "No se pudo verificar el estado del trabajo.", "error");
        }
    });
});

function cargarProductosYMostrarModal(idtrabajo) {
    $("#trabajo_id").val(idtrabajo);
    $.ajax({
        url: "cargar_productos_trabajo.php",
        type: "POST",
        data: { trabajo_id: idtrabajo },
        success: function (html) {
            $("#productosTrabajoContainer").html(html);
            $("#modalDevolverMaterial").modal("show");
        },
        error: function () {
            Swal.fire("Error", "No se pudieron cargar los productos.", "error");
        }
    });
}

</script>


<script>
// ✅ Calcula automáticamente "usados" cuando se escribe en "devuelto"
$(document).on("input", ".input-devuelto", function () {
    const cantidadAsignada = parseInt($(this).closest("tr").find(".asignado").data("cantidad"));
    const cantidadDevuelta = parseInt($(this).val()) || 0;
    const id = $(this).data("id");

    const usados = Math.max(cantidadAsignada - cantidadDevuelta, 0);

    // ✅ Muestra en label
    $("#usado_" + id).text(usados);

    // ✅ Guarda en input hidden
    $("#input_usado_" + id).val(usados);
});

</script>



<script>
$(document).ready(function () {
    const esAdmin = "<?= $_SESSION['rol'] === 'Administrador' ? '1' : '0' ?>";

    $(document).on("click", "#btnGuardarDevolucion", function () {
        console.log("CLICK DEVOLUCIÓN");

        const totalProductos = $(".input-devuelto").length;
        let devueltosCount = 0;

        $(".input-devuelto").each(function () {
            const val = parseInt($(this).val());
            if (!isNaN(val) && val > 0) {
                devueltosCount++;
            }
        });

        if (devueltosCount === 0) {
            Swal.fire("Sin devoluciones", "No ingresaste ninguna devolución.", "warning");
            return;
        }

        const continuarEnvio = () => {
            if (esAdmin === "1") {
                console.log("🔐 Admin detectado, enviando devolución");
                enviarFormularioDevolucion(new FormData($("#formDevolverMaterial")[0]));
            } else {
                console.log("🔒 Usuario común, solicitando clave");
                const modal = new bootstrap.Modal(document.getElementById('modalClaveGuardarDevolucion'));
                modal.show();
            }
        };

        if (devueltosCount < totalProductos) {
            Swal.fire({
                title: `Solo estás devolviendo ${devueltosCount} de ${totalProductos} productos`,
                text: "¿Deseas continuar? Los demás productos seguirán disponibles para futuras devoluciones.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, continuar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    continuarEnvio();
                }
            });
        } else {
            continuarEnvio();
        }
    });

    // ✅ Enviar si se valida la clave
    $("#formClaveGuardarDevolucion").submit(function (e) {
        e.preventDefault();
        const clave = $(this).find("input[name='clave_admin']").val();

        $.post("validar_clave_admin.php", { clave_admin: clave }, function (res) {
            if (res.status === "success") {
                const formData = new FormData(document.getElementById("formDevolverMaterial"));
                formData.append("clave_admin", clave);
                formData.append("from_auth_modal", "1");
                enviarFormularioDevolucion(formData);
                const modal = bootstrap.Modal.getInstance(document.getElementById("modalClaveGuardarDevolucion"));
                modal.hide();
            } else {
                Swal.fire("Acceso denegado", res.message, "error");
            }
        }, "json");
    });

    function enviarFormularioDevolucion(formData) {
        console.log("📦 Enviando devolución...");
        $.ajax({
            url: "guardar_devolucion_material.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                Swal.fire("Éxito", "Devolución registrada correctamente.", "success").then(() => {
                    $("#modalDevolverMaterial").modal("hide");
                    $("#productosTrabajoContainer").html("");
                    $("#trabajo_id").val("");
                    document.getElementById("formDevolverMaterial").reset();
                    location.reload();
                });
            },
            error: function () {
                Swal.fire("Error", "No se pudo guardar la devolución.", "error");
            }
        });
    }
});
</script>
<script>
const rolUsuario = "<?= $_SESSION['rol'] ?>";

$(document).on("click", "#btnRehacerDevolucion", function (e) {
    e.preventDefault();
    e.stopPropagation();

    const trabajoId = $("#trabajo_id").val();
    $("#modalDevolverMaterial").modal("hide");

    if (rolUsuario === "Administrador") {
        $.post("rehacer_devolucion.php", { trabajo_id: trabajoId }, function (response) {
            if (response.status === "success") {
                Swal.fire({
                    title: "✅ Devolución Rehecha",
                    text: "Las devoluciones fueron eliminadas correctamente.",
                    icon: "success"
                }).then(() => {
                    $("#productosTrabajoContainer").html("");
                    $("#trabajo_id").val("");
                    location.reload();
                });
            } else {
                Swal.fire("Error", response.message, "error");
            }
        }, "json");

    } else {
        setTimeout(() => {
            Swal.fire({
                title: "Rehacer Devolución",
                html: `
                    <div style="text-align: left;">
                        <label for="adminPassword">Contraseña del administrador:</label>
                        <input id="adminPassword" type="password" placeholder="Escribe la contraseña" class="form-control" style="margin-top: 8px;" autocomplete="off" />
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: "Rehacer",
                cancelButtonText: "Cancelar",
                focusConfirm: false,
                preConfirm: () => {
                    const password = document.getElementById("adminPassword").value;
                    if (!password) {
                        Swal.showValidationMessage("La contraseña es obligatoria");
                    }
                    return password;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("rehacer_devolucion.php", {
                        trabajo_id: trabajoId,
                        password: result.value
                    }, function (response) {
                        if (response.status === "success") {
                            Swal.fire({
                                title: "✅ Devolución Rehecha",
                                text: "Las devoluciones fueron eliminadas correctamente.",
                                icon: "success"
                            }).then(() => {
                                $("#productosTrabajoContainer").html("");
                                $("#trabajo_id").val("");
                                location.reload();
                            });
                        } else {
                            Swal.fire("Error", response.message, "error");
                        }
                    }, "json");
                }
            });
        }, 300);
    }
});
</script>

























































<!-- ✅ Script para activar Select2 -->
<!-- ✅ Script para activar Select2 dentro del modal -->
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Seleccione una Empresa",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#modalAgregarTrabajo') // ✅ Hace que Select2 funcione dentro del modal
        });
    });
</script>

<script>
function agregarOperacion() {
    let tableBody = document.getElementById("operacionesBody");
    let row = document.createElement("tr");

    row.innerHTML = `
        <td><input type="text" name="operacion[]" class="form-control" required></td>
        <td><input type="text" name="pto_trab[]" class="form-control" required></td>
        <td><input type="text" name="desc_pto_trab[]" class="form-control" required></td>
        <td><input type="text" name="desc_oper[]" class="form-control" required></td>
        <td><input type="number" name="n_pers[]" class="form-control" required></td>
        <td><input type="number" name="h_est[]" class="form-control" required></td>
        <td><input type="number" name="hh_tot_prog[]" class="form-control" required></td>
        <td><button type="button" class="btn btn-danger" onclick="eliminarOperacion(this)">Eliminar</button></td>
    `;

    tableBody.appendChild(row);
}

function eliminarOperacion(button) {
    let row = button.parentNode.parentNode;
    row.parentNode.removeChild(row);
}
</script>




<script>
$(document).ready(function() {
    // ✅ Inicializa select2 solo en el select de materiales
    $("#materialSelect").select2({
        width: '100%',
        allowClear: true,
        placeholder: "Seleccione un producto"
    });
});

// ✅ Función para agregar un material a la tabla
function agregarMaterial() {
    let select = $("#materialSelect option:selected");
    let id = select.val();
    let nombre = select.data("nombre");
    let descripcion = select.data("descripcion") || "";
    let nro_asignacion = select.data("nroasignacion"); 
    let disponibles = parseInt(select.data("disponibles")) || 0;

    if (!id || $("#row-" + id).length) {
        alert("⚠️ El material ya está agregado o no es válido.");
        return;
    }

    if (disponibles < 1) {
        alert("❌ No hay stock disponible para este producto.");
        return;
    }

    // ✅ Agregar fila a la tabla con inputs hidden para que los datos se envíen al backend
    let fila = `
        <tr id="row-${id}">
            <td>
                ${nombre}
                <input type="hidden" name="material_id[]" value="${id}">
            </td>
            <td>
                <input type="text" name="descripcion_material[]" class="form-control" value="${descripcion}">
            </td>
            <td>${nro_asignacion}</td>
            <td class="disponibles">${disponibles}</td>
            <td class="cantidad-col">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="modificarCantidad(${id}, -1)">➖</button>
                <span class="cantidad" data-stock="${disponibles}">1</span>
                <input type="hidden" name="cantidad_material[]" value="1">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="modificarCantidad(${id}, 1)">➕</button>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarMaterial(${id})">Eliminar</button>
            </td>
        </tr>
    `;

    $("#materialesBody").append(fila);

    // ✅ Reducir stock disponible
    actualizarStock(id, -1);
}

// ✅ Función para modificar la cantidad
function modificarCantidad(id, cambio) {
    let cantidadSpan = $("#row-" + id + " .cantidad");
    let cantidadInput = $("#row-" + id + " input[name='cantidad_material[]']");
    let disponiblesCell = $("#row-" + id + " .disponibles");
    let cantidad = parseInt(cantidadSpan.text());
    let stockDisponible = parseInt(cantidadSpan.attr("data-stock"));

    let nuevaCantidad = cantidad + cambio;
    if (nuevaCantidad < 1) {
        alert("⚠️ La cantidad no puede ser menor a 1.");
        return;
    }
    if (nuevaCantidad > stockDisponible) {
        alert("❌ Error: No hay suficiente stock disponible.");
        return;
    }

    cantidadSpan.text(nuevaCantidad);
    cantidadInput.val(nuevaCantidad); // ✅ Actualiza el input hidden para el formulario
    disponiblesCell.text(stockDisponible - nuevaCantidad);
}

// ✅ Función para eliminar un material del carrito
function eliminarMaterial(id) {
    let cantidad = parseInt($("#row-" + id + " .cantidad").text());
    $("#row-" + id).remove();
    actualizarStock(id, cantidad);
}

// ✅ Función para actualizar stock disponible en el select
function actualizarStock(id, cambio) {
    let option = $("#materialSelect option[value='" + id + "']");
    let stockDisponible = parseInt(option.data("disponibles"));

    let nuevoStock = stockDisponible + cambio;
    if (nuevoStock < 0) nuevoStock = 0;

    option.data("disponibles", nuevoStock);
    option.text(`${id} - ${option.data("nombre")} (Disp: ${nuevoStock})`);
}

// ✅ Función para actualizar descripción
function actualizarDescripcion(id, input) {
    let nuevaDescripcion = input.value.trim();
    if (nuevaDescripcion === "") {
        alert("⚠️ La descripción no puede estar vacía.");
        input.value = input.getAttribute("data-original"); // Restaurar valor original si está vacío
        return;
    }

    // ✅ Actualizar la descripción en la fila correspondiente
    $(`#row-${id} .descripcion-input`).val(nuevaDescripcion);
}

$(document).ready(function() {
    cargarTrabajadores(); // ✅ Cargar trabajadores cuando se carga la página
});

// ✅ Función para cargar trabajadores desde la BD (usado en cada fila nueva)
function cargarTrabajadores(selectElement) {
    return $.ajax({
        url: "cargar_trabajadores.php",
        type: "GET",
        success: function (response) {
            $(selectElement).html(response);
        },
        error: function () {
            console.log("Error al cargar trabajadores.");
        }
    });
}


function agregarTrabajador() {
    let fila = `<tr>
        <td>
            <select name="trabajador_id[]" class="form-control trabajadorSelect" required>
                <option value="">Seleccione un trabajador</option>
            </select>
        </td>
        <td><input type="number" name="horas_trabajadas[]" class="form-control" min="1" required></td>
        <td><button type="button" class="btn btn-danger" onclick="eliminarFila(this)">Eliminar</button></td>
    </tr>`;

    $("#trabajadoresBody").append(fila);

    let nuevoSelect = $(".trabajadorSelect").last();

    // Cargar trabajadores ANTES de inicializar Select2
    cargarTrabajadores(nuevoSelect).then(() => {
        nuevoSelect.select2({
            placeholder: "Seleccione un trabajador",
            allowClear: true,
            width: '100%'
        });

        nuevoSelect.on("select2:select", function () {
            let selectedOption = $(this).find(':selected');
            let asignado = selectedOption.attr('data-asignado');

            if (asignado == "1") {
                Swal.fire({
                    title: "Este trabajador ya está asignado a un trabajo activo",
                    text: "¿Deseas agregarlo de todos modos?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Sí, agregar",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (!result.isConfirmed) {
                        $(this).val(null).trigger("change");
                    }
                });
            }
        });
    });
}


// ✅ Función para eliminar una fila
function eliminarFila(boton) {
    $(boton).closest("tr").remove();
}
</script>


</body>
</html>