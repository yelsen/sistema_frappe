<?php
include("../modelo/conexion.php");


function mostrarDato($dto)
{
    global $conexion;

    $sql = "SELECT c.idcompra, MAX(d.iddetalle_compra) AS iddetalle_compra,
        CONCAT(t.tipo_comprobante, ': ', c.num_comprobanteC) AS comprobante,
        c.fecha_ingresoC, c.transporte, c.monto_ingresoC, pe.dni,
        CONCAT_WS(' ', pe.apellidos, pe.nombres) AS proveedor, e.nombre_empresa, e.RUC,

        GROUP_CONCAT(i.nombre_insumo SEPARATOR ', ') AS insumos,
        GROUP_CONCAT(d.stock_insumo SEPARATOR ', ') AS cantidades,
        GROUP_CONCAT(d.precio_insumo SEPARATOR ', ') AS precios,
        GROUP_CONCAT(d.stock_insumo * d.precio_insumo SEPARATOR ', ') AS subtotales,
        GROUP_CONCAT(d.fecha_ven_insumo SEPARATOR ', ') AS fechas_vencimiento
    FROM compras AS c
    LEFT JOIN detalle_compras AS d ON c.idcompra = d.fk_idcompra
    INNER JOIN insumos AS i ON i.idinsumo = d.fk_idinsumo
    INNER JOIN tipo_comprobantes AS t ON t.idtipo_comprobante = c.fk_idtipo_comprobante
    INNER JOIN proveedores AS p ON p.idproveedor = c.fk_idproveedor
    INNER JOIN empresas AS e ON e.idempresa = p.fk_idempresa
    INNER JOIN personas AS pe ON pe.dni = p.fk_dniP
    WHERE c.cond_comp = 0 
    AND (c.num_comprobanteC LIKE ? OR e.nombre_empresa LIKE ? OR pe.dni LIKE ? OR CONCAT_WS(' ', pe.apellidos, pe.nombres) LIKE ?)
    GROUP BY c.idcompra;";
    
    $stmt = $conexion->prepare($sql);
    $dto = "%$dto%";
    $stmt->bind_param('ssss', $dto, $dto, $dto, $dto);
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
                            <th scope='col'>Transporte</th>
                            <th scope='col'>Monto</th>
                            <th scope='col'>Proveedor</th>
                            <th scope='col'>Empresa</th>
                            <th scope='col'>Detalle de la Compra</th>
                            <th scope='col' class='text-end'>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>"
    );

    if ($resultado->num_rows > 0) {
        $item = 0;
        while ($row = $resultado->fetch_assoc()) {
            $item++;
            $insumos = explode(", ", $row['insumos']);
            $cantidades = explode(", ", $row['cantidades']);
            $precios = explode(", ", $row['precios']);
            $subtotales = explode(", ", $row['subtotales']);
            $fechas = explode(", ", $row['fechas_vencimiento']);

            echo (
                "<tr>      
                    <td>" . $item . "</td>
                    <td>" . htmlspecialchars($row['comprobante'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . htmlspecialchars($row['transporte'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . number_format($row['monto_ingresoC'], 2) . "</td>
                    <td>" . htmlspecialchars($row['proveedor'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . htmlspecialchars($row['nombre_empresa'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>
                        <table class='table'>
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                    <th>Fecha de caducidad</th>
                                </tr>
                            </thead>
                            <tbody>"
            );

            for ($i = 0; $i < count($insumos); $i++) {
                echo (
                    "<tr>
                        <td>" . htmlspecialchars($insumos[$i], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>" . htmlspecialchars($cantidades[$i], ENT_QUOTES, 'UTF-8') . "</td>
                        <td>" . number_format($precios[$i], 2) . "</td>
                        <td>" . number_format($subtotales[$i], 2) . "</td>
                        <td>" . htmlspecialchars($fechas[$i], ENT_QUOTES, 'UTF-8') . "</td>
                    </tr>"
                );
            }

            echo (
                "</tbody>
                        </table>
                    </td>
                    <td class='text-end'>
                        <div class='btn-group'>
                            <button onclick=\"editar('" . $row['idcompra'] . "')\" 
                                    title='Editar datos' 
                                    type='button' 
                                    class='btn btn-warning'>
                                <i class='fas fa-edit'></i>
                            </button>
                            <button onclick=\"eliminar('" . $row['idcompra'] . "')\" 
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





function registrarDato($comprobante, $numero, $proveedor, $monto, $transporte)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("call p_compra( ?, ?, ?, ?, ?, 1, 1);");
        $stmt->bind_param("sssss", $monto, $numero, $transporte, $comprobante, $proveedor);
        if ($stmt->execute()) {
            return "Registro correcto";
        } else {
            return "No se pudo registrar: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error al registrar el dato: " . $e->getMessage();
    }
}



function registrarDetalleDato($monto, $numero, $comprobante, $cantidad, $precio, $fecha, $insumo)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("call p_detalle_compra( ?, ?, ?, ?, ?, ?, ?, 1, 1);");
        $stmt->bind_param("sssssss", $monto, $numero, $comprobante, $cantidad, $precio, $fecha, $insumo);
        if ($stmt->execute()) {
            return "Registro correcto";
        } else {
            return "No se pudo registrar: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error al registrar el dato: " . $e->getMessage();
    }
}


function modificarDato($dni, $usuario, $psw)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("call p_usuarios( ?, ?, ?, 2);");
        $stmt->bind_param("sss", $dni, $usuario, $psw);

        if ($stmt->execute()) {
            return "Modificación correcta";
        } else {
            return "No se pudo modificar: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error al modificar el dato: " . $e->getMessage();
    }
}


