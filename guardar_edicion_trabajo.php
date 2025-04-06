<?php
include 'db.php';
session_start();
date_default_timezone_set('America/Santiago');
file_put_contents("debug_materiales.txt", print_r($_POST['materiales_eliminados'], true));

if (!isset($_POST['trabajo_id']) || empty($_POST['trabajo_id'])) {
    die("Error: ID de trabajo no especificado.");
}

$trabajo_id = intval($_POST['trabajo_id']);
$usuario_id = $_SESSION['usuario_id'];

// Capturar datos del formulario
$datos_nuevos = [
    'titulo' => $_POST['titulo'] ?? '',
    'tipo' => $_POST['tipo'] ?? '',
    'cliente_id' => $_POST['cliente_id'] ?? '',
    'descripcion_orden' => $_POST['descripcion_orden'] ?? '',
    'nro_aviso' => $_POST['nro_aviso'] ?? '',
    'ubicacion_tecnica' => $_POST['ubicacion_tecnica'] ?? '',
    'equipo' => $_POST['equipo'] ?? '',
    'den_equipo' => $_POST['den_equipo'] ?? '',
    'clase_orden' => $_POST['clase_orden'] ?? '',
    'clase_actividad_pl' => $_POST['clase_actividad_pl'] ?? '',
    'prioridad' => $_POST['prioridad'] ?? '',
    'revision' => $_POST['revision'] ?? '',
    'grp_planificacion' => $_POST['grp_planificacion'] ?? '',
    'pto_trab_responsable' => $_POST['pto_trab_responsable'] ?? '',
    'reserva' => $_POST['reserva'] ?? '',
    'sol_ped' => $_POST['sol_ped'] ?? '',
    'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
    'hora_inicio' => $_POST['hora_inicio'] ?? '',
    'hora_fin' => $_POST['hora_fin'] ?? '',
    'duracion_dias' => $_POST['duracion_dias'] ?? '',
    'fecha_entrega' => $_POST['fecha_entrega'] ?? '',
    'descripcion_detallada' => $_POST['descripcion_detallada'] ?? '',
    'estado' => $_POST['estado'] ?? ''
];

// Obtener datos anteriores para comparar
$sql_anterior = "SELECT * FROM trabajo WHERE idtrabajo = ?";
$stmt_anterior = $conn->prepare($sql_anterior);
$stmt_anterior->bind_param("i", $trabajo_id);
$stmt_anterior->execute();
$resultado_anterior = $stmt_anterior->get_result();
$datos_anteriores = $resultado_anterior->fetch_assoc();

// Actualizar datos en tabla trabajo
$sql_update_trabajo = "UPDATE trabajo SET titulo=?, tipo=?, cliente_id=?, descripcion_orden=?, nro_aviso=?, ubicacion_tecnica=?, equipo=?, den_equipo=?, clase_orden=?, clase_actividad_pl=?, prioridad=?, revision=?, grp_planificacion=?, pto_trab_responsable=?, reserva=?, sol_ped=?, fecha_inicio=?, hora_inicio=?, hora_fin=?, duracion_dias=?, fecha_entrega=?, descripcion_detallada=?, estado=? WHERE idtrabajo=?";

$stmt = $conn->prepare($sql_update_trabajo);
$stmt->bind_param(
    "ssissssssssssssssssisssi",
    $datos_nuevos['titulo'], $datos_nuevos['tipo'], $datos_nuevos['cliente_id'], $datos_nuevos['descripcion_orden'], $datos_nuevos['nro_aviso'],
    $datos_nuevos['ubicacion_tecnica'], $datos_nuevos['equipo'], $datos_nuevos['den_equipo'], $datos_nuevos['clase_orden'], $datos_nuevos['clase_actividad_pl'],
    $datos_nuevos['prioridad'], $datos_nuevos['revision'], $datos_nuevos['grp_planificacion'], $datos_nuevos['pto_trab_responsable'],
    $datos_nuevos['reserva'], $datos_nuevos['sol_ped'], $datos_nuevos['fecha_inicio'], $datos_nuevos['hora_inicio'], $datos_nuevos['hora_fin'],
    $datos_nuevos['duracion_dias'], $datos_nuevos['fecha_entrega'], $datos_nuevos['descripcion_detallada'], $datos_nuevos['estado'], $trabajo_id
);
if ($stmt->execute()) {
    // Registrar cambios en el historial
    foreach ($datos_nuevos as $campo => $nuevo_valor) {
        if ($datos_anteriores[$campo] != $nuevo_valor) {
            $detalle = "Campo '$campo' cambió de '" . $datos_anteriores[$campo] . "' a '$nuevo_valor'";

            $sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id) VALUES (?, 'Actualización', 'trabajo', ?, ?, ?)";
            $stmt_historial = $conn->prepare($sql_historial);
            $fecha_actual = date('Y-m-d H:i:s');

            $stmt_historial->bind_param("sisi", $fecha_actual, $trabajo_id, $detalle, $usuario_id);
            $stmt_historial->execute();
            $stmt_historial->close();
        }
    }
}

