<?php
session_start();
session_unset();     
session_destroy();    //destruinmos sesion y variables
header("Location: ../inicio/index.php"); // volvemos el inicio
exit();
?>
