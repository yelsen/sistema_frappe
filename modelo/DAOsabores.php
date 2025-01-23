<?php
include("../modelo/conexion.php");

function mostrarDato($dto)
{
    global $conexion;
    $sql = "SELECT * FROM sabores WHERE sabor LIKE '%" . $dto . "%'";
    $resultado = $conexion->query($sql);
    echo (
        "<div class='invoice-add-table'>
            <div class='table-responsive'>
                <table class='table table-hover'>
                    <thead>
                        <tr>
                            <th scope='col'>N°</th>
                            <th scope='col'>Sabores</th>
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
                "<tr id='" . $row['idsabor'] . "' onclick='seleccion(this.id)'>      
                    <td>" .  $item . "</td>
                    <td>" . $row['sabor'] . "</td>
                    <td class='text-end'>
                        <div class='btn-group'>
                           <button onclick=\"verDetalle('" . $row['idsabor'] . "')\" title='Ver detalles' type='button' class='btn btn-success'><i class='fas fa-eye'></i></button>
                           <button onclick=\"verEditar('" . $row['idsabor'] . "')\" title='Editar datos' type='button' class='btn btn-warning'><i class='fas fa-pencil-alt'></i></button>
                           <button onclick=\"eliminar('" . $row['idsabor'] . "')\" title=Eliminar datos' type='button' class='btn btn-danger'><i class='fas fa-trash-alt'></i></button>
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




function registrarDato($sabores)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("INSERT INTO sabores(sabor) VALUES (?)");
        $stmt->bind_param("s", $sabores);
        if ($stmt->execute()) {
            return "Registro correcto";
        } else {
            return "No se pudo registrar: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error al registrar el dato: " . $e->getMessage();
    }
}


function modificarDato($id, $sabores)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("UPDATE sabores SET sabor = ? WHERE idsabor = ?");
        $stmt->bind_param("si", $sabores, $id);

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
        $stmt = $conexion->prepare("DELETE FROM sabores WHERE idsabor = ?");
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
                $_POST['sabores']
            );
            break;
        }
    case 2: {
            echo modificarDato(
                $_POST['idsabor'],
                $_POST['sabores']
            );
            break;
        }

    case 3: {
            echo eliminarDato(
                $_POST['idsabor']
            );
            break;
        }
}
