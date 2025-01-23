<?php

$servidor = "localhost";
$usuario = "root";
$password = "root";
$basededatos = "bdfrappe";

$conexion = new mysqli($servidor, $usuario, $password, $basededatos);
//$conexion = mysqli_connect($servidor, $usuario, $password, $basededatos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
    die("Error al conectar a la Base de Datos: " . mysqli_connect_error());
}
$conexion->set_charset("utf8");
?>

