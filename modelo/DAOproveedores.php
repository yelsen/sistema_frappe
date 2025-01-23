<?php
include("../modelo/conexion.php");

function mostrarDato($dto)
{
    global $conexion;
    $sql = "SELECT idproveedor, dni, concat_ws(' ',apellidos,nombres) as 'proveedor', telefono, nombre_empresa, RUC, direccion, telefono_em
 from proveedores as p
 inner join personas as pe on pe.dni=p.fk_dniP
 inner join empresas as e on e.idempresa=p.fk_idempresa
 WHERE cond='0' and (dni LIKE '%" . $dto . "%'  or concat_ws(' ',apellidos,nombres) LIKE '%" . $dto . "%' or telefono LIKE '%" . $dto . "%' or nombre_empresa LIKE '%" . $dto . "%' or RUC LIKE '%" . $dto . "%')";

    $resultado = $conexion->query($sql);
    echo (
        "<div class='invoice-add-table'>
            <div class='table-responsive'>
                <table class='table table-hover'>
                    <thead>
                        <tr>
                            <th scope='col'>N°</th>
                            <th scope='col'>DNI</th>
                            <th scope='col'>Proveedor</th>
                            <th scope='col'>Telefono</th>
                            <th scope='col'>Nombre de la Empresa</th>
                            <th scope='col'>RUC</th>
                            <th scope='col'>Dirección de la Empresa</th>
                            <th scope='col'>Teléfono de la Empresa</th>
                            <th scope='col' class='text-end'>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>"
    );
    if ($resultado) {
        $item = 0;
        while ($row = $resultado->fetch_assoc()) {
            $item++;
            echo (
                "<tr id='" . $row['idproveedor'] . "'>      
                    <td>" .  $item . "</td>
                    <td>" . $row['dni'] . "</td>
                    <td>" . $row['proveedor'] . "</td>
                    <td>" . $row['telefono'] . "</td>
                    <td>" . $row['nombre_empresa'] . "</td>
                    <td>" . $row['RUC'] . "</td>
                    <td>" . $row['direccion'] . "</td>
                    <td>" . $row['telefono_em'] . "</td>
                    <td class='text-end'>
                        <div class='btn-group'>
                           <button onclick=\"verDetalle('" . $row['idproveedor'] . "')\" title='Ver detalles' type='button' class='btn btn-success'><i class='fas fa-eye'></i></button>
                           <button onclick=\"verEditar('" . $row['idproveedor'] . "')\" title='Editar datos' type='button' class='btn btn-warning'><i class='fas fa-pencil-alt'></i></button>
                           <button onclick=\"eliminar('" . $row['idproveedor'] . "')\" title=Eliminar datos' type='button' class='btn btn-danger'><i class='fas fa-trash-alt'></i></button>
                        </div>
                    </td>
                </tr>"
            );
        }
    } else {
        echo ("<tr><td colspan='3' class='text-center'>No se encontraron datos</td></tr>");
    }
    echo ("</tbody></table></div></div>");
}

function registrarDato($dni, $apellidos, $nombres, $telefono, $empresa)
{
    global $conexion;

    try {
        $stmt = $conexion->prepare("call p_proveedor( ?, ?, ?, ?, ?, 1);");
        $stmt->bind_param("sssss", $dni, $apellidos, $nombres, $telefono, $empresa);
        if ($stmt->execute()) {
            return "Registro correcto";
        } else {
            return "No se pudo registrar: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error al registrar el dato: " . $e->getMessage();
    }
}


function modificarDato($dni, $apellidos, $nombres, $telefono, $empresa)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("call p_proveedor( ?, ?, ?, ?, ?, 2);");
        $stmt->bind_param("sssss", $dni, $apellidos, $nombres, $telefono, $empresa);
        if ($stmt->execute()) {
            return "Modificación correcta";
        } else {
            return "No se pudo modificar: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error al modificar el dato: " . $e->getMessage();
    }
}

function eliminarDato($dni)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("call p_proveedor( ?, '', '', '', '', 3);");
        $stmt->bind_param("s", $dni);
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
        $sql = "SELECT nombre_empresa, RUC FROM empresas 
                WHERE  nombre_empresa LIKE CONCAT('%', ?, '%') OR RUC LIKE CONCAT('%', ?, '%')";

        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("ss", $dto, $dto);
            $stmt->execute();
            $resultado = $stmt->get_result();

            $data = [];
            while ($row = $resultado->fetch_assoc()) {
                $data[] = [
                    "nombre_empresa" => $row['nombre_empresa'],
                    "RUC" => $row['RUC']
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


function seleccionarDato($idp){
    global $conexion;
    try {
        $query = "SELECT dni, apellidos, nombres, telefono, nombre_empresa, RUC, direccion, telefono_em
                    from proveedores as p
                    inner join personas as pe on pe.dni=p.fk_dniP
                    inner join empresas as e on e.idempresa=p.fk_idempresa
                WHERE idproveedor = ? ";

        if ($stmt = $conexion->prepare($query)) {
            $stmt->bind_param("i", $idp);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                $row = $resultado->fetch_assoc();
                echo json_encode([
                    "success" => true,
                    "dni" => $row['dni'],
                    "apellidos" => $row['apellidos'],
                    "nombres" => $row['nombres'],
                    "telefono" => $row['telefono'],
                    "nombre_empresa" => $row['nombre_empresa'],
                    "RUC" => $row['RUC'],
                    "direccion" => $row['direccion'],
                    "telefono_em" => $row['telefono_em']
                ]);
            } else {
                echo json_encode(["success" => false, "message" => "No se encontraron datos para el DNI proporcionado."]);
            }
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "message" => "Error al preparar la consulta"]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error en la consulta: " . $e->getMessage()]);
    }
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
                $_POST['dni'],
                $_POST['apellidos'],
                $_POST['nombres'],
                $_POST['telefono'],
                $_POST['empresa']
            );
            break;

        case 2:
            echo modificarDato(
                $_POST['dni'],
                $_POST['apellidos'],
                $_POST['nombres'],
                $_POST['telefono'],
                $_POST['empresa']
            );
            break;

        case 3:
            echo eliminarDato(
                $_POST['dni']
            );
            break;

        case 4:
            if (isset($_POST['entrada'])) {
                $entrada = trim($_POST['entrada']);
                echo filtradorDato($entrada);
            } else {
                echo json_encode(["success" => false, "message" => "Falta el parámetro 'entrada'."]);
            }
            break;

        case 5: {
                if (isset($_POST['idp'])) {
                    $idp = $_POST['idp'];
                    seleccionarDato($idp);
                }
                break;
            }
    }
}
