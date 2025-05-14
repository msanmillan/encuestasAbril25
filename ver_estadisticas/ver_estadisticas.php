<?php
session_start();
include '../conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

$sql = "
    SELECT DISTINCT e.id_encuesta, e.titulo
    FROM encuestas e
    JOIN preguntas p ON e.id_encuesta = p.id_encuesta
    JOIN respuestas r ON p.id_pregunta = r.id_pregunta
    JOIN respuestas_usuario ru ON ru.id_respuesta = r.id_respuesta
    WHERE ru.id_usuario = ?
    ORDER BY e.id_encuesta DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

include 'ver_estadisticas.html';
?>