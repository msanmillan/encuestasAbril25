<?php
session_start();
include '../conexion.php';
// Hacemos POST para nombre y contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $sql = "SELECT * FROM usuarios WHERE nombre = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $resultado = $stmt->get_result();
// dependiendo de si ponemos bien la contraseña o mal o ponemos mal el usuario nos dirige a index.php o no 
        if ($resultado->num_rows == 1) {
            $usuario = $resultado->fetch_assoc();
            if (password_verify($password, $usuario['contrasena'])) {
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['rol'] = $usuario['rol'];
                header("Location: ../inicio/index.php");
                exit();
            } else {
                echo "⚠ Contraseña incorrecta.";
            }
        } else {
            echo "⚠ Usuario no encontrado.";
        }
    } else {
        echo "⚠ Completa todos los campos.";
    }
} else {
    echo "⚠ Acceso no autorizado.";
}
?>
