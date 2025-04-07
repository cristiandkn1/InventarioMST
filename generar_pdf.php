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

class MYPDF extends TCPDF {
    public function Header() {
        $logo_path = __DIR__ . '/img/logo.png';
        if (file_exists($logo_path)) {
            $this->Image($logo_path, 10, 10, 25);
        }

        // ❌ Esto NO tiene efecto aquí para el contenido principal
        // $this->SetY(60);
    }
}








// Crear PDF con clase personalizada
$pdf = new MYPDF();

// ✅ Mueve el contenido hacia abajo en TODAS las páginas
$pdf->SetMargins(15, 40, 15); // ← aquí defines el margen superior (45px después del logo)

$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->AddPage();

// Ya NO necesitas hacer $pdf->SetY(60); aquí

// 🔹 Solo en la primera página bajamos manualmente el contenido (porque no queremos tanto margen)
$pdf->SetY(20); // Puedes ajustar esto según el espacio que quieras



// **Título del documento**
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, "Detalles del Trabajo", 0, 1, 'C');

// Espaciado
$pdf->Ln(5);

// **TABLA - INFORMACIÓN GENERAL**
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, "Información General", 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

$tbl = <<<EOD
<style>
    table {
        border-collapse: collapse;
        font-size: 9.5pt;
        margin-left: -10px; /* 🔹 Corrección para que no se salga de la hoja */
    }
    td {
        border: 0.4px solid #444;
        padding: 3px;
        vertical-align: top;
    }
    .titulo {
        background-color: #dce6f1;
        font-weight: bold;
        width: 28%;
    }
    .valor {
        background-color: #f9f9f9;
        width: 22%;
    }
</style>
<table>
    <tr>
        <td class="titulo">Tipo de Trabajo</td><td class="valor">{$trabajo['tipo']}</td>
        <td class="titulo">Título</td><td class="valor">{$trabajo['titulo']}</td>
    </tr>
    <tr>
        <td class="titulo">Empresa Mandante</td><td class="valor">{$trabajo['empresa']}</td>
        <td class="titulo">N° Orden</td><td class="valor">{$trabajo['nro_orden']}</td>
    </tr>
    <tr>
        <td class="titulo">Fecha de Creación</td><td class="valor">{$trabajo['fecha_creacion']}</td>
        <td class="titulo">Fecha de Entrega</td><td class="valor">{$trabajo['fecha_entrega']}</td>
    </tr>
    <tr>
        <td class="titulo">Estado</td><td class="valor">{$trabajo['estado']}</td>
        <td class="titulo">Clase de Orden</td><td class="valor">{$trabajo['clase_orden']}</td>
    </tr>
    <tr>
        <td class="titulo">Clase de Actividad PL</td><td class="valor">{$trabajo['clase_actividad_pl']}</td>
        <td class="titulo">Prioridad</td><td class="valor">{$trabajo['prioridad']}</td>
    </tr>
    <tr>
        <td class="titulo">Revisión</td><td class="valor">{$trabajo['revision']}</td>
        <td class="titulo">Grupo de Planificación</td><td class="valor">{$trabajo['grp_planificacion']}</td>
    </tr>
    <tr>
        <td class="titulo">Punto de Trabajo Responsable</td><td class="valor">{$trabajo['pto_trab_responsable']}</td>
        <td class="titulo">Ubicación Técnica</td><td class="valor">{$trabajo['ubicacion_tecnica']}</td>
    </tr>
    <tr>
        <td class="titulo">Equipo</td><td class="valor">{$trabajo['equipo']}</td>
        <td class="titulo">Denominación del Equipo</td><td class="valor">{$trabajo['den_equipo']}</td>
    </tr>
    <tr>
        <td class="titulo">Reserva</td><td class="valor">{$trabajo['reserva']}</td>
        <td class="titulo">Solicitud de Pedido</td><td class="valor">{$trabajo['sol_ped']}</td>
    </tr>
    <tr>
        <td class="titulo">Fecha de Inicio</td><td class="valor">{$trabajo['fecha_inicio']}</td>
        <td class="titulo">Duración Estimada (Días)</td><td class="valor">{$trabajo['duracion_dias']}</td>
    </tr>
    <tr>
        <td class="titulo">Hora de Inicio</td><td class="valor">{$trabajo['hora_inicio']}</td>
        <td class="titulo">Hora de Fin</td><td class="valor">{$trabajo['hora_fin']}</td>
    </tr>
