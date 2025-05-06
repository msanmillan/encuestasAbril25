<?php
session_start();
session_unset();      // Limpia variables de sesión
session_destroy();    // Destruye la sesión actual
header("Location: ../login/login.html"); // Redirige al login
exit();
?>
