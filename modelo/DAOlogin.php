<?php
include '../modelo/conexion.php'; // Asegúrate de que esta ruta sea correcta

function verificarUsuario($usuario, $psswrd)
{
    global $conexion;
    try {
        // Consulta SQL preparada
        $stmt = $conexion->prepare(
            "SELECT idusuario, usuario, psswrd, dni, apellidos, nombres 
             FROM usuarios AS u
             INNER JOIN personas AS p ON p.dni = u.fk_dniU
             WHERE usuario = ? AND psswrd = ?"
        );

        // Vincular parámetros
        $stmt->bind_param("ss", $usuario, $psswrd);

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener resultados
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            // Usuario encontrado
            return $result->fetch_assoc(); // Devuelve los datos del usuario
        } else {
            return null; // Usuario no encontrado
        }
    } catch (Exception $e) {
        die("Error al verificar usuario: " . $e->getMessage());
    }
}

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $psswrd = isset($_POST['psswrd']) ? trim($_POST['psswrd']) : '';

    if (!empty($usuario) && !empty($psswrd)) {
        // Verificar credenciales
        $user = verificarUsuario($usuario, $psswrd);

        if ($user) {
            // Iniciar sesión
            session_start();
            $_SESSION['apellidos'] = $user['apellidos'];
            $_SESSION['nombres'] = $user['nombres'];

            // Redirigir al menú principal
            header("Location: ../vista/menu.php");
            exit();
        } else {
            // Credenciales incorrectas
            echo "<script>alert('Usuario o contraseña incorrectos.'); window.location.href='../vista/login.php';</script>";
        }
    } else {
        echo "<script>alert('Por favor, complete todos los campos.'); window.location.href='../vista/login.php';</script>";
    }
} else {
    // Acceso no permitido
    header("Location: ../vista/login.php");
    exit();
}
