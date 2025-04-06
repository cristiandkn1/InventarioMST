<?php
include 'db.php';
session_start(); // ✅ Iniciar sesión

// ✅ Establecer zona horaria
date_default_timezone_set('America/Santiago');

// ✅ Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Obtener ID del empleado a editar
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("ID de empleado no válido.");
}

// ✅ Obtener datos del empleado
$sql = "SELECT * FROM empleado WHERE idempleado = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Empleado no encontrado.");
}

$empleado = $result->fetch_assoc();
$stmt->close();

// ✅ Si se envió el formulario para guardar cambios
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ✅ Capturar y validar datos del formulario
    $rut           = trim($_POST['rut']); // <-- Nuevo campo

    $nombre        = trim($_POST['nombre']);
    $cargo         = trim($_POST['cargo']);
    $telefono      = trim($_POST['telefono']);
    $correo        = trim($_POST['correo']);
    $fecha_ingreso = trim($_POST['fecha_ingreso']);
    $estado        = trim($_POST['estado']);
    $errores = [];

    if (empty($rut) || empty($nombre) || empty($cargo) || empty($correo) || empty($fecha_ingreso)) {
        $errores[] = "Todos los campos obligatorios deben completarse.";
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo no tiene un formato válido.";
    }

    if (!empty($errores)) {
        foreach ($errores as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
    } else {
        // ✅ Obtener valores antiguos antes de actualizar
        $sql_old = "SELECT * FROM empleado WHERE idempleado = ?";
        $stmt_old = $conn->prepare($sql_old);
        $stmt_old->bind_param("i", $id);
        $stmt_old->execute();
        $result_old = $stmt_old->get_result();
        $empleado_old = $result_old->fetch_assoc();
        $stmt_old->close();

        // ✅ Ejecutar UPDATE
        $sql_update = "UPDATE empleado 
               SET rut=?, nombre=?, cargo=?, telefono=?, correo=?, fecha_ingreso=?, estado=? 
               WHERE idempleado=?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("sssssssi", $rut, $nombre, $cargo, $telefono, $correo, $fecha_ingreso, $estado, $id);

        if ($stmt_update->execute()) {
            // ✅ Comparar cambios y registrar en historial
            $cambios = [];
            if ($empleado_old['nombre'] !== $nombre) $cambios[] = "Nombre: '{$empleado_old['nombre']}' → '$nombre'";
            if ($empleado_old['cargo'] !== $cargo) $cambios[] = "Cargo: '{$empleado_old['cargo']}' → '$cargo'";
            if ($empleado_old['telefono'] !== $telefono) $cambios[] = "Teléfono: '{$empleado_old['telefono']}' → '$telefono'";
            if ($empleado_old['correo'] !== $correo) $cambios[] = "Correo: '{$empleado_old['correo']}' → '$correo'";
            if ($empleado_old['fecha_ingreso'] !== $fecha_ingreso) $cambios[] = "Fecha de Ingreso: '{$empleado_old['fecha_ingreso']}' → '$fecha_ingreso'";
            if ($empleado_old['estado'] !== $estado) $cambios[] = "Estado: '{$empleado_old['estado']}' → '$estado'";
            if ($empleado_old['rut'] !== $rut) $cambios[] = "RUT: '{$empleado_old['rut']}' → '$rut'";

            if (!empty($cambios)) {
                // ✅ Registrar en historial
                $usuario_id = $_SESSION['usuario_id'];
                $detalle = "Se actualizaron los siguientes cambios en el empleado ID $id: " . implode(", ", $cambios);
                $fecha_hora = date('Y-m-d H:i:s');

                $sql_hist = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                             VALUES (?, 'Actualización Empleado', 'empleado', ?, ?, ?)";
                $stmt_hist = $conn->prepare($sql_hist);
                $stmt_hist->bind_param("sisi", $fecha_hora, $id, $detalle, $usuario_id);
                $stmt_hist->execute();
                $stmt_hist->close();
            }

            // ✅ Redirección después de actualizar
            echo "<script>
                    alert('Empleado actualizado correctamente.');
                    window.location.href='empleados.php';
                  </script>";
            exit();
        } else {
            echo "<p style='color: red;'>Error al actualizar: " . $conn->error . "</p>";
        }

        $stmt_update->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h2 class="text-center">Editar Empleado</h2>
    
    <form method="post">
        <div class="mb-3">
            <label>Nombre:</label>
            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($empleado['nombre'] ?? ''); ?>" required>
        </div>
        
        <div class="mb-3">
            <label>RUT:</label>
            <input type="text" name="rut" class="form-control" value="<?php echo htmlspecialchars($empleado['rut'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label>Cargo:</label>
            <input type="text" name="cargo" class="form-control" value="<?php echo htmlspecialchars($empleado['cargo'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label>Teléfono:</label>
            <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($empleado['telefono'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label>Correo:</label>
            <input type="email" name="correo" class="form-control" value="<?php echo htmlspecialchars($empleado['correo'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label>Fecha de Ingreso:</label>
            <input type="date" name="fecha_ingreso" class="form-control" value="<?php echo htmlspecialchars($empleado['fecha_ingreso'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label>Estado:</label>
            <select name="estado" class="form-control">
                <option value="Activo" <?php echo ($empleado['estado'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                <option value="Inactivo" <?php echo ($empleado['estado'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" name="guardar" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </form>
</body>
</html>
