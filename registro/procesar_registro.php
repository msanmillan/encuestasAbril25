<?php
session_start();
include '../conexion.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $contrasena = trim($_POST["password"]);
    $confirmar = trim($_POST["confirm_password"]);

    if ($contrasena !== $confirmar) {
        die("❌ Las contraseñas no coinciden.");
    }

    // confirmamos email ya esta registrado
    $sql = "SELECT id_usuario FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        die("❌ El correo ya está registrado.");
    }

// con esto encriptamos la contraseña 
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    //  usuario con rol por defecto "usuario"
    $insert = "INSERT INTO usuarios (nombre, email, contrasena, rol) VALUES (?, ?, ?, 'usuario')";
    $stmt_insert = $conn->prepare($insert);
    $stmt_insert->bind_param("sss", $nombre, $email, $hash);

    if ($stmt_insert->execute()) {
        echo "✅ Usuario registrado correctamente. <a href='../login/login.html'>Iniciar sesión</a>";
    } else {
        echo "❌ Error al registrar usuario.";
    }

    $stmt_insert->close();
    $stmt->close();
    $conn->close();
} else {
    echo "⚠️ Acceso no permitido.";
}
?>
