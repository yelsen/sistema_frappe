<?php
include("../modelo/conexion.php");

function mostrarDato($dto)
{
    global $conexion;
    $sql = "SELECT dni, concat_ws(' ',apellidos,nombres) as persona,usuario, psswrd
    from usuarios as u
    inner join personas as p on p.dni=u.fk_dniU
    where cond='0' and (concat_ws(' ',apellidos,nombres) LIKE '%" . $dto . "%' OR dni LIKE '%" . $dto . "%' OR usuario LIKE '%" . $dto . "%');";


    $resultado = $conexion->query($sql);
    echo (
        "<div class='invoice-add-table'>
            <div class='table-responsive'>
                <table class='table table-hover'>
                    <thead>
                        <tr>
                            <th scope='col'>N°</th>
                            <th scope='col'>DNI</th>
                            <th scope='col'>Apellidos y Nombres</th>
                            <th scope='col'>Usuario</th>
                            <th scope='col'>Contraseña</th>
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
                "<tr id='" . $row['dni'] . "'>      
                    <td>" .  $item . "</td>
                    <td>" . $row['dni'] . "</td>
                    <td>" . $row['persona'] . "</td>
                    <td>" . $row['usuario'] . "</td>
                    <td>" . $row['psswrd'] . "</td>
                    <td class='text-end'>
                        <div class='btn-group'>
                           <button onclick=\"verDetalle('" . $row['dni'] . "')\" title='Ver detalles' type='button' class='btn btn-success'><i class='fas fa-eye'></i></button>
                           <button onclick=\"verEditar('" . $row['dni'] . "')\" title='Editar datos' type='button' class='btn btn-warning'><i class='fas fa-pencil-alt'></i></button>
                           <button onclick=\"eliminar('" . $row['dni'] . "')\" title=Eliminar datos' type='button' class='btn btn-danger'><i class='fas fa-trash-alt'></i></button>
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

function registrarDato($dni, $usuario, $psw)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("call p_usuarios( ?, ?, ?, 1);");
        $stmt->bind_param("sss", $dni, $usuario, $psw);
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

function eliminarDato($dni)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("call p_usuarios( ?, '', '', 3);");
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






function filtradorDato($dto) {
    global $conexion;

    try {
        $sql = "SELECT dni, apellidos, nombres
                FROM personas
                WHERE dni LIKE CONCAT('%', ?, '%')
                   OR CONCAT_WS(' ', apellidos, nombres) LIKE CONCAT('%', ?, '%');";

        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("ss", $dto, $dto);
            $stmt->execute();
            $resultado = $stmt->get_result();

            $data = [];
            while ($row = $resultado->fetch_assoc()) {
                $data[] = [
                    "dni" => $row['dni'],
                    "apellidos" => $row['apellidos'],
                    "nombres" => $row['nombres']
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
                $_POST['usuario'], 
                $_POST['psw']
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

        default:
            echo json_encode(["success" => false, "message" => "Evento no válido."]);
            break;
    }
} else {
    echo json_encode(["success" => false, "message" => "No se especificó ningún evento."]);
}


?>
