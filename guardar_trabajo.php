<?php
include 'db.php'; // Conectar a la BD
session_start();
date_default_timezone_set('America/Santiago');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ✅ Capturar datos del formulario
    $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';
    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
    $equipo = isset($_POST['equipo']) ? trim($_POST['equipo']) : '';
    $revision = isset($_POST['revision']) ? trim($_POST['revision']) : ''; 
    $fecha_creacion = date("Y-m-d");
    $descripcion_orden = isset($_POST['descripcion_orden']) ? trim($_POST['descripcion_orden']) : null;
    $clase_orden = isset($_POST['clase_orden']) ? trim($_POST['clase_orden']) : null;
    $nro_aviso = isset($_POST['nro_aviso']) ? trim($_POST['nro_aviso']) : null;
    $clase_actividad_pl = isset($_POST['clase_actividad_pl']) ? trim($_POST['clase_actividad_pl']) : null;
    $ubicacion_tecnica = isset($_POST['ubicacion_tecnica']) ? trim($_POST['ubicacion_tecnica']) : null;
    $prioridad = isset($_POST['prioridad']) ? trim($_POST['prioridad']) : null;
    $den_equipo = isset($_POST['den_equipo']) ? trim($_POST['den_equipo']) : null;
    $grp_planificacion = isset($_POST['grp_planificacion']) ? trim($_POST['grp_planificacion']) : null;
    $pto_trab_responsable = isset($_POST['pto_trab_responsable']) ? trim($_POST['pto_trab_responsable']) : null;
    $fecha_ini_prog = isset($_POST['fecha_ini_prog']) && !empty($_POST['fecha_ini_prog']) ? $_POST['fecha_ini_prog'] : null;
    $hora_inicio = isset($_POST['hora_inicio']) && !empty($_POST['hora_inicio']) ? $_POST['hora_inicio'] : null;
    $hora_fin = isset($_POST['hora_fin']) && !empty($_POST['hora_fin']) ? $_POST['hora_fin'] : null;
    $duracion_dias = isset($_POST['duracion_dias']) && !empty($_POST['duracion_dias']) ? intval($_POST['duracion_dias']) : null;
    $sol_ped = isset($_POST['sol_ped']) ? trim($_POST['sol_ped']) : null;
    $descripcion_detallada = isset($_POST['descripcion_detallada']) ? trim($_POST['descripcion_detallada']) : null;
    $reserva = isset($_POST['reserva']) && !empty(trim($_POST['reserva'])) ? trim($_POST['reserva']) : "N/A";
    $fecha_inicio = isset($_POST['fecha_inicio']) && !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null;
    $comentarios = isset($_POST['comentarios']) ? trim($_POST['comentarios']) : null;







    // ✅ Capturar fecha de entrega (puede ser NULL)
    $fecha_entrega = !empty($_POST['fecha_entrega']) ? date("Y-m-d", strtotime($_POST['fecha_entrega'])) : NULL;

    // ✅ Verificar campos obligatorios
    if (empty($tipo) || empty($titulo) || $cliente_id <= 0 || empty($equipo) || empty($revision)) {
        echo "<script>
                alert('El tipo, título, cliente, equipo y revisión son obligatorios.');
                window.location.href='trabajos.php';
              </script>";
        exit();
    }

    // ✅ Obtener el último número de orden registrado
    $sql_last = "SELECT nro_orden FROM trabajo ORDER BY idtrabajo DESC LIMIT 1";
    $result_last = $conn->query($sql_last);
    
    if ($result_last && $result_last->num_rows > 0) {
        $row = $result_last->fetch_assoc();
        $ultimo_nro = intval($row['nro_orden']) + 1; // Incrementar en 1
        $nro_orden = str_pad($ultimo_nro, 7, "0", STR_PAD_LEFT); // Formato 0000001, 0000002...
    } else {
        $nro_orden = "0000001"; // Si no hay registros, iniciamos en 0000001
    }

    // ✅ Insertar en la base de datos
    $sql = "INSERT INTO trabajo (
        tipo, titulo, cliente_id, equipo, den_equipo, revision, grp_planificacion, 
        pto_trab_responsable, fecha_creacion, nro_orden, fecha_entrega, 
        descripcion_orden, clase_orden, nro_aviso, clase_actividad_pl, 
        ubicacion_tecnica, prioridad, fecha_ini_prog, hora_inicio, hora_fin, 
        duracion_dias, sol_ped, descripcion_detallada, reserva, fecha_inicio, comentarios) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    // ✅ Vincular parámetros (fecha_entrega puede ser NULL)
    $stmt->bind_param("ssisssssssssssssssssisssss", 
    $tipo, $titulo, $cliente_id, $equipo, $den_equipo, $revision, $grp_planificacion, 
    $pto_trab_responsable, $fecha_creacion, $nro_orden, $fecha_entrega, 
    $descripcion_orden, $clase_orden, $nro_aviso, $clase_actividad_pl, 
    $ubicacion_tecnica, $prioridad, $fecha_ini_prog, $hora_inicio, $hora_fin, 
    $duracion_dias, $sol_ped, $descripcion_detallada, $reserva, $fecha_inicio, $comentarios);


