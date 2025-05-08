<?php
session_start();
include '../conexion.php';

// si el usuario no ha iniciado sesión, igual mostramos encuestas públicas
$html = file_get_contents("index.html");

// obtenemos encuestas desde la base de datos
$sql = "SELECT id_encuesta, titulo, descripcion FROM encuestas ORDER BY id_encuesta DESC";
$resultado = $conn->query($sql);

// preparamos los recuadros con cada encuesta
$bloques = "";
if ($resultado->num_rows > 0) {
    while ($encuesta = $resultado->fetch_assoc()) {
        $titulo = htmlspecialchars($encuesta['titulo']);
        $descripcion = htmlspecialchars($encuesta['descripcion']);

        // si está logueado puede responder, si no, se le avisa
        if (isset($_SESSION['id_usuario'])) {
            $accion = "<a class='btn' href='#'>responder</a>";
        } else {
            $accion = "<p class='info'> inicia sesión para responder</p>";
        }

        $bloques .= "
        <div class='card'>
            <h3>$titulo</h3>
            <p>$descripcion</p>
            $accion
        </div>";
    }
} else {
    $bloques = "<p>no hay encuestas aún</p>";
}

// sustituimos los recuadros en el html
$html = str_replace("{{encuestas}}", $bloques, $html);

// preparamos la navegación según si ha iniciado sesión o no
if (isset($_SESSION['id_usuario'])) {
    $nav = "
        <span class='usuario'>👤 " . htmlspecialchars($_SESSION['nombre']) . "</span>
        <a href='../crear_encuesta/crear.html'>crear encuesta</a>
        <a href='../logout/cerrar_sesion.php'>cerrar sesión</a>";
} else {
    $nav = "
        <a href='../login/login.html'>iniciar sesión</a>
        <a href='../registro/registro.html'>registrarse</a>";
}

// insertamos la navegación en el html
$html = str_replace("{{navegacion}}", $nav, $html);

// mostramos el contenido completo
echo $html;
?>
