<?php
include 'db.php'; // ✅ Conectar a la BD
session_start(); // ✅ Iniciar sesión

date_default_timezone_set('America/Santiago');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ✅ Capturar datos del formulario
    $nombre        = trim($_POST['nombre']);
    $rut           = trim($_POST['rut']); // ✅ Nuevo campo
    $cargo         = trim($_POST['cargo']);
    $telefono      = trim($_POST['telefono'] ?? '');
    $correo        = trim($_POST['correo']);
    $fecha_ingreso = trim($_POST['fecha_ingreso']);
    $estado        = trim($_POST['estado']);

    // ✅ Validación de sesión
    if (!isset($_SESSION['usuario_id'])) {
        echo "<script>
                alert('Debes iniciar sesión para agregar empleados.');
                window.location.href='login.php';
              </script>";
        exit();
    }

    $usuario_id = $_SESSION['usuario_id'];
    $fecha_creacion = date("Y-m-d H:i:s");

    // ✅ Validaciones
    if (empty($nombre) || empty($rut) || empty($cargo) || empty($correo) || empty($fecha_ingreso)) {
        echo "<script>
                alert('Todos los campos obligatorios deben completarse.');
                window.location.href='empleados.php';
              </script>";
        exit();
    }

    // ✅ Insertar en tabla empleado
    $sql = "INSERT INTO empleado (nombre, rut, cargo, telefono, correo, fecha_ingreso, estado, fecha_creacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Error en la preparación: " . $conn->error);
    }

    $stmt->bind_param("ssssssss", $nombre, $rut, $cargo, $telefono, $correo, $fecha_ingreso, $estado, $fecha_creacion);

    if ($stmt->execute()) {
        $empleado_id = $stmt->insert_id;

        // ✅ Registrar historial
        $accion = "Creación de Empleado";
        $detalle = "Se agregó el empleado '$nombre' con RUT $rut, ID $empleado_id, Cargo: '$cargo'.";
        $sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                          VALUES (?, ?, 'empleado', ?, ?, ?)";
        $stmt_historial = $conn->prepare($sql_historial);
        $stmt_historial->bind_param("ssisi", $fecha_creacion, $accion, $empleado_id, $detalle, $usuario_id);
        $stmt_historial->execute();
        $stmt_historial->close();

        echo "<script>
                alert('Empleado agregado correctamente.');
                window.location.href='empleados.php';
              </script>";
    } else {
        echo "Error al agregar empleado: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
