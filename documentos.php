<?php
include 'db.php';
?>

<?php
session_start(); // Iniciar la sesión
if (isset($_SESSION['mensaje'])) {
  echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
          Swal.fire({
              icon: '{$_SESSION['mensaje']['tipo']}',
              title: '{$_SESSION['mensaje']['titulo']}',
              text: '{$_SESSION['mensaje']['texto']}'
          });
      });
  </script>";
  unset($_SESSION['mensaje']);
}
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
    <title>Documentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <!-- SweetAlert2 CSS (opcional pero recomendado) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

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
    /* Reducir tamaño de la tabla Documentos */
    #tablaDocumentos {
        font-size: 12px;
    }

    #tablaDocumentos th,
    #tablaDocumentos td {
        padding: 6px 8px !important;
        white-space: nowrap;
    }

    /* Habilitar scroll horizontal si es necesario */
    #tablaDocumentos {
        overflow-x: auto;
        display: block;
    }
}
</style>




<div class="container mt-4">
    <h2 class="mb-4">Documentos Subidos</h2>

    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalAgregar">➕ Agregar Documento</button>
    <?php if ($_SESSION['rol'] === 'Administrador'): ?>
    <button id="btnLimpiarDocumentos" class="btn btn-danger mb-3 ms-2">
        🧹 Limpiar Documentos
    </button>
<?php endif; ?>

    <div class="mb-3">
        <label for="filtroDocumentos" class="form-label">Filtrar por:</label>
        <select id="filtroDocumentos" class="form-select" style="max-width: 300px;">
            <option value="">📂 Todos</option>
            <option value="recibido">📥 Recibidos</option>
            <option value="enviado">📤 Enviados</option>
        </select>
    </div>

    <table id="tablaDocumentos" class="display table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Fecha</th>
                <th>Enviado por</th>
                <th>Enviado a</th>
                <th>Archivo</th>
                <th>Acción</th>

            </tr>
        </thead>
        <tbody>
            <?php
$id_usuario = $_SESSION['usuario_id'];

$where = "(
    (d.usuario_origen = $id_usuario AND d.eliminado_origen = 0) OR 
    (d.usuario_destino = $id_usuario AND d.eliminado_destino = 0)
)";

$query = "
    SELECT d.*, 
           u1.nombre AS origen, 
           u2.nombre AS destino 
    FROM documento d
    LEFT JOIN usuario u1 ON d.usuario_origen = u1.idusuario
    LEFT JOIN usuario u2 ON d.usuario_destino = u2.idusuario
    WHERE $where
    ORDER BY d.fecha_subida DESC
";




            $documentos = mysqli_query($conn, $query);
            while ($doc = mysqli_fetch_assoc($documentos)) {
                $tipo = ($doc['usuario_destino'] == $id_usuario) ? 'recibido' : 'enviado';
            
                echo "<tr class='$tipo'>
                    <td>{$doc['nombre']}</td>
                    <td>{$doc['tipo']}</td>
                    <td>{$doc['fecha_subida']}</td>
                    <td>{$doc['origen']}</td>
                    <td>{$doc['destino']}</td>
                    <td><a href='documentos/{$doc['archivo']}' target='_blank'>Ver</a></td>
                    <td>
    <button class='btn btn-sm btn-warning' 
            data-bs-toggle='modal' 
            data-bs-target='#modalEditar{$doc['iddocumento']}'>Editar</button>

    <button class='btn btn-sm btn-danger btnEliminarDocumento' 
        data-id='{$doc['iddocumento']}'>
    Eliminar
</button>


    <a href='enviar_documento.php?id={$doc['iddocumento']}' 
       class='btn btn-sm btn-info'>Enviar por correo</a>
</td>

                </tr>";
            
                // ✅ Aquí se agrega el modal de edición justo después del <tr>
                echo "
                <div class='modal fade' id='modalEditar{$doc['iddocumento']}' tabindex='-1' aria-labelledby='modalEditarLabel{$doc['iddocumento']}' aria-hidden='true'>
                    <div class='modal-dialog'>
                        <div class='modal-content'>
                            <form action='editar_documento.php' method='POST' enctype='multipart/form-data'>
                                <input type='hidden' name='id' value='{$doc['iddocumento']}'>
                                <div class='modal-header'>
                                    <h5 class='modal-title' id='modalEditarLabel{$doc['iddocumento']}'>Editar Documento</h5>
                                    <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                </div>
                                <div class='modal-body'>
                                    <div class='mb-3'>
                                        <label class='form-label'>Nombre del documento:</label>
                                        <input type='text' name='nombre' class='form-control' value='{$doc['nombre']}' required>
                                    </div>
            
                                    <div class='mb-3'>
                                        <label class='form-label'>Cambiar archivo (opcional):</label>
                                        <input type='file' name='archivo' class='form-control'>
                                    </div>
            
                                    <div class='mb-3'>
                                        <label class='form-label'>Enviar a:</label>
                                        <select name='usuario_destino' class='form-select' required>";
            
                                            $usuarios = mysqli_query($conn, "SELECT * FROM usuario");
                                            while ($user = mysqli_fetch_assoc($usuarios)) {
                                                $selected = ($user['idusuario'] == $doc['usuario_destino']) ? 'selected' : '';
                                                echo "<option value='{$user['idusuario']}' $selected>{$user['nombre']} ({$user['email']})</option>";
                                            }
            
                                echo "</select>
                                    </div>
                                </div>
                                <div class='modal-footer'>
                                    <button type='submit' class='btn btn-primary'>Guardar Cambios</button>
                                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>";
            }
            ?>
            
        </tbody>
        
    </table>
    
