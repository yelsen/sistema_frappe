<?php

require('../modelo/fpdf186/fpdf.php');
include("../modelo/conexion.php");

function generarPDF($venta) {
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Título del documento
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetFillColor(45, 62, 80); // Fondo oscuro para el título
    $pdf->SetTextColor(255, 255, 255); // Texto blanco
    $pdf->Cell(0, 10, "Comprobante de Venta", 0, 1, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0); // Volver a color de texto negro
    $pdf->Ln(10);

    // Información general del comprobante
    $pdf->Cell(0, 10, "Comprobante: " . $venta['comprobante'], 0, 1, 'L');
    $pdf->Cell(0, 10, "Fecha de Venta: " . $venta['fecha_venta'], 0, 1, 'L');
    $pdf->Cell(0, 10, "Monto Total: S/ " . number_format($venta['total_monto'], 2), 0, 1, 'L');
    $pdf->Ln(10);

    // Tabla de productos, cantidades, precios y subtotales
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(220, 220, 220); // Fondo gris claro para las celdas
    $pdf->Cell(100, 10, 'Producto', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Cantidad', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Precio (S/)', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Subtotal (S/)', 1, 1, 'C', true);
    
    // Productos, cantidades, precios y subtotales
    $productos = explode(', ', $venta['productos']);
    $cantidades = explode(', ', $venta['cantidades']);
    $precios = explode(', ', $venta['precios']);
    $subtotales = explode(', ', $venta['subtotales']);
    
    $pdf->SetFont('Arial', '', 10);
    for ($i = 0; $i < count($productos); $i++) {
        $pdf->Cell(100, 10, $productos[$i], 1, 0, 'L');
        $pdf->Cell(30, 10, $cantidades[$i], 1, 0, 'C');
        $pdf->Cell(30, 10, number_format($precios[$i], 2), 1, 0, 'R');
        $pdf->Cell(30, 10, number_format($subtotales[$i], 2), 1, 1, 'R');
    }

    // Salida del PDF
    return $pdf->Output('S');
}

// Obtener la venta desde la base de datos
if (isset($_GET['idventa'])) {
    $idventa = $_GET['idventa']; // Cambié $_POST a $_GET

    $stmt = $conexion->prepare("
        SELECT 
            CONCAT(t.tipo_comprobante, ': ', c.num_comprobanteV) AS comprobante,
            v.fecha_venta, 
            v.monto_venta,
            SUM(v.monto_venta) AS total_monto, 
            GROUP_CONCAT(CONCAT(ct.categoria, ' - ', s.sabor, ' (', p.presentacion, ')') SEPARATOR ', ') AS productos,
            GROUP_CONCAT(d.cantidad SEPARATOR ', ') AS cantidades,
            GROUP_CONCAT(pr.precio_venta SEPARATOR ', ') AS precios,
            GROUP_CONCAT(d.subtotal SEPARATOR ', ') AS subtotales
        FROM ventas AS v
        LEFT JOIN detalle_ventas AS d ON v.idventa = d.fk_idventa
        INNER JOIN comprobantes AS c ON v.idventa = c.fk_idventaV
        INNER JOIN tipo_comprobantes AS t ON t.idtipo_comprobante = c.fk_idtipo_comprobanteV
        INNER JOIN productos AS pr ON pr.idproducto = d.fk_idproducto
        INNER JOIN catalogos AS ca ON ca.idcatalogo = pr.fk_idcatalogo
        INNER JOIN categorias AS ct ON ct.idcategoria = ca.fk_idcategoria
        INNER JOIN sabores AS s ON s.idsabor = ca.fk_idsabor
        INNER JOIN presentaciones AS p ON p.idpresentacion = ca.fk_idpresentacion
        WHERE v.idventa = ?
        GROUP BY  
            v.idventa, 
            t.tipo_comprobante, 
            c.num_comprobanteV, 
            v.fecha_venta, 
            v.hora_venta;
    ");
    $stmt->bind_param("i", $idventa);
    $stmt->execute();
    $venta = $stmt->get_result()->fetch_assoc();

    if ($venta) {
        // Generar el PDF con los datos obtenidos
        $pdfContent = generarPDF($venta);

        // Mostrar el PDF generado en el navegador
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="comprobante.pdf"');

        echo $pdfContent;
    } else {
        echo "Venta no encontrada.";
    }

    // Cerrar la conexión a la base de datos
    $conexion->close();
} else {
    echo "Por favor, proporciona el ID de la venta.";
}

?>