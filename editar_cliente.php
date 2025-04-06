<?php
include 'db.php';
session_start(); // ✅ Iniciar sesión

// ✅ Establecer zona horaria (Chile)
date_default_timezone_set('America/Santiago');

// ✅ Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Verificar si el formulario fue enviado (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ✅ Autenticación si el rol no es administrador
    if ($_SESSION['rol'] !== 'Administrador') {
        $clave_admin = $_POST['clave_admin'] ?? '';

        $stmt = $conn->prepare("SELECT password FROM usuario WHERE rol = 'Administrador'");
        $stmt->execute();
        $res = $stmt->get_result();
        $valido = false;

        while ($row = $res->fetch_assoc()) {
            if (password_verify($clave_admin, $row['password'])) {
                $valido = true;
                break;
            }
        }

        if (!$valido) {
            echo "<script>alert('Contrase\u00f1a de administrador inv\u00e1lida.'); history.back();</script>";
            exit();
        }
    }

    $idcliente = isset($_POST['idcliente']) ? (int)$_POST['idcliente'] : 0;
    $nombre    = trim($_POST['nombre'] ?? '');
    $empresa   = trim($_POST['empresa'] ?? '');
    $correo    = trim($_POST['correo'] ?? '');
    $contacto  = trim($_POST['contacto'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $rut = trim($_POST['rut'] ?? '');


    if ($idcliente <= 0) die("Error: ID de cliente no v\u00e1lido.");
    if (empty($nombre) || empty($empresa) || empty($correo)) die("Error: Todos los campos son obligatorios.");

    $sql_old = "SELECT * FROM cliente WHERE idcliente = ?";
    $stmt_old = $conn->prepare($sql_old);
    $stmt_old->bind_param("i", $idcliente);
    $stmt_old->execute();
    $result_old = $stmt_old->get_result();
    $cliente_old = $result_old->fetch_assoc();
    $stmt_old->close();

    if (!$cliente_old) die("Error: Cliente no encontrado.");

    $sql_update = "UPDATE cliente SET nombre=?, empresa=?, rut=?, correo=?, contacto=?, direccion=? WHERE idcliente=?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("ssssssi", $nombre, $empresa, $rut, $correo, $contacto, $direccion, $idcliente);


    if ($stmt_update->execute()) {
        $cambios = [];
        if ($cliente_old['nombre'] !== $nombre) $cambios[] = "Nombre: '{$cliente_old['nombre']}' → '$nombre'";
        if ($cliente_old['empresa'] !== $empresa) $cambios[] = "Empresa: '{$cliente_old['empresa']}' → '$empresa'";
        if ($cliente_old['correo'] !== $correo) $cambios[] = "Correo: '{$cliente_old['correo']}' → '$correo'";
        if ($cliente_old['contacto'] !== $contacto) $cambios[] = "Contacto: '{$cliente_old['contacto']}' → '$contacto'";
        if ($cliente_old['direccion'] !== $direccion) $cambios[] = "Dirección: '{$cliente_old['direccion']}' → '$direccion'";
        if ($cliente_old['rut'] !== $rut) $cambios[] = "RUT: '{$cliente_old['rut']}' → '$rut'";


        if (!empty($cambios)) {
            $usuario_id = $_SESSION['usuario_id'];
            $detalle = "Se actualizaron los siguientes cambios en el cliente '{$cliente_old['nombre']}' (ID: $idcliente): " . implode(", ", $cambios);
            $fecha = date('Y-m-d H:i:s');

            $sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id)
                              VALUES (?, 'Edición Cliente', 'cliente', ?, ?, ?)";
            $stmt_historial = $conn->prepare($sql_historial);
            $stmt_historial->bind_param("sisi", $fecha, $idcliente, $detalle, $usuario_id);
            $stmt_historial->execute();
            $stmt_historial->close();
        }

        echo "<script>
                alert('Cliente actualizado correctamente.');
                window.location.href='clientes.php';
              </script>";
    } else {
        echo "Error al actualizar cliente: " . $conn->error;
    }

    $stmt_update->close();
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM cliente WHERE idcliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) die("Cliente no encontrado.");
    $cliente = $result->fetch_assoc();
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h2 class="text-center">Editar Cliente</h2>
    
    <form method="post" action="editar_cliente.php">
        <input type="hidden" name="idcliente" value="<?php echo $cliente['idcliente']; ?>">
        
        <div class="mb-3">
            <label>Nombre del Cliente:</label>
            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($cliente['nombre'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label>Nombre de la Empresa:</label>
            <input type="text" name="empresa" class="form-control" value="<?php echo htmlspecialchars($cliente['empresa'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label>RUT:</label>
            <input type="text" name="rut" class="form-control" value="<?php echo htmlspecialchars($cliente['rut'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label>Correo:</label>
            <input type="email" name="correo" class="form-control" value="<?php echo htmlspecialchars($cliente['correo'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label>Contacto:</label>
            <input type="text" name="contacto" class="form-control" value="<?php echo htmlspecialchars($cliente['contacto'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label>Dirección:</label>
            <input type="text" name="direccion" class="form-control" value="<?php echo htmlspecialchars($cliente['direccion'] ?? ''); ?>">
        </div>

        <div class="d-flex justify-content-between">
            <a href="borrarCliente.php?id=<?php echo $cliente['idcliente']; ?>" 
               class="btn btn-danger" 
               onclick="return confirm('¿Está seguro de eliminar este cliente? Esta acción no se puede deshacer.');">
               Borrar Cliente
            </a>
            <button type="submit" name="guardar" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </form>
</body>
</html>
