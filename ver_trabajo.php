<?php
include 'db.php';
session_start();

// Verificar si se recibió un ID de trabajo
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID de trabajo no especificado.'); window.location.href='trabajos.php';</script>";
    exit();
}

$trabajo_id = intval($_GET['id']);

// ✅ Obtener datos del trabajo
$sql_trabajo = "SELECT t.*, c.empresa 
                FROM trabajo t 
                LEFT JOIN cliente c ON t.cliente_id = c.idcliente 
                WHERE t.idtrabajo = ?";
$stmt_trabajo = $conn->prepare($sql_trabajo);
if (!$stmt_trabajo) {
    die("Error en la preparación de la consulta de trabajo: " . $conn->error);
}
$stmt_trabajo->bind_param("i", $trabajo_id);
$stmt_trabajo->execute();
$result = $stmt_trabajo->get_result();
$trabajo = $result->fetch_assoc();

if (!$trabajo) {
    echo "<script>alert('Trabajo no encontrado.'); window.location.href='trabajos.php';</script>";
    exit();
}

// ✅ Obtener operaciones del trabajo
$sql_operaciones = "SELECT * FROM operaciones WHERE trabajo_id = ?";
$stmt_operaciones = $conn->prepare($sql_operaciones);
if (!$stmt_operaciones) {
    die("Error en la preparación de la consulta de operaciones: " . $conn->error);
}
$stmt_operaciones->bind_param("i", $trabajo_id);
$stmt_operaciones->execute();
$operaciones = $stmt_operaciones->get_result();

// ✅ Obtener materiales del trabajo
$sql_materiales = "
    SELECT tp.cantidad, 
           p.nombre, 
           p.descripcion, 
           p.nro_asignacion 
    FROM trabajo_producto tp
    JOIN producto p ON tp.producto_id = p.idproducto
    WHERE tp.trabajo_id = ?";
$stmt_materiales = $conn->prepare($sql_materiales);
if (!$stmt_materiales) {
    die("Error en la preparación de la consulta de materiales: " . $conn->error);
}
$stmt_materiales->bind_param("i", $trabajo_id);
$stmt_materiales->execute();
$materiales = $stmt_materiales->get_result();


// ✅ Obtener trabajadores asignados
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
    <title>Ver Trabajo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .logo-container {
            position: absolute;
            top: 10px;
            left: 10px;
        }

        .logo-container img {
            max-height: 200px;
            width: auto;
        }
    </style>
</head>
<body>

<!-- Logo de la compañía -->
<div class="logo-container">
    <img src="img/compañia.png" alt="Logo Compañía">
</div>


