<?php
include("../modelo/conexion.php");

function mostrarDato($dto)
{
    global $conexion;
    $sql = "SELECT * FROM presentaciones WHERE presentacion LIKE '%" . $dto . "%'";
    $resultado = $conexion->query($sql);
    echo (
        "<div class='invoice-add-table'>
            <div class='table-responsive'>
                <table class='table table-hover'>
                    <thead>
                        <tr>
                            <th scope='col'>N°</th>
                            <th scope='col'>Presentaciones</th>
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
                "<tr id='" . $row['idpresentacion'] . "' onclick='seleccion(this.id)'>      
                    <td>" .  $item . "</td>
                    <td>" . $row['presentacion'] . "</td>
                    <td class='text-end'>
                        <div class='btn-group'>
                           <button onclick=\"verDetalle('" . $row['idpresentacion'] . "')\" title='Ver detalles' type='button' class='btn btn-success'><i class='fas fa-eye'></i></button>
                           <button onclick=\"verEditar('" . $row['idpresentacion'] . "')\" title='Editar datos' type='button' class='btn btn-warning'><i class='fas fa-pencil-alt'></i></button>
                           <button onclick=\"eliminar('" . $row['idpresentacion'] . "')\" title=Eliminar datos' type='button' class='btn btn-danger'><i class='fas fa-trash-alt'></i></button>
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

function registrarDato($roles)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("INSERT INTO presentaciones(presentacion) VALUES (?)");
        $stmt->bind_param("s", $roles);
        if ($stmt->execute()) {
            return "Registro correcto";
        } else {
            return "No se pudo registrar: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error al registrar el dato: " . $e->getMessage();
    }
}


function modificarDato($idpresentacion, $presentaciones)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("UPDATE presentaciones SET presentacion = ? WHERE idpresentacion = ?");
        $stmt->bind_param("si", $presentaciones, $idpresentacion);

        if ($stmt->execute()) {
            return "Modificación correcta";
        } else {
            return "No se pudo modificar: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error al modificar el dato: " . $e->getMessage();
    }
}

function eliminarDato($id)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("DELETE FROM presentaciones WHERE idpresentacion = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            return "Eliminación correcta";
        } else {
            return "No se pudo eliminar: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error al eliminar el dato: " . $e->getMessage();
    }
}



$event = $_POST['ev'];
switch ($event) {
    case 0: {
            mostrarDato($_POST['dt']);
            break;
        }
    case 1: {
            echo registrarDato(
                $_POST['presentaciones']
            );
            break;
        }
    case 2: {
            echo modificarDato(
                $_POST['idpresentacion'],
                $_POST['presentaciones']
            );
            break;
        }

    case 3: {
            echo eliminarDato(
                $_POST['idpresentacion']
            );
            break;
        }
}

?>