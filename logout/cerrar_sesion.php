<?php
session_start();
session_unset();     
session_destroy();    //destruinmos sesion y variables
header("Location: ../login/login.html"); // volvemos la login
exit();
?>
