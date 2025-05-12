<?php
$host = "localhost";
$usuario = "root";
$contrasena = "";
$basededatos = "encuestasbd";

$conn = new mysqli($host, $usuario, $contrasena, $basededatos);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
