<?php
include("../modelo/conexion.php");

function mostrarDato($dto)
{
    global $conexion;
    $sql = "SELECT dni, concat_ws(' ', apellidos, nombres) as personal, telefono, nombre_rol 
    FROM personales as pe
    INNER JOIN personas as p ON pe.fk_dniPE = p.dni
    INNER JOIN roles as r ON r.idrol = pe.fk_idrol
    WHERE cond='0' and (dni LIKE '%" . $dto . "%' OR concat_ws(' ', apellidos, nombres) LIKE '%" . $dto . "%' OR telefono LIKE '%" . $dto . "%' OR nombre_rol LIKE '%" . $dto . "%')";

    $resultado = $conexion->query($sql);
    echo (
        "<div class='invoice-add-table'>
            <div class='table-responsive'>
                <table class='table table-hover'>
                    <thead>
                        <tr>
                            <th scope='col'>N°</th>
                            <th scope='col'>DNI</th>
                            <th scope='col'>Personales</th>
                            <th scope='col'>Telefonos</th>
                            <th scope='col'>Roles</th>
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
                    <td>" . $row['personal'] . "</td>
                    <td>" . $row['telefono'] . "</td>
                    <td>" . $row['nombre_rol'] . "</td>
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

function registrarDato($dni, $apellidos, $nombres, $telefono, $rol)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("CALL p_personales(?, ?, ?, ?, ?, 1);");
        $stmt->bind_param("sssss", $dni, $apellidos, $nombres, $telefono, $rol);
        if ($stmt->execute()) {
            return "Registro correcto";
        } else {
            return "No se pudo registrar: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error al registrar el dato: " . $e->getMessage();
    }
}


function modificarDato($dni,$apellidos,$nombres,$telefono,$rol)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("CALL p_personales(?, ?, ?, ?, ?, 2);");
        $stmt->bind_param("sssss", $dni, $apellidos, $nombres, $telefono, $rol);

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
        $stmt = $conexion->prepare("call p_personales( ? , '', '', '', null, 3);");
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

function seleccionarDato($dni)
{
    global $conexion;
    try {
        $query = "SELECT p.dni, p.apellidos, p.nombres, p.telefono, idrol, nombre_rol, fecha_ingresoP
                    FROM personales as pe
                    INNER JOIN personas as p ON pe.fk_dniPE = p.dni
                    INNER JOIN roles as r ON r.idrol = pe.fk_idrol
                where p.dni = ?";

        if ($stmt = $conexion->prepare($query)) {
            $stmt->bind_param("s", $dni);
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
                    "idrol" => $row['idrol'],
                    "nombre_rol" => $row['nombre_rol'],
                    "fecha_ingresoP" => $row['fecha_ingresoP']
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





function obtenerOpciones(){
    global $conexion;
    try {
        $sql = "SELECT idrol, nombre_rol FROM roles";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'val1' => $row['idrol'],
                'val2' => $row['nombre_rol']
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

$event = $_POST['ev'];
switch ($event) {
    case 0: {
            echo mostrarDato($_POST['dt']);
            break;
        }
    case 1: {
            echo registrarDato(
                $_POST['dni'],
                $_POST['apellidos'],
                $_POST['nombres'],
                $_POST['telefono'],
                $_POST['rol']
            );
            break;
        }
    case 2: {
            echo modificarDato(
                $_POST['dni'],
                $_POST['apellidos'],
                $_POST['nombres'],
                $_POST['telefono'],
                $_POST['rol']
            );
            break;
        }
    case 3: {
            echo eliminarDato(
                $_POST['dni']
            );
            break;
        }
    case 4: {
            if (isset($_POST['dni'])) {
                $dni = $_POST['dni'];
                seleccionarDato($dni);
            } 
            break;
        }
        
}
