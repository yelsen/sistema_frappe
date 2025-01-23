<?php
include("../modelo/conexion.php");

function mostrarDato($dto)
{
    global $conexion;
    $sql = "SELECT * FROM tipo_comprobantes WHERE tipo_comprobante LIKE '%" . $dto . "%'";
    $resultado = $conexion->query($sql);
    echo (
        "<div class='invoice-add-table'>
            <div class='table-responsive'>
                <table class='table table-hover'>
                    <thead>
                        <tr>
                            <th scope='col'>N°</th>
                            <th scope='col'>Tipo de Comprobante</th>
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
                "<tr id='" . $row['idtipo_comprobante'] . "' onclick='seleccion(this.id)'>      
                    <td>" .  $item . "</td>
                    <td>" . $row['tipo_comprobante'] . "</td>
                    <td class='text-end'>
                        <div class='btn-group'>
                           <button onclick=\"verDetalle('" . $row['idtipo_comprobante'] . "')\" title='Ver detalles' type='button' class='btn btn-success'><i class='fas fa-eye'></i></button>
                           <button onclick=\"verEditar('" . $row['idtipo_comprobante'] . "')\" title='Editar datos' type='button' class='btn btn-warning'><i class='fas fa-pencil-alt'></i></button>
                           <button onclick=\"eliminar('" . $row['idtipo_comprobante'] . "')\" title=Eliminar datos' type='button' class='btn btn-danger'><i class='fas fa-trash-alt'></i></button>
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

function registrarDato($tipo_comprobante)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("INSERT INTO tipo_comprobantes(tipo_comprobante) VALUES (?)");
        $stmt->bind_param("s", $tipo_comprobante);
        if ($stmt->execute()) {
            return "Registro correcto";
        } else {
            return "No se pudo registrar: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error al registrar el dato: " . $e->getMessage();
    }
}


function modificarDato($id, $tipo_comprobante)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("UPDATE tipo_comprobantes SET tipo_comprobante = ? WHERE idtipo_comprobante = ?");
        $stmt->bind_param("si", $tipo_comprobante, $id);

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
        $stmt = $conexion->prepare("DELETE FROM tipo_comprobantes WHERE idtipo_comprobante = ?");
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
                $_POST['tipo_comprobante']
            );
            break;
        }
    case 2: {
            echo modificarDato(
                $_POST['idtipo_comprobante'],
                $_POST['tipo_comprobante']
            );
            break;
        }

    case 3: {
            echo eliminarDato(
                $_POST['idtipo_comprobante']
            );
            break;
        }
}
