<?php
// INICIAR LA SESION PARA ACCEDER A VARIABLES DE SESION
session_start();

// VERIFICAR SI EL USUARIO HA INICIADO SESION, SI NO REDIRIGIR AL LOGIN
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login/login.html");
    exit();
}

// INCLUIR ARCHIVO DE CONEXION A LA BASE DE DATOS
include '../conexion.php';

// VERIFICAR QUE EL METODO DE LA SOLICITUD SEA POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // OBTENER Y LIMPIAR EL TITULO Y LA DESCRIPCION DEL FORMULARIO
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);

    // OBTENER LAS PREGUNTAS DEL FORMULARIO (SI NO HAY, USAR ARRAY VACIO)
    $preguntas = $_POST['preguntas'] ?? [];

    // VERIFICAR QUE EL TITULO, LA DESCRIPCION Y LAS PREGUNTAS NO ESTEN VACIOS
    if (empty($titulo) || empty($descripcion) || count($preguntas) === 0) {
        die("Debes completar el título, la descripción y al menos una pregunta.");
    }

    // INICIAR UNA TRANSACCION PARA GARANTIZAR ATOMICIDAD
    $conn->begin_transaction();

    try {
        // INSERTAR LA ENCUESTA EN LA TABLA `encuestas`
        $sql_encuesta = "INSERT INTO encuestas (titulo, descripcion, creador_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql_encuesta);
        $stmt->bind_param("ssi", $titulo, $descripcion, $_SESSION['id_usuario']);
        $stmt->execute();

        // OBTENER EL ID DE LA ENCUESTA INSERTADA PARA USARLO EN LAS PREGUNTAS
        $id_encuesta = $stmt->insert_id;

        // ITERAR SOBRE CADA PREGUNTA ENVIADA
        foreach ($preguntas as $index => $textoPregunta) {
            // INSERTAR CADA PREGUNTA EN LA TABLA `preguntas`
            $sql_pregunta = "INSERT INTO preguntas (id_encuesta, texto) VALUES (?, ?)";
            $stmt_pregunta = $conn->prepare($sql_pregunta);
            $stmt_pregunta->bind_param("is", $id_encuesta, $textoPregunta);
            $stmt_pregunta->execute();

            // OBTENER EL ID DE LA PREGUNTA PARA RELACIONAR LAS RESPUESTAS
            $id_pregunta = $stmt_pregunta->insert_id;

            // CONSTRUIR LA CLAVE QUE CONTIENE LAS RESPUESTAS DE ESTA PREGUNTA
            $clave_respuestas = 'respuestas_' . ($index + 1);
            $respuestas = $_POST[$clave_respuestas] ?? [];

            // VALIDAR QUE HAYA ENTRE 2 Y 4 RESPUESTAS POR PREGUNTA
            if (count($respuestas) < 2 || count($respuestas) > 4) {
                // LANZAR UNA EXCEPCION PARA DETENER TODO Y HACER ROLLBACK
                throw new Exception("Cada pregunta debe tener entre 2 y 4 respuestas.");
            }

            // INSERTAR CADA RESPUESTA EN LA TABLA `respuestas`
            foreach ($respuestas as $textoRespuesta) {
                $sql_resp = "INSERT INTO respuestas (id_pregunta, texto) VALUES (?, ?)";
                $stmt_resp = $conn->prepare($sql_resp);
                $stmt_resp->bind_param("is", $id_pregunta, $textoRespuesta);
                $stmt_resp->execute();
            }
        }

        // SI TODO FUE EXITOSO, CONFIRMAMOS LA TRANSACCION
        $conn->commit();

        // REDIRIGIMOS AL INICIO
        header("Location: ../inicio/index.php");
        exit();

    } catch (Exception $e) {
        // SI OCURRE ALGUN ERROR, CANCELAMOS TODA LA TRANSACCION
        $conn->rollback();

        // REDIRIGIMOS AL INICIO EN CASO DE FALLO
        header("Location: ../inicio/index.php");
    }
}
?>