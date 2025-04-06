<?php
require_once(__DIR__ . '/TCPDF-main/tcpdf.php');
include 'db.php';
session_start();

// Verificar si hay un ID de trabajo
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de trabajo no especificado.");
}

$trabajo_id = intval($_GET['id']);

// Obtener datos del trabajo
$sql_trabajo = "SELECT t.*, c.empresa 
                FROM trabajo t 
                LEFT JOIN cliente c ON t.cliente_id = c.idcliente 
                WHERE t.idtrabajo = ?";
$stmt = $conn->prepare($sql_trabajo);
$stmt->bind_param("i", $trabajo_id);
$stmt->execute();
$result = $stmt->get_result();
$trabajo = $result->fetch_assoc();

if (!$trabajo) {
    die("Trabajo no encontrado.");
}

// 📌 Consulta SQL para obtener las operaciones del trabajo
$sql_operaciones = "SELECT * FROM operaciones WHERE trabajo_id = ?";
$stmt_operaciones = $conn->prepare($sql_operaciones);
$stmt_operaciones->bind_param("i", $trabajo_id);
$stmt_operaciones->execute();
$operaciones = $stmt_operaciones->get_result();

// 📌 **Consulta SQL para obtener los materiales/productos del trabajo**
$sql_materiales = "SELECT tp.*, p.nombre 
                   FROM trabajo_producto tp
                   LEFT JOIN producto p ON tp.producto_id = p.idproducto
                   WHERE tp.trabajo_id = ?";
$stmt_materiales = $conn->prepare($sql_materiales);
$stmt_materiales->bind_param("i", $trabajo_id);
$stmt_materiales->execute();
$materiales = $stmt_materiales->get_result();

// 📌 **Consulta SQL para obtener los trabajadores asignados al trabajo**
$sql_trabajadores = "SELECT tt.*, e.nombre AS trabajador 
                     FROM trabajo_trabajadores tt
                     LEFT JOIN empleado e ON tt.id_trabajador = e.idempleado
                     WHERE tt.id_trabajo = ?";
$stmt_trabajadores = $conn->prepare($sql_trabajadores);
$stmt_trabajadores->bind_param("i", $trabajo_id);
$stmt_trabajadores->execute();
$trabajadores = $stmt_trabajadores->get_result();










// Crear nuevo PDF
$pdf = new TCPDF();
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->AddPage();

// **Título del documento**
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, "Detalles del Trabajo", 0, 1, 'C');

// Espaciado
$pdf->Ln(5);

// **TABLA - INFORMACIÓN GENERAL**
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, "Información General", 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);















$tbl = <<<EOD
<table border="1" cellpadding="5">
    <tr>
    <td><strong>Tipo de Trabajo:</strong> {$trabajo['tipo']}</td>
    <td><strong>Título:</strong> {$trabajo['titulo']}</td>
    <td><strong>Empresa:</strong> {$trabajo['empresa']}</td>
    <td><strong>N° Orden:</strong> {$trabajo['nro_orden']}</td>
</tr>
<tr>
    <td><strong>Fecha de Creación:</strong> {$trabajo['fecha_creacion']}</td>
    <td><strong>Fecha de Entrega:</strong> {$trabajo['fecha_entrega']}</td>
    <td><strong>Estado:</strong> {$trabajo['estado']}</td>
    <td><strong>Clase de Orden:</strong> {$trabajo['clase_orden']}</td>
</tr>
<tr>
    <td><strong>Clase de Actividad PL:</strong> {$trabajo['clase_actividad_pl']}</td>
    <td><strong>Prioridad:</strong> {$trabajo['prioridad']}</td>
    <td><strong>Revisión:</strong> {$trabajo['revision']}</td>
    <td><strong>Grupo de Planificación:</strong> {$trabajo['grp_planificacion']}</td>
</tr>
<tr>
    <td><strong>Punto de Trabajo Responsable:</strong> {$trabajo['pto_trab_responsable']}</td>
    <td><strong>Ubicación Técnica:</strong> {$trabajo['ubicacion_tecnica']}</td>
    <td><strong>Equipo:</strong> {$trabajo['equipo']}</td>
    <td><strong>Denominación del Equipo:</strong> {$trabajo['den_equipo']}</td>
</tr>
<tr>
    <td><strong>Reserva:</strong> {$trabajo['reserva']}</td>
    <td><strong>Solicitud de Pedido:</strong> {$trabajo['sol_ped']}</td>
    <td colspan="2"></td>
</tr>
<tr>
    <td><strong>Fecha de Creación:</strong> {$trabajo['fecha_creacion']}</td>
    <td><strong>Fecha de Entrega:</strong> {$trabajo['fecha_entrega']}</td>
    <td><strong>Fecha de Inicio:</strong> {$trabajo['fecha_inicio']}</td>
    <td><strong>Duración Estimada (Días):</strong> {$trabajo['duracion_dias']}</td>
</tr>
<tr>
    <td><strong>Hora de Inicio:</strong> {$trabajo['hora_inicio']}</td>
    <td><strong>Hora de Fin:</strong> {$trabajo['hora_fin']}</td>
    <td colspan="2"></td>
