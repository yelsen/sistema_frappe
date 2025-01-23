<?php
include("../modelo/conexion.php");
function mostrarDato($dto)
{
    global $conexion;

    $sql = "SELECT 
                v.idventa,
                CONCAT(t.tipo_comprobante, ': ', c.num_comprobanteV) AS comprobante,
                v.fecha_venta, 
                v.hora_venta, 
                v.monto_venta,
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
            WHERE  
                c.num_comprobanteV LIKE ? OR 
                v.fecha_venta LIKE ? OR 
                t.tipo_comprobante LIKE ?
            GROUP BY  
                v.idventa, 
                t.tipo_comprobante, 
                c.num_comprobanteV, 
                v.fecha_venta, 
                v.hora_venta, 
                v.monto_venta;";

    $stmt = $conexion->prepare($sql);
    $dto = "%$dto%";
    $stmt->bind_param('sss', $dto, $dto, $dto);
    $stmt->execute();
    $resultado = $stmt->get_result();

    echo (
        "<div class='invoice-add-table'>
            <div class='table-responsive'>
                <table class='table table-hover'>
                    <thead>
                        <tr>
                            <th scope='col'>N°</th>
                            <th scope='col'>Comprobante</th>
                            <th scope='col'>Fecha</th>
                            <th scope='col'>Hora</th>
                            <th scope='col'>Monto</th>
                            <th scope='col'>Detalle de la Venta</th>
                            <th scope='col' class='text-end'>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>"
    );

    if ($resultado->num_rows > 0) {
        $item = 0;
        while ($row = $resultado->fetch_assoc()) {
            $item++;
            $productos = explode(", ", $row['productos']);
            $cantidades = explode(", ", $row['cantidades']);
            $precios = explode(", ", $row['precios']);
            $subtotales = explode(", ", $row['subtotales']);

            echo (
                "<tr>      
                    <td>" . $item . "</td>
                    <td>" . htmlspecialchars($row['comprobante'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . htmlspecialchars($row['fecha_venta'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . htmlspecialchars($row['hora_venta'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . number_format($row['monto_venta'], 2) . "</td>
                    <td>
                        <table class='table'>
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>"
            );

            for ($i = 0; $i < count($productos); $i++) {
                echo (
                    "<tr>
                        <td>" . htmlspecialchars($productos[$i], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>" . htmlspecialchars($cantidades[$i], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>" . number_format($precios[$i], 2) . "</td>
                        <td>" . number_format($subtotales[$i], 2) . "</td>
                    </tr>"
                );
            }

            echo (
                "</tbody>
                        </table>
                    </td>
                    <td class='text-end'>
                        <div class='btn-group'>
                            <button onclick=\"editar('" . $row['idventa'] . "')\" 
                                    title='Editar datos' 
                                    type='button' 
                                    class='btn btn-warning'>
                                <i class='fas fa-edit'></i>
                            </button>
                            <button onclick=\"eliminar('" . $row['idventa'] . "')\" 
                                    title='Eliminar datos' 
                                    type='button' 
                                    class='btn btn-danger'>
                                <i class='fas fa-trash-alt'></i>
                            </button>
                        </div>
                    </td>
                </tr>"
            );
        }
    } else {
        echo ("<tr><td colspan='8' class='text-center'>No se encontraron datos</td></tr>");
    }

    echo ("</tbody></table></div></div>");
}








session_start();

function registrarVenta($monto, $vuelto, $letra, $numero, $idtipo)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("CALL p_ventas(?, ?, ?, ?, ?, @idvent);");
        $stmt->bind_param("sssss", $monto, $vuelto, $letra, $numero, $idtipo);

        if ($stmt->execute()) {
            $result = $conexion->query("SELECT @idvent AS idvent;");
            $row = $result->fetch_assoc();
            $_SESSION['idventa'] = $row['idvent'];
            return "Venta registrada correctamente con ID: " . $_SESSION['idventa'];
        } else {
            return "Error al registrar la venta: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error al registrar la venta: " . $e->getMessage();
    }
}

function registrarDetalleVenta($idventa, $producto, $cantidad, $subtotal)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("CALL p_detalle_ventas(?, ?, ?, ?);");
        $stmt->bind_param("ssss", $idventa, $producto, $cantidad, $subtotal);

        if ($stmt->execute()) {
            return "Detalle registrado correctamente para venta ID: " . $idventa;
        } else {
            return "Error al registrar el detalle: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}















function obtenerProducto($productoDato)
{
    global $conexion;
    try {
        error_log("Producto recibido: " . $productoDato);
        $sql = "SELECT precio_venta, stock_producto
                FROM productos AS pr
                INNER JOIN catalogos AS ca ON ca.idcatalogo = pr.fk_idcatalogo
                INNER JOIN categorias AS c ON c.idcategoria = ca.fk_idcategoria
                INNER JOIN sabores AS s ON s.idsabor = ca.fk_idsabor
                INNER JOIN presentaciones AS p ON p.idpresentacion = ca.fk_idpresentacion
                WHERE CONCAT(categoria, ' - ', sabor, ' (', presentacion, ')') = ?";

        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("s", $productoDato);
            $stmt->execute();

            $resultado = $stmt->get_result();

            if ($row = $resultado->fetch_assoc()) {
                return json_encode([
                    "success" => true,
                    "data" => [
                        "precio_venta" => $row['precio_venta'],
                        "stock_producto" => $row['stock_producto']
                    ]
                ]);
            } else {
                return json_encode(["success" => false, "message" => "No se encontraron datos."]);
            }
        } else {
            return json_encode(["success" => false, "message" => "Error al preparar la consulta: " . $conexion->error]);
        }
    } catch (Exception $e) {
        return json_encode(["success" => false, "message" => "Error en la consulta: " . $e->getMessage()]);
    }
}