// ==================== ELIMINAR MATERIALES ASIGNADOS ====================
if (!empty($_POST['materiales_eliminados']) && is_array($_POST['materiales_eliminados'])) {
    foreach ($_POST['materiales_eliminados'] as $producto_id => $cantidad) {
        $producto_id = intval($producto_id);
        $cantidad = intval($cantidad);

        if ($cantidad <= 0) continue;

        // Eliminar de trabajo_producto
        $sql_eliminar = "DELETE FROM trabajo_producto WHERE trabajo_id = ? AND producto_id = ?";
        $stmt_eliminar = $conn->prepare($sql_eliminar);
        $stmt_eliminar->bind_param("ii", $trabajo_id, $producto_id);
        $stmt_eliminar->execute();

        // Actualizar producto: disminuir en_uso
        $sql_update_producto = "UPDATE producto SET en_uso = GREATEST(0, en_uso - ?) WHERE idproducto = ?";
        $stmt_update = $conn->prepare($sql_update_producto);
        $stmt_update->bind_param("ii", $cantidad, $producto_id);
        $stmt_update->execute();

        // Registrar en historial
        $detalle = "Se eliminaron $cantidad unidades del producto $producto_id de la OT $trabajo_id";
        $sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                          VALUES (NOW(), 'Eliminación', 'material', ?, ?, ?)";
        $stmt_historial = $conn->prepare($sql_historial);
        $stmt_historial->bind_param("isi", $producto_id, $detalle, $usuario_id);
        $stmt_historial->execute();
    }
}
// ==================== FIN ELIMINAR MATERIALES ====================
// ==================== AGREGAR MATERIALES ====================
if (!empty($_POST['material_id']) && is_array($_POST['material_id'])) {
    $sql_insert_material = "INSERT INTO trabajo_producto (trabajo_id, producto_id, cantidad) VALUES (?, ?, ?)";
    $stmt_insert_material = $conn->prepare($sql_insert_material);

    $sql_update_producto = "UPDATE producto SET en_uso = en_uso + ? WHERE idproducto = ?";
    $stmt_update_producto = $conn->prepare($sql_update_producto);

    foreach ($_POST['material_id'] as $index => $material_id) {
        $material_id = intval($material_id);
        $cantidad = intval($_POST['cantidad_material'][$index] ?? 0);

        if ($cantidad <= 0) continue;

        // Insertar en trabajo_producto
        $stmt_insert_material->bind_param("iii", $trabajo_id, $material_id, $cantidad);
        $stmt_insert_material->execute();

        // Actualizar en_uso en producto
        $stmt_update_producto->bind_param("ii", $cantidad, $material_id);
        $stmt_update_producto->execute();

        // Registrar en historial
        $detalle = "Se asignaron $cantidad unidades del producto ID $material_id al trabajo.";
        $sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                          VALUES (NOW(), 'Creación', 'material_trabajo', ?, ?, ?)";
        $stmt_historial = $conn->prepare($sql_historial);
        $stmt_historial->bind_param("isi", $trabajo_id, $detalle, $usuario_id);
        $stmt_historial->execute();
        $stmt_historial->close();
    }

    $stmt_insert_material->close();
    $stmt_update_producto->close();
}
// ==================== FIN AGREGAR MATERIALES ====================
// Eliminar operaciones anteriores
$sql_eliminar_operaciones = "DELETE FROM operaciones WHERE trabajo_id = ?";
$stmt_eliminar_operaciones = $conn->prepare($sql_eliminar_operaciones);
$stmt_eliminar_operaciones->bind_param("i", $trabajo_id);
$stmt_eliminar_operaciones->execute();
$stmt_eliminar_operaciones->close();

