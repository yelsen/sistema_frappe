<?php
include("../modelo/conexion.php");

function mostrarDato($dto)
{
    global $conexion;
    $sql = "SELECT * FROM empresas WHERE nombre_empresa LIKE '%" . $dto . "%' or RUC LIKE '%" . $dto . "%' or direccion LIKE '%" . $dto . "%' OR telefono_em LIKE '%" . $dto . "%'";
    $resultado = $conexion->query($sql);
    echo (
        "<div class='invoice-add-table'>
            <div class='table-responsive'>
                <table class='table table-hover'>
                    <thead>
                        <tr>
                            <th scope='col'>N°</th>
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
                "<tr id='" . $row['idempresa'] . "' onclick='seleccion(this.id)'>      
                    <td>" .  $item . "</td>
                    <td>" . $row['nombre_empresa'] . "</td>
                    <td>" . $row['RUC'] . "</td>
                    <td>" . $row['direccion'] . "</td>
                    <td>" . $row['telefono_em'] . "</td>
                    <td class='text-end'>
                        <div class='btn-group'>
                           <button onclick=\"verDetalle('" . $row['idempresa'] . "')\" title='Ver detalles' type='button' class='btn btn-success'><i class='fas fa-eye'></i></button>
                           <button onclick=\"verEditar('" . $row['idempresa'] . "')\" title='Editar datos' type='button' class='btn btn-warning'><i class='fas fa-pencil-alt'></i></button>
                           <button onclick=\"eliminar('" . $row['idempresa'] . "')\" title=Eliminar datos' type='button' class='btn btn-danger'><i class='fas fa-trash-alt'></i></button>
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

function registrarDato($empresa, $ruc, $direccion, $telefono)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("INSERT INTO empresas(nombre_empresa, RUC, direccion, telefono_em) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $empresa, $ruc, $direccion, $telefono);
        if ($stmt->execute()) {
            return "Registro correcto";
        } else {
            return "No se pudo registrar: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error al registrar el dato: " . $e->getMessage();
    }
}


function modificarDato($id, $empresa, $ruc, $direccion, $telefono)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("UPDATE empresas SET nombre_empresa = ?, RUC = ?, direccion = ?, telefono_em = ? WHERE idempresa = ?");
        $stmt->bind_param("ssssi", $empresa, $ruc, $direccion, $telefono, $id);

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
        $stmt = $conexion->prepare("DELETE FROM empresas WHERE idempresa = ?");
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
                $_POST['empresa'],
                $_POST['ruc'],
                $_POST['direccion'],
                $_POST['telefono']
            );
            break;
        }
    case 2: {
            echo modificarDato(
                $_POST['id'],
                $_POST['empresa'],
                $_POST['ruc'],
                $_POST['direccion'],
                $_POST['telefono']
            );
            break;
        }

    case 3: {
            echo eliminarDato(
                $_POST['id']
            );
            break;
        }
}
?>