function generarNumeroComprobante($idTipoComprobante)
{
    global $conexion;
    try {
        $sql = "SELECT MAX(num_comprobanteV) AS max_numero FROM comprobantes AS c
                INNER JOIN tipo_comprobantes AS t ON t.idtipo_comprobante = c.fk_idtipo_comprobanteV
                WHERE idtipo_comprobante = ?";

        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("i", $idTipoComprobante);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $row = $resultado->fetch_assoc();

            if ($row && $row['max_numero'] !== null) {
                $nuevoNumero = intval($row['max_numero']) + 1;
                $numeroFormateado = str_pad($nuevoNumero, 12, "0", STR_PAD_LEFT);

                return json_encode(["success" => true, "num_comprobanteV" => $numeroFormateado]);
            } else {
                $numeroInicial = str_pad(1, 12, "0", STR_PAD_LEFT);
                return json_encode(["success" => true, "num_comprobanteV" => $numeroInicial]);
            }
        } else {
            return json_encode(["success" => false, "message" => "Error al preparar la consulta: " . $conexion->error]);
        }
    } catch (Exception $e) {
        return json_encode(["success" => false, "message" => "Error en la consulta: " . $e->getMessage()]);
    }
}

function filtradorDatoProductos($dto)
{
    global $conexion;
    try {
        $sql = "SELECT idproducto, concat(categoria, ' - ', sabor, ' (', presentacion,')') as 'producto', precio_venta, stock_producto
                from productos as pr
                inner join catalogos as ca on ca.idcatalogo=pr.fk_idcatalogo
                inner join categorias as c on c.idcategoria=ca.fk_idcategoria
                inner join sabores as s on s.idsabor=ca.fk_idsabor
                inner join presentaciones as p on p.idpresentacion=ca.fk_idpresentacion
            WHERE concat(categoria, ' - ', sabor, ' (', presentacion,')') LIKE CONCAT('%', ?, '%');";

        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("s", $dto);
            $stmt->execute();
            $resultado = $stmt->get_result();

            $data = [];
            while ($row = $resultado->fetch_assoc()) {
                $data[] = [
                    "val1" => $row['idproducto'],
                    "val2" => $row['producto'],
                    "val3" => $row['precio_venta'],
                    "val4" => $row['stock_producto']
                ];
            }

            if (!empty($data)) {
                return json_encode(["success" => true, "data" => $data]);
            } else {
                return json_encode(["success" => false, "message" => "No se encontraron datos."]);
            }
        } else {
            return json_encode(["success" => false, "message" => "Error al preparar la consulta: " . $conexion->error]);
        }
    } catch (Exception $e) {
        return json_encode(["success" => false, "message" => "Error en la consulta: " . $e->getMessage()]);
    }
}

function obtenerOpciones()
{
    global $conexion;
    try {
        $sql = "SELECT idtipo_comprobante, tipo_comprobante FROM tipo_comprobantes";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'val1' => $row['idtipo_comprobante'],
                'val2' => $row['tipo_comprobante']
            ];
        }
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['data' => $data]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener las categorías: ' . $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['funcion']) && $_GET['funcion'] === 'obtenerOpciones') {
    obtenerOpciones();
    exit;
}


if (isset($_POST['ev'])) {
    $event = intval($_POST['ev']);
    switch ($event) {
        case 0:
            echo mostrarDato(
                $_POST['dt']
            );
            break;

        case 1:
            echo registrarVenta(
                $_POST['monto'],
                $_POST['vuelto'],
                $_POST['letra'],
                $_POST['numero'],
                $_POST['idtipo']
            );
            break;
        case 2:
            echo modificarDato(
                $_POST['dni'],
                $_POST['usuario'],
                $_POST['psw']
            );
            break;
        case 3:
            if (isset($_POST['idcatalogo']) && isset($_POST['idinsumo'])) {
                $idcatalogo = intval($_POST['idcatalogo']);
                $idinsumo = intval($_POST['idinsumo']);
                echo eliminarDato($idcatalogo, $idinsumo);
            } else {
                echo "Datos incompletos para eliminar.";
            }
            break;

        case 4:
            if (isset($_POST['entrada'])) {
                $entrada = trim($_POST['entrada']);
                echo filtradorDatoProductos($entrada);
            }
            break;
        case 5:
            echo generarNumeroComprobante(
                $_POST['idtipo_comprobante']
            );
            break;

        case 6:
            echo registrarDetalleVenta(
                $_POST['idventa'],
                $_POST['producto'],
                $_POST['cantidad'],
                $_POST['subtotal']
            );

            break;

        case 7:
            echo obtenerProducto(
                $_POST['producto']
            );
            break;
    }
}
