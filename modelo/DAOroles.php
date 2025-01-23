<?php
include("../modelo/conexion.php");

function mostrarDato($dto)
{
    global $conexion;
    $sql = "SELECT * FROM roles WHERE nombre_rol LIKE '%" . $dto . "%'";
    $resultado = $conexion->query($sql);
    echo (
        "<div class='invoice-add-table'>
            <div class='table-responsive'>
                <table class='table table-hover'>
                    <thead>
                        <tr>
                            <th scope='col'>N°</th>
                            <th scope='col'> Roles</th>
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
                "<tr id='" . $row['idrol'] . "' onclick='seleccion(this.id)'>      
                    <td>" .  $item . "</td>
                    <td>" . $row['nombre_rol'] . "</td>
                    <td class='text-end'>
                        <div class='btn-group'>
                           <button onclick=\"verDetalle('" . $row['idrol'] . "')\" title='Ver detalles' type='button' class='btn btn-success'><i class='fas fa-eye'></i></button>
                           <button onclick=\"verEditar('" . $row['idrol'] . "')\" title='Editar datos' type='button' class='btn btn-warning'><i class='fas fa-pencil-alt'></i></button>
                           <button onclick=\"eliminar('" . $row['idrol'] . "')\" title=Eliminar datos' type='button' class='btn btn-danger'><i class='fas fa-trash-alt'></i></button>
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
        $stmt = $conexion->prepare("INSERT INTO roles(nombre_rol) VALUES (?)");
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


function modificarDato($id, $roles)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("UPDATE roles SET nombre_rol = ? WHERE idrol = ?");
        $stmt->bind_param("si", $roles, $id);

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
        $stmt = $conexion->prepare("DELETE FROM roles WHERE idrol = ?");
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
                $_POST['roles']
            );
            break;
        }
    case 2: {
            echo modificarDato(
                $_POST['idrol'],
                $_POST['roles']
            );
            break;
        }

    case 3: {
            echo eliminarDato(
                $_POST['idrol']
            );
            break;
        }
}
?>