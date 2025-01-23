<?php
include("../modelo/conexion.php");
function mostrarIngresos($fecha1, $fecha2) {
    global $conexion;

    $sql = "SELECT 
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
                v.hora_venta";

    if (!$stmt = $conexion->prepare($sql)) {
        die("Error en la preparación de la consulta: " . $conexion->error);
    }
    $stmt->bind_param('ss', $fecha1, $fecha2);

    if (!$stmt->execute()) {
        die("Error en la ejecución de la consulta: " . $stmt->error);
    }

    $resultado = $stmt->get_result();

    $totalMonto = 0;

    ob_start();  // Comienza el buffer de salida

    echo "<div class='invoice-add-table'>
            <div class='table-responsive'>
                <table class='table table-hover'>
                    <thead>
                        <tr>
                            <th scope='col'>N°</th>
                            <th scope='col'>Comprobante</th>
                            <th scope='col'>Fecha de Ingreso</th>
                            <th scope='col'>Monto</th>
                            <th scope='col'>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>";

    if ($resultado->num_rows > 0) {
        $item = 0;
        while ($row = $resultado->fetch_assoc()) {
            $item++;
            $totalMonto += $row['total_monto'];
            $insumos = explode(", ", $row['productos']);
            $cantidades = explode(", ", $row['cantidades']);
            $precios = explode(", ", $row['precios']);
            $subtotales = explode(", ", $row['subtotales']);

            $detalleId1 = "detalle-ingreso" . $item;
            echo "<tr>
                    <td>" . $item . "</td>
                    <td>" . htmlspecialchars($row['comprobante'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . htmlspecialchars($row['fecha_venta'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . number_format($row['monto_venta'], 2) . "</td>
                    <td>
                        <button class='btn btn-link' onclick=\"toggleDetalle('$detalleId1')\">Ver Detalle</button>
                        <div id='$detalleId1' class='detalle-compra' style='display: none;'>
                            <table class='table'>
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>";

            for ($i = 0; $i < count($insumos); $i++) {
                echo "<tr>
                        <td>" . htmlspecialchars($insumos[$i], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>" . htmlspecialchars($cantidades[$i], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>" . number_format($precios[$i], 2) . "</td>
                        <td>" . number_format($subtotales[$i], 2) . "</td>
                    </tr>";
            }

            echo "</tbody></table></div></td></tr>";
        }
    } else {
        echo "<tr><td colspan='6' class='text-center'>No se encontraron datos</td></tr>";
    }

    echo "</tbody></table></div></div>";

    // Retorna la tabla generada como JSON
    echo json_encode([
        'tabla' => ob_get_clean(),  // Captura la salida generada
        'total_monto' => number_format($totalMonto, 2)
    ]);
}




function mostrarEgresos($fecha1, $fecha2)
{
    global $conexion;

    $sql = "SELECT 
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
            INNER JOIN empresas AS e ON e.idempresa = p.fk_idempresa
            INNER JOIN personas AS pe ON pe.dni = p.fk_dniP
            WHERE c.cond_comp = 0 
            AND (c.fecha_ingresoC BETWEEN ? AND ?)
            GROUP BY c.idcompra;";

    if (!$stmt = $conexion->prepare($sql)) {
        die("Error en la preparación de la consulta: " . $conexion->error);
    }

    $stmt->bind_param('ss', $fecha1, $fecha2);

    if (!$stmt->execute()) {
        die("Error en la ejecución de la consulta: " . $stmt->error);
    }

    $resultado = $stmt->get_result();

    $totalEgreso = 0;

    ob_start();  // Comienza el buffer de salida

    echo "<div class='invoice-add-table'>
            <div class='table-responsive'>
                <table class='table table-hover'>
                    <thead>
                        <tr>
                            <th scope='col'>N°</th>
                            <th scope='col'>Comprobante</th>
                            <th scope='col'>Fecha de Egreso</th>
                            <th scope='col'>Transporte</th>
                            <th scope='col'>Monto</th>
                            <th scope='col'>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>";

    if ($resultado->num_rows > 0) {
        $item = 0;
        while ($row = $resultado->fetch_assoc()) {
            $item++;
            $insumos = explode(", ", $row['insumos']);
            $cantidades = explode(", ", $row['cantidades']);
            $precios = explode(", ", $row['precios']);
            $subtotales = explode(", ", $row['subtotales']);

            $detalleId2 = "detalle-egreso" . $item;

            // Sumar los egresos
            $totalEgreso += $row['monto_ingresoC'] + $row['transporte'];

            echo "<tr>      
                    <td>" . $item . "</td>
                    <td>" . htmlspecialchars($row['comprobante'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . htmlspecialchars($row['fecha_ingresoC'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . htmlspecialchars($row['transporte'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . number_format($row['monto_ingresoC'], 2) . "</td>
                    <td>
                        <button class='btn btn-link' onclick=\"toggleDetalle('$detalleId2')\">Ver Detalle</button>
                        <div id='$detalleId2' class='detalle-compra' style='display: none;'>
                            <table class='table'>
                                <thead>
                                    <tr>
                                        <th>Insumo</th>
                                        <th>Cantidad</th>
                                        <th>Precio</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>";

            for ($i = 0; $i < count($insumos); $i++) {
                echo "<tr>
                        <td>" . htmlspecialchars($insumos[$i], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>" . htmlspecialchars($cantidades[$i], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>" . number_format($precios[$i], 2) . "</td>
                        <td>" . number_format($subtotales[$i], 2) . "</td>
                    </tr>";
            }

            echo "</tbody></table></div></td></tr>";
        }
    } else {
        echo "<tr><td colspan='6' class='text-center'>No se encontraron datos</td></tr>";
    }

    echo "</tbody></table></div></div>";


    echo json_encode([
        'tabla' => ob_get_clean(),  
        'total_egreso' => number_format($totalEgreso, 2)
    ]);
}








if (isset($_POST['ev'])) {
    $event = intval($_POST['ev']);
    switch ($event) {
        case 0:
            echo mostrarIngresos(
                $_POST['fech1'],
                $_POST['fech2']
            );
            break;
        case 1:
            echo mostrarEgresos(
                $_POST['fech1'],
                $_POST['fech2']
            );
            break;

    }
}
