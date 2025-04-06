<?php
include 'db.php';
session_start();

if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: trabajos.php");
    exit();
}





$trabajo_id = intval($_GET['id']); // Asegura que el ID sea un número


$trabajo_id = intval($_GET['id']);
// 📌 **Obtener datos del trabajo**
$sql_trabajo = "SELECT t.*, c.empresa 
                FROM trabajo t 
                LEFT JOIN cliente c ON t.cliente_id = c.idcliente 
                WHERE t.idtrabajo = ?";
$stmt = $conn->prepare($sql_trabajo);
if (!$stmt) {
    die("Error en la preparación de la consulta de trabajo: " . $conn->error);
}
$stmt->bind_param("i", $trabajo_id);
$stmt->execute();
$result = $stmt->get_result();
$trabajo = $result->fetch_assoc();
if (!$trabajo) {
    die("Error: No se encontró el trabajo con ID $trabajo_id.");
}

// 📌 **Obtener operaciones asignadas**
$sql_operaciones = "SELECT * FROM operaciones WHERE trabajo_id = ?";
$stmt_operaciones = $conn->prepare($sql_operaciones);
if (!$stmt_operaciones) {
    die("Error en la preparación de la consulta de operaciones: " . $conn->error);
}
$stmt_operaciones->bind_param("i", $trabajo_id);
$stmt_operaciones->execute();
$operaciones = $stmt_operaciones->get_result();



$sql = "SELECT 
    tp.producto_id,
    tp.cantidad AS cantidad_asignada,
    tp.descripcion AS descripcion_asignacion,
    p.nombre,
    p.nro_asignacion,
    p.cantidad AS cantidad_total,
    p.en_uso,
    (p.cantidad - p.en_uso) AS disponibles
FROM trabajo_producto tp
INNER JOIN producto p ON tp.producto_id = p.idproducto
WHERE tp.trabajo_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $trabajo_id);
$stmt->execute();
$result = $stmt->get_result();




// 📌 **Obtener trabajadores asignados**
$sql_trabajadores = "SELECT tt.*, e.nombre AS trabajador 
                     FROM trabajo_trabajadores tt
                     LEFT JOIN empleado e ON tt.id_trabajador = e.idempleado
                     WHERE tt.id_trabajo = ?";

$stmt_trabajadores = $conn->prepare($sql_trabajadores);
if (!$stmt_trabajadores) {
    die("Error en la preparación de la consulta de trabajadores: " . $conn->error);
}
$stmt_trabajadores->bind_param("i", $trabajo_id);
$stmt_trabajadores->execute();
$trabajadores = $stmt_trabajadores->get_result();

