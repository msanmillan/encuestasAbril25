<?php
session_start();
include '../conexion.php';

// comprobamos si ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

$sql = "
    SELECT DISTINCT e.id_encuesta, e.titulo
    FROM encuestas e
    JOIN preguntas p ON e.id_encuesta = p.id_encuesta
    JOIN respuestas r ON p.id_pregunta = r.id_pregunta
    JOIN respuestas_usuario ru ON ru.id_respuesta = r.id_respuesta
    WHERE ru.id_usuario = ?
    ORDER BY e.id_encuesta DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Mis estadísticas</title>
    <link rel="stylesheet" href="ver_estadisticas.css">
</head>
<body>

<header>
    <div class="nav-izquierda">
        <a href="../inicio/index.php" class="logo">Encuestas</a>
    </div>
    <div class="nav-derecha">
        <?php if (isset($_SESSION['id_usuario'])): ?>
            <span class="usuario">👤 <?= htmlspecialchars($_SESSION['nombre']) ?></span>
            <a class="cerrar" href="../logout/cerrar_sesion.php">Cerrar sesión</a>
        <?php endif; ?>
    </div>
</header>

<h2>Encuestas que has respondido</h2>

<?php if ($res->num_rows > 0): ?>
    <?php while ($row = $res->fetch_assoc()): ?>
        <div class="encuesta-box">
            <h3><?= htmlspecialchars($row['titulo']) ?></h3>
            <a class="btn" href="estadisticas_encuesta.php?id=<?= $row['id_encuesta'] ?>">Ver estadísticas</a>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No has respondido a ninguna encuesta todavía.</p>
<?php endif; ?>

<div class="volver-container">
    <a href="../inicio/index.php" class="volver-btn">← Volver al inicio</a>
</div>
</body>
</html>
