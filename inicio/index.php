<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login/login.html");
    exit();
}

// Leer el contenido del HTML como texto
$html = file_get_contents("index.html");

// Reemplazar los marcadores con datos reales
$html = str_replace("{{nombre}}", htmlspecialchars($_SESSION['nombre']), $html);
$html = str_replace("{{rol}}", htmlspecialchars($_SESSION['rol']), $html);

// Mostrar el HTML con los datos insertados
echo $html;
?>
