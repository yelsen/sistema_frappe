<?php
require('../modelo/fpdf186/fpdf.php');
include("../modelo/conexion.php");

function generarPDF($fechaInicio, $fechaFin, $ingresos, $egresos) {
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Título
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetFillColor(45, 62, 80); // Fondo oscuro para el título
    $pdf->SetTextColor(255, 255, 255); // Texto blanco
    $pdf->Cell(0, 10, "Reporte de Movimientos", 0, 1, 'C', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0); // Volver a color de texto negro
    $pdf->Cell(0, 10, "Rango de fechas: Desde $fechaInicio - Hasta $fechaFin", 0, 1, 'L');
    $pdf->Ln(10);



    // Tabla de ingresos
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(220, 220, 220); // Fondo gris claro para las celdas
    $pdf->Cell(0, 10, '1. Ingresos', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetFillColor(230, 230, 230); // Fondo más claro para la tabla

    // Encabezados de ingresos
    $pdf->Cell(10, 10, '#', 1, 0, 'C', true);
    $pdf->Cell(70, 10, 'Comprobante', 1, 0, 'C', true);
    $pdf->Cell(60, 10, 'Fecha', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Monto Total (S/)', 1, 1, 'C', true);

    $totalIngresos = 0;
    $contadorIngresos = 1;
    foreach ($ingresos as $ingreso) {
        $pdf->Cell(10, 10, $contadorIngresos++, 1, 0, 'C');
        $pdf->Cell(70, 10, $ingreso['comprobante'], 1, 0, 'L');
        $pdf->Cell(60, 10, $ingreso['fecha_venta'], 1, 0, 'C');
        $pdf->Cell(50, 10, number_format($ingreso['monto_venta'], 2), 1, 1, 'R');
        $totalIngresos += $ingreso['total_monto'];
    }
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(140, 10, 'Total Ingresos:', 1, 0, 'R');
    $pdf->Cell(50, 10, number_format($totalIngresos, 2), 1, 1, 'R');
    $pdf->Ln(15);

    // Tabla de egresos
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(220, 220, 220); // Fondo gris claro para las celdas
    $pdf->Cell(0, 10, '2. Egresos (Compras)', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetFillColor(230, 230, 230); // Fondo más claro para la tabla

    // Encabezados de egresos
    $pdf->Cell(10, 10, '#', 1, 0, 'C', true);
    $pdf->Cell(60, 10, 'Comprobante', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Fecha', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Transporte (S/)', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Monto Total (S/)', 1, 1, 'C', true);

    $totalEgresos = 0;
    $contadorEgresos = 1;
    foreach ($egresos as $egreso) {
        $pdf->Cell(10, 10, $contadorEgresos++, 1, 0, 'C');
        $pdf->Cell(60, 10, $egreso['comprobante'], 1, 0, 'L');
        $pdf->Cell(40, 10, $egreso['fecha_ingresoC'], 1, 0, 'C');
        $pdf->Cell(40, 10, number_format($egreso['transporte'], 2), 1, 0, 'R');
        $pdf->Cell(40, 10, number_format($egreso['monto_ingresoC'], 2), 1, 1, 'R');
        $totalEgresos += $egreso['monto_ingresoC'] + $egreso['transporte'];
    }
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(150, 10, 'Total Egresos:', 1, 0, 'R');
    $pdf->Cell(40, 10, number_format($totalEgresos, 2), 1, 1, 'R');
    $pdf->Ln(15);

    // Balance total
    $balance = $totalIngresos - $totalEgresos;
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(245, 245, 245); // Fondo muy claro para el balance
    $pdf->Cell(150, 10, 'Balance Total:', 1, 0, 'R');
    $pdf->Cell(40, 10, number_format($balance, 2), 1, 1, 'R');

    // Salida del PDF
    return $pdf->Output('S');
}



if (isset($_POST['fechaInicio'], $_POST['fechaFin'])) {
    $fechaInicio = $_POST['fechaInicio'];
    $fechaFin = $_POST['fechaFin'];

    $stmtIngresos = $conexion->prepare("
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
            WHERE v.fecha_venta BETWEEN ? AND ?
            GROUP BY  
                v.idventa, 
                t.tipo_comprobante, 
                c.num_comprobanteV, 
                v.fecha_venta, 
                v.hora_venta;
    ");

    $stmtIngresos->bind_param("ss", $fechaInicio, $fechaFin);
    $stmtIngresos->execute();
    $ingresos = $stmtIngresos->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmtEgresos = $conexion->prepare("
        SELECT 
                CONCAT(t.tipo_comprobante, ': ', c.num_comprobanteC) AS comprobante,
                c.fecha_ingresoC, 
                GROUP_CONCAT(COALESCE(i.nombre_insumo, '') SEPARATOR ', ') AS insumos,
                GROUP_CONCAT(COALESCE(d.stock_insumo, 0) SEPARATOR ', ') AS cantidades,
                GROUP_CONCAT(COALESCE(d.precio_insumo, 0) SEPARATOR ', ') AS precios,
                GROUP_CONCAT(COALESCE(d.stock_insumo * d.precio_insumo, 0) SEPARATOR ', ') AS subtotales,
                c.transporte, c.monto_ingresoC,
                SUM(c.transporte + c.monto_ingresoC) AS total_egreso
            FROM compras AS c
            LEFT JOIN detalle_compras AS d ON c.idcompra = d.fk_idcompra
            INNER JOIN insumos AS i ON i.idinsumo = d.fk_idinsumo
            INNER JOIN tipo_comprobantes AS t ON t.idtipo_comprobante = c.fk_idtipo_comprobante
            INNER JOIN proveedores AS p ON p.idproveedor = c.fk_idproveedor
            WHERE c.cond_comp = 0 
            AND (c.fecha_ingresoC BETWEEN ? AND ?)
            GROUP BY c.idcompra;
    ");
    $stmtEgresos->bind_param("ss", $fechaInicio, $fechaFin);
    $stmtEgresos->execute();
    $egresos = $stmtEgresos->get_result()->fetch_all(MYSQLI_ASSOC);

    // Generar el contenido del PDF
    $pdfContent = generarPDF($fechaInicio, $fechaFin, $ingresos, $egresos);

    // Mostrar el PDF generado en el navegador
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="reporte_movimientos.pdf"');
    echo $pdfContent;

    // Cerrar la conexión a la base de datos
    $conexion->close();
} else {
    echo "Por favor, envía ambas fechas para generar el reporte.";
}
?>