</div>



<!-- jQuery (primero, antes que todo lo demás) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Activar DataTable + Filtro -->
<script>
    $(document).ready(function () {
        const tabla = $('#tablaDocumentos').DataTable({
            pageLength: 100, // 👈 Esto establece 100 filas por página
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            }
        });

        $('#filtroDocumentos').on('change', function () {
            const filtro = $(this).val();
            if (filtro === '') {
                tabla.rows().nodes().to$().show();
            } else {
                tabla.rows().nodes().to$().each(function () {
                    $(this).toggle($(this).hasClass(filtro));
                });
            }
        });
    });
</script>



<!-- Modal para subir documento -->
<div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <form id="formSubirDocumento" action="subir_documento.php" method="POST" enctype="multipart/form-data">
        <div class="modal-header">
                    <h5 class="modal-title" id="modalAgregarLabel">Subir y Enviar Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    
                    <!-- Campo nombre personalizado -->
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del documento:</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Contrato Cliente X" required>
                    </div>

                    <!-- Archivo -->
                    <div class="mb-3">
                        <label for="archivo" class="form-label">Selecciona archivo:</label>
                        <input type="file" name="archivo" class="form-control" required>
                    </div>

                    <!-- Destinatario -->
                    <div class="mb-3">
                        <label for="usuario_destino" class="form-label">Enviar a:</label>
                        <select name="usuario_destino" class="form-select" required>
                            <option value="" disabled selected>Selecciona un usuario</option>
                            <?php
                            $usuarios = mysqli_query($conn, "SELECT * FROM usuario");
                            while ($row = mysqli_fetch_assoc($usuarios)) {
                                echo "<option value='{$row['idusuario']}'>{$row['nombre']} ({$row['email']})</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Subir y Enviar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById("formSubirDocumento").addEventListener("submit", function (e) {
        e.preventDefault(); // Detiene el envío

        const select = document.querySelector("select[name='usuario_destino']");
        const selectedOption = select.options[select.selectedIndex];

        if (!selectedOption || selectedOption.disabled) {
            Swal.fire("Selecciona un usuario válido.");
            return;
        }

        const nombreUsuario = selectedOption.textContent;

        Swal.fire({
            title: "¿Estás seguro?",
            text: `Has seleccionado al usuario ${nombreUsuario}. ¿Deseas enviar el documento a esta persona?`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, enviar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                e.target.submit(); // Envía el formulario
            }
        });
    });
</script>

<script>
$(document).on("click", ".btnEliminarDocumento", function () {
    const id = $(this).data("id");

    Swal.fire({
        title: '¿Estás seguro?',
        text: "El documento será ocultado solo para ti. El otro usuario seguirá viéndolo.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, ocultar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("eliminar_documento.php", { iddocumento: id }, function (res) {
                if (res.status === "success") {
                    Swal.fire("✅ Ocultado", res.message, "success").then(() => {
                        location.reload(); // O bien, remover la fila sin recargar
                    });
                } else {
                    Swal.fire("Error", res.message, "error");
                }
            }, "json").fail(() => {
                Swal.fire("Error", "No se pudo procesar la solicitud.", "error");
            });
        }
    });
});
</script>

<script>
$(document).on("click", "#btnLimpiarDocumentos", function () {
    Swal.fire({
        title: "¿Deseas limpiar documentos?",
        text: "Se eliminarán permanentemente los archivos que fueron eliminados tanto por el emisor como el receptor.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.get("limpiar_documentos.php", function (data) {
                Swal.fire("Listo", "Documentos limpiados correctamente.", "success").then(() => {
                    location.reload();
                });
            }).fail(() => {
                Swal.fire("Error", "No se pudo completar la limpieza.", "error");
            });
        }
    });
});
</script>

</body>
</html> 