</table>
EOD;

$pdf->writeHTML($tbl, true, false, false, false, '');
$pdf->Ln(1);






// 🔷 SECCIÓN: Descripción de la Orden
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(33, 37, 41); // texto oscuro
$pdf->Cell(0, 10, "Descripción de la Orden", 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(245, 245, 245); // fondo gris claro
$pdf->SetDrawColor(180, 180, 180); // borde gris
$pdf->MultiCell(0, 15, $trabajo['descripcion_orden'], 1, 'L', true);
$pdf->Ln(5);

// 🔷 SECCIÓN: Estado del Trabajo
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 10, "Estado del Trabajo", 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(255, 255, 255); // fondo blanco
$pdf->SetDrawColor(180, 180, 180);
$pdf->MultiCell(0, 10, $trabajo['estado'], 1, 'L', true);
$pdf->Ln(5);

// 🔷 SECCIÓN: Descripción Detallada
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 10, "Descripción Detallada", 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(245, 245, 245);
$pdf->SetDrawColor(180, 180, 180);
$pdf->MultiCell(0, 25, $trabajo['descripcion_detallada'], 1, 'L', true);
$pdf->Ln(5);





// 🔷 SECCIÓN: Operaciones
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 10, "Operaciones", 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

// ✅ Tabla con columnas iguales (14.2857% * 7 = 100%)
$tbl_operaciones = <<<EOD
<style>
    table.op {
        border-collapse: collapse;
        font-size: 9pt;
        width: 100%;
    }
    table.op th {
        background-color: #f0f0f0;
        border: 1px solid #999;
        padding: 5px;
        font-weight: bold;
        text-align: center;
    }
    table.op td {
        border: 1px solid #ccc;
        padding: 4px;
        text-align: center;
    }
</style>
<table class="op">
    <thead>
        <tr>
            <th width="14.2857%">Operación</th>
            <th width="14.2857%">Pto. Trab.</th>
            <th width="14.2857%">Desc. Pto. Trab.</th>
            <th width="14.2857%">Desc. Operación</th>
            <th width="14.2857%">N° Pers.</th>
            <th width="14.2857%">H Est.</th>
            <th width="14.2857%">HH Prog.</th>
        </tr>
    </thead>
    <tbody>
EOD;

while ($op = $operaciones->fetch_assoc()) {
    $tbl_operaciones .= '<tr>
        <td>' . htmlspecialchars($op['operacion']) . '</td>
        <td>' . htmlspecialchars($op['pto_trab']) . '</td>
        <td>' . htmlspecialchars($op['desc_pto_trab']) . '</td>
        <td>' . htmlspecialchars($op['desc_oper']) . '</td>
        <td>' . htmlspecialchars($op['n_pers']) . '</td>
        <td>' . htmlspecialchars($op['h_est']) . '</td>
        <td>' . htmlspecialchars($op['hh_tot_prog']) . '</td>
    </tr>';
}

$tbl_operaciones .= '</tbody></table>';

// ✅ Imprimir tabla en el PDF
$pdf->writeHTML($tbl_operaciones, true, false, false, false, '');


// Espaciado
$pdf->Ln(2);

// 🔹 SECCIÓN: Materiales Utilizados
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 10, " Materiales Utilizados", 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

// ✅ Tabla con 4 columnas iguales (25% cada una)
$tbl_materiales = <<<EOD
<style>
    table.mat {
        border-collapse: collapse;
        font-size: 9pt;
        width: 100%;
    }
    table.mat th {
        background-color: #e9ecef;
        border: 1px solid #999;
        padding: 5px;
        font-weight: bold;
        text-align: center;
    }
    table.mat td {
        border: 1px solid #ccc;
        padding: 4px;
        text-align: center;
    }
</style>
<table class="mat">
    <thead>
        <tr>
            <th width="25%">Producto</th>
            <th width="25%">Descripción</th>
            <th width="25%">Número de Parte</th>
            <th width="25%">Cantidad</th>
        </tr>
    </thead>
    <tbody>
EOD;

while ($mat = $materiales->fetch_assoc()) {
    $tbl_materiales .= '<tr>
        <td>' . htmlspecialchars($mat['nombre']) . '</td>
        <td>' . htmlspecialchars($mat['descripcion']) . '</td>
        <td>' . htmlspecialchars($mat['numero_parte']) . '</td>
        <td>' . htmlspecialchars($mat['cantidad']) . '</td>
    </tr>';
}

$tbl_materiales .= '</tbody></table>';

// ✅ Imprimir tabla en el PDF
$pdf->writeHTML($tbl_materiales, true, false, false, false, '');




// Espaciado
$pdf->Ln(5);

// 🔹 SECCIÓN: Trabajadores Asignados
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 10, " Trabajadores Asignados", 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

// ✅ Tabla con columnas iguales y estilo uniforme
$tbl_trabajadores = <<<EOD
<style>
    table.trab {
        border-collapse: collapse;
        font-size: 9pt;
        width: 100%;
    }
    table.trab th {
        background-color: #e9ecef;
        border: 1px solid #999;
        padding: 5px;
        font-weight: bold;
        text-align: center;
    }
    table.trab td {
        border: 1px solid #ccc;
        padding: 4px;
        text-align: center;
    }
</style>
<table class="trab">
    <thead>
        <tr>
            <th width="50%">Trabajador</th>
            <th width="50%">Horas Trabajadas</th>
        </tr>
    </thead>
    <tbody>
EOD;

while ($trab = $trabajadores->fetch_assoc()) {
    $tbl_trabajadores .= '<tr>
        <td>' . htmlspecialchars($trab['trabajador']) . '</td>
        <td>' . htmlspecialchars($trab['horas_trabajadas']) . '</td>
    </tr>';
}

$tbl_trabajadores .= '</tbody></table>';

// ✅ Agregar la tabla al PDF
$pdf->writeHTML($tbl_trabajadores, true, false, false, false, '');

// Espaciado
$pdf->Ln(10);

// 🔹 SECCIÓN: Registro Final de Actividad - Firmas
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 10, " Registro Final de Actividad", 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

$tbl_firmas = <<<EOD
<style>
    table.firma {
        border-collapse: collapse;
        font-size: 9pt;
        width: 100%;
        margin-top: 10px;
    }
    table.firma td {
        border: 1px solid #ccc;
        text-align: left;
    }
    .label {
        font-weight: bold;
        background-color: #f8f9fa;
        height: 25px; /* altura más baja para la fila de etiquetas */
        padding: 4px;
    }
    .firma-space {
        height: 60px; /* altura mayor para la fila de firmas */
        padding: 4px;
    }
</style>
<table class="firma">
    <tr>
        <td class="label" width="50%">Firma Responsable:</td>
        <td class="label" width="50%">Firma Revisión Documento:</td>
    </tr>
    <tr>
        <td class="firma-space"></td>
        <td class="firma-space"></td>
    </tr>
</table>
EOD;

// ✅ Agregar tabla de firmas al PDF
$pdf->writeHTML($tbl_firmas, true, false, false, false, '');

// Generar archivo PDF y descargarlo
$pdf->Output("trabajo_$trabajo_id.pdf", "D");
?>
