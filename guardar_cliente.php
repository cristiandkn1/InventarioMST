<?php
include 'db.php';
session_start();
date_default_timezone_set('America/Santiago');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $rut = isset($_POST['rut']) ? trim($_POST['rut']) : NULL;
    $empresa = isset($_POST['empresa']) ? trim($_POST['empresa']) : NULL;
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : NULL;
    $contacto = isset($_POST['contacto']) ? trim($_POST['contacto']) : NULL;
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : NULL;

    if (empty($nombre)) {
        echo "<script>
                alert('El nombre del cliente es obligatorio.');
                window.location.href='clientes.php';
              </script>";
        exit();
    }

    if (!isset($_SESSION['usuario_id'])) {
        echo "<script>
                alert('Debes iniciar sesión para agregar clientes.');
                window.location.href='login.php';
              </script>";
        exit();
    }

    $usuario_id = $_SESSION['usuario_id'];

    $sql = "INSERT INTO cliente (nombre, rut, empresa, correo, contacto, direccion) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("ssssss", $nombre, $rut, $empresa, $correo, $contacto, $direccion);

    if ($stmt->execute()) {
        $cliente_id = $stmt->insert_id;

        $fecha_hora = date("Y-m-d H:i:s");
        $accion = "Creación de Cliente";
        $detalle = "Se creó el cliente \"$nombre\" (ID: $cliente_id), Empresa: \"$empresa\".";

        $sql_historial = "INSERT INTO historial (fecha, accion, entidad, entidad_id, detalle, usuario_id) 
                          VALUES (?, ?, 'cliente', ?, ?, ?)";
        $stmt_historial = $conn->prepare($sql_historial);

        if (!$stmt_historial) {
            die("Error en la preparación de la consulta de historial: " . $conn->error);
        }

        $stmt_historial->bind_param("ssisi", $fecha_hora, $accion, $cliente_id, $detalle, $usuario_id);
        $stmt_historial->execute();
        $stmt_historial->close();

        echo "<script>
                alert('Cliente agregado correctamente y registrado en el historial.');
                window.location.href='clientes.php';
              </script>";
    } else {
        echo "Error al guardar cliente: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