<div class="container mt-4">
    <h2 class="mb-4">Detalles del Trabajo</h2>

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
                            <td><?php echo htmlspecialchars($trabajo['tipo']); ?></td>
                            <td><strong>Título:</strong></td>
                            <td><?php echo htmlspecialchars($trabajo['titulo']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Empresa:</strong></td>
                            <td><?php echo htmlspecialchars($trabajo['empresa'] ?? 'Sin Empresa'); ?></td>
                            <td><strong>N° Orden:</strong></td>
                            <td><?php echo htmlspecialchars($trabajo['nro_orden']); ?></td>
                        </tr>

<!-- Espacio en blanco -->
<tr>
    <td colspan="4" style="height: 10px; border: none;"></td>
</tr>
                        <!-- Fila 2 - Descripción -->
                        <tr>
                            <td><strong>Descripción de la Orden:</strong></td>
                            <td colspan="3"><?php echo htmlspecialchars($trabajo['descripcion_orden']); ?></td>
                        </tr>
                        
<!-- Espacio en blanco -->
<tr>
    <td colspan="4" style="height: 10px; border: none;"></td>
</tr>

                        <!-- Fila 3 -->
                        <tr>
                            <td><strong>Clase de Orden:</strong></td>
                            <td><?php echo htmlspecialchars($trabajo['clase_orden']); ?></td>
                            <td><strong>Clase de Actividad PL:</strong></td>
                            <td><?php echo htmlspecialchars($trabajo['clase_actividad_pl']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Prioridad:</strong></td>
                            <td><?php echo htmlspecialchars($trabajo['prioridad']); ?></td>
                            <td><strong>Revisión:</strong></td>
                            <td><?php echo htmlspecialchars($trabajo['revision']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Grupo de Planificación:</strong></td>
                            <td><?php echo htmlspecialchars($trabajo['grp_planificacion']); ?></td>
                            <td><strong>Punto de Trabajo Responsable:</strong></td>
                            <td><?php echo htmlspecialchars($trabajo['pto_trab_responsable']); ?></td>
                        </tr>

                        <!-- Fila 4 -->
                        <tr>
                            <td><strong>Ubicación Técnica:</strong></td>
                            <td><?php echo htmlspecialchars($trabajo['ubicacion_tecnica']); ?></td>
                            <td><strong>Equipo:</strong></td>
                            <td><?php echo htmlspecialchars($trabajo['equipo']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Denominación del Equipo:</strong></td>
                            <td><?php echo htmlspecialchars($trabajo['den_equipo']); ?></td>
                            <td><strong>Reserva:</strong></td>
                            <td><?php echo htmlspecialchars($trabajo['reserva']); ?></td>
                        </tr>
                        
<!-- Espacio en blanco -->
<tr>
    <td colspan="4" style="height: 10px; border: none;"></td>
</tr>
                        <tr>
                            <td><strong>Solicitud de Pedido:</strong></td>
                            <td><?php echo htmlspecialchars($trabajo['sol_ped']); ?></td>
                            
                        </tr>

<!-- Espacio en blanco -->
<tr>
    <td colspan="4" style="height: 10px; border: none;"></td>
</tr>

                        <!-- Fila 5 - Fechas y Horas (Agrupadas en un solo lado) -->
                        <tr>
                            <td><strong>Fecha de Creación:</strong></td>
                            <td><?php echo date("d-m-Y", strtotime($trabajo['fecha_creacion'])); ?></td>
                            <td><strong>Fecha de Inicio:</strong></td>
                            <td><?php echo date("d-m-Y", strtotime($trabajo['fecha_inicio'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Fecha de Entrega:</strong></td>
                            <td><?php echo !empty($trabajo['fecha_entrega']) ? date("d-m-Y", strtotime($trabajo['fecha_entrega'])) : 'No definida'; ?></td>
                            <td><strong>Duración Estimada (Días):</strong></td>
                            <td><?php echo htmlspecialchars($trabajo['duracion_dias']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Hora de Inicio:</strong></td>
                            <td><?php echo htmlspecialchars($trabajo['hora_inicio']); ?></td>
                            <td><strong>Hora de Fin:</strong></td>
                            <td><?php echo htmlspecialchars($trabajo['hora_fin']); ?></td>
                        </tr>

<!-- Espacio en blanco -->
<tr>
    <td colspan="4" style="height: 10px; border: none;"></td>
</tr>
                        <!-- Fila 6 - Descripción Detallada -->
                        <tr>
                            <td><strong>Descripción Detallada:</strong></td>
                            <td colspan="3"><?php echo nl2br(htmlspecialchars($trabajo['descripcion_detallada'])); ?></td>
                        </tr>
                        
<!-- Espacio en blanco -->
<tr>
    <td colspan="4" style="height: 10px; border: none;"></td>
</tr>
<tr>
    <td><strong>Estado:</strong></td>
    <td>
        <?php echo htmlspecialchars($trabajo['estado']); ?>
        
    </td>
    <td>
    <button class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#modalActualizarEstado">
            Actualizar Estado
        </button> 
    </td>
   
</tr>

                            
<!-- Espacio en blanco -->
<tr>
    <td colspan="4" style="height: 10px; border: none;"></td>
</tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>






<!-- Operaciones -->
<div class="card mt-3" style="max-width: 1300px; margin: auto;"> <!-- ✅ Limitamos el ancho -->
    <div class="card-header bg-secondary text-white">
        Operaciones
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Operación</th>
                        <th>Pto. Trab.</th>
                        <th>Desc. Pto. Trab.</th>
                        <th>Desc. Oper.</th>
                        <th>N° Pers.</th>
                        <th>H Est.</th>
                        <th>HH Tot. Prog.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($op = $operaciones->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $op['operacion']; ?></td>
                            <td><?php echo $op['pto_trab']; ?></td>
                            <td><?php echo $op['desc_pto_trab']; ?></td>
                            <td><?php echo $op['desc_oper']; ?></td>
                            <td><?php echo $op['n_pers']; ?></td>
                            <td><?php echo $op['h_est']; ?></td>
                            <td><?php echo $op['hh_tot_prog']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Materiales -->
<div class="card mt-3" style="max-width: 1300px; margin: auto;"> <!-- ✅ Limitamos el ancho -->
    <div class="card-header bg-success text-white">
        Materiales Utilizados
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Producto</th>
                        <th>Descripción</th>
                        <th>Nro Asignación</th> <!-- ✅ Cambiado de "Número de Parte" a "Nro Asignación" -->
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($mat = $materiales->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($mat['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($mat['descripcion']); ?></td>
                            <td><?php echo htmlspecialchars($mat['nro_asignacion']); ?></td> <!-- ✅ Asegúrate de que la clave sea la correcta -->
                            <td><?php echo htmlspecialchars($mat['cantidad']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- Trabajadores Asignados -->
<div class="card mt-3" style="max-width: 1300px; margin: auto;"> <!-- ✅ Limitamos el ancho -->
    <div class="card-header bg-warning">
        Trabajadores Asignados
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Trabajador</th>
                        <th>Horas Trabajadas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($trab = $trabajadores->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($trab['trabajador']); ?></td>
                            <td><?php echo htmlspecialchars($trab['horas_trabajadas']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- ✅ Mini Modal para Actualizar Estado -->
<div class="modal fade" id="modalActualizarEstado" tabindex="-1" aria-labelledby="modalActualizarEstadoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalActualizarEstadoLabel">Actualizar Estado del Trabajo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="actualizar_estado.php" method="POST">
                    <input type="hidden" name="trabajo_id" value="<?php echo $trabajo_id; ?>">
                    
                    <div class="mb-3">
                        <label for="nuevo_estado" class="form-label">Selecciona un nuevo estado:</label>
                        <select name="nuevo_estado" id="nuevo_estado" class="form-control" required>
                            <option value="Pendiente">Pendiente</option>
                            <option value="En Proceso">En Proceso</option>
                            <option value="Completado">Completado</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Actualizar Estado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Espacio superior para separar los botones de la tabla -->
<div style="height: 60px;"></div>

<!-- Botones con espacio reducido entre ellos -->
<div class="d-flex justify-content-center gap-3">
    <a href="trabajos.php" class="btn btn-secondary mt-3">Volver</a>
    <!-- Botón para descargar PDF -->
    <a href="generar_pdf.php?id=<?php echo $trabajo_id; ?>" class="btn btn-danger mt-3">
        <i class="fas fa-file-pdf"></i> Descargar PDF
    </a>
</div>


<!-- Espacio inferior para separar los botones del final de la página -->
<div style="height: 200px;"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
