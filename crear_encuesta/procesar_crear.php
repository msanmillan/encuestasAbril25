<?php
session_start();
//debe de iniciar sesion para poder accede a crear encuesta
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login/login.html");
    exit();
}

include '../conexion.php';
//metodo post  para enviar los datos a la base de datos 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $preguntas = $_POST['preguntas'] ?? [];

    if (empty($titulo) || empty($descripcion) || count($preguntas) == 0) {
        die("Debes completar el título, la descripción y al menos una pregunta.");
    }

    // insertamos la encuesta en la tabla 
    $sql_encuesta = "INSERT INTO encuestas (titulo, descripcion, creador_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_encuesta);
    $stmt->bind_param("ssi", $titulo, $descripcion, $_SESSION['id_usuario']);
    $stmt->execute();
    $id_encuesta = $stmt->insert_id;

    // y aqui insertamos las preguntas y respuestas en distintas tablas 
    foreach ($preguntas as $index => $textoPregunta) {
        $sql_pregunta = "INSERT INTO preguntas (id_encuesta, texto) VALUES (?, ?)";
        $stmt_pregunta = $conn->prepare($sql_pregunta);
        $stmt_pregunta->bind_param("is", $id_encuesta, $textoPregunta);
        $stmt_pregunta->execute();
        $id_pregunta = $stmt_pregunta->insert_id;

        $clave_respuestas = 'respuestas_' . ($index + 1);
        $respuestas = $_POST[$clave_respuestas] ?? [];

        //debe de haber entre 2 y 4 respuestas 

        if (count($respuestas) < 2 || count($respuestas) > 4) {
            die("Cada pregunta debe tener entre 2 y 4 respuestas.");
        }

        foreach ($respuestas as $textoRespuesta) {
            $sql_resp = "INSERT INTO respuestas (id_pregunta, texto) VALUES (?, ?)";
            $stmt_resp = $conn->prepare($sql_resp);
            $stmt_resp->bind_param("is", $id_pregunta, $textoRespuesta);
            $stmt_resp->execute();
        }
    }

    header("Location: ../inicio/index.php");
    exit();
    }
?>
