<?php
include '../conexion.php';
session_start();

// comprobamos si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    echo "debes iniciar sesión para enviar respuestas";
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// recogemos el JSON enviado por fetch
$datos = json_decode(file_get_contents("php://input"), true);

// validamos que lleguen respuestas
if (!isset($datos['respuestas']) || !is_array($datos['respuestas'])) {
    echo "no se han recibido respuestas válidas";
    exit();
}

$respuestas = $datos['respuestas'];

// insertamos cada respuesta en la tabla
foreach ($respuestas as $id_respuesta) {
    if (!$id_respuesta) continue;

    $sql = "INSERT INTO respuestas_usuario (id_usuario, id_respuesta) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_usuario, $id_respuesta);
    $stmt->execute();
}