?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Trabajo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container mt-4">
    <h2 class="mb-4 text-center">Editar Orden de Trabajo</h2>

    <form action="guardar_edicion_trabajo.php" method="POST">
        <input type="hidden" name="trabajo_id" value="<?php echo $trabajo_id; ?>">

        <div class="container mt-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    Información General del Trabajo
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <!-- Fila 1 -->
                            <tr>
                                <td><strong>Tipo de Trabajo:</strong></td>
                                <td>
                                    <select name="tipo" class="form-control" required>
                                        <option value="OT" <?php echo ($trabajo['tipo'] == 'OT') ? 'selected' : ''; ?>>Orden de Trabajo (OT)</option>
                                        <option value="OM" <?php echo ($trabajo['tipo'] == 'OM') ? 'selected' : ''; ?>>Orden de Mantenimiento (OM)</option>
                                    </select>
                                </td>
                                <td><strong>Descripcion OT:</strong></td>
                                <td><input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($trabajo['titulo']); ?>" required></td>
                            </tr>
                            <tr>
                                <td><strong>Empresa Mandante:</strong></td>
                                <td>
                                    <select name="cliente_id" class="form-control select2" required>
                                        <option value="">Seleccione una Empresa</option>
                                        <?php
                                        $empresas_sql = "SELECT idcliente, empresa FROM cliente ORDER BY empresa ASC";
                                        $empresas_result = $conn->query($empresas_sql);
                                        while ($empresa = $empresas_result->fetch_assoc()) {
                                            $selected = ($empresa['idcliente'] == $trabajo['cliente_id']) ? 'selected' : '';
                                            echo "<option value='{$empresa['idcliente']}' $selected>{$empresa['empresa']}</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td><strong>N° Orden:</strong></td>
                                <td><input type="text" name="nro_orden" class="form-control" value="<?php echo htmlspecialchars($trabajo['nro_orden']); ?>" required></td>
                                
                            </tr>
                            <td><strong>N° Aviso:</strong></td>
                            <td><input type="text" name="nro_aviso" class="form-control" value="<?php echo htmlspecialchars($trabajo['nro_aviso']); ?>"></td>

                            <!-- Espacio en blanco -->
                            <tr><td colspan="4" style="height: 10px; border: none;"></td></tr>

                            <!-- Descripción -->
                            <tr>
                                <td><strong>Descripción de la Orden:</strong></td>
                                <td colspan="3"><textarea name="descripcion_orden" class="form-control"><?php echo htmlspecialchars($trabajo['descripcion_orden']); ?></textarea></td>
                            </tr>

                            <!-- Espacio en blanco -->
                            <tr><td colspan="4" style="height: 10px; border: none;"></td></tr>

                            <!-- Información Adicional -->
                            <tr>
                                <td><strong>Clase de Orden:</strong></td>
                                <td><input type="text" name="clase_orden" class="form-control" value="<?php echo htmlspecialchars($trabajo['clase_orden']); ?>"></td>
                                <td><strong>Prioridad:</strong></td>
                                <td><input type="text" name="prioridad" class="form-control" value="<?php echo htmlspecialchars($trabajo['prioridad']); ?>"></td>
                            </tr>
                            <tr>
                                <td><strong>Grupo de Planificación:</strong></td>
                                <td><input type="text" name="grp_planificacion" class="form-control" value="<?php echo htmlspecialchars($trabajo['grp_planificacion']); ?>"></td>
                                <td><strong>Ubicación Técnica:</strong></td>
                                <td><input type="text" name="ubicacion_tecnica" class="form-control" value="<?php echo htmlspecialchars($trabajo['ubicacion_tecnica']); ?>"></td>
                            </tr>
                            <tr>
                                <td><strong>Denominación del Equipo:</strong></td>
                                <td><input type="text" name="den_equipo" class="form-control" value="<?php echo htmlspecialchars($trabajo['den_equipo']); ?>"></td>
                                <td><strong>Clase de Actividad PL:</strong></td>
                                <td><input type="text" name="clase_actividad_pl" class="form-control" value="<?php echo htmlspecialchars($trabajo['clase_actividad_pl']); ?>"></td>
                            </tr>
                            <tr>
                                <td><strong>Revisión:</strong></td>
                                <td><input type="text" name="revision" class="form-control" value="<?php echo htmlspecialchars($trabajo['revision']); ?>"></td>
                                <td><strong>Punto de Trabajo Responsable:</strong></td>
                                <td><input type="text" name="pto_trab_responsable" class="form-control" value="<?php echo htmlspecialchars($trabajo['pto_trab_responsable']); ?>"></td>
                            </tr>
                            <tr>
                                <td><strong>Equipo:</strong></td>
                                <td><input type="text" name="equipo" class="form-control" value="<?php echo htmlspecialchars($trabajo['equipo']); ?>"></td>
                                <td><strong>Reserva:</strong></td>
                                <td><input type="text" name="reserva" class="form-control" value="<?php echo htmlspecialchars($trabajo['reserva']); ?>"></td>
                            </tr>

                            <!-- Espacio en blanco -->
                            <tr><td colspan="4" style="height: 10px; border: none;"></td></tr>
                            <tr>
                                <td><strong>Solicitud de Pedido:</strong></td>
                                <td>
                                    <select name="sol_ped" class="form-control">
                                        <option value="Servicios" <?php echo ($trabajo['sol_ped'] == 'Servicios') ? 'selected' : ''; ?>>Servicios</option>
                                        <option value="Materiales" <?php echo ($trabajo['sol_ped'] == 'Materiales') ? 'selected' : ''; ?>>Materiales</option>
                                    </select>
                                </td>
                                <td></td> <!-- Columna vacía para mantener formato -->
                                <td></td> <!-- Columna vacía para mantener formato -->
                            </tr>

                            <!-- Espacio en blanco -->
                            <tr><td colspan="4" style="height: 10px; border: none;"></td></tr>
                                                        <tr>

                            <tr>
                                <td><strong>Fecha de Creación:</strong></td>
                                <td><input type="date" name="fecha_creacion" class="form-control" value="<?php echo htmlspecialchars($trabajo['fecha_creacion']); ?>" readonly></td>

                                <td><strong>Fecha de Entrega:</strong></td>
                                <td><input type="date" name="fecha_entrega" class="form-control" value="<?php echo htmlspecialchars($trabajo['fecha_entrega']); ?>"></td>
                            </tr>
                            <tr>
                                <td><strong>Fecha de Inicio:</strong></td>
                                <td><input type="date" name="fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($trabajo['fecha_inicio']); ?>"></td>

                                <td><strong>Duración Estimada (Días):</strong></td>
                                <td><input type="number" name="duracion_dias" class="form-control" value="<?php echo htmlspecialchars($trabajo['duracion_dias']); ?>"></td>
                            </tr>
                            <tr>
                                <td><strong>Hora de Inicio:</strong></td>
                                <td><input type="time" name="hora_inicio" class="form-control" value="<?php echo htmlspecialchars($trabajo['hora_inicio']); ?>"></td>

                                <td><strong>Hora de Fin:</strong></td>
                                <td><input type="time" name="hora_fin" class="form-control" value="<?php echo htmlspecialchars($trabajo['hora_fin']); ?>"></td>
                            </tr>

                            <tr><td colspan="4" style="height: 10px; border: none;"></td></tr>
                                                        <tr>
                                                        <tr>
                                <td><strong>Descripción Detallada:</strong></td>
                                <td colspan="3">
                                    <textarea name="descripcion_detallada" class="form-control" rows="3" required><?php echo htmlspecialchars($trabajo['descripcion_detallada']); ?></textarea>
                                </td>
                            </tr>

                            <!-- Estado -->
                            <tr>
                                <td><strong>Estado:</strong></td>
                                <td>
                                    <select name="estado" class="form-control" required>
                                        <option value="Pendiente" <?php echo ($trabajo['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="En Proceso" <?php echo ($trabajo['estado'] == 'En Proceso') ? 'selected' : ''; ?>>En Proceso</option>
                                        <option value="Completado" <?php echo ($trabajo['estado'] == 'Completado') ? 'selected' : ''; ?>>Completado</option>
                                    </select>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div style="width: 100%; margin-top: 100px;"></div>
<!-- ✅ Tabla de Operaciones -->
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
            <?php while ($op = $operaciones->fetch_assoc()): ?>
                <tr>
                    <td><input type="text" name="operacion[]" class="form-control" value="<?php echo htmlspecialchars($op['operacion']); ?>"></td>
                    <td><input type="text" name="pto_trab[]" class="form-control" value="<?php echo htmlspecialchars($op['pto_trab']); ?>"></td>
                    <td><input type="text" name="desc_pto_trab[]" class="form-control" value="<?php echo htmlspecialchars($op['desc_pto_trab']); ?>"></td>
                    <td><input type="text" name="desc_oper[]" class="form-control" value="<?php echo htmlspecialchars($op['desc_oper']); ?>"></td>
                    <td><input type="number" name="n_pers[]" class="form-control" value="<?php echo htmlspecialchars($op['n_pers']); ?>"></td>
                    <td><input type="number" name="h_est[]" class="form-control" step="0.1" value="<?php echo htmlspecialchars($op['h_est']); ?>"></td>
                    <td><input type="number" name="hh_tot_prog[]" class="form-control" step="0.1" value="<?php echo htmlspecialchars($op['hh_tot_prog']); ?>"></td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">Eliminar</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <button type="button" class="btn btn-success" onclick="agregarOperacion()">Agregar Operación</button>
</div>


<div style="width: 100%; margin-top: 100px;"></div>

<!------------------ MATERIALES MATERIALES MATERIALES MATERIALES MATERIALES MATERIALES MATERIALES MATERIALES MATERIALES ----------------------------------------------------->
<table class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>N° Asignación</th>
            <th>Descripción</th>
            <th>Total</th>
            <th>En Uso</th>
            <th>Disponibles</th>
            <th>Asignados a OT</th>
            <th>Acción</th>

        </tr>
    </thead>
    <tbody id="materialesBody">
<?php while ($row = $result->fetch_assoc()): ?>
<tr data-producto-id="<?php echo $row['producto_id']; ?>" id="row-<?php echo $row['producto_id']; ?>">
    <td><?php echo $row['producto_id']; ?></td>
    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
    <td><?php echo $row['nro_asignacion']; ?></td>
    <td><?php echo htmlspecialchars($row['descripcion_asignacion']); ?></td>
    <td><?php echo $row['cantidad_total']; ?></td>
    <td class="en-uso"><?php echo $row['en_uso']; ?></td>
    <td class="disponibles"
        data-total="<?php echo $row['cantidad_total']; ?>"
        data-enuso="<?php echo $row['en_uso']; ?>">
        <?php echo ($row['cantidad_total'] - $row['en_uso']); ?>
    </td>
    <td><?php echo $row['cantidad_asignada']; ?></td>
    <td>
        <button type="button"
                class="btn btn-sm btn-danger btn-eliminar-material"
                data-id="<?php echo $row['producto_id']; ?>"
                data-nombre="<?php echo htmlspecialchars($row['nombre']); ?>"
                data-cantidad="<?php echo $row['cantidad_asignada']; ?>">
            <i class="fas fa-trash-alt"></i> Eliminar
        </button>
        <input type="hidden"
               name="materiales_eliminados[<?php echo $row['producto_id']; ?>]"
               id="eliminado-<?php echo $row['producto_id']; ?>"
               value="0">
    </td>
</tr>
<?php endwhile; ?>
</tbody>

</table>
<div class="card mt-4 shadow-sm border rounded-4">
    <div class="card-body">
        <h5 class="card-title mb-3">➕ Agregar Material al Trabajo</h5>

        <div class="row g-3 align-items-end">
            <!-- Selector de producto -->
            <div class="col-md-6">
                <label for="materialSelect" class="form-label fw-semibold">Producto:</label>
                <select id="materialSelect" class="form-select select2" >
                    <option value="">Seleccione un producto</option>
                    <?php
                    $productos = $conn->query("SELECT idproducto, nombre, cantidad, en_uso FROM producto WHERE eliminado = 0 ORDER BY nombre ASC");
                    while ($p = $productos->fetch_assoc()) {
                        $disponibles = $p['cantidad'] - $p['en_uso'];
                        $nombre = htmlspecialchars($p['nombre']);
                        echo "<option value='{$p['idproducto']}'
                                    data-nombre='{$nombre}'
                                    data-total='{$p['cantidad']}'
                                    data-enuso='{$p['en_uso']}'
                                    data-disponibles='{$disponibles}'>
                                {$p['idproducto']} - {$nombre} (Disponibles: {$disponibles})
                              </option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Cantidad -->
            <div class="col-md-3">
                <label for="cantidadMaterial" class="form-label fw-semibold">Cantidad:</label>
                <input type="number" id="cantidadMaterial" class="form-control form-control-sm" min="1" value="1" required>
            </div>

            <!-- Botón -->
            <div class="col-md-3">
                <button type="button" class="btn btn-success btn-sm w-100" onclick="agregarMaterial()">
                    <i class="fas fa-plus me-1"></i> Agregar
                </button>
            </div>
        </div>
    </div>
</div>




<div style="width: 100%; margin-top: 100px;"></div>

<!-- ✅ Tabla de Trabajadores Asignados -->
<div class="mb-3">
    <label>Trabajadores Asignados:</label>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Trabajador</th>
                <th>Horas Trabajadas</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody id="trabajadoresBody">
        <?php
$empleados_sql = "SELECT e.idempleado, e.nombre,
                    (SELECT COUNT(*) FROM trabajo_trabajadores tt 
                     JOIN trabajo t ON tt.id_trabajo = t.idtrabajo 
                     WHERE tt.id_trabajador = e.idempleado AND t.estado != 'Completado') AS asignado
                  FROM empleado e
                  ORDER BY e.nombre ASC";
$empleados_result = $conn->query($empleados_sql);
?>

<?php while ($trab = $trabajadores->fetch_assoc()): ?>
    <tr>
        <td>
            <select name="trabajador_id[]" class="form-control select2" required>
                <option value="">Seleccione un Trabajador</option>
                <?php
                mysqli_data_seek($empleados_result, 0); // reiniciar puntero del result
                while ($empleado = $empleados_result->fetch_assoc()) {
                    $selected = ($empleado['idempleado'] == $trab['id_trabajador']) ? 'selected' : '';
                    $asignadoText = ($empleado['asignado'] > 0) ? ' (Ya asignado)' : '';
                    echo "<option value='{$empleado['idempleado']}' $selected>{$empleado['nombre']}{$asignadoText}</option>";
                }
                ?>
            </select>
        </td>
        <td><input type="number" name="horas_trabajadas[]" class="form-control" value="<?php echo $trab['horas_trabajadas']; ?>" min="1"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">Eliminar</button></td>
    </tr>
<?php endwhile; ?>

        </tbody>
    </table>
    <button type="button" class="btn btn-success" onclick="agregarTrabajador()">Agregar Trabajador</button>
</div>



<!-- ✅ Incluir jQuery y Select2 (CDN) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // ✅ Inicializa Select2 en los select existentes
    $(".select2").select2({
        width: '100%',
        placeholder: "Seleccione una opción",
        allowClear: true
    });
});

// ✅ Función para inicializar Select2 en nuevos elementos agregados dinámicamente
function inicializarSelect2() {
    $(".select2").select2({
        width: '100%',
        placeholder: "Seleccione una opción",
        allowClear: true
    });
}

// ✅ Función para agregar un trabajador dinámicamente con Select2
function agregarTrabajador() {
    let fila = `
        <tr>
            <td>
                <select name="trabajador_id[]" class="form-control select2" required>
                    <option value="">Seleccione un Trabajador</option>
                    <?php
                    $empleados_sql = "SELECT idempleado, nombre FROM empleado ORDER BY nombre ASC";
                    $empleados_result = $conn->query($empleados_sql);
                    while ($empleado = $empleados_result->fetch_assoc()) {
                        echo "<option value='{$empleado['idempleado']}'>{$empleado['nombre']}</option>";
                    }
                    ?>
                </select>
            </td>
            <td><input type="number" name="horas_trabajadas[]" class="form-control" min="1"></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">Eliminar</button></td>
        </tr>
    `;
    document.getElementById("trabajadoresBody").insertAdjacentHTML("beforeend", fila);

    // ✅ Re-inicializar Select2 en la nueva fila agregada
    inicializarSelect2();
}

// ✅ Función para eliminar una fila de la tabla
function eliminarFila(btn) {
    btn.closest("tr").remove();
}
</script>



<script>
    // ✅ Función para mostrar alertas
    function mostrarAlerta(mensaje, tipo = 'success') {
        if (typeof toastr !== 'undefined') {
            toastr[tipo](mensaje);
        } else {
            alert(mensaje);
        }
    }

    // ✅ Función para eliminar un material de la tabla
    function manejarEliminacion(fila, id, nombre, cantidad) {
        if (!confirm(`¿Eliminar ${cantidad} unidad(es) de "${nombre}"?\nEsta acción liberará el stock asignado.`)) {
            return;
        }

        // Marcar el input hidden como eliminado
        const input = document.getElementById(`eliminado-${id}`);
        if (input) input.value = cantidad;

        // Actualizar stock visualmente en la tabla
        const disponibles = fila.find('.disponibles');
        const enUso = fila.find('.en-uso');

        const cantidadAsignada = parseInt(cantidad);
        const disponiblesActual = parseInt(disponibles.text()) || 0;
        const enUsoActual = parseInt(enUso.text()) || 0;

        disponibles.text(disponiblesActual + cantidadAsignada);
        enUso.text(Math.max(0, enUsoActual - cantidadAsignada));

        // Estilo visual de fila eliminada
        fila.addClass('table-danger eliminado');
        fila.find('.btn-eliminar-material').prop('disabled', true);
    }

    // ✅ Evento de eliminación (para filas existentes y nuevas)
    $(document).on('click', '.btn-eliminar-material', function () {
        const button = $(this);
        const fila = button.closest('tr');
        const id = button.data('id');
        const nombre = button.data('nombre');
        const cantidad = button.data('cantidad');

        manejarEliminacion(fila, id, nombre, cantidad);
    });

    // ✅ Función para agregar un nuevo material
    function agregarMaterial() {
        const select = $('#materialSelect');
        const id = select.val();
        const selected = select.find('option:selected');
        const nombre = selected.data('nombre');
        const total = parseInt(selected.data('total'));
        const enUso = parseInt(selected.data('enuso'));
        const disponibles = parseInt(selected.data('disponibles'));
        const cantidad = parseInt($('#cantidadMaterial').val());

        if (!id || isNaN(cantidad) || cantidad <= 0) {
            mostrarAlerta("Selecciona un producto válido y una cantidad mayor a cero.", "warning");
            return;
        }

        if (cantidad > disponibles) {
            mostrarAlerta(`No puedes asignar más de ${disponibles} unidades disponibles.`, "error");
            return;
        }

        if ($(`#row-${id}`).length > 0) {
            mostrarAlerta("Este material ya fue agregado a la tabla.", "warning");
            return;
        }

        const nuevoEnUso = enUso + cantidad;
        const nuevoDisponible = total - nuevoEnUso;

        const nuevaFila = `
            <tr id="row-${id}" data-producto-id="${id}">
                <td>${id}</td>
                <td>${nombre}</td>
                <td>-</td>
                <td></td>
                <td>${total}</td>
                <td class="en-uso">${nuevoEnUso}</td>
                <td class="disponibles" data-total="${total}" data-enuso="${nuevoEnUso}">${nuevoDisponible}</td>
                <td>${cantidad}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger btn-eliminar-material"
                            data-id="${id}" data-nombre="${nombre}" data-cantidad="${cantidad}">
                        <i class="fas fa-trash-alt"></i> Eliminar
                    </button>
                    <input type="hidden" name="material_id[]" value="${id}">
                    <input type="hidden" name="cantidad_material[]" value="${cantidad}">
                    <input type="hidden" name="materiales_eliminados[${id}]" id="eliminado-${id}" value="0">
                </td>
            </tr>`;

        $('#materialesBody').append(nuevaFila);
        mostrarAlerta(`✅ Se agregó ${cantidad} unidad(es) de ${nombre}`, 'success');

        // Reset inputs
        $('#materialSelect').val('').trigger('change');
        $('#cantidadMaterial').val(1);
    }
</script>

<script>
function agregarOperacion() {
    let fila = `
        <tr>
            <td><input type="text" name="operacion[]" class="form-control"></td>
            <td><input type="text" name="pto_trab[]" class="form-control"></td>
            <td><input type="text" name="desc_pto_trab[]" class="form-control"></td>
            <td><input type="text" name="desc_oper[]" class="form-control"></td>
            <td><input type="number" name="n_pers[]" class="form-control"></td>
            <td><input type="number" name="h_est[]" class="form-control" step="0.1"></td>
            <td><input type="number" name="hh_tot_prog[]" class="form-control" step="0.1"></td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">Eliminar</button>
            </td>
        </tr>
    `;
    document.getElementById("operacionesBody").insertAdjacentHTML("beforeend", fila);
}

function eliminarFila(btn) {
    btn.closest("tr").remove();
}
</script>










<!-- Espaciado extra para permitir desplazamiento -->
<div style="height: 60px;"></div>
</body>
</html>

<!-- Botones -->
<div class="d-flex justify-content-between mt-4">
    <!-- 🔹 Botones de Eliminar y Cancelar a la Izquierda -->
    <div>
    <button type="button" class="btn btn-secondary ms-2" onclick="window.history.back();">Volver atras</button>
        <button type="button" class="btn btn-danger" onclick="confirmarEliminacion(<?php echo $trabajo_id; ?>)">Eliminar Trabajo</button>
        
    </div>
    
    <!-- 🔹 Botón de Guardar Cambios a la Derecha -->
    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
</div>



        
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>





<script>
function confirmarEliminacion(trabajoId) {
    if (confirm("⚠️ ¿Estás seguro de que deseas eliminar este trabajo? Esta acción no se puede deshacer.")) {
        window.location.href = "eliminar_trabajo.php?id=" + trabajoId;
    }
}
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('guardado') === 'ok') {
        Swal.fire({
            icon: 'success',
            title: 'Trabajo actualizado correctamente',
            showConfirmButton: false,
            timer: 1800
        }).then(() => {
            window.location.href = 'trabajos.php';
        });
    }
});
</script>





<!-- Espaciado extra para permitir desplazamiento -->
<div style="height: 200px;"></div>



</body>



</html>