// Insertar operaciones nuevas
if (!empty($_POST['operacion']) && is_array($_POST['operacion'])) {
    $sql_insert_operaciones = "INSERT INTO operaciones (trabajo_id, operacion, pto_trab, desc_pto_trab, desc_oper, n_pers, h_est, hh_tot_prog)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert_operaciones = $conn->prepare($sql_insert_operaciones);

    foreach ($_POST['operacion'] as $index => $operacion) {
        $pto_trab = $_POST['pto_trab'][$index] ?? '';
        $desc_pto_trab = $_POST['desc_pto_trab'][$index] ?? '';
        $desc_oper = $_POST['desc_oper'][$index] ?? '';
        $n_pers = isset($_POST['n_pers'][$index]) ? intval($_POST['n_pers'][$index]) : 0;
        $h_est = isset($_POST['h_est'][$index]) ? floatval($_POST['h_est'][$index]) : 0;
        $hh_tot_prog = isset($_POST['hh_tot_prog'][$index]) ? floatval($_POST['hh_tot_prog'][$index]) : 0;

        $stmt_insert_operaciones->bind_param("issssidd", $trabajo_id, $operacion, $pto_trab, $desc_pto_trab, $desc_oper, $n_pers, $h_est, $hh_tot_prog);
        $stmt_insert_operaciones->execute();
    }

    // Historial de operaciones
    $fecha_actual = date('Y-m-d H:i:s');
    $detalle_eliminacion = "Se eliminaron las operaciones anteriores del trabajo.";
    $sql_historial_eliminacion = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                                  VALUES (?, 'Eliminación', 'operaciones', ?, ?, ?)";
    $stmt_historial_eliminacion = $conn->prepare($sql_historial_eliminacion);
    $stmt_historial_eliminacion->bind_param("sisi", $fecha_actual, $trabajo_id, $detalle_eliminacion, $usuario_id);
    $stmt_historial_eliminacion->execute();
    $stmt_historial_eliminacion->close();

    $sql_historial_insercion = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                                VALUES (?, 'Creación', 'operaciones', ?, ?, ?)";
    $stmt_historial_insercion = $conn->prepare($sql_historial_insercion);
    $detalle_insercion = "Se agregaron nuevas operaciones al trabajo.";
    $stmt_historial_insercion->bind_param("sisi", $fecha_actual, $trabajo_id, $detalle_insercion, $usuario_id);
    $stmt_historial_insercion->execute();
    $stmt_historial_insercion->close();

    $stmt_insert_operaciones->close();
}// ==================== ELIMINAR TRABAJADORES ANTERIORES ====================
$sql_eliminar_trabajadores = "DELETE FROM trabajo_trabajadores WHERE id_trabajo = ?";
$stmt_eliminar_trabajadores = $conn->prepare($sql_eliminar_trabajadores);
if ($stmt_eliminar_trabajadores) {
    $stmt_eliminar_trabajadores->bind_param("i", $trabajo_id);
    $stmt_eliminar_trabajadores->execute();
    $stmt_eliminar_trabajadores->close();
} else {
    error_log("Error al preparar eliminación de trabajadores: " . $conn->error);
}

// ==================== GUARDAR TRABAJADORES ASIGNADOS ====================
if (!empty($_POST['trabajador_id']) && is_array($_POST['trabajador_id'])) {
    $sql_insert_trabajador = "INSERT INTO trabajo_trabajadores (id_trabajo, id_trabajador, horas_trabajadas) 
                              VALUES (?, ?, ?)";
    $stmt_insert_trabajador = $conn->prepare($sql_insert_trabajador);
    if (!$stmt_insert_trabajador) {
        die("Error al preparar statement de trabajadores: " . $conn->error);
    }

    foreach ($_POST['trabajador_id'] as $i => $trabajador_id) {
        $trabajador_id = intval($trabajador_id);
        $horas = isset($_POST['horas_trabajadas'][$i]) ? floatval($_POST['horas_trabajadas'][$i]) : 0;

        if ($trabajador_id > 0 && $horas > 0) {
            $stmt_insert_trabajador->bind_param("iid", $trabajo_id, $trabajador_id, $horas);
            $stmt_insert_trabajador->execute();
        }
    }
    $stmt_insert_trabajador->close();

    // Historial (opcional)
    $detalle = "Se asignaron trabajadores al trabajo.";
    $sql_historial_trab = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                           VALUES (NOW(), 'Creación', 'trabajo_trabajadores', ?, ?, ?)";
    $stmt_historial_trab = $conn->prepare($sql_historial_trab);
    $stmt_historial_trab->bind_param("isi", $trabajo_id, $detalle, $usuario_id);
    $stmt_historial_trab->execute();
    $stmt_historial_trab->close();
}
// ==================== FIN GUARDAR TRABAJADORES ====================


$conn->close();
header("Location: editar_trabajo.php?id=$trabajo_id&guardado=ok");
exit;
