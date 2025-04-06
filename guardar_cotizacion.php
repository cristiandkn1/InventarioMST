<?php
session_start();
include 'db.php';
date_default_timezone_set('America/Santiago');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ✅ Capturar datos del formulario
    $trabajo_id = intval($_POST['trabajo_id']);
    $fecha_cotizacion = $_POST['fecha_cotizacion'];
    $numero_cotizacion = $_POST['numero_cotizacion'];
    $estado = $_POST['estado'];
    $duracion_dias = intval($_POST['duracion_dias']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $descripcion = $conn->real_escape_string($_POST['descripcion']);
    
    // ✅ Capturar el total estimado desde el input oculto del formulario
    $total_estimado = isset($_POST['total_estimado_input']) ? floatval($_POST['total_estimado_input']) : 0;

    // ✅ Obtener el nombre del cliente desde la tabla `clientes`
    $sql_cliente = "SELECT clientes.nombre FROM clientes 
                    INNER JOIN trabajos ON clientes.id = trabajos.cliente_id 
                    WHERE trabajos.id = ?";
    $stmt_cliente = $conn->prepare($sql_cliente);
    $stmt_cliente->bind_param("i", $trabajo_id);
    $stmt_cliente->execute();
    $stmt_cliente->bind_result($cliente_nombre);
    $stmt_cliente->fetch();
    $stmt_cliente->close();

    if (!$cliente_nombre) {
        die("Error: No se encontró un cliente asociado a este trabajo.");
    }

    // ✅ Insertar cotización en la tabla `cotizaciones`
    $sql_cotizacion = "INSERT INTO cotizaciones (trabajo_id, proveedor, total, estado, fecha_creacion, numero_cotizacion, fecha_cotizacion, duracion_dias, fecha_inicio, descripcion, total_estimado)
                       VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)";

    $stmt_cot = $conn->prepare($sql_cotizacion);
    if (!$stmt_cot) {
        die("Error en la consulta SQL (cotizaciones): " . $conn->error);
    }

    $proveedor = $cliente_nombre; // ✅ Guardamos el nombre del cliente como proveedor
    $total = 0; // Se actualizará más adelante

    $stmt_cot->bind_param("ississsdsd", $trabajo_id, $proveedor, $total, $estado, $numero_cotizacion, $fecha_cotizacion, $duracion_dias, $fecha_inicio, $descripcion, $total_estimado);
    $stmt_cot->execute();
    $cotizacion_id = $stmt_cot->insert_id;
    $stmt_cot->close();

    // ✅ Inicializar total general (en caso de que el formulario no envíe el valor estimado correctamente)
    $total_general = ($total_estimado > 0) ? $total_estimado : 0;

    // ✅ Guardar productos/servicios
    if (!empty($_POST['producto_nombre']) && !empty($_POST['producto_descripcion']) && !empty($_POST['cantidades']) && !empty($_POST['precios'])) {
        $productos = $_POST['producto_nombre'];
        $descripciones = $_POST['producto_descripcion'];
        $cantidades = $_POST['cantidades'];
        $precios = $_POST['precios'];

        $sql_prod = "INSERT INTO cotizacion_productos (cotizacion_id, trabajo_id, nombre_producto, descripcion, cantidad, precio, total)
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_prod = $conn->prepare($sql_prod);
        
        if (!$stmt_prod) {
            die("Error en consulta productos: " . $conn->error);
        }

        for ($i = 0; $i < count($productos); $i++) {
            $nombre = $productos[$i];
            $desc = $descripciones[$i];
            $cantidad = floatval($cantidades[$i]);
            $precio = floatval($precios[$i]);
            $total_producto = $cantidad * $precio;

            $total_general += $total_producto; // ✅ Sumamos al total estimado

            $stmt_prod->bind_param("iissidd", $cotizacion_id, $trabajo_id, $nombre, $desc, $cantidad, $precio, $total_producto);
            $stmt_prod->execute();
        }

        $stmt_prod->close();
    }

    // ✅ Guardar trabajadores asignados
    if (!empty($_POST['trabajadores']) && !empty($_POST['roles']) && !empty($_POST['costo_trabajador'])) {
        $trabajadores = $_POST['trabajadores'];
        $roles = $_POST['roles'];
        $costos_trabajadores = $_POST['costo_trabajador'];

        $sql_trab = "INSERT INTO cotizacion_trabajadores (cotizacion_id, trabajador_id, rol, costo, trabajo_id)
                     VALUES (?, ?, ?, ?, ?)";
        $stmt_trab = $conn->prepare($sql_trab);

        if (!$stmt_trab) {
            die("Error en consulta trabajadores: " . $conn->error);
        }

        for ($i = 0; $i < count($trabajadores); $i++) {
            $trabajador_id = intval($trabajadores[$i]);
            $rol = $roles[$i];
            $costo = floatval($costos_trabajadores[$i]);

            $total_general += $costo; // ✅ Sumamos al total estimado

            $stmt_trab->bind_param("iisdi", $cotizacion_id, $trabajador_id, $rol, $costo, $trabajo_id);
            $stmt_trab->execute();
        }

        $stmt_trab->close();
    }

    // ✅ Guardar costos adicionales
    if (!empty($_POST['descripcion_costo']) && !empty($_POST['monto_costo'])) {
        $descripcion_costos = $_POST['descripcion_costo'];
        $monto_costos = $_POST['monto_costo'];

        $sql_costo = "INSERT INTO cotizacion_costos_adicionales (cotizacion_id, descripcion, monto, trabajo_id)
                      VALUES (?, ?, ?, ?)";
        $stmt_costo = $conn->prepare($sql_costo);

        if (!$stmt_costo) {
            die("Error en consulta costos adicionales: " . $conn->error);
        }

        for ($i = 0; $i < count($descripcion_costos); $i++) {
            $desc_costo = $descripcion_costos[$i];
            $monto = floatval($monto_costos[$i]);

            $total_general += $monto; // ✅ Sumamos al total estimado

            $stmt_costo->bind_param("isdi", $cotizacion_id, $desc_costo, $monto, $trabajo_id);
            $stmt_costo->execute();
        }

        $stmt_costo->close();
    }

    // ✅ Actualizar total general en la tabla cotizaciones
    $sql_update_total = "UPDATE cotizaciones SET total = ?, total_estimado = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update_total);

    if (!$stmt_update) {
        die("Error en consulta actualización total: " . $conn->error);
    }

    $stmt_update->bind_param("ddi", $total_general, $total_general, $cotizacion_id);
    $stmt_update->execute();
    $stmt_update->close();

    // ✅ Redirigir tras guardar correctamente
    echo "<script>
            alert('Cotización guardada correctamente.');
            window.location.href='trabajos.php';
          </script>";

    $conn->close();
}
?>
