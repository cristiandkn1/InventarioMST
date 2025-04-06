<?php
include 'db.php'; // Conexión a la base de datos
session_start(); // Iniciar sesión para obtener el usuario logueado
date_default_timezone_set('America/Santiago');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar los datos del formulario
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $estado = $_POST['estado'];
    $categoria_id = $_POST['categoria_id'];
    $descripcion = $_POST['descripcion'];
    $cantidad = $_POST['cantidad'];
    $nro_asignacion = $_POST['nro_asignacion'];

    // ✅ Verificar que el usuario esté logueado
    if (!isset($_SESSION['usuario_id'])) {
        echo "<script>
                alert('Debes iniciar sesión para agregar productos.');
                window.location.href='login.php';
              </script>";
        exit();
    }

    $usuario_id = $_SESSION['usuario_id'];

    // ✅ Validaciones básicas
    if (empty($nombre) || empty($precio) || empty($categoria_id) || empty($cantidad)) {
        echo "<script>
                alert('Por favor, completa todos los campos obligatorios.');
                window.location.href='index.php';
              </script>";
        exit();
    }

    // ✅ Evitar inyecciones SQL y limpiar datos
    $nombre = $conn->real_escape_string($nombre);
    $precio = floatval($precio);
    $estado = $conn->real_escape_string($estado);
    $categoria_id = intval($categoria_id);
    $descripcion = $conn->real_escape_string($descripcion);
    $cantidad = intval($cantidad);
    $nro_asignacion = !empty($nro_asignacion) ? $conn->real_escape_string($nro_asignacion) : NULL;
    
    // ✅ "disponibles" es igual a la cantidad al momento de la creación
    $en_uso = 0; // Inicialmente en uso es 0
    $disponibles = $cantidad;

    // ✅ Insertar datos en la tabla `producto`
    $sql = "INSERT INTO producto (nombre, precio, estado, categoria_id, descripcion, cantidad, en_uso, disponibles, nro_asignacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdsissiii", $nombre, $precio, $estado, $categoria_id, $descripcion, $cantidad, $en_uso, $disponibles, $nro_asignacion);

    if ($stmt->execute()) {
        $producto_id = $stmt->insert_id; // Obtener el ID del producto insertado

        // ✅ Guardar en historial
        $accion = "Creación de Producto";
        $detalle = "Se creó el producto '$nombre ', Cantidad Creada $cantidad.";
        $fecha_hora = date("Y-m-d H:i:s");

        // ✅ Insertar en `historial`
        $sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                          VALUES (?, ?, 'producto', ?, ?, ?)";
        $stmt_historial = $conn->prepare($sql_historial);
        $stmt_historial->bind_param("ssisi", $fecha_hora, $accion, $producto_id, $detalle, $usuario_id);

        if ($stmt_historial->execute()) {
            echo "<script>
                    alert('Producto agregado correctamente y registrado en el historial');
                    window.location.href='index.php';
                  </script>";
        } else {
            echo "Error al registrar en historial: " . $conn->error;
        }

        $stmt_historial->close();
    } else {
        echo "Error al guardar producto: " . $conn->error;
    }

    // ✅ Cerrar conexiones
    $stmt->close();
    $conn->close();
}
?>
