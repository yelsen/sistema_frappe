<?php
include("../modelo/conexion.php");


function mostrarDato($dto)
{
    global $conexion;
    $sql = " SELECT c.idcatalogo, 
        GROUP_CONCAT(d.fk_idinsumoD ORDER BY d.fk_idinsumoD SEPARATOR ',') AS 'idinsumo', 
        CONCAT(ca.categoria, ' - ', s.sabor, ' (', p.presentacion, ')') AS 'producto',
        GROUP_CONCAT(i.nombre_insumo ORDER BY i.nombre_insumo SEPARATOR ', ') AS 'insumos',
        GROUP_CONCAT(d.cantidad_usada ORDER BY i.nombre_insumo SEPARATOR ', ') AS 'cantidades'
    FROM catalogos AS c
    INNER JOIN categorias AS ca ON ca.idcategoria = c.fk_idcategoria
    INNER JOIN sabores AS s ON s.idsabor = c.fk_idsabor
    INNER JOIN presentaciones AS p ON p.idpresentacion = c.fk_idpresentacion
    LEFT JOIN detalle_insumos AS d ON c.idcatalogo = d.fk_idcatalogoD
    LEFT JOIN insumos AS i ON i.idinsumo = d.fk_idinsumoD
    WHERE c.cond_cat = 0 AND CONCAT(ca.categoria, ' - ', s.sabor, ' (', p.presentacion, ')') LIKE '%" . $dto . "%'
    GROUP BY c.idcatalogo, ca.categoria, s.sabor, p.presentacion;";

    $resultado = $conexion->query($sql);
    echo (
        "<div class='invoice-add-table'>
            <div class='table-responsive'>
                <table class='table table-hover'>
                    <thead>
                        <tr>
                            <th scope='col'>N°</th>
                            <th scope='col'>Producto</th>
                            <th scope='col'>Lista de Insumos</th>
                            <th scope='col' class='text-end'>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>"
    );

    if ($resultado) {
        $item = 0;
        while ($row = $resultado->fetch_assoc()) {
            $item++;
            $idinsumo = htmlspecialchars($row['idinsumo'], ENT_QUOTES, 'UTF-8');
            $insumos = explode(", ", $row['insumos']);
            $cantidades = explode(", ", $row['cantidades']);

            echo (
                "<tr id='" . $row['idcatalogo'] . "'>      
                    <td>" . $item . "</td>
                    <td>" . $row['producto'] . "</td>
                    <td>
                        <table class='table'>
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>"
            );

            for ($i = 0; $i < count($insumos); $i++) {
                echo (
                    "<tr id='" . $idinsumo[$i] . "'>
                        <td>" . $insumos[$i] . "</td>
                        <td>" . $cantidades[$i] . "</td>
                    </tr>"
                );
            }

            echo (
                "</tbody>
                        </table>
                    </td>
                    <td class='text-end'>
                        <div class='btn-group'>"
            );

            if (!empty($row['idinsumo'])) {
                echo (
                    "<button onclick=\"eliminar('" . $row['idcatalogo'] . "', '" . $idinsumo . "')\" 
                            title='Eliminar datos' 
                            type='button' 
                            class='btn btn-danger'>
                        <i class='fas fa-trash-alt'></i>
                    </button>"
                );
            } else {
                echo (
                    "<button disabled title='Sin insumos para eliminar' 
                            type='button' 
                            class='btn btn-secondary'>
                        <i class='fas fa-trash-alt'></i>
                    </button>"
                );
            }

            echo (
                "</div>
                    </td>
                </tr>"
            );
        }
    } else {
        echo ("<tr><td colspan='5' class='text-center'>No se encontraron datos</td></tr>");
    }

    echo ("</tbody></table></div></div>");
}





function registrarDato($insumo, $cantidad, $producto)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("call p_detalle_insumos( ?, ?, ?, 1);");
        $stmt->bind_param("sss", $insumo, $producto, $cantidad);
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









function filtradorDato($dto)
{
    global $conexion;

    try {
        $sql = "SELECT  idcategoria, CONCAT(categoria, ' - ', sabor, ' (', presentacion, ')') AS 'product' from catalogos as c
                    inner join categorias as ca on ca.idcategoria=c.fk_idcategoria
                    inner join sabores as s on s.idsabor=c.fk_idsabor
                    inner join presentaciones as p on p.idpresentacion=c.fk_idpresentacion
                    WHERE cond_cat=0 AND (CONCAT(categoria, ' - ', sabor, ' (', presentacion, ')') LIKE CONCAT('%', ?, '%') );";

        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("s", $dto);
            $stmt->execute();
            $resultado = $stmt->get_result();

            $data = [];
            while ($row = $resultado->fetch_assoc()) {
                $data[] = [
                    "val1" => $row['idcategoria'],
                    "val2" => $row['product']
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
        $sql = "SELECT idinsumo, nombre_insumo FROM insumos";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'val1' => $row['idinsumo'],
                'val2' => $row['nombre_insumo']
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
                $_POST['insumo'],
                $_POST['cantidad'],
                $_POST['producto']
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
                echo filtradorDato($entrada);
            } else {
                echo json_encode(["success" => false, "message" => "Falta el parámetro 'entrada'."]);
            }
            break;
    }
}