</tr>



</table>
EOD;

$pdf->writeHTML($tbl, true, false, false, false, '');



// Espaciado
$pdf->Ln(5);

// 🔹 **Nueva Sección: Descripción de la Orden**
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, "Descripción de la Orden", 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);

// 📌 Se agregó el campo "Descripción de la Orden" aquí
$pdf->MultiCell(0, 10, $trabajo['descripcion_orden'], 1, 'L');

// Espaciado
$pdf->Ln(5);

// 🔹 **Nueva Sección: Estado del Trabajo**
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, "Estado del Trabajo", 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);

// 📌 Se agregó el campo "Estado" aquí
$pdf->MultiCell(0, 10, $trabajo['estado'], 1, 'L');

// Espaciado
$pdf->Ln(5);

// **TABLA - DESCRIPCIÓN DETALLADA**
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, "Descripción Detallada", 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);

// 📌 Se mantuvo la sección de "Descripción Detallada"
$pdf->MultiCell(0, 10, $trabajo['descripcion_detallada'], 1, 'L');






// Espaciado
$pdf->Ln(5);

// 🔹 **Nueva Sección: Operaciones**
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, "Operaciones", 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);

// 📌 Construcción de la tabla de operaciones con columnas ajustadas
$tbl_operaciones = '<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th width="12%"><strong>Operación</strong></th>
            <th width="15%"><strong>Pto. Trab.</strong></th>
            <th width="20%"><strong>Desc. Pto. Trab.</strong></th>
            <th width="29%"><strong>Desc. Oper.</strong></th>
            <th width="8%"><strong>N° Pers.</strong></th>
            <th width="8%"><strong>H Est.</strong></th>
            <th width="8%"><strong>HH Tot. Prog.</strong></th>
        </tr>
    </thead>
    <tbody>';

while ($op = $operaciones->fetch_assoc()) {
    $tbl_operaciones .= '<tr>
        <td width="12%">' . htmlspecialchars($op['operacion']) . '</td>
        <td width="15%">' . htmlspecialchars($op['pto_trab']) . '</td>
        <td width="20%">' . htmlspecialchars($op['desc_pto_trab']) . '</td>
        <td width="29%">' . htmlspecialchars($op['desc_oper']) . '</td>
        <td width="8%">' . htmlspecialchars($op['n_pers']) . '</td>
        <td width="8%">' . htmlspecialchars($op['h_est']) . '</td>
        <td width="8%">' . htmlspecialchars($op['hh_tot_prog']) . '</td>
    </tr>';
}

$tbl_operaciones .= '</tbody></table>';

// ✅ Agregar la tabla de operaciones al PDF
$pdf->writeHTML($tbl_operaciones, true, false, false, false, '');



// Espaciado
$pdf->Ln(5);

// 🔹 **Nueva Sección: Materiales/Productos**
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, "Materiales Utilizados", 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);

// 📌 Construcción de la tabla de materiales con columnas ajustadas
$tbl_materiales = '<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th width="23%"><strong>Producto</strong></th>
            <th width="50%"><strong>Descripción</strong></th>
            <th width="15%"><strong>Número de Parte</strong></th>
            <th width="12%"><strong>Cantidad</strong></th>
        </tr>
    </thead>
    <tbody>';

while ($mat = $materiales->fetch_assoc()) {
    $tbl_materiales .= '<tr>
        <td width="23%">' . htmlspecialchars($mat['nombre']) . '</td>
        <td width="50%">' . htmlspecialchars($mat['descripcion']) . '</td>
        <td width="15%">' . htmlspecialchars($mat['numero_parte']) . '</td>
        <td width="12%">' . htmlspecialchars($mat['cantidad']) . '</td>
    </tr>';
}

$tbl_materiales .= '</tbody></table>';

// ✅ Agregar la tabla de materiales al PDF
$pdf->writeHTML($tbl_materiales, true, false, false, false, '');




// Espaciado
$pdf->Ln(5);

// 🔹 **Nueva Sección: Trabajadores Asignados**
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, "Trabajadores Asignados", 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);

// 📌 Construcción de la tabla de trabajadores con columnas ajustadas
$tbl_trabajadores = '<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th width="70%"><strong>Trabajador</strong></th>
            <th width="30%"><strong>Horas Trabajadas</strong></th>
        </tr>
    </thead>
    <tbody>';

while ($trab = $trabajadores->fetch_assoc()) {
    $tbl_trabajadores .= '<tr>
        <td width="70%">' . htmlspecialchars($trab['trabajador']) . '</td>
        <td width="30%">' . htmlspecialchars($trab['horas_trabajadas']) . '</td>
    </tr>';
}

$tbl_trabajadores .= '</tbody></table>';

// ✅ Agregar la tabla de trabajadores al PDF
$pdf->writeHTML($tbl_trabajadores, true, false, false, false, '');




// Generar archivo PDF y descargarlo
$pdf->Output("trabajo_$trabajo_id.pdf", "D");
?>
