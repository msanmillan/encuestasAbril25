<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    // si no hemos logeado nos dirigue a login.html
    header("Location: ../login/login.html");
    exit();
}

// el contenido index como texto 
$html = file_get_contents("index.html");

// reeemplazamos el nombre y rol por los datos de la base de datos 
$html = str_replace("{{nombre}}", htmlspecialchars($_SESSION['nombre']), $html);
$html = str_replace("{{rol}}", htmlspecialchars($_SESSION['rol']), $html);

// y lo mostramos en el html
echo $html;
?>
