<?php
include 'db.php';

// Establecer zona horaria de Chile
date_default_timezone_set('America/Santiago');

// Consulta para obtener todos los vehículos
// Consulta para obtener todos los vehículos
$sql = "SELECT idvehiculo, nombre, patente, img, permiso_fin, revision_fin, vencimiento_cambio_aceite, ultima_mantencion  
        FROM vehiculo 
        ORDER BY fecha_registro DESC";

$result = $conn->query($sql);


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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario Vehiculos</title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- ✅ Cargar jQuery antes que todo lo demás -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables (Depende de jQuery, por eso va después de jQuery) -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Select2 (También depende de jQuery, por eso va después de jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ✅ Asegurar que jQuery está cargado antes de usarlo -->
    <script>
        if (typeof jQuery === "undefined") {
            console.error("❌ jQuery no se cargó correctamente. Revisa tu conexión o CDN.");
        } else {
            console.log("✅ jQuery está cargado correctamente.");
        }
    </script>
    <!-- Bootstrap Datepicker CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<!-- jQuery UI CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">

<!-- jQuery y jQuery UI JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>


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


<button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAgregarVehiculo">
    Añadir Vehículo
</button>


<div class="container mt-4">
    <h2 class="text-center">Catálogo de Vehículos</h2>

    <div class="row">
        <?php
        // ✅ Verificar si la función ya existe antes de declararla
        if (!function_exists('calcularDiasRestantes')) {
            function calcularDiasRestantes($fecha, $hoy) {
                if (!$fecha) return "No registrado";
                $dias = (int) $hoy->diff($fecha)->format('%r%a'); // %r maneja negativos
                
                return ($dias <= 0) ? "<span class='text-danger fw-bold'>VENCIDO</span>" : "$dias días restantes";
            }
        }

        if (!function_exists('colorTexto')) {
            function colorTexto($dias) {
                return (strpos($dias, 'VENCIDO') !== false || (int) filter_var($dias, FILTER_SANITIZE_NUMBER_INT) <= 15)
                    ? "text-danger fw-bold"
                    : "fw-bold";
            }
        }

        if ($result->num_rows > 0) {
            // Obtener fecha actual con hora en Chile
            $hoy = new DateTime('now', new DateTimeZone('America/Santiago'));

            while ($row = $result->fetch_assoc()) {
                $vehiculo_id = htmlspecialchars($row['idvehiculo'], ENT_QUOTES, 'UTF-8');
                $nombre = htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8');
                $patente = htmlspecialchars($row['patente'], ENT_QUOTES, 'UTF-8');

                $img = !empty($row['img']) ? "img/" . htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8') : "img/no-image.png";

                // Convertir fechas desde la BD (Si están vacías, mostrar "No registrado")
                $permiso_fin = !empty($row['permiso_fin']) ? new DateTime($row['permiso_fin']) : null;
                $revision_fin = !empty($row['revision_fin']) ? new DateTime($row['revision_fin']) : null;
                $vencimiento_cambio_aceite = !empty($row['vencimiento_cambio_aceite']) ? new DateTime($row['vencimiento_cambio_aceite']) : null;

                // Calcular días restantes
                $dias_permiso = calcularDiasRestantes($permiso_fin, $hoy);
                $dias_revision = calcularDiasRestantes($revision_fin, $hoy);
                $dias_aceite = calcularDiasRestantes($vencimiento_cambio_aceite, $hoy);

                // 🔧 Calcular días desde última mantención
                $ultima_mantencion = !empty($row['ultima_mantencion']) ? new DateTime($row['ultima_mantencion']) : null;
                $dias_mantencion = $ultima_mantencion ? $ultima_mantencion->diff($hoy)->days : null;

                $texto_mantencion = $ultima_mantencion
                    ? "<strong>Última Mantención:</strong> " . $ultima_mantencion->format("d-m-Y") . " (<span class='fw-bold text-primary'>$dias_mantencion días atrás</span>)"
                    : "<strong>Última Mantención:</strong> No registrada";


                    echo "<div class='col-lg-3 col-md-4 col-sm-6 mb-4'>
                    <div class='card shadow-sm'>
                        <img src='$img' class='card-img-top img-fluid' style='height: 200px; object-fit: cover; cursor: pointer;'>
                        
                        <div class='card-body'>
                            <h5 class='card-title text-center'>$nombre</h5>
                            <p class='text-center text-muted mb-2'><strong>Patente:</strong> $patente</p>
                
                            <hr>
                            <p class='card-text " . colorTexto($dias_permiso) . "'>
                                <strong>Permiso Circulación:</strong> " . ($permiso_fin ? $permiso_fin->format("d-m-Y") : "No registrado") . " ($dias_permiso)
                            </p>
                            <p class='card-text " . colorTexto($dias_revision) . "'>
                                <strong>Revisión Técnica:</strong> " . ($revision_fin ? $revision_fin->format("d-m-Y") : "No registrado") . " ($dias_revision)
                            </p>
                            <p class='card-text " . colorTexto($dias_aceite) . "'>
                                <strong>Cambio de Aceite:</strong> " . ($vencimiento_cambio_aceite ? $vencimiento_cambio_aceite->format("d-m-Y") : "No registrado") . " ($dias_aceite)
                            </p>
                            <p class='card-text'>$texto_mantencion</p>
                
                            <div class='text-center mt-3'>
                                <button class='btn btn-warning btn-sm btnEditar' 
        data-idvehiculo='$vehiculo_id'>
    <i class='fas fa-edit'></i> Editar
</button>


                
                                <button class='btn btn-info btn-sm btnVerDetalles' 
                                    data-idvehiculo='$vehiculo_id' 
                                    data-bs-toggle='modal' 
                                    data-bs-target='#modalVerVehiculo'>
                                    <i class='fas fa-eye'></i> Ver Detalles
                                </button>
                            </div>
                
                        </div>
                    </div>
                </div>";
                


            }
        } else {
            echo "<p class='text-center'>No hay vehículos registrados.</p>";
        }
        ?>
    </div>
</div>


<div class="modal fade" id="modalClaveEditarVehiculo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formClaveEditarVehiculo" class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">Autenticación requerida</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="vehiculo_id_auth">
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

$(document).ready(function () {
    $(document).on("click", ".btnEditar", function () {
        const idVehiculo = $(this).data("idvehiculo");

        if (esAdmin === "1") {
            obtenerYMostrarVehiculo(idVehiculo);
        } else {
            $("#vehiculo_id_auth").val(idVehiculo);
            const modal = new bootstrap.Modal(document.getElementById("modalClaveEditarVehiculo"));
            modal.show();
        }
    });

    // Validar contraseña de admin si no es administrador
    $("#formClaveEditarVehiculo").submit(function (e) {
        e.preventDefault();
        const clave = $(this).find("input[name='clave_admin']").val();
        const idVehiculo = $("#vehiculo_id_auth").val();

        $.post("validar_clave_admin.php", { clave_admin: clave }, function (res) {
            if (res.status === "success") {
                $("#modalClaveEditarVehiculo").modal("hide");
                obtenerYMostrarVehiculo(idVehiculo);
            } else {
                Swal.fire("Acceso denegado", res.message, "error");
            }
        }, "json");
    });

    // Tu función actual separada para reutilizarla
    function obtenerYMostrarVehiculo(vehiculo_id) {
        $.ajax({
            url: "obtener_vehiculo.php",
            type: "POST",
            data: { idvehiculo: vehiculo_id },
            dataType: "json",
            success: function (data) {
                if (data.status === "success") {
                    $("#editar_id").val(data.vehiculo.idvehiculo);
                    $("#editar_nombre").val(data.vehiculo.nombre);
                    $("#editar_patente").val(data.vehiculo.patente);
                    $("#editar_marca").val(data.vehiculo.marca);
                    $("#editar_modelo").val(data.vehiculo.modelo);
                    $("#editar_anio").val(data.vehiculo.anio);
                    $("#editar_precio").val(data.vehiculo.precio);
                    $("#editar_estado").val(data.vehiculo.estado).change();
                    $("#editar_descripcion").val(data.vehiculo.descripcion);

                    $("#editar_permiso_inicio").val(data.vehiculo.permiso_inicio || "");
                    $("#editar_permiso_fin").val(data.vehiculo.permiso_fin || "");
                    $("#editar_revision_inicio").val(data.vehiculo.revision_inicio || "");
                    $("#editar_revision_fin").val(data.vehiculo.revision_fin || "");
                    $("#editar_fecha_cambio_aceite").val(data.vehiculo.fecha_cambio_aceite || "");
                    $("#editar_vencimiento_cambio_aceite").val(data.vehiculo.vencimiento_cambio_aceite || "");

                    if (data.vehiculo.img && data.vehiculo.img !== "img/no-image.png") {
                        $("#editar_preview").attr("src", data.vehiculo.img).show();
                        $("#editar_img_nombre").text(data.vehiculo.img.split('/').pop());
                        $("#imagen_actual").val(data.vehiculo.img.split('/').pop());
                    } else {
                        $("#editar_preview").attr("src", "img/no-image.png").show();
                        $("#editar_img_nombre").text("No hay archivo seleccionado");
                        $("#imagen_actual").val("");
                    }

                    if (data.vehiculo.permiso_circulacion) {
                        $("#permiso_circulacion_actual").html(`<a href="${data.vehiculo.permiso_circulacion}" target="_blank">📄 Ver Documento</a>`);
                    } else {
                        $("#permiso_circulacion_actual").html("No disponible");
                    }

                    if (data.vehiculo.revision_tecnica) {
                        $("#revision_tecnica_actual").html(`<a href="${data.vehiculo.revision_tecnica}" target="_blank">📄 Ver Documento</a>`);
                    } else {
                        $("#revision_tecnica_actual").html("No disponible");
                    }

                    $("#modalEditarVehiculo").modal("show");
                } else {
                    Swal.fire("Error", "No se encontraron los datos del vehículo.", "error");
                }
            },
            error: function () {
                Swal.fire("Error", "No se pudo obtener los datos del vehículo.", "error");
            }
        });
    }

    // Imagen y archivo preview como lo tienes
    $("#editar_img").change(function () {
        let archivo = this.files[0];
        if (archivo) {
            $("#editar_img_nombre").text(archivo.name);
            let lector = new FileReader();
            lector.onload = function (e) {
                $("#editar_preview").attr("src", e.target.result).show();
            };
            lector.readAsDataURL(archivo);
        } else {
            $("#editar_img_nombre").text("No hay archivo seleccionado");
            $("#editar_preview").attr("src", "img/no-image.png").show();
        }
    });

    $("#editar_permiso_circulacion, #editar_revision_tecnica").change(function () {
        let archivo = $(this).prop("files")[0];
        if (archivo) {
            $(this).siblings("label").text("📂 Archivo seleccionado: " + archivo.name);
        }
    });
});
</script>














<!-- Modal para Agregar Vehículo -->
<div class="modal fade" id="modalAgregarVehiculo" tabindex="-1">
  <div class="modal-dialog modal-lg"> 
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Añadir Nuevo Vehículo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formAgregarVehiculo" enctype="multipart/form-data">
          
          <!-- ✅ Imagen -->
          <div class="mb-3">
            <label class="form-label">Imagen del Vehículo:</label>
            <input type="file" id="img" name="img" class="form-control" accept="image/*">
            <img id="preview" src="img/no-image.png" class="mt-2" style="width: 100px; height: 100px; object-fit: cover; display: none;">
          </div>

          <!-- ✅ Datos Principales -->
          <div class="row">
            <div class="col-md-6">
              <label>Nombre:</label>
              <input type="text" id="nombre" name="nombre" class="form-control" required>
            </div>
            <div class="col-md-6">
  <label>Patente:</label>
  <input type="text" id="patente" name="patente" class="form-control" required>
</div>
        
            <div class="col-md-6">
              <label>Marca:</label>
              <input type="text" id="marca" name="marca" class="form-control" required>
            </div>
          </div>

          <div class="row mt-2">
            <div class="col-md-6">
              <label>Modelo:</label>
              <input type="text" id="modelo" name="modelo" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label>Año:</label>
              <input type="number" id="anio" name="anio" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label>Precio:</label>
              <input type="number" id="precio" name="precio" class="form-control" required>
            </div>
          </div>

          <!-- ✅ Permiso de Circulación -->
          <h5 class="mt-3">Permiso de Circulación</h5>
          <div class="row">
            <div class="col-md-6">
              <label>Inicio:</label>
              <input type="date" id="permiso_inicio" name="permiso_inicio" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Vencimiento:</label>
              <input type="date" id="permiso_fin" name="permiso_fin" class="form-control" required>
            </div>
          </div>
          <div class="mb-3">
            <label>Documento Permiso de Circulación:</label>
            <input type="file" id="permiso_circulacion" name="permiso_circulacion" class="form-control" accept="application/pdf, image/*">
          </div>

          <!-- ✅ Revisión Técnica -->
          <h5 class="mt-3">Revisión Técnica</h5>
          <div class="row">
            <div class="col-md-6">
              <label>Inicio:</label>
              <input type="date" id="revision_inicio" name="revision_inicio" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Vencimiento:</label>
              <input type="date" id="revision_fin" name="revision_fin" class="form-control" required>
            </div>
          </div>
          <div class="mb-3">
            <label>Documento Revisión Técnica:</label>
            <input type="file" id="revision_tecnica" name="revision_tecnica" class="form-control" accept="application/pdf, image/*">
          </div>

          <!-- ✅ Cambio de Aceite -->
          <h5 class="mt-3">Cambio de Aceite</h5>
          <div class="row">
            <div class="col-md-6">
              <label>Fecha de Cambio:</label>
              <input type="date" id="fecha_cambio_aceite" name="fecha_cambio_aceite" class="form-control">
            </div>
            <div class="col-md-6">
              <label>Vencimiento:</label>
              <input type="date" id="vencimiento_cambio_aceite" name="vencimiento_cambio_aceite" class="form-control">
            </div>
          </div>

          <!-- ✅ Estado y Descripción -->
          <h5 class="mt-3">Estado del Vehículo</h5>
          <div class="row">
            <div class="col-md-6">
              <label>Estado:</label>
              <select id="estado" name="estado" class="form-control">
                <option value="Activo">Activo</option>
                <option value="En mantenimiento">En mantenimiento</option>
                <option value="Dado de baja">Dado de baja</option>
              </select>
            </div>
            <div class="col-md-6">
              <label>Descripción:</label>
              <textarea id="descripcion" name="descripcion" class="form-control"></textarea>
            </div>
          </div>

          <!-- ✅ Botón para guardar -->
          <div class="text-center mt-4">
            <button type="submit" class="btn btn-success">Guardar Vehículo</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>






<!-- Modal para Editar Vehículo -->
<div class="modal fade" id="modalEditarVehiculo" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar Vehículo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formEditarVehiculo" enctype="multipart/form-data">
          <input type="hidden" id="editar_id" name="idvehiculo">

          <div class="mb-3 text-center">
    <!-- ✅ Imagen Previsualización -->
    <img id="editar_preview" src="img/no-image.png" class="rounded mb-2" 
         style="width: 150px; height: 150px; object-fit: cover; display: none;">
    
    <!-- ✅ Nombre del archivo actual -->
    <input type="hidden" id="imagen_actual" name="imagen_actual">
    <p id="editar_img_nombre" class="text-muted">No hay archivo seleccionado</p>

    <!-- ✅ Input para subir nueva imagen -->
    <input type="file" id="editar_img" name="editar_img" class="form-control mt-2" accept="image/*">
</div>


          <!-- Datos Principales -->
          <div class="row">
            <div class="col-md-6">
              <label>Nombre:</label>
              <input type="text" id="editar_nombre" name="nombre" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Patente:</label>
              <input type="text" id="editar_patente" name="patente" class="form-control" required>
            </div>            
            <div class="col-md-6">
              <label>Marca:</label>
              <input type="text" id="editar_marca" name="marca" class="form-control" required>
            </div>
          </div>

          <div class="row mt-2">
            <div class="col-md-6">
              <label>Modelo:</label>
              <input type="text" id="editar_modelo" name="modelo" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label>Año:</label>
              <input type="number" id="editar_anio" name="anio" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label>Precio:</label>
              <input type="number" id="editar_precio" name="precio" class="form-control" required>
            </div>
          </div>

          <!-- Permiso de Circulación -->
          <h5 class="mt-3">Permiso de Circulación</h5>
          <div class="row">
            <div class="col-md-6">
              <label>Inicio:</label>
              <input type="date" id="editar_permiso_inicio" name="permiso_inicio" class="form-control">
            </div>
            <div class="col-md-6">
              <label>Vencimiento:</label>
              <input type="date" id="editar_permiso_fin" name="permiso_fin" class="form-control">
            </div>
          </div>

          <!-- Documento Permiso de Circulación -->
<div class="mb-3">
    <label class="form-label">Documento Permiso de Circulación:</label>
    <div id="permiso_circulacion_actual">No disponible</div>
    <input type="file" id="editar_permiso_circulacion" name="editar_permiso_circulacion" class="form-control mt-2" accept=".pdf, .jpg, .jpeg, .png">
</div>

          <!-- Revisión Técnica -->
          <h5 class="mt-3">Revisión Técnica</h5>
          <div class="row">
            <div class="col-md-6">
              <label>Inicio:</label>
              <input type="date" id="editar_revision_inicio" name="revision_inicio" class="form-control">
            </div>
            <div class="col-md-6">
              <label>Vencimiento:</label>
              <input type="date" id="editar_revision_fin" name="revision_fin" class="form-control">
            </div>
          </div>

          <!-- Documento Revisión Técnica -->
<div class="mb-3">
    <label class="form-label">Documento Revisión Técnica:</label>
    <div id="revision_tecnica_actual">No disponible</div>
    <input type="file" id="editar_revision_tecnica" name="editar_revision_tecnica" class="form-control mt-2" accept=".pdf, .jpg, .jpeg, .png">
</div>

          <!-- Cambio de Aceite -->
          <h5 class="mt-3">Cambio de Aceite</h5>
          <div class="row">
            <div class="col-md-6">
              <label>Fecha de Cambio:</label>
              <input type="date" id="editar_fecha_cambio_aceite" name="fecha_cambio_aceite" class="form-control">
            </div>
            <div class="col-md-6">
              <label>Vencimiento:</label>
              <input type="date" id="editar_vencimiento_cambio_aceite" name="vencimiento_cambio_aceite" class="form-control">
            </div>
          </div>

          <!-- Estado y Descripción -->
          <h5 class="mt-3">Estado del Vehículo</h5>
          <div class="row">
            <div class="col-md-6">
              <label>Estado:</label>
              <select id="editar_estado" name="estado" class="form-control">
                <option value="Activo">Activo</option>
                <option value="En mantenimiento">En mantenimiento</option>
                <option value="Dado de baja">Dado de baja</option>
              </select>
            </div>
            <div class="col-md-6">
              <label>Descripción:</label>
              <textarea id="editar_descripcion" name="descripcion" class="form-control"></textarea>
            </div>
          </div>

          <!-- Botones -->
          <div class="text-center mt-4">
            <button type="button" class="btn btn-danger ms-2" id="btnEliminarVehiculo">
            <i class="fas fa-trash-alt"></i> Eliminar
            </button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>



<!-- Modal para Ver Detalles del Vehículo -->
<div class="modal fade" id="modalVerVehiculo" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <div class="modal-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="modal-title"><i class="fas fa-car"></i> Detalles del Vehículo</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <!-- ✅ Campo oculto con ID del vehículo -->
        <input type="hidden" id="detalle_idvehiculo">

        <div id="detallesVehiculo">
          <!-- Aquí se mostrarán los detalles con JavaScript -->
        </div>

        <!-- Input de Fecha de Mantención (oculto por defecto) -->
        <div id="formMantencion" class="mt-3" style="display: none;">
          <hr>
          <label for="ultima_mantencion" class="form-label"><strong>Fecha de última mantención:</strong></label>
          <div class="input-group">
            <input type="date" id="ultima_mantencion" class="form-control">
            <button class="btn btn-success" id="guardar_mantencion" type="button">
              Guardar
            </button>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-light">
        <button type="button" id="btnMantencion" class="btn btn-primary me-2">
          <i class="fas fa-tools"></i> Mantención
        </button>

        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>







<script>
  $(document).ready(function () {
    $(".btnVerDetalles").click(function () {
        let id = $(this).data("idvehiculo");
        

        $.ajax({
            url: "obtener_vehiculo.php",
            method: "POST",
            data: { idvehiculo: id },
            dataType: "json",
            success: function (data) {
                if (data.status === "success") {
                    const v = data.vehiculo;
                    $("#detalle_idvehiculo").val(v.idvehiculo);
                    $("#ultima_mantencion").val(v.ultima_mantencion ?? "");

                    let html = `
  <div class="row">
    <div class="col-md-4 text-center">
      <img src="${v.img}" alt="Imagen" class="img-fluid rounded shadow" style="max-height: 200px;">
    </div>
    <div class="col-md-8">
      <h5 class="text-primary">${v.nombre}</h5>
            <p><strong>Patente:</strong> ${v.patente ?? "No registrada"}</p>

      <p><strong>Marca:</strong> ${v.marca}</p>
      <p><strong>Modelo:</strong> ${v.modelo}</p>
      <p><strong>Año:</strong> ${v.anio}</p>
      <p><strong>Precio:</strong> $${parseFloat(v.precio).toLocaleString()}</p>
      <p><strong>Estado:</strong> ${v.estado}</p>
    </div>
  </div>
  <hr>
  <div class="row">
    <div class="col-md-6">
      <p><strong>Permiso de Circulación:</strong><br>${v.permiso_inicio ?? "No registrado"} al ${v.permiso_fin ?? "No registrado"}</p>
      <p><strong>Revisión Técnica:</strong><br>${v.revision_inicio ?? "No registrado"} al ${v.revision_fin ?? "No registrado"}</p>
      <p><strong>Último Cambio de Aceite:</strong><br>${v.fecha_cambio_aceite ?? "No registrado"}</p>
      <p><strong>Vencimiento Cambio de Aceite:</strong><br>${v.vencimiento_cambio_aceite ?? "No registrado"}</p>
    </div>
    <div class="col-md-6">
      <p><strong>Descripción:</strong><br>${v.descripcion || "Sin descripción"}</p>
      
    </div>
  </div>
`;

                    if (v.permiso_circulacion) {
                        html += `<p><strong>Documento Permiso:</strong> <a href="${v.permiso_circulacion}" target="_blank">📄 Ver Documento</a></p>`;
                    }

                    if (v.revision_tecnica) {
                        html += `<p><strong>Documento Revisión Técnica:</strong> <a href="${v.revision_tecnica}" target="_blank">📄 Ver Documento</a></p>`;
                    }

                    $("#detallesVehiculo").html(html);
                } else {
                    $("#detallesVehiculo").html("<p class='text-danger'>No se pudo cargar el vehículo.</p>");
                }
            },
            error: function () {
                $("#detallesVehiculo").html("<p class='text-danger'>Error al obtener los datos.</p>");
            }
        });
    });
});

</script>

<script>
  // Mostrar u ocultar el formulario de mantención
$("#btnMantencion").click(function () {
  $("#formMantencion").slideToggle();
});

// Guardar mantención
$("#guardar_mantencion").click(function () {
  const fecha = $("#ultima_mantencion").val();
  const id = $("#detalle_idvehiculo").val();

  if (!fecha || !id) {
    Swal.fire("Error", "Debe seleccionar una fecha válida.", "warning");
    return;
  }

  $.ajax({
    url: "guardar_mantencion.php",
    type: "POST",
    data: { idvehiculo: id, ultima_mantencion: fecha },
    success: function (resp) {
      try {
        const data = JSON.parse(resp);
        if (data.status === "success") {
          Swal.fire("Guardado", "Fecha de mantención actualizada correctamente.", "success")
            .then(() => {
              window.location.href = "vehiculos.php"; // 🔄 Redirige a la página
            });
        } else {
          Swal.fire("Error", data.message || "No se pudo guardar.", "error");
        }
      } catch (e) {
        console.error("Error JSON:", e);
        Swal.fire("Error", "Error inesperado.", "error");
      }
    },
    error: function () {
      Swal.fire("Error", "No se pudo guardar la mantención.", "error");
    }
  });
});

</script>







<script>
$(document).ready(function () {

    // Previsualización de la imagen seleccionada
    $("#img").change(function (event) {
        let file = event.target.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function (e) {
                $("#preview").attr("src", e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });

    // 🟢 Manejo del formulario de agregar vehículo
    $("#formAgregarVehiculo").submit(function (e) {
        e.preventDefault();

        let formData = new FormData(this); // Captura los datos del formulario, incluyendo archivos

        $.ajax({
            url: "agregar_vehiculo.php", // 📌 Archivo PHP para procesar la solicitud
            type: "POST",
            data: formData,
            processData: false, // No procesar datos
            contentType: false, // No establecer tipo de contenido
            dataType: "json", // Esperamos una respuesta en formato JSON
            success: function (response) {
                if (response.status === "success") {
                    Swal.fire({
                        title: "¡Vehículo Agregado!",
                        text: "El vehículo se ha registrado correctamente.",
                        icon: "success"
                    }).then(() => {
                        location.reload(); // Recargar la página para ver el nuevo vehículo
                    });
                } else {
                    Swal.fire("Error", response.message, "error");
                }
            },
            error: function () {
                Swal.fire("Error", "No se pudo conectar con el servidor.", "error");
            }
        });
    });

});
</script>












<script>
$(document).ready(function () {
    // Evento para actualizar la vista previa y el nombre del archivo
    $("#editar_img").on("change", function (event) {
        let file = event.target.files[0];

        if (file) {
            $("#nombre_archivo").text("Archivo seleccionado: " + file.name);
        } else {
            $("#nombre_archivo").text("No hay archivos seleccionados"); // Si se borra el archivo
        }
    });
});
</script>

<script>
$(document).ready(function () {
    $("#formEditarVehiculo").submit(function (e) {
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: "editar_vehiculo.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                console.log("Respuesta cruda del servidor:", response); // 👀 Verifica el contenido

                try {
                  let res = response;
                  if (res.status === "success") {
                        Swal.fire({
                            title: "Actualizado",
                            text: "El vehículo ha sido actualizado correctamente.",
                            icon: "success"
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire("Error", res.message, "error");
                    }
                } catch (err) {
                    console.error("Error al parsear JSON:", err);
                    console.log("Respuesta completa:", response);
                    Swal.fire("Error", "La respuesta del servidor no es válida.", "error");
                }
            },
            error: function (xhr, status, error) {
                console.error("Error en la petición AJAX:", error);
                Swal.fire("Error", "No se pudo actualizar el vehículo. [" + error + "]", "error");
            }
        });
    });
});
</script>







<script>
$(document).ready(function () {
  $("#btnEliminarVehiculo").click(function () {
    const id = $("#editar_id").val();

    Swal.fire({
      title: "¿Estás seguro?",
      text: "Esta acción eliminará el vehículo permanentemente.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar"
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: "eliminar_vehiculo.php",
          type: "POST",
          data: { idvehiculo: id },
          dataType: "json",
          success: function (response) {
            if (response.status === "success") {
              Swal.fire("Eliminado", "El vehículo ha sido eliminado.", "success")
                .then(() => {
                  location.reload();
                });
            } else {
              Swal.fire("Error", response.message || "No se pudo eliminar.", "error");
            }
          },
          error: function () {
            Swal.fire("Error", "No se pudo conectar con el servidor.", "error");
          }
        });
      }
    });
  });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>







</body>
</html>