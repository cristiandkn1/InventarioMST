<?php


function recalcularStock($conn) {
    $sql = "
        UPDATE producto p
        LEFT JOIN (
            SELECT 
                tp.producto_id,
                IFNULL(SUM(tp.cantidad), 0) AS total_trabajo,
                IFNULL(SUM(d.devueltos + d.usados), 0) AS total_devuelto
            FROM trabajo_producto tp
            LEFT JOIN devoluciones d ON d.trabajo_producto_id = tp.idtrabajo_producto
            GROUP BY tp.producto_id
        ) trabajos ON p.idproducto = trabajos.producto_id

        LEFT JOIN (
            SELECT 
                epd.producto_id,
                SUM(
                    epd.cantidad_enviada 
                    - IFNULL(epd.cantidad_devuelta, 0)
                    - IFNULL(epd.cantidad_usada, 0)
                    - IFNULL(epd.cantidad_perdida, 0)
                ) AS total_en_uso_envios
            FROM envio_producto_detalle epd
            JOIN envio_producto ep ON ep.idenvio = epd.envio_id
            GROUP BY epd.producto_id
        ) envios ON p.idproducto = envios.producto_id

        SET 
            p.en_uso = GREATEST(
                IFNULL(trabajos.total_trabajo, 0) - IFNULL(trabajos.total_devuelto, 0)
                + IFNULL(envios.total_en_uso_envios, 0), 0),

            p.disponibles = GREATEST(
                p.cantidad - (
                    GREATEST(
                        IFNULL(trabajos.total_trabajo, 0) - IFNULL(trabajos.total_devuelto, 0), 0)
                    + IFNULL(envios.total_en_uso_envios, 0)
                ), 0)

        WHERE p.cantidad IS NOT NULL
    ";

    if (!$conn->query($sql)) {
        die("❌ Error al recalcular stock: " . $conn->error);
    }
}
