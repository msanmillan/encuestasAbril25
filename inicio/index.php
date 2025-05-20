<?php
session_start();
include '../conexion.php';

$html = file_get_contents("index.html");

$por_pagina = 6;
$pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$inicio = ($pagina_actual - 1) * $por_pagina;

$sql_total = "SELECT COUNT(*) AS total FROM encuestas";
$resultado_total = $conn->query($sql_total);
$fila_total = $resultado_total->fetch_assoc();
$total_encuestas = $fila_total['total'];
$total_paginas = ceil($total_encuestas / $por_pagina);

$sql = "SELECT id_encuesta, titulo, descripcion FROM encuestas ORDER BY id_encuesta DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $inicio, $por_pagina);
$stmt->execute();
$resultado = $stmt->get_result();

// Paginación arriba
$paginacion = "";
if ($total_paginas > 1) {
    $paginacion .= "<div class='paginacion'>";

    // Botón anterior
    if ($pagina_actual > 1) {
        $prev = $pagina_actual - 1;
        $paginacion .= "<a class='btn-pag' href='?pagina=$prev'>&laquo; Anterior</a>";
    }

    // Botones numerados
    for ($i = 1; $i <= $total_paginas; $i++) {
        if ($i == $pagina_actual) {
            $paginacion .= "<span class='btn-pag active'>$i</span>";
        } else {
            $paginacion .= "<a class='btn-pag' href='?pagina=$i'>$i</a>";
        }
    }

    // Botón siguiente
    if ($pagina_actual < $total_paginas) {
        $next = $pagina_actual + 1;
        $paginacion .= "<a class='btn-pag' href='?pagina=$next'>Siguiente &raquo;</a>";
    }

    $paginacion .= "</div>";
}



$bloques = "";
if ($resultado->num_rows > 0) {
    while ($encuesta = $resultado->fetch_assoc()) {
        $titulo = htmlspecialchars($encuesta['titulo']);
        $descripcion = htmlspecialchars($encuesta['descripcion']);
        $id_encuesta_actual = $encuesta['id_encuesta'];

        if (isset($_SESSION['id_usuario'])) {
            $id_usuario = $_SESSION['id_usuario'];

            $sql_check = "
                SELECT 1
                FROM respuestas_usuario ru
                JOIN respuestas r ON ru.id_respuesta = r.id_respuesta
                JOIN preguntas p ON r.id_pregunta = p.id_pregunta
                WHERE ru.id_usuario = ? AND p.id_encuesta = ?
                LIMIT 1
            ";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("ii", $id_usuario, $id_encuesta_actual);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $accion = "<a class='btn' href='../ver_estadisticas/estadisticas_encuesta.php?id=$id_encuesta_actual'>Ver estadísticas</a>";
            } else {
                $accion = "<a class='btn' href='../ver_encuestas/ver_encuesta.html?id=$id_encuesta_actual'>Responder</a>";
            }
            $stmt_check->close();
        } else {
            $accion = "<p class='info'>Inicia sesión para responder</p>";
        }

        $bloques .= "
        <div class='card'>
            <h3>$titulo</h3>
            <p>$descripcion</p>
            $accion
        </div>";
    }
} else {
    $bloques = "<p>No hay encuestas aún</p>";
}

// Insertamos todo dentro de la rejilla y paginación
$contenido = "
$paginacion
<div class='grid-encuestas'>
    $bloques
</div>";

$html = str_replace("{{encuestas}}", $contenido, $html);

// Navegación
if (isset($_SESSION['id_usuario'])) {
    $nav = "
        <span class='usuario'>👤 " . htmlspecialchars($_SESSION['nombre']) . "</span>
        <a href='../crear_encuesta/crear.html'>Crear encuesta</a>
        <a href='../ver_estadisticas/ver_estadisticas.php'>Ver estadísticas</a>
        <a href='../logout/cerrar_sesion.php'>Cerrar sesión</a>";
} else {
    $nav = "
        <a href='../login/login.html'>Iniciar sesión</a>
        <a href='../registro/registro.html'>Registrarse</a>";
}

$html = str_replace("{{navegacion}}", $nav, $html);

echo $html;
?>