// ✅ Ejecutar la consulta principal de trabajo
if ($stmt->execute()) {
    // ✅ Obtener el ID del trabajo recién insertado
    $trabajo_id = $stmt->insert_id;

    // ✅ Guardar operaciones si se han enviado
    if (!empty($_POST['operacion']) && is_array($_POST['operacion'])) {
        $sql_operacion = "INSERT INTO operaciones (trabajo_id, operacion, pto_trab, desc_pto_trab, desc_oper, n_pers, h_est, hh_tot_prog) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_operacion = $conn->prepare($sql_operacion);

        foreach ($_POST['operacion'] as $index => $operacion) {
            $pto_trab = $_POST['pto_trab'][$index] ?? '';
            $desc_pto_trab = $_POST['desc_pto_trab'][$index] ?? '';
            $desc_oper = $_POST['desc_oper'][$index] ?? '';
            $n_pers = isset($_POST['n_pers'][$index]) ? intval($_POST['n_pers'][$index]) : 0;
            $h_est = isset($_POST['h_est'][$index]) ? floatval($_POST['h_est'][$index]) : 0;
            $hh_tot_prog = isset($_POST['hh_tot_prog'][$index]) ? floatval($_POST['hh_tot_prog'][$index]) : 0;

            $stmt_operacion->bind_param("issssidd", $trabajo_id, $operacion, $pto_trab, $desc_pto_trab, $desc_oper, $n_pers, $h_est, $hh_tot_prog);
            $stmt_operacion->execute();
        }
        $stmt_operacion->close();
    }


// ✅ Verificar conexión a la base de datos
if ($conn->connect_error) {
    die("❌ Conexión fallida: " . $conn->connect_error);
}

// ✅ Verificar que se recibe un ID de trabajo válido
if (empty($trabajo_id)) {
    die("❌ Error: No se recibió un ID de trabajo válido.");
}


// ✅ Guardar productos (materiales) si se han enviado
if (!empty($_POST['material_id']) && is_array($_POST['material_id'])) {
    $sql_producto = "INSERT INTO trabajo_producto (trabajo_id, producto_id, descripcion, cantidad, numero_parte) 
                     VALUES (?, ?, ?, ?, ?)";
    $stmt_producto = $conn->prepare($sql_producto);

    foreach ($_POST['material_id'] as $index => $id_producto) {
        $descripcion = $_POST['descripcion_material'][$index] ?? 'Sin descripción';
        $cantidad = isset($_POST['cantidad_material'][$index]) ? intval($_POST['cantidad_material'][$index]) : 1;
        $numero_parte = $_POST['numero_parte'][$index] ?? 'N/A';


        // ✅ Verificar cantidad disponible del producto
        $sql_verificar = "SELECT cantidad, en_uso FROM producto WHERE idproducto = ?";
        $stmt_verificar = $conn->prepare($sql_verificar);
        $stmt_verificar->bind_param("i", $id_producto);
        $stmt_verificar->execute();
        $result = $stmt_verificar->get_result();
        $producto = $result->fetch_assoc();
        $stmt_verificar->close();

        if ($producto) {
            $cantidad_total = intval($producto['cantidad']);
            $en_uso = intval($producto['en_uso']);
            $disponible = $cantidad_total - $en_uso;

            if ($cantidad > $disponible) {
                echo "<script>
                        alert('❌ Error: No hay suficiente stock disponible para el producto ID $id_producto.');
                        window.location.href = 'trabajos.php';
                      </script>";
                exit();
            }

            // ✅ Insertar en trabajo_producto
            $stmt_producto->bind_param("iisii", $trabajo_id, $id_producto, $descripcion, $cantidad, $numero_parte);
            if (!$stmt_producto->execute()) {
                die("❌ Error en la consulta INSERT: " . $stmt_producto->error);
            }

            // ✅ Actualizar el campo 'en_uso' en la tabla producto
            $sql_update_producto = "UPDATE producto SET en_uso = en_uso + ? WHERE idproducto = ?";
            $stmt_update_producto = $conn->prepare($sql_update_producto);
            $stmt_update_producto->bind_param("ii", $cantidad, $id_producto);
            $stmt_update_producto->execute();
            $stmt_update_producto->close();
        }
    }
    $stmt_producto->close();
}

echo "<script>alert('✅ Productos añadidos correctamente.'); window.location.href = 'trabajos.php';</script>";





    // ✅ Guardar trabajadores asignados si se han enviado
if (!empty($_POST['trabajador_id']) && is_array($_POST['trabajador_id'])) {
    $sql_trabajador = "INSERT INTO trabajo_trabajadores (id_trabajo, id_trabajador, horas_trabajadas) 
                       VALUES (?, ?, ?)";
    $stmt_trabajador = $conn->prepare($sql_trabajador);

    // 🔹 Verificar si la consulta fue preparada correctamente
    if (!$stmt_trabajador) {
        die("Error en la preparación de la consulta de trabajadores: " . $conn->error);
    }

    foreach ($_POST['trabajador_id'] as $index => $trabajador_id) {
        $horas_trabajadas = isset($_POST['horas_trabajadas'][$index]) ? floatval($_POST['horas_trabajadas'][$index]) : 0;

        $stmt_trabajador->bind_param("iid", $trabajo_id, $trabajador_id, $horas_trabajadas);
        $stmt_trabajador->execute();
    }
    $stmt_trabajador->close();
}

    // ✅ Redirigir con mensaje de éxito
    echo "<script>
            alert('Trabajo agregado correctamente con número de orden: $nro_orden');
            window.location.href='trabajos.php';
          </script>";
} else {
    echo "Error al guardar el trabajo: " . $conn->error;
}






// ✅ Guardar en el historial la creación del trabajo
$sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id) 
                  VALUES (NOW(), ?, ?, ?, ?, ?)";
$stmt_historial = $conn->prepare($sql_historial);

if (!$stmt_historial) {
    die("Error en la preparación de la consulta del historial: " . $conn->error);
}

// 🔹 Definir los valores a insertar en el historial
$accion = "Creación";
$entidad = "trabajo";
$detalle = "Se creó el trabajo con número de orden: $nro_orden";
$usuario_id = $_SESSION['usuario_id'] ?? 0; // Obtiene el ID del usuario si está en sesión

$stmt_historial->bind_param("ssisi", $accion, $entidad, $trabajo_id, $detalle, $usuario_id);
$stmt_historial->execute();
$stmt_historial->close();

// ✅ Redirigir con mensaje de éxito
echo "<script>
        alert('Trabajo agregado correctamente con número de orden: $nro_orden');
        window.location.href='trabajos.php';
      </script>";
} else {
    echo "Error al guardar el trabajo: " . $conn->error;
}











$stmt->close();
$conn->close();

?>
