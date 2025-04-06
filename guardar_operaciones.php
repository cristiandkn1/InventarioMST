<?php
include 'db.php';
session_start();

// 🔹 Obtener ID del trabajo desde POST (debe enviarse en el formulario)
$trabajo_id = isset($_POST['id_trabajo']) ? intval($_POST['id_trabajo']) : (isset($_GET['id_trabajo']) ? intval($_GET['id_trabajo']) : 0);
if ($trabajo_id <= 0) {
    die("Error: No se recibió un ID de trabajo válido.");
}

// 🔹 Verificar si hay operaciones enviadas correctamente
if (!empty($_POST['operaciones']) && is_array($_POST['operaciones'])) {
    $sql_operacion = "INSERT INTO operaciones (trabajo_id, operacion, pto_trab, desc_pto_trab, desc_oper, n_pers, h_est, hh_tot_prog) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_operacion = $conn->prepare($sql_operacion);

    foreach ($_POST['operaciones'] as $op) {
        // Verificar si los datos están presentes y no están vacíos
        if (!empty($op['operacion']) && !empty($op['pto_trab'])) {
            $operacion = trim($op['operacion']);
            $pto_trab = trim($op['pto_trab']);
            $desc_pto_trab = trim($op['desc_pto_trab']);
            $desc_oper = trim($op['desc_oper']);
            $n_pers = intval($op['n_pers']);
            $h_est = floatval($op['h_est']);
            $hh_tot_prog = floatval($op['hh_tot_prog']);

            $stmt_operacion->bind_param("issssidd", $trabajo_id, $operacion, $pto_trab, $desc_pto_trab, $desc_oper, $n_pers, $h_est, $hh_tot_prog);
            $stmt_operacion->execute();
        }
    }
    $stmt_operacion->close();

    echo "<script>
        alert('Operaciones guardadas correctamente.');
        window.location.href = 'trabajos.php';
    </script>";
} else {
    echo "<script>
        alert('No se recibieron operaciones válidas.');
        window.location.href = 'trabajos.php';
    </script>";
}

$conn->close();
?>