function eliminarDato($idcatalogo, $idinsumo)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("DELETE FROM detalle_insumos WHERE fk_idcatalogoD = ? AND fk_idinsumoD = ?");
        $stmt->bind_param("ii", $idcatalogo, $idinsumo);

        if ($stmt->execute()) {
            return "Eliminación correcta";
        } else {
            return "No se pudo eliminar: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error al eliminar el dato: " . $e->getMessage();
    }
}









function filtradorDatoInsumos($dto)
{
    global $conexion;

    try {
        $sql = "SELECT idinsumo, nombre_insumo from insumos
            WHERE nombre_insumo LIKE CONCAT('%', ?, '%');";

        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("s", $dto);
            $stmt->execute();
            $resultado = $stmt->get_result();

            $data = [];
            while ($row = $resultado->fetch_assoc()) {
                $data[] = [
                    "val1" => $row['idinsumo'],
                    "val2" => $row['nombre_insumo']
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


function filtradorDatoProveedor($dto)
{
    global $conexion;

    try {
        $sql = "SELECT dni, concat_ws(' ',apellidos,nombres) as 'proveedor', RUC, nombre_empresa
                from proveedores as po
                inner join empresas as e on e.idempresa=po.fk_idempresa
                inner join personas as p on p.dni=po.fk_dniP
            WHERE cond=0 AND (concat_ws(' ',apellidos,nombres) LIKE CONCAT('%', ?, '%') OR dni LIKE CONCAT('%', ?, '%'));";

        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("ss", $dto, $dto);
            $stmt->execute();
            $resultado = $stmt->get_result();

            $data = [];
            while ($row = $resultado->fetch_assoc()) {
                $data[] = [
                    "val1" => $row['dni'],
                    "val2" => $row['proveedor'],
                    "val3" => $row['RUC'],
                    "val4" => $row['nombre_empresa']
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
            echo registrarDato(
                $_POST['comprobante'],
                $_POST['numero'],
                $_POST['proveedor'],
                $_POST['monto'],
                $_POST['transporte']
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
                echo filtradorDatoInsumos($entrada);
            } else {
                echo json_encode(["success" => false, "message" => "Falta el parámetro 'entrada'."]);
            }
            break;
        case 5:
            if (isset($_POST['entrada'])) {
                $entrada = trim($_POST['entrada']);
                echo filtradorDatoProveedor($entrada);
            } else {
                echo json_encode(["success" => false, "message" => "Falta el parámetro 'entrada'."]);
            }
            break;

        case 6:
            echo registrarDetalleDato(
                $_POST['monto'],
                $_POST['numero'],
                $_POST['comprobante'],
                $_POST['cantidad'],
                $_POST['precio'],
                $_POST['fecha'],
                $_POST['insumo']
            );
            break;
    }
}
