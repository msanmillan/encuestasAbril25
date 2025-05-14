<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../conexion.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: ../inicio/index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_encuesta = intval($_GET['id']);

// Obtener título de la encuesta
$sql_titulo = "SELECT titulo FROM encuestas WHERE id_encuesta = ?";
$stmt = $conn->prepare($sql_titulo);
$stmt->bind_param("i", $id_encuesta);
$stmt->execute();
$res = $stmt->get_result();
$titulo = $res->fetch_assoc()['titulo'] ?? 'Sin título';

include 'estadisticas_encuesta.html';
