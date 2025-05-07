<?php
session_start();
include '../conexion.php';

header('Content-Type: application/json');

$response = ['success' => false, 'errors' => []];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '') {
    $response['errors']['email'] = 'Ingresa tu correo electrónico.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['errors']['email'] = 'Formato de correo inválido.';
}


    if ($password === '') {
        $response['errors']['password'] = 'Ingresa tu contraseña.';
    }

    if (empty($response['errors'])) {
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            if (password_verify($password, $usuario['contrasena'])) {
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['rol'] = $usuario['rol'];
                $response['success'] = true;
                $response['redirect'] = '../inicio/index.php';
            } else {
                $response['errors']['password'] = 'La contraseña es incorrecta.';
            }
        } else {
            $response['errors']['email'] = 'Correo no registrado.';
        }
    }
} else {
    $response['errors']['general'] = 'Acceso no autorizado.';
}

echo json_encode($response);
exit;
