<?php
include 'db.php';

$trabajo_id = intval($_POST['trabajo_id']);

// 🔄 Consulta con cálculo de asignado restante
$sql = "
    SELECT 
        tp.idtrabajo_producto,
        p.nombre,
        tp.cantidad AS asignado_total,
        COALESCE(SUM(d.devueltos + d.usados), 0) AS total_devuelto_usado,
        (tp.cantidad - COALESCE(SUM(d.devueltos + d.usados), 0)) AS asignado_restante
    FROM trabajo_producto tp
    JOIN producto p ON p.idproducto = tp.producto_id
    LEFT JOIN devoluciones d ON d.trabajo_producto_id = tp.idtrabajo_producto
    WHERE tp.trabajo_id = $trabajo_id
    GROUP BY tp.idtrabajo_producto, p.nombre, tp.cantidad
";

$result = $conn->query($sql);

$html = "<table class='table table-sm table-bordered'>
            <thead class='table-light'>
                <tr>
                    <th>Producto</th>
                    <th>Asignado</th>
                    <th>Devuelto</th>
                    <th>Usado/Perdido</th>
                </tr>
            </thead>
            <tbody>";

while ($row = $result->fetch_assoc()) {
    $idTrabajoProducto = $row['idtrabajo_producto'];
    $restante = intval($row['asignado_restante']);
    $nombre = htmlspecialchars($row['nombre']);

    // ✅ Si ya no queda nada para devolver, desactivamos los inputs
    if ($restante <= 0) {
        $html .= "<tr>
            <td>{$nombre}</td>
            <td class='asignado text-muted' data-cantidad='0'>0</td>
            <td colspan='2' class='text-center text-muted'>✔️ Todo devuelto</td>
        </tr>";
    } else {
        $html .= "<tr>
            <td>{$nombre}</td>
            <td class='asignado' data-cantidad='{$restante}'>{$restante}</td>
            <td>
                <input 
                    type='number' 
                    name='devueltos[{$idTrabajoProducto}]' 
                    class='form-control input-devuelto' 
                    data-id='{$idTrabajoProducto}' 
                    min='0' 
                    max='{$restante}'>
            </td>
            <td>
                <label id='usado_{$idTrabajoProducto}' class='form-control-plaintext text-danger'>0</label>
                <input 
                    type='hidden' 
                    name='usados[{$idTrabajoProducto}]' 
                    id='input_usado_{$idTrabajoProducto}' 
                    value='0'>
            </td>
        </tr>";
    }
}

$html .= "</tbody></table>";

echo $html;
