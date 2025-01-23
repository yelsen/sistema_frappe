<?php
include("../modelo/conexion.php");

function mostrarDato($dto)
{
    global $conexion;
    $sql = "SELECT idcatalogo, categoria ,sabor, presentacion, img
        from catalogos as c
        inner join sabores as s on s.idsabor=c.fk_idsabor
        inner join categorias as ca on ca.idcategoria=c.fk_idcategoria
        inner join presentaciones as p on p.idpresentacion=c.fk_idpresentacion
    where cond_cat='0' and (categoria LIKE '%" . $dto . "%' OR sabor LIKE '%" . $dto . "%' OR presentacion LIKE '%" . $dto . "%');";

    $resultado = $conexion->query($sql);
    echo (
        "<div class='invoice-add-table'>
            <div class='table-responsive'>
                <table class='table table-hover'>
                    <thead>
                        <tr>
                            <th scope='col'>N°</th>
                            <th scope='col'>Categoria</th>
                            <th scope='col'>Sabor</th>
                            <th scope='col'>Presentacion</th>
                            <th scope='col'>Imagen</th>
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
                "<tr id='" . $row['idcatalogo'] . "'>      
                    <td>" . $item . "</td>
                    <td>" . $row['categoria'] . "</td>
                    <td>" . $row['sabor'] . "</td>
                    <td>" . $row['presentacion'] . "</td>
                    <td class='dt-type-numeric'>
                        <img class='rounded-circle' src='../image/" . $row['img'] . "' alt='Any somosa' width='50' height='50'>
                    </td>
                    <td class='text-end'>
                        <div class='btn-group'>
                           <button onclick=\"verDetalle('" . $row['idcatalogo'] . "')\" title='Ver detalles' type='button' class='btn btn-success'><i class='fas fa-eye'></i></button>
                           <button onclick=\"verEditar('" . $row['idcatalogo'] . "')\" title='Editar datos' type='button' class='btn btn-warning'><i class='fas fa-pencil-alt'></i></button>
                           <button onclick=\"eliminar('" . $row['idcatalogo'] . "')\" title=Eliminar datos' type='button' class='btn btn-danger'><i class='fas fa-trash-alt'></i></button>
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


function registrarDato($linkImagen, $sabor, $categoria, $presentacion)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("CALL p_catalogos( ?, ?, ?, ?, 0, 1);");
        $stmt->bind_param("ssss",  $linkImagen, $sabor, $categoria, $presentacion);
        if ($stmt->execute()) {
            return "Registro correcto";
        } else {
            return "No se pudo registrar: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error al registrar el dato: " . $e->getMessage();
    }
}


function modificarDato($linkImagen, $sabor, $categoria, $presentacion, $ids)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("CALL p_catalogos( ?, ?, ?, ?, ?, 2);");
        $stmt->bind_param("sssss", $linkImagen, $sabor, $categoria, $presentacion, $ids);

        if ($stmt->execute()) {
            return "Modificación correcta";
        } else {
            return "No se pudo modificar: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error al modificar el dato: " . $e->getMessage();
    }
}

function eliminarDato($ids)
{
    global $conexion;
    try {
        $stmt = $conexion->prepare("CALL p_catalogos( null, null, null, null, ?, 3);");
        $stmt->bind_param("i", $ids);
        if ($stmt->execute()) {
            return "Eliminación correcta";
        } else {
            return "No se pudo eliminar: " . $stmt->error;
        }
    } catch (Exception $e) {
        return "Error al eliminar el dato: " . $e->getMessage();
    }
}


//
function obtenerCategorias()
{
    global $conexion;
    try {
        $sql = "SELECT idcategoria, categoria FROM categorias";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'val1' => $row['idcategoria'],
                'val2' => $row['categoria']
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

function obtenerSabores()
{
    global $conexion;
    try {
        $sql = "SELECT idsabor, sabor FROM sabores";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'val1' => $row['idsabor'],
                'val2' => $row['sabor']
            ];
        }
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['data' => $data]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener los sabores: ' . $e->getMessage()]);
    }
}

function obtenerPresentaciones()
{
    global $conexion;
    try {
        $sql = "SELECT idpresentacion, presentacion FROM presentaciones";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'val1' => $row['idpresentacion'],
                'val2' => $row['presentacion']
            ];
        }
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['data' => $data]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener las presentaciones: ' . $e->getMessage()]);
    }
}

function seleccionarDato($dni)
{
    global $conexion;
    try {
        $query = "SELECT c.idcatalogo, ca.idcategoria, ca.categoria , s.idsabor, s.sabor, p.idpresentacion, p.presentacion, c.img
                from catalogos as c
                inner join sabores as s on s.idsabor=c.fk_idsabor
                inner join categorias as ca on ca.idcategoria=c.fk_idcategoria
                inner join presentaciones as p on p.idpresentacion=c.fk_idpresentacion
        where c.idcatalogo = ?";

        if ($stmt = $conexion->prepare($query)) {
            $stmt->bind_param("s", $dni);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                $row = $resultado->fetch_assoc();
                echo json_encode([
                    "success" => true,
                    "idcategoria" => $row['idcategoria'],
                    "idsabor" => $row['idsabor'],
                    "idpresentacion" => $row['idpresentacion'],
                    "img" => $row['img'],
                    "categoria" => $row['categoria'],
                    "sabor" => $row['sabor'],
                    "presentacion" => $row['presentacion']

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



// los metodos get
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['funcion'])) {
    switch ($_GET['funcion']) {
        case 'obtenerCategorias':
            obtenerCategorias();
            break;
        case 'obtenerSabores':
            obtenerSabores();
            break;
        case 'obtenerPresentaciones':
            obtenerPresentaciones();
            break;
    }
    exit;
}


// los metodos post
switch ($_POST['ev']) {
    case 0: {
            echo mostrarDato($_POST['dt']);
            break;
        }
    case 1: {
            if (isset($_FILES['img'])) {
                $img = $_FILES['img'];
                $directorio = "../image/";
                $nombreArchivo = basename($img['name']);
                $rutaArchivo = $directorio . $nombreArchivo;
                if (move_uploaded_file($img['tmp_name'], $rutaArchivo)) {
                    echo registrarDato(
                        $nombreArchivo,
                        $_POST['sabor'],
                        $_POST['categoria'],
                        $_POST['presentacion']
                    );
                }
            }
            break;
        }
    case 2: {
            if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
                $img = $_FILES['img'];
                $directorio = "../image/";
                $nombreArchivo = basename($img['name']);
                $rutaArchivo = $directorio . $nombreArchivo;
                if (move_uploaded_file($img['tmp_name'], $rutaArchivo)) {
                    echo modificarDato(
                        $nombreArchivo,
                        $_POST['sabor'],
                        $_POST['categoria'],
                        $_POST['presentacion'],
                        $_POST['ids']
                    );
                } else {
                    echo "Error al subir la imagen.";
                }
            } else {
                echo modificarDato(
                    $_POST['img'],
                    $_POST['sabor'],
                    $_POST['categoria'],
                    $_POST['presentacion'],
                    $_POST['ids']
                );
            }
            break;
        }

    case 3: {
            echo eliminarDato(
                $_POST['ids']
            );
            break;
        }
    case 4: {
            if (isset($_POST['ids'])) {
                $ids = $_POST['ids'];
                seleccionarDato($ids);
            }
            break;
        }
}
