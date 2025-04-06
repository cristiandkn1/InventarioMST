<?php
if (!isset($trabajo_id) || empty($trabajo_id)) {
    die("❌ Error: No se recibió un ID de trabajo válido.");
}

// ✅ Recorrer materiales y actualizar en `trabajo_producto`
if (!empty($_POST['material_id']) && is_array($_POST['material_id'])) {
    foreach ($_POST['material_id'] as $index => $id_producto) {
        $descripcion = $_POST['descripcion_material'][$index] ?? '';
        $numero_parte = $_POST['numero_parte_material'][$index] ?? '';
        $nro_asignacion_nuevo = $_POST['nro_asignacion_material'][$index] ?? '';
        $cantidad_nueva = isset($_POST['cantidad_material'][$index]) ? intval($_POST['cantidad_material'][$index]) : 1;

        if (!$id_producto) {
            echo "<script>alert('❌ Error: Producto no válido.'); window.history.back();</script>";
            exit();
        }

        // Obtener la cantidad anterior y nro_asignacion actual
        $sql = "SELECT tp.cantidad, p.nro_asignacion 
                FROM trabajo_producto tp
                LEFT JOIN producto p ON tp.producto_id = p.idproducto
                WHERE tp.trabajo_id = ? AND tp.producto_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $trabajo_id, $id_producto);
        $stmt->execute();
        $result = $stmt->get_result();
        $material_existente = $result->fetch_assoc();
        $stmt->close();

        $cantidad_anterior = $material_existente['cantidad'] ?? 0;
        $nro_asignacion_anterior = $material_existente['nro_asignacion'] ?? '';

        if ($nro_asignacion_nuevo !== $nro_asignacion_anterior) {
            $sql = "UPDATE producto SET nro_asignacion = ? WHERE idproducto = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $nro_asignacion_nuevo, $id_producto);
            $stmt->execute();
            $stmt->close();
        }

        $sql = "SELECT cantidad, en_uso FROM producto WHERE idproducto = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_producto);
        $stmt->execute();
        $result = $stmt->get_result();
        $producto = $result->fetch_assoc();
        $stmt->close();

        if (!$producto) {
            echo "<script>alert('❌ Producto no encontrado.'); window.history.back();</script>";
            exit();
        }

        $cantidad_total = $producto['cantidad'];
        $en_uso = $producto['en_uso'];
// ✅ Recalcular stock disponible permitiendo reutilizar lo que ya está asignado al trabajo actual
$stock_disponible = $cantidad_total - ($en_uso - $cantidad_anterior);

        if ($cantidad_nueva > ($stock_disponible + $cantidad_anterior)) {
            echo "<script>
                    alert('❌ No puedes asignar más de lo disponible. Producto ID $id_producto. \nDisponible: $stock_disponible');
                    window.history.back();
                  </script>";
            exit();
        }

        if ($material_existente) {
            $sql = "UPDATE trabajo_producto SET descripcion = ?, numero_parte = ?, cantidad = ? 
                    WHERE trabajo_id = ? AND producto_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiii", $descripcion, $numero_parte, $cantidad_nueva, $trabajo_id, $id_producto);
            $stmt->execute();
            $stmt->close();
        } else {
            $sql = "INSERT INTO trabajo_producto (trabajo_id, producto_id, descripcion, numero_parte, cantidad) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissi", $trabajo_id, $id_producto, $descripcion, $numero_parte, $cantidad_nueva);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Eliminar materiales si corresponde
if (!empty($_POST['eliminar_material']) && is_array($_POST['eliminar_material'])) {
    foreach ($_POST['eliminar_material'] as $id_producto) {
        $id_producto = intval($id_producto);

        // Obtener la cantidad antes de eliminar para ajustar en_uso
        $sql = "SELECT cantidad FROM trabajo_producto WHERE trabajo_id = ? AND producto_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $trabajo_id, $id_producto);
        $stmt->execute();
        $result = $stmt->get_result();
        $fila = $result->fetch_assoc();
        $cantidad_eliminada = $fila['cantidad'] ?? 0;
        $stmt->close();

        // Eliminar de trabajo_producto
        $sql = "DELETE FROM trabajo_producto WHERE trabajo_id = ? AND producto_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $trabajo_id, $id_producto);
        $stmt->execute();
        $stmt->close();

        // Reducir en_uso manualmente
        $sql = "UPDATE producto SET en_uso = GREATEST(en_uso - ?, 0) WHERE idproducto = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $cantidad_eliminada, $id_producto);
        $stmt->execute();
        $stmt->close();
    }
}

// ✅ Recalcular stock
recalcularStock($conn);

// ✅ Redirección
echo "<script>
        alert('✅ Los cambios fueron guardados correctamente.');
        window.location.href = 'trabajos.php';
      </script>";
exit();