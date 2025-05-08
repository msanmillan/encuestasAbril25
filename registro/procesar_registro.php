<?php
session_start();
include '../conexion.php';

header('Content-Type: application/json');

$response = ['success' => false, 'errors' => []];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $contrasena = trim($_POST["password"]);
    $confirmar = trim($_POST["confirm_password"]);

    // VALIDAR QUE TODOS LOS CAMPOS ESTEN RELLENOS
    if (empty($nombre)) {
        $response['errors']['username'] = 'El nombre de usuario es obligatorio.';
    }
    if (empty($email)) {
        $response['errors']['email'] = 'El correo electrónico es obligatorio.';
    }
    if (empty($contrasena)) {
        $response['errors']['password'] = 'La contraseña es obligatoria.';
    }
    if (empty($confirmar)) {
        $response['errors']['confirm_password'] = 'Es necesario confirmar la contraseña.';
    }

    // VALIDAR QUE EL NOMBRE DE USUARIO TENGA AL MENOS 3 CARACTERES
    if (strlen($nombre) < 3) {
        $response['errors']['username'] = 'El nombre de usuario debe tener al menos 3 caracteres.';
    }

    // CONFIRMAR QUE LAS CONTRASEÑAS COINCIDAN
    if ($contrasena !== $confirmar) {
        $response['errors']['confirm_password'] = 'Las contraseñas no coinciden.';
    }

    // VALIDAR QUE LA CONTRASEÑA CUMPLA CON LOS REQUISITOS (AL MENOS UNA LETRA, UN NUMERO, UN CARACTER ESPECIAL Y 8 CARACTERES)
    $passwordPattern = '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    if (!preg_match($passwordPattern, $contrasena)) {
        $response['errors']['password'] = 'La contraseña debe tener al menos 8 caracteres, una letra, un número y un carácter especial.';
    }

    // VERIFICAR QUE EL CORREO TENGA FORMATO VALIDO
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['errors']['email'] = 'El correo electrónico no tiene un formato válido.';
    }


    // CONFIRMAR QUE EL NOMBRE DE USUARIO NO ESTE REGISTRADO
    $sql = "SELECT id_usuario FROM usuarios WHERE nombre = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $response['errors']['username'] = 'El nombre de usuario ya está registrado.';
    }

    // CONFIRMAR QUE EL CORREO ELECTRONICO NO ESTE REGISTRADO
    $sql_email = "SELECT id_usuario FROM usuarios WHERE email = ?";
    $stmt_email = $conn->prepare($sql_email);
    $stmt_email->bind_param("s", $email);
    $stmt_email->execute();
    $stmt_email->store_result();

    if ($stmt_email->num_rows > 0) {
        $response['errors']['email'] = 'El correo electrónico ya está registrado.';
    }


    // SI NO HAY ERRORES, SE INSERTA EL NUEVO USUARIO
    if (empty($response['errors'])) {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);

        $insert = "INSERT INTO usuarios (nombre, email, contrasena, rol) VALUES (?, ?, ?, 'usuario')";
        $stmt_insert = $conn->prepare($insert);
        $stmt_insert->bind_param("sss", $nombre, $email, $hash);

        if ($stmt_insert->execute()) {
            $response['success'] = true;
        } else {
            $response['errors']['general'] = 'Error al registrar el usuario.';
        }

        $stmt_insert->close();
    }

    $stmt->close();
    $stmt_email->close();
    $conn->close();
} else {
    $response['errors']['general'] = 'Acceso no permitido.';
}

echo json_encode($response);
exit;